<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class HawbPackages extends Model
{
    protected $table = 'hawb_packages';
    public $timestamps = false;
    protected $fillable = [
        'hawb_id','cargo_id','pweight','measure_weight','pvolume','measure_volume','ppieces'
    ];

    protected function getData($id)
    {
    	$data = DB::table('hawb_packages')->where('hawb_id',$id)->first();
    	return $data;
    }

}
