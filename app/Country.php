<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Country extends Model
{
    protected $table = 'country';
    public $timestamps = false;
    protected $fillable = [
        'name','status','created_at','updated_at','deleted','deleted_at'
    ];

    protected function getData($id)
    {
    	$dataCountry = DB::table('country')->where('id',$id)->first();
    	return $dataCountry;
    }
}
