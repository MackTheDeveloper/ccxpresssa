<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VerificationInspectionNote extends Model
{
    protected $table = 'verification_inspection_notes';
    public $timestamps = false;
    protected $fillable = [
        'hawb_id', 'flag_note','notes','created_on','created_by','ups_id','aeropost_id','ccpack_id','cargo_master_id', 'ups_master_id','aeropost_master_id','ccpack_master_id'
    ];

    public function getData($id)
    {
    	$data = VerificationInspectionNote::find($id);
        return $data;
    }
}
