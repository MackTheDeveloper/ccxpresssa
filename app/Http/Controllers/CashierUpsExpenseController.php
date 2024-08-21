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
class CashierUpsExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_courier_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $cargoExpenseDataByVoucher = DB::table('expenses')
                        //->select(DB::raw('count(*) as user_count, voucher_number'))
                        ->whereNotNull('ups_details_id')
                        ->where('deleted','0')
                        ->where('cashier_id',Auth::user()->id)
                        /* ->where(function ($query) {
                                $query->where('expense_request','Approved')
                                      ->orWhere('expense_request','Disbursement done');
                            }) */
                        ->orderBy('expense_id', 'desc')
                        ->get();
        return view("cashier-role.upsexpenses.index",['cargoExpenseDataByVoucher'=>$cargoExpenseDataByVoucher]);
    }

   

    public function  getprintviewsingleupsexpensecashier($expenseId = null,$upsId =  null,$flag = null)
    {
        $checkPermission = User::checkPermission(['change_file_expense_status_courier_import'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        if($flag == 'fromNotification')
            Expense::where('expense_id',$expenseId)->update(['display_notification_cashier_for_ups'=>0]);

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

        return view('cashier-role.upsexpenses.getprintviewsingleupsexpensecashier',['cargoExpenseData'=>$cargoExpenseData,'expenseId'=>$expenseId,'upsId'=>$upsId,'expenseStatus'=>$expenseStatus,'cashCredit'=>$cashCredit]);   
    }

    public function getprintsingleupsexpensecashier($expenseId = null,$upsId =  null)
    {
        $cargoExpenseData = DB::table('expenses')->where('expense_id',$expenseId)->get();
        $cargoExpenseDetailsData = DB::table('expense_details')->where('voucher_number',$cargoExpenseData[0]->voucher_number)->where('expense_id',$cargoExpenseData[0]->expense_id)->count();

        $scale = [290,300];

        if($cargoExpenseDetailsData == 1)
            $scale = [210,180];

        if($cargoExpenseDetailsData == 2)
            $scale = [210,185];

        if($cargoExpenseDetailsData == 3)
            $scale = [210,200];

        if($cargoExpenseDetailsData == 4)
            $scale = [210,225];
        $dataCargo = DB::table('ups_details')->where('id',$upsId)->first();
        $pdf = PDF::loadView('cashier-role.upsexpenses.printupsexpensecashier',['cargoExpenseData'=>$cargoExpenseData,'dataCargo'=>$dataCargo],[],['format' => $scale]);
        $pdf_file = $dataCargo->file_number.'_'.$expenseId.'_expense.pdf';
        $pdf_path = 'public/upsExpensePdf/'.$pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function changeupsstatusbycashier(Request $request)
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
            $modelActivities->description = $totalExpenses.'-'.$model->note;
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            // Store to QB
            $fData['flagModule'] = 'expenses';
            if(isset($_SESSION['sessionAccessToken']))
            {   
                //pre('test');
                $fData['id'] = $model->expense_id;
                $fData['module'] = '4';
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
        $dataCargo = DB::table('ups_details')->where('id', $model->ups_details_id)->first();
        $pdf = PDF::loadView('upsexpenses.printupsexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/upsExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Ups/';
        if ($dataCargo->courier_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $cargoExpenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');

        return redirect()->route('cashierupsexpenses');
    }

    
}
