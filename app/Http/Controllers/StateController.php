<?php

namespace App\Http\Controllers;

use App\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = DB::table('state')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("state.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new State;
        $country = DB::table('country')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $country = json_decode($country,1);
        ksort($country);
        return view('state.form',['model'=>$model,'country'=>$country]);
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
            'name' => 'required|string',
        ]);
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        State::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('states');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function show(State $state)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function edit(State $state,$id)
    {
        $model = DB::table('state')->where('id',$id)->first();
        $country = DB::table('country')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $country = json_decode($country,1);
        ksort($country);
        return view("state.form",['model'=>$model,'country'=>$country]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, State $state,$id)
    {
        $model = State::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('states');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\State  $state
     * @return \Illuminate\Http\Response
     */
    public function destroy(State $state,$id)
    {
        $model = State::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }
}
