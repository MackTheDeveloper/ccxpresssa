<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Clients;
use App\ExpenseDetails;
use App\Cargo;
use App\Common;
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
use App\CashCredit;
use App\Currency;
use App\Vendors;
use App\CheckGuaranteeToPay;
use Illuminate\Support\Facades\Storage;
use Config;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view("expenses.index");
    }

    public function expenserequestlisting()
    {
        $cargoExpenseDataByVoucher = DB::table('expenses')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->where('expense_details.paid_to', auth()->user()->id)
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('cargo_id')
            ->where('expenses.deleted', '0')
            //->where('expense_request','Approved')
            ->orderBy('expenses.expense_id', 'desc')
            ->groupBy('expense_details.voucher_number')
            ->get();
        return view("expenses.indexexpenserequestlisting", ['cargoExpenseDataByVoucher' => $cargoExpenseDataByVoucher]);
    }

    /* public function expensereport()
    {
        $checkPermission = User::checkPermission(['listing_cargo_expenses_reports'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $cargoExpenseDataByVoucher = DB::table('expenses')
                        //->select(DB::raw('count(*) as user_count, voucher_number'))
                        ->whereNotNull('cargo_id')
                        ->where('deleted','0')
                        //->where('expense_request','Approved')
                        ->orderBy('expense_id', 'desc')
                        ->get();
        return view("expenses.expensereport",['cargoExpenseDataByVoucher'=>$cargoExpenseDataByVoucher]);
    } */


    public function createexpenseusingawl($flag, $cargoId = null, $flagFromWhere = null, $gauranteeId = null)
    {
        $gauranteeId = $gauranteeId ?: null;
        $flagFromWhere = $flagFromWhere ?: null;
        $cargoId = $cargoId ?: null;

        $checkPermission = User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
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


        /* $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id'); */
        $dataAwbNos = DB::table('cargo')->where('deleted', 0)->get()->pluck('awb_bl_no', 'id');

        $dataFileNumber = DB::table('cargo')->where('deleted', 0)
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

        if (!empty($cargoId))
            $dataCargo = DB::table('cargo')->where('id', $cargoId)->first();
        else
            $dataCargo = new Cargo();

        $guaranteeData = [];
        if ($gauranteeId) {
            $guaranteeData = CheckGuaranteeToPay::find($gauranteeId);
        }

        return view('expenses._formexpenseusingawl', ['model' => $model, 'billingParty' => $billingParty, 'flag' => $flag, 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'voucherNo' => $voucherNo, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'cargoId' => $cargoId, 'flagFromWhere' => $flagFromWhere, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'currency' => $currency, 'cashier' => $cashier, 'dataCargo' => $dataCargo, 'guaranteeData' => $guaranteeData]);
    }


    public function storeexpenseusingawl(Request $request)
    {
        //session_start();
        $input = $request->all();
        $fileData = DB::table('cargo')->where('id', $input['file_number'])->where('deleted', 0)->first();
        //pre($input);
        //$dataExpense = DB::table('expenses')->where('voucher_number',$input['voucher_number'])->first();
        $dataExpense = array();
        if (!empty($dataExpense)) {
            //$fData['flagModule'] = 'updateExpense';
            $model = ExpenseDetails::where('expense_id', $dataExpense->expense_id)->delete();
            $model = Expense::find($dataExpense->expense_id);
            $model->fill($request->input());
            Activities::log('update', 'cargoexpense', $model);
            $input = $request->all();


            $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

            if ($dataExpense->cashier_id != $input['cashier_id']) {
                $input['display_notification_cashier'] = '1';
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

                $dataCargo = DB::table('cargo')->where('id', $dataExpense->cargo_id)->first();
                $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
                $pdf_file = $dataCargo->file_number . '_' . $dataExpense->expense_id . '_expense.pdf';
                $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
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

            //$fData['flagModule'] = 'expenses';
            $input['created_on'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = Auth::user()->id;
            $input['approved_by'] = Auth::user()->id;
            $input['request_by'] = Auth::user()->id;
            $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
            $input['display_notification_cashier'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $input['admin_managers'] = Auth::user()->id;
            $model = Expense::create($input);
            //pre($model);
            $data['id'] = $model->expense_id;
            $dataCargo = DB::table('cargo')->where('id', $_POST['cargo_id'])->first();
            if ($dataCargo->cargo_operation_type == 1)
                $data['flagExpense'] = $_POST['flag'] . ' Import - ' . $dataCargo->file_number;
            else if ($dataCargo->cargo_operation_type == 2)
                $data['flagExpense'] = $_POST['flag'] . ' Export - ' . $dataCargo->file_number;
            else
                $data['flagExpense'] = $_POST['flag'] . ' Locale - ' . $dataCargo->file_number;
            Activities::log('create', 'cargoexpense', (object) $data);


            $countexp = $_POST['count_expense'];
            $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);

            $paidTo = '';
            for ($i = 0; $i < $countexp; $i++) {
                $modelExp = new ExpenseDetails();
                $modelExp->expense_id = $model->expense_id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->expense_type = $input['expense_type'][$i];
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->paid_to = $input['paid_to'][0];
                $modelExp->save();
                $paidTo = $input['paid_to'][0];
            }

            // Store expense activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'cargo';
            $modelActivities->related_id = $model->cargo_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Expense #' . $model->voucher_number . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($dataCargo->consolidate_flag == 1 && $dataCargo->unit_of_file == 'www') {
                // Add expense of house files
                // Get House Files of master
                $createdExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->first();
                $houseFiles = DB::table('hawb_files')->whereIn('id', explode(',', $dataCargo->hawb_hbl_no))->get();
                $packageData = DB::table('cargo_packages')->where('cargo_id', $_POST['cargo_id'])->first();

                $masterFileWeight = $packageData->pweight;

                $flagWeight = 0;
                if ($dataCargo->unit_of_file == 'w') {
                    $flagWeight = 1;
                    //$totalWeightOrVolumne = $packageData->pweight;
                } else {
                    //$totalWeightOrVolumne = $packageData->pvolume;
                }
                //$totalExpenses = Expense::getExpenseTotal($model->expense_id);
                //$perUnitExpense = $totalExpenses / $totalWeightOrVolumne;
                // Calculate the expense

                foreach ($houseFiles as $k => $v) {
                    $housePackageData = DB::table('hawb_packages')->where('hawb_id', $v->id)->first();
                    $sharedPercentageHouseFile = $housePackageData->pweight * 100 / $masterFileWeight;
                    $sumOfMasterFileExpense = DB::table('expense_details')
                        ->select(DB::raw("SUM(amount) as sumOfMasterFileExpense"))
                        ->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')
                        ->where('expenses.cargo_id', $createdExpenseData->cargo_id)->first();

                    $checkCostSharedExpense = DB::table('expenses')
                        ->select(DB::raw("expense_details.id as expenseDetailId"))
                        ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                        ->where('cost_shared', 1)->where('house_file_id', $v->id)->first();

                    if (!empty($checkCostSharedExpense)) {
                        DB::table('expense_details')->where('id', $checkCostSharedExpense->expenseDetailId)->update(['amount' => $sumOfMasterFileExpense->sumOfMasterFileExpense * $sharedPercentageHouseFile / 100]);
                    } else {
                        $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
                        if (empty($getLastExpense))
                            $inputHouseExpens['voucher_number'] = '1001';
                        else
                            $inputHouseExpens['voucher_number'] = $getLastExpense->voucher_number + 1;

                        if ($v->cargo_operation_type == '1')
                            $blAwb = $v->hawb_hbl_no;
                        else
                            $blAwb = $v->export_hawb_hbl_no;

                        $dataConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
                        $dataShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                        $inputHouseExpens['house_file_id'] = $v->id;
                        $inputHouseExpens['file_number'] = $v->id;
                        $inputHouseExpens['exp_date'] = $createdExpenseData->exp_date;
                        $inputHouseExpens['currency'] = $createdExpenseData->currency;
                        //$inputHouseExpens['expense_request'] = $createdExpenseData->expense_request;
                        $inputHouseExpens['expense_request'] = 'Disbursement done';
                        $inputHouseExpens['cashier_id'] = $createdExpenseData->cashier_id;
                        /* $inputHouseExpens['display_notification_cashier'] = $createdExpenseData->display_notification_cashier;
                    $inputHouseExpens['notification_date_time'] = $createdExpenseData->notification_date_time; */
                        $inputHouseExpens['consignee'] = $dataConsignee->company_name;
                        $inputHouseExpens['shipper'] = $dataShipper->company_name;
                        $inputHouseExpens['billing_party'] = $v->billing_party;
                        $inputHouseExpens['bl_awb'] = $blAwb;
                        $inputHouseExpens['note'] = $createdExpenseData->note;
                        $inputHouseExpens['request_by'] = $createdExpenseData->request_by;
                        $inputHouseExpens['approved_by'] = $createdExpenseData->approved_by;
                        $inputHouseExpens['created_by'] = $createdExpenseData->created_by;
                        $inputHouseExpens['created_on'] = gmdate("Y-m-d H:i:s");
                        $inputHouseExpens['cost_shared'] = 1;
                        $modelHouseExpense = Expense::create($inputHouseExpens);

                        $dataCostItems = DB::table('costs')->select('id', 'cost_name', 'code')->where('code', 'THC/Groupage')->first();
                        $modelExp = new ExpenseDetails();
                        $modelExp->expense_id = $modelHouseExpense->expense_id;
                        $modelExp->voucher_number = $modelHouseExpense->voucher_number;
                        $modelExp->expense_type = $dataCostItems->id;
                        $modelExp->amount = $sumOfMasterFileExpense->sumOfMasterFileExpense * $sharedPercentageHouseFile / 100;
                        $modelExp->description = $dataCostItems->cost_name;
                        $modelExp->paid_to = $paidTo;
                        $modelExp->save();
                    }
                    /* if ($flagWeight == 1)
                        $housePackageDataTotalWeightORVolume = DB::table('hawb_packages')
                            ->select(DB::raw("SUM(pweight) as houseWeightVolumeSum"))
                            ->whereIn('hawb_id', explode(',', $dataCargo->hawb_hbl_no))->first();
                    else
                        $housePackageDataTotalWeightORVolume = DB::table('hawb_packages')
                            ->select(DB::raw("SUM(pvolume) as houseWeightVolumeSum"))
                            ->whereIn('hawb_id', explode(',', $dataCargo->hawb_hbl_no))->first();

                    for ($i = 0; $i < $countexp; $i++) {
                        $modelExp = new ExpenseDetails();
                        $modelExp->expense_id = $modelHouseExpense->expense_id;
                        $modelExp->voucher_number = $modelHouseExpense->voucher_number;
                        $modelExp->expense_type = $input['expense_type'][$i];
                        if ($flagWeight == 1)
                            $modelExp->amount = ($input['amount'][$i] / $housePackageDataTotalWeightORVolume->houseWeightVolumeSum) * $housePackageData->pweight;
                        else
                            $modelExp->amount = $input['amount'][$i] / $housePackageDataTotalWeightORVolume->houseWeightVolumeSum * $housePackageData->pvolume;
                        $modelExp->description = $input['description'][$i];
                        $modelExp->paid_to = $input['paid_to'][0];
                        $modelExp->save();
                    } */
                }
            }


            if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {

                $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();

                $dataCargo = DB::table('cargo')->where('id', $model->cargo_id)->first();
                $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
                $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
                $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
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



    public function changeexpenserequeststatus()
    {
        $status = $_POST['status'];
        $changeStatus = ($status == 'Approved') ? 'Pending' : 'Approved';
        $expenseId = $_POST['expenseId'];
        $model = Expense::find($expenseId);
        $data['expense_request'] = $changeStatus;
        $model->fill($data);
        Activities::log('update', 'expenserequestchangestatus', $model);
        $displayNotification = 0;
        if ($status != $changeStatus)
            $displayNotification = 1;
        $userData = DB::table('expenses')->where('expense_id', $expenseId)->update(['expense_request' => $changeStatus, 'display_notification' => $displayNotification]);

        return 'true';
    }

    public function changeexpenserequestnumberinlisting()
    {
        $cargoId = $_POST['cargoId'];
        $countPending = Expense::getPendingExpenses($cargoId);
        return $countPending;
    }

    public function getexpenserequestnumberinlistingall()
    {
        $countPendingAll = Expense::getPendingExpensesAll();
        return $countPendingAll;
    }


    public function getexpensedata()
    {
        $flag = $_POST['flag'];
        $courierId = $_POST['courierId'];
        if ($flag == 'ups')
            $dataExpense = DB::table('expenses')->where('ups_details_id', $courierId)->where('deleted', '0')->get();
        else if ($flag == 'cargo')
            $dataExpense = DB::table('expenses')->where('cargo_id', $courierId)->where('deleted', '0')->get();
        else
            $dataExpense = DB::table('expenses')->where('courier_id', $courierId)->where('deleted', '0')->get();

        $dataExpense = json_decode($dataExpense, 1);
        return view('expenses.expensedata', ['dataExpense' => $dataExpense]);
    }

    public function getexpensedataoftoday()
    {
        $flag = $_POST['flag'];
        $courierId = $_POST['courierId'];
        if ($flag == 'ups')
            $dataExpense = DB::table('expenses')->where('ups_details_id', $courierId)->where('deleted', '0')->get();
        else if ($flag == 'cargo')
            $dataExpense = DB::table('expenses')->where('cargo_id', $courierId)->where('deleted', '0')->whereDate('created_on', '=', date('Y-m-d'))->get();
        else
            $dataExpense = DB::table('expenses')->where('courier_id', $courierId)->where('deleted', '0')->get();

        $dataExpense = json_decode($dataExpense, 1);
        return view('expenses.expensedata', ['dataExpense' => $dataExpense]);
    }


    public function editexpensevoucher(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_cargo_expenses'], '', auth()->user()->id);
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


        /* $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id'); */
        $dataAwbNos = DB::table('cargo')->where('deleted', 0)->get()->pluck('awb_bl_no', 'id');

        //$dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');
        $dataFileNumber = DB::table('cargo')->where('deleted', 0)
            ->whereNull('file_close')
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

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        return view('expenses._formeditexpenseusingawl', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'flagFromWhere' => $flagFromWhere, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'dataCargo' => $dataCargo, 'currency' => $currency, 'cashier' => $cashier]);
    }

    public function editagentexpensesbyadmin(Expense $expense, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_cargo_expenses'], '', auth()->user()->id);
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

        
        $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id');
        // $dataAwbNos = DB::table('cargo')->where('deleted', 0)->get()->pluck('awb_bl_no', 'id');
        // dd($dataAwbNos);
        //$dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');
        $dataFileNumber = DB::table('cargo')->where('deleted', 0)
            ->whereNull('file_close')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id', $id)->where('deleted', 0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
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
        $adminManagersUsers = DB::table('users')->select(['id', 'name'])->where('department', $model->admin_manager_role)->where('deleted', 0)->orderBy('id', 'desc')->pluck('name', 'id');

        $model->admin_managers = explode(',', $model->admin_managers);

        $expenseStatus = array();
        $expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        $expenseStatus['Requested'] = 'Requested';
        $expenseStatus['Disbursement done'] = 'Disbursement done';

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        return view('expenses._formeditexpenserequest', ['model' => $model, 'billingParty' => $billingParty, 'flag' => 'cargo', 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'dataExpenseDetails' => $dataExpenseDetails, 'flagFromWhere' => $flagFromWhere, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'dataCargo' => $dataCargo, 'currency' => $currency, 'adminManagersRole' => $adminManagersRole, 'adminManagersUsers' => $adminManagersUsers, 'expenseStatus' => $expenseStatus, 'cashier' => $cashier]);
    }

    public function updateexpenseusingawl(Request $request, $id)
    {
        //session_start();
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $model->fill($request->input());
        Activities::log('update', 'cargoexpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');

        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier'] = '1';
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
        $dataCargo = DB::table('cargo')->where('id', $model->cargo_id)->first();
        $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
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

    public function updateagentexpensesbyadmin(Request $request, $id)
    {
        $model = ExpenseDetails::where('expense_id', $id)->delete();
        $model = Expense::find($id);
        $oldCashier = $model->cashier_id;
        $oldExpenseStatus = $model->expense_request;
        $model->fill($request->input());
        Activities::log('update', 'cargoexpense', $model);
        $input = $request->all();
        $input['exp_date'] = !empty($input['exp_date']) ? date('Y-m-d', strtotime($input['exp_date'])) : date('Y-m-d');
        if ($oldCashier != $input['cashier_id']) {
            $input['display_notification_cashier'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }
        $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
        $input['updated_by'] = auth()->user()->id;
        if ($oldExpenseStatus != $input['expense_request']) {
            $input['display_notification_cashier'] = '1';
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

        $activityAmot = 0;
        $paidTo = '';
        for ($i = 0; $i < $countexp; $i++) {
            $modelExp = new ExpenseDetails();
            $modelExp->expense_id = $model->expense_id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->expense_type = $input['expense_type'][$i];
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->paid_to = $input['paid_to'][0];
            $modelExp->save();
            $paidTo = $input['paid_to'][0];
        }

        $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataCargo = DB::table('cargo')->where('id', $model->cargo_id)->first();
        $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
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

        if ($input['expense_request'] == 'Approved' && $dataCargo->consolidate_flag == 1 && $dataCargo->unit_of_file == 'www') {
            // Add expense of house files
            // Get House Files of masteer
            $createdExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->first();
            $houseFiles = DB::table('hawb_files')->whereIn('id', explode(',', $dataCargo->hawb_hbl_no))->get();
            $packageData = DB::table('cargo_packages')->where('cargo_id', $_POST['cargo_id'])->first();

            $masterFileWeight = $packageData->pweight;

            $flagWeight = 0;
            if ($dataCargo->unit_of_file == 'w') {
                $flagWeight = 1;
                //$totalWeightOrVolumne = $packageData->pweight;
            } else {
                //$totalWeightOrVolumne = $packageData->pvolume;
            }
            //$totalExpenses = Expense::getExpenseTotal($model->expense_id);
            //$perUnitExpense = $totalExpenses / $totalWeightOrVolumne;
            // Calculate the expense

            foreach ($houseFiles as $k => $v) {
                $housePackageData = DB::table('hawb_packages')->where('hawb_id', $v->id)->first();
                $sharedPercentageHouseFile = $housePackageData->pweight * 100 / $masterFileWeight;
                $sumOfMasterFileExpense = DB::table('expense_details')
                    ->select(DB::raw("SUM(amount) as sumOfMasterFileExpense"))
                    ->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')
                    ->where('expenses.cargo_id', $createdExpenseData->cargo_id)->first();

                $checkCostSharedExpense = DB::table('expenses')
                    ->select(DB::raw("expense_details.id as expenseDetailId"))
                    ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                    ->where('cost_shared', 1)->where('house_file_id', $v->id)->first();

                if (!empty($checkCostSharedExpense)) {
                    DB::table('expense_details')->where('id', $checkCostSharedExpense->expenseDetailId)->update(['amount' => $sumOfMasterFileExpense->sumOfMasterFileExpense * $sharedPercentageHouseFile / 100]);
                } else {
                    $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
                    if (empty($getLastExpense))
                        $inputHouseExpens['voucher_number'] = '1001';
                    else
                        $inputHouseExpens['voucher_number'] = $getLastExpense->voucher_number + 1;

                    if ($v->cargo_operation_type == '1')
                        $blAwb = $v->hawb_hbl_no;
                    else
                        $blAwb = $v->export_hawb_hbl_no;

                    $dataConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
                    $dataShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                    $inputHouseExpens['house_file_id'] = $v->id;
                    $inputHouseExpens['file_number'] = $v->id;
                    $inputHouseExpens['exp_date'] = $createdExpenseData->exp_date;
                    $inputHouseExpens['currency'] = $createdExpenseData->currency;
                    //$inputHouseExpens['expense_request'] = $createdExpenseData->expense_request;
                    $inputHouseExpens['expense_request'] = 'Disbursement done';
                    $inputHouseExpens['cashier_id'] = $createdExpenseData->cashier_id;
                    /* $inputHouseExpens['display_notification_cashier'] = $createdExpenseData->display_notification_cashier;
                $inputHouseExpens['notification_date_time'] = $createdExpenseData->notification_date_time; */
                    $inputHouseExpens['consignee'] = $dataConsignee->company_name;
                    $inputHouseExpens['shipper'] = $dataShipper->company_name;
                    $inputHouseExpens['billing_party'] = $v->billing_party;
                    $inputHouseExpens['bl_awb'] = $blAwb;
                    $inputHouseExpens['note'] = $createdExpenseData->note;
                    $inputHouseExpens['request_by'] = $createdExpenseData->request_by;
                    $inputHouseExpens['approved_by'] = $createdExpenseData->approved_by;
                    $inputHouseExpens['created_by'] = $createdExpenseData->created_by;
                    $inputHouseExpens['created_on'] = gmdate("Y-m-d H:i:s");
                    $inputHouseExpens['cost_shared'] = 1;
                    $modelHouseExpense = Expense::create($inputHouseExpens);

                    $dataCostItems = DB::table('costs')->select('id', 'cost_name', 'code')->where('code', 'THC/Groupage')->first();
                    $modelExp = new ExpenseDetails();
                    $modelExp->expense_id = $modelHouseExpense->expense_id;
                    $modelExp->voucher_number = $modelHouseExpense->voucher_number;
                    $modelExp->expense_type = $dataCostItems->id;
                    $modelExp->amount = $sumOfMasterFileExpense->sumOfMasterFileExpense * $sharedPercentageHouseFile / 100;
                    $modelExp->description = $dataCostItems->cost_name;
                    $modelExp->paid_to = $paidTo;
                    $modelExp->save();
                }
            }
        }

        Session::flash('flash_message', 'Record has been updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expense $expense, $id)
    {
        $model = ExpenseDetails::where('id', $id)->delete();
    }

    public function destroyexpensevoucher(Expense $expense, $id)
    {
        session_start();
        $record = DB::table('expenses')->where('expense_id', $id)->first();

        // Remove DECSA record if expense is created for decsa
        if($record->identification_flag == 1)
        {
            CheckGuaranteeToPay::where('id', $record->decsa_id)->update(['deleted' => '1', 'deleted_at' => gmdate("Y-m-d H:i:s")]);
        }

        //$model = Expense::where('expense_id', $id)->delete();
        $model = Expense::where('expense_id', $id)->update(['deleted' => 1, 'deleted_on' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);
        $type = '';
        if (!empty($record->cargo_id)) {
            $type = 'cargo';
            $relatedID = $record->cargo_id;
            $qbModule = '5';
        } else if (!empty($record->house_file_id)) {
            $type = 'houseFile';
            $relatedID = $record->house_file_id;
            $qbModule = '16';
        } else if (!empty($record->ups_details_id)) {
            $type = 'ups';
            $relatedID = $record->ups_details_id;
            $qbModule = '4';
        } else if (!empty($record->ups_master_id)) {
            $type = 'upsMaster';
            $relatedID = $record->ups_master_id;
            $qbModule = '21';
        } else if (!empty($record->aeropost_id)) {
            $type = 'aeropost';
            $relatedID = $record->aeropost_id;
            $qbModule = '14';
        } else if (!empty($record->aeropost_master_id)) {
            $type = 'aeropostMaster';
            $relatedID = $record->aeropost_master_id;
            $qbModule = '22';
        } else if (!empty($record->ccpack_id)) {
            $type = 'ccpack';
            $relatedID = $record->ccpack_id;
            $qbModule = '15';
        } else if (!empty($record->ccpack_master_id)) {
            $type = 'ccpackMaster';
            $relatedID = $record->ccpack_master_id;
            $qbModule = '23';
        }

        // Store expense activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = $type;
        $modelActivities->related_id = $relatedID;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Expense #' . $record->voucher_number . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        // Delete expense to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('deleteExpense',$record);
        } */

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['voucher_number'] = $record->voucher_number;
            $fData['qb_id'] = $record->quick_book_id;
            $fData['module'] = $qbModule;
            $fData['flagModule'] = 'deleteExpense';
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

    public function getcargofilenumberforprint()
    {
        $dataCargo = DB::table('cargo')->where('id', $_POST['cargoId'])->first();
        $url = '../../public/cargoExpensePdf/' . $dataCargo->file_number . '_expense.pdf';
        return $url;
    }

    public function addmoreexpense()
    {
        $model = new Expense;
        $selectedVendor = $_POST['selectedVendor'];
        $dataBilligParty = DB::table('vendors')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $dataBilligParty = json_decode($dataBilligParty, 1);
        ksort($dataBilligParty);


        $dataPaidTo = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo, 1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->get()->pluck('billing_name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');

        /* $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id'); */
        $dataAwbNos = DB::table('cargo')->where('deleted', 0)->get()->pluck('awb_bl_no', 'id');

        //$dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');
        $dataFileNumber = DB::table('cargo')->where('deleted', 0)
            ->whereNull('file_close')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');

        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', 0)->where('vendors.status', 1)->orderBy('vendors.id', 'desc')->pluck('vendorData', 'id');


        return view('expenses.addmore', ['model' => $model, 'dataBilligParty' => $dataBilligParty, 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'counter' => $_POST['counter'], 'allUsers' => $allUsers, 'selectedVendor' => $selectedVendor]);
    }

    public function addmoreexpenseforrequest()
    {
        $model = new Expense;

        $dataBilligParty = DB::table('vendors')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $dataBilligParty = json_decode($dataBilligParty, 1);
        ksort($dataBilligParty);


        $dataPaidTo = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo, 1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->get()->pluck('billing_name', 'id');

        $dataCost = DB::table('costs')
            ->select(DB::raw("id,CONCAT(cost_name,' - ',cost_billing_code) as fullcost"))
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('fullcost', 'id');

        /* $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id'); */
        $dataAwbNos = DB::table('cargo')->where('deleted', 0)->get()->pluck('awb_bl_no', 'id');

        //$dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');
        $dataFileNumber = DB::table('cargo')->where('deleted', 0)
            ->whereNull('file_close')
            ->whereNotNull('billing_party')
            ->get()->pluck('file_number', 'id');

        return view('expenses.addmoreexpenseforrequest', ['model' => $model, 'dataBilligParty' => $dataBilligParty, 'dataBillingItems' => $dataBillingItems, 'dataAwbNos' => $dataAwbNos, 'dataFileNumber' => $dataFileNumber, 'dataPaidTo' => $dataPaidTo, 'dataCost' => $dataCost, 'counter' => $_POST['counter']]);
    }



    public function expandexpenses()
    {
        if (isset($_POST['flaguser']) && !empty($_POST['flaguser'])) {
            $expenseId = $_POST['expenseId'];
            $rowId = $_POST['rowId'];

            $packageData = DB::table('expense_details')->where('expense_id', $expenseId)->where('paid_to', auth()->user()->id)->where('deleted', 0)->get();
            $data = DB::table('expenses')->where('expense_id', $expenseId)->first();
            if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'Ups')
                $dataCargo = DB::table('ups_details')->where('id', $data->ups_details_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'UpsMaster')
                $dataCargo = DB::table('ups_master')->where('id', $data->ups_master_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'houseFile')
                $dataCargo = DB::table('hawb_files')->where('id', $data->house_file_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'aeropost')
                $dataCargo = DB::table('aeropost')->where('id', $data->aeropost_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'AeropostMaster')
                $dataCargo = DB::table('aeropost_master')->where('id', $data->aeropost_master_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'ccpack')
                $dataCargo = DB::table('ccpack')->where('id', $data->ccpack_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'CcpackMaster')
                $dataCargo = DB::table('ccpack_master')->where('id', $data->ccpack_master_id)->first();
            else
                $dataCargo = DB::table('cargo')->where('id', $data->cargo_id)->first();
            return view('expenses.renderexpenses', ['packageData' => $packageData, 'rowId' => $rowId, 'dataCargo' => $dataCargo]);
        } else {

            $expenseId = $_POST['expenseId'];
            $rowId = $_POST['rowId'];

            $packageData = DB::table('expense_details')->where('expense_id', $expenseId)->where('deleted', 0)->get();
            $data = DB::table('expenses')->where('expense_id', $expenseId)->first();
            if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'Ups')
                $dataCargo = DB::table('ups_details')->where('id', $data->ups_details_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'UpsMaster')
                $dataCargo = DB::table('ups_master')->where('id', $data->ups_master_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'houseFile')
                $dataCargo = DB::table('hawb_files')->where('id', $data->house_file_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'aeropost')
                $dataCargo = DB::table('aeropost')->where('id', $data->aeropost_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'AeropostMaster')
                $dataCargo = DB::table('aeropost_master')->where('id', $data->aeropost_master_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'ccpack')
                $dataCargo = DB::table('ccpack')->where('id', $data->ccpack_id)->first();
            else if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'CcpackMaster')
                $dataCargo = DB::table('ccpack_master')->where('id', $data->ccpack_master_id)->first();
            else
                $dataCargo = DB::table('cargo')->where('id', $data->cargo_id)->first();
            return view('expenses.renderexpenses', ['packageData' => $packageData, 'rowId' => $rowId, 'dataCargo' => $dataCargo]);
        }
    }

    public function generatevoucheronsavenext()
    {
        $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
        if (empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        return $voucherNo;
    }


    public function makereadnotification($expenseId)
    {
        Expense::where('expense_id', $expenseId)->update(['display_notification' => 0]);
        return redirect('expense/expenserequestlisting');
    }

    public function getcargoreportdata()
    {
        $data = array();
        $cargoId = $_POST['cargoId'];
        $totalRevenue = DB::table('invoices')
            ->select(DB::raw('sum(invoices.balance_of) as total'))
            ->where('invoices.deleted', 0)
            ->where('invoices.payment_status', 'Paid')
            ->where('invoices.cargo_id', $cargoId)
            ->get()
            ->first();
        $data['revenueTotal'] = (!empty($totalRevenue->total) ? $totalRevenue->total : '0.00');

        $totalExpense = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Approved')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $cargoId)
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();

        $data['expenseTotal'] = (!empty($totalExpense->total) ? $totalExpense->total : '0.00');

        $data['margin'] = $totalRevenue->total - $totalExpense->total;
        return json_encode($data);
    }


    public function getprintsingleexpense($expenseId = null, $cargoId =  null, $flag = null)
    {

        if ($flag == 'fromNotification')
            Expense::where('expense_id', $expenseId)->update(['display_notification_agent' => 0]);

        //$myfile = fopen("testwrite.txt", "a"); 
        //fwrite($myfile, '--ooo--'); 
        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $dataCargo = DB::table('cargo')->where('id', $cargoId)->first();
        if (empty($dataCargo))
            $dataCargo = new Cargo;
        $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $expenseId . '_expense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
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

    public function getprintallexpense($flag = null)
    {
        $cargoExpenseData = DB::table('expenses')
            ->select(DB::raw('expenses.*,cargo.file_number'))
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->where('expenses.deleted', '0')
            ->whereNotNull('expenses.cargo_id');
        //->where('expense_request','Approved')
        //->orderBy('expenses.expense_id', 'desc')

        if ($flag != 'all') {
            $cargoExpenseData = $cargoExpenseData->where('cargo_id', $flag)->get()->toArray();
            $cargoData = DB::table('cargo')->where('id', $flag)->first();
        } else {
            $cargoExpenseData = $cargoExpenseData->get()->toArray();
            $cargoData = array();
        }


        $query1 = array();
        foreach ($cargoExpenseData as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->cargo_id;
        }

        array_multisort((array) $query1, SORT_DESC, $cargoExpenseData);



        $pdf = PDF::loadView('expenses.printallcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'flag' => $flag, 'cargoData' => $cargoData]);
        $pdf_file = 'printallexpense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'AllExpense.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function viewdetailscargoexpense($id)
    {
        $checkPermission = User::checkPermission(['viewdetails_cargo_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'cargoexpense')->orderBy('id', 'desc')->get()->toArray();
        $model = Expense::find($id);
        return view("expenses.view-details", ['model' => $model, 'activityData' => $activityData]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoExpensesEdit = User::checkPermission(['update_cargo_expenses'], '', auth()->user()->id);
        $permissionCargoExpensesDelete = User::checkPermission(['delete_cargo_expenses'], '', auth()->user()->id);

        $req = $request->all();

        $status = $req['status'];
        $expenseType = $req['expenseType'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['', 'expenses.expense_id', 'expenses.expense_id', 'exp_date', 'voucher_number', 'cargo.file_number', 'bl_awb', '', 'note', 'consignee', 'shipper', 'currency.code', '', '', 'expense_request', 'expense_type'];

        $total = Expense::selectRaw('count(*) as total')
            //->where('expenses.deleted', '0')
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->whereNotNull('expenses.cargo_id');
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
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.cargo_id');
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
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.cargo_id');
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
                $query2->where('cargo.file_number', 'like', '%' . $search . '%')
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
                $query2->where('cargo.file_number', 'like', '%' . $search . '%')
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
            $query1[$key] = $row->cargo_id;
        }
        
        array_multisort((array)$query1, SORT_DESC,$query); */

        $data1 = [];
        foreach ($query as $key => $items) {

            $dataCargo = Cargo::getCargoData($items->cargo_id);
            if (!empty($dataCargo)) {

                $invoiceOfFile = Expense::getInvoicesOfFile($items->cargo_id);
                $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
                $totlaExpense = Expense::getExpenseTotal($items->expense_id);
                //$dataCurrency = Currency::getData($items->currency); 
                $dataCurrency = Vendors::getDataFromPaidTo($items->expense_id);
                $dataClientUsingModuleId = Common::getClientDataUsingModuleId('cargo', $items->cargo_id);


                $action = '<div class="dropdown">';

                $delete =  route('deleteexpensevoucher', $items->expense_id);
                if (checkloggedinuserdata() == 'Agent') {
                    $edit =  route('editagentexpenses', [$items->expense_id, 'flagFromExpenseListing']);
                } else {
                    if ($items->request_by_role == 12 || $items->request_by_role == 10)
                        $edit =  route('editagentexpensesbyadmin', [$items->expense_id, 'flagFromExpenseListing']);
                    else
                        $edit =  route('editexpensevoucher', [$items->expense_id, 'flagFromExpenseListing']);
                }

                $action .= '<a title="Click here to print"  target="_blank" href="' . route('getprintsingleexpense', [$items->expense_id, $items->cargo_id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

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

    public function checkexistingvoucherno()
    {
        $billNo = $_POST['billNo'];
        $dataExpenses = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
        $voucherNumber = $dataExpenses->voucher_number;
        $check = DB::table('expenses')->where('voucher_number', $billNo)->where('deleted', '0')->count();
        $data = array();
        if ($check > 0) {
            $data['exist'] = '1';
            $data['billNo'] = $voucherNumber + 1;
        } else {
            $data['exist'] = '0';
        }
        return json_encode($data);
    }
}
