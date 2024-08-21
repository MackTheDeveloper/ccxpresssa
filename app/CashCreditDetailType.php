<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CashCreditDetailType extends Model
{
    protected $table = 'cashcredit_detail_type';
    public $timestamps = false;
    protected $fillable = [
        'account_type_id', 'name','status','created_at','updated_at','quickbook_account_type_id','quickbook_account_sub_type_id', 'quick_book_id', 'qb_sync'
    ];

    protected function getData($id)
    {
    	return DB::table('cashcredit_detail_type')->where('id',$id)->first();
    }
}
