<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HawbContainers extends Model
{
    protected $table = 'hawb_containers';
    public $timestamps = false;
    protected $fillable = [
        'container_number','hawb_id','cargo_id'
    ];
}
