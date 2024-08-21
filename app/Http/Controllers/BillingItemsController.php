<?php

namespace App\Http\Controllers;

use App\BillingItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
use App\Admin;
use App\Jobs\quickBook;

class BillingItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /* $emailJob = new quickBook();
        pre($emailJob,1);
        dispatch($emailJob);
        Artisan::call('queue:work');
        exit; */
        $checkPermission = User::checkPermission(['listing_billing_items'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        //$items = DB::table('billing_items')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("billing-items.index");
    }

    public function listbillingitems(Request $request)
    {
        $permissionEdit = User::checkPermission(['update_billing_items'], '', auth()->user()->id);
        $permissionDelete = User::checkPermission(['delete_billing_items'], '', auth()->user()->id);

        $req = $request->all();
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['billing_items.id', 'billing_name', 'item_code', 'costs.code', 'flag_prod_tax_type', 'billing_items.status'];

        $total = BillingItems::selectRaw('count(*) as total')
            ->where('billing_items.deleted', '0');
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('billing_items')
            ->selectRaw('billing_items.id,billing_items.billing_name,billing_items.item_code,costs.code,billing_items.flag_prod_tax_type,billing_items.status')
            ->leftJoin('costs', 'costs.id', '=', 'billing_items.code')
            ->where('billing_items.deleted', '0');

        $filteredq = DB::table('billing_items')
            ->leftJoin('costs', 'costs.id', '=', 'billing_items.code')
            ->where('billing_items.deleted', '0');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('billing_name', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%')
                    ->orWhere('flag_prod_tax_type', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('billing_name', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%')
                    ->orWhere('flag_prod_tax_type', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $action = '<div class="dropdown">';

            $delete =  route('deletebillingitem', $items->id);
            $edit =  route('editbillingitem', $items->id);

            if ($permissionEdit) {
                $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
            }

            if ($items->item_code != 'SCC' && $items->item_code != 'FDBC' && $items->item_code != 'FDDC' && $items->item_code != 'FDDC/ DUTY CHARGES' && $items->item_code != 'FDTC' && $items->item_code != 'FDOGC' && $items->item_code != '1058' && $items->id != '26' && $items->item_code != 'C1071' && $items->item_code != 'C1071/ Commission fret aerien (UPS)') {
                if ($permissionDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
            }
            $action .= '</div>';

            $data[] = [$items->id, $items->billing_name, $items->item_code, !empty($items->code) ? $items->code : '-', $items->flag_prod_tax_type == 1 ? 'Yes' : 'No', ($items->status == 1) ? 'Active' : 'Inactive', $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_billing_items'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new BillingItems;
        /*$dataCodes = DB::table('costs')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'code');
        $dataCodes = json_decode($dataCodes,1);
        ksort($dataCodes);*/
        $dataCost = DB::table('costs')
            ->select('id', DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');
        return view('billing-items.form', ['model' => $model, 'dataCost' => $dataCost]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        session_start();
        $validater = $this->validate($request, [
            'billing_name' => 'required|string',
        ]);
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = BillingItems::create($input);

        // Add billing item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('billing-item',$model);
        }*/

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '3';
            $fData['flagModule'] = 'billing-item';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('billingitems');
    }

    public function storenewitem(Request $request)
    {
        session_start();
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = BillingItems::create($input);

        // Add billing item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('billing-item',$model);
        } */

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '3';
            $fData['flagModule'] = 'billing-item';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        return 'true';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\BillingItems  $billingItems
     * @return \Illuminate\Http\Response
     */
    public function show(BillingItems $billingItems)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BillingItems  $billingItems
     * @return \Illuminate\Http\Response
     */
    public function edit(BillingItems $billingItems, $id)
    {
        $checkPermission = User::checkPermission(['update_billing_items'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('billing_items')->where('id', $id)->first();
        /*$dataCodes = DB::table('costs')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'code');
        $dataCodes = json_decode($dataCodes,1);
        ksort($dataCodes);*/

        $dataCost = DB::table('costs')
            ->select('id', DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');
        return view("billing-items.form", ['model' => $model, 'dataCost' => $dataCost]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BillingItems  $billingItems
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BillingItems $billingItems, $id)
    {
        session_start();
        $model = BillingItems::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        if (!isset($input['flag_prod_tax_type'])) {
            $input['flag_prod_tax_type'] = 0;
            $input['flag_prod_tax_amount'] = 0.00;
        }
        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);

        // Update billing item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('update-billing-item',$model);
        }*/

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '3';
            $fData['flagModule'] = 'update-billing-item';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('billingitems');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BillingItems  $billingItems
     * @return \Illuminate\Http\Response
     */
    public function destroy(BillingItems $billingItems, $id)
    {
        session_start();
        $record = DB::table('billing_items')->where('id', $id)->first();

        $model = BillingItems::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);

        // Delete billing item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('delete-billing-item',$record);
        }*/

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '3';
            $fData['flagModule'] = 'delete-billing-item';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function getbillinglistdata()
    {
        $id = $_POST['billingId'];
        $aAr = array();
        $dataBilling = DB::table('billing_items')->where('id', $id)->first();
        $aAr['percentageType'] = $dataBilling->flag_prod_tax_type;
        $aAr['percentage'] = $dataBilling->flag_prod_tax_amount;
        $aAr['billingAccount'] = $dataBilling->billing_account;
        return json_encode($aAr);
    }

    public function getbillingdata()
    {
        $id = $_POST['billingId'];
        $aAr = array();
        $dataBilling = DB::table('billing_items')->where('id', $id)->first();
        $storageChargeData = DB::table('storage_charges')->where('measure', 'M')->first();
        if ($dataBilling->item_code == 'SCC') {
            if ($_POST['flagModule'] == 'cargo') {
                $hawbId = $_POST['hawbId'];
                $aAr['billingCode'] = 'SCC';
                $dataCargo = DB::table('cargo')->where('id', $_POST['moduleId'])->first();
                $dataHouseFile = DB::table('hawb_files')->where('id', $hawbId)->first();
                /*$fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');*/
                $fromDate = $dataHouseFile->shipment_received_date;
                $toDate = $dataHouseFile->shipment_delivered_date;
                if (empty($fromDate))
                    $fromDate = date('Y-m-d');
                if (empty($toDate))
                    $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = strtotime($toDate) - $your_date;

                $dayDifference = round($datediff / (60 * 60 * 24));

                $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $hawbId)->first();
                $measureWeight = $modelCargoPackage->measure_weight;
                $measureVolume = $modelCargoPackage->measure_volume;

                $pWeight = $modelCargoPackage->pweight;
                $pVolume = $modelCargoPackage->pvolume;

                $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
                $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
                $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
                if ($chageDaysWeight > 0)
                    $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
                else
                    $totalChargeWeight = '0.00';


                $storageChargeDataVolume = DB::table('storage_charges')->where('measure', strtoupper($measureVolume))->first();
                $chageDaysVolume = $dayDifference - $storageChargeDataVolume->grace_period;
                $chargeVolumePerMeterOrFeet = $storageChargeDataVolume->charge;
                if ($chageDaysVolume > 0)
                    $totalChargeVolume = $chargeVolumePerMeterOrFeet * $pVolume * $chageDaysVolume;
                else
                    $totalChargeVolume = '0.00';

                if ($totalChargeVolume > $totalChargeWeight) {
                    $finalChargeDays = $chageDaysVolume;
                    $finalCharge = $chargeVolumePerMeterOrFeet * $pVolume;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataVolume->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
                } else if ($totalChargeWeight > $totalChargeVolume) {
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
                } else if ($totalChargeWeight == $totalChargeVolume && ($totalChargeWeight > 0 || $totalChargeVolume > 0)) {
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
                } else {
                    $finalChargeDays = '0.00';
                    $finalCharge = '0.00';
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : No Charge (In Grace Period)';
                }

                // Check invoice has been created or not
                $dataHouseFileInvoices = DB::table('invoices')
                    ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.hawb_hbl_no', $hawbId)
                    ->where('invoices.housefile_module', 'cargo')
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->first();

                $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                    ->select(DB::raw('invoices.date'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.hawb_hbl_no', $hawbId)
                    ->where('invoices.housefile_module', 'cargo')
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->orderBy('invoices.id', 'desc')
                    ->first();
                if (!empty($dataHouseFileInvoicesForCheckLastInvoice)) {
                    $your_date = strtotime($dataHouseFileInvoicesForCheckLastInvoice->date);
                    $datediff = strtotime($toDate) - $your_date;

                    $dayDifference = round($datediff / (60 * 60 * 24));
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($dataHouseFileInvoicesForCheckLastInvoice->date)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days)';
                }
            } else if ($_POST['flagModule'] == 'ups') {
                $aAr['billingCode'] = 'SCC';
                $data = DB::table('ups_details')->where('id', $_POST['moduleId'])->first();

                $fromDate = $data->shipment_received_date;
                $toDate = $data->shipment_delivered_date;
                if (empty($fromDate))
                    $fromDate = date('Y-m-d');
                if (empty($toDate))
                    $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = strtotime($toDate) - $your_date;

                $dayDifference = round($datediff / (60 * 60 * 24));

                $measureWeight = 'k';
                $pWeight = $data->weight;


                $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
                $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
                $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
                if ($chageDaysWeight > 0)
                    $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
                else
                    $totalChargeWeight = '0.00';

                if ($totalChargeWeight > 0) {
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
                } else {
                    $finalChargeDays = '0';
                    $finalCharge = '0';
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : No Charge (In Grace Period)';
                }

                // Check invoice has been created or not
                $dataHouseFileInvoices = DB::table('invoices')
                    ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.ups_id', $_POST['moduleId'])
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->first();

                $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                    ->select(DB::raw('invoices.date'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.ups_id', $_POST['moduleId'])
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->orderBy('invoices.id', 'desc')
                    ->first();
                if (!empty($dataHouseFileInvoicesForCheckLastInvoice)) {
                    $your_date = strtotime($dataHouseFileInvoicesForCheckLastInvoice->date);
                    $datediff = strtotime($toDate) - $your_date;

                    $dayDifference = round($datediff / (60 * 60 * 24));
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($dataHouseFileInvoicesForCheckLastInvoice->date)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days)';
                }
            } else if ($_POST['flagModule'] == 'aeropost') {
                $aAr['billingCode'] = 'SCC';
                $data = DB::table('aeropost')->where('id', $_POST['moduleId'])->first();

                $fromDate = $data->shipment_received_date;
                $toDate = $data->shipment_delivered_date;
                if (empty($fromDate))
                    $fromDate = date('Y-m-d');
                if (empty($toDate))
                    $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = strtotime($toDate) - $your_date;

                $dayDifference = round($datediff / (60 * 60 * 24));

                $measureWeight = 'k';
                $pWeight = $data->real_weight;


                $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
                $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
                $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
                if ($chageDaysWeight > 0)
                    $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
                else
                    $totalChargeWeight = '0.00';

                if ($totalChargeWeight > 0) {
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
                } else {
                    $finalChargeDays = '0';
                    $finalCharge = '0';
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : No Charge (In Grace Period)';
                }

                // Check invoice has been created or not
                $dataHouseFileInvoices = DB::table('invoices')
                    ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.aeropost_id', $_POST['moduleId'])
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->first();

                $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                    ->select(DB::raw('invoices.date'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.aeropost_id', $_POST['moduleId'])
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->orderBy('invoices.id', 'desc')
                    ->first();
                if (!empty($dataHouseFileInvoicesForCheckLastInvoice)) {
                    $your_date = strtotime($dataHouseFileInvoicesForCheckLastInvoice->date);
                    $datediff = strtotime($toDate) - $your_date;

                    $dayDifference = round($datediff / (60 * 60 * 24));
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($dataHouseFileInvoicesForCheckLastInvoice->date)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days)';
                }
            } else if ($_POST['flagModule'] == 'ccpack') {
                $aAr['billingCode'] = 'SCC';
                $data = DB::table('ccpack')->where('id', $_POST['moduleId'])->first();

                $fromDate = $data->shipment_received_date;
                $toDate = $data->shipment_delivered_date;
                if (empty($fromDate))
                    $fromDate = date('Y-m-d');
                if (empty($toDate))
                    $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = strtotime($toDate) - $your_date;

                $dayDifference = round($datediff / (60 * 60 * 24));

                $measureWeight = 'k';
                $pWeight = $data->weight;


                $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
                $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
                $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
                if ($chageDaysWeight > 0)
                    $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
                else
                    $totalChargeWeight = '0.00';

                if ($totalChargeWeight > 0) {
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
                } else {
                    $finalChargeDays = '0';
                    $finalCharge = '0';
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : No Charge (In Grace Period)';
                }

                // Check invoice has been created or not
                $dataHouseFileInvoices = DB::table('invoices')
                    ->select(['invoice_item_details.quantity'])
                    ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.ccpack_id', $_POST['moduleId'])
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->first();

                $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                    ->select(DB::raw('invoices.date'))
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.ccpack_id', $_POST['moduleId'])
                    ->where('invoices.deleted', '0')
                    ->where('invoice_item_details.item_code', 'SCC')
                    ->orderBy('invoices.id', 'desc')
                    ->first();
                if (!empty($dataHouseFileInvoicesForCheckLastInvoice)) {
                    $your_date = strtotime($dataHouseFileInvoicesForCheckLastInvoice->date);
                    $datediff = strtotime($toDate) - $your_date;

                    $dayDifference = round($datediff / (60 * 60 * 24));
                    $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($dataHouseFileInvoicesForCheckLastInvoice->date)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days)';
                }
            }

            $aAr['billingName'] = $desc;
            $aAr['billingCustomDays'] =  number_format($finalChargeDays - $dataHouseFileInvoices->totalAddedDays, 2);
            $aAr['billingCustomStorageCharage'] = number_format($finalCharge, 2);
        } else if ($dataBilling->item_code == 'SCD') {
            $aAr['billingCode'] = 'SCD';
            if ($_POST['cargoId'] != '') {
                $dataCargo = DB::table('cargo')->where('id', $_POST['cargoId'])->first();
                $fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = $now - $your_date;

                $dayDifference = round($datediff / (60 * 60 * 24));
                $aAr['billingName'] = 'Storage Charge (Daily) Duration : ' . $dayDifference . ' Days';
                $aAr['billingDays'] = round($datediff / (60 * 60 * 24));
            } else {
                $aAr['billingName'] = $dataBilling->description;
                $aAr['billingDays'] = 0;
            }
        } else if ($dataBilling->item_code == 'SCW') {
            $aAr['billingCode'] = 'SCW';
            if ($_POST['cargoId'] != '') {
                $dataCargo = DB::table('cargo')->where('id', $_POST['cargoId'])->first();
                $fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = $now - $your_date;

                $dayDifference = round(($datediff / (60 * 60 * 24) / 7));
                $aAr['billingName'] = 'Storage Charge (Weekly) Duration : ' . $dayDifference . ' Weeks';
                $aAr['billingWeeks'] = round(($datediff / (60 * 60 * 24)) / 7);
            } else {
                $aAr['billingName'] = $dataBilling->description;
                $aAr['billingWeeks'] = 0;
            }
        } else if ($dataBilling->item_code == 'SCM') {
            $aAr['billingCode'] = 'SCM';
            if ($_POST['cargoId'] != '') {
                $dataCargo = DB::table('cargo')->where('id', $_POST['cargoId'])->first();
                $fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = $now - $your_date;

                $dayDifference = round(($datediff / (60 * 60 * 24) / 30));
                $aAr['billingName'] = 'Storage Charge (Monthly) Duration : ' . $dayDifference . ' Months';
                $aAr['billingMonths'] = round(($datediff / (60 * 60 * 24)) / 30);
            } else {
                $aAr['billingName'] = $dataBilling->description;
                $aAr['billingMonths'] = 0;
            }
        } else {
            $aAr['billingName'] = $dataBilling->description;
        }

        $aAr['code'] = $dataBilling->item_code;
        return json_encode($aAr);
    }

    public function getbillingitemsdropdowndataaftersubmit()
    {
        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get();
        $dt = '<option selected="selected" value="">Select ...</option>';
        foreach ($dataBillingItems as $key => $value) {
            $dt .=  '<option value="' . $value->id . '">' . $value->billing_name . '</option>';
        }
        return $dt;
    }

    public function checkunique($flag = '')
    {
        $value = $_POST['value'];
        $flag = $_POST['flag'];
        $id = $_POST['id'];
        if (!empty($id)) {
            if ($flag == 'billingName')
                $data = DB::table('billing_items')->where('deleted', '0')->where('billing_name', $value)->where('id', '<>', $id)->count();
            else
                $data = DB::table('billing_items')->where('deleted', '0')->where('item_code', $value)->where('id', '<>', $id)->count();
        } else {
            if ($flag == 'billingName')
                $data = DB::table('billing_items')->where('deleted', '0')->where('billing_name', $value)->count();
            else
                $data = DB::table('billing_items')->where('deleted', '0')->where('item_code', $value)->count();
        }

        if ($data)
            return 1;
        else
            return 0;
    }
}
