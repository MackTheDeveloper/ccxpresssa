<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CashCreditAccountType extends Model
{
    protected $table = 'cashcredit_account_type';
    public $timestamps = false;
    protected $fillable = [
         'name','status','created_at','updated_at','quickbook_account_type_id'
    ];

    protected function getData($id)
    {
    	return DB::table('cashcredit_account_type')->where('id',$id)->first();
    }
}
