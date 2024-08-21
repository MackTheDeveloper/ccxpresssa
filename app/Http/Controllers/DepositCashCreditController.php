<?php

namespace App\Http\Controllers;

use App\Depositcashcredit;
use App\Expense;
use App\Activities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
class DepositCashCreditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        $checkPermission = User::checkPermission(['listing_deposite_vouchers'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $deposits = DB::table('deposit_cash_credit')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("depositcashcredit.index",['deposits'=>$deposits]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_deposite_vouchers'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new Depositcashcredit;
        $cashcreditAccounts = DB::table('cashcredit')->where('deleted',0)->where('status',1)->get()->pluck('name','id');
        $allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        
        $allUsers = json_decode($allUsers,1);
        ksort($allUsers);
            
        $model->amount = '0.00';
        return view('depositcashcredit.form',['model'=>$model,'cashcreditAccounts'=>$cashcreditAccounts,'allUsers'=>$allUsers]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 

       $validater = $this->validate($request, [
            'cash_credit_account' => 'required',
            'amount' => 'required|numeric',
            'deposit_date' => 'required',
            'approved_by_user' => 'required'
        ]);
        $input = $request->all();
        $input['created_on'] = gmdate("Y-m-d H:i:s");
        $input['created_by'] = auth()->user()->id;
        $input['deposit_date'] = !empty($input['deposit_date']) ? date('Y-m-d', strtotime($input['deposit_date'])) : null;
        $model = Depositcashcredit::create($input);

        $totalExp = Expense::getExpenseTotalOfSamePettyCash($model->cash_credit_account);
        $getCashCreditData = DB::table('cashcredit')->where('id',$model->cash_credit_account)->first();
        $finalAmt = $getCashCreditData->available_balance + $request['amount'];
        DB::table('cashcredit')->where('id',$model->cash_credit_account)->update(['available_balance' => $finalAmt]);

        // Store deposite activities
        $modelActivities = new Activities;
        $modelActivities->type = 'cashCredit';
        $modelActivities->related_id = $model->cash_credit_account;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = $request['amount'].'-'.$model->comments;
        $modelActivities->cash_credit_flag = '2';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
       
        Session::flash('flash_message', 'Record has been added successfully');
        return redirect('depositcashcredit');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Depositcashcredit  $depositcashcredit
     * @return \Illuminate\Http\Response
     */
    public function show(Depositcashcredit $depositcashcredit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Depositcashcredit  $depositcashcredit
     * @return \Illuminate\Http\Response
     */
    public function edit(Depositcashcredit $depositcashcredit,$id)
    {
        $checkPermission = User::checkPermission(['update_deposite_vouchers'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $model = DB::table('deposit_cash_credit')->where('id',$id)->first();
        $cashcreditAccounts = DB::table('cashcredit')->where('deleted',0)->where('status',1)->get()->pluck('name','id');
        $allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        
        $allUsers = json_decode($allUsers,1);
        ksort($allUsers);
        return view("depositcashcredit.form",['model'=>$model,'cashcreditAccounts'=>$cashcreditAccounts,'allUsers'=>$allUsers]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Depositcashcredit  $depositcashcredit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Depositcashcredit $depositcashcredit,$id)
    {
        $model = Depositcashcredit::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $oldAmount = $model->amount;
        $newAmount = $input['amount'];
        $finalAount = $newAmount - $oldAmount;
        $input['deposit_date'] = !empty($input['deposit_date']) ? date('Y-m-d', strtotime($input['deposit_date'])) : null;
        $model->update($input);

        if($newAmount != $oldAmount)
        {
        $getCashCreditData = DB::table('cashcredit')->where('id',$model->cash_credit_account)->first();
        if($finalAount > 0)
            $finalAmt = $getCashCreditData->available_balance + $finalAount;
        else
            $finalAmt = $getCashCreditData->available_balance - abs($finalAount);
        DB::table('cashcredit')->where('id',$model->cash_credit_account)->update(['available_balance' => $finalAmt]);

        // Store deposite activities
        $modelActivities = new Activities;
        $modelActivities->type = 'cashCredit';
        $modelActivities->related_id = $model->cash_credit_account;
        $modelActivities->user_id   = auth()->user()->id;
        if($finalAount > 0)
        {
            $modelActivities->description = $finalAount.'-'.$model->comments;
            $modelActivities->cash_credit_flag = '2';
        }
        else
        {
            $modelActivities->description = abs($finalAount).'-'.$model->comments;
            $modelActivities->cash_credit_flag = '1';
        }
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
        }


        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('depositcashcredit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Depositcashcredit  $depositcashcredit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Depositcashcredit $depositcashcredit,$id)
    {
        $model = Depositcashcredit::where('id',$id)->update(['deleted'=>1,'deleted_on'=>gmdate("Y-m-d H:i:s")]);
    }
}
