<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Clients;
use App\ExpenseDetails;
use App\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Session;
use PDF;
use App\User;
use Illuminate\Support\Facades\Storage;
use Config;
class AgentUpsExpenseController extends Controller
{
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_courier_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $cargoExpenseDataByVoucher = DB::table('expenses')
                        //->select(DB::raw('count(*) as user_count, voucher_number'))
                        ->whereNotNull('ups_details_id')
                        ->where('deleted','0')
                        ->where('request_by_role','12')
                        ->where('request_by',Auth::user()->id)
                        ->orderBy('expense_id', 'desc')
                        ->get();
        return view("agent-role.upsexpenses.index",['cargoExpenseDataByVoucher'=>$cargoExpenseDataByVoucher]);
    }

    public function create($upsId = null)
    {
        $checkPermission = User::checkPermission(['add_courier_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new Expense;

        $billingParty = DB::table('clients')->select(['id','company_name'])->where('client_flag','B')->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty,1);
        ksort($billingParty);


        $cashCredit = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted',0)->where('status',1)->pluck('cashcreditData', 'id');
        $cashCredit = json_decode($cashCredit,1);
        ksort($cashCredit);
        
        
        $dataPaidTo = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo,1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted',0)->where('status',1)->get()->pluck('billing_name','id');

        $dataCost = DB::table('costs')
        ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
        ->orderBy('id','desc')->where('deleted',0)->where('status',1)->get()
        ->pluck('fullcost','id');
        

        //$dataAwbNos = DB::table('ups_details')->where('deleted',0)->whereNotNull('awb_number')->get()->pluck('awb_number','id');
        //$dataAwbNos = DB::table('ups_details')->where('deleted',0)->get()->pluck('awb_number','id');
        $dataAwbNos = array();
        $dataFileNumber = DB::table('ups_details')->whereNotNull('billing_party')->whereNull('file_close')->where('deleted',0)->get()->pluck('file_number','id');

        $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
        if(empty($getLastExpense))
            $voucherNo = '1001';
        else
            $voucherNo = $getLastExpense->voucher_number + 1;

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
        ->join('currency', 'currency.id', '=', 'vendors.currency')
        ->where('vendors.deleted',0)->where('vendors.status',1)->orderBy('vendors.id','desc')->pluck('vendorData', 'id');

        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');
        $currency = json_decode($currency,1);
        ksort($currency);

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id','name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status',1)->orderBy('id','desc')->pluck('name', 'id');

        $adminManagersUsers = DB::table('users')->select(['id','name'])->where('department','13')->where('status',1)->orderBy('id','desc')->pluck('name', 'id');;

        $cashier = DB::table('users')->select(['id','name'])->where('deleted',0)->where('department','11')->orderBy('id','desc')->pluck('name', 'id');

        return view('agent-role.upsexpenses._form',['model'=>$model,'billingParty'=>$billingParty,'dataBillingItems'=>$dataBillingItems,'dataAwbNos'=>$dataAwbNos,'dataFileNumber'=>$dataFileNumber,'voucherNo'=>$voucherNo,'dataPaidTo'=>$dataPaidTo,'dataCost'=>$dataCost,'upsId'=>$upsId,'allUsers'=>$allUsers,'cashCredit'=>$cashCredit,'currency'=>$currency,'adminManagersRole'=>$adminManagersRole,'adminManagersUsers'=>$adminManagersUsers,'cashier'=>$cashier]);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $fileData = DB::table('ups_details')->where('id', $input['file_number'])->where('deleted', 0)->first();
        //$dataExpense = DB::table('expenses')->where('voucher_number',$input['voucher_number'])->first();
        $dataExpense = array();
        if(!empty($dataExpense))
        {
            $model = ExpenseDetails::where('expense_id',$dataExpense->expense_id)->delete();
            $model = Expense::find($dataExpense->expense_id);
            $model->fill($request->input());
            Activities::log('update','upsexpense',$model);
            $input = $request->all();
            $input['exp_date'] = date('Y-m-d',strtotime($input['exp_date']));
            $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
            $input['display_notification_admin'] = '1';
            $input['display_notification_cashier_for_ups'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $model->update($input);

            $countexp = $_POST['count_expense'];
            $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);
            
            
            for($i = 0; $i < $countexp; $i++)
            {
                $modelExp = new ExpenseDetails();
                $modelExp->expense_id = $model->expense_id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->expense_type = $input['expense_type'][$i];
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->paid_to = $input['paid_to'][0];
                $modelExp->save();
            }
        }else
        {
            $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
            if (empty($getLastExpense))
                $input['voucher_number'] = '1001';
            else
                $input['voucher_number'] = $getLastExpense->voucher_number + 1;

            $input['created_on'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = Auth::user()->id;
            $input['request_by'] = Auth::user()->id;
            $input['request_by_role'] = '12';
            $input['expense_request'] = 'Requested';
            $input['exp_date'] = date('Y-m-d',strtotime($input['exp_date']));
            $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
            $input['display_notification_admin'] = '1';
            $input['display_notification_cashier_for_ups'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $model = Expense::create($input);

            $data['id'] = $_POST['ups_details_id'];
            $dataCargo = DB::table('ups_details')->where('id',$data['id'])->first();
            $data['flagExpense'] = $dataCargo->file_number;
            Activities::log('create','upsexpense',(object)$data);
            
            
            $countexp = $_POST['count_expense'];
            $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
            $input['amount'] = array_values($input['expenseDetails']['amount']);
            $input['description'] = array_values($input['expenseDetails']['description']);
            $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);
            
            for($i = 0; $i < $countexp; $i++)
            {
                $modelExp = new ExpenseDetails();
                $modelExp->expense_id = $model->expense_id;
                $modelExp->voucher_number = $model->voucher_number;
                $modelExp->expense_type = $input['expense_type'][$i];
                $modelExp->amount = $input['amount'][$i];
                $modelExp->description = $input['description'][$i];
                $modelExp->paid_to = $input['paid_to'][0];
                $modelExp->save();
            }

            // Store expense activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'ups';
            $modelActivities->related_id = $model->ups_details_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Expense #' . $model->voucher_number . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {

                $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();

                $dataCargo = DB::table('ups_details')->where('id', $model->ups_details_id)->first();
                $pdf = PDF::loadView('upsexpenses.printupsexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
                $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
                $pdf_path = 'public/upsExpensePdf/' . $pdf_file;
                $pdf->save($pdf_path);
                $s3path = 'Files/Courier/Ups/';
                if ($fileData->courier_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Expenses/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Expenses/';
                }

                $filecontent = file_get_contents($pdf_path);
                $success = Storage::disk('s3')->put($s3path . $fileData->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');
                Session::flash('flash_message', 'Expense has been created successfully');
                return url('/') . '/' . $pdf_path;
            }
        }
    }

    public function edit(Expense $expense,$id)
    {
        $checkPermission = User::checkPermission(['update_courier_expenses'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        $model =  Expense::find($id);

        $billingParty = DB::table('clients')->select(['id','company_name'])->where('client_flag','B')->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty,1);
        ksort($billingParty);

        $cashCredit = DB::table('cashcredit')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit,1);
        ksort($cashCredit);
        
        
        $dataPaidTo = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $dataPaidTo = json_decode($dataPaidTo,1);
        ksort($dataPaidTo);

        $dataBillingItems = DB::table('billing_items')->where('deleted',0)->where('status',1)->get()->pluck('billing_name','id');

        $dataCost = DB::table('costs')
        ->select(DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
        ->orderBy('id','desc')->where('deleted',0)->where('status',1)->get()
        ->pluck('fullcost','id');
        

        
        //$dataAwbNos = DB::table('ups_details')->where('deleted',0)->whereNotNull('awb_number')->get()->pluck('awb_number','id');
        //$dataAwbNos = DB::table('ups_details')->where('deleted',0)->get()->pluck('awb_number','id');
        $dataAwbNos = array();
        $dataFileNumber = DB::table('ups_details')->whereNotNull('billing_party')->whereNull('file_close')->where('deleted',0)->get()->pluck('file_number','id');

        $dataExpenseDetails  = DB::table('expense_details')->where('expense_id',$id)->where('deleted',0)->get();
        $dataExpenseDetails = json_decode(json_encode($dataExpenseDetails));
        
        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        //$allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->pluck('company_name', 'id');
        $allUsers = DB::table('vendors')->select(DB::raw("vendors.id,CONCAT(vendors.company_name,' - ',currency.code) as vendorData"))
        ->join('currency', 'currency.id', '=', 'vendors.currency')
        ->where('vendors.deleted',0)->where('vendors.status',1)->orderBy('vendors.id','desc')->pluck('vendorData', 'id');

        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');
        $currency = json_decode($currency,1);
        ksort($currency);

        $adminManagersRole = DB::table('cashcredit_detail_type')->select(['id','name'])
            ->whereIn('name', Config::get('app.adminManagers'))->where('status',1)->orderBy('id','desc')->pluck('name', 'id');

        $adminManagersUsers = DB::table('users')->select(['id','name'])->where('department',$model->admin_manager_role)->orderBy('id','desc')->pluck('name', 'id');

        $model->admin_managers = explode(',', $model->admin_managers);

        $cashier = DB::table('users')->select(['id','name'])->where('deleted',0)->where('department','11')->orderBy('id','desc')->pluck('name', 'id');
        
        return view('agent-role.upsexpenses._formedit',['model'=>$model,'billingParty'=>$billingParty,'flag'=>'cargo','dataBillingItems'=>$dataBillingItems,'dataAwbNos'=>$dataAwbNos,'dataFileNumber'=>$dataFileNumber,'dataPaidTo'=>$dataPaidTo,'dataCost'=>$dataCost,'dataExpenseDetails'=>$dataExpenseDetails,'allUsers'=>$allUsers,'cashCredit'=>$cashCredit,'currency'=>$currency,'adminManagersRole'=>$adminManagersRole,'adminManagersUsers'=>$adminManagersUsers,'cashier'=>$cashier]);
    }

     public function update(Request $request,$id)
    {
        $model = ExpenseDetails::where('expense_id',$id)->delete();
        $model = Expense::find($id);
        $model->fill($request->input());
        Activities::log('update','upsexpense',$model);
        $input = $request->all();
        $input['exp_date'] = date('Y-m-d',strtotime($input['exp_date']));
        $input['admin_managers'] = isset($input['admin_managers']) ? implode(',', $input['admin_managers']) : '';
        $model->update($input);
        
        $countexp = $_POST['count_expense'];
        $input['expense_type'] = array_values($input['expenseDetails']['expense_type']);
        $input['amount'] = array_values($input['expenseDetails']['amount']);
        $input['description'] = array_values($input['expenseDetails']['description']);
        $input['paid_to'] = array_values($input['expenseDetails']['paid_to']);
        
        
        for($i = 0; $i < $countexp; $i++)
        {
            $modelExp = new ExpenseDetails();
            $modelExp->expense_id = $model->expense_id;
            $modelExp->voucher_number = $model->voucher_number;
            $modelExp->expense_type = $input['expense_type'][$i];
            $modelExp->amount = $input['amount'][$i];
            $modelExp->description = $input['description'][$i];
            $modelExp->paid_to = $input['paid_to'][0];
            $modelExp->save();
        }

        $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataCargo = DB::table('ups_details')->where('id', $model->ups_details_id)->first();
        $pdf = PDF::loadView('upsexpenses.printupsexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/upsExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Ups/';
        if ($dataCargo->courier_operation_type == 1) {
            $s3path .= 'Import/' . $dataCargo->file_number . '/Expenses/';
        } else {
            $s3path .= 'Export/' . $dataCargo->file_number . '/Expenses/';
        }
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $dataCargo->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

        Session::flash('flash_message', 'Record has been updated successfully');
    }
}
