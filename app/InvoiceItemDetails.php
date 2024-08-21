<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItemDetails extends Model
{
    protected $table = 'invoice_item_details';
    public $timestamps = false;
    protected $fillable = [
        'invoice_id', 'fees_name', 'quantity','unit_price','total_of_items','fees_name_hidden','fees_name_desc','item_code'
    ];
}
