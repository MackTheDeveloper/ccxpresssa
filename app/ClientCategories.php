<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClientCategories extends Model
{
    protected $table = 'client_categories';
    public $timestamps = false;
    protected $fillable = [
        'name','status','created_at','updated_at','deleted','deleted_at'
    ];

    protected function getData($id)
    {
    	$dataCategory = DB::table('client_categories')->where('id',$id)->first();
    	return $dataCategory;
    }
}
