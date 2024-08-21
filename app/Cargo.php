<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargo';
    public $timestamps = false;
    protected $fillable = [
        'cargo_operation_type', 'consignee_name', 'consignee_address','shipper_name','awb_bl_no','no_of_units','opening_date','rental_starting_date','rental_ending_date','rental','rental_cost','contract_months','rental_paid_status','arrival_date','nature_of_goods','prod_date','prod_description','pro_expense','to_bill_gdes','to_bill_usd','credit_gdes_usd','information','shipper_address','weight','hawb_hbl_no','no_of_pieces','sent_on','sent_by','billing_party','consolidate_flag','consolidate_count','pro_expense_gdes','pro_expense_usd','credit_gdes','credit_usd','flag_package_container','no_of_container','file_number','custom_file_number','file_name','agent_id','warehouse','cash_credit','display_notification','display_notification_warehouse','warehouse_status','display_notification_admin','warehouse_user','display_notification_warehouse_invoice','display_notification_admin_invoice','invoice_status_changed_by','shipment_received_date','notification_date_time','updated_by','cashier_id','shipment_delivered_date','display_notification_cashier','created_by','inspection_date', 'file_close', 'unit_of_file', 'close_unclose_date', 'close_unclose_by', 'cargo_master_scan_status', 'reason_for_return'
    ];

    static function getSuppliers()
    {
    	$dataConsignee = DB::table('cargo')->select(['consignee_name'])->where('deleted',0)->where('status',1)->get()->toArray();
    	$i = 0;
    	$final1 = array();
        foreach ($dataConsignee as $vl) {
        	$fData['label'] = $vl->consignee_name;
            $fData['value'] = $vl->consignee_name;
            $final1[] = $fData;
        }
        return json_encode($final1);
    }

    static function getCargoData($id)
    {
        $dataCargo = DB::table('cargo')->where('id',$id)->first();
        return $dataCargo;
    }

    static function getCargoTypeUsingFileNumber($fileNumber)
    {
        $dataCargo = DB::table('cargo')->where('id',$id)->first();
        return $dataCargo;
    }

    static function checkFileAssgned($cargoId)
    {
        $dataCargo = DB::table('cargo')->where('id',$cargoId)->first();
        $assigned = '';
        if($dataCargo->consolidate_flag == 1)
        {
            if(empty($dataCargo->billing_party) || empty($dataCargo->warehouse) || empty($dataCargo->cash_credit))
                $assigned = 'no';    
        }
        else if(empty($dataCargo->billing_party) || empty($dataCargo->cash_credit))
        {
            $assigned = 'no';
        }
        else
        {
            $assigned = 'yes';
        }

        return $assigned;

    }
}
