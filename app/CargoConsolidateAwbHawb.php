<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CargoConsolidateAwbHawb extends Model
{
    protected $table = 'cargo_consolidate_awb_hawb';
    public $timestamps = false;
    protected $fillable = [
        'cargo_id', 'awb_bl_no', 'hawb_hbl_no','agency_name','weight','no_of_pieces'
    ];
}
