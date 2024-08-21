<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class ccpack extends Model
{
	protected $table = 'ccpack';
	public $timestamps = false;
	protected $fillable = ['ccpack_operation_type','file_number','awb_number','consignee','consignee_address','consignee_telephone','shipper_name','shipper_address','shipper_telephone','no_of_pcs','weight','freight','expences','commission','arrival_date','ccpack_scan_status','warehouse','created_by','created_on','updated_by','updated_on','deleted','deleted_by','delete_on','billing_party','warehouse_status', 'shipment_status', 'shipment_status_changed_by', 'shipment_received_date', 'shipment_incomplete_date', 'shipment_shortshipped_date', 'shipment_delivered_date', 'inspection_flag', 'inspection_date', 'inspection_by', 'custom_file_number', 'custom_invoice_number', 'release_by', 'release_by_customer', 'release_by_css_agent', 'release_by_css_driver', 'move_to_nonbounded_wh', 'nonbounded_wh_confirmation', 'nonbounded_wh_confirmation_by', 'nonbounded_wh_confirmation_on', 'delivery_boy', 'move_to_nonbounded_wh_by', 'move_to_nonbounded_wh_on', 'delivery_boy_assigned_by', 'delivery_boy_assigned_on', 'inspection_file_status', 'delivery_status', 'reason_for_return', 'file_close', 'display_notification_nonbounded_wh', 'display_notification_nonbounded_wh_datetime', 'master_file_number', 'close_unclose_date', 'close_unclose_by', 'master_ccpack_id'];

	static function getccpackdetail($id){
		$ccpackData = DB::table('ccpack')->where('id',$id)->first();
		return $ccpackData;
	}

	static function getinvoicedetail($id){
		$ccpackinvoiceData = DB::table('invoices')->where('ccpack_id',$id)->first();
		return $ccpackinvoiceData;
	}

	static function getinvoiceitemdetail($id){
		$ccpackitemData = DB::table('invoice_item_details')->where('invoice_id',$id)->get();
		return $ccpackitemData;
	}
}
