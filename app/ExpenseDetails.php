<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class ExpenseDetails extends Model
{
    protected $table = 'expense_details';
    public $timestamps = false;
    protected $fillable = [
        'expense_id', 'expense_type', 'description','amount','cash_credit_account','paid_to','voucher_number','deleted'
    ];

    public static function checkExpense($id)
    {
    	$expenses = DB::table('expense_details')->where('expense_id',$id)->where('deleted',0)->count();
    	return $expenses;
    }

    public static function checkExpenseuser($id)
    {
        $expenses = DB::table('expense_details')->where('expense_id',$id)->where('paid_to',auth()->user()->id)->where('deleted',0)->count();
        return $expenses;
    }

    
}
