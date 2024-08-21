<?php

namespace App\Http\Controllers;

use App\PaymentTerms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
class PaymentTermsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_payment_terms'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('payment_terms')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("payment-terms.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_payment_terms'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new PaymentTerms;
        return view('payment-terms.form',['model'=>$model]);
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
            'title' => 'required|string',
        ]);
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        PaymentTerms::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('paymentterms');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PaymentTerms  $paymentTerms
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentTerms $paymentTerms)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PaymentTerms  $paymentTerms
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentTerms $paymentTerms,$id)
    {
        $checkPermission = User::checkPermission(['update_payment_terms'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $model = DB::table('payment_terms')->where('id',$id)->first();
        return view("payment-terms.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PaymentTerms  $paymentTerms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentTerms $paymentTerms,$id)
    {
        $model = PaymentTerms::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('paymentterms');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PaymentTerms  $paymentTerms
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentTerms $paymentTerms,$id)
    {
        $model = PaymentTerms::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }
}
