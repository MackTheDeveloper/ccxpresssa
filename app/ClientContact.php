<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    protected $table = 'client_contact';
    public $timestamps = false;
    protected $fillable = [
        'client_id','name','personal_contact','cell_number','direct_line','work','email','status','created_at','updated_at','company_name','vendor_id'
    ];
}
