<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Currency extends Model
{
    protected $table = 'currency';
    public $timestamps = false;
    protected $fillable = [
        'name','code','status','created_at','updated_at','deleted','deleted_at', 'quick_book_id', 'qb_sync'
    ];

    static function getData($id)
    {
    	$dataCurrency = DB::table('currency')->where('id',$id)->where('deleted','0')->first();
    	return $dataCurrency;
    }

    static function getDataUsingCode($code)
    {
        $dataCurrency = DB::table('currency')->where('code',$code)->first();
        return $dataCurrency;
    }
}
