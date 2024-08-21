<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UpsFileStatus extends Model
{
    protected $table = 'inprogress_status';
    public $timestamps = false;
    protected $fillable = ['status','after_or_before','created_by','created_at','updated_by','updated_at','deleted','deleted_by','deleted_at'];

    
}
