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
class CashierUpsMasterExpenseController extends Controller
{
    public function  viewupsmasterexpenseforcashier($expenseId = null,$upsMasterId =  null,$flag = null)
    {
        $checkPermission = User::checkPermission(['change_file_expense_status_ups_master'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        if($flag == 'fromNotification')
            Expense::where('expense_id',$expenseId)->update(['display_notification_cashier_for_ups_master'=>0]);

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

        return view('cashier-role.ups-master-expense.viewupsmasterexpenseforcashier',['cargoExpenseData'=>$cargoExpenseData,'expenseId'=>$expenseId, 'upsMasterId'=> $upsMasterId,'expenseStatus'=>$expenseStatus,'cashCredit'=>$cashCredit]);   
    }


    public function changeupsmasterexpensestatusbycashier(Request $request)
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
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $cargoExpenseData[0]->voucher_number . '_expense.pdf', $filecontent, 'public');

        return redirect()->route('upsmasterexpenses');
    }

    
}
