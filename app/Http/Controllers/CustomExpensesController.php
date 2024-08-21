<?php

namespace App\Http\Controllers;

use App\CustomExpenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Activities;
use App\Customs;
use Session;
use PDF;
use App\User;
use App\CustomExpensesDetails;
use Auth;

class CustomExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_courier_custom_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
         $cargoExpenseDataByVoucher = DB::table('custom_expenses')
                        ->select('custom_expenses.*')
                        ->join('ups_details', 'ups_details.id', '=','custom_expenses.ups_details_id' )
                        ->whereNotNull('custom_expenses.ups_details_id')
                        ->where('custom_expenses.deleted','0')
                        //->where('expense_request','Approved')
                        ->orderBy('custom_expenses.id', 'desc')
                        ->get();
        return view("custom-expenses.index",['cargoExpenseDataByVoucher'=>$cargoExpenseDataByVoucher]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($upsId = null)
    {
        $checkPermission = User::checkPermission(['add_courier_custom_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new CustomExpenses;

        $customFileIds = DB::table('custom_expenses')->select('ups_details_id')->where('deleted',0)->whereNotNull('ups_details_id')->get();
        $arCustom = array();
        foreach ($customFileIds as $key => $value) {
            $arCustom[] = $value->ups_details_id;
        }

        $dataFileNumber = DB::table('ups_details')
        ->select(DB::raw("ups_details.id,CONCAT(COALESCE(awb_number,''),' - ',COALESCE(file_number,'')) as fullname"))
        ->orderBy('ups_details.id','desc')->where('ups_details.deleted',0)->where('ups_details.status',1)
        //->whereNotIn('ups_details.id',$arCustom)
        ->where('ups_details.courier_operation_type','1')
        ->pluck('fullname','ups_details.id');
        /* $dataFileNumber = DB::table('ups_details')
        ->leftJoin('clients', 'ups_details.billing_party', '=','clients.id' )
        ->select(DB::raw("ups_details.id,CONCAT(COALESCE(awb_number,''),' - ',COALESCE(clients.company_name,'')) as fullname"))
        ->orderBy('ups_details.id','desc')->where('ups_details.deleted',0)->where('ups_details.status',1)
        ->pluck('fullname','ups_details.id'); */
        

        $getLastExpense = DB::table('custom_expenses')->orderBy('id', 'desc')->first();
        if(empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;
        $model->custom_file_number = 'E';
        return view('custom-expenses._form',['model'=>$model,'dataFileNumber'=>$dataFileNumber,'voucherNo'=>$voucherNo,'upsId'=>$upsId]);
    }

    public function getcustomdata()
    {
        $id = $_POST['id'];
        $data = DB::table('customs')->where('ups_details_id', $id)->first();
        return json_encode($data);
    }

    public function addmorecustomexpense()
    {
        $model = new CustomExpenses;
        return view('custom-expenses.addmorecustomexpense',['model'=>$model,'counter'=>$_POST['counter']]);
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


        $dataExpense = DB::table('custom_expenses')->where('voucher_number',$input['voucher_number'])->first();
        if(!empty($dataExpense))
        {
            $model = CustomExpensesDetails::where('expense_id',$dataExpense->id)->delete();
            $model = CustomExpenses::find($dataExpense->id);
            $input = $request->all();
            $input['exp_date'] = date('Y-m-d',strtotime($input['exp_date']));
            

            $modelCustom =  DB::table('customs')->where('ups_details_id',$input['ups_details_id'])->first();
            if(empty($modelCustom))
            {
                $modelCustom = new Customs;
                $modelCustom->file_number = $input['custom_file_number']; 
                $modelCustom->custom_date = date('Y-m-d');
                $modelCustom->ups_details_id = $input['ups_details_id'];
                $modelCustom->save();
                $input['custom_id'] = $modelCustom->id;
            }else
            {
                DB::table('customs')
                    ->where('ups_details_id', $input['ups_details_id'])
                    ->update(['file_number' => $input['custom_file_number']]);    
            }

            $model->update($input);
            $countexp = $_POST['count_expense'];
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            
            for($i = 0; $i < $countexp; $i++)
            {
                $modelExp = new CustomExpensesDetails();
                $modelExp->expense_id = $model->id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->save();
            }
        }else
        {
            $input['created_on'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = Auth::user()->id;
            $input['exp_date'] = date('Y-m-d',strtotime($input['exp_date']));

            $modelCustom =  DB::table('customs')->where('ups_details_id',$input['ups_details_id'])->first();
            if(empty($modelCustom))
            {
                $modelCustom = new Customs;
                $modelCustom->file_number = $input['custom_file_number']; 
                $modelCustom->custom_date = date('Y-m-d');
                $modelCustom->ups_details_id = $input['ups_details_id'];
                $modelCustom->save();
                $input['custom_id'] = $modelCustom->id;
            }
            else
            {
                DB::table('customs')
                    ->where('ups_details_id', $input['ups_details_id'])
                    ->update(['file_number' => $input['custom_file_number']]);    
            }

            $model = CustomExpenses::create($input);

            $countexp = $_POST['count_expense'];
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            
            
            for($i = 0; $i < $countexp; $i++)
            {
                $modelExp = new CustomExpensesDetails();
                $modelExp->expense_id = $model->id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->save();
            }
        }
    }

    public function generatecustomexpensevoucheronsavenext()
    {
        $getLastExpense = DB::table('custom_expenses')->orderBy('id', 'desc')->first();
        if(empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        return $voucherNo;
    }

    

    public function expandcustomexpenses()
    {
        $expenseId = $_POST['expenseId'];
        $rowId = $_POST['rowId'];
        
        $packageData = DB::table('custom_expense_details')->where('expense_id',$expenseId)->where('deleted',0)->get();
        $data = DB::table('custom_expenses')->where('id',$expenseId)->first();
        $dataUps = DB::table('ups_details')->where('id',$data->ups_details_id)->first();
        
        return view('custom-expenses.rendercustomexpenses',['packageData'=>$packageData,'rowId'=>$rowId,'dataUps'=>$dataUps]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CustomExpenses  $customExpenses
     * @return \Illuminate\Http\Response
     */
    public function show(CustomExpenses $customExpenses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CustomExpenses  $customExpenses
     * @return \Illuminate\Http\Response
     */
    public function edit(CustomExpenses $customExpenses,$id)
    {
        $checkPermission = User::checkPermission(['update_courier_custom_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $model = CustomExpenses::find($id);

        /*$customFileIds = DB::table('customs')->select('ups_details_id')->where('deleted',0)->whereNotNull('ups_details_id')->get();
        $arCustom = array();
        foreach ($customFileIds as $key => $value) {
            $arCustom[] = $value->ups_details_id;
        }*/
        //$dataFileNumber = DB::table('ups_details')->where('deleted',0)->whereIn('id',$arCustom)->get()->pluck('file_number','id');

        $dataFileNumber = DB::table('ups_details')
        ->select(DB::raw("ups_details.id,CONCAT(COALESCE(awb_number,''),' - ',COALESCE(file_number,'')) as fullname"))
        ->orderBy('ups_details.id','desc')->where('ups_details.deleted',0)->where('ups_details.status',1)
        ->pluck('fullname','ups_details.id');
        
        /* $dataFileNumber = DB::table('ups_details')
        ->leftJoin('clients', 'ups_details.billing_party', '=','clients.id' )
        ->select(DB::raw("ups_details.id,CONCAT(COALESCE(awb_number,''),' - ',COALESCE(clients.company_name,'')) as fullname"))
        ->orderBy('ups_details.id','desc')->where('ups_details.deleted',0)->where('ups_details.status',1)
        ->pluck('fullname','ups_details.id'); */

        $dataExpenseDetails  = DB::table('custom_expense_details')->where('expense_id',$id)->where('deleted',0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));

        return view('custom-expenses._formedit',['model'=>$model,'dataFileNumber'=>$dataFileNumber,'dataExpenseDetails'=>$dataExpenseDetails]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CustomExpenses  $customExpenses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomExpenses $customExpenses,$id)
    {
        $model = CustomExpensesDetails::where('expense_id',$id)->delete();
        $model = CustomExpenses::find($id);
        $input = $request->all();
        
        $input['exp_date'] = date('Y-m-d',strtotime($input['exp_date']));
        $model->update($input);

        $countexp = $_POST['count_expense'];
        $input['amount'] = array_values($input['expenseDetails']['amount']);
        $input['description'] = array_values($input['expenseDetails']['description']);
        
        
        
        for($i = 0; $i < $countexp; $i++)
        {
            $modelExp = new CustomExpensesDetails();
            $modelExp->expense_id = $model->id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->save();
        }
    }

    public function destroy(CustomExpenses $CustomExpenses,$id)
    {
        $model = CustomExpensesDetails::where('id',$id)->delete();
    }

    public function deletecustomexpnesevoucher(CustomExpenses $CustomExpenses,$id)
    {
        $model = CustomExpenses::where('id',$id)->delete();
    }
}
