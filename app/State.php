<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'state';
    public $timestamps = false;
    protected $fillable = [
        'name','country_id','status','created_at','updated_at','deleted','deleted_at'
    ];
}
