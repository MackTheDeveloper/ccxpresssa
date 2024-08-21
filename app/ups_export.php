<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ups_export extends Model
{
    protected $table = 'ups_export_details';
    public $timestamps = false;
    protected $fillable = ['import_id','awb_number','description','shipper_account_no','courier_operation_type','shipper_name','shipper_contract','shipper_address','shipper_address_2','shipper_city_state_zip','consignee_name','consignee_address','consignee_city_state','destination_country','consignee_telephone','weight','unit','dim_weight','dim_weight_unit','declared_value','currency','nbr_pcs','HS_CODE','freight','ups_status','freight_currency','fc','fd','pp','created_on','created_by','deleted','deleted_on','deleted_by','updated_on','updated_by'];
}
