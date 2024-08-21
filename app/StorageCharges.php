<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorageCharges extends Model
{
    protected $table = 'storage_charges';
    public $timestamps = false;
    protected $fillable = [
        'storage_period', 'charge','status','created_at','updated_at','deleted','deleted_at','grace_period','measure_weight','measure_volume','measure'
    ];

    public function getData($id)
    {
    	$data = StorageCharges::find($id);
        return $data;
    }
}
