<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AeropostCommission extends Model
{
    protected $table = 'aeropost_commission';
    public $timestamps = false;
    protected $fillable = ['commission'];
}
