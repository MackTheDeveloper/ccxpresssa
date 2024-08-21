<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CashCredit extends Model
{
    protected $table = 'cashcredit';
    public $timestamps = false;
    protected $fillable = ['quick_book_id',
        'account_type', 'detail_type', 'name','description','status','created_at','updated_at','opening_balance','as_of','available_balance','currency', 'currency_code', 'qb_sync'
    ];

    protected function getbalance($id)
    {
        if(!empty($id))
        {
        $data = DB::table('cashcredit')->where('id',$id)->first();
        return '('.$data->currency_code.') '.$data->available_balance;
        }
        else
        {
            return '';
        }
    }

    protected function getCashCreditData($id)
    {
        $data = DB::table('cashcredit')->where('id',$id)->first();
        return $data;
    }
    
}
