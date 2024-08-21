<?php

namespace App\Http\Controllers;

use App\FdCharges;
use App\BillingItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class FdChargesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = DB::table('fd_charges')->orderBy('id', 'desc')->get();
        return view("fd-charges.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new FdCharges;
        return view('fd-charges.form',['model'=>$model]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = FdCharges::create($input);

        // Create billing item
        $checkExist = DB::table('billing_items')->where('item_code',$model->code)->where('deleted','0')->first();
        if(!empty($checkExist))
        {
            DB::table('billing_items')
            ->where('item_code',$model->code)
            ->update(['item_code'=>$model->code,'billing_name'=>$model->name]);
        }else
        {
            $modelBillingItem = new BillingItems();
            $modelBillingItem->item_code = $model->code;
            $modelBillingItem->billing_name = $model->name;
            $modelBillingItem->save();
        }
        

        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('fdcharges');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\FdCharges  $fdCharges
     * @return \Illuminate\Http\Response
     */
    public function show(FdCharges $fdCharges)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FdCharges  $fdCharges
     * @return \Illuminate\Http\Response
     */
    public function edit(FdCharges $fdCharges,$id)
    {
        $model = DB::table('fd_charges')->where('id',$id)->first();
        return view("fd-charges.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FdCharges  $fdCharges
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FdCharges $fdCharges,$id)
    {
        $model = FdCharges::find($id);
        $input = $request->all();
        $model->update($input);

        // Create billing item
        $checkExist = DB::table('billing_items')->where('item_code',$model->code)->where('deleted','0')->first();
        if(!empty($checkExist))
        {
            DB::table('billing_items')
            ->where('item_code',$model->code)
            ->update(['item_code'=>$model->code,'billing_name'=>$model->name]);
        }else
        {
            $modelBillingItem = new BillingItems();
            $modelBillingItem->item_code = $model->code;
            $modelBillingItem->billing_name = $model->name;
            $modelBillingItem->save();
        }
        
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('fdcharges');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FdCharges  $fdCharges
     * @return \Illuminate\Http\Response
     */
    public function destroy(FdCharges $fdCharges,$id)
    {
        $model = fdCharges::where('id',$id)->delete();
    }
}
