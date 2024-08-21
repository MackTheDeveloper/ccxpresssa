<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Depositcashcredit extends Model
{
    protected $table = 'deposit_cash_credit';
    public $timestamps = false;
    protected $fillable = [
        'cash_credit_account', 'amount', 'deposit_date','approved_by_user','comments','created_on','created_by','deleted','deleted_on','deleted_by'
    ];
}
