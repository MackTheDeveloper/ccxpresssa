<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CargoContainers extends Model
{
    protected $table = 'cargo_containers';
    public $timestamps = false;
    protected $fillable = [
        'cargo_id', 'container_number'
    ];

     protected function getData($id)
    {
    	$data = DB::table('cargo_containers')
    	->select(DB::raw('group_concat(container_number) as containerNumbers'))
    	->where('cargo_id',$id)
    	->first();
    	
    	return $data;
    }
}
