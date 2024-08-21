<?php

namespace App\Http\Controllers;

use App\Cargo;
use App\Ups;
use App\Aeropost;
use App\ccpack;
use Illuminate\Http\Request;
use App\User;
use App\CargoProductDetails;
use App\CargoConsolidateAwbHawb;
use App\CargoContainers;
use App\CargoPackages;
use App\HawbFiles;
use App\Invoices;
use App\InvoiceItemDetails;
use App\VerificationInspectionNote;
use Session;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Illuminate\Support\Facades\Validator;
use Response;
use PDF;
use Config;
use QuickBooksOnline\API\Facades\Invoice;

class WarehouseCargoController extends Controller
{
    public function warehousecargoall()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id', auth()->user()->id)
            ->first();

        $wh = explode(',', $getWarehouseOfUser->warehouses);

        $dataWarehouseCargo = DB::table('cargo')
            //->whereIn('warehouse',$wh)
            ->where('consolidate_flag', 1)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('cargo_operation_type', '1')
                    ->orWhere('cargo_operation_type', '2');
            })
            ->orderBy('id', 'desc')
            ->get();

        return view("warehouse-role.cargo.index", ['dataWarehouseCargo' => $dataWarehouseCargo]);
    }

    public function warehousecargoimportsajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id', auth()->user()->id)
            ->first();

        $wh = explode(',', $getWarehouseOfUser->warehouses);

        $dataWarehouseCargo = DB::table('cargo')
            //->whereIn('warehouse', $wh)
            ->where('consolidate_flag', 1)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where('cargo_operation_type', '1')
            ->orderBy('id', 'desc')
            ->get();

        return view("warehouse-role.cargo.importindexajax", ['dataWarehouseCargo' => $dataWarehouseCargo]);
    }

    public function warehousecargoexportsajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id', auth()->user()->id)
            ->first();

        $wh = explode(',', $getWarehouseOfUser->warehouses);

        $dataWarehouseCargo = DB::table('cargo')
            //->whereIn('warehouse', $wh)
            ->where('consolidate_flag', 1)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where('cargo_operation_type', '2')
            ->orderBy('id', 'desc')
            ->get();

        return view("warehouse-role.cargo.exportindexajax", ['dataWarehouseCargo' => $dataWarehouseCargo]);
    }

    public function warehousecargoallajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id', auth()->user()->id)
            ->first();

        $wh = explode(',', $getWarehouseOfUser->warehouses);

        $dataWarehouseCargo = DB::table('cargo')
            //->whereIn('warehouse', $wh)
            ->where('consolidate_flag', 1)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('cargo_operation_type', '1')
                    ->orWhere('cargo_operation_type', '2');
            })
            ->orderBy('id', 'desc')
            ->get();

        return view("warehouse-role.cargo.cargoallajax", ['dataWarehouseCargo' => $dataWarehouseCargo]);
    }

    public function warehousehawbfiles()
    {
        $data = DB::table('hawb_files')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("warehouse-role.hawb-files.index", ['data' => $data]);
    }

    public function cargowarehouseflow($masterId = null, $houseId = null)
    {
        $model = Cargo::find($masterId);
        $modelCargoHouse = HawbFiles::find($houseId);
        $HouseAWBData = DB::table('hawb_files')->where('id', $houseId)->get();
        return view('warehouse-role.cargo.warehouseflow', ['model' => $model, 'id' => $model->cargo_operation_type, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId, 'masterId' => $masterId, 'modelCargoHouse' => $modelCargoHouse]);
    }

    public function viewcargodetailforwarehouse($id, $flag = null, $houseId = null)
    {
        if ($flag == 'fromNotification')
            Cargo::where('id', $id)->update(['display_notification_warehouse' => 0]);

        $model = Cargo::find($id);
        $dataHawbIds = explode(',', $model->hawb_hbl_no);

        //$HouseAWBData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->get();
        $HouseAWBData = DB::table('hawb_files')
            ->selectRaw('hawb_files.*,c1.company_name as consigneeName,c2.company_name as shipperName,c3.company_name as billingParty')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'hawb_files.billing_party')
            ->whereIn('hawb_files.id', $dataHawbIds)
            ->get();

        return view('warehouse-role.cargo.viewcargodetailforwarehouse', ['model' => $model, 'id' => $model->cargo_operation_type, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId]);
    }

    public function verificationinspection($id, $flag = null)
    {
        $flag = $_POST['flag'];
        $status = $_POST['status'];
        $changeStatus = ($status == '1') ? '0' : '1';
        $hawbId = $_POST['hawbId'];
        $model = HawbFiles::find($hawbId);
        if ($flag == 'verifyFlag')
            $data = DB::table('hawb_files')->where('id', $hawbId)->update(['verify_flag' => $changeStatus]);
        else
            $data = DB::table('hawb_files')->where('id', $hawbId)->update(['inspection_flag' => $changeStatus]);
        return "true";
    }


    public function assignwarehousestatustohousefilebywarehouseuser(Request $request)
    {
        $input = $request->all();

        if ($input['flagModule'] == 'cargo') {
            $model = HawbFiles::find($input['id']);
        }

        $oldStatus = $model->warehouse_status;
        $newtatus = $input['warehouse_status'];
        $model->warehouse_user = auth()->user()->id;

        if (!empty($newtatus)) {
            if ($newtatus == '1') {
                $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));
                $input['shipment_delivered_date'] = null;
            } else if ($newtatus == '2') {
                HawbFiles::where('id', $input['id'])->update(['rack_location' => null]);
                VerificationInspectionNote::where('hawb_id', $input['id'])->delete();
            } else if ($newtatus == '3') {
                HawbFiles::where('id', $input['id'])->update(['rack_location' => null]);
                $input['shipment_delivered_date'] = date('Y-m-d', strtotime($input['shipment_delivered_date']));
                $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));

                // Generate storage charge invoice
                /* $dataInvoice['cargo_id'] = $input['cargo_id'];
                $dataInvoice['date'] = date('Y-m-d');
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->first();
                if (empty($getLastInvoice)) {
                    $dataInvoice['bill_no'] = 'CA-5001';
                } else {
                    $ab = 'CA-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $dataInvoice['bill_no'] = $ab;
                }
                
                $dataClient = DB::table('clients')->where('id',$model->billing_party)->first();
                $dataConsignee = DB::table('clients')->where('id', $model->consignee_name)->first();
                $dataShipper = DB::table('clients')->where('id', $model->shipper_name)->first();
                $dataInvoice['bill_to'] = $model->billing_party;
                $dataInvoice['date'] = date('Y-m-d');
                $dataInvoice['email'] = $dataClient->email;
                $dataInvoice['telephone'] = $dataClient->phone_number;
                $dataInvoice['shipper'] = $dataShipper->company_name;
                $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                $dataInvoice['file_no'] = $model->file_number;
                $dataInvoice['awb_no'] = $model->cargo_operation_type == '1' ? $model->hawb_hbl_no : $model->export_hawb_hbl_no;
                $dataInvoice['type_flag'] = $model->cargo_operation_type == '1' ? 'IMPORT' : 'EXPORT';
                $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id',$model->id)->first();
                $dataInvoice['weight'] = $modelCargoPackage->pweight;
                $dataInvoice['currency'] = $dataClient->currency;
                $dataInvoice['hawb_hbl_no'] = $model->id;
                $dataInvoice['housefile_module'] = 'cargo';
                $dataInvoice['created_by'] = auth()->user()->id;
                $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                
                $dataBilling = DB::table('billing_items')->where('item_code','SCC')->first();
                $storageChargeData = DB::table('storage_charges')->where('measure','M')->first();

                $fromDate = $model->arrival_date;
                $toDate = $model->shipment_delivered_date;

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = strtotime($toDate) - $your_date;

                $dayDifference = round($datediff / (60 * 60 * 24));
                $chageDays = $dayDifference-$storageChargeData->grace_period;
                if($chageDays > 0)
                {
                    $dataInvoiceItems['quantity'] = $chageDays;
                    $dataInvoiceItems['unit_price'] = $storageChargeData->charge;
                    $dataInvoiceItems['fees_name_desc'] = 'Storage Charge : Duration : '.date('d-m-Y',strtotime($fromDate)).' - '.date('d-m-Y',strtotime($toDate)).'('.$dayDifference.' days - '.$storageChargeData->grace_period.' Grace days = '.$chageDays.' Days)';
                }
                else
                {
                    $dataInvoiceItems['quantity'] = '0.00';
                    $dataInvoiceItems['unit_price'] = '0.00';
                    $dataInvoiceItems['fees_name_desc'] = 'Storage Charge : No Charge (In Grace Period)';
                }

                $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                $dataInvoice['sub_total'] = $dataInvoiceItems['total_of_items'];
                $dataInvoice['total'] = $dataInvoiceItems['total_of_items'];
                $dataInvoice['balance_of'] = $dataInvoiceItems['total_of_items'];
                $dataInvoices = Invoices::Create($dataInvoice);

                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                $dataInvoiceItems['fees_name'] = $dataBilling->id;
                $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                InvoiceItemDetails::Create($dataInvoiceItems); */
            }
        }


        if ($input['inspection_flag'] == '1') {
            $input['inspection_date'] = date('Y-m-d', strtotime($input['inspection_date']));
        }


        $model->update($input);
        Session::flash('flash_message', 'Status has been changed successfully');
        return $model->warehouse_status;
    }

    public function generatehousefileinvoice()
    {

        $moduleId = $_POST['moduleId'];
        $flagModule = $_POST['flagModule'];
        $id = $_POST['id'];
        if ($flagModule == 'cargo') {
            $model = HawbFiles::find($id);
            $dataInvoice['cargo_id'] = $moduleId;
            $dataInvoice['hawb_hbl_no'] = $model->id;
            $dataInvoice['housefile_module'] = 'cargo';
            $dataInvoice['awb_no'] = $model->cargo_operation_type == '1' ? $model->hawb_hbl_no : $model->export_hawb_hbl_no;
            $dataInvoice['type_flag'] = $model->cargo_operation_type == '1' ? 'IMPORT' : 'EXPORT';
            $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $model->id)->first();
            $dataInvoice['weight'] = $modelCargoPackage->pweight;

            $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $id)->first();
            $measureWeight = $modelCargoPackage->measure_weight;
            $measureVolume = $modelCargoPackage->measure_volume;

            $pWeight = $modelCargoPackage->pweight;
            $pVolume = $modelCargoPackage->pvolume;
        } else if ($flagModule == 'ups') {
            $model = Ups::find($id);
            $dataInvoice['ups_id'] = $moduleId;
            $dataInvoice['hawb_hbl_no'] = null;
            $dataInvoice['housefile_module'] = 'ups';
            $dataInvoice['awb_no'] = $model->awb_number;
            $dataInvoice['type_flag'] = $model->courier_operation_type == '1' ? 'IMPORT' : 'EXPORT';
            $dataInvoice['weight'] = $model->weight;

            $measureWeight = 'k';
            $measureVolume = 'm';

            $pWeight = $model->weight;
            $pVolume = '0.00';
        } else if ($flagModule == 'aeropost') {
            $model = Aeropost::find($id);
            $dataInvoice['aeropost_id'] = $moduleId;
            $dataInvoice['hawb_hbl_no'] = null;
            $dataInvoice['housefile_module'] = 'aeropost';
            $dataInvoice['awb_no'] = $model->tracking_no;
            $dataInvoice['type_flag'] = 'EXPORT';
            $dataInvoice['weight'] = $model->real_weight;

            $measureWeight = 'k';
            $measureVolume = 'm';

            $pWeight = $model->real_weight;
            $pVolume = '0.00';
        } else if ($flagModule == 'ccpack') {
            $model = ccpack::find($id);
            $dataInvoice['ccpack_id'] = $moduleId;
            $dataInvoice['hawb_hbl_no'] = null;
            $dataInvoice['housefile_module'] = 'ccpack';
            $dataInvoice['awb_no'] = $model->awb_number;
            $dataInvoice['type_flag'] = $model->ccpack_operation_type == '1' ? 'IMPORT' : 'EXPORT';
            $dataInvoice['weight'] = $model->weight;

            $measureWeight = 'k';
            $measureVolume = 'm';

            $pWeight = $model->weight;
            $pVolume = '0.00';
        }


        if ($_POST['revise'] == '1') {
            $dataInvoice['date'] = date('Y-m-d');
        } else {
            $dataInvoice['date'] = empty($_POST['invoiceDate']) ? date('Y-m-d') : date('Y-m-d', strtotime($_POST['invoiceDate']));
            $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
            if (empty($getLastInvoice)) {
                if ($flagModule == 'cargo')
                    $dataInvoice['bill_no'] = 'HF-5001';
                else if ($flagModule == 'ups')
                    $dataInvoice['bill_no'] = 'UP-5001';
                else if ($flagModule == 'aeropost')
                    $dataInvoice['bill_no'] = 'AP-5001';
                else if ($flagModule == 'ccpack')
                    $dataInvoice['bill_no'] = 'CC-5001';
            } else {
                if ($flagModule == 'cargo')
                    $ab = 'HF-';
                else if ($flagModule == 'ups')
                    $ab = 'UP-';
                else if ($flagModule == 'aeropost')
                    $ab = 'AP-';
                else if ($flagModule == 'ccpack')
                    $ab = 'CC-';

                $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                $dataInvoice['bill_no'] = $ab;
            }
        }

        if ($flagModule == 'cargo' || $flagModule == 'ups') {
            $dataClient = DB::table('clients')->where('id', $model->billing_party)->first();
            $dataConsignee = DB::table('clients')->where('id', $model->consignee_name)->first();
            $dataShipper = DB::table('clients')->where('id', $model->shipper_name)->first();
            $dataInvoice['bill_to'] = $model->billing_party;
            $dataInvoice['email'] = $dataClient->email;
            $dataInvoice['telephone'] = $dataClient->phone_number;
            $dataInvoice['shipper'] = $dataShipper->company_name;
            $dataInvoice['consignee_address'] = $dataConsignee->company_name;
        }
        if ($flagModule == 'aeropost') {
            $dataClient = DB::table('clients')->where('id', $model->billing_party)->first();
            $dataConsignee = DB::table('clients')->where('id', $model->consignee)->first();
            $dataInvoice['bill_to'] = $model->billing_party;
            $dataInvoice['email'] = $dataClient->email;
            $dataInvoice['telephone'] = $dataClient->phone_number;
            $dataInvoice['shipper'] = $model->from_address;
            $dataInvoice['consignee_address'] = $dataConsignee->company_name;
        }
        if ($flagModule == 'ccpack') {
            $dataClient = DB::table('clients')->where('id', $model->billing_party)->first();
            $dataConsignee = DB::table('clients')->where('id', $model->consignee)->first();
            $dataShipper = DB::table('clients')->where('id', $model->shipper_name)->first();
            $dataInvoice['bill_to'] = $model->billing_party;
            $dataInvoice['email'] = $dataClient->email;
            $dataInvoice['telephone'] = $dataClient->phone_number;
            $dataInvoice['shipper'] = $dataShipper->company_name;
            $dataInvoice['consignee_address'] = $dataConsignee->company_name;
        }

        $dataInvoice['file_no'] = $model->file_number;
        $dataInvoice['currency'] = $dataClient->currency;
        $dataInvoice['created_by'] = auth()->user()->id;
        $dataInvoice['created_at'] = date('Y-m-d h:i:s');

        $dataBilling = DB::table('billing_items')->where('item_code', 'SCC')->first();
        $storageChargeData = DB::table('storage_charges')->where('measure', 'M')->first();

        //$fromDate = $model->arrival_date;
        //$toDate = $model->shipment_received_date;

        //$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkInvoiceIsGeneratedOrNot($id,'housefile');

        $fromDate = $model->shipment_received_date;
        if ($_POST['revise'] == '1')
            $toDate = date('Y-m-d');
        else
            $toDate = $dataInvoice['date'];

        $now = time();
        $your_date = strtotime($fromDate);
        $datediff = strtotime($toDate) - $your_date;
        /* pre($fromDate,1);
        pre($toDate,1); */

        $dayDifference = round($datediff / (60 * 60 * 24));
        /* pre($dayDifference,1); */

        $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
        $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
        $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
        if ($chageDaysWeight > 0)
            $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
        else
            $totalChargeWeight = '0.00';

        /* pre($chargeWeightPerKgOrPound,1);
        pre($modelCargoPackage->pweight,1);
        pre($chageDaysWeight,1);
        pre($totalChargeWeight,1); */

        $storageChargeDataVolume = DB::table('storage_charges')->where('measure', strtoupper($measureVolume))->first();
        $chageDaysVolume = $dayDifference - $storageChargeDataVolume->grace_period;
        $chargeVolumePerMeterOrFeet = $storageChargeDataVolume->charge;
        if ($chageDaysVolume > 0)
            $totalChargeVolume = $chargeVolumePerMeterOrFeet * $pVolume * $chageDaysVolume;
        else
            $totalChargeVolume = '0.00';

        /* pre($chargeVolumePerMeterOrFeet,1);
        pre($modelCargoPackage->pvolume,1);
        pre($chageDaysVolume,1);
        pre($totalChargeVolume); */


        if ($totalChargeVolume > $totalChargeWeight) {
            $finalChargeDays = $chageDaysVolume;
            $finalCharge = $chargeVolumePerMeterOrFeet * $pVolume;
            $totalCharge = $finalChargeDays * $finalCharge;
            $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataVolume->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
        } else if ($totalChargeWeight > $totalChargeVolume) {
            $finalChargeDays = $chageDaysWeight;
            $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
            $totalCharge = $finalChargeDays * $finalCharge;
            $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
        } else if ($totalChargeWeight == $totalChargeVolume && ($totalChargeWeight > 0 || $totalChargeVolume > 0)) {
            $finalChargeDays = $chageDaysWeight;
            $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
            $totalCharge = $finalChargeDays * $finalCharge;
            $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
        } else {
            $finalChargeDays = '0.00';
            $finalCharge = '0.00';
            $totalCharge = $finalChargeDays * $finalCharge;
            $desc = 'Storage Charge : No Charge (In Grace Period)';
        }

        // Check invoice has been created or not
        if ($flagModule == 'cargo') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.hawb_hbl_no', $id)
                ->where('invoices.housefile_module', 'cargo')
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();

            $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                ->select(DB::raw('invoices.date'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.hawb_hbl_no', $id)
                ->where('invoices.housefile_module', 'cargo')
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->orderBy('invoices.id', 'desc')
                ->first();
        } else if ($flagModule == 'ups') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.ups_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();

            $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                ->select(DB::raw('invoices.date'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.ups_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->orderBy('invoices.id', 'desc')
                ->first();
        } else if ($flagModule == 'aeropost') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.aeropost_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();

            $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                ->select(DB::raw('invoices.date'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.aeropost_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->orderBy('invoices.id', 'desc')
                ->first();
        } else if ($flagModule == 'ccpack') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalAddedDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.ccpack_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();

            $dataHouseFileInvoicesForCheckLastInvoice = DB::table('invoices')
                ->select(DB::raw('invoices.date'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.ccpack_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->orderBy('invoices.id', 'desc')
                ->first();
        }

        if (!empty($dataHouseFileInvoicesForCheckLastInvoice)) {
            $your_date = strtotime($dataHouseFileInvoicesForCheckLastInvoice->date);
            $datediff = strtotime($toDate) - $your_date;

            $dayDifference = round($datediff / (60 * 60 * 24));
            $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($dataHouseFileInvoicesForCheckLastInvoice->date)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days)';
        }

        $dataInvoiceItems['quantity'] = $finalChargeDays - $dataHouseFileInvoices->totalAddedDays;
        $dataInvoiceItems['unit_price'] = $finalCharge;
        $dataInvoiceItems['fees_name_desc'] = $desc;
        $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];

        $dataInvoice['sub_total'] = $dataInvoiceItems['total_of_items'];
        $dataInvoice['total'] = $dataInvoiceItems['total_of_items'];
        $dataInvoice['balance_of'] = $dataInvoiceItems['total_of_items'];
        //$dataInvoices = Invoices::Create($dataInvoice);
        if ($flagModule == 'cargo') {
            if ($_POST['revise'] == '1')
                $dataInvoices = Invoices::update(['hawb_hbl_no' => $id, 'housefile_module' => 'cargo'], $dataInvoice);
            else
                $dataInvoices = Invoices::create($dataInvoice);
        } else if ($flagModule == 'ups') {
            if ($_POST['revise'] == '1')
                $dataInvoices = Invoices::update(['ups_id' => $id, 'housefile_module' => 'ups'], $dataInvoice);
            else
                $dataInvoices = Invoices::create($dataInvoice);
        } else if ($flagModule == 'aeropost') {
            if ($_POST['revise'] == '1')
                $dataInvoices = Invoices::update(['aeropost_id' => $id, 'housefile_module' => 'aeropost'], $dataInvoice);
            else
                $dataInvoices = Invoices::create($dataInvoice);
        } else if ($flagModule == 'ccpack') {
            if ($_POST['revise'] == '1')
                $dataInvoices = Invoices::update(['ccpack_id' => $id, 'housefile_module' => 'ccpack'], $dataInvoice);
            else
                $dataInvoices = Invoices::create($dataInvoice);
        }


        $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
        $dataInvoiceItems['fees_name'] = $dataBilling->id;
        $dataInvoiceItems['item_code'] = $dataBilling->item_code;
        //InvoiceItemDetails::Create($dataInvoiceItems);
        if ($_POST['revise'] == '1')
            InvoiceItemDetails::update(['invoice_id' => $dataInvoices->id], $dataInvoiceItems);
        else
            InvoiceItemDetails::create($dataInvoiceItems);


        $invoiceData = DB::table('invoices')->where('id', $dataInvoices->id)->first();
        $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => (array) $invoiceData]);
        $pdf_file = 'printInvoice_' . $dataInvoices->id . '.pdf';
        $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
        $pdf->save($pdf_path);

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $type = '';
        $relatedId = '';
        if ($flagModule == 'cargo') {
            $type = 'houseFile';
            $relatedId = $dataInvoices->hawb_hbl_no;
        } else if ($flagModule == 'ups') {
            $type = 'ups';
            $relatedId = $dataInvoices->ups_id;
        } else if ($flagModule == 'aeropost') {
            $type = 'aeropost';
            $relatedId = $dataInvoices->aeropost_id;
        } else if ($flagModule == 'ccpack') {
            $type = 'ccpack';
            $relatedId = $dataInvoices->ccpack_id;
        }
        $modelActivities->type = $type;
        $modelActivities->related_id = $relatedId;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $dataInvoices->bill_no . ' has been generated';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        return url('/') . '/' . $pdf_path;
    }

    public function assignwarehousestatusbywarehouseuser(Request $request)
    {
        $input = $request->all();
        $model = Cargo::find($input['id']);
        $oldStatus = $model->warehouse_status;
        $newtatus = $input['warehouse_status'];
        $model->warehouse_user = auth()->user()->id;
        if ($oldStatus != $newtatus) {
            $input['display_notification_admin'] = '1';
            $input['display_notification'] = '1';
            $input['display_notification_cashier'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }

        if (!empty($input['shipment_received_date']))
            $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));

        if ($newtatus == '1')
            $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));

        if ($newtatus == '3') {
            $loc = explode(',', $model->hawb_hbl_no);
            HawbFiles::whereIn('id', $loc)->update(['rack_location' => null]);
            $input['shipment_delivered_date'] = date("Y-m-d");
        }

        if ($newtatus != '1') {
            $input['shipment_received_date'] = null;
        }
        if ($newtatus == '2') {
            $loc = explode(',', $model->hawb_hbl_no);
            HawbFiles::whereIn('id', $loc)->update(['rack_location' => null, 'inspection_flag' => '0', 'verify_flag' => '0']);
            foreach ($loc as $key => $value) {
                VerificationInspectionNote::where('hawb_id', $value)->delete();
            }
        }

        $model->update($input);
        Session::flash('flash_message', 'Status has been changed successfully');
        return $model->warehouse_status;
    }

    public function assignstatusbywarehousehousefile(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $oldStatus = $model->hawb_scan_status;
        $model->update($input);
        if (!empty($model)) {
            $newStatus = $model->hawb_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'houseFile';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;

                if (!empty($oldStatus))
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . " )";
                else
                    $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . " )";

                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'houseFile';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }
        Session::flash('flash_message', 'Status has been updated successfully');
        return redirect('hawbfile/view/' . $input['id']);
    }

    public function assigncargohousefilestatusbywarehouseuser(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $oldStatus = $model->hawb_scan_status;
        $model->update($input);
        if (!empty($model)) {
            $newStatus = $model->hawb_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'houseFile';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;

                if (!empty($oldStatus))
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                else
                    $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";

                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'houseFile';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        Session::flash('flash_message', 'Status has been updated successfully');
        return redirect('cargo/cargowarehouseflow/' . $input['masterId'] . '/' . $input['id']);
    }

    public function assigncargomasterfilestatusbyadmin(Request $request)
    {
        $input = $request->all();
        $model = Cargo::find($input['id']);
        $oldStatus = $model->cargo_master_scan_status;
        $model->update($input);
        if (!empty($model)) {
            $newStatus = $model->cargo_master_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'cargo';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;

                if (!empty($oldStatus))
                    $modelActivities->description = "File Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                else
                    $modelActivities->description = "File Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";

                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }
        Session::flash('flash_message', 'Status has been updated successfully');
        if (checkloggedinuserdata() == 'Warehouse') {
            return redirect('cargo/viewcargodetailforwarehouse/' . $input['id']);
        } else {
            return redirect('cargo/viewcargo/' . $input['id'] . '/' . $model->cargo_operation_type);
        }
    }

    public function addracklocationinwarehousefile($id = null)
    {
        $model = $model = HawbFiles::find($id);
        $rackLocations = DB::table('hawb_files')->select(DB::raw('GROUP_CONCAT(rack_location) AS rackLocations'))->whereNotNull('rack_location')->where('deleted', '0')->get()->toArray();
        $loc = explode(',', $rackLocations[0]->rackLocations);

        $availableLocations = DB::table('storage_racks')
            ->whereNotIn('id', $loc)
            ->where('deleted', '0')
            ->where('status', '1')
            ->get()
            ->toArray();

        $dataAvailableLocations =  array();
        foreach ($availableLocations as $key => $value) {
            $dataAvailableLocations[$value->id] = Config::get('app.rackDepartment')[$value->rack_department] . ' - ' . $value->main_section . ' - ' . $value->sub_section . ' - ' . $value->location_number;
        }

        return view('warehouse-role.cargo.formaddracklocation', ['dataAvailableLocations' => $dataAvailableLocations, 'model' => $model, 'id' => $id]);
    }

    public function storeracklocationinwarehousefile(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];
        $racks = $input['racks'];
        $racksImploded = implode(',', $input['racks']);
        unset($input['racks']);
        $input['rack_location'] = $racksImploded;
        $model = HawbFiles::find($id);
        $model->update($input);

        $rackLocationData = DB::table('storage_racks')
            ->whereIn('id', $racks)
            ->get();

        $racksLocations = '';
        foreach ($rackLocationData as $k => $v) {
            $racksLocations .= Config::get('app.rackDepartment')[$v->rack_department] . ' - ' . $v->main_section . ' - ' . $v->sub_section . ' - ' . $v->location_number . ', ';
        }
        return rtrim($racksLocations, ', ');
    }

    public function releeaseracklocationinwarehousefile(Request $request)
    {
        HawbFiles::where('id', $_POST['HawbId'])->update(['rack_location' => null]);
        return 'true';
    }

    public function addverificationnote($cargoHouseAWBId, $flag = null)
    {
        $houseAWBData = HawbFiles::find($cargoHouseAWBId);
        return view('warehouse-role.cargo.formaddvarificationnotes', ['houseAWBData' => $houseAWBData, 'flag' => $flag]);
    }

    public function saveverificationnote(Request $request)
    {
        $input = $request->all();
        $model = VerificationInspectionNote::create($input);
        return 'true';
    }

    public function viewverificationnote($cargoHouseAWBId, $flag = null)
    {
        $houseAWBNotesData = DB::table('verification_inspection_notes')->where('hawb_id', $cargoHouseAWBId)->where('flag_note', $flag)->orderBy('id', 'desc')->get()->toArray();
        return view('warehouse-role.cargo.viewvarificationnotes', ['houseAWBNotesData' => $houseAWBNotesData]);
    }

    public function fileamendmentbywarehouse()
    {
        $flagModule = $_POST['flagModule'];
        $arrivalData = date('Y-m-d', strtotime($_POST['arrival_date']));
        $inspectionDate = date('Y-m-d', strtotime($_POST['inspection_date']));
        $id = $_POST['id'];
        if ($flagModule == 'cargo') {
            Cargo::where('id', $id)->update(['arrival_date' => $arrivalData, 'inspection_date' => $inspectionDate]);
            $dataCargo = Cargo::where('id', $id)->first();
            HawbFiles::whereIn('id', explode(',', $dataCargo->hawb_hbl_no))->update(['arrival_date' => $arrivalData]);
        } else if ($flagModule == 'ups')
            Ups::where('id', $id)->update(['arrival_date' => $arrivalData]);
        else if ($flagModule == 'aeropost')
            Aeropost::where('id', $id)->update(['date' => $arrivalData]);
        else if ($flagModule == 'ccpack')
            ccpack::where('id', $id)->update(['arrival_date' => $arrivalData]);

        return '1';
    }

    public function step1shipmentstatus(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $input['shipment_received_date'] = !empty($input['shipment_received_date']) ? date('Y-m-d', strtotime($input['shipment_received_date'])) : null;
        $input['shipment_incomplete_date'] = !empty($input['shipment_incomplete_date']) ? date('Y-m-d', strtotime($input['shipment_incomplete_date'])) : null;
        $input['shipment_shortshipped_date'] = !empty($input['shipment_shortshipped_date']) ? date('Y-m-d', strtotime($input['shipment_shortshipped_date'])) : null;
        $input['shipment_status_changed_by'] = auth()->user()->id;
        $input['warehouse_status'] = $input['shipment_status'] == '1' ? '1' : null;
        $model->update($input);

        if (!empty($input['shipment_notes'])) {
            $inputNotes['flag_note'] = 'V';
            $inputNotes['hawb_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['shipment_status_changed_by']);

        $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'V')->where('hawb_id', $input['id'])->orderBy('id', 'desc')->get();

        $ajaxData['status'] = $input['shipment_status'] == '1' ? 'Received' : Config::get('app.shipmentStatus')[$input['shipment_status']];
        $ajaxData['on'] = $input['shipment_status'] == '1' ? date('d-m-Y', strtotime($model->shipment_received_date)) : ($input['shipment_status'] == '2' ? date('d-m-Y', strtotime($model->shipment_incomplete_date)) : date('d-m-Y', strtotime($model->shipment_shortshipped_date)));
        $ajaxData['changedBy'] = $dataUser->name;
        if (count($dataComments) > 0)
            $ajaxData['comments'] = $dataComments;
        else
            $ajaxData['comments'] = '';


        return view('warehouse-role.cargo.step1shipmentstatusajax', ['ajaxData' => $ajaxData]);
    }

    public function step2racklocation(Request $request)
    {
        $input = $request->all();
        $id = $input['id'];
        if (!isset($input['rack_location'])) {
            $model = HawbFiles::find($id);
            $input['rack_location'] = null;
            $model->update($input);

            return "";
        } else {
            $racks = $input['rack_location'];
            $racksImploded = implode(',', $input['rack_location']);
            unset($input['racks']);
            $input['rack_location'] = $racksImploded;
            $model = HawbFiles::find($id);
            $model->update($input);

            $modelActivities = new Activities;
            $modelActivities->type = 'houseFile';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = "Rack has been assigned";
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            $rackLocationData = DB::table('storage_racks')
                ->whereIn('id', $racks)
                ->get();

            $racksLocations = '';
            foreach ($rackLocationData as $k => $v) {
                $racksLocations .= Config::get('app.rackDepartment')[$v->rack_department] . ' - ' . $v->main_section . ' - ' . $v->sub_section . ' - ' . $v->location_number . ' | ';
            }
            return rtrim($racksLocations, ' | ');
        }
    }

    public function step3custominspection(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $input['inspection_flag'] = $input['inspection_flag'] == 'true' ? '1' : '0';
        $input['inspection_date'] = date('Y-m-d', strtotime($input['inspection_date']));
        $input['inspection_by'] = auth()->user()->id;
        $model->update($input);

        $modelActivities = new Activities;
        $modelActivities->type = 'houseFile';
        $modelActivities->related_id = $input['id'];
        $modelActivities->user_id   = auth()->user()->id;
        if ($input['inspection_flag'] == '1')
            $modelActivities->description = "Custom Inspection | <strong>Done</strong> | On " . date('d-m-Y', strtotime($input['inspection_date'])) . " | Custom File Number : " . $model->custom_file_number;
        else
            $modelActivities->description = "Custom Inspection | <strong>Pending</strong>";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (!empty($input['shipment_notes'])) {
            $inputNotes['flag_note'] = 'I';
            $inputNotes['hawb_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['inspection_by']);

        $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'I')->where('hawb_id', $input['id'])->orderBy('id', 'desc')->get();

        $ajaxData['status'] = Config::get('app.inspectionFileWarehouse')[$input['inspection_flag']];
        $ajaxData['on'] = $input['inspection_flag'] == '1' ? date('d-m-Y', strtotime($model->inspection_date)) : '-';
        $ajaxData['changedBy'] = $dataUser->name;
        if (count($dataComments) > 0)
            $ajaxData['comments'] = $dataComments;
        else
            $ajaxData['comments'] = '';


        return view('warehouse-role.cargo.step3custominspection', ['ajaxData' => $ajaxData]);
    }

    public function step4invoiceandpayment(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $model->update($input);
    }

    public function step5shipmentrelease(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $input['warehouse_status'] = '3';
        $input['shipment_delivered_date'] = date('Y-m-d');
        $input['release_by'] = auth()->user()->id;
        $model->update($input);

        $modelActivities = new Activities;
        $modelActivities->type = 'houseFile';
        $modelActivities->related_id = $input['id'];
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "Shipment has been <strong>Delivered</strong> | On " . date('d-m-Y');
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['release_by']);

        $ajaxData['id'] = $input['id'];
        $ajaxData['status'] = 'Done';
        $ajaxData['on'] = date('d-m-Y', strtotime($input['shipment_delivered_date']));
        if (!empty($dataUser))
            $ajaxData['changedBy'] = $dataUser->name;
        else
            $ajaxData['changedBy'] = '-';

        return view('warehouse-role.cargo.step5shipmentreleaseajax', ['ajaxData' => $ajaxData]);
    }

    public function assigncargomasterfilestatusbywarehouse(Request $request)
    {
        $input = $request->all();
        $model = Cargo::find($input['id']);
        $oldStatus = $model->cargo_master_scan_status;
        if ($input['cargo_master_scan_status'] == '6') {
            $input['warehouse_status'] = '3';
            $input['shipment_delivered_date'] = date('Y-m-d');
        }
        $model->update($input);

        if (!empty($input['shipment_notes'])) {
            $inputNotes['flag_note'] = 'R';
            $inputNotes['cargo_master_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        if (!empty($model)) {
            $newStatus = $model->cargo_master_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'cargo';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;

                if (!empty($oldStatus))
                    $modelActivities->description = "File Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                else
                    $modelActivities->description = "File Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";

                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }
    }

    public function releasereceipt(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $masterFileData = Cargo::find($model->cargo_id);
        $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $input['id'])->first();
        $checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkInvoiceIsGeneratedOrNot($input['id'], 'housefile');
        $pdf = PDF::loadView('warehouse-role.cargo.releasereceipt', ['model' => $model, 'masterFileData' => $masterFileData, 'modelCargoPackage' => $modelCargoPackage, 'checkInvoiceIsGeneratedOrNot' => $checkInvoiceIsGeneratedOrNot]);
        $pdf_file = $model->file_number . '_release-receipt.pdf';
        $pdf_path = 'public/releaseReceipts/cargo/' . $pdf_file;
        $pdf->save($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoInvoicesAdd = User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($column == 2) {
            $column = 0;
        }

        $orderby = ['cargo.id', 'cargo.id', 'file_number', 'c3.company_name', 'cargo_master_scan_status', 'users.name', 'opening_date', 'awb_bl_no', 'c1.company_name', 'c2.company_name'];

        $total = Cargo::selectRaw('count(*) as total')
            //->where('deleted', '0')
            ->where('consolidate_flag', 1)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('cargo_operation_type', '1')
                    ->orWhere('cargo_operation_type', '2');
            });
        if (!empty($fileStatus)) {
            $total = $total->where('cargo_master_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('cargo')
            ->selectRaw('cargo.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'cargo.billing_party')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id')
            //->where('cargo.deleted', '0')
            ->where('consolidate_flag', 1)
            ->where('cargo.status', 1)
            ->where(function ($query) {
                $query->where('cargo.cargo_operation_type', '1')
                    ->orWhere('cargo.cargo_operation_type', '2');
            });
        if (!empty($fileStatus)) {
            $query = $query->where('cargo_master_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('cargo')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'cargo.billing_party')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id')
            //->where('cargo.deleted', '0')
            ->where('consolidate_flag', 1)
            ->where('cargo.status', 1)
            ->where(function ($query) {
                $query->where('cargo.cargo_operation_type', '1')
                    ->orWhere('cargo.cargo_operation_type', '2');
            });
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('cargo_master_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $dataBillingParty = app('App\Clients')->getClientData($value->billing_party);
            $agentData = app('App\User')->getUserName($value->agent_id);
            $consigneeData = app('App\Clients')->getClientData($value->consignee_name);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $shipperData = app('App\Clients')->getClientData($value->shipper_name);
            $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
            $agent = !empty($agentData->name) ? $agentData->name : '-';

            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printcargofile", [$value->id, $value->cargo_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($value->deleted == '0') {
                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCargoInvoicesAdd) {
                    $action .= '<li><a href="' . route('createhousefileinvoice', 'cargo') . '">Add Invoice</a></li>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $closedDetail = '';
            if ($value->file_close == 1) {
                $dataUserCloseFile = DB::table('users')->where('id', $value->close_unclose_by)->first();
                $closedDetail .= !empty($value->close_unclose_date) ? date('d-m-Y', strtotime($value->close_unclose_date)) : '-';
                $closedDetail .= ' | ';
                $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
            }

            $data[] = [$value->id, '', $value->file_number, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($value->cargo_master_scan_status) ? $value->cargo_master_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($value->cargo_master_scan_status) ? $value->cargo_master_scan_status : '-'] : '-', $agent, date('d-m-Y', strtotime($value->opening_date)), !empty($value->awb_bl_no) ? $value->awb_bl_no : '-', $consignee, $shipper, ($value->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function listbydatatableserversidehawbfile(Request $request)
    {
        $permissionCargoHAWBEdit = User::checkPermission(['update_cargo_hawb'], '', auth()->user()->id);
        $permissionCargoHAWBDelete = User::checkPermission(['delete_cargo_hawb'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($column == 2) {
            $column = 0;
        }

        $orderby = ['hawb_files.id', '', 'cargo_operation_type', 'file_number', 'c3.company_name', 'hawb_scan_status', 'opening_date', '', 'c1.company_name', 'c2.company_name', '', '', 'hawb_files.shipment_received_date', 'hawb_files.shipment_delivered_date', '', ''];

        $total = HawbFiles::selectRaw('count(*) as total');
        //->where('deleted', '0')
        if (!empty($fileStatus)) {
            $total = $total->where('hawb_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('hawb_files')
            ->selectRaw('hawb_files.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'hawb_files.billing_party');
        //->where('hawb_files.deleted', '0');
        if (!empty($fileStatus)) {
            $query = $query->where('hawb_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('hawb_files')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'hawb_files.billing_party');
        //->where('hawb_files.deleted', '0');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('hawb_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->where('hawb_hbl_no', 'like', '%' . $search . '%')
                            ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->where('hawb_hbl_no', 'like', '%' . $search . '%')
                            ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $dataBillingParty = app('App\Clients')->getClientData($value->billing_party);
            $consigneeData = app('App\Clients')->getClientData($value->consignee_name);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $shipperData = app('App\Clients')->getClientData($value->shipper_name);
            $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
            $shipmentReceivedDate = ($value->shipment_status == 1 && !empty($value->shipment_received_date)) ? date('d-m-Y', strtotime($value->shipment_received_date)) : '-';
            $shipmentDeliveredDate = !empty($value->shipment_delivered_date) ? date('d-m-Y', strtotime($value->shipment_delivered_date)) : '-';
            $noOfDays = app('App\Invoices')->getTotalChargableDays($value->id, 'housefile');
            $totalStorageCharge = app('App\Invoices')->getTotalChargeTillToday($value->id, 'housefile');
            $dataConsolidate  = DB::table('cargo')
                ->select(DB::raw('group_concat(file_number) as MasterFiles'))
                ->whereRaw("find_in_set($value->id,hawb_hbl_no)")
                ->first();


            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printhawbfiles", [$value->id, $value->cargo_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete =  route('deletehawbfile', $value->id);
            $edit =  route('edithawbfile', $value->id);
            if ($value->deleted == '0') {
                if ($permissionCargoHAWBEdit) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }
                if ($permissionCargoHAWBDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $closedDetail = '';
            if ($value->file_close == 1) {
                $dataUserCloseFile = DB::table('users')->where('id', $value->close_unclose_by)->first();
                $closedDetail .= !empty($value->close_unclose_date) ? date('d-m-Y', strtotime($value->close_unclose_date)) : '-';
                $closedDetail .= ' | ';
                $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
            }

            $data[] = [$value->id, $value->cargo_id, $value->cargo_operation_type == 1 ? 'Import' :  'Export', $value->file_number, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($value->hawb_scan_status) ? $value->hawb_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($value->hawb_scan_status) ? $value->hawb_scan_status : '-'] : '-', date('d-m-Y', strtotime($value->opening_date)), $value->cargo_operation_type == 1 ? $value->hawb_hbl_no : $value->export_hawb_hbl_no, $consignee, $shipper, !empty($dataConsolidate->MasterFiles) ? $dataConsolidate->MasterFiles : 'Not Assigned', !empty($value->warehouse_status) ? Config::get('app.warehouseStatus')[$value->warehouse_status] : '-', $shipmentReceivedDate, $shipmentDeliveredDate, $noOfDays, number_format((float) $totalStorageCharge, 2), ($value->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }
}
