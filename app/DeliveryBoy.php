<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeliveryBoy extends Model
{
    protected $table = 'delivery_boy';
    public $timestamps = false;
    protected $fillable = [
        'quick_book_id',
        'name', 'email', 'status', 'created_at', 'updated_at', 'deleted', 'deleted_at', 'phone_number', 'company_name'
    ];

    public function getDeliveryBodData($id)
    {
        $data = DeliveryBoy::find($id);
        return $data;
    }

    public function getNoOfAssignedFiles($id)
    {
        $result = DB::select("SELECT (SELECT COUNT(*) FROM ups_details where delivery_boy = $id AND deleted = '0') as upsCount, (SELECT COUNT(*) FROM aeropost where delivery_boy = $id AND deleted = '0') as aeropostCount, (SELECT COUNT(*) FROM ccpack where delivery_boy = $id AND deleted = '0') as ccpackCount");
        return $result[0]->upsCount + $result[0]->aeropostCount + $result[0]->ccpackCount;
    }
}
