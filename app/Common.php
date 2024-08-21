<?php

namespace App;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class Common extends Model
{
    static function getClientDataUsingModuleId($model, $id)
    {
        if ($model == 'cargo') {
            $data = DB::table('cargo')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->where('cargo.id', $id)
                ->first();
        } else if ($model == 'ups') {
            $data = DB::table('ups_details')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.id', $id)
                ->first();
        } else if ($model == 'upsMaster') {
            $data = DB::table('ups_master')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->where('ups_master.id', $id)
                ->first();
        } else if ($model == 'houseFile') {
            $data = DB::table('hawb_files')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->where('hawb_files.id', $id)
                ->first();
        } else if ($model == 'aeropost') {
            $data = DB::table('aeropost')
                ->select('c1.company_name as consigneeName', 'aeropost.from_location as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->where('aeropost.id', $id)
                ->first();
        } else if ($model == 'aeropostMaster') {
            $data = DB::table('aeropost_master')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->where('aeropost_master.id', $id)
                ->first();
        } else if ($model == 'ccpackMaster') {
            $data = DB::table('ccpack_master')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->where('ccpack_master.id', $id)
                ->first();
        } else if ($model == 'ccpack') {
            $data = DB::table('ccpack')
                ->select('c1.company_name as consigneeName', 'c2.company_name as shipperName')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->where('ccpack.id', $id)
                ->first();
        }


        if (!empty($data)) {
            $dataFinal['consigneeName'] = !empty($data->consigneeName) ? $data->consigneeName : '-';
            $dataFinal['shipperName'] = !empty($data->shipperName) ? $data->shipperName : '-';
        } else {
            $dataFinal['consigneeName'] = '-';
            $dataFinal['shipperName'] = '-';
        }

        return $dataFinal;
    }

    public function getCommonAllModuleData($flagModule, $id)
    {
        if ($flagModule == 'cargo') {
            $tblName = 'hawb_files';
        } else if ($flagModule == 'ups') {
            $tblName = 'ups_details';
        } else if ($flagModule == 'aeropost') {
            $tblName = 'aeropost';
        } else if ($flagModule == 'ccpack') {
            $tblName = 'ccpack';
        }

        $data = DB::table($tblName)
            ->where('id', $id)
            ->first();
        return $data;
    }

    public function getInvoicesOfCouriers($id, $flagModule)
    {
        if ($flagModule == 'ups') {
            $column = 'ups_id';
        } else if ($flagModule == 'aeropost') {
            $column = 'aeropost_id';
        } else if ($flagModule == 'ccpack') {
            $column = 'ccpack_id';
        }
        $tblName = 'invoices';
        $data = DB::table($tblName)
            ->where($column, $id)
            ->where('deleted', '0')
            ->get();
        return $data;
    }

    public function getTotalNoOfInvoices($ids, $flagModule)
    {
        if ($flagModule == 'ups') {
            $column = 'ups_id';
        } else if ($flagModule == 'aeropost') {
            $column = 'aeropost_id';
        } else if ($flagModule == 'ccpack') {
            $column = 'ccpack_id';
        }
        $tblName = 'invoices';
        $data = DB::table($tblName)
            ->whereIn($column, $ids)
            ->where('deleted', '0')
            ->count();
        return $data;
    }

    public function checkIfInvoiceStatusPending($id, $flagModule)
    {
        if ($flagModule == 'ups') {
            $column = 'ups_id';
        } else if ($flagModule == 'aeropost') {
            $column = 'aeropost_id';
        } else if ($flagModule == 'ccpack') {
            $column = 'ccpack_id';
        }

        $UpsClientId = '';
        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        if (!empty($dataClient))
            $UpsClientId = $dataClient->id;

        $AeropostClientId = '';
        $dataClientAeropost = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
        if (!empty($dataClientAeropost))
            $AeropostClientId = $dataClientAeropost->id;

        /* $tblName = 'invoices';
        $checkInvoiceGeneraterOrNot = DB::table($tblName)
            ->where($column, $id)
            ->where('deleted', '0')
            ->where('invoices.total', '!=', '0.00')
            ->where('invoices.bill_to', '<>', $UpsClientId)
            ->where('invoices.bill_to', '<>', $AeropostClientId)
            ->count();
        if ($checkInvoiceGeneraterOrNot == 0) {
            return '1';
        }

        $countPending = DB::table($tblName)
            ->where($column, $id)
            ->where('deleted', '0')
            ->where('invoices.total', '!=', '0.00')
            ->where('invoices.bill_to', '<>', $UpsClientId)
            ->where('invoices.bill_to', '<>', $AeropostClientId)
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->count(); */
        $tblName = 'invoices';
        $countPending = DB::table($tblName)
            ->where($column, $id)
            ->where('deleted', '0')
            ->where('invoices.total', '!=', '0.00')
            ->where('invoices.bill_to', '<>', $UpsClientId)
            ->where('invoices.bill_to', '<>', $AeropostClientId)
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->count();
        return $countPending;
    }

    public function getCommentOfDelivery($id, $flagModule)
    {
        if ($flagModule == 'ups') {
            $column = 'ups_id';
        } else if ($flagModule == 'aeropost') {
            $column = 'aeropost_id';
        } else if ($flagModule == 'ccpack') {
            $column = 'ccpack_id';
        }
        $tblName = 'verification_inspection_notes';
        $data = DB::table($tblName)
            ->where($column, $id)
            ->where('flag_note', 'R')
            ->orderBy('id', 'desc')
            ->first();
        return $data;
    }

    public function getReceiptNumbers($invoiceId, $flagModule)
    {
        $column = 'invoice_id';
        $tblName = 'invoice_payments';
        $data = DB::table($tblName)
            ->select(DB::raw('group_concat(receipt_number) as receiptNumbers'))
            ->where($column, $invoiceId)
            ->first();
        return $data->receiptNumbers;
    }
}
