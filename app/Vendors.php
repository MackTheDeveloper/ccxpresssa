<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class Vendors extends Model
{
    protected $table = 'vendors';
    public $timestamps = false;
    protected $fillable = ['quick_book_id',
        'first_name','middle_name','last_name', 'email', 'company_phone','currency','currency_code','street','city','state','zipcode','country','company_name','status','created_at','updated_at','password','user_id','opening_balance','as_of', 'vendor_type', 'qb_sync', 'payment_term'
    ];

    
    public function getVendorData($id)
    {
        $dataVendor = Vendors::find($id);
        return $dataVendor;
    }

    static function getDataFromPaidTo($expenseId)
    {
        $dataPaidTo = DB::table('expense_details')->where('expense_id',$expenseId)->first();
        if(!empty($dataPaidTo))
        {
            $dataVendor = Vendors::find($dataPaidTo->paid_to);
            if(!empty($dataVendor))
            {
                $dataPaidToCurrency = Currency::getData($dataVendor->currency); 
                return $dataPaidToCurrency;
            }else{
                return array();
            }
        }else
        {
            return array();
        }
    }

    static function getDataFromPaidToAdministration($expenseId)
    {
        $dataPaidTo = DB::table('other_expenses_details')->where('expense_id', $expenseId)->first();
        if (!empty($dataPaidTo)) {
            $dataVendor = Vendors::find($dataPaidTo->paid_to);
            if (!empty($dataVendor)) {
                $dataPaidToCurrency = Currency::getData($dataVendor->currency);
                return $dataPaidToCurrency;
            } else {
                return array();
            }
        } else {
            return array();
        }
    }

    static function getVendorsAutocomplete()
    {
        $dataConsignee = DB::table('vendors')->select(['id','company_name'])
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

    static function checkExistingCurrencyVendor($name)
    {
        $findStr = '';
        if (strpos($name, 'USD') !== false) {
            $findStr = str_replace('USD','HTG',$name);
        }else if (strpos($name, 'HTG') !== false)
        {
            $findStr = str_replace('HTG','USD',$name);
        }
        $dataClients = DB::table('vendors')->where('deleted',0)->where('company_name',$findStr)->count();
        if($dataClients > 0)
            return '1';
        else
            return '0';
        
    }
}
