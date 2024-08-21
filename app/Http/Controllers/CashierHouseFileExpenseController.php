<?php

namespace App\Http\Controllers;

use App\Expense;
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
class CashierHouseFileExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $cargoExpenseDataByVoucher = DB::table('expenses')
                        //->select(DB::raw('count(*) as user_count, voucher_number'))
                        ->whereNotNull('house_file_id')
                        ->where('deleted','0')
                        //->where('cashier_id',Auth::user()->id)
                       /*  ->where(function ($query) {
                                $query->where('expense_request','Approved')
                                      ->orWhere('expense_request','Disbursement done');
                            }) */
                        ->orderBy('expense_id', 'desc')
                        ->get();
        return view("cashier-role.housefile-expenses.index",['cargoExpenseDataByVoucher'=>$cargoExpenseDataByVoucher]);
    }

    public function expandexpensescashier()
    {
            $expenseId = $_POST['expenseId'];
            $rowId = $_POST['rowId'];
            
            $packageData = DB::table('expense_details')->where('expense_id',$expenseId)->where('deleted',0)->get();
            $data = DB::table('expenses')->where('expense_id',$expenseId)->first();
            if(isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'Ups')
                $dataCargo = DB::table('ups_details')->where('id',$data->ups_details_id)->first();
            else
                $dataCargo = DB::table('cargo')->where('id',$data->cargo_id)->first();
            return view('cashier-role.expenses.renderexpenses',['packageData'=>$packageData,'rowId'=>$rowId,'dataCargo'=>$dataCargo]);
    }

    public function  getprintviewsinglehousefileexpensecashier($expenseId = null,$houseId =  null,$flag = null)
    {
        $checkPermission = User::checkPermission(['change_file_expense_status_cargo'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        if($flag == 'fromNotification')
            Expense::where('expense_id',$expenseId)->update(['display_notification_cashier_for_house_file_expense'=>0]);

        $cargoExpenseData = DB::table('expenses')->where('expense_id',$expenseId)->get();

        $expenseStatus = array();
        $expenseStatus['Disbursement done'] = 'Disbursement done';
        //$expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        //$expenseStatus['Requested'] = 'Requested';

        $cashCredit = DB::table('cashcredit')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit,1);
        ksort($cashCredit);

        return view('cashier-role.housefile-expenses.getprintviewsinglehousefileexpensecashier',['cargoExpenseData'=>$cargoExpenseData,'expenseId'=>$expenseId,'houseId'=>$houseId,'expenseStatus'=>$expenseStatus,'cashCredit'=>$cashCredit]);   
    }

    public function getprintsinglehousefileexpensecashier($expenseId = null,$houseId =  null)
    {
        //$myfile = fopen("testwrite.txt", "a"); 
        //fwrite($myfile, '--ooo--'); 
        $cargoExpenseData = DB::table('expenses')->where('expense_id',$expenseId)->get();


        $cargoExpenseDetailsData = DB::table('expense_details')->where('voucher_number',$cargoExpenseData[0]->voucher_number)->where('expense_id',$cargoExpenseData[0]->expense_id)->count();

        $scale = [290,300];

        if($cargoExpenseDetailsData == 1)
            $scale = [210,200];

        if($cargoExpenseDetailsData == 2)
            $scale = [210,205];

        if($cargoExpenseDetailsData == 3)
            $scale = [210,220];

        if($cargoExpenseDetailsData == 4)
            $scale = [210,245];
        
        $dataCargo = DB::table('hawb_files')->where('id',$houseId)->first();
        $pdf = PDF::loadView('cashier-role.housefile-expenses.getprintsinglehousefileexpensecashier',['cargoExpenseData'=>$cargoExpenseData,'dataCargo'=>$dataCargo],[],['format' => $scale]);
        $pdf_file = $dataCargo->file_number.'_'.$expenseId.'_expense.pdf';
        $pdf_path = 'public/houseFileExpensesPdf/'.$pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

   

    public function changehousefilestatusbycashier(Request $request)
    {
        session_start();
        $input = $request->all();
        $model = Expense::find($input['id']);
        $input['updated_by'] = Auth::user()->id;
        $input['disbursed_by'] = null;
        if($input['expense_request'] == 'Disbursement done')
        {
            $input['disbursed_by'] = Auth::user()->id;
            $input['disbursed_datetime'] = date('Y-m-d H:i:s');
            $totalExpenses = str_replace(',','',Expense::getExpenseTotal($input['id']));

            $getCashCreditData = DB::table('cashcredit')->where('id',$input['cash_credit_account'])->first();
            $finalAmt = $getCashCreditData->available_balance - $totalExpenses;
            DB::table('cashcredit')->where('id',$input['cash_credit_account'])->update(['available_balance' => $finalAmt,'qb_sync' => 0]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCredit';
            $modelActivities->related_id = $input['cash_credit_account'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $totalExpenses.'-'.$input['expense_request_status_note'];
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            // Store to QB
            $fData['flagModule'] = 'expenses';
            if(isset($_SESSION['sessionAccessToken']))
            {   
                //pre('test');
                $fData['id'] = $model->expense_id;
                $fData['module'] = '16';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model='.$newModel);

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
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $cargoExpenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');
        
        return redirect()->route('getprintviewsinglehousefileexpensecashier',[$model->expense_id,$model->house_file_id]);
    }

    
}
