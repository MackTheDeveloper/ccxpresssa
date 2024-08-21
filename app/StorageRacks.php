<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Config;
use Illuminate\Support\Facades\DB;
class StorageRacks extends Model
{
    protected $table = 'storage_racks';
    public $timestamps = false;
    protected $fillable = [
        'rack_department', 'main_section','sub_section','location_number','status','created_at','updated_at','deleted','deleted_at'
    ];

    public function getData($id)
    {
        $rackLocationData = DB::table('storage_racks')
            ->whereIn('id',explode(',',$id))
            ->get();
    	
        if(count($rackLocationData) > 0)
        {
            $racksLocations = '';
            foreach($rackLocationData as $k => $v)
            {
                $racksLocations .= Config::get('app.rackDepartment')[$v->rack_department].' - '.$v->main_section.' - '.$v->sub_section.' - '.$v->location_number.' | ';
            } 
            return rtrim($racksLocations,' | ');   
        }
        else
            return 'Not Allocated';
    }

    public function getAvailableLocations($id)
    {
        $rackLocations = DB::table('hawb_files')->select(DB::raw('GROUP_CONCAT(rack_location) AS rackLocations'))->whereNotNull('rack_location')->where('deleted','0')->where('id','<>',$id)->get()->toArray();
        $loc = explode(',',$rackLocations[0]->rackLocations);

        $availableLocations = DB::table('storage_racks')
            ->whereNotIn('id',$loc)
            ->where('deleted','0')
            ->where('status','1')
            ->get()
            ->toArray();

        $dataAvailableLocations =  array();
        foreach ($availableLocations as $key => $value) {
                $dataAvailableLocations[$value->id] = Config::get('app.rackDepartment')[$value->rack_department].' - '.$value->main_section.' - '.$value->sub_section.' - '.$value->location_number;
        }    
        return $dataAvailableLocations;
    }


}
