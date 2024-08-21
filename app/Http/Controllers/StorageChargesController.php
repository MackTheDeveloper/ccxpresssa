<?php

namespace App\Http\Controllers;

use App\StorageCharges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use Session;
class StorageChargesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_storage_charges'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('storage_charges')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("storage-charges.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_storage_charges'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new storageCharges;
        return view('storage-charges.form',['model'=>$model]);
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
        $model = storageCharges::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('storagecharges');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StorageCharges  $storageCharges
     * @return \Illuminate\Http\Response
     */
    public function show(StorageCharges $storageCharges)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StorageCharges  $storageCharges
     * @return \Illuminate\Http\Response
     */
    public function edit(StorageCharges $storageCharges,$id)
    {
        $checkPermission = User::checkPermission(['update_storage_charges'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = DB::table('storage_charges')->where('id',$id)->first();
        
        return view("storage-charges.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StorageCharges  $storageCharges
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StorageCharges $storageCharges,$id)
    {
        $model = storageCharges::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('storagecharges');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StorageCharges  $storageCharges
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorageCharges $storageCharges,$id)
    {
        $model = StorageCharges::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }

    public function checkuniquemeasure()
    {
        $measure = $_POST['measure'];
        $id = $_POST['id'];
        if(!empty($id))
            $upsData = DB::table('storage_charges')->where('deleted','0')->where('measure',$measure)->where('id','<>',$id)->count();
        else
            $upsData = DB::table('storage_charges')->where('deleted','0')->where('measure',$measure)->count();

        if($upsData)
            return 1;
        else
            return 0;
    }
}
