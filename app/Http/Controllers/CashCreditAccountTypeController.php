<?php

namespace App\Http\Controllers;

use App\CashCreditAccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;

class CashCreditAccountTypeController extends Controller
{
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_account_types'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cashcredit = DB::table('cashcredit_account_type')
            ->select(['cashcredit_account_type.*', 'quickbook_account_types.name as QbAccountName'])
            ->leftJoin('quickbook_account_types', 'quickbook_account_types.id', '=', 'cashcredit_account_type.quickbook_account_type_id')
            ->where('cashcredit_account_type.deleted', '0')->orderBy('cashcredit_account_type.id', 'desc')->get();
        return view("cashcreditaccounttype.index", ['cashcredit' => $cashcredit]);
    }

    public function create()
    {
        $checkPermission = User::checkPermission(['add_account_types'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new CashCreditAccountType;

        $QbAccounts = DB::table('quickbook_account_types')->pluck('name', 'id');

        return view('cashcreditaccounttype.form', ['model' => $model, 'QbAccounts' => $QbAccounts]);
    }

    public function store(Request $request)
    {
        $validater = $this->validate($request, [
            'name' => 'required|string',
        ]);
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = CashCreditAccountType::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('cashcreditaccounttype');
    }

    public function edit(CashCreditAccountType $CashCreditAccountType, $id)
    {
        $checkPermission = User::checkPermission(['update_account_types'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('cashcredit_account_type')->where('id', $id)->first();
        $QbAccounts = DB::table('quickbook_account_types')->pluck('name', 'id');
        return view("cashcreditaccounttype.form", ['model' => $model, 'QbAccounts' => $QbAccounts]);
    }

    public function update(Request $request, CashCreditAccountType $CashCreditAccountType, $id)
    {
        $model = CashCreditAccountType::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('cashcreditaccounttype');
    }

    public function destroy(CashCreditAccountType $CashCreditAccountType, $id)
    {
        $model = CashCreditAccountType::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);
    }
}
