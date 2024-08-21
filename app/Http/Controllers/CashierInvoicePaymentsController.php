<?php

namespace App\Http\Controllers;

use App\InvoicePayments;
use App\Invoices;
use App\Activities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use Config;
class CashierInvoicePaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($cargoId = null,$invoceId = null,$billingParty = null,$fromMenu = null)
    {
        $checkPermission = User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new InvoicePayments;

        $cashCredit = DB::table('cashcredit')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit,1);
        ksort($cashCredit);

        $dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');

        $cashCredit = DB::table('cashcredit')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit,1);
        ksort($cashCredit);

        $model->amount = '0.00';
        $model->cargo_id = $cargoId;
        $model->invoice_id = $invoceId;
        $model->client = $billingParty;
        

        $invoiceArray = DB::table('invoices')->whereNotNull('cargo_id')
            ->where(function ($query) {
                    $query->where('payment_status','Pending')
                          ->orWhere('payment_status','Partial');
                })
            ->where('deleted',0)
            ->get()->pluck('bill_no','id');


        $pendingInvoicesClients = DB::table('invoices')
            ->select(DB::raw("GROUP_CONCAT(bill_to) AS billTo"))
            ->whereNotNull('cargo_id')
            ->where(function ($query) {
                    $query->where('payment_status','Pending')
                          ->orWhere('payment_status','Partial');
                })
            ->where('deleted',0)
            ->first();
        $dataeExploded = explode(',', $pendingInvoicesClients->billTo);    
        

        $allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)
        ->whereIn('id',$dataeExploded)
        ->orderBy('id','desc')->pluck('company_name', 'id');            

        $paymentVia = Config::get('app.paymentMethod');
        

        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');

        return view("cashier-role.invoicepayments.form",['cashCredit'=>$cashCredit,'model'=>$model,'dataFileNumber'=>$dataFileNumber,'allUsers'=>$allUsers,'cashCredit'=>$cashCredit,'cargoId'=>$cargoId,'invoceId'=>$invoceId,'invoiceArray'=>$invoiceArray,'paymentVia'=>$paymentVia,'billingParty'=>$billingParty,'fromMenu'=>$fromMenu,'currency'=>$currency]);        
    }

    

    public function getinvoicesusingfilenumber()
    {
        $cargoId = $_POST['cargoId'];
        $invoiceNumbers = DB::table('invoices')->where('cargo_id',$cargoId)->where('payment_status','Pending')->where('deleted','0')->get();
        $dt = '';
        foreach ($invoiceNumbers as $key => $value) {
           $dt .=  '<option value="'.$value->id.'">'.$value->bill_no.'</option>';
        }
        return $dt;
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
        if(!empty($input['client']) || !empty($input['invoice_id']))
        {
            $input['payment'] = array_filter($input['payment']);
            $exchageValues = array_filter($input['exchange_amount']);
            foreach ($input['payment'] as $key => $value) {
                $value = str_replace(',','',$value);
                $dataInvoice = DB::table('invoices')->where('id',$key)->first();
                $dataCargo = DB::table('cargo')->where('id',$dataInvoice->cargo_id)->first();

                $input['invoice_id'] = $key;
                $input['invoice_number'] = $dataInvoice->bill_no;
                $input['cargo_id'] = $dataInvoice->cargo_id;
                $input['file_number'] = $dataCargo->file_number;
                $input['amount'] = $value;
                $input['exchange_amount'] = str_replace(',','',$exchageValues[$key]);
                $input['exchange_currency'] = $input['exchange_currency'];
                $input['created_at'] = gmdate("Y-m-d H:i:s");
                $input['payment_accepted_by'] = auth()->user()->id;
                
                if(empty($input['client']))
                $input['client'] = $dataInvoice->bill_to;
                $model = InvoicePayments::create($input);

                if($value == $dataInvoice->balance_of)
                    DB::table('invoices')->where('id',$key)->update(['invoice_status_changed_by'=>auth()->user()->id,'display_notification_warehouse_invoice'=>1,'display_notification_admin_invoice_status_changed'=>1,'notification_date_time'=>date('Y-m-d H:i:s'),'credits' => $dataInvoice->total,'payment_status'=>'Paid','balance_of'=>'0.00']);
                else
                    DB::table('invoices')->where('id',$key)->update(['invoice_status_changed_by'=>auth()->user()->id,'display_notification_warehouse_invoice'=>1,'display_notification_admin_invoice_status_changed'=>1,'notification_date_time'=>date('Y-m-d H:i:s'),'credits' => $dataInvoice->credits + $value,'payment_status'=>'Partial','balance_of'=>$dataInvoice->total - ($dataInvoice->credits + $value)]);

                // Store deposite activities
                $modelActivities = new Activities;
                $modelActivities->type = 'cashCreditClient';
                $modelActivities->related_id = $dataInvoice->bill_to;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = number_format($value,2).'-Invoice Payment Received.';
                $modelActivities->cash_credit_flag = '2';
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                $dataclient = DB::table('clients')->where('id',$dataInvoice->bill_to)->first();
                $lastBalance = $dataclient->available_balance + $value;
                DB::table('clients')->where('id',$dataInvoice->bill_to)->update(['available_balance' => $lastBalance]);
            }
        }

        if(!empty($input['amt-credit-to-client']) && $input['amt-credit-to-client'] > 0)
        {
            if(empty($input['client']))
                $input['client'] = $dataInvoice->bill_to;

            $dataclient = DB::table('clients')->where('id',$input['client'])->first();
            $lastBalance = $dataclient->available_balance + str_replace(',','',$input['amt-credit-to-client']);
            DB::table('clients')->where('id',$input['client'])->update(['available_balance' => $lastBalance]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $input['client'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $input['amt-credit-to-client']. '- Amount deposited.';
            $modelActivities->cash_credit_flag = '2';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        
        Session::flash('flash_message', 'Payment has been received successfully');
        //return redirect('invoices');
    }

    

   

    public function getselectedinvoicedata()
    {
        $invoiceId = $_POST['invoiceId'];
        $dataInvoice = DB::table('invoices')->where('id',$invoiceId)->get();
        return view("invoicepayments.getclientinvoicesajax",['dataInvoice'=> $dataInvoice, 'flagModule' => 'Cargo']);
    }

    public function getcourierorcargodata()
    {
        $flag = $_POST['flag'];
        if($flag == 'Cargo')
        {
             $invoiceArray = DB::table('invoices')->whereNotNull('cargo_id')
                ->where(function ($query) {
                    $query->where('payment_status','Pending')
                          ->orWhere('payment_status','Partial');
                })
            ->where('deleted',0)
            ->get()
            ->toArray();
        }
        else
        {
            $invoiceArray = DB::table('invoices')->whereNotNull('ups_id')
            ->where(function ($query) {
                    $query->where('payment_status','Pending')
                          ->orWhere('payment_status','Partial');
                })
            ->where('deleted',0)
            ->get()
            ->toArray();
        }

        
        $dt = '<option value="">-- Select</option>';
        foreach ($invoiceArray as $key => $value) {
           $dt .=  '<option value="'.$value->id.'">'.$value->bill_no.'</option>';
        }
        return $dt;
    }

    

    /**
     * Display the specified resource.
     *
     * @param  \App\InvoicePayments  $invoicePayments
     * @return \Illuminate\Http\Response
     */
    public function show(InvoicePayments $invoicePayments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InvoicePayments  $invoicePayments
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoicePayments $invoicePayments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InvoicePayments  $invoicePayments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoicePayments $invoicePayments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InvoicePayments  $invoicePayments
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoicePayments $invoicePayments)
    {
        //
    }
}
