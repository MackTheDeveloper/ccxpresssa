<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class ClientContactMore extends Model
{
    protected $table = 'client_contact_more';
    public $timestamps = false;
    protected $fillable = [
        'contact_id','contact_type','contact'
    ];
}
