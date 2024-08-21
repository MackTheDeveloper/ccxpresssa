<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Manifests extends Model
{
    protected $table = 'manifests';
    public $timestamps = false;
    protected $fillable = [
        'bateau', 'date_voyage', 'no_voyage', 'carrier', 'all_details', 'created_on', 'created_by', 'deleted_on', 'deleted_by'
    ];
}
