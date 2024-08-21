<?php

namespace App\Http\Controllers;

use App\CashCredit;
use App\Activities;
use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use App\Admin;
class CashCreditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cash_bank'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $cashcredit = DB::table('cashcredit')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("cashcredit.index",['cashcredit'=>$cashcredit]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_cash_bank'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new CashCredit;
        $accoutTypes = DB::table('cashcredit_account_type')->where('deleted',0)->where('status',1)->get()->pluck('name','id');
        $detailTypes = array();
        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');
        $currency = json_decode($currency,1);
        ksort($currency);

         $types = DB::table('cashcredit_detail_type')
                    ->select(['cashcredit_detail_type.name','cashcredit_detail_type.id'])
                    ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
                    ->where('cashcredit_account_type.name','Cash/Bank')
                    ->pluck('name', 'id');
        $model->as_of = date('d-m-Y');
        return view('cashcredit.form',['model'=>$model,'accoutTypes'=>$accoutTypes,'detailTypes'=>$detailTypes,'currency'=>$currency,'types'=>$types]);
    }

    public function getdetailtypedata()
    {
        $accountType = $_POST['accountType'];
        $detailTypes = DB::table('cashcredit_detail_type')->where('account_type_id',$accountType)->get();
        $dt = '';
        foreach ($detailTypes as $key => $value) {
           $dt .=  '<option value="'.$value->id.'">'.$value->name.'</option>';
        }
        return $dt;
    }
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        session_start();
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $input['available_balance'] = $input['opening_balance'];
        $currencyData = Currency::getData($input['currency']);
        $input['currency_code'] = $currencyData->code;
        $input['as_of'] = date('Y-m-d',strtotime($input['as_of']));
        $model = CashCredit::create($input);

        // Store deposite activities
        $modelActivities = new Activities;
        $modelActivities->type = 'cashCredit';
        $modelActivities->related_id = $model->id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = $model->opening_balance.'- Opening Balance.';
        $modelActivities->cash_credit_flag = '2';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
        
        // Store Cash/Bank account to QB
        // if(isset($_SESSION['sessionAccessToken'])){
        //     $modeladmin = new Admin();
        //     $modeladmin->qbApiCall('account',$model);
        // }  

        if(isset($_SESSION['sessionAccessToken']))
        {
            $fData['id'] = $model->id;
            $fData['module'] = '2';
            $fData['flagModule'] = 'account';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model='.$newModel);

            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }  
        return redirect('cashcredit');

        Session::flash('flash_message', 'Record has been created successfully');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashCredit  $cashCredit
     * @return \Illuminate\Http\Response
     */
    public function show(CashCredit $cashCredit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CashCredit  $cashCredit
     * @return \Illuminate\Http\Response
     */
    public function edit(CashCredit $cashCredit,$id)
    {
        $checkPermission = User::checkPermission(['update_cash_bank'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = DB::table('cashcredit')->where('id',$id)->first();
        $accoutTypes = DB::table('cashcredit_account_type')->where('deleted',0)->where('status',1)->get()->pluck('name','id');
        $detailTypes = DB::table('cashcredit_detail_type')->where('account_type_id',$model->account_type)->get()->pluck('name','id');
        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');
        $currency = json_decode($currency,1);
        ksort($currency);
         $types = DB::table('cashcredit_detail_type')
                    ->select(['cashcredit_detail_type.name','cashcredit_detail_type.id'])
                    ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
                    ->where('cashcredit_account_type.name','Cash/Bank')
                    ->pluck('name', 'id');
        $model->as_of = date('d-m-Y',strtotime($model->as_of));
        return view("cashcredit.form",['model'=>$model,'accoutTypes'=>$accoutTypes,'detailTypes'=>$detailTypes,'currency'=>$currency,'types'=>$types]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CashCredit  $cashCredit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CashCredit $cashCredit,$id)
    {
        session_start();
        $model = CashCredit::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $currencyData = Currency::getData($request['currency']);
        $input['currency_code'] = $currencyData->code;
        $input['as_of'] = date('Y-m-d',strtotime($input['as_of']));
        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);

        Session::flash('flash_message', 'Record has been updated successfully');
        // Update Cash/Bank account to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            
            $modeladmin = new Admin();
            $modeladmin->qbApiCall('updateAccount',$model);
        } */

        if(isset($_SESSION['sessionAccessToken']))
        {
            $fData['id'] = $model->id;
            $fData['module'] = '2';
            $fData['flagModule'] = 'updateAccount';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model='.$newModel);

            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        return redirect('cashcredit');
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CashCredit  $cashCredit
     * @return \Illuminate\Http\Response
     */
    public function destroy(CashCredit $cashCredit,$id)
    {   
        session_start();
        $data = CashCredit::where('id',$id)->first();
        if($data->available_balance > 0){
            return 'N';
        } else {
            $model = CashCredit::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);
            $model = $data->toArray();
            if(isset($_SESSION['sessionAccessToken']))
            {
                $fData['id'] = $id;
                $fData['module'] = '2';
                $fData['flagModule'] = 'deleteAccount';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                // Store expense to QB
                $newModel = base64_encode(serialize($fData));
                //$newTest = unserialize(base64_decode($newModel, true));
                //pre($newTest);
                $urlAction = url('call/qb?model='.$newModel);

                
                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
        
        // Delete Cash/Bank account to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modeladmin = new Admin();
            $modeladmin->qbApiCall('deleteAccount',$id);
        }*/


        
    }

    public function getbalance()
    {
        $iId = $_POST['tId'];
        $data = DB::table('cashcredit')->where('id',$iId)->first();
        return json_encode($data);
    }


    public function checkunique(Request $request){
        $value = $request->get('value');
        $id = $request->get('id');

        if(!empty($id)){
            
            $data = DB::table('cashcredit')->where('name',$value)->where('id','<>',$id)->where('deleted','0')->count();
        } else {

            $data = DB::table('cashcredit')->where('name',$value)->where('deleted','0')->count();
        }

        if($data){
            return 1;
        } else {
            return 0;
        }
    } 
}
