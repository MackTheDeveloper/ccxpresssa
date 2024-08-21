<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpsMasterInvoices extends Model
{
    protected $table = 'invoices';
    public $timestamps = false;
    protected $fillable = [
        'bill_to', 'date', 'bill_no', 'email', 'telephone', 'shipper', 'consignee_address', 'file_no', 'awb_no', 'carrier', 'type_flag', 'weight', 'sub_total', 'tca', 'total', 'credits', 'balance_of', 'sent_on', 'sent_by', 'tba', 'payment_status', 'cargo_id', 'ups_id', 'currency', 'billing_party', 'hawb_hbl_no', 'display_notification_warehouse_invoice', 'display_notification_admin_invoice', 'invoice_status_changed_by', 'created_by', 'notification_date_time', 'display_notification_admin_invoice_status_changed', 'aeropost_id', 'ccpack_id', 'quick_book_id', 'housefile_module', 'payment_received_on', 'payment_received_by', 'ups_master_id', 'aeropost_master_id', 'ccpack_master_id', 'memo', 'flag_invoice', 'qb_sync'
    ];

    public function totalInCurrency($id)
    {
        $data = DB::table('invoice_payments')
            ->where('id', '=', $id)
            ->first();
        return $data->exchange_amount;
    }

    static function getMasterUpsInvoiceData($invoiceId)
    {
        $dataInvoices = DB::table('invoices')->where('id', $invoiceId)->first();
        return $dataInvoices;
    }
}
