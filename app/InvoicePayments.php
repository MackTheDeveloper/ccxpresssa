<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class InvoicePayments extends Model
{
    protected $table = 'invoice_payments';
    public $timestamps = false;
    protected $fillable = [
        'file_number', 'invoice_number', 'invoice_id', 'client', 'amount', 'cash_credit_account', 'status', 'created_at', 'updated_at', 'deleted', 'deleted_at', 'cargo_id', 'ups_id', 'ccpack_id', 'aeropost_id', 'payment_via', 'payment_via_note', 'payment_accepted_by', 'exchange_currency', 'exchange_rate', 'exchange_amount', 'house_file_id', 'receipt_number', 'credited_amount', 'deleted_by', 'ups_master_id', 'aeropost_master_id', 'ccpack_master_id'
    ];

    public function getDataFromInvoice($invoiceId)
    {
        $data = DB::table('invoice_payments')->where('invoice_id', $invoiceId)->orderBy('id','desc')->first();
        return $data;
    }

    static function checkReceipt($receiptNumber)
    {
        $deleted = '0';
        $dataInvoices = DB::table('invoice_payments')->where('receipt_number', $receiptNumber)->where('deleted','1')->count();
        if($dataInvoices > 0)
            $deleted = '1';
        return $deleted;
    }
}
