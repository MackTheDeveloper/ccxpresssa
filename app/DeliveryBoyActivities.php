<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeliveryBoyActivities extends Model
{
    protected $table = 'delivery_boy_activities';
    public $timestamps = false;
    protected $fillable = [
        'ups_id', 'aeropost_id', 'ccpack_id', 'file_number', 'date_time', 'description', 'assigned_by', 'deleted', 'delivery_boy_id'
    ];
}
