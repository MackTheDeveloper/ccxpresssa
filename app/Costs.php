<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Costs extends Model
{
    protected $table = 'costs';
    public $timestamps = false;
    protected $fillable = ['quick_book_id',
        'cost_name', 'code','status','created_at','updated_at','deleted','deleted_at', 'cost_billing_code', 'qb_sync'
    ];

    public function getCostData($id)
    {
        $costData = DB::table('costs')->where('id', $id)->where('deleted', '0')->where('status', 1)->first();
        return $costData;
    }

    public function getCostDataUsingCode($code)
    {
        $costData = DB::table('costs')->where('code', $code)->where('deleted', '0')->where('status', 1)->first();
        return $costData;
    }
}
