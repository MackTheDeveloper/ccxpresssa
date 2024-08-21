<?php

namespace App\Http\Controllers;

use App\Customs;
use App\CustomsDetails;
use App\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Session;
use PDF;
use App\User;

class CustomsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cargoExpenseDataByVoucher = DB::table('customs')
                        //->select(DB::raw('count(*) as user_count, voucher_number'))
                        ->whereNotNull('cargo_id')
                        ->where('deleted','0')
                        //->where('expense_request','Approved')
                        ->orderBy('id', 'desc')
                        ->get();
        return view("customs.index",['cargoExpenseDataByVoucher'=>$cargoExpenseDataByVoucher]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($cargoId = null)
    {

        $model = new Customs;

        $dataBilligParty = DB::table('vendors')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $dataBilligParty = json_decode($dataBilligParty,1);
        ksort($dataBilligParty);


        $cashCredit = DB::table('cashcredit')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit,1);
        ksort($cashCredit);
        
        
        $dataPaidTo = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo,1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted',0)->where('status',1)->get()->pluck('billing_name','id');

        $dataCost = DB::table('costs')
        ->select(DB::raw("id,CONCAT(cost_name,' - ',cost_billing_code) as fullcost"))
        ->where('deleted',0)->where('status',1)->get()
        ->pluck('fullcost','id');
        

        $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id');

        $dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');

        $getLastExpense = DB::table('customs')->orderBy('id', 'desc')->first();
        if(empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        $allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $allUsers = json_decode($allUsers,1);
        ksort($allUsers);

        return view('customs._form',['model'=>$model,'dataBilligParty'=>$dataBilligParty,'dataBillingItems'=>$dataBillingItems,'dataAwbNos'=>$dataAwbNos,'dataFileNumber'=>$dataFileNumber,'voucherNo'=>$voucherNo,'dataPaidTo'=>$dataPaidTo,'dataCost'=>$dataCost,'cargoId'=>$cargoId,'allUsers'=>$allUsers,'cashCredit'=>$cashCredit]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customs  $customs
     * @return \Illuminate\Http\Response
     */
    public function show(Customs $customs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customs  $customs
     * @return \Illuminate\Http\Response
     */
    public function edit(Customs $customs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customs  $customs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customs $customs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customs  $customs
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customs $customs)
    {
        //
    }

    public function checkuniquecustomfilenumber()
    {
     
        $value = $_POST['value'];
        $upsId = $_POST['upsId'];
        
        $data = DB::table('customs')->where('deleted','0')->where('file_number',$value)
        ->where('ups_details_id','<>',$upsId)
        ->count();

        if($data)
            return 1;
        else
            return 0;
    }
    
}
