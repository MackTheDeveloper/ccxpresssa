<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class NatureOfServices extends Model
{
    protected $table = 'nature_of_services';
    public $timestamps = false;
    protected $fillable = [
        'name','status','created_at','updated_at','deleted','deleted_at'
    ];

    protected function getData($id)
    {
    	$dataNature = DB::table('nature_of_services')->where('id',$id)->first();
    	return $dataNature;
    }
}
