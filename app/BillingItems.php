<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BillingItems extends Model
{
    protected $table = 'billing_items';
    public $timestamps = false;
    protected $fillable = [
        'billing_name', 'code', 'status', 'created_at', 'updated_at', 'deleted', 'deleted_at', 'flag_prod_tax_type', 'flag_prod_tax_amount', 'billing_account', 'item_code', 'description', 'quick_book_id', 'qb_sync'
    ];

    public function getBillingData($id)
    {
        $billingData = DB::table('billing_items')->where('id', $id)->where('deleted', '0')->where('status', 1)->first();
        return $billingData;
    }


    static function getBillingItemsAutocomplete()
    {
        $data = DB::table('billing_items')->select(['id', 'billing_name'])->where('deleted', 0)->where('status', 1)->get()->toArray();
        $i = 0;
        $final1 = array();
        foreach ($data as $vl) {
            $fData['label'] = $vl->billing_name;
            $fData['value'] = $vl->id;
            $final1[] = $fData;
        }
        return json_encode($final1);
    }

    static function checkAssociateBillingCreatedOrNot($module, $moduleId = null, $costItemId = null)
    {
        $getAssociateBillingItem = DB::table('costs')->where('id', $costItemId)->first();
        $billingItemId = !empty($getAssociateBillingItem->cost_billing_code) ? $getAssociateBillingItem->cost_billing_code : '';

        if (!empty($billingItemId)) {
            $dataInvoices = DB::table('invoices')->select(DB::raw('GROUP_CONCAT(invoices.id) as invoiceId'));
            if ($module == 'Cargo') {
                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('cargo_id')
                    ->whereNull('housefile_module')
                    ->where('invoices.cargo_id', $moduleId);
            } else if ($module == 'House File') {
                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->where('housefile_module', 'cargo')
                    ->where('invoices.hawb_hbl_no', $moduleId);
            } else if ($module == 'Ups') {
                $UpsClientId = '';
                $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                if (!empty($dataClient))
                    $UpsClientId = $dataClient->id;

                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('ups_id')
                    ->where('invoices.bill_to', '<>', $UpsClientId)
                    ->where('invoices.ups_id', $moduleId);
            } else if ($module == 'upsMaster') {
                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('ups_master_id')
                    ->where('invoices.ups_master_id', $moduleId);
            } else if ($module == 'Aeropost') {
                $UpsClientId = '';
                $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
                if (!empty($dataClient))
                    $UpsClientId = $dataClient->id;

                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('aeropost_id')
                    ->where('invoices.bill_to', '<>', $UpsClientId)
                    ->where('invoices.aeropost_id', $moduleId);
            } else if ($module == 'aeropostMaster') {
                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('aeropost_master_id')
                    ->where('invoices.aeropost_master_id', $moduleId);
            } else if ($module == 'ccpackMaster') {
                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('ccpack_master_id')
                    ->where('invoices.ccpack_master_id', $moduleId);
            } else {
                $dataInvoices = $dataInvoices->where('invoices.deleted', '0')
                    ->whereNotNull('ccpack_id')
                    ->where('invoices.ccpack_id', $moduleId);
            }
            $dataInvoices = $dataInvoices->first();

            if (!empty($dataInvoices->invoiceId)) {
                $dataS = explode(',', $dataInvoices->invoiceId);
                $countAssociateItems =  DB::table('invoice_item_details')
                    ->whereIn('invoice_id', $dataS)
                    ->where('fees_name', $billingItemId)->count();

                if ($countAssociateItems > 0)
                    return "1";
                else
                    return "0";
            } else {
                return "0";
            }
        } else {
            return "0";
        }
    }

    static function getUnpaidExpenses($module)
    {
        $arrayOfNonBilledExpenseCostItems = array();
        if ($module == 'UPS') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ups_details_id,expenses.voucher_number,ups_details.file_number,ups_details.courier_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->whereNotNull('expenses.ups_details_id')
                ->where('expenses.deleted', '0')
                ->where('ups_details.fd', 0)
                ->where('ups_details.package_type', '!=', 'DOC')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('Ups', $v->upsId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'upsMaster') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ups_master_id,expenses.voucher_number,ups_master.file_number,ups_master.ups_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ups_master.id as upsMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->whereNotNull('expenses.ups_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('upsMaster', $v->upsMasterId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'Aeropost') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.aeropost_id,expenses.voucher_number,aeropost.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->whereNotNull('expenses.aeropost_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('Aeropost', $v->aeropostId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'aeropostMaster') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.aeropost_master_id,expenses.voucher_number,aeropost_master.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,aeropost_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->whereNotNull('expenses.aeropost_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('aeropostMaster', $v->aeropostMasterId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'ccpackMaster') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ccpack_master_id,expenses.voucher_number,ccpack_master.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ccpack_master.id as ccpackMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->whereNotNull('expenses.ccpack_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('ccpackMaster', $v->ccpackMasterId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'CCPack') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ccpack_id,expenses.voucher_number,ccpack.file_number,ccpack.ccpack_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->whereNotNull('expenses.ccpack_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('CCPack', $v->ccpackId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'Cargo') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.cargo_id,expenses.voucher_number,cargo.file_number,cargo.cargo_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,cargo.id as cargoId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->whereNotNull('expenses.cargo_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('Cargo', $v->cargoId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        } elseif ($module == 'House File') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.house_file_id,expenses.voucher_number,hawb_files.file_number,hawb_files.cargo_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,hawb_files.id as houseId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->whereNotNull('expenses.house_file_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->get();

            foreach ($query as $k => $v) {
                $result = self::checkAssociateBillingCreatedOrNot('House File', $v->houseId, $v->expense_type);
                if ($result == '0')
                    $arrayOfNonBilledExpenseCostItems[] = $v->id;
            }

            return $arrayOfNonBilledExpenseCostItems;
        }
    }
}
