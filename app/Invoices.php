<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDF;

class Invoices extends Model
{
    protected $table = 'invoices';
    public $timestamps = false;
    protected $fillable = [
        'bill_to', 'date', 'bill_no', 'email', 'telephone', 'shipper', 'consignee_address', 'file_no', 'awb_no', 'carrier', 'type_flag', 'weight', 'sub_total', 'tca', 'total', 'credits', 'balance_of', 'sent_on', 'sent_by', 'tba', 'payment_status', 'cargo_id', 'ups_id', 'currency', 'billing_party', 'hawb_hbl_no', 'display_notification_warehouse_invoice', 'display_notification_admin_invoice', 'invoice_status_changed_by', 'created_by', 'notification_date_time', 'display_notification_admin_invoice_status_changed', 'aeropost_id', 'ccpack_id', 'quick_book_id', 'housefile_module', 'payment_received_on', 'payment_received_by', 'ups_master_id', 'aeropost_master_id', 'ccpack_master_id', 'memo', 'flag_invoice', 'qb_sync'
    ];

    protected function getAllPendingInvoices()
    {
        $pendingInvoiceData = DB::table('invoices')->where('deleted', '0')->where('payment_status', 'Pending')->orderBy('id', 'desc')->get();
        $pdf = PDF::loadView('invoices.printallpendinginvoices', ['pendingInvoiceData' => $pendingInvoiceData]);
        $pdf_file = 'pendingInvoices.pdf';
        $pdf_path = 'public/pendingInvoicesPdf/' . $pdf_file;
        $pdf->save($pdf_path);
    }

    protected function getInvoiceNumber($id)
    {
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        return $invoiceData->bill_no;
    }

    protected function getDueAmount($id)
    {
        $totalDue = DB::table('invoices')
            ->select(DB::raw('sum(balance_of) as total'))
            ->where('deleted', 0)
            ->where(function ($query) {
                $query->where('payment_status', "Pending")
                    ->orWhere('payment_status', "Partial");
            })
            ->where('bill_to', $id)
            ->get()
            ->first();
        return !empty($totalDue->total) ? $totalDue->total : '0.00';
    }

    public function totalInCurrency($id)
    {
        $data = DB::table('invoice_payments')
            ->where('id', '=', $id)
            ->first();
        return $data->exchange_amount;
    }

    static function getInvoiceData($invoiceId)
    {
        $dataInvoices = DB::table('invoices')->where('id', $invoiceId)->first();
        return $dataInvoices;
    }

    public function checkInvoiceIsGeneratedOrNot($id, $flag)
    {
        if ($flag == 'housefile') {
            $dataInvoices = DB::table('invoices')->where('hawb_hbl_no', $id)->where('housefile_module', 'cargo')->first();
        } else if ($flag == 'ups') {
            $dataInvoices = DB::table('invoices')->where('ups_id', $id)->where('housefile_module', 'ups')->first();
        }
        return $dataInvoices;
    }

    public function checkIfAnyInvoicePending($id, $flag)
    {
        if ($flag == 'housefile') {
            $checkIfAnyInvoicePending = DB::table('invoices')
                ->where('invoices.hawb_hbl_no', $id)
                ->where('invoices.housefile_module', 'cargo')
                ->where('invoices.deleted', '0')
                ->where('invoices.payment_status', 'Pending')
                ->where('invoices.total', '!=', '0.00')
                ->count();
        } else if ($flag == 'ups') {
            $checkIfAnyInvoicePending = DB::table('invoices')
                ->where('invoices.ups_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoices.payment_status', 'Pending')
                ->where('invoices.total', '!=', '0.00')
                ->count();
        } else if ($flag == 'aeropost') {
            $checkIfAnyInvoicePending = DB::table('invoices')
                ->where('invoices.aeropost_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoices.payment_status', 'Pending')
                ->where('invoices.total', '!=', '0.00')
                ->count();
        } else if ($flag == 'ccpack') {
            $checkIfAnyInvoicePending = DB::table('invoices')
                ->where('invoices.ccpack_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoices.payment_status', 'Pending')
                ->where('invoices.total', '!=', '0.00')
                ->count();
        }
        return $checkIfAnyInvoicePending;
    }

    public function getLastInvoiceDateOfPayment($id, $flag)
    {
        if ($flag == 'housefile') {
            $getLastInvoiceDateOfPayment = DB::table('invoices')
                ->where('invoices.hawb_hbl_no', $id)
                ->where('invoices.housefile_module', 'cargo')
                ->where('invoices.deleted', '0')
                ->orderBy('payment_received_on', 'desc')
                ->first();
        } else if ($flag == 'ups') {
        }
        return $getLastInvoiceDateOfPayment->payment_received_on;
    }

    public function checkAllInvoiceIsGeneratedOrNot($id, $flag)
    {
        if ($flag == 'housefile') {
            $dataInvoices = DB::table('invoices')->where('hawb_hbl_no', $id)->where('housefile_module', 'cargo')->where('invoices.deleted', '0')->where('invoices.total', '!=', '0.00')->get();
        } else if ($flag == 'ups') {
            $dataInvoices = DB::table('invoices')->where('ups_id', $id)->where('invoices.deleted', '0')->where('invoices.total', '!=', '0.00')->get();
        } else if ($flag == 'aeropost') {
            $dataInvoices = DB::table('invoices')->where('aeropost_id', $id)->where('invoices.deleted', '0')->where('invoices.total', '!=', '0.00')->get();
        } else if ($flag == 'ccpack') {
            $dataInvoices = DB::table('invoices')->where('ccpack_id', $id)->where('invoices.deleted', '0')->where('invoices.total', '!=', '0.00')->get();
        }
        return $dataInvoices;
    }

    public function getTotalDaysOfCharge($id, $flag)
    {
        $dayDifference = 0;
        if ($flag == 'housefile') {
            $data = DB::table('hawb_files')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $dayDifference;
        } else if ($flag == 'ups') {
            $data = DB::table('ups_details')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $dayDifference;
        } else if ($flag == 'aeropost') {
            $data = DB::table('aeropost')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $dayDifference;
        } else if ($flag == 'ccpack') {
            $data = DB::table('ccpack')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $dayDifference;
        }
        /*$fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');*/
        $fromDate = $data->shipment_received_date;
        $toDate = $data->shipment_delivered_date;
        if (empty($fromDate))
            $fromDate = date('Y-m-d');
        if (empty($toDate))
            $toDate = date('Y-m-d');

        $your_date = strtotime($fromDate);
        $datediff = strtotime($toDate) - $your_date;

        $dayDifference = round($datediff / (60 * 60 * 24));

        return $dayDifference;
    }

    public function getTotalBilledDays($id, $flag)
    {
        if ($flag == 'housefile') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(['invoice_item_details.quantity'])
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalBilledDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.hawb_hbl_no', $id)
                ->where('invoices.housefile_module', 'cargo')
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();
        } else if ($flag == 'ups') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(['invoice_item_details.quantity'])
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalBilledDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.ups_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();
        } else if ($flag == 'aeropost') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(['invoice_item_details.quantity'])
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalBilledDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.aeropost_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();
        } else if ($flag == 'ccpack') {
            $dataHouseFileInvoices = DB::table('invoices')
                ->select(['invoice_item_details.quantity'])
                ->select(DB::raw('sum(invoice_item_details.quantity) as totalBilledDays'))
                ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                ->where('invoices.ccpack_id', $id)
                ->where('invoices.deleted', '0')
                ->where('invoice_item_details.item_code', 'SCC')
                ->first();
        }
        return empty($dataHouseFileInvoices->totalBilledDays) ? 0 : (int) $dataHouseFileInvoices->totalBilledDays;
    }

    public function getTotalChargableDays($id, $flag)
    {
        $finalChargeDays = 0;
        if ($flag == 'housefile') {
            $dataHouseFile = DB::table('hawb_files')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($dataHouseFile))
                return $finalChargeDays;
            /*$fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');*/
            $fromDate = $dataHouseFile->shipment_received_date;
            $toDate = $dataHouseFile->shipment_delivered_date;
            if (empty($fromDate))
                $fromDate = date('Y-m-d');
            if (empty($toDate))
                $toDate = date('Y-m-d');

            $now = time();
            $your_date = strtotime($fromDate);
            $datediff = strtotime($toDate) - $your_date;

            $dayDifference = round($datediff / (60 * 60 * 24));

            $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $id)->first();
            $measureWeight = !empty($modelCargoPackage->measure_weight) ? $modelCargoPackage->measure_weight : 'k';
            $measureVolume = !empty($modelCargoPackage->measure_volume) ? $modelCargoPackage->measure_volume : 'm';

            $pWeight = $modelCargoPackage->pweight;
            $pVolume = $modelCargoPackage->pvolume;

            $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
            $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
            $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
            if ($chageDaysWeight > 0)
                $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
            else
                $totalChargeWeight = '0.00';


            $storageChargeDataVolume = DB::table('storage_charges')->where('measure', strtoupper($measureVolume))->first();
            $chageDaysVolume = $dayDifference - $storageChargeDataVolume->grace_period;
            $chargeVolumePerMeterOrFeet = $storageChargeDataVolume->charge;
            if ($chageDaysVolume > 0)
                $totalChargeVolume = $chargeVolumePerMeterOrFeet * $pVolume * $chageDaysVolume;
            else
                $totalChargeVolume = '0.00';

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
                $finalChargeDays = '0';
                $finalCharge = '0';
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : No Charge (In Grace Period)';
            }
        } else if ($flag == 'ups') {
            $data = DB::table('ups_details')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $finalChargeDays;

            $fromDate = $data->shipment_received_date;
            $toDate = $data->shipment_delivered_date;
            if (empty($fromDate))
                $fromDate = date('Y-m-d');
            if (empty($toDate))
                $toDate = date('Y-m-d');

            $now = time();
            $your_date = strtotime($fromDate);
            $datediff = strtotime($toDate) - $your_date;

            $dayDifference = round($datediff / (60 * 60 * 24));

            $measureWeight = 'k';
            $pWeight = $data->weight;


            $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
            $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
            $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
            if ($chageDaysWeight > 0)
                $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
            else
                $totalChargeWeight = '0.00';

            if ($totalChargeWeight > 0) {
                $finalChargeDays = $chageDaysWeight;
                $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
            } else {
                $finalChargeDays = '0';
                $finalCharge = '0';
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : No Charge (In Grace Period)';
            }
        } else if ($flag == 'aeropost') {
            $data = DB::table('aeropost')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $finalChargeDays;

            $fromDate = $data->shipment_received_date;
            $toDate = $data->shipment_delivered_date;
            if (empty($fromDate))
                $fromDate = date('Y-m-d');
            if (empty($toDate))
                $toDate = date('Y-m-d');

            $now = time();
            $your_date = strtotime($fromDate);
            $datediff = strtotime($toDate) - $your_date;

            $dayDifference = round($datediff / (60 * 60 * 24));

            $measureWeight = 'k';
            $pWeight = $data->real_weight;


            $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
            $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
            $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
            if ($chageDaysWeight > 0)
                $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
            else
                $totalChargeWeight = '0.00';

            if ($totalChargeWeight > 0) {
                $finalChargeDays = $chageDaysWeight;
                $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
            } else {
                $finalChargeDays = '0';
                $finalCharge = '0';
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : No Charge (In Grace Period)';
            }
        } else if ($flag == 'ccpack') {
            $data = DB::table('ccpack')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($data))
                return $finalChargeDays;

            $fromDate = $data->shipment_received_date;
            $toDate = $data->shipment_delivered_date;
            if (empty($fromDate))
                $fromDate = date('Y-m-d');
            if (empty($toDate))
                $toDate = date('Y-m-d');

            $now = time();
            $your_date = strtotime($fromDate);
            $datediff = strtotime($toDate) - $your_date;

            $dayDifference = round($datediff / (60 * 60 * 24));

            $measureWeight = 'k';
            $pWeight = $data->weight;


            $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
            $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
            $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
            if ($chageDaysWeight > 0)
                $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
            else
                $totalChargeWeight = '0.00';

            if ($totalChargeWeight > 0) {
                $finalChargeDays = $chageDaysWeight;
                $finalCharge = $chargeWeightPerKgOrPound * $pWeight;
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : Duration : ' . date('d-m-Y', strtotime($fromDate)) . ' - ' . date('d-m-Y', strtotime($toDate)) . '(' . $dayDifference . ' days - ' . $storageChargeDataWeight->grace_period . ' Grace days = ' . $finalChargeDays . ' Days)';
            } else {
                $finalChargeDays = '0';
                $finalCharge = '0';
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : No Charge (In Grace Period)';
            }
        }
        return $finalChargeDays;
    }

    public function getTotalChargeTillToday($id, $flag)
    {
        $totalCharge = 0;
        if ($flag == 'housefile') {
            $dataHouseFile = DB::table('hawb_files')->where('id', $id)->where('shipment_status', '1')->first();
            if (empty($dataHouseFile))
                return '-';
            /*$fromDate = $dataCargo->shipment_received_date;
                $toDate = date('Y-m-d');*/
            $fromDate = $dataHouseFile->shipment_received_date;
            $toDate = $dataHouseFile->shipment_delivered_date;
            if (empty($fromDate))
                $fromDate = date('Y-m-d');
            if (empty($toDate))
                $toDate = date('Y-m-d');


            $now = time();
            $your_date = strtotime($fromDate);
            $datediff = strtotime($toDate) - $your_date;

            $dayDifference = round($datediff / (60 * 60 * 24));

            $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $id)->first();
            $measureWeight = !empty($modelCargoPackage->measure_weight) ? $modelCargoPackage->measure_weight : 'k';
            $measureVolume = !empty($modelCargoPackage->measure_volume) ? $modelCargoPackage->measure_volume : 'm';

            $pWeight = $modelCargoPackage->pweight;
            $pVolume = $modelCargoPackage->pvolume;

            $storageChargeDataWeight = DB::table('storage_charges')->where('measure', strtoupper($measureWeight))->first();
            $chageDaysWeight = $dayDifference - $storageChargeDataWeight->grace_period;
            $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
            if ($chageDaysWeight > 0)
                $totalChargeWeight = $chargeWeightPerKgOrPound * $pWeight * $chageDaysWeight;
            else
                $totalChargeWeight = '0.00';


            $storageChargeDataVolume = DB::table('storage_charges')->where('measure', strtoupper($measureVolume))->first();
            $chageDaysVolume = $dayDifference - $storageChargeDataVolume->grace_period;
            $chargeVolumePerMeterOrFeet = $storageChargeDataVolume->charge;
            if ($chageDaysVolume > 0)
                $totalChargeVolume = $chargeVolumePerMeterOrFeet * $pVolume * $chageDaysVolume;
            else
                $totalChargeVolume = '0.00';

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
                $finalChargeDays = '0';
                $finalCharge = '0';
                $totalCharge = $finalChargeDays * $finalCharge;
                $desc = 'Storage Charge : No Charge (In Grace Period)';
            }
        }
        return $totalCharge;
    }
}
