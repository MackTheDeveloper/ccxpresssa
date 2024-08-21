<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class PaymentTerms extends Model
{
    protected $table = 'payment_terms';
    public $timestamps = false;
    protected $fillable = [
        'title','status','created_at','updated_at','deleted','deleted_at'
    ];

    protected function getData($id)
    {
    	$dataPaymentTerms = DB::table('payment_terms')->where('id',$id)->first();
    	return $dataPaymentTerms;
    }

    static function getDataUsingName($name)
    {
        $data = DB::table('payment_terms')->where('title',$name)->first();
        return $data;
    }
}
