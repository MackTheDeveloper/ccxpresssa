<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDF;
class OtherExpenses extends Model
{
    protected $table = 'other_expenses';
    public $timestamps = false;
    protected $fillable = ['type',
        'created_on','created_by','voucher_number','expense_request','note','exp_date','department','currency','file_number', 'display_notification_admin','cash_credit_account', 'admin_manager_role', 'request_by_role', 'request_by', 'approved_by', 'disbursed_by', 'disbursed_datetime', 'display_notification_cashier', 'cashier_id', 'notification_date_time', 'expense_request_status_note'
    ];

    protected function getExpenseTotal($expenseId)
    {
        $countTotal = DB::table('other_expenses_details')
        ->select(DB::raw('sum(amount) as total'))
        ->where('expense_id',$expenseId)
        ->where('deleted','0')
        ->first();
        
        if(empty($countTotal->total))
            return '0.00';
        else
            return number_format($countTotal->total,2);
    }

    public static function getExpenseData($expenseId)
    {
        $dataExpense = DB::table('other_expenses')->where('id', $expenseId)->first();
        return $dataExpense;
    }
}
