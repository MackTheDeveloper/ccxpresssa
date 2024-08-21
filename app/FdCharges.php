<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class FdCharges extends Model
{
    protected $table = 'fd_charges';
    public $timestamps = false;
    protected $fillable = [
        'name','code','charge'
    ];

    protected function getData($code)
    {
    	$data = DB::table('fd_charges')->where('code',$code)->first();
    	return $data;
    }
}
