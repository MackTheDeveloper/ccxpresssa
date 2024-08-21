<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $table = 'courier_detail';
    public $timestamps = false;
    protected $fillable = [
        'consignee_name', 'no_manifeste', 'awe_tracking','origin_country_code','origin_city','nbr_pcs','weight','declared_value','freight','freight_certificate','trucking','insurance','value_custom_purpose','charges_in_usd','charges_in_haiti','freight_collect','free_domicile','freight_prepaid','file_reference','credit','invest_in_htg','invest_in_usd'
    ];
}
