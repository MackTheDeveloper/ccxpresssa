<?php

namespace App\Http\Controllers;

use App\OtherExpenseItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
class OtherExpenseItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_other_expense_items'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('other_expense_items')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("other-expense-items.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_other_expense_items'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new OtherExpenseItems;
        return view('other-expense-items.form',['model'=>$model]);
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
        OtherExpenseItems::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('otherexpenseitems');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OtherExpenseItems  $otherExpenseItems
     * @return \Illuminate\Http\Response
     */
    public function show(OtherExpenseItems $otherExpenseItems)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OtherExpenseItems  $otherExpenseItems
     * @return \Illuminate\Http\Response
     */
    public function edit(OtherExpenseItems $otherExpenseItems,$id)
    {
        $checkPermission = User::checkPermission(['update_other_expense_items'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = DB::table('other_expense_items')->where('id',$id)->first();
        return view("other-expense-items.form",['model'=>$model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OtherExpenseItems  $otherExpenseItems
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OtherExpenseItems $otherExpenseItems,$id)
    {
        $model = OtherExpenseItems::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('otherexpenseitems');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OtherExpenseItems  $otherExpenseItems
     * @return \Illuminate\Http\Response
     */
    public function destroy(OtherExpenseItems $otherExpenseItems,$id)
    {
        $model = OtherExpenseItems::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }
}
