<?php

namespace App\Http\Controllers;

use App\Activities;
use Illuminate\Http\Request;
use App\User;
use App\ccpack;
use App\Expense;
use DB;
use Session;
use App\Clients;
use App\VerificationInspectionNote;
use Config;
use Illuminate\Support\Facades\Storage;
use Response;
use PDF;

class ccpackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_ccpack'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view('ccpack.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_ccpack'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $model = new ccpack;
        $model->arrival_date = date('d-m-Y');
        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');
        return view('ccpack._form', ['model' => $model, 'warehouses' => $warehouses]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $ccpack_operation_type = $input['ccpack_operation_type'];
        $consignee_name = $input['consignee_name'];
        $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
        if (empty($clientData)) {

            $newClientData['company_name'] = $consignee_name;
            $newClientData['phone_number'] = $input['consignee_telephone'];
            $newClientData['company_address'] = $input['consignee_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();


            $input['consignee'] = $clientData->id;
            $input['consignee_telephone'] = $input['consignee_telephone'];
            $input['consignee_address'] = $input['consignee_address'];
        } else {
            $input['consignee'] = $clientData->id;
            $input['consignee_telephone'] = $input['consignee_telephone'];
            $input['consignee_address'] = $input['consignee_address'];
        }

        $shipper_name = $input['shipper_name'];
        $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
        if (empty($clientData)) {

            $newClientData['company_name'] = $shipper_name;
            $newClientData['phone_number'] = $input['shipper_telephone'];
            $newClientData['company_address'] = $input['shipper_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();


            $input['shipper_name'] = $clientData->id;
            $input['shipper_telephone'] = $input['shipper_telephone'];
            $input['shipper_address'] = $input['shipper_address'];
        } else {
            $input['shipper_name'] = $clientData->id;
            $input['shipper_telephone'] = $input['shipper_telephone'];
            $input['shipper_address'] = $input['shipper_address'];
        }


        $dataLast = DB::table('ccpack')->orderBy('id', 'desc')->first();
        if (empty($dataLast)) {
            if ($ccpack_operation_type == 1)
                $input['file_number'] = 'CCI 1110';
            else
                $input['file_number'] = 'CCE 1110';
        } else {
            if ($ccpack_operation_type == 1) {
                $ab = 'CCI ';
            } else {
                $ab = 'CCE ';
            }
            $ab .= substr($dataLast->file_number, 4) + 1;
            $input['file_number'] = $ab;
        }
        $input['created_by'] = auth()->user()->id;
        $input['created_on'] = gmdate('Y-m-d H:i:s');
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        //pre($input);
        $model = ccpack::create($input);
        $data = json_decode($model, 1);
        if ($model->ccpack_operation_type == 1) {
            $data['flagFile'] = 'CCPack Import';
        } else {
            $data['flagFile'] = 'CCPack Export';
        }
        Activities::log('create', 'ccpack', (object) $data);
        if ($model->ccpack_operation_type == 1) {
            $dir = "Files/Courier/CCpack/Import/" . $model->file_number;
        } else {
            $dir = 'Files/Courier/CCpack/Export/' . $model->file_number;
        }

        $filePath = $dir;
        //pre($filePath);
        $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
        //pre($success.' '.'test');
        Session::flash('flash_message', 'Record has been created successfully');
        if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'addCcpackHousefile') {
            $dataCargoForImport = DB::table('ccpack_master')->where('deleted', 0)->whereNotNull('hawb_hbl_no')->get();
            $existingImportHawbFiles = '';
            $existingImportHawbFilesArray = array();
            if (!empty($dataCargoForImport)) {
                foreach ($dataCargoForImport as $key => $value) {
                    $existingImportHawbFiles .= $value->hawb_hbl_no . ',';
                    //array_push($existingHawbFiles,$dataExp);
                }
            }
            $nexistingImportHawbFiles = rtrim($existingImportHawbFiles, ',');
            $existingImportHawbFilesArray = explode(',', $nexistingImportHawbFiles);

            $dataImportHawb = DB::table('ccpack')->where('deleted', '0')->whereNotIn('id', $existingImportHawbFilesArray)->get();

            $dataImportHawbAll = array();
            foreach ($dataImportHawb as $k => $v) {
                $dataImportHawbAll[$k]['value'] = $v->id;
                $dataImportHawbAll[$k]['hawb_hbl_no'] = $v->awb_number;

                $dataClientConsignee = DB::table('clients')->where('id', $v->consignee)->first();
                $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                $dataImportHawbAll[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
                $dataImportHawbAll[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            }
            $dataImportHawbAll = json_encode($dataImportHawbAll, JSON_NUMERIC_CHECK);
            return Response::json(['success' => '1', 'dataImportHawbAll' => $dataImportHawbAll]);
        } else {
            return redirect('ccpack');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $checkPermission = User::checkPermission(['update_ccpack'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('ccpack')->where('id', $id)->first();
        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');

        return view('ccpack.update', ['model' => $model, 'warehouses' => $warehouses, 'billingParty' => $billingParty]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $checkPermission = User::checkPermission(['update_ccpack'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $input = $request->all();
        $consignee_name = $input['consignee_name'];
        $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
        if (empty($clientData)) {

            $newClientData['company_name'] = $consignee_name;
            $newClientData['phone_number'] = $input['consignee_telephone'];
            $newClientData['company_address'] = $input['consignee_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();


            $input['consignee'] = $clientData->id;
            $input['consignee_telephone'] = $input['consignee_telephone'];
            $input['consignee_address'] = $input['consignee_address'];
        } else {
            $input['consignee'] = $clientData->id;
            $input['consignee_telephone'] = $input['consignee_telephone'];
            $input['consignee_address'] = $input['consignee_address'];
        }

        $shipper_name = $input['shipper_name'];
        $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
        if (empty($clientData)) {

            $newClientData['company_name'] = $shipper_name;
            $newClientData['phone_number'] = $input['shipper_telephone'];
            $newClientData['company_address'] = $input['shipper_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();


            $input['shipper_name'] = $clientData->id;
            $input['shipper_telephone'] = $input['shipper_telephone'];
            $input['shipper_address'] = $input['shipper_address'];
        } else {
            $input['shipper_name'] = $clientData->id;
            $input['shipper_telephone'] = $input['shipper_telephone'];
            $input['shipper_address'] = $input['shipper_address'];
        }



        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['updated_by'] = auth()->user()->id;
        $input['created_on'] = gmdate('Y-m-d H:i:s');


        $model = ccpack::find($id);
        $model->fill($request->input());
        Activities::log('update', 'ccpack', $model);
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('ccpack');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $checkPermission = User::checkPermission(['delete_ccpack'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        DB::table('ccpack')->where('id', $id)->update(['deleted' => '1', 'deleted_by' => auth()->user()->id, 'deleted_on' => gmdate('Y-m-d h:i:s')]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'ccpack';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
        //return redirect('ccpack');
    }

    public function checkuniqueawbnumber()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit')
            $ccpackData = DB::table('ccpack')->where('deleted', '0')->where('awb_number', $number)->where('id', '<>', $id)->count();
        else
            $ccpackData = DB::table('ccpack')->where('deleted', '0')->where('awb_number', $number)->count();

        if ($ccpackData)
            return 1;
        else
            return 0;
    }

    public function filterbyfiletype(Request $request)
    {
        $file_type = $request->get('ccpackId');

        if ($file_type == 0) {
            $ccpackData = DB::table('ccpack')->where('deleted', '0')->get();
            return view('ccpack.ccpackallajax', ['ccpackData' => $ccpackData]);
        } else if ($file_type == 1) {
            $ccpackData = DB::table('ccpack')->where('ccpack_operation_type', 1)->where('deleted', '0')->get();
            return view('ccpack.importindexajax', ['ccpackData' => $ccpackData]);
        } else {
            $ccpackData = DB::table('ccpack')->where('ccpack_operation_type', 2)->where('deleted', '0')->get();
            return view('ccpack.exportindexajax', ['ccpackData' => $ccpackData]);
        }
    }

    public function viewdetails($id)
    {

        $model = ccpack::find($id);

        $dataExpense = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('ccpack_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('ccpack_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();

        $ccpackInvoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.ccpack_id', $id)
            ->orderBy('invoices.id', 'desc')
            ->get();

        $totalInvoiceOfHTG = 0;
        $totalInvoiceOfUSD = 0;

        foreach ($ccpackInvoices as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInvoiceOfUSD += $v->total;

            if ($v->currencyCode == 'HTG')
                $totalInvoiceOfHTG += $v->total;
        }

        if ($model->ccpack_operation_type == 1)
            $path = 'Files/Courier/CCpack/Import/' . $model->file_number;
        else
            $path = 'Files/Courier/CCpack/Export/' . $model->file_number;

        $attachedFiles = DB::table('ccpack_uploaded_files')->where('file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        /* $newArr = array();
        $files = Storage::disk('s3')->files($path);

        if ($files) {
            $fileName = explode('/', $files[0]);
            $afc = count($attachedFiles);

            for ($i = 0; $i < count($files); $i++) {
                if (count($attachedFiles) != $i) {
                    $newArr[$i][0] = $attachedFiles[$i]->file_type;
                } else {
                    $newArr[$i][0] = '';
                }
                $tempArr = explode('/', $files[$i]);

                $newArr[$i][1] = $tempArr[(count($tempArr) - 1)];
            }
        } else {
            $newArr = [];
        } */
        $fileTypes = Config::get('app.fileTypes');

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('ccpack', 'expenses.ccpack_id', '=', 'ccpack.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ccpack_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('ccpack', 'expenses.ccpack_id', '=', 'ccpack.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ccpack_id', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();

        /* Report by billing items */
        $getBillingAssociatedData = $getBillingItemData = DB::table('billing_items')
            //->select(DB::raw("CONCAT(billing_items.id,'-',costs.id) as fullcost"))
            ->select('billing_items.id as billingItemId', DB::raw('group_concat(costs.id) as costIds'))
            ->leftJoin('costs', 'costs.cost_billing_code', '=', 'billing_items.id')
            ->groupBy('billing_items.id')
            ->get();
        foreach ($getBillingAssociatedData as $k => $v) {
            $finalGetBillingAssociatedData[$getBillingAssociatedData[$k]->billingItemId] = $v;
        }
        /* Report by billing items */

        $getBillingItemData = DB::table('invoices')
            ->select(['invoice_item_details.fees_name as biliingItemId', 'invoice_item_details.fees_name_desc as biliingItemDescription', 'invoice_item_details.total_of_items as biliingItemAmount', 'currency.code as currencyCode', 'currency.code as billingCurrencyCode'])
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.ccpack_id', $id)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.ccpack_id', $id)
            ->where('expenses.deleted', '0')
            ->get();

        /* Report by billing items */
        $finalReportData = array();
        foreach ($finalGetBillingAssociatedData as $k => $v) {
            foreach ($getBillingItemData as $k1 => $v1) {
                if ($k == $v1->biliingItemId) {
                    $finalReportData[$k]['billingData'][] = $v1;
                }
            }

            foreach ($getCostItemData as $k1 => $v1) {
                if (in_array($v1->costItemId, explode(',', $v->costIds))) {
                    $finalReportData[$k]['costData'][] = $v1;
                }
            }
        }

        foreach ($finalReportData as $k => $v) {
            $countBillingData = 0;
            $countCostData = 0;
            if (isset($v['billingData']))
                $countBillingData = count($v['billingData']);
            if (isset($v['costData']))
                $countCostData = count($v['costData']);
            $maxCount = max($countBillingData, $countCostData);
            if ($maxCount == $countBillingData)
                $vG = 'billingGreater';
            else
                $vG = 'costGreater';

            if ($vG == 'costGreater') {
                $v['allData'] = $v['costData'];
                foreach ($v['costData'] as $k1 => $v1) {
                    $v['allData'][$k1]->biliingItemId = isset($v['billingData'][$k1]->biliingItemId) ? $v['billingData'][$k1]->biliingItemId : '';
                    $v['allData'][$k1]->biliingItemDescription = isset($v['billingData'][$k1]->biliingItemDescription) ? $v['billingData'][$k1]->biliingItemDescription : '';
                    $v['allData'][$k1]->biliingItemAmount = isset($v['billingData'][$k1]->biliingItemAmount) ? $v['billingData'][$k1]->biliingItemAmount : '';
                    $v['allData'][$k1]->billingCurrencyCode = isset($v['billingData'][$k1]->billingCurrencyCode) ? $v['billingData'][$k1]->billingCurrencyCode : '';
                }
                $finalReportData[$k] = $v;
            } else {
                $v['allData'] = $v['billingData'];
                foreach ($v['billingData'] as $k1 => $v1) {
                    $v['allData'][$k1]->costItemId = isset($v['costData'][$k1]->costItemId) ? $v['costData'][$k1]->costItemId : '';
                    $v['allData'][$k1]->costDescription = isset($v['costData'][$k1]->costDescription) ? $v['costData'][$k1]->costDescription : '';
                    $v['allData'][$k1]->costAmount = isset($v['costData'][$k1]->costAmount) ? $v['costData'][$k1]->costAmount : '';
                    $v['allData'][$k1]->costCurrencyCode = isset($v['costData'][$k1]->costCurrencyCode) ? $v['costData'][$k1]->costCurrencyCode : '';
                }
                $finalReportData[$k] = $v;
            }
            unset($finalReportData[$k]['costData']);
            unset($finalReportData[$k]['billingData']);
        }
        /* Report by billing items */

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'ccpack')->orderBy('id', 'desc')->get()->toArray();
        return view('ccpack.view-details', ['id' => $id, 'model' => $model, 'ccpackInvoices' => $ccpackInvoices, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'path' => $path, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'dataExpense' => $dataExpense, 'activityData' => $activityData, 'finalReportData' => $finalReportData]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionUpdateCcpack = User::checkPermission(['update_ccpack'], '', auth()->user()->id);
        $permissionDeleteCcpack = User::checkPermission(['delete_ccpack'], '', auth()->user()->id);
        $permissionCcpackAddInvoice = User::checkPermission(['add_ccpack_invoices'], '', auth()->user()->id);
        $permissionCcpackExpensesAdd = User::checkPermission(['add_ccpack_expenses'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);

        $req = $request->all();
        $ccPackType = $req['ccPackType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        /* Session::put('ccpackListingFromDate', $req['fromDate']);
        Session::put('ccpackListingToDate', $req['toDate']); */
        $fileStatus = $req['fileStatus'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ccpack.id', 'ccpack.file_number', 'master_file_number',  'c3.company_name', 'ccpack_scan_status', 'arrival_date', 'awb_number', 'c1.company_name', 'c2.company_name', '', 'no_of_pcs', 'weight', 'freight'];

        if (checkloggedinuserdata() == 'Warehouse') {
            $getWarehouseOfUser =  DB::table('users')
                ->select('warehouses')
                ->where('id', auth()->user()->id)
                ->first();
            $wh = explode(',', $getWarehouseOfUser->warehouses);
        }

        $total = ccpack::selectRaw('count(*) as total');
        //->where('deleted', '0');
        /* if (checkloggedinuserdata() == 'Warehouse')
            $total = $total->whereIn('warehouse', $wh); */
        if (!empty($fileStatus)) {
            $total = $total->where('ccpack_scan_status', $fileStatus);
        }
        if (!empty($ccPackType)) {
            $total = $total->where('ccpack_operation_type', $ccPackType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('ccpack')
            ->selectRaw('ccpack.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party');
        //->where('ccpack.deleted', '0');
        if (!empty($fileStatus)) {
            $query = $query->where('ccpack_scan_status', $fileStatus);
        }
        if (!empty($ccPackType)) {
            $query = $query->where('ccpack_operation_type', $ccPackType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        /* if (checkloggedinuserdata() == 'Warehouse')
            $query = $query->whereIn('warehouse', $wh); */
        $filteredq = DB::table('ccpack')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party');
        //->where('ccpack.deleted', '0');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('ccpack_scan_status', $fileStatus);
        }
        if (!empty($ccPackType)) {
            $filteredq = $filteredq->where('ccpack_operation_type', $ccPackType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        /* if (checkloggedinuserdata() == 'Warehouse')
            $filteredq = $filteredq->whereIn('warehouse', $wh); */



        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(arrival_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('no_of_pcs', 'like', '%' . $search . '%')
                    ->orWhere('weight', 'like', '%' . $search . '%')
                    ->orWhere('freight', 'like', '%' . $search . '%');
                //->orWhere('ccpack_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(arrival_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('no_of_pcs', 'like', '%' . $search . '%')
                    ->orWhere('weight', 'like', '%' . $search . '%')
                    ->orWhere('freight', 'like', '%' . $search . '%');
                //->orWhere('ccpack_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data1 = [];
        foreach ($query as $key => $data) {
            $dataBillingParty = app('App\Clients')->getClientData($data->billing_party);
            $consigneeData = app('App\Clients')->getClientData($data->consignee);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $shipperData = app('App\Clients')->getClientData($data->shipper_name);
            $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
            $invoiceNumbers = Expense::getCcpackInvoicesOfFile($data->id);

            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printccpackfile", [$data->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete =  route('deleteccpack', $data->id);
            $edit =  route('editccpack', $data->id);
            if ($data->deleted == '0') {
                if ($permissionUpdateCcpack) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }
                if ($permissionDeleteCcpack) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['ccpack', $data->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';
                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCcpackExpensesAdd) {
                    $action .= '<li><a href="' . route('ccpackexpensecreate', $data->id) . '">Add Expense</a></li>';
                }

                if ($permissionCcpackAddInvoice) {
                    $action .= '<li><a href="' . route('createccpackinvoices', $data->id) . '">Add Invoice</a></li>';
                }

                $action .= '<li><button id="btnAddWarehouseInFile" data-module ="Warehouse" class="btnModalPopup" value="' . route('addwarehouseinfile', [$data->id, 'ccpack']) . '">Add Warehouse</button></li>';

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['CCPack', $data->id]) . '">Close File</a></li>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $closedDetail = '';
            if ($data->file_close == 1) {
                $dataUserCloseFile = DB::table('users')->where('id', $data->close_unclose_by)->first();
                $closedDetail .= !empty($data->close_unclose_date) ? date('d-m-Y', strtotime($data->close_unclose_date)) : '-';
                $closedDetail .= ' | ';
                $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
            }

            $data1[] = [$data->id, $data->file_number, !empty($data->master_file_number) ? $data->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($data->ccpack_scan_status) ? $data->ccpack_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($data->ccpack_scan_status) ? $data->ccpack_scan_status : '-'] : '-', date('d-m-Y', strtotime($data->arrival_date)), !empty($data->awb_number) ? $data->awb_number : '-', $consignee, $shipper, $invoiceNumbers, $data->no_of_pcs, $data->weight . ' ' . 'KGS', $data->freight,  ($data->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data1
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserversideccpack()
    {
        $flag = $_POST['flag'];
        if ($flag == 'getFileData') {
            $ccpackId = $_POST['ccpackId'];
            return json_encode(ccpack::getccpackdetail($ccpackId));
        }
    }

    public function getccpackdata()
    {
        $id = $_POST['ccpackId'];
        $aAr = array();
        $dataCcpack = DB::table('ccpack')->where('id', $id)->first();

        $dataConsignee = DB::table('clients')->where('id', $dataCcpack->consignee)->first();
        $dataShipper = DB::table('clients')->where('id', $dataCcpack->shipper_name)->first();

        $aAr['consigneeName'] = !empty($dataConsignee->company_name) ? $dataConsignee->company_name : '-';
        $aAr['shipperName'] = !empty($dataShipper->company_name) ? $dataShipper->company_name : '-';
        $aAr['billing_party'] = $dataCcpack->billing_party;
        return json_encode($aAr);
    }

    public function viewccpackdetailforagent($id)
    {
        $checkPermission = User::checkPermission(['assign_billingparty_cashcredit_ccpack'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = ccpack::find($id);
        //Aeropost::where('id',$id)->update(['display_notification'=>0]);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'ccpack')->orderBy('id', 'desc')->get()->toArray();
        $attachedFiles = DB::table('ccpack_uploaded_files')->where('file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');
        return view('agent-role.ccpack.viewdetail', ['model' => $model, 'billingParty' => $billingParty, 'activityData' => $activityData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes]);
    }

    public function assignbillingparty(Request $request)
    {
        $input = $request->all();
        $model = ccpack::find($input['id']);
        $oldArrivalDate = $model->arrival_date;
        $oldStatus = $model->ccpack_scan_status;
        $oldBillingParty = $model->billing_party;
        $newBillingParty = $request->billing_party;
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['shipment_received_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['shipment_status'] = '1';
        $input['shipment_status_changed_by'] = auth()->user()->id;
        if ($input['ccpack_scan_status'] == '6') {
            $input['warehouse_status'] = '3';
            $input['shipment_delivered_date'] = date('Y-m-d');
        }

        $model->update($input);
        $inputNotes['flag_note'] = 'R';
        $inputNotes['ccpack_id'] = $input['id'];
        $inputNotes['notes'] = $input['shipment_notes_for_return'];
        $inputNotes['created_on'] = date('Y-m-d');
        $inputNotes['created_by'] = auth()->user()->id;
        VerificationInspectionNote::create($inputNotes);

        if (!empty($model)) {
            $newStatus = $model->ccpack_scan_status;
            if ($oldStatus != $newStatus) {
                if (empty($oldStatus))
                    $oldStatus = '1';
                $modelActivities = new Activities;
                $modelActivities->type = 'ccpack';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . " )";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'ccpack';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        if ($oldArrivalDate != $input['arrival_date']) {
            $modelActivities = new Activities;
            $modelActivities->type = 'ccpack';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Arrival Date has been updated to ' . date('d-m-Y', strtotime($input['arrival_date']));
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        if ($oldBillingParty != $newBillingParty) {
            $oldBillingPartyName = DB::table('clients')->where('id', $oldBillingParty)->first();
            $oldBillingPartyNameA = !empty($oldBillingPartyName) ? $oldBillingPartyName->company_name : 'N/A';
            $newBillingPartyName = DB::table('clients')->where('id', $newBillingParty)->first();
            $newBillingPartyNameA = !empty($newBillingPartyName) ? $newBillingPartyName->company_name : 'N/A';
            $modelActivities = new Activities;
            $modelActivities->type = 'ccpack';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Updated Billing Party From <b>' . $oldBillingPartyNameA . '</b> To <b>' . $newBillingPartyNameA . '</b>';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        return 'true';
    }

    public function printccpackfile($ccpackId)
    {
        $model = DB::table('ccpack')->where('id', $ccpackId)->first();
        $pdf = PDF::loadView('ccpack.printfile', ['model' => $model]);

        $pdf_file = $model->file_number . '.pdf';
        $pdf_path = 'public/ccpackFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }
}
