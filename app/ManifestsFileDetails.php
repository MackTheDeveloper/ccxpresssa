<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ManifestsFileDetails extends Model
{
    protected $table = 'manifests_file_details';
    public $timestamps = false;
    protected $fillable = [
        'file_name', 'total_pages', 'upload_status', 'uploaded_percentage', 'uploaded_seconds', 'created_on', 'created_by', 'deleted', 'deleted_on', 'deleted_by'
    ];
}
