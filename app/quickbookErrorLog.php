<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class quickbookErrorLog extends Model
{
    protected $table = 'quickbook_error_logs';
    public $timestamps = false;
    protected $fillable = ['module','module_id','operation','error_message'];
}
