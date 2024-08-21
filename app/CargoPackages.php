<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CargoPackages extends Model
{
    protected $table = 'cargo_packages';
    public $timestamps = false;
    protected $fillable = [
        'cargo_id', 'pweight', 'pvolume','ppieces'
    ];

    protected function getData($id)
    {
    	$data = DB::table('cargo_packages')->where('cargo_id',$id)->first();
    	return $data;
    }
}
