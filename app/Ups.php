<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use PDF;
use Config;

class Ups extends Model
{
    protected $table = 'ups_details';
    public $timestamps = false;
    protected $fillable = [
        'record_type', 'tdate', 'company', 'no_manifeste', 'awb_number', 'destination', 'origin', 'shipment_number', 'shipper_name', 'shipper_address', 'shipper_address_2', 'shipper_contact', 'shipper_telephone', 'shipper_city', 'transporter', 'arrival_date', 'consignee_name', 'consignee_contact_name', 'affretement', 'consignee_address', 'consignee_city_state', 'consignee_telephone', 'nature_marchandise', 'nbr_pcs', 'weight', 'unit', 'dim_weight', 'dim_unit', 'declared_value', 'freight', 'freight_currency', 'Insurance', 'customs_value', 'us_fees', 'htg_fees', 'arrival', 'fc', 'fd', 'pp', 'ups_scan_status', 'inprogress_scan_status', 'other_status', 'commission_amount_approve', 'description', 'last_action', 'last_action_flag', 'file_number', 'file_name', 'agent_id', 'display_notification', 'billing_party', 'warehouse', 'cash_credit', 'courier_operation_type', 'notification_date_time', 'package_type', 'warehouse_status', 'shipment_status', 'shipment_status_changed_by', 'shipment_received_date', 'shipment_incomplete_date', 'shipment_shortshipped_date', 'shipment_delivered_date', 'inspection_flag', 'inspection_date', 'inspection_by', 'custom_file_number', 'custom_invoice_number', 'release_by', 'release_by_customer', 'release_by_css_agent', 'release_by_css_driver', 'move_to_nonbounded_wh', 'nonbounded_wh_confirmation', 'nonbounded_wh_confirmation_by', 'nonbounded_wh_confirmation_on', 'delivery_boy', 'move_to_nonbounded_wh_by', 'move_to_nonbounded_wh_on', 'delivery_boy_assigned_by', 'delivery_boy_assigned_on', 'inspection_file_status', 'delivery_status', 'reason_for_return', 'file_close', 'display_notification_nonbounded_wh', 'display_notification_nonbounded_wh_datetime', 'master_file_number', 'master_ups_id', 'close_unclose_date', 'close_unclose_by'
    ];

    public static function checkPakckages($id)
    {
        $users = DB::table('ups_details_package')->where('ups_details_id', $id)->count();
        return $users;
    }

    public static function getBillingTerm($id)
    {
        $whichSession = '';
        $upsData = DB::table('ups_details')->where('id', $id)->first();
        if (!empty($upsData)) {
            if ($upsData->fc == 1)
                $whichSession = 'FC';
            if ($upsData->fd == 1)
                $whichSession = 'FD';
            if ($upsData->pp == 1)
                $whichSession = 'PP';
        } else {
            $whichSession = '';
        }


        return $whichSession;
    }

    public static function checkAwbExist($awbNumber)
    {
        $count = DB::table('ups_details')->where('awb_number', $awbNumber)->first();
        if (!empty($count)) {
            return $count->id;
        } else {
            return 0;
        }
    }

    static function getUpsData($id)
    {
        $dataUps = DB::table('ups_details')->where('id', $id)->first();
        return $dataUps;
    }

    static function getUpsDataWithInvoice($id)
    {
        $dataUps = DB::table('ups_details')->where('id', $id)->first();

        $UpsClientId = '';
        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        if (!empty($dataClient))
            $UpsClientId = $dataClient->id;

        $countFChasInvoice = DB::table('invoices')
            ->where('invoices.ups_id', $id)
            ->where('invoices.bill_to', '<>', $UpsClientId)
            ->count();
        /* if ($dataUps->fc == '1' && $countFChasInvoice == 0)
            return '1';
        else
            return '0'; */
        $data = array();
        if ($dataUps->fc == '1' && $dataUps->courier_operation_type == '1' && $countFChasInvoice == 0)
            $data['fc'] = '1';
        else if ($dataUps->pp == '1' && $dataUps->courier_operation_type == '2' && $countFChasInvoice == 0)
            $data['pp'] = '1';

        return $data;
    }

    static function checkFileAssgned($upsId)
    {
        $dataCargo = DB::table('ups_details')->where('id', $upsId)->first();
        $assigned = '';
        if (empty($dataCargo->billing_party) || empty($dataCargo->cash_credit)) {
            $assigned = 'no';
        } else {
            $assigned = 'yes';
        }

        return $assigned;
    }

    static function getConsigneeName($id)
    {
        $consignee = DB::table('clients')->where('id', $id)->first();
        if (!empty($consignee))
            $consigneeName = $consignee->company_name;
        else
            $consigneeName = '';

        return $consigneeName;
    }

    static function getCommission($id)
    {
        //pre($id);
        $commissionData = DB::table('ups_freight_commission')->where('ups_file_id', $id)->first();
        //pre(!empty($commissionData));
        if (!empty($commissionData)) {
            $commission = $commissionData->commission;
        } else {
            $commission = 0;
        }
        //pre($commission);
        return $commission;
    }

    static function getCommissionData($id)
    {
        $commissionData = DB::table('ups_freight_commission')->where('ups_file_id', $id)->first();
        return $commissionData;
    }

    static function getNatureOfShipment($id)
    {
        $upsData = DB::table('ups_details')->where('id', $id)->first();
        $natureOfShipment = '';
        if ($upsData->description == 'letter') {
            $natureOfShipment = 'LTR';
        } else if ($upsData->description == 'document') {
            $natureOfShipment = 'DOC';
        } else {
            $natureOfShipment = 'PKG';
        }
        return $natureOfShipment;
    }

    static function generateUpsInvoice($id)
    {
        $data = DB::table('ups_details')->where('id', $id)->first();
        $dataInvoice['ups_id'] = $data->id;
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
        if (empty($getLastInvoice)) {
            $dataInvoice['bill_no'] = 'UP-5001';
        } else {
            $ab = 'UP-';
            $ab .= substr($getLastInvoice->bill_no, 3) + 1;
            $dataInvoice['bill_no'] = $ab;
        }

        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        $dataConsignee = DB::table('clients')->where('id', $data->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $data->shipper_name)->first();
        $dataInvoice['bill_to'] = $dataClient->id;
        $dataInvoice['date'] = date('Y-m-d');
        $dataInvoice['email'] = $dataClient->email;
        $dataInvoice['telephone'] = $dataClient->phone_number;
        $dataInvoice['shipper'] = $dataShipper->company_name;
        $dataInvoice['consignee_address'] = $dataConsignee->company_name;
        $dataInvoice['file_no'] = $data->file_number;
        $dataInvoice['awb_no'] = $data->awb_number;
        $dataInvoice['type_flag'] = $data->courier_operation_type == 1 ? 'IMPORT' : 'EXPORT';
        $dataInvoice['weight'] = $data->weight;
        $dataInvoice['currency'] = '1';
        $dataInvoice['created_by'] = auth()->user()->id;
        $dataInvoice['created_at'] = date('Y-m-d h:i:s');
        $dataInvoices = UpsInvoices::Create($dataInvoice);
        $dataBilling = DB::table('billing_items')->whereIn('item_code', ['C1071','C1071/ Commission fret aerien (UPS)'])->first();
        $dataCommission = DB::table('ups_freight_commission')->where('ups_file_id', $id)->first();
        $commission = '0.00';
        if (!empty($dataCommission))
            $commission = $dataCommission->commission;
        if (!empty($dataBilling)) {
            $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
            $dataInvoiceItems['fees_name'] = $dataBilling->id;
            $dataInvoiceItems['item_code'] = $dataBilling->item_code;
            $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
            $dataInvoiceItems['quantity'] = 1;
            $dataInvoiceItems['unit_price'] = $commission;
            $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];

            $modelUpdateUpsInvoice = UpsInvoices::find($dataInvoices->id);
            $modelUpdateUpsInvoice->sub_total = $dataInvoiceItems['total_of_items'];
            $modelUpdateUpsInvoice->total = $dataInvoiceItems['total_of_items'];
            $modelUpdateUpsInvoice->balance_of = $dataInvoiceItems['total_of_items'];
            $modelUpdateUpsInvoice->update();
        }
        $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
        UpsInvoiceItemDetails::Create($dataInvoiceItems);

        $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
        $pdf = PDF::loadView('upsinvoices.printupsinvoice', ['invoice' => (array) $dataAll]);
        $pdf_file = 'printUpsInvoice_' . $dataInvoices->id . '.pdf';
        $pdf_path = 'public/upsInvoices/' . $pdf_file;
        $pdf->save($pdf_path);
    }

    public function getWarehouseData($id, $tblName)
    {
        $data = DB::table($tblName)->where('id', $id)->first();
        if ($data->move_to_nonbounded_wh == 1)
            return Config::get('app.nonBoundedWHName');
        else {
            if (!empty($data->warehouse)) {
                $dataWarehouse = DB::table('warehouse')->where('id', $data->warehouse)->first();
                return $dataWarehouse->name;
            } else {
                //return Config::get('app.boundedWHName');
                return "-";
            }
        }
    }
}
