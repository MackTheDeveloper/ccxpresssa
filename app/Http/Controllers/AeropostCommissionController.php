<?php

namespace App\Http\Controllers;

use App\AeropostCommission;
use Illuminate\Http\Request;
use DB;
use Session;
class AeropostCommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $commissionData = DB::table('aeropost_commission')->get();
        return view('aeropost-commission.index',['commissionData'=>$commissionData]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new AeropostCommission;
        return view('aeropost-commission._form',['model'=>$model]);
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
        AeropostCommission::Create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('commission/aeropostcommission');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AeropostCommission  $aeropostCommission
     * @return \Illuminate\Http\Response
     */
    public function show(AeropostCommission $aeropostCommission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AeropostCommission  $aeropostCommission
     * @return \Illuminate\Http\Response
     */
    public function edit(AeropostCommission $aeropostCommission,$id)
    {
        $model = DB::table('aeropost_commission')->where('id',$id)->first();
        return view('aeropost-commission._form',['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AeropostCommission  $aeropostCommission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AeropostCommission $aeropostCommission,$id)
    {
        $input = $request->all();
        $model = AeropostCommission::find($id);
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('commission/aeropostcommission');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AeropostCommission  $aeropostCommission
     * @return \Illuminate\Http\Response
     */
    public function destroy(AeropostCommission $aeropostCommission)
    {
        //
    }
}
