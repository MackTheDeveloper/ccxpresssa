<?php

namespace App\Http\Controllers;

use App\NatureOfServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
class NatureOfServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('nature_of_services')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("nature-of-services.index",['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new NatureOfServices;
        return view('nature-of-services.form',['model'=>$model]);
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
        $model = NatureOfServices::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('natureofservice');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\NatureOfServices  $natureOfServices
     * @return \Illuminate\Http\Response
     */
    public function show(NatureOfServices $natureOfServices)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\NatureOfServices  $natureOfServices
     * @return \Illuminate\Http\Response
     */
    public function edit(NatureOfServices $natureOfServices,$id)
    {
        $model = DB::table('nature_of_services')->where('id',$id)->first();
        return view("nature-of-services.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\NatureOfServices  $natureOfServices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NatureOfServices $natureOfServices,$id)
    {
        $model = NatureOfServices::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('natureofservice');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\NatureOfServices  $natureOfServices
     * @return \Illuminate\Http\Response
     */
    public function destroy(NatureOfServices $natureOfServices,$id)
    {
        $model = NatureOfServices::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }
}
