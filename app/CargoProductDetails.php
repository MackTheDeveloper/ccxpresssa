<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CargoProductDetails extends Model
{
    protected $table = 'cargo_product_details';
    public $timestamps = false;
    protected $fillable = [
        'prod_date', 'prod_description', 'pro_expense','to_bill_gdes','to_bill_usd','credit_gdes_usd','cargo_id','pro_expense_gdes','pro_expense_usd','credit_gdes','credit_usd'
    ];
}
