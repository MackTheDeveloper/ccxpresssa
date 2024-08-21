<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CargoRenewContract extends Model
{
    protected $table = 'cargo_renew_contract';
    public $timestamps = true;
    protected $fillable = [
        'cargo_id','previous_date','renew_months','new_date','updated_by','updated_by_name'
    ];
}
