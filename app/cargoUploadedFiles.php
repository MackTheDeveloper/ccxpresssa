<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cargoUploadedFiles extends Model
{
    protected $table = 'cargo_uploaded_files';
    public $timestamps = false;
    protected $fillable = ['file_type','file_id','file_name','uploaded_at','uploaded_by','downloaded_at','downloaded_by','deleted','deleted_at','deleted_by', 'flag_module'];
}
