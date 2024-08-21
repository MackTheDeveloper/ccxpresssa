<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UpsFileStatus;
use DB;
use Session;
class FileStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fileStatus = DB::table('inprogress_status')->where('deleted','0')->orderBy('id','DESC')->get();
        
        return view('upsfilestatus.index',['fileStatus'=>$fileStatus]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new UpsFileStatus;
        return view('upsfilestatus.form',['model'=>$model]);
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
        $input['created_by'] = auth()->user()->id;
        $input['created_at'] = date('Y-m-d H:i:s');
        UpsFileStatus::Create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect(route('filestatusindex'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = DB::table('inprogress_status')->where('id',$id)->first();
        return view('upsfilestatus.form',['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $model = UpsFileStatus::find($id);
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect(route('filestatusindex'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('inprogress_status')->where('id',$id)->update(['deleted'=>'1','deleted_by'=>auth()->user()->id,'deleted_at'=>date('Y-m-d h:i:s')]);
        return redirect(route('filestatusindex'));
    }
}
