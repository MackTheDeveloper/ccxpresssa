<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CurrencyExchange extends Model
{
    protected $table = 'currency_exchange';
    public $timestamps = false;
    protected $fillable = [
        'from_currency','to_currency','exchange_value'
    ];
}
