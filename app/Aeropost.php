<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Aeropost extends Model
{
    protected $table = 'aeropost';
    public $timestamps = false;
    protected $fillable = [
        'date', 'manifest_no', 'tracking_no', 'from_location', 'from_address', 'from_phone', 'from_city', 'destination_port', 'destination_city', 'consignee', 'consignee_id', 'consignee_address', 'consignee_phone', 'account', 'airline', 'flight_date_time', 'description', 'route', 'price', 'total_pieces', 'real_weight', 'shipment_real_weight', 'volumetric_weight', 'declared_value', 'freight', 'total_freight', 'insurance', 'custom_value', 'aeropost_scan_status', 'warehouse', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted', 'deleted_by', 'delete_at', 'piece', 'file_number', 'billing_party', 'warehouse_status', 'shipment_status', 'shipment_status_changed_by', 'shipment_received_date', 'shipment_incomplete_date', 'shipment_shortshipped_date', 'shipment_delivered_date', 'inspection_flag', 'inspection_date', 'inspection_by', 'custom_file_number', 'custom_invoice_number', 'release_by', 'release_by_customer', 'release_by_css_agent', 'release_by_css_driver', 'move_to_nonbounded_wh', 'nonbounded_wh_confirmation', 'nonbounded_wh_confirmation_by', 'nonbounded_wh_confirmation_on', 'delivery_boy', 'move_to_nonbounded_wh_by', 'move_to_nonbounded_wh_on', 'delivery_boy_assigned_by', 'delivery_boy_assigned_on', 'inspection_file_status', 'delivery_status', 'reason_for_return', 'file_close', 'display_notification_nonbounded_wh', 'display_notification_nonbounded_wh_datetime', 'master_file_number', 'close_unclose_date', 'close_unclose_by', 'master_aeropost_id'
    ];

    static function getAeropostData($id)
    {
        $data = DB::table('aeropost')->where('id', $id)->first();
        return $data;
    }
}
