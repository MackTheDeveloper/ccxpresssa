<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HawbFiles extends Model
{
    protected $table = 'hawb_files';
    public $timestamps = false;
    protected $fillable = [
        'cargo_id', 'awb_bl_no', 'hawb_hbl_no', 'agency_name', 'weight', 'no_of_pieces', 'hdate', 'consignee_name', 'consignee_address', 'shipper_name', 'shipper_address', 'opening_date', 'arrival_date', 'flag_package_container', 'no_of_container', 'information', 'file_name', 'sent_by', 'sent_on', 'cargo_operation_type', 'export_hawb_hbl_no', 'created_by', 'verify_flag', 'inspection_flag', 'rack_location', 'file_number', 'billing_party', 'warehouse_status', 'shipment_received_date', 'shipment_delivered_date', 'warehouse_user', 'inspection_date', 'shipment_status', 'shipment_status_changed_by', 'shipment_incomplete_date', 'shipment_shortshipped_date', 'custom_file_number', 'inspection_by', 'custom_invoice_number', 'release_by_customer', 'release_by_css_agent', 'release_by_css_driver', 'release_by', 'hawb_scan_status', 'file_close', 'close_unclose_date', 'close_unclose_by', 'agent_id', 'reason_for_return'
    ];

    protected function checkHawbFiles($id)
    {
        $data = array();
        $dataCargo = DB::table('cargo')->where('id', $id)->first();
        $dataHawbIds = explode(',', $dataCargo->hawb_hbl_no);

        $total = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->where('deleted', '0')->get();
        $allDelivered = 0;
        if (count($total) > 0) {
            $allDelivered = 1;
            foreach ($total as $k => $v) {
                if ($v->hawb_scan_status != 6 && $v->hawb_scan_status != 13 && $v->hawb_scan_status != 14) {
                    $allDelivered = 0;
                    break;
                }
            }
        }
        $data['total'] = count($total);
        $data['allDelivered'] = $allDelivered;

        return $data;
    }

    public function getHawbFilesNumbers($id)
    {
        $dataCargo = DB::table('cargo')->where('id', $id)->first();
        $dataHawbIds = explode(',', $dataCargo->hawb_hbl_no);

        if ($dataCargo->cargo_operation_type == 1) {
            $numbers = DB::table('hawb_files')
                ->select(DB::raw('group_concat(hawb_hbl_no) as Hawbnumbers'))
                ->whereIn('id', $dataHawbIds)
                ->first();
        } else {
            $numbers = DB::table('hawb_files')
                ->select(DB::raw('group_concat(export_hawb_hbl_no) as Hawbnumbers'))
                ->whereIn('id', $dataHawbIds)
                ->first();
        }
        return $numbers->Hawbnumbers;
    }

    static function getHouseFileData($id)
    {
        $data = DB::table('hawb_files')->where('id', $id)->first();
        return $data;
    }

    public function getNumberFromCargoFile($operationType, $numbers)
    {
        if ($operationType == 1) {
            $numbers = DB::table('hawb_files')
                ->select(DB::raw('group_concat(hawb_hbl_no) as Hawbnumbers'))
                ->whereIn('id', $numbers)
                ->first();
        } else {
            $numbers = DB::table('hawb_files')
                ->select(DB::raw('group_concat(export_hawb_hbl_no) as Hawbnumbers'))
                ->whereIn('id', $numbers)
                ->first();
        }
        return $numbers->Hawbnumbers;
    }
}
