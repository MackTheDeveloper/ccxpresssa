<?php

namespace App\Http\Controllers;

use App\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
use App\Cargo;
use App\Ups;
use App\Aeropost;
use App\ccpack;
class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_warehouses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('warehouse')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("warehouses.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_warehouses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new Warehouse;
        return view('warehouses.form',['model'=>$model]);
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
        Warehouse::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('warehouses');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function show(Warehouse $warehouse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function edit(Warehouse $warehouse,$id)
    {
        $checkPermission = User::checkPermission(['update_warehouses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $model = DB::table('warehouse')->where('id',$id)->first();
        return view("warehouses.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warehouse $warehouse,$id)
    {
        $model = Warehouse::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('warehouses');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse,$id)
    {
        $model = Warehouse::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }

    public function addwarehouseinfile($moduleId = null,$flagModule = null)
    {
        $checkPermission = User::checkPermission(['add_warehouses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        if($flagModule == 'cargo')
        {
            $model = Cargo::find($moduleId);
            $warehouses = DB::table('warehouse')->select(['id','name'])->where('deleted',0)->where('status',1)->where('warehouse_for','Cargo')->pluck('name', 'id');
        }else if($flagModule == 'ups')
        {
            $model = Ups::find($moduleId);
            $warehouses = DB::table('warehouse')->select(['id','name'])->where('deleted',0)->where('status',1)->where('warehouse_for','Courier')->pluck('name', 'id');
        }else if($flagModule == 'aeropost')
        {
            $model = Aeropost::find($moduleId);
            $warehouses = DB::table('warehouse')->select(['id','name'])->where('deleted',0)->where('status',1)->where('warehouse_for','Courier')->pluck('name', 'id');
        }else if($flagModule == 'ccpack')
        {
            $model = ccpack::find($moduleId);
            $warehouses = DB::table('warehouse')->select(['id','name'])->where('deleted',0)->where('status',1)->where('warehouse_for','Courier')->pluck('name', 'id');
        }
        $warehouses = json_decode($warehouses,1);
        ksort($warehouses);
        return view('warehouses.formaddwarehouseinfile',['model'=>$model,'warehouses'=>$warehouses,'moduleId'=>$moduleId,'flagModule'=>$flagModule]);
    }

    public function addcashcreditinfile($cargoId)
    {
        $checkPermission = User::checkPermission(['add_warehouses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = Cargo::find($cargoId);

        return view('warehouses.formaddcashcreditinfile',['model'=>$model,'cargoId'=>$cargoId]);
    }

    public function storewarehouseinfile(Request $request)
    {
        $input = $request->all();
        if($input['flagModule'] == 'cargo')
            $model = Cargo::find($input['id']);
        else if($input['flagModule'] == 'ups')
            $model = Ups::find($input['id']);
        else if($input['flagModule'] == 'aeropost')
            $model = Aeropost::find($input['id']);
        else if($input['flagModule'] == 'ccpack')
            $model = ccpack::find($input['id']);
        $model->update($input);
        return 'true';
    }
    public function storecashcreditinfile(Request $request)
    {
        $input = $request->all();
        $model = Cargo::find($input['id']);
        $model->update($input);
        return 'true';
    }
}
