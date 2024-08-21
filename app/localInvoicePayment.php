<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class localInvoicePayment extends Model
{
    protected $table = 'local_invoice_payment_detail';
    protected $fillable = ['id','local_invoice_id','date','duration','total','status','mail_send','created_by','created_at']; 
    public $timestamps = false;
}
