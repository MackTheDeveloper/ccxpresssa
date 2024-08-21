<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class AeropostMaster extends Model
{
    protected $table = 'aeropost_master';
    public $timestamps = false;
    protected $fillable = [
        'arrival_date', 'tracking_number', 'file_number', 'house_aeropost_files', 'weight', 'measure_weight', 'volume', 'measure_volume', 'pieces','shipper_name', 'shipper_address', 'consignee_name','consignee_address', 'agent_id', 'information', 'created_on', 'created_by', 'updated_on', 'updated_by', 'deleted', 'deleted_on', 'deleted_by', 'file_close', 'billing_party', 'close_unclose_date', 'close_unclose_by'
    ];

    protected function getAeropostMasterInvoicesOfFile($aeropostMasterId)
    {
        $invoices = DB::table('invoices')
            ->where('aeropost_master_id', $aeropostMasterId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    static function checkFileAssgned($aeropostMasterId)
    {
        $data = DB::table('aeropost_master')->where('id', $aeropostMasterId)->first();
        $assigned = '';
        if (empty($data->billing_party)) {
            $assigned = 'no';
        } else {
            $assigned = 'yes';
        }
        return $assigned;
    }

    protected function checkHawbFiles($id)
    {
        $data = array();
        $total = DB::table('aeropost')->where('master_aeropost_id', $id)->where('deleted','0')->get();
        $allDelivered = 0;
        if (count($total) > 0) {
            $allDelivered = 1;
            foreach ($total as $k => $v) {
                if ($v->aeropost_scan_status != 6 && $v->aeropost_scan_status != 13 && $v->aeropost_scan_status != 14) {
                    $allDelivered = 0;
                    break;
                }
            }
        }
        $data['total'] = count($total);
        $data['allDelivered'] = $allDelivered;
        return $data;
    }

    static function getMasterAeropostData($aeropostMasterId)
    {
        $data = DB::table('aeropost_master')->where('id', $aeropostMasterId)->first();
        return $data;
    }

    public function getConsigneeShipper($companyName)
    {
        $data = DB::table('clients')->where('client_flag','O')->where('company_name', $companyName)->first();
        return $data;
    }
}
