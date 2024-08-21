<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class OtherExpensesDetails extends Model
{
    protected $table = 'other_expenses_details';
    public $timestamps = false;
    protected $fillable = [
        'expense_id', 'expense_type', 'description','amount','paid_to','voucher_number','deleted'
    ];

    public static function checkExpense($id)
    {
    	$expenses = DB::table('other_expenses_details')->where('expense_id',$id)->where('deleted',0)->count();
    	return $expenses;
    }
}
