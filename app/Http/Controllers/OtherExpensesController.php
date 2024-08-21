<?php

namespace App\Http\Controllers;

use App\OtherExpenses;
use App\OtherExpenseItems;
use App\Clients;
use App\OtherExpensesDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Session;
use PDF;
use App\User;
use Illuminate\Support\Facades\Storage;
use Config;
use App\CashCredit;
use App\Vendors;
use Excel;

class OtherExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($flagFrom = null)
    {
        $checkPermission = User::checkPermission(['listing_other_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        /* $data = DB::table('other_expenses')
            ->where('deleted', '0')
            ->orderBy('id', 'desc')
            ->get(); */
        return view("other-expenses.index", ['flagFrom' => $flagFrom]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($flagFrom = null)
    {
        $checkPermission = User::checkPermission(['add_other_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new otherExpenses;

        $cashCredit = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted', 0)->where('status', 1)->pluck('cashcreditData', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $getLastExpense = DB::table('other_expenses')->orderBy('id', 'desc')->first();
        if (empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        $allUsers = DB::table('vendors')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');

        $departments = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where('cashcredit_account_type.name', 'Other Expense Department')
            ->where('cashcredit_detail_type.deleted', 0)
            ->pluck('cashcredit_account_type.name', 'cashcredit_account_type.id');
        //pre($departments);
        $model->exp_date = date('d-m-Y');
        $dataOtherExpenseItemsAutoComplete = OtherExpenseItems::getAutocomplete();

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');

        return view('other-expenses._form', ['model' => $model, 'voucherNo' => $voucherNo, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'departments' => $departments, 'dataOtherExpenseItemsAutoComplete' => $dataOtherExpenseItemsAutoComplete, 'flagFrom' => $flagFrom, 'currency' => $currency, 'adminManagersRole' => $adminManagersRole, 'cashier' => $cashier, 'dataCost' => $dataCost]);
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
        $getLastExpense = DB::table('other_expenses')->orderBy('id', 'desc')->first();
        if (empty($getLastExpense))
            $input['voucher_number'] = '1001';
        else
            $input['voucher_number'] = $getLastExpense->voucher_number + 1;
        $input['type'] = $input['flagFrom'];

        $input['created_on'] = gmdate("Y-m-d H:i:s");
        $input['created_by'] = Auth::user()->id;
        $input['request_by'] = Auth::user()->id;
        if (checkloggedinuserdata() != 'Other') {
            $input['request_by_role'] = auth()->user()->department;
            $input['expense_request'] = 'Requested';
            $input['display_notification_admin'] = '1';
        } else {
            $input['approved_by'] = Auth::user()->id;
            $input['display_notification_cashier'] = '1';
        }
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $model = otherExpenses::create($input);
        Activities::log('create', 'administrationExpense', $model);

        $countexp = $_POST['count_expense'];
        $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
        $input['amount'] = array_values($input['expenseDetails']['amount']);
        $input['description'] = array_values($input['expenseDetails']['description']);
        $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);

        $activityAmot = 0;
        for ($i = 0; $i < $countexp; $i++) {
            $modelExp = new OtherExpensesDetails();
            $modelExp->expense_id = $model->id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->expense_type = $input['expense_type'][$i];
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->paid_to = $input['paid_to'][0];
            $modelExp->save();
        }

        if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {
            $expenseData = DB::table('other_expenses')->where('id', $model->id)->get();
            $pdf = PDF::loadView('other-expenses.printotherexpense', ['expenseData' => $expenseData]);
            $pdf_file = $model->voucher_number . '_' . $model->id . '_expense.pdf';
            $pdf_path = 'public/otherExpensePdf/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Administration Expenses/';



            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . $model->voucher_number . '_expense.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);
            return url('/') . '/' . $pdf_path;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OtherExpenses  $otherExpenses
     * @return \Illuminate\Http\Response
     */
    public function show(OtherExpenses $otherExpenses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OtherExpenses  $otherExpenses
     * @return \Illuminate\Http\Response
     */
    public function edit(OtherExpenses $otherExpenses, $id, $flagFrom = null)
    {
        $checkPermission = User::checkPermission(['update_other_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        OtherExpenses::where('id', $id)->update(['display_notification_admin' => 0]);

        $model =  otherExpenses::find($id);

        $dataExpenseDetails  = DB::table('other_expenses_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));

        $cashCredit = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted', 0)->where('status', 1)->pluck('cashcreditData', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $allUsers = DB::table('vendors')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $allUsers = json_decode($allUsers, 1);
        ksort($allUsers);

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $departments = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where('cashcredit_account_type.name', 'Other Expense Department')
            ->where('cashcredit_detail_type.deleted', 0)
            ->pluck('name', 'id');
        $dataOtherExpenseItemsAutoComplete = OtherExpenseItems::getAutocomplete();
        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id', 'name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status', 1)->orderBy('id', 'desc')->pluck('name', 'id');
        $expenseStatus = array();
        $expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        $expenseStatus['Requested'] = 'Requested';
        $expenseStatus['Disbursement done'] = 'Disbursement done';

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');

        return view('other-expenses._formedit', ['model' => $model, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'departments' => $departments, 'dataOtherExpenseItemsAutoComplete' => $dataOtherExpenseItemsAutoComplete, 'dataExpenseDetails' => $dataExpenseDetails, 'flagFrom' => $flagFrom, 'adminManagersRole' => $adminManagersRole, 'currency' => $currency, 'expenseStatus' => $expenseStatus, 'cashier' => $cashier, 'dataCost' => $dataCost]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OtherExpenses  $otherExpenses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OtherExpenses $otherExpenses, $id)
    {
        $model = OtherExpensesDetails::where('expense_id', $id)->delete();
        $model = OtherExpenses::find($id);
        $oldCashier = $model->cashier_id;
        $oldStatus = $model->expense_request;
        $model->fill($request->input());
        Activities::log('update', 'administrationExpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }
        $input['updated_by'] = auth()->user()->id;
        if (checkloggedinuserdata() == 'Other' && isset($input['expense_request']) && !empty($input['expense_request'])) {
            if ($oldStatus != $input['expense_request']) {
                $input['display_notification_cashier'] = '1';
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

        $activityAmot = 0;
        for ($i = 0; $i < $countexp; $i++) {
            $modelExp = new OtherExpensesDetails();
            $modelExp->expense_id = $id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->expense_type = $input['expense_type'][$i];
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->paid_to = $input['paid_to'][0];
            $modelExp->save();
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect()->route('otherexpenses', $input['flagFrom']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OtherExpenses  $otherExpenses
     * @return \Illuminate\Http\Response
     */
    public function deleteotherexpense(OtherExpenses $otherExpenses, $id)
    {
        $model = OtherExpensesDetails::where('id', $id)->delete();
    }

    public function deleteotherexpensevoucher(otherExpenses $otherExpenses, $id)
    {
        $record = DB::table('other_expenses')->where('id', $id)->first();
        //$model = otherExpenses::where('id', $id)->delete();
        $model = otherExpenses::where('id', $id)->update(['deleted' => 1, 'deleted_on' => gmdate("Y-m-d H:i:s")]);
    }

    public function expandotherexpenses()
    {
        $expenseId = $_POST['expenseId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('other_expenses_details')->where('expense_id', $expenseId)->where('deleted', 0)->get();
        return view('other-expenses.renderotherexpenses', ['packageData' => $packageData, 'rowId' => $rowId]);
    }

    public function generateotherexpensevoucheronsavenext()
    {
        $getLastExpense = DB::table('other_expenses')->orderBy('id', 'desc')->first();
        if (empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        return $voucherNo;
    }
    public function addmoreotherexpense()
    {
        $model = new otherExpenses;
        $selectedVendor = $_POST['selectedVendor'];
        $allUsers = DB::table('vendors')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');

        $dataOtherExpenseItemsAutoComplete = OtherExpenseItems::getAutocomplete();
        return view('other-expenses.addmoreother', [
            'model' => $model, 'allUsers' => $allUsers, 'dataOtherExpenseItems' => $dataOtherExpenseItemsAutoComplete, 'counter' => $_POST['counter'], 'selectedVendor' => $selectedVendor, 'dataCost' => $dataCost
        ]);
    }
    public function getprintsingleotherexpense($expenseId = null)
    {
        $expenseDataOne = DB::table('other_expenses')->where('id', $expenseId)->first();
        $expenseData = DB::table('other_expenses')->where('id', $expenseId)->get();
        $pdf = PDF::loadView('other-expenses.printotherexpense', ['expenseData' => $expenseData]);
        $pdf_file = $expenseDataOne->voucher_number . '_' . $expenseDataOne->id . '_expense.pdf';
        $s3path = 'Files/Administration Expenses/';
        $pdf_path = 'public/otherExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $expenseDataOne->voucher_number . '_expense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function printotherexpensebyfilter($fromDate = null, $toDate = null, $expenseStatus = null, $submitButtonName = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        $expenseData = DB::table('other_expenses')
            ->selectRaw('other_expenses.*,cashcredit_detail_type.name as deptName')
            ->leftJoin('currency', 'currency.id', '=', 'other_expenses.currency')
            ->leftJoin('cashcredit_detail_type', 'cashcredit_detail_type.id', '=', 'other_expenses.department');
        if (!empty($fromDate) && !empty($toDate)) {
            $expenseData = $expenseData->whereBetween('other_expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($expenseStatus)) {
            $expenseData = $expenseData->where('expense_request', $expenseStatus);
        }
        $expenseData = $expenseData->orderBy('other_expenses.id', 'desc')->get();

        if ($submitButtonName == 'clsPrint') {
            $totalInUsd = '0.00';
            $totalInHtg = '0.00';
            foreach ($expenseData as $k => $v) {

                $dataCurrencyAll = Vendors::getDataFromPaidToAdministration($v->id);
                $totlaExpenseAll = OtherExpenses::getExpenseTotal($v->id);

                if ($dataCurrencyAll->code == 'USD')
                    $totalInUsd += str_replace(',', '', $totlaExpenseAll);
                if ($dataCurrencyAll->code == 'HTG')
                    $totalInHtg += str_replace(',', '', $totlaExpenseAll);
            }

            $pdf = PDF::loadView('other-expenses.printotherexpensebyfilter', ['expenseData' => $expenseData, 'fromDate' => $fromDate, 'toDate' => $toDate, 'totalInUsd' => $totalInUsd, 'totalInHtg' => $totalInHtg]);
            $pdf_file = 'other_expense_filtered.pdf';
            $pdf_path = 'public/otherExpensePdf/' . $pdf_file;
            $pdf->save($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else {
            $otherExpenseArray[] = array('Date', 'Voucher No.', 'Department', 'Cash/Bank', 'Currency', 'Total Amount', 'Description', 'Status');
            foreach ($expenseData as $items) {
                $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
                $totlaExpense = OtherExpenses::getExpenseTotal($items->id);
                $dataCurrency = Vendors::getDataFromPaidToAdministration($items->id);
                $otherExpenseArray[] = array(
                    'Date' => date('d-m-Y', strtotime($items->exp_date)),
                    'Voucher No.' => $items->voucher_number,
                    'Department' => $items->deptName,
                    'Cash/Bank' => !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-',
                    'Currency' => !empty($dataCurrency->code) ? $dataCurrency->code : "-",
                    'Total Amount' => $totlaExpense,
                    'Description' => $items->note != '' ? $items->note : '-',
                    'Status' => $items->expense_request,
                );
            }
            $excelObj = Excel::create('UpsCommissionReport', function ($excel) use ($otherExpenseArray) {
                $excel->setTitle('Administration Expenses');
                $excel->sheet('Administration Expense', function ($sheet) use ($otherExpenseArray) {
                    $sheet->fromArray($otherExpenseArray, null, 'A1', false, false);
                });
            });
            $excelObj->download('xlsx');
        }
    }


    public function  getprintviewsingleadministrationexpensecashier($expenseId = null, $flag = null)
    {
        if ($flag == 'fromNotification')
            OtherExpenses::where('id', $expenseId)->update(['display_notification_cashier' => 0]);

        $cargoExpenseData = DB::table('other_expenses')->where('id', $expenseId)->get();

        $expenseStatus = array();

        $expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        $expenseStatus['Requested'] = 'Requested';
        $expenseStatus['Disbursement done'] = 'Disbursement done';

        /* $expenseStatus['Disbursement done'] = 'Disbursement done';
        //$expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        //$expenseStatus['Requested'] = 'Requested'; */


        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        return view('other-expenses.getprintviewsingleadministrationexpensecashier', ['cargoExpenseData' => $cargoExpenseData, 'expenseId' => $expenseId, 'expenseStatus' => $expenseStatus, 'cashCredit' => $cashCredit]);
    }

    public function changeadministrationexpensestatusbycashier(Request $request)
    {
        session_start();
        $input = $request->all();
        $model = OtherExpenses::find($input['id']);
        $input['updated_by'] = Auth::user()->id;
        $input['disbursed_by'] = null;
        if ($input['expense_request'] == 'Disbursement done') {
            $input['disbursed_by'] = Auth::user()->id;
            $input['disbursed_datetime'] = date('Y-m-d H:i:s');
            $totalExpenses = str_replace(',', '', OtherExpenses::getExpenseTotal($input['id']));

            $getCashCreditData = DB::table('cashcredit')->where('id', $input['cash_credit_account'])->first();
            $finalAmt = $getCashCreditData->available_balance - $totalExpenses;
            DB::table('cashcredit')->where('id', $input['cash_credit_account'])->update(['available_balance' => $finalAmt]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCredit';
            $modelActivities->related_id = $input['cash_credit_account'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $totalExpenses . '-' . $input['expense_request_status_note'];
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        $model->update($input);

        $expenseData = DB::table('other_expenses')->where('id', $model->id)->get();
        $expenseDataOne = DB::table('other_expenses')->where('id', $model->id)->first();
        $pdf = PDF::loadView('other-expenses.printotherexpense', ['expenseData' => $expenseData]);
        $pdf_file = $expenseDataOne->voucher_number . '_' . $expenseDataOne->id . '_expense.pdf';
        $s3path = 'Files/Administration Expenses/';
        $pdf_path = 'public/otherExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $expenseDataOne->voucher_number . '_expense.pdf', $filecontent, 'public');

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect()->route('getprintviewsingleadministrationexpensecashier', [$model->id]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionExpensesEdit = User::checkPermission(['update_other_expenses'], '', auth()->user()->id);
        $permissionExpensesDelete = User::checkPermission(['delete_other_expenses'], '', auth()->user()->id);

        $req = $request->all();

        $status = $req['status'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : date('Y-m-01');
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : date('Y-m-d');
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['', 'other_expenses.id', 'other_expenses.id', 'exp_date', 'voucher_number', 'cashcredit_detail_type.name', '', 'currency.code', '', 'note', 'expense_request'];

        $total = OtherExpenses::selectRaw('count(*) as total');
        //->where('other_expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent')
            $total = $total->where('request_by', Auth::user()->id);
        if (!empty($status)) {
            $total = $total->where('expense_request', $status);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('other_expenses.exp_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('other_expenses')
            ->selectRaw('other_expenses.*,cashcredit_detail_type.name as deptName')
            ->leftJoin('currency', 'currency.id', '=', 'other_expenses.currency')
            ->leftJoin('cashcredit_detail_type', 'cashcredit_detail_type.id', '=', 'other_expenses.department');
        //->where('other_expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent')
            $query = $query->where('request_by', Auth::user()->id);
        if (!empty($status)) {
            $query = $query->where('expense_request', $status);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('other_expenses.exp_date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('other_expenses')
            ->leftJoin('currency', 'currency.id', '=', 'other_expenses.currency')
            ->leftJoin('cashcredit_detail_type', 'cashcredit_detail_type.id', '=', 'other_expenses.department');
        //->where('other_expenses.deleted', '0');
        if (checkloggedinuserdata() == 'Agent')
            $filteredq = $filteredq->where('request_by', Auth::user()->id);
        if (!empty($status)) {
            $filteredq = $filteredq->where('expense_request', $status);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('other_expenses.exp_date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->Where(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('cashcredit_detail_type.name', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('cashcredit_detail_type.name', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $queryForTotal = $query->get()->toArray();
        $query1 = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get()->toArray();

        $totalInUsd = '0.00';
        $totalInHtg = '0.00';
        foreach ($queryForTotal as $k => $v) {

            $dataCurrencyAll = Vendors::getDataFromPaidToAdministration($v->id);
            $totlaExpenseAll = OtherExpenses::getExpenseTotal($v->id);

            if (!empty($dataCurrencyAll->code)) {
                if ($dataCurrencyAll->code == 'USD')
                    $totalInUsd += str_replace(',', '', $totlaExpenseAll);
                if ($dataCurrencyAll->code == 'HTG')
                    $totalInHtg += str_replace(',', '', $totlaExpenseAll);
            }
        }

        $data1 = [];
        foreach ($query1 as $key => $items) {
            $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
            $totlaExpense = OtherExpenses::getExpenseTotal($items->id);
            //$dataCurrency = Currency::getData($items->currency);
            $dataCurrency = Vendors::getDataFromPaidToAdministration($items->id);

            $action = '<div class="dropdown">';

            $delete = route('deleteotherexpensevoucher', $items->id);
            $edit = route('editotherexpense', [$items->id]);

            $action .= '<a title="Click here to print"  target="_blank" href="' . route('getprintsingleotherexpense', [$items->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($items->deleted == '0') {
                if ($permissionExpensesEdit) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }
                if ($permissionExpensesDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
            }

            $action .= '</div>';

            if ($items->expense_request == 'Requested') {
                $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $items->id . '" value="' . $items->id . '" />';
            } else {
                $checkBoxes = '';
            }

            $data1[] = [$checkBoxes, $items->id, '', date('d-m-Y', strtotime($items->exp_date)), $items->voucher_number, $items->deptName, !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-', !empty($dataCurrency->code) ? $dataCurrency->code : "-", $totlaExpense, $items->note != '' ? $items->note : '-', $items->expense_request, $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "totalUsd" => number_format($totalInUsd, 2),
            "totalHtg" => number_format($totalInHtg, 2),
            "data" => $data1,
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserversideadministrationexpense()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkExpense') {
            $expenseId = $_POST['expenseId'];
            return OtherExpensesDetails::checkExpense($expenseId);
        }
        if ($flag == 'getExpenseData') {
            $expenseId = $_POST['expenseId'];
            return json_encode(OtherExpenses::getExpenseData($expenseId));
        }
    }
}
