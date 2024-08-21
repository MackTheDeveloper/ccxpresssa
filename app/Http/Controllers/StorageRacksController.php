<?php

namespace App\Http\Controllers;

use App\StorageRacks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use Session;
class StorageRacksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_storage_racks'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('storage_racks')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("storage-racks.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_storage_racks'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new storageRacks;
        $alphas = range('A', 'Z');
        foreach ($alphas as $key => $value) {
            $alphaAll[$value] = $value;
        }

        foreach (range(1, 10) as $k => $number) {
            $locationNumbers[$number] = $number;
        }
        return view('storage-racks.form',['model'=>$model,'alphaAll'=>$alphaAll,'locationNumbers'=>$locationNumbers]);
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
        $model = storageRacks::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('storageracks');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\StorageRacks  $storageRacks
     * @return \Illuminate\Http\Response
     */
    public function show(StorageRacks $storageRacks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\StorageRacks  $storageRacks
     * @return \Illuminate\Http\Response
     */
    public function edit(StorageRacks $storageRacks,$id)
    {
        $checkPermission = User::checkPermission(['update_storage_racks'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = DB::table('storage_racks')->where('id',$id)->first();
        $alphas = range('A', 'Z');
        foreach ($alphas as $key => $value) {
            $alphaAll[$value] = $value;
        }

        foreach (range(1, 10) as $k => $number) {
            $locationNumbers[$number] = $number;
        }
        return view("storage-racks.form",['model'=>$model,'alphaAll'=>$alphaAll,'locationNumbers'=>$locationNumbers]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\StorageRacks  $storageRacks
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, StorageRacks $storageRacks,$id)
    {
        $model = StorageRacks::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('storageracks');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\StorageRacks  $storageRacks
     * @return \Illuminate\Http\Response
     */
    public function destroy(StorageRacks $storageRacks,$id)
    {
        $model = StorageRacks::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }

    
    public function checkuniqueracklocation()
    {
        
        $rackDepartment = $_POST['rackDepartment'];
        $mainSection = $_POST['mainSection'];
        $subSection = $_POST['subSection'];
        $locationNumber = $_POST['locationNumber'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if($flag == 'edit')
        {
            $countRacks = DB::table('storage_racks')
                ->where('deleted','0')
                ->where('status','1')
                ->where('rack_department',$rackDepartment)
                ->where('main_section',$mainSection)
                ->where('sub_section',$subSection)
                ->where('location_number',$locationNumber)
                ->where('id','<>',$id)
                ->count();
        }
        else
        {
            $countRacks = DB::table('storage_racks')
                ->where('deleted','0')
                ->where('status','1')
                ->where('rack_department',$rackDepartment)
                ->where('main_section',$mainSection)
                ->where('sub_section',$subSection)
                ->where('location_number',$locationNumber)
                ->count();
        }

        if($countRacks)
            return 1;
        else
            return 0;
    }
}
