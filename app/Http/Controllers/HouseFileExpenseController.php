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
use App\HawbFiles;
use App\CashCredit;
use App\Currency;
use App\Vendors;
use Illuminate\Support\Facades\Storage;
use Config;

class HouseFileExpenseController extends Controller
{
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_house_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view("housefile-expenses.index");
    }

    public function create($houseId = null, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_cargo_house_expenses'], '', auth()->user()->id);
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


        $dataAwbImport = DB::table('hawb_files')->where('deleted', 0)->whereNotNull('hawb_hbl_no')->get()->pluck('hawb_hbl_no', 'id')->toArray();
        $dataAwbExport = DB::table('hawb_files')->where('deleted', 0)->whereNotNull('export_hawb_hbl_no')->get()->pluck('export_hawb_hbl_no', 'id')->toArray();;
        /*$dataAwbNo[] = $dataAwbImport;
        $dataAwbNo[] = $dataAwbExport;*/
        $dataAwbNos = $dataAwbImport + $dataAwbExport;


        $dataFileNumber = DB::table('hawb_files')->where('deleted', 0)
            ->where(function ($query) {
                $query->where('file_number', '!=', '')
                    ->orWhereNull('file_number');
            })
            ->whereNull('file_close')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
        if (empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;


        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');



        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $cashier = DB::table('users')->select(['id', 'name'])->where('department', '11')->where('deleted', 0)->orderBy('id', 'desc')->pluck('name', 'id');

        return view('housefile-expenses._form', ['model' => $model, 'billingParty' => $billingParty, 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'voucherNo' => $voucherNo, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'currency' => $currency, 'cashier' => $cashier, 'houseId' => $houseId, 'flagFromWhere' => $flagFromWhere]);
    }

    public function store(Request $request)
    {
        session_start();
        $input = $request->all();
        $fileData = DB::table('hawb_files')->where('id', $input['file_number'])->where('deleted', 0)->first();
        //pre($input);
        //$dataExpense = DB::table('expenses')->where('voucher_number',$input['voucher_number'])->first();
        $dataExpense = array();
        if (!empty($dataExpense)) {
            $fData['flagModule'] = 'updateExpense';
            $model = ExpenseDetails::where('expense_id', $dataExpense->expense_id)->delete();
            $model = Expense::find($dataExpense->expense_id);
            $model->fill($request->input());
            Activities::log('update', 'houseFileExpense', $model);
            $input = $request->all();


            $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

            if ($dataExpense->cashier_id != $input['cashier_id']) {
                $input['display_notification_cashier_for_house_file_expense'] = '1';
                $input['notification_date_time'] = date('Y-m-d H:i:s');
            }

            //pre($model);
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

            if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {
                $cargoExpenseData = DB::table('expenses')->where('expense_id', $dataExpense->expense_id)->get();

                $dataCargo = DB::table('hawb_files')->where('id', $dataExpense->house_file_id)->first();
                $pdf = PDF::loadView('housefile-expenses.print', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
                $pdf_file = $dataCargo->file_number . '_' . $dataExpense->expense_id . '_expense.pdf';
                $pdf_path = 'public/houseFileExpensesPdf/' . $pdf_file;
                $pdf->save($pdf_path);
                $s3path = 'Files/Cargo/';
                if ($fileData->cargo_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Expenses/';
                } else if ($fileData->cargo_operation_type == 2) {
                    $s3path .= 'Export/' . $fileData->file_number . '/Expenses/';
                } else {
                    $s3path .= 'Local/' . $fileData->file_number . '/Expenses/';
                }

                $filecontent = file_get_contents($pdf_path);
                $success = Storage::disk('s3')->put($s3path . $fileData->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');
                return url('/') . '/' . $pdf_path;
            }
        } else {
            $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
            if (empty($getLastExpense))
                $input['voucher_number'] = '1001';
            else
                $input['voucher_number'] = $getLastExpense->voucher_number + 1;

            $fData['flagModule'] = 'expenses';
            $input['created_on'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = Auth::user()->id;
            $input['approved_by'] = Auth::user()->id;
            $input['request_by'] = Auth::user()->id;
            $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
            $input['display_notification_cashier_for_house_file_expense'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $input['admin_managers'] = Auth::user()->id;
            $model = Expense::create($input);
            //pre($model);
            $data['id'] = $model->expense_id;
            $dataCargo = DB::table('hawb_files')->where('id', $_POST['house_file_id'])->first();
            if ($dataCargo->cargo_operation_type == 1)
                $data['flagExpense'] = ' Import - ' . $dataCargo->file_number;
            else
                $data['flagExpense'] = ' Export - ' . $dataCargo->file_number;
            Activities::log('create', 'houseFileExpense', (object) $data);


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

            // Store expense activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'houseFile';
            $modelActivities->related_id = $model->house_file_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Expense #' . $model->voucher_number . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {

                $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();

                $dataCargo = DB::table('hawb_files')->where('id', $model->house_file_id)->first();

                $pdf = PDF::loadView('housefile-expenses.print', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
                $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
                $pdf_path = 'public/houseFileExpensesPdf/' . $pdf_file;
                $pdf->save($pdf_path);
                $s3path = 'Files/Cargo/';
                if ($fileData->cargo_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Expenses/';
                } else if ($fileData->cargo_operation_type == 2) {
                    $s3path .= 'Export/' . $fileData->file_number . '/Expenses/';
                } else {
                    $s3path .= 'Local/' . $fileData->file_number . '/Expenses/';
                }

                $filecontent = file_get_contents($pdf_path);
                $success = Storage::disk('s3')->put($s3path . $fileData->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

                Session::flash('flash_message', 'Expense has been created successfully');
                return url('/') . '/' . $pdf_path;
            }
            Session::flash('flash_message', 'Expense has been created successfully');
        }
    }

    public function edit(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_cargo_house_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model =  Expense::find($id);

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


        $dataAwbImport = DB::table('hawb_files')->where('deleted', 0)->whereNotNull('hawb_hbl_no')->get()->pluck('hawb_hbl_no', 'id')->toArray();
        $dataAwbExport = DB::table('hawb_files')->where('deleted', 0)->whereNotNull('export_hawb_hbl_no')->get()->pluck('export_hawb_hbl_no', 'id')->toArray();
        $dataAwbNos = $dataAwbImport + $dataAwbExport;

        $dataFileNumber = DB::table('hawb_files')->where('deleted', 0)
            ->whereNull('file_close')
            ->whereNotNull('file_number')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));



        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');

        $dataCargo = DB::table('cargo')->where('id', $model->cargo_id)->first();

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $cashier = DB::table('users')->select(['id', 'name'])->where('department', '11')->where('deleted', 0)->orderBy('id', 'desc')->pluck('name', 'id');

        return view('housefile-expenses._formedit', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'flagFromWhere' => $flagFromWhere, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'dataCargo' => $dataCargo, 'currency' => $currency, 'cashier' => $cashier]);
    }

    public function editrequestedbyagent(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_cargo_house_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        Expense::where('expense_id', $id)->update(['display_notification_admin' => 0]);
        $model =  Expense::find($id);

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


        $dataAwbImport = DB::table('hawb_files')->where('deleted', 0)->whereNotNull('hawb_hbl_no')->get()->pluck('hawb_hbl_no', 'id')->toArray();
        $dataAwbExport = DB::table('hawb_files')->where('deleted', 0)->whereNotNull('export_hawb_hbl_no')->get()->pluck('export_hawb_hbl_no', 'id')->toArray();
        $dataAwbNos = $dataAwbImport + $dataAwbExport;

        $dataFileNumber = DB::table('hawb_files')->where('deleted', 0)
            ->whereNull('file_close')
            ->whereNotNull('file_number')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));



        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');

        $dataCargo = DB::table('cargo')->where('id', $model->cargo_id)->first();

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');

        if (empty($model->admin_manager_role))
            $model->admin_manager_role = 13;
        $adminManagersUsers = DB::table('users')->select(['id', 'name'])->where('department', $model->admin_manager_role)->orderBy('id', 'desc')->pluck('name', 'id');

        $model->admin_managers = explode(',', $model->admin_managers);

        $expenseStatus = array();
        $expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        $expenseStatus['Requested'] = 'Requested';
        $expenseStatus['Disbursement done'] = 'Disbursement done';

        $cashier = DB::table('users')->select(['id', 'name'])->where('department', '11')->where('deleted', 0)->orderBy('id', 'desc')->pluck('name', 'id');

        return view('housefile-expenses._formeditexpenserequest', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'flagFromWhere' => $flagFromWhere, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'dataCargo' => $dataCargo, 'currency' => $currency, 'cashier' => $cashier, 'adminManagersRole' => $adminManagersRole, 'adminManagersUsers' => $adminManagersUsers, 'expenseStatus' => $expenseStatus]);
    }

    public function update(Request $request, $id)
    {
        session_start();
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $model->fill($request->input());
        Activities::log('update', 'houseFileExpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier_for_house_file_expense'] = '1';
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
        $dataCargo = DB::table('hawb_files')->where('id', $model->house_file_id)->first();
        $pdf = PDF::loadView('housefile-expenses.print', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/houseFileExpensesPdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';
        if ($dataCargo->cargo_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else if ($dataCargo->cargo_operation_type == 2) {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Local/' . $dataCargo->file_number . '/Expenses/';
        }
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

        // Update expense to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('updateExpense',$model);
        } */

        /*if(isset($_SESSION['sessionAccessToken']))
        {

            $fData['id'] = $model->expense_id;
            $fData['module'] = '5';
            $fData['flagModule'] = 'updateExpense';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            
            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model='.$newModel);
            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }*/
        Session::flash('flash_message', 'Record has been updated successfully');
    }

    public function updaterequestedbyagent(Request $request, $id)
    {
        session_start();
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $oldExpenseStatus = $model->expense_request;
        $model->fill($request->input());
        Activities::log('update', 'houseFileExpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier_for_house_file_expense'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }
        $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
        $input['updated_by'] = auth()->user()->id;
        if ($oldExpenseStatus != $input['expense_request']) {
            $input['display_notification_cashier_for_house_file_expense'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            if ($input['expense_request'] == 'Approved')
                $input['approved_by'] = auth()->user()->id;
            else
                $input['approved_by'] = null;
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
        $dataCargo = DB::table('hawb_files')->where('id', $model->house_file_id)->first();
        $pdf = PDF::loadView('housefile-expenses.print', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/houseFileExpensesPdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';
        if ($dataCargo->cargo_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else if ($dataCargo->cargo_operation_type == 2) {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Local/' . $dataCargo->file_number . '/Expenses/';
        }
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

        // Update expense to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('updateExpense',$model);
        } */

        /*if(isset($_SESSION['sessionAccessToken']))
        {

            $fData['id'] = $model->expense_id;
            $fData['module'] = '5';
            $fData['flagModule'] = 'updateExpense';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            
            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model='.$newModel);
            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }*/
        Session::flash('flash_message', 'Record has been updated successfully');
    }

    public function destroy(Expense $expense, $id)
    {
        session_start();
        $record = DB::table('expenses')->where('expense_id', $id)->first();
        $model = Expense::where('expense_id', $id)->delete();

        // Delete expense to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('deleteExpense',$record);
        } */

        /*if(isset($_SESSION['sessionAccessToken']))
            {

                $fData['voucher_number'] = $record->voucher_number;
                $fData['qb_id'] = $record->quick_book_id;
                $fData['module'] = '10';
                $fData['flagModule'] = 'deleteExpense';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                // Store expense to QB
                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model='.$newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }*/
    }

    public function print($expenseId = null, $houseId =  null, $flag = null)
    {

        if ($flag == 'fromNotification')
            Expense::where('expense_id', $expenseId)->update(['display_notification_agent' => 0]);

        //$myfile = fopen("testwrite.txt", "a"); 
        //fwrite($myfile, '--ooo--'); 
        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $dataCargo = DB::table('hawb_files')->where('id', $houseId)->first();

        $pdf = PDF::loadView('housefile-expenses.print', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $expenseId . '_expense.pdf';
        $pdf_path = 'public/houseFileExpensesPdf/' . $pdf_file;
        $pdf->save($pdf_path);

        $s3path = 'Files/Cargo/';
        if ($dataCargo->cargo_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else if ($dataCargo->cargo_operation_type == 2) {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Local/' . $dataCargo->file_number . '/Expenses/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $cargoExpenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function printall($flag = null)
    {
        $cargoExpenseData = DB::table('expenses')
            ->select(DB::raw('expenses.*,hawb_files.file_number'))
            ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
            ->where('expenses.deleted', '0')
            ->whereNotNull('expenses.house_file_id');
        //->where('expense_request','Approved')
        //->orderBy('expenses.expense_id', 'desc')

        if ($flag != 'all') {
            $cargoExpenseData = $cargoExpenseData->where('house_file_id', $flag)->get()->toArray();
            $cargoData = DB::table('hawb_files')->where('id', $flag)->first();
        } else {
            $cargoExpenseData = $cargoExpenseData->get()->toArray();
            $cargoData = array();
        }

        $query1 = array();
        foreach ($cargoExpenseData as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->house_file_id;
        }

        array_multisort((array) $query1, SORT_DESC, $cargoExpenseData);

        $pdf = PDF::loadView('expenses.printallcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'flag' => $flag, 'cargoData' => $cargoData]);
        $pdf_file = 'printallHouseFileExpense.pdf';
        $pdf_path = 'public/houseFileExpensesPdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'AllHouseFileExpense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function viewdetails($id)
    {
        $checkPermission = User::checkPermission(['viewdetails_cargo_house_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'houseFileExpense')->orderBy('id', 'desc')->get()->toArray();
        $model = Expense::find($id);
        return view("housefile-expenses.view-details", ['model' => $model, 'activityData' => $activityData]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoExpensesEdit = User::checkPermission(['update_cargo_house_expenses'], '', auth()->user()->id);
        $permissionCargoExpensesDelete = User::checkPermission(['delete_cargo_house_expenses'], '', auth()->user()->id);

        $req = $request->all();

        $status = $req['status'];
        $expenseType = $req['expenseType'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['', 'expenses.expense_id', 'expenses.expense_id', 'exp_date', 'voucher_number', 'hawb_files.file_number', 'bl_awb', '', 'note', 'consignee', 'shipper', 'currency.code', '', '', 'expense_request', 'expense_type'];

        $total = Expense::selectRaw('count(*) as total')
            //->where('expenses.deleted', '0')
            ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
            ->whereNotNull('expenses.house_file_id');
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
            ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.house_file_id');
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
            ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.house_file_id');
        //->where('expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent') {
            $filteredq = $filteredq->where('request_by', Auth::user()->id);
        }
        if (!empty($status)) {
            $query = $query->where('expense_request', $status);
        }
        if (!empty($expenseType)) {
            $filteredq = $filteredq->where('expense_type', $expenseType);
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('hawb_files.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('hawb_files.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
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
            $query1[$key] = $row->house_file_id;
        }
        array_multisort((array)$query1, SORT_DESC,$query); */

        $data1 = [];
        foreach ($query as $key => $items) {

            $dataCargo = HawbFiles::getHouseFileData($items->house_file_id);
            if (!empty($dataCargo)) {

                $invoiceOfFile = Expense::getHouseFileInvoicesOfFile($items->house_file_id);
                $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
                $totlaExpense = Expense::getExpenseTotal($items->expense_id);
                //$dataCurrency = Currency::getData($items->currency); 
                $dataCurrency = Vendors::getDataFromPaidTo($items->expense_id);
                $dataClientUsingModuleId = Common::getClientDataUsingModuleId('houseFile', $items->house_file_id);
                $action = '<div class="dropdown">';

                $delete =  route('deleteexpensevoucher', $items->expense_id);
                if (checkloggedinuserdata() == 'Agent') {
                    $edit =  route('editagenthousefileexpenses', [$items->expense_id, 'flagFromExpenseListing']);
                } else {
                    if ($items->request_by_role == 12 || $items->request_by_role == 10)
                        $edit =  route('edithousefileexpenserequestedbyagent', [$items->expense_id]);
                    else
                        $edit =  route('edithousefileexpense', [$items->expense_id]);
                }

                $action .= '<a title="Click here to print"  target="_blank" href="' . route('getprintsinglehousefileexpense', [$items->expense_id, $items->house_file_id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

                if ($items->deleted == '0' && checkloggedinuserdata() != 'Cashier') {
                    if ($permissionCargoExpensesEdit && $dataCargo->file_close != 1) {
                        $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                    }
                    if ($permissionCargoExpensesDelete) {
                        $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                    }
                }
                $action .= '</div>';

                if ($items->expense_request == 'Requested') {
                    $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $items->expense_id . '" value="' . $items->expense_id . '" />';
                } else {
                    $checkBoxes = '';
                }

                $data1[] = [$checkBoxes, $items->expense_id, '', date('d-m-Y', strtotime($items->exp_date)), $items->voucher_number, $items->expense_type == 1 ? 'Cash' : 'Credit', $dataCargo->file_number, $items->bl_awb, !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-', $items->note != '' ? $items->note : '-', $dataClientUsingModuleId['consigneeName'], $dataClientUsingModuleId['shipperName'], !empty($dataCurrency->code) ? $dataCurrency->code : "-", $totlaExpense, $invoiceOfFile, $items->expense_request, $action];
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
