<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Common;
use App\Clients;
use App\ExpenseDetails;
use App\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Session;
use PDF;
use App\User;
use App\Admin;
use Illuminate\Support\Facades\Storage;
use App\Vendors;
use App\CashCredit;
use App\Currency;
use App\Ups;
use App\UpsMaster;
use URL;
use Config;

class UpsMasterExpensesController extends Controller
{
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_ups_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view("ups-master-expenses.index");
    }

    public function create($upsMasterId = null, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_ups_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Expense;

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);


        $cashCredit = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted', 0)->where('status', 1)->pluck('cashcreditData', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);


        $dataPaidTo = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo, 1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->get()->pluck('billing_name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');


        //$dataAwbNos = DB::table('ups_details')->where('deleted',0)->whereNotNull('awb_number')->get()->pluck('awb_number','id');
        $dataAwbNos = DB::table('ups_master')->where('deleted', 0)->get()->pluck('tracking_number', 'id');

        $dataFileNumber = DB::table('ups_master')
            ->whereNull('file_close')
            ->whereNotNull('billing_party')
            ->where('deleted', 0)
            ->get()->pluck('file_number', 'id');

        $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
        if (empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        //$allUsers = DB::table('users')->select(['id','first_name'])->where('deleted',0)->where('status',1)->pluck('first_name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);
        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');
        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');
        return view('ups-master-expenses._form', ['model' => $model, 'billingParty' => $billingParty, 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'voucherNo' => $voucherNo, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'upsMasterId' => $upsMasterId, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'currency' => $currency, 'cashier' => $cashier, 'adminManagersRole' => $adminManagersRole, 'flagFromWhere' => $flagFromWhere]);
    }

    public function store(Request $request)
    {
        //session_start();
        $input = $request->all();
        $fileData = DB::table('ups_master')->where('id', $input['file_number'])->where('deleted', 0)->first();
        //$dataExpense = DB::table('expenses')->where('voucher_number',$input['voucher_number'])->first();
        $dataExpense = array();
        if (!empty($dataExpense)) {
            //$fData['flagModule'] = 'updateExpense';
            $model = ExpenseDetails::where('expense_id', $dataExpense->expense_id)->delete();
            $model = Expense::find($dataExpense->expense_id);
            $model->fill($request->input());
            Activities::log('update', 'upsMasterExpense', $model);
            $input = $request->all();
            $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
            if ($dataExpense->cashier_id != $input['cashier_id']) {
                $input['display_notification_cashier_for_ups_master'] = '1';
                $input['notification_date_time'] = date('Y-m-d H:i:s');
            }
            $model->update($input);

            $countexp = $_POST['count_expense'];
            $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);

            for ($i = 0; $i < $countexp; $i++) {
                $modelExp = new ExpenseDetails();
                $modelExp->expense_id = $model->expense_id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->expense_type = $input['expense_type'][$i];
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->paid_to = $input['paid_to'][0];
                $modelExp->save();
            }
        } else {
            $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
            if (empty($getLastExpense))
                $input['voucher_number'] = '1001';
            else
                $input['voucher_number'] = $getLastExpense->voucher_number + 1;

            //$fData['flagModule'] = 'expenses';
            $input['created_on'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = Auth::user()->id;
            $input['request_by'] = Auth::user()->id;

            if (checkloggedinuserdata() == 'Agent') {
                $input['request_by_role'] = '12';
                $input['expense_request'] = 'Requested';
                $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
                $input['display_notification_admin'] = '1';
            } else {
                $input['approved_by'] = Auth::user()->id;
                $input['admin_managers'] = Auth::user()->id;
            }
            $input['display_notification_cashier_for_ups_master'] = '1';
            $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $input['admin_managers'] = Auth::user()->id;
            $model = Expense::create($input);

            $data['id'] = $model->expense_id;
            $dataCargo = DB::table('ups_master')->where('id', $_POST['ups_master_id'])->first();
            $data['flagExpense'] = $dataCargo->file_number;
            Activities::log('create', 'upsMasterExpense', (object) $data);


            $countexp = $_POST['count_expense'];
            $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);

            //pre($model);
            for ($i = 0; $i < $countexp; $i++) {
                $modelExp = new ExpenseDetails();
                $modelExp->expense_id = $model->expense_id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->expense_type = $input['expense_type'][$i];
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->paid_to = $input['paid_to'][0];
                $modelExp->save();
            }

            // Store expense activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'upsMaster';
            $modelActivities->related_id = $model->ups_master_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Expense #' . $model->voucher_number . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {

                $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();

                $dataCargo = DB::table('ups_master')->where('id', $model->ups_master_id)->first();
                $pdf = PDF::loadView('ups-master-expenses.print-ups-master-expense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
                $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
                $pdf_path = 'public/upsMasterExpensePdf/' . $pdf_file;
                $pdf->save($pdf_path);
                $s3path = 'Files/Courier/Ups-Master/';
                if ($fileData->ups_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Expenses/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Expenses/';
                }

                $filecontent = file_get_contents($pdf_path);
                $success = Storage::disk('s3')->put($s3path . $fileData->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');
                Session::flash('flash_message', 'Expense has been created successfully');
                return url('/') . '/' . $pdf_path;
            }

            //Add Expense to QB
        }

        /* if(isset($_SESSION['sessionAccessToken']))
        {   
            // pre($model);
            $fData['id'] = $model->expense_id;
            $fData['module'] = '4';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            // pre($fData);
            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model='.$newModel);
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        } */
        Session::flash('flash_message', 'Expense has been created successfully');

        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('expenses',$model);
        }*/
        // pre("TSEt");
    }



    public function edit(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_ups_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model =  Expense::find($id);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);

        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);


        $dataPaidTo = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo, 1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->get()->pluck('billing_name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');



        $dataAwbNos = DB::table('ups_master')->where('deleted', 0)->whereNotNull('tracking_number')->get()->pluck('tracking_number', 'id');

        $dataFileNumber = DB::table('ups_master')->whereNotNull('billing_party')->whereNull('file_close')->where('deleted', 0)->get()->pluck('file_number', 'id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        return view('ups-master-expenses._formedit', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'currency' => $currency, 'cashier' => $cashier, 'flagFromWhere' => $flagFromWhere]);
    }

    public function editagentupsmasterexpense(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_ups_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        Expense::where('expense_id', $id)->update(['display_notification_admin' => 0]);
        $model =  Expense::find($id);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);

        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);


        $dataPaidTo = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo, 1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->get()->pluck('billing_name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');



        $dataAwbNos = DB::table('ups_master')->where('deleted', 0)->whereNotNull('tracking_number')->get()->pluck('tracking_number', 'id');

        $dataFileNumber = DB::table('ups_master')->whereNotNull('billing_party')->whereNull('file_close')->where('deleted', 0)->get()->pluck('tracking_number', 'id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');

        if (empty($model->admin_manager_role))
            $model->admin_manager_role = 13;
        $adminManagersUsers = DB::table('users')->select(['id', 'name'])->where('department', $model->admin_manager_role)->where('deleted', 0)->orderBy('id', 'desc')->pluck('name', 'id');

        $model->admin_managers = explode(',', $model->admin_managers);

        $expenseStatus = array();
        $expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        $expenseStatus['Requested'] = 'Requested';
        $expenseStatus['Disbursement done'] = 'Disbursement done';

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        return view('ups-master-expenses._formeditexpenserequest', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'currency' => $currency, 'adminManagersRole' => $adminManagersRole, 'adminManagersUsers' => $adminManagersUsers, 'expenseStatus' => $expenseStatus, 'cashier' => $cashier, 'flagFromWhere' => $flagFromWhere]);
    }

    public function update(Request $request, $id)
    {

        //session_start();
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $model->fill($request->input());
        Activities::log('update', 'upsMasterExpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier_for_ups_master'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }
        $model->update($input);

        $countexp = $_POST['count_expense'];
        $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
        $input['amount'] = array_values($input['expenseDetails']['amount']);
        $input['description'] = array_values($input['expenseDetails']['description']);
        $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);

        for ($i = 0; $i < $countexp; $i++) {
            $modelExp = new ExpenseDetails();
            $modelExp->expense_id = $model->expense_id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->expense_type = $input['expense_type'][$i];
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->paid_to = $input['paid_to'][0];
            $modelExp->save();
        }

        $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataCargo = DB::table('ups_master')->where('id', $model->ups_master_id)->first();
        $pdf = PDF::loadView('ups-master-expenses.print-ups-master-expense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/upsMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Ups-Master/';
        if ($dataCargo->ups_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

        // Update expense to QB
        // if(isset($_SESSION['sessionAccessToken'])){
        //     $modelAdmin = new Admin;
        //     $modelAdmin->qbApiCall('updateExpense',$model);
        // } 

        /* if(isset($_SESSION['sessionAccessToken']))
        {

            $fData['id'] = $model->expense_id;
            $fData['flagModule'] = 'updateExpense';
            $fData['module'] = '4';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            
            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model='.$newModel);

            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        } */


        Session::flash('flash_message', 'Record has been updated successfully');
    }

    public function updateagentupsmasterexpense(Request $request, $id)
    {
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $oldExpenseStatus = $model->expense_request;
        $model->fill($request->input());
        Activities::log('update', 'upsMasterExpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier_for_ups_master'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }
        
        $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
        $input['updated_by'] = auth()->user()->id;

        if (checkloggedinuserdata() == 'Other' && isset($input['expense_request']) && !empty($input['expense_request'])) {
            if ($oldExpenseStatus != $input['expense_request']) {
                $input['display_notification_cashier_for_ups_master'] = '1';
                $input['notification_date_time'] = date('Y-m-d H:i:s');
                if ($input['expense_request'] == 'Approved')
                    $input['approved_by'] = auth()->user()->id;
                else
                    $input['approved_by'] = null;
            }
        }
        $model->update($input);

        $countexp = $_POST['count_expense'];
        $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
        $input['amount'] = array_values($input['expenseDetails']['amount']);
        $input['description'] = array_values($input['expenseDetails']['description']);
        $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);


        for ($i = 0; $i < $countexp; $i++) {
            $modelExp = new ExpenseDetails();
            $modelExp->expense_id = $model->expense_id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->expense_type = $input['expense_type'][$i];
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->paid_to = $input['paid_to'][0];
            $modelExp->save();
        }

        $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataCargo = DB::table('ups_master')->where('id', $model->ups_master_id)->first();
        $pdf = PDF::loadView('ups-master-expenses.print-ups-master-expense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/upsMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Ups-Master/';
        if ($dataCargo->ups_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');
        Session::flash('flash_message', 'Record has been updated successfully');
    }

    public function generateupsvoucheronsavenext()
    {
        $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
        if (empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        return $voucherNo;
    }

    public function print($expenseId = null, $upsMasterId =  null, $flag = null)
    {
        if ($flag == 'fromNotification')
            Expense::where('expense_id', $expenseId)->update(['display_notification_agent' => 0]);

        //$myfile = fopen("testwrite.txt", "a"); 
        //fwrite($myfile, '--ooo--'); 
        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $dataCargo = DB::table('ups_master')->where('id', $upsMasterId)->first();
        $pdf = PDF::loadView('ups-master-expenses.print-ups-master-expense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $expenseId . '_expense.pdf';
        $pdf_path = 'public/upsMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Ups-Master/';
        if ($dataCargo->ups_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $cargoExpenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function printall($flag = null)
    {
        $cargoExpenseData = DB::table('expenses')
            ->select(DB::raw('expenses.*,ups_master.file_number'))
            ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
            ->where('expenses.deleted', '0')
            ->whereNotNull('expenses.ups_master_id');


        if ($flag != 'all') {
            $cargoExpenseData = $cargoExpenseData->where('ups_master_id', $flag)->get()->toArray();
            $cargoData = DB::table('ups_master')->where('id', $flag)->first();
        } else {
            $cargoExpenseData = $cargoExpenseData->get()->toArray();
            $cargoData = array();
        }



        $query1 = array();
        foreach ($cargoExpenseData as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->ups_details_id;
        }

        array_multisort((array) $query1, SORT_DESC, $cargoExpenseData);

        $pdf = PDF::loadView('expenses.printallcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'flag' => $flag, 'cargoData' => $cargoData]);
        $pdf_file = 'printallexpense.pdf';
        $pdf_path = 'public/upsMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Ups-Master/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'AllExpense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function view($id)
    {
        $checkPermission = User::checkPermission(['viewdetails_ups_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'upsMasterExpense')->orderBy('id', 'desc')->get()->toArray();
        $model = Expense::find($id);
        return view("ups-master-expenses.view", ['model' => $model, 'activityData' => $activityData]);
    }

    public function checkCurrency(Request $request)
    {

        $account = $request->get('account');
        if (empty($account))
            return 0;

        $vendor = $request->get('vendor');
        $accountData = CashCredit::find($account);
        $vendorData = Vendors::find($vendor);
        $currencyData = Currency::getData($vendorData->currency);
        $vendoreCurrency = $currencyData->code;
        $accountCurrency = $accountData->currency_code;
        if ($vendoreCurrency == $accountCurrency) {
            return 0;
        } else {
            return 1;
        }
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCourierExpensesEdit = User::checkPermission(['update_ups_master_expenses'], '', auth()->user()->id);
        $permissionCourierExpensesDelete = User::checkPermission(['delete_ups_master_expenses'], '', auth()->user()->id);

        $req = $request->all();

        $status = $req['status'];
        $expenseType = $req['expenseType'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['', 'expenses.expense_id', 'expenses.ups_master_id', 'expenses.expense_id', 'exp_date', 'voucher_number', 'ups_master.file_number', 'bl_awb', 'currency.code', '', '', 'shipper', 'consignee', '', 'expense_request', 'expense_type'];

        $total = Expense::selectRaw('count(*) as total')
            //->where('expenses.deleted', '0')
            ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
            ->whereNotNull('expenses.ups_master_id');
        if (checkloggedinuserdata() == 'Agent') {
            $total = $total->where('request_by', Auth::user()->id);
        }
        if (!empty($status)) {
            $total = $total->where('expense_request', $status);
        }
        if (!empty($expenseType)) {
            $total = $total->where('expense_type', $expenseType);
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('expenses')
            ->selectRaw('expenses.*')
            ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.ups_master_id');
        //->where('expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent') {
            $query = $query->where('request_by', Auth::user()->id);
        }
        if (!empty($status)) {
            $query = $query->where('expense_request', $status);
        }
        if (!empty($expenseType)) {
            $query = $query->where('expense_type', $expenseType);
        }

        $filteredq = DB::table('expenses')
            ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.ups_master_id');
        //->where('expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent') {
            $filteredq = $filteredq->where('request_by', Auth::user()->id);
        }
        if (!empty($status)) {
            $filteredq = $filteredq->where('expense_request', $status);
        }
        if (!empty($expenseType)) {
            $filteredq = $filteredq->where('expense_type', $expenseType);
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get()->toArray();

        /* $query1 = array();
        foreach ($query as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->ups_details_id;
        }
        array_multisort((array)$query1, SORT_DESC,$query); */

        $data1 = [];
        foreach ($query as $key => $items) {

            $dataCargo = UpsMaster::getMasterUpsData($items->ups_master_id);
            if (!empty($dataCargo)) {

                $invoiceOfFile = Expense::getUpsMasterInvoicesOfFile($items->ups_master_id);
                $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
                $totlaExpense = Expense::getExpenseTotal($items->expense_id);
                //$dataCurrency = Currency::getData($items->currency); 
                $dataCurrency = Vendors::getDataFromPaidTo($items->expense_id);
                $dataClientUsingModuleId = Common::getClientDataUsingModuleId('upsMaster', $items->ups_master_id);

                $action = '<div class="dropdown">';

                $delete =  route('deleteexpensevoucher', $items->expense_id);
                if ($items->request_by_role == 12 || $items->request_by_role == 10)
                    $edit =  route('editagentupsmasterexpense', [$items->expense_id, 'flagFromExpenseListing']);
                else
                    $edit =  route('editupsmasterexpense', $items->expense_id);

                $action .= '<a title="Click here to print"  target="_blank" href="' . route('printsingleupsmasterexpense', [$items->expense_id, $items->ups_master_id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

                if ($items->deleted == '0' && checkloggedinuserdata() != 'Cashier') {
                    if ($permissionCourierExpensesEdit && $dataCargo->file_close != 1) {
                        $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                    }
                    if ($permissionCourierExpensesDelete) {
                        $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                    }
                }
                $action .= '</div>';

                if ($items->expense_request == 'Requested') {
                    $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $items->expense_id . '" value="' . $items->expense_id . '" />';
                } else {
                    $checkBoxes = '';
                }

                $data1[] = [$checkBoxes, $items->expense_id, $items->ups_master_id, '', date('d-m-Y', strtotime($items->exp_date)), $items->voucher_number, $items->expense_type == 1 ? 'Cash' : 'Credit', $dataCargo->file_number, $items->bl_awb, !empty($dataCurrency->code) ? $dataCurrency->code : "-", $totlaExpense, !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-', $dataClientUsingModuleId['shipperName'], $dataClientUsingModuleId['consigneeName'], $invoiceOfFile, $items->expense_request, $action];
            }
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data1
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserverside()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkExpense') {
            $expenseId = $_POST['expenseId'];
            return ExpenseDetails::checkExpense($expenseId);
        }
        if ($flag == 'getExpenseData') {
            $expenseId = $_POST['expenseId'];
            return json_encode(Expense::getExpenseData($expenseId));
        }
    }
}
