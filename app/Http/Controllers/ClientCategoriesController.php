<?php

namespace App\Http\Controllers;

use App\ClientCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;

class ClientCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = DB::table('client_categories')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("client-categories.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new ClientCategories;
        return view('client-categories.form',['model'=>$model]);
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
        ClientCategories::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('clientcategories');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientCategories  $clientCategories
     * @return \Illuminate\Http\Response
     */
    public function show(ClientCategories $clientCategories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientCategories  $clientCategories
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientCategories $clientCategories,$id)
    {
        $model = DB::table('client_categories')->where('id',$id)->first();
        return view("client-categories.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientCategories  $clientCategories
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientCategories $clientCategories,$id)
    {
        $model = ClientCategories::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('clientcategories');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientCategories  $clientCategories
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientCategories $clientCategories,$id)
    {
        $model = ClientCategories::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }
}
