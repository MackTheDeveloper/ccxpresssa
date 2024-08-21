<?php

namespace App\Http\Controllers;

use App\Aeropost;
use App\Common;
use App\Expense;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use App\Activities;
use App\ExpenseDetails;
use Session;
use PDF;
use App\User;
use App\Admin;
use App\AeropostMaster;
use App\CashCredit;
use App\Currency;
use App\Vendors;
use Illuminate\Support\Facades\Storage;
use Config;

class AeropostMasterExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_aeropost_expenses'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
       
        return view("aeropost-master-expenses.index");
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionExpensesEdit = User::checkPermission(['update_aeropost_master_expenses'], '', auth()->user()->id);
        $permissionExpensesDelete = User::checkPermission(['delete_aeropost_master_expenses'], '', auth()->user()->id);

        $req = $request->all();

        $status = $req['status'];
        $expenseType = $req['expenseType'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['', 'expenses.expense_id', 'expenses.expense_id', 'exp_date', 'voucher_number', 'aeropost_master.file_number', 'bl_awb', '', 'note', 'expenses.consignee', 'expenses.shipper', 'currency.code', '', '', 'expense_request', 'expense_type'];

        $total = Expense::selectRaw('count(*) as total')
            //->where('expenses.deleted', '0')
            ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
            ->whereNotNull('expenses.aeropost_master_id');
        if (checkloggedinuserdata() == 'Agent')
            $total = $total->where('request_by', Auth::user()->id);
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
            ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.aeropost_master_id');
        //->where('expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent')
            $query = $query->where('request_by', Auth::user()->id);
        if (!empty($status)) {
            $query = $query->where('expense_request', $status);
        }
        if (!empty($expenseType)) {
            $query = $query->where('expense_type', $expenseType);
        }

        $filteredq = DB::table('expenses')
            ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.aeropost_master_id');
        //->where('expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent')
            $filteredq = $filteredq->where('request_by', Auth::user()->id);
        if (!empty($status)) {
            $filteredq = $filteredq->where('expense_request', $status);
        }
        if (!empty($expenseType)) {
            $filteredq = $filteredq->where('expense_type', $expenseType);
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('expenses.consignee', 'like', '%' . $search . '%')
                    ->orWhere('expenses.shipper', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('expenses.consignee', 'like', '%' . $search . '%')
                    ->orWhere('expenses.shipper', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get()->toArray();

        $query1 = array();
        foreach ($query as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->aeropost_master_id;
        }

        array_multisort((array) $query1, SORT_DESC, $query);

        $data1 = [];
        foreach ($query as $key => $items) {

            $aeropostCargo = AeropostMaster::getMasterAeropostData($items->aeropost_master_id);
            if (!empty($aeropostCargo)) {

                $invoiceOfFile = Expense::getAeropostMasterInvoicesOfFile($items->aeropost_master_id);
                $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
                $totlaExpense = Expense::getExpenseTotal($items->expense_id);
                //$dataCurrency = Currency::getData($items->currency);
                $dataCurrency = Vendors::getDataFromPaidTo($items->expense_id);
                $dataClientUsingModuleId = Common::getClientDataUsingModuleId('aeropostMaster', $items->aeropost_master_id);
                $action = '<div class="dropdown">';

                $delete = route('deleteexpensevoucher', $items->expense_id);
                $edit = route('editaeropostmasterexpense', [$items->expense_id]);

                $action .= '<a title="Click here to print"  target="_blank" href="' . route('printsingleaeropostmasterexpense', [$items->expense_id, $items->aeropost_master_id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';
                if ($items->deleted == '0') {
                    if ($permissionExpensesEdit && $aeropostCargo->file_close != 1) {
                        $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                    }
                    if ($permissionExpensesDelete) {
                        $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                    }
                }
                $action .= '</div>';

                if ($items->expense_request == 'Requested') {
                    $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $items->expense_id . '" value="' . $items->expense_id . '" />';
                } else {
                    $checkBoxes = '';
                }

                $data1[] = [$checkBoxes, $items->expense_id, '', date('d-m-Y', strtotime($items->exp_date)), $items->voucher_number, $items->expense_type == 1 ? 'Cash' : 'Credit', $aeropostCargo->file_number, $items->bl_awb, !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-', $items->note != '' ? $items->note : '-', $dataClientUsingModuleId['consigneeName'], $dataClientUsingModuleId['shipperName'], !empty($dataCurrency->code) ? $dataCurrency->code : "-", $totlaExpense, $invoiceOfFile, $items->expense_request, $action];
            }
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data1,
        );
        return Response::json($json_data);
    }

    public function create($aeropostMasterId = null, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_aeropost_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Expense;

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);


        $cashCredit = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted', 0)->where('status', 1)->pluck('cashcreditData', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');

        $dataAwbNos = DB::table('aeropost_master')->where('deleted', '0')->get()->pluck('tracking_number', 'id');

        $dataFileNumber = DB::table('aeropost_master')->where('deleted', '0')
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

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');

        return view('aeropost-master-expenses._form', ['model' => $model, 'billingParty' => $billingParty, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'voucherNo' => $voucherNo, 'dataCost' => $dataCost, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'currency' => $currency, 'cashier' => $cashier, 'aeropostMasterId' => $aeropostMasterId, 'flagFromWhere' => $flagFromWhere, 'adminManagersRole' => $adminManagersRole]);
    }

    public function store(Request $request)
    {
        //session_start();
        $input = $request->all();
        $fileData = DB::table('aeropost_master')->where('id', $input['file_number'])->where('deleted', '0')->first();
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
        $input['display_notification_cashier_for_aeropost_master'] = '1';
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $model = Expense::create($input);
        $data['id'] = $model->expense_id;
        $dataAeropost = DB::table('aeropost_master')->where('id', $_POST['aeropost_master_id'])->first();
        $data['flagExpense'] = $dataAeropost->file_number;
        Activities::log('create', 'aeropostMasterExpense', (object) $data);


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
        $modelActivities->type = 'aeropostMaster';
        $modelActivities->related_id = $model->aeropost_master_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Expense #' . $model->voucher_number . ' has been generated';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {
            $expenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();

            $dataAeropost = DB::table('aeropost_master')->where('id', $model->aeropost_master_id)->first();
            $pdf = PDF::loadView('aeropost-master-expenses.print-one', ['expenseData' => $expenseData, 'dataAeropost' => $dataAeropost]);
            $pdf_file = $dataAeropost->file_number . '_' . $model->expense_id . '_expense.pdf';
            $pdf_path = 'public/aeropostMasterExpensePdf/' . $pdf_file;
            $pdf->save($pdf_path);

            $s3path = 'Files/Courier/Aeropost-Master/' . $fileData->file_number . '/Expenses/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . $fileData->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

            /* if(isset($_SESSION['sessionAccessToken']))
            {
                $fData = $model->expense_id;
                $fData['module'] = '5';
                
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                // Store expense to QB
                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model='.$newModel);
                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            } */
            Session::flash('flash_message', 'Expense has been created successfully');
            return url('/') . '/' . $pdf_path;
        }

        /* if(isset($_SESSION['sessionAccessToken']))
        {   
            $fData['id'] = $model->expense_id;
            $fData['module'] = '5';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model='.$newModel);
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        } */
        Session::flash('flash_message', 'Expense has been created successfully');
    }

    public function edit(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_aeropost_master_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model =  Expense::find($id);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);

        $cashCredit = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted', 0)->where('status', 1)->pluck('cashcreditData', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');


        $dataAwbNos = DB::table('aeropost_master')->where('deleted', '0')->get()->pluck('tracking_number', 'id');

        $dataFileNumber = DB::table('aeropost_master')->where('deleted', '0')
            ->whereNull('file_close')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));


        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');


        $dataAeropost = DB::table('aeropost_master')->where('id', $model->aeropost_master_id)->first();

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))
            ->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');

        $expenseStatus = array();
        $expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        $expenseStatus['Requested'] = 'Requested';
        $expenseStatus['Disbursement done'] = 'Disbursement done';

        return view('aeropost-master-expenses._formedit', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'flagFromWhere' => $flagFromWhere, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'dataAeropost' => $dataAeropost, 'currency' => $currency, 'cashier' => $cashier, 'adminManagersRole' => $adminManagersRole, 'expenseStatus' => $expenseStatus]);
    }

    public function update(Request $request, $id)
    {
        //session_start();
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $oldExpenseStatus = $model->expense_request;
        $model->fill($request->input());
        Activities::log('update', 'aeropostMasterExpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier_for_aeropost_master'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }

        $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
        $input['updated_by'] = auth()->user()->id;
        if (checkloggedinuserdata() == 'Other' && isset($input['expense_request']) && !empty($input['expense_request'])) {
            if ($oldExpenseStatus != $input['expense_request']) {
                $input['display_notification_cashier_for_aeropost_master'] = '1';
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

        $expenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataAeropost = DB::table('aeropost_master')->where('id', $model->aeropost_master_id)->first();
        $pdf = PDF::loadView('aeropost-master-expenses.print-one', ['expenseData' => $expenseData, 'dataAeropost' => $dataAeropost]);
        $pdf_file = $dataAeropost->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/aeropostMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Aeropost-Master/' . $dataAeropost->file_number . '/Expenses/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataAeropost->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

        /* if(isset($_SESSION['sessionAccessToken']))
        {

            $fData['id'] = $model->expense_id;
            $fData['module'] = '5';
            $fData['flagModule'] = 'updateExpense';
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

    public function print($expenseId = null, $aeropostMasterId =  null, $flag = null)
    {
        if ($flag == 'fromNotification')
            Expense::where('expense_id', $expenseId)->update(['display_notification_agent' => 0]);

        $expenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $dataAeropost = DB::table('aeropost_master')->where('id', $aeropostMasterId)->first();
        if (empty($dataAeropost))
            $dataAeropost = new AeropostMaster;
        $pdf = PDF::loadView('aeropost-master-expenses.print-one', ['expenseData' => $expenseData, 'dataAeropost' => $dataAeropost]);
        $pdf_file = $dataAeropost->file_number . '_' . $expenseId . '_expense.pdf';
        $pdf_path = 'public/aeropostMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Aeropost-Master/' . $dataAeropost->file_number . '/Expenses/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataAeropost->file_number . '_' . $expenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function printall($flag = null)
    {
        $cargoExpenseData = DB::table('expenses')
            ->select(DB::raw('expenses.*,aeropost_master.file_number'))
            ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
            ->where('expenses.deleted', '0')
            ->whereNotNull('expenses.aeropost_master_id');
        //->where('expense_request','Approved')
        //->orderBy('expenses.expense_id', 'desc')

        if ($flag != 'all') {
            $cargoExpenseData = $cargoExpenseData->where('aeropost_master_id', $flag)->get()->toArray();
            $cargoData = DB::table('aeropost_master')->where('id', $flag)->first();
        } else {
            $cargoExpenseData = $cargoExpenseData->get()->toArray();
            $cargoData = array();
        }


        $query1 = array();
        foreach ($cargoExpenseData as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->aeropost_master_id;
        }

        array_multisort((array) $query1, SORT_DESC, $cargoExpenseData);



        $pdf = PDF::loadView('aeropost-master-expenses.print-all', ['cargoExpenseData' => $cargoExpenseData, 'flag' => $flag, 'cargoData' => $cargoData]);
        $pdf_file = 'printallexpense.pdf';
        $pdf_path = 'public/aeropostMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function  viewaeropostmasterexpenseforcashier($expenseId = null, $aeropostMasterId =  null, $flag = null)
    {
        $checkPermission = User::checkPermission(['change_file_expense_status_aeropost_master'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        if ($flag == 'fromNotification')
            Expense::where('expense_id', $expenseId)->update(['display_notification_cashier_for_aeropost_master' => 0]);

        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $expenseStatus = array();
        $expenseStatus['Disbursement done'] = 'Disbursement done';
        //$expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        //$expenseStatus['Requested'] = 'Requested';


        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        return view('aeropost-master-expenses.getprintviewsingleaeropostexpensecashier', ['cargoExpenseData' => $cargoExpenseData, 'expenseId' => $expenseId, 'aeropostMasterId' => $aeropostMasterId, 'expenseStatus' => $expenseStatus, 'cashCredit' => $cashCredit]);
    }

    public function changeaeropostmasterexpensestatusbycashier(Request $request)
    {
        session_start();
        $input = $request->all();
        $model = Expense::find($input['id']);
        $input['updated_by'] = Auth::user()->id;
        $input['disbursed_by'] = null;
        if ($input['expense_request'] == 'Disbursement done') {
            $input['disbursed_by'] = Auth::user()->id;
            $input['disbursed_datetime'] = date('Y-m-d H:i:s');
            $totalExpenses = str_replace(',', '', Expense::getExpenseTotal($input['id']));

            $getCashCreditData = DB::table('cashcredit')->where('id', $input['cash_credit_account'])->first();
            $finalAmt = $getCashCreditData->available_balance - $totalExpenses;
            DB::table('cashcredit')->where('id', $input['cash_credit_account'])->update(['available_balance' => $finalAmt,'qb_sync' => 0]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCredit';
            $modelActivities->related_id = $input['cash_credit_account'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $totalExpenses . '-' . $input['expense_request_status_note'];
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            // Store to QB
            $fData['flagModule'] = 'expenses';
            if (isset($_SESSION['sessionAccessToken'])) {
                //pre('test');
                $fData['id'] = $model->expense_id;
                $fData['module'] = '14';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
        /* $input['display_notification_admin'] = '1';
        $input['display_notification_agent'] = '1';
        $input['notification_date_time'] = date('Y-m-d H:i:s'); */
        $input['display_notification_agent'] = '1';
        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $model->update($input);

        $expenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataAeropost = DB::table('aeropost_master')->where('id', $model->aeropost_master_id)->first();
        if (empty($dataAeropost))
            $dataAeropost = new AeropostMaster;
        $pdf = PDF::loadView('aeropost-master-expenses.print-one', ['expenseData' => $expenseData, 'dataAeropost' => $dataAeropost]);
        $pdf_file = $dataAeropost->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/aeropostMasterExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Aeropost-Master/' . $dataAeropost->file_number . '/Expenses/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataAeropost->file_number . '_' . $expenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect()->route('viewaeropostmasterexpenseforcashier', [$model->expense_id, $model->aeropost_master_id]);
    }
}
