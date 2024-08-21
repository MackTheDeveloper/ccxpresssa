<?php

namespace App\Http\Controllers;

use App\Costs;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Vendors;
use App\Admin;
use App\Expense;
use App\Activities;
use QuickBooksOnline\API\Facades\Vendor;
use Excel;
use Auth;

class CostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_costs'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        //$items = DB::table('costs')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("costs.index");
    }

    public function listcosts(Request $request)
    {
        $permissionEdit = User::checkPermission(['update_costs'], '', auth()->user()->id);
        $permissionDelete = User::checkPermission(['delete_costs'], '', auth()->user()->id);

        $req = $request->all();
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['costs.id', 'costs.cost_name', 'costs.code', 'billing_items.billing_name', 'costs.status'];

        $total = Costs::selectRaw('count(*) as total')
            ->where('costs.deleted', '0');
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('costs')
            ->selectRaw('costs.id,costs.cost_name,billing_items.billing_name,costs.code,costs.status')
            ->leftJoin('billing_items', 'billing_items.id', '=', 'costs.cost_billing_code')
            ->where('costs.deleted', '0');

        $filteredq = DB::table('costs')
            ->leftJoin('billing_items', 'billing_items.id', '=', 'costs.cost_billing_code')
            ->where('costs.deleted', '0');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('costs.cost_name', 'like', '%' . $search . '%')
                    ->orWhere('billing_items.billing_name', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('costs.cost_name', 'like', '%' . $search . '%')
                    ->orWhere('billing_items.billing_name', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $action = '<div class="dropdown">';

            $delete =  route('deletecost', $items->id);
            $edit =  route('editcost', $items->id);

            if ($permissionEdit) {
                $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
            }

            if ($items->code != '2046/ Garantie DECSA') {
                if ($permissionDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
            }

            $action .= '</div>';

            $data[] = [$items->id, $items->cost_name, $items->code, !empty($items->billing_name) ? $items->billing_name : '-', ($items->status == 1) ? 'Active' : 'Inactive', $action];
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
        $checkPermission = User::checkPermission(['add_costs'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Costs;
        $findCode = DB::table('costs')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->first();
        if (empty($findCode))
            $bilCode = '501';
        else
            $bilCode = ((int) $findCode->cost_billing_code + 1);

        $dataBillingItems = DB::table('billing_items')
            ->select('id', DB::raw("item_code,CONCAT(item_code,' - ',billing_name) as fullcost"))
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');


        return view('costs.form', ['model' => $model, 'dataBillingItems' => $dataBillingItems]);
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
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = Costs::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        //pre(isset($_SESSION['sessionAccessToken']));

        //Store cost item to QB

        /*if(isset($_SESSION['sessionAccessToken'])){
            //pre('test');
            $modeladmin = new Admin();
            $modeladmin->qbApiCall('cost',$model);
        }*/

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '0';
            $fData['flagModule'] = 'cost';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        return redirect('costs');
    }

    public function storenewitem(Request $request)
    {
        session_start();
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = Costs::create($input);

        // Store cost item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modeladmin = new Admin();
            $modeladmin->qbApiCall('cost',$model);
        }*/

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '0';
            $fData['flagModule'] = 'cost';
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
     * @param  \App\Costs  $costs
     * @return \Illuminate\Http\Response
     */
    public function show(Costs $costs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Costs  $costs
     * @return \Illuminate\Http\Response
     */
    public function edit(Costs $costs, $id)
    {
        $checkPermission = User::checkPermission(['update_costs'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('costs')->where('id', $id)->first();
        $dataBillingItems = DB::table('billing_items')
            ->select('id', DB::raw("item_code,CONCAT(item_code,' - ',billing_name) as fullcost"))
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');
        return view("costs.form", ['model' => $model, 'dataBillingItems' => $dataBillingItems]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Costs  $costs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Costs $costs, $id)
    {
        session_start();
        $model = Costs::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');

        // Update cost item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modeladmin = new Admin();
            $modeladmin->qbApiCall('updateCost',$model);
        }*/


        if (isset($_SESSION['sessionAccessToken'])) {
            //pre('test');
            $fData['id'] = $model->id;
            $fData['module'] = '0';
            $fData['flagModule'] = 'updateCost';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        return redirect('costs');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Costs  $costs
     * @return \Illuminate\Http\Response
     */
    public function destroy(Costs $costs, $id)
    {
        session_start();
        $model = Costs::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);

        // Delete cost item to QB

        /*if(isset($_SESSION['sessionAccessToken'])){
            $modeladmin = new Admin();
            $modeladmin->qbApiCall('deleteCost',$id);
        }*/

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '0';
            $fData['flagModule'] = 'deleteCost';
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

    public function getcostdata()
    {
        $id = $_POST['costId'];
        $aAr = array();
        $dataBilling = DB::table('costs')->where('id', $id)->first();
        $aAr['costName'] = $dataBilling->cost_name;
        return json_encode($aAr);
    }

    public function getcostdropdowndataaftersubmit()
    {
        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get();
        $dt = '<option selected="selected" value="">Select ...</option>';
        foreach ($dataCost as $key => $value) {
            $dt .=  '<option value="' . $value->id . '">' . $value->fullcost . '</option>';
        }
        return $dt;
    }

    public function checkunique(Request $request)
    {
        $id = $request->get('id');
        $value = $request->get('value');
        $flag = $_POST['flag'];
        if (!empty('id')) {
            if ($flag == 'costName')
                $data = DB::table('costs')->where('cost_name', $value)->where('id', '<>', $id)->where('deleted', '0')->count();
            else
                $data = DB::table('costs')->where('code', $value)->where('id', '<>', $id)->where('deleted', '0')->count();
        } else {
            if ($flag == 'costName')
                $data = DB::table('costs')->where('cost_name', $value)->where('deleted', '0')->count();
            else
                $data = DB::table('costs')->where('code', $value)->where('deleted', '0')->count();
        }

        if ($data) {
            return 1;
        } else {
            return 0;
        }
    }

    public function accountpayablereport()
    {
        $vendors = DB::table('vendors')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        $vendors = json_decode($vendors, 1);
        return view("reports.accountpayablereport", ['vendors' => $vendors]);
    }

    public function listaccountpayablereport(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $modules = $req['modules'];
        $vendors = $req['vendors'];
        $duration = $req['duration'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['id', 'id', 'company_name'];

        $getVendorWithUnpaidExpense = DB::table('expenses')
            ->select(DB::raw('vendors.id'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->where('vendors.deleted', 0)
            ->where('expenses.deleted', 0)
            ->where('expenses.expense_type', 2)
            ->where('expense_details.amount', '>', 0)
            ->distinct('expense_details.paid_to')
            ->orderBy('vendors.id', 'desc');


        if ($modules == 'Cargo') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ccpack_master_id');
        }

        $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->pluck('vendors.id')->toArray();

        //pre($getVendorWithUnpaidExpense);

        $total = Vendors::selectRaw('count(*) as total')
            ->where('deleted', 0)
            ->whereIn('id', $getVendorWithUnpaidExpense);
        if (!empty($vendors)) {
            $total = $total->whereIn('id', $vendors);
        }

        $query = DB::table('vendors')
            ->select(DB::raw('id,company_name'))
            ->whereIn('id', $getVendorWithUnpaidExpense)
            ->where('deleted', 0);
        if (!empty($vendors)) {
            $query = $query->whereIn('id', $vendors);
        }

        $filteredq = DB::table('vendors')
            ->whereIn('id', $getVendorWithUnpaidExpense)
            ->where('deleted', 0);
        if (!empty($vendors)) {
            $filteredq = $filteredq->whereIn('id', $vendors);
        }

        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('company_name', 'like', '%' . $search . '%');
            });

            $filteredq->where(function ($query2) use ($search) {
                $query2->where('company_name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        $data = [];
        foreach ($query as $key => $value) {
            $getTotlaPendingExpenses = Expense::getTotlaPendingExpenses($value->id, $fromDate, $toDate, $modules, $duration);

            $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $value->id . '" value="' . $value->id . '" />';

            $data[] = [$checkBoxes, $value->id, '', $value->company_name, '', '', '', '', '', number_format($getTotlaPendingExpenses, 2)];
        }

        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function getaccountpayablereportdata(Request $request)
    {
        $vendorId = $_REQUEST['vendorId'];
        $duration = $_REQUEST['duration'];
        $rowId = $_REQUEST['rowId'];
        $modules = $_REQUEST['modules'];
        $fromDate = !empty($_REQUEST['fromDate']) ? date('Y-m-d', strtotime($_REQUEST['fromDate'])) : '';
        $toDate = !empty($_REQUEST['toDate']) ? date('Y-m-d', strtotime($_REQUEST['toDate'])) : '';

        $fileOfExpensesUnpaid = Expense::getaccountpayablereportdata($vendorId, $fromDate, $toDate, $modules, $duration);

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        return view('reports.getaccountpayablereportdata', ['vendorId' => $vendorId, 'rowId' => $rowId, 'modules' => $modules, 'fromDate' => $fromDate, 'toDate' => $toDate, 'duration' => $duration, 'fileOfExpensesUnpaid' => $fileOfExpensesUnpaid, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate]);
    }

    public function exportaccountpayablereport($fromDate = null, $toDate = null, $modules = null, $vendors = null, $duration = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';
        $vendors = !empty($vendors) ? explode(',', $vendors) : '';
        $duration = !empty($duration) ? $duration : '';

        if (empty($vendors)) {
            $getVendorWithUnpaidExpense = DB::table('expenses')
                ->select(DB::raw('vendors.id'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
                ->where('expenses.expense_request', '!=', 'Disbursement done')
                ->where('vendors.deleted', 0)
                ->where('expenses.deleted', 0)
                ->where('expenses.expense_type', 2)
                ->where('expense_details.amount', '>', 0)
                ->distinct('expense_details.paid_to')
                ->orderBy('vendors.id', 'desc');


            if ($modules == 'Cargo') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.cargo_id');
            }
            if ($modules == 'House File') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.house_file_id');
            }
            if ($modules == 'UPS') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ups_details_id');
            }
            if ($modules == 'upsMaster') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ups_master_id');
            }
            if ($modules == 'Aeropost') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.aeropost_id');
            }
            if ($modules == 'aeropostMaster') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.aeropost_master_id');
            }
            if ($modules == 'CCPack') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ccpack_id');
            }
            if ($modules == 'ccpackMaster') {
                $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ccpack_master_id');
            }

            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->pluck('vendors.id')->toArray();
            $vendors = Vendors::select('id')->whereIn('id', $getVendorWithUnpaidExpense)->pluck('id')->toArray();
        }

        $getTotlaPendingExpenses = Expense::getTotlaPendingExpensesForExport($vendors, $fromDate, $toDate, $modules, $duration);

        $data[] = array('Summary', 'You need to pay ' . count($vendors) . ' vendors total amount $' . $getTotlaPendingExpenses);
        $vendorData = DB::table('vendors')->select('id', 'company_name')->whereIn('id', $vendors)->orderBy('id', 'desc')->get();
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();
        $exchangeRateOfUsdToHTH = $exchangeRateOfUsdToHTH->exchangeRate;
        foreach ($vendorData as $kVendor => $vVendor) {
            $getTotlaPendingExpensesByVendor = Expense::getTotlaPendingExpenses($vVendor->id, $fromDate, $toDate, $modules, $duration);
            $data[] = array($vVendor->company_name, '$' . $getTotlaPendingExpensesByVendor);
            $data[] = array('File No', 'Cost Item', 'Cost Amount', 'Conversion', 'Voucher No', 'Billing Item', 'Billing Amount', 'Conversion', 'Invoice No', 'Exc Rate', 'P/L');

            $fileOfExpensesUnpaid = Expense::getaccountpayablereportdata($vVendor->id, $fromDate, $toDate, $modules, $duration);
            $invoicesum = 0;
            $expensesum = 0;
            foreach ($fileOfExpensesUnpaid as $kd1 => $vv1) {
                $finalReportData = Expense::getFinalReportData($vv1->moduleId, $modules, $vVendor->id, $fromDate, $toDate, $duration);
                //pre($finalReportData);
                $invoicesumCurrentItem = 0;
                $expensesumCurrentItem = 0;
                $checkFileNumberArray = array();
                foreach ($finalReportData as $k => $v) {
                    if (isset($v['allData'])) {
                        foreach ($v['allData'] as $k => $v1) {
                            if (empty($v1->costItemId)) continue;
                            $invoicesumCurrentItemInd = 0;
                            $expensesumCurrentItemInd = 0;

                            if ($v1->costCurrencyCode == 'HTG') {
                                if (!empty($v1->costAmount)) {
                                    $costConversion = 'USD' . ' ' . number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2);

                                    $expensesumCurrentItem += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
                                    $expensesum += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
                                    $expensesumCurrentItemInd += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
                                } else {
                                    $costConversion = '0.00';
                                }
                            } else {
                                if (!empty($v1->costAmount)) {
                                    $costConversion = 'USD' . ' ' . number_format($v1->costAmount, 2);
                                    $expensesumCurrentItem += str_replace(',', '', number_format($v1->costAmount, 2));
                                    $expensesum += str_replace(',', '', number_format($v1->costAmount, 2));
                                    $expensesumCurrentItemInd += str_replace(',', '', number_format($v1->costAmount, 2));
                                } else {
                                    $costConversion = '0.00';
                                }
                            }

                            if ($v1->billingCurrencyCode == 'HTG') {
                                if (!empty($v1->biliingItemAmount)) {
                                    $billingConversion = 'USD' . ' ' . number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2);

                                    $invoicesumCurrentItem += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
                                    $invoicesum += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
                                    $invoicesumCurrentItemInd += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
                                } else {
                                    $billingConversion = '0.00';
                                }
                            } else {
                                if (!empty($v1->biliingItemAmount)) {
                                    $billingConversion = 'USD' . ' ' . number_format($v1->biliingItemAmount, 2);
                                    $invoicesumCurrentItem += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
                                    $invoicesum += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
                                    $invoicesumCurrentItemInd += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
                                } else {
                                    $billingConversion = '0.00';
                                }
                            }

                            $data[] = array(!in_array($vv1->fileNumber, $checkFileNumberArray) ? $vv1->fileNumber : '', !empty($v1->costDescription) ? $v1->costDescription : '', !empty($v1->costAmount) ? $v1->costCurrencyCode . ' ' . $v1->costAmount : '', $costConversion, $v1->voucherNumber, !empty($v1->biliingItemDescription) ? $v1->biliingItemDescription : '', !empty($v1->biliingItemAmount) ? $v1->billingCurrencyCode . ' ' . number_format($v1->biliingItemAmount, 2) : '', $billingConversion, $v1->invoiceNumber, $exchangeRateOfUsdToHTH, number_format($invoicesumCurrentItemInd - $expensesumCurrentItemInd, 2));

                            $checkFileNumberArray[] = $vv1->fileNumber;
                        }
                    }
                }
                $data[] = array('Total', '', '', 'USD' . ' ' . number_format($expensesumCurrentItem, 2), '', '', '', 'USD' . ' ' . number_format($invoicesumCurrentItem, 2), '', '', 'USD' . ' ' . number_format($invoicesumCurrentItem - $expensesumCurrentItem, 2));
            }
            $data[] = array('Total', '', '', 'USD' . ' ' . number_format($expensesum, 2), '', '', '', 'USD' . ' ' . number_format($invoicesum, 2), '', '', 'USD' . ' ' . number_format($invoicesum - $expensesum, 2));

            $data[] = array('', '', '', '', '', '', '', '', '', '', '');
        }


        $excelObj = Excel::create('Account Payable', function ($excel) use ($data) {
            $excel->setTitle('Account Payable');
            $excel->sheet('Account Payable', function ($sheet) use ($data) {
                $sheet->cells('A1:B1', function ($cells) {
                    $cells->setBackground('#CCCCCC');
                    $cells->setAlignment('center');
                });
                $sheet->fromArray($data, null, 'A1', false, false);
            });
        });
        $excelObj->download('xlsx');
    }

    public function approveexpenseinaccountpayablereport()
    {
        $ids = explode(',', $_POST['ids']);
        $modules = $_POST['moduleForApproval'];
        $getVendorWithUnpaidExpense = DB::table('expenses')
            ->select(DB::raw('expenses.expense_id'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->whereIn('expense_details.paid_to', $ids);
        if ($modules == 'Cargo') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->whereNotNull('expenses.ccpack_master_id');
        }
        $getVendorWithUnpaidExpense = $getVendorWithUnpaidExpense->pluck('expense_id')->toArray();
        DB::table('expenses')->whereIn('expense_id', $getVendorWithUnpaidExpense)->update([
            'expense_request' => 'Approved',
        ]);
        Session::flash('flash_message', 'Record has been change to Approved.');
        return redirect()->route('accountpayablereport');
    }

    public function apdisbursement($module = null)
    {
        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);
        return view('common.apdisbursement', ['cashCredit' => $cashCredit]);
    }

    public function apdisbursementsubmit(Request $request)
    {
        session_start();
        $input = $request->all();
        //pre($input, 1);
        $selectedIds = explode(',', $input['selectedIds']);
        //pre($selectedIds);
        foreach ($selectedIds as $k => $v) {
            $exploadExpenseAndDetail = explode(':', $v);
            $expenseId = $exploadExpenseAndDetail[0];
            $expenseDetailId = $exploadExpenseAndDetail[1];

            $model = Expense::find($expenseId);
            $input['updated_by'] = Auth::user()->id;

            $input['disbursed_by'] = Auth::user()->id;
            $input['disbursed_datetime'] = date('Y-m-d H:i:s');
            $totalExpenses = str_replace(',', '', Expense::getExpenseTotal($expenseId));
            //$totalExpenses = DB::table('expense_details')->where('id', $expenseDetailId)->first();

            $getCashCreditData = DB::table('cashcredit')->where('id', $input['cash_credit_account'])->first();
            $finalAmt = $getCashCreditData->available_balance - $totalExpenses;
            DB::table('cashcredit')->where('id', $input['cash_credit_account'])->update(['available_balance' => $finalAmt, 'qb_sync' => 0]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCredit';
            $modelActivities->related_id = $input['cash_credit_account'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $totalExpenses . '-' . $input['expense_request_status_note'];
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            $fData['flagModule'] = 'expenses';
            if (isset($_SESSION['sessionAccessToken'])) {
                //pre('test');
                $fData['id'] = $model->expense_id;
                $fData['module'] = '5';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }

            $input['display_notification_agent'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $input['expense_request'] = 'Disbursement done';
            $model->update($input);
        }
        Session::flash('flash_message_disbursement', 'Expense has been disbursed successfully');
        return redirect()->route('accountpayablereport');
    }
}
