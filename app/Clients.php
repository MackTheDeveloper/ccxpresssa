<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Clients extends Model
{
    protected $table = 'clients';
    public $timestamps = false;
    protected $fillable = [
        'name', 'email','company_address','company_name','status','created_at','updated_at','password','user_id','credit_limit','available_balance','category','branch_name','tax_number','payment_term','website','phone_number','country','state','city','zip_code','fax','currency','client_branch_id','flag_prod_tax_type','flag_prod_tax_amount','cash_credit','credit_limit_add','first_name','middle_name','last_name','street','zipcode','quick_book_id','client_flag', 'qb_sync', 'email_two', 'email_three'
    ];

    
    static function getClients()
    {
    	$dataConsignee = DB::table('clients')->select(['company_name'])->where('deleted',0)->where('status',1)->get()->toArray();
    	$i = 0;
    	$final1 = array();
        foreach ($dataConsignee as $vl) {
        	$fData['label'] = $vl->company_name;
            $fData['value'] = $vl->company_name;
            $final1[] = $fData;
        }
        return json_encode($final1);
    }

    static function getClientsAutocomplete($search="")
    {
        $dataConsignee = DB::table('clients')->select(['id','company_name'])
            ->where(function ($query) {
                $query->where('client_flag', 'O')
                    ->orWhereNull('client_flag');
            })
        ->where('deleted',0)->where('status',1);
        if($search){
            $dataConsignee->where('company_name','like','%'.$search.'%');
        }
        $dataConsignee = $dataConsignee->get()->toArray();
        $i = 0;
        $final1 = array();
        foreach ($dataConsignee as $vl) {
            $fData['label'] = $vl->company_name;
            $fData['value'] = $vl->id;
            $final1[] = $fData;
        }
        return json_encode($final1);
    }

    static function getClientsAutocompleteNew($search = "")
    {
        $dataConsignee = DB::table('clients')->selectRaw("id as value, company_name as label")
            ->where(function ($query) {
                $query->where('client_flag', 'O')
                    ->orWhereNull('client_flag');
            })
            ->where('deleted', 0)->where('status', 1);
        if ($search) {
            $dataConsignee->where('company_name', 'like', '%' . $search . '%');
        }
        $dataConsignee = $dataConsignee->limit(100)->get()->toArray();
        return $dataConsignee;
    }

    static function getBillingPartyAutocomplete()
    {
        $dataConsignee = DB::table('clients')->select(['id','company_name'])
        ->where('client_flag','B')
        ->where('deleted',0)->where('status',1)->get()->toArray();
        $i = 0;
        $final1 = array();
        foreach ($dataConsignee as $vl) {
            $fData['label'] = $vl->company_name;
            $fData['value'] = $vl->id;
            $final1[] = $fData;
        }
        return json_encode($final1);
    }

    public function getClientData($id)
    {
        $dataClient = Clients::find($id);
        return $dataClient;
    }

    public function getClientDataByCompanyName($companyName)
    {
        $dataClient = Clients::where('company_name',$companyName)->first();
        return $dataClient;
    }

    static function checkExistingCurrencyClient($name)
    {
        $findStr = '';
        if (strpos($name, 'USD') !== false) {
            $findStr = str_replace('USD','HTG',$name);
        }else if (strpos($name, 'HTG') !== false)
        {
            $findStr = str_replace('HTG','USD',$name);
        }
        $dataClients = DB::table('clients')->where('deleted',0)->where('company_name',$findStr)->count();
        if($dataClients > 0)
            return '1';
        else
            return '0';
        
    }
}
