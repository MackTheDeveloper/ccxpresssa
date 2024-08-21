<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientBranch extends Model
{
    protected $table = 'client_branch';
    public $timestamps = false;
    protected $fillable = [
        'client_id','tax_number', 'company_name','branch_name','branch_email','status','created_at','updated_at','phone_number','branch_address','country_id','state_id','city','zipcode'
    ];

}
