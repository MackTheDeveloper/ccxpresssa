<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CheckGuaranteeToPay extends Model
{
    protected $table = 'check_guarantee_to_pay';
    public $timestamps = false;
    protected $fillable = [
        'master_cargo_id', 'check_number', 'bank', 'date', 'amount', 'paid', 'detention_days_allowed', 'invoice_number', 'detention_days_used', 'amount_deducted', 'amount_balance', 'notes', 'deleted', 'created_at', 'updated_at', 'deleted_at', 'file_number', 'check_return', 'delivered_date', 'return_date', 'total_cost_container', 'total_cost_chassis', 'total_cost', 'billed', 'approved', 'cashier_check_number','check_type'
    ];

    static function getData($id)
    {
        $data = DB::table('check_guarantee_to_pay')->where('id', $id)->where('deleted', '0')->first();
        return $data;
    }

    public static function getBilledAmountOfFile($moduleId)
    {
        $dataInvoices = DB::table('invoices')
            ->select(DB::raw('sum(invoice_item_details.total_of_items) as total'))
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->where('invoices.cargo_id', $moduleId)
            ->where('invoices.deleted', '0')
            ->where('invoice_item_details.item_code', '1042')
            ->first();

        return empty($dataInvoices->total) ? 0 : (int) $dataInvoices->total;
    }
}
