<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class upsImportExportCommission extends Model
{
    protected $table = 'ups_import_export_commission';
    public $timestamps = false;
    protected $fillable = ['file_type','billing_term','courier_type','commission','created_by','created_at','updated_by','updated_at','deleted','deleted_by','deleted_at'];
}
