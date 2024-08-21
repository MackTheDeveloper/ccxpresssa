<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class AeropostFreightCommission extends Model
{
    protected $table = 'aeropost_freight_commission';
    public $timestamps = false;
    public $fillable = ['aeropost_id','freight','commission','created_by','created_at','updated_by','updated_at','deleted','deleted_by','delete_at'];

    public function getCommission($id)
    {
    	$commission = DB::table('aeropost_freight_commission')->where('aeropost_id',$id)->first();
    	return !empty($commission->commission) ? $commission->commission : '0.00';
    }
}
