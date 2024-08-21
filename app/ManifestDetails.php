<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ManifestDetails extends Model
{
    protected $table = 'manifest_details';
    public $timestamps = false;
    protected $fillable = [
        'manifest_id', 'bateau', 'date_voyage', 'no_voyage', 'carrier', 'cntrqty', 'shipper', 'consignee', 'port', 'weight', 'comodity', 'quantity', 'quantity_unit', 'created_on', 'created_by', 'deleted_on', 'deleted_by'
    ];
}
