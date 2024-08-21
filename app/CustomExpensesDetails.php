<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class CustomExpensesDetails extends Model
{
    protected $table = 'custom_expense_details';
    public $timestamps = false;
    protected $fillable = [
        'expense_id', 'voucher_number', 'expense_type','description','amount','deleted'
    ];

    public static function checkExpense($id)
    {
    	$expenses = DB::table('custom_expense_details')->where('expense_id',$id)->where('deleted',0)->count();
    	return $expenses;
    }
}
