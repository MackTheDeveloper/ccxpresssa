<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Customs extends Model
{
    protected $table = 'customs';
    public $timestamps = false;
    protected $fillable = [
        'status','courier_id','ups_details_id','cargo_id','created_on','created_by','file_number','custom_date'
    ];

    protected function getData($upsId)
    {
    	$data = DB::table('customs')->where('ups_details_id',$upsId)->first();
    	return $data;
    }
}
