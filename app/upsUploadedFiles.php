<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class upsUploadedFiles extends Model
{
    protected $table = 'ups_uploaded_files';
    public $timestamps = false;
    protected $fillable = ['file_type', 'file_id', 'master_file_id', 'file_name', 'uploaded_at', 'uploaded_by', 'downloaded_at', 'downloaded_by', 'deleted', 'deleted_at', 'deleted_by'];
}
