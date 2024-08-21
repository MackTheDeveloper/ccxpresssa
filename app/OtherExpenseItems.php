<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class OtherExpenseItems extends Model
{
    protected $table = 'other_expense_items';
    public $timestamps = false;
    protected $fillable = [
        'name','status','created_at','updated_at','deleted','deleted_at'
    ];

    public function getData($id)
    {
    	$data = OtherExpenseItems::find($id);
        return $data;
    }

    
    static function getAutocomplete()
    {
        $data = DB::table('other_expense_items')->select(['id','name'])->where('deleted',0)->where('status',1)->get()->toArray();
        $i = 0;
        $final1 = array();
        foreach ($data as $vl) {
            $fData['label'] = $vl->name;
            $fData['value'] = $vl->name;
            $final1[] = $fData;
        }
        return json_encode($final1);
    }
}
