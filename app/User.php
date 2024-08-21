<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;
use Illuminate\Support\Facades\DB;
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','department','deleted','deleted_at','status','user_type','warehouses','default_cashbank_account_for_report'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function checkPermission($slug, $type, $rel_id, $per_flag = 1)
    {
        $permissionsUser = DB::table('permissions')->whereIn('slug',$slug)->where('type_flag','1')->where('related_id',$rel_id)->get();
        if(count($permissionsUser) > 0)
        {
            return 1;
        }else
        {
            return 0;
        }
            
        //pre(Auth::user());
        /*$dept = Auth::user()->department;
        $permissionsDept = DB::table('permissions')->whereIn('slug',$slug)->where('type_flag','2')->where('related_id',$dept)->get();
        //pre(count($permissionsDept));
        if(count($permissionsDept) > 0)
        {
            return 1;
        }
        else
        {
            $permissionsUser = DB::table('permissions')->whereIn('slug',$slug)->where('type_flag','1')->where('related_id',$rel_id)->get();
            if(count($permissionsUser) > 0)
            {
                return 1;
            }else
            {
                return 0;
            }
        }*/

    }

    public function getUserName($id)
    {
        $dataUser = User::find($id);
        return $dataUser;
    }

    // USED FOR BACKGROUND PROCESS                
    function backgroundPost($url) {
        $parts = parse_url($url);
        $parts['query'] = '';
        $parts['path'] = $parts['path']->segment(2) . '?' . $parts['query'];
        pre($parts);
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        //$fp = fsockopen('ssl://www.ecoerp1.com',443,$errno, $errstr, 30);
        if (!$fp) {
            return false;
        } else {
            $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            if (isset($parts['query']))
                $out .= "Content-Length: " . strlen($parts['query']) . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            if (isset($parts['query']))
                $out .= $parts['query'];

            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }

    protected function getMultipleNames($ids)
    {
        $ids = explode(',',$ids);
        
        $data = DB::table('users')
        ->select(DB::raw('GROUP_CONCAT(name) as Names'))
        ->whereIn('id',$ids)
        ->first();
        return $data->Names;
    }

    public function checkUserByRole()
    {
        $dept = DB::table('cashcredit_detail_type')
            ->select('cashcredit_detail_type.name')
            ->join('users', 'users.department', '=', 'cashcredit_detail_type.id')
            ->where('users.department',auth()->user()->department)
            ->first();

        if(!empty($dept))
            return $dept->name;
        else
            return "";

    }

    
}