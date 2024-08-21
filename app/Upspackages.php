<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upspackages extends Model
{
    protected $table = 'ups_details_package';
    public $timestamps = false;
    protected $fillable = [
        'ups_details_id', 'record_type', 'shipment_number','package_weight','package_weight_unit','currency_code_package_revenue','currency_code_insurance_charges','currency_code_register_charges','inbound_container_number','incomplete_shipping_flag','container_flag','package_tracking_number','package_load'
    ];
}
