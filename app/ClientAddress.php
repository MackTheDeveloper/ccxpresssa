<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    protected $table = 'client_address';
    public $timestamps = false;
    protected $fillable = [
        'client_id','client_branch_id', 'company_name','status','created_at','updated_at','address','zipcode','country_id','state_id','city'
    ];
}
