<?php

namespace App\Http\Controllers;

use App\Invoices;
use App\Clients;
use App\BillingItems;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use Illuminate\Support\Facades\DB;
use App\User;
use App\InvoiceItemDetails;
use App\Activities;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceDetailMail;
use App\Mail\sendCashierInvoiceMail;
use PDF;
class WarehouseInvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_invoices'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id',auth()->user()->id)
            ->first();
        $wh = explode(',',$getWarehouseOfUser->warehouses);
        $dataCargo = DB::table('cargo')->select(DB::raw('group_concat(id) as consolidate'))->where('deleted',0)->whereIn('warehouse',$wh)->first();
        $dataS = explode(',', $dataCargo->consolidate);
        $invoices = DB::table('invoices')->where('deleted','0')->whereIn('cargo_id',$dataS)->whereNotNull('cargo_id')->orderBy('id', 'desc')->get();
        return view("warehouse-role.invoices.index",['invoices'=>$invoices]);
    }

    public function indexpendinginvoices()
    {
        $pendingInvoices = DB::table('invoices')->where('deleted','0')->where('payment_status','Pending')->whereNotNull('cargo_id')->orderBy('id', 'desc')->get();
        return view("warehouse-role.invoices.pendinginvoiceindex",['pendingInvoices'=>$pendingInvoices]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($cargoId = null)
    {
        $checkPermission = User::checkPermission(['add_cargo_invoices'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new Invoices;
        //$dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');

        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id',auth()->user()->id)
            ->first();

        $wh = explode(',',$getWarehouseOfUser->warehouses);

        $dataCargo = DB::table('cargo')
            ->whereIn('warehouse',$wh)
            ->where('cargo_operation_type','<>','3')
            ->where('warehouse_status','3')
            ->where('deleted',0)
            ->get();

       
        $dataFileNumber = array();
        $NdataFileNumber = array();
        foreach($dataCargo as $k => $v)
        {
            $dataFileNumber[$v->id] = $v->file_number.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name : '.(!empty($v->file_name) ? $v->file_name : '-').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Consignee : '.(!empty($v->consignee_name) ? $v->consignee_name : '-').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shipper : '.(!empty($v->shipper_name) ? $v->shipper_name : '-').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bill To : -';

            $NdataFileNumber[$k]['value'] = $v->id;
            $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
            $NdataFileNumber[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
            $NdataFileNumber[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
            $NdataFileNumber[$k]['bill_to'] = '-';
        }

        
        $NdataFileNumber = json_encode($NdataFileNumber, JSON_NUMERIC_CHECK);
        

        /*$dataFileNumber = DB::table('cargo')
        ->select('id',DB::raw("CONCAT(file_number, ' (Name-',file_name,') (Consignee-',consignee_name,') (Shipper-',shipper_name,')') as fulldata"))
        ->where('deleted',0)->where('status',1)->get()
        ->pluck('fulldata','id');*/


        $dataHawbAll = array();
        $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id');
        $dataBillingItems = DB::table('billing_items')->where('deleted',0)->where('status',1)->orderBy('id','desc')->get()->pluck('billing_name','id');
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted','0')->whereNull('flag_invoice')->first();
        if(empty($getLastInvoice))
        {
            $model->bill_no = 'CA-5001';
        }
        else
        {
            $ab = 'CA-';
            $ab .= substr($getLastInvoice->bill_no,3) + 1;
            $model->bill_no = $ab;
        }

        if(!empty($cargoId)){
            $model->file_number = $cargoId;
            $model->cargo_id = $cargoId;

            $dataCargoSingle = DB::table('cargo')->where('id',$cargoId)->first();
            $dAwb = explode(',', $dataCargoSingle->hawb_hbl_no);
            $dataHawb = DB::table('hawb_files')->where('deleted',0)->whereIn('id',$dAwb)->get();
            
            if(!empty($dataHawb))
            {
                foreach($dataHawb as $k => $v)
                {
                    $dataHawbAll[$k]['value'] = $v->id;
                    $dataHawbAll[$k]['hawb_hbl_no'] = ($v->cargo_operation_type) == 1 ? $v->hawb_hbl_no : $v->export_hawb_hbl_no;
                    $dataHawbAll[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
                    $dataHawbAll[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
                }
            }
            $dataHawbAll = json_encode($dataHawbAll, JSON_NUMERIC_CHECK);
        }else{

        }

        $model->sent_on = date('Y-m-d');

        $allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->orderBy('id','desc')->pluck('company_name', 'id');
        //$allUsers = json_decode($allUsers,1);
        //ksort($allUsers);
        
        $dataBillingItemsAutoComplete = BillingItems::getBillingItemsAutocomplete();
        $model->sub_total = '0.00';
        $model->tca = '0.00';
        $model->total = '0.00';
        $model->credits = '0.00';
        $model->balance_of = '0.00';
        $model->date = date('d-m-Y');

        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');
        $currency = json_decode($currency,1);
        ksort($currency);

        $billingParty = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->orderBy('id','desc')->pluck('company_name', 'id');
        
        return view('warehouse-role.invoices.form',['model'=>$model,'dataAwbNos'=>$dataAwbNos,'dataFileNumber'=>$dataFileNumber,'dataBillingItems'=>$dataBillingItems,'allUsers'=>$allUsers,'dataBillingItemsAutoComplete'=>$dataBillingItemsAutoComplete,'currency'=>$currency,'NdataFileNumber'=>$NdataFileNumber,'billingParty'=>$billingParty,'dataHawbAll'=>$dataHawbAll,'cargoId'=>$cargoId]);
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
            'file_number' => 'required',
        ]);
        $input = $request->all();
        $input['sub_total'] = str_replace(',', '',$input['sub_total']);
        $input['tca'] = str_replace(',', '',$input['tca']);
        $input['total'] = str_replace(',', '',$input['total']);
        $input['credits'] = str_replace(',', '',$input['credits']);
        $input['balance_of'] = str_replace(',', '',$input['balance_of']);

        //$dataInvoice = DB::table('invoices')->where('bill_no',$input['bill_no'])->first();
        $dataInvoice = array();
        if(!empty($dataInvoice))
        {
            $model = InvoiceItemDetails::where('invoice_id',$dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $model = Invoices::find($dataInvoice->id);
            $model->fill($request->input());
            Activities::log('update','cargoinvoice',$model);
            $input['date'] = date('Y-m-d',strtotime($input['date']));
            $model->update($input);
            
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);
            
            for($i = 0; $i < $countInvoiceItems; $i++)
            {
                $modelInvoiceDetails = new InvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id',$input['fees_name'][$i])->first();
                if(!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';
            

            
            $pdf = PDF::loadView('invoices.printcargoinvoice',['invoice'=>$input]);
            $pdf_file = 'printCargoInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/cargoInvoices/'.$pdf_file;
            $pdf->save($pdf_path);
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            Mail::to($input['email'])->send(new InvoiceDetailMail($input));
            
            if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
            {
            $modelClient = Clients::where('id',$model->bill_to)->first();
            $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
            $modelClient->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            if($input['balance_of'] - $dataInvoice->balance_of < 0)
            {
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of). '- Amount Deposited.';
                $modelActivities->cash_credit_flag = '2';
            }else
            {
                $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of.'-Invoice payment.';
                $modelActivities->cash_credit_flag = '1';
            }
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
            }   

            if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }
            
            Session::flash('flash_message', 'Record has been created successfully');
            return redirect('warehouseinvoices/warehouseinvoices');
        }
        else
        {
            $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted','0')->whereNull('flag_invoice')->first();
            if(empty($getLastInvoice))
            {
                $input['bill_no'] = 'CA-5001';
            }
            else
            {
                $ab = 'CA-';
                $ab .= substr($getLastInvoice->bill_no,3) + 1;
                $input['bill_no'] = $ab;
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d',strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $input['display_notification_admin_invoice'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
            $model = Invoices::create($input);
            Activities::log('create','cargoinvoice',$model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);
            
            for($i = 0; $i < $countInvoiceItems; $i++)
            {
                $modelInvoiceDetails = new InvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id',$input['fees_name'][$i])->first();
                if(!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';
            

            
            $pdf = PDF::loadView('invoices.printcargoinvoice',['invoice'=>$input]);
            $pdf_file = 'printCargoInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/cargoInvoices/'.$pdf_file;
            $pdf->save($pdf_path);
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            Mail::to($input['email'])->send(new InvoiceDetailMail($input));
            
            $modelClient = Clients::where('id',$model->bill_to)->first();
            $modelClient->available_balance = $modelClient->available_balance - $model->balance_of;
            $modelClient->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = number_format($model->balance_of,2).'-Invoice Generated.';
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'client';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #'.$model->bill_no.' has been created';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }
            
            Session::flash('flash_message', 'Record has been created successfully');
            return redirect('warehouseinvoices/warehouseinvoices');
        }
        
    }


    public function storecargoinvoiceandprint(Request $request)
    {
        $validater = $this->validate($request, [
            'file_number' => 'required',
        ]);
        $input = $request->all();
        $input['sub_total'] = str_replace(',', '',$input['sub_total']);
        $input['tca'] = str_replace(',', '',$input['tca']);
        $input['total'] = str_replace(',', '',$input['total']);
        $input['credits'] = str_replace(',', '',$input['credits']);
        $input['balance_of'] = str_replace(',', '',$input['balance_of']);

        $dataInvoice = DB::table('invoices')->where('bill_no',$input['bill_no'])->first();
        if($input['saveandprintinupdate'] == '0')
            $dataInvoice = array();
        if(!empty($dataInvoice))
        {
            $model = InvoiceItemDetails::where('invoice_id',$dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $model = Invoices::find($dataInvoice->id);
            $model->fill($request->input());
            Activities::log('update','cargoinvoice',$model);
            $input['date'] = date('Y-m-d',strtotime($input['date']));
            $model->update($input);
            
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);
            
            for($i = 0; $i < $countInvoiceItems; $i++)
            {
                $modelInvoiceDetails = new InvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id',$input['fees_name'][$i])->first();
                if(!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';
            

            
            $pdf = PDF::loadView('invoices.printcargoinvoice',['invoice'=>$input]);
            $pdf_file = 'printCargoInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/cargoInvoices/'.$pdf_file;
            $pdf->save($pdf_path);
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            Mail::to($input['email'])->send(new InvoiceDetailMail($input));
            
            if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
            {
            $modelClient = Clients::where('id',$model->bill_to)->first();
            $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
            $modelClient->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            if($input['balance_of'] - $dataInvoice->balance_of < 0)
            {
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of). '- Amount Deposited.';
                $modelActivities->cash_credit_flag = '2';
            }else
            {
                $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of.'-Invoice payment.';
                $modelActivities->cash_credit_flag = '1';
            }
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
            }

            if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }
            
            return url('/').'/'.$pdf_path;
        }else
        {
            $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted','0')->whereNull('flag_invoice')->first();
            if(empty($getLastInvoice))
            {
                $input['bill_no'] = 'CA-5001';
            }
            else
            {
                $ab = 'CA-';
                $ab .= substr($getLastInvoice->bill_no,3) + 1;
                $input['bill_no'] = $ab;
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d',strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = Invoices::create($input);
            Activities::log('create','cargoinvoice',$model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);
            
            for($i = 0; $i < $countInvoiceItems; $i++)
            {
                $modelInvoiceDetails = new InvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id',$input['fees_name'][$i])->first();
                if(!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';
            

            
            $pdf = PDF::loadView('invoices.printcargoinvoice',['invoice'=>$input]);
            $pdf_file = 'printCargoInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/cargoInvoices/'.$pdf_file;
            $pdf->save($pdf_path);
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            Mail::to($input['email'])->send(new InvoiceDetailMail($input));
            
            $modelClient = Clients::where('id',$model->bill_to)->first();
            $modelClient->available_balance = $modelClient->available_balance - $model->balance_of;
            $modelClient->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = number_format($model->balance_of,2).'-Invoice Generated.';
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'client';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #'.$model->bill_no.' has been created';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }
            
            return url('/').'/'.$pdf_path;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show(Invoices $invoices)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoices $invoices,$id,$flag = null)
    {
        $checkPermission = User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');
        
        if($flag == 'fromNotification')
            Invoices::where('id',$id)->update(['display_notification_warehouse_invoice'=>0]);

        $checkPermission = User::checkPermission(['update_invoice'],'',auth()->user()->id);


        $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id',auth()->user()->id)
            ->first();

        $wh = explode(',',$getWarehouseOfUser->warehouses);

        //$dataFileNumber = DB::table('cargo')->where('deleted',0)->get()->pluck('file_number','id');
        $dataCargo = DB::table('cargo')
            ->whereIn('warehouse',$wh)
            ->where('cargo_operation_type','<>','3')
            ->where('warehouse_status','3')
            ->where('deleted',0)
            ->get();
        $dataFileNumber = array();
        $NdataFileNumber = array();
        foreach($dataCargo as $k => $v)
        {
            $dataFileNumber[$v->id] = $v->file_number.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Name : '.(!empty($v->file_name) ? $v->file_name : '-').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Consignee : '.(!empty($v->consignee_name) ? $v->consignee_name : '-').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shipper : '.(!empty($v->shipper_name) ? $v->shipper_name : '-').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bill To : -';

            $NdataFileNumber[$k]['value'] = $v->id;
            $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
            $NdataFileNumber[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
            $NdataFileNumber[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
            $NdataFileNumber[$k]['bill_to'] = '-';
        }
        $NdataFileNumber = json_encode($NdataFileNumber, JSON_NUMERIC_CHECK);
        $dataAwbNos = DB::table('cargo')->where('deleted',0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no','id');

        $dataInvoiceDetails  = DB::table('invoice_item_details')->where('invoice_id',$id)->get();
        $dataInvoiceDetails = json_decode(json_encode($dataInvoiceDetails));

        $dataBillingItems = DB::table('billing_items')->where('deleted',0)->where('status',1)->orderBy('id','desc')->get()->pluck('billing_name','id');
        
        $model = DB::table('invoices')->where('id',$id)->first();

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'name');
        $allUsers = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->orderBy('id','desc')->pluck('company_name', 'id');
        //$allUsers = json_decode($allUsers,1);
        //ksort($allUsers);

        $dataBillingItemsAutoComplete = BillingItems::getBillingItemsAutocomplete();
        $model->date = date('d-m-Y',strtotime($model->date));

        $currency = DB::table('currency')->select(['id','code'])->where('deleted',0)->where('status',1)->pluck('code', 'id');
        $currency = json_decode($currency,1);
        ksort($currency);

        $billingParty = DB::table('clients')->select(['id','company_name'])->where('deleted',0)->where('status',1)->orderBy('id','desc')->pluck('company_name', 'id');

        $dataCargoSingle = DB::table('cargo')->where('id',$model->cargo_id)->first();
        $dAwb = explode(',', $dataCargoSingle->hawb_hbl_no);
        $dataHawb = DB::table('hawb_files')->where('deleted',0)->whereIn('id',$dAwb)->get();
        $dataHawbAll = array();
        if(!empty($dataHawb))
        {
            foreach($dataHawb as $k => $v)
            {
                $dataHawbAll[$k]['value'] = $v->id;
                $dataHawbAll[$k]['hawb_hbl_no'] = $v->cargo_operation_type == 1 ? $v->hawb_hbl_no : $v->export_hawb_hbl_no;
                $dataHawbAll[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
                $dataHawbAll[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
            }
        }
        $dataHawbAll = json_encode($dataHawbAll, JSON_NUMERIC_CHECK);
        

        return view("warehouse-role.invoices.form",['model'=>$model,'dataAwbNos'=>$dataAwbNos,'dataFileNumber'=>$dataFileNumber,'dataBillingItems'=>$dataBillingItems,'id'=>$id,'dataInvoiceDetails'=>$dataInvoiceDetails,'allUsers'=>$allUsers,'dataBillingItemsAutoComplete'=>$dataBillingItemsAutoComplete,'currency'=>$currency,'NdataFileNumber'=>$NdataFileNumber,'billingParty'=>$billingParty,'dataHawbAll'=>$dataHawbAll,'cargoId'=>$model->cargo_id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoices $invoices,$id)
    {
        $model = InvoiceItemDetails::where('invoice_id',$id)->delete();
        $model = Invoices::find($id);
        $dataInvoice = Invoices::find($id);
        $model->fill($request->input());
        Activities::log('update','cargoinvoice',$model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $input['date'] = date('Y-m-d',strtotime($input['date']));
        $input['sub_total'] = str_replace(',', '',$input['sub_total']);
        $input['tca'] = str_replace(',', '',$input['tca']);
        $input['total'] = str_replace(',', '',$input['total']);
        $input['credits'] = str_replace(',', '',$input['credits']);
        $input['balance_of'] = str_replace(',', '',$input['balance_of']);

        $model->update($input);

        $countInvoiceItems = $_POST['count_invoice_items'];

        $input['fees_name'] = array_values($input['fees_name']);
        $input['fees_name_desc'] = array_values($input['fees_name_desc']);
        $input['quantity'] = array_values($input['quantity']);
        $input['unit_price'] = array_values($input['unit_price']);
        $input['total_of_items'] = array_values($input['total_of_items']);
        
        
        for($i = 0; $i < $countInvoiceItems; $i++)
        {
            $modelInvoiceDetails = new InvoiceItemDetails();
            $modelInvoiceDetails->invoice_id = $model->id;
            $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
            $dataBilling = DB::table('billing_items')->where('id',$input['fees_name'][$i])->first();
                if(!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
            $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
            $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
            $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
            $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
            $modelInvoiceDetails->save();
        }
        $input['id'] = $model->id;
        $pdf = PDF::loadView('invoices.printcargoinvoice',['invoice'=>$input]);
        $pdf_file = 'printCargoInvoice_'.$model->id.'.pdf';
        $pdf_path = 'public/cargoInvoices/'.$pdf_file;
        $pdf->save($pdf_path);

        if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
        {
        $modelClient = Clients::where('id',$model->bill_to)->first();
        $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
        $modelClient->save();

        // Store deposite activities
        $modelActivities = new Activities;
        $modelActivities->type = 'cashCreditClient';
        $modelActivities->related_id = $model->bill_to;
        $modelActivities->user_id   = auth()->user()->id;
        if($input['balance_of'] - $dataInvoice->balance_of < 0)
        {
            $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of). '- Amount Deposited.';
            $modelActivities->cash_credit_flag = '2';
        }else
        {
            $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of.'-Invoice payment.';
            $modelActivities->cash_credit_flag = '1';
        }
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('warehouseinvoices/warehouseinvoices');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoices $invoices,$id)
    {
        $model = Invoices::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }

    public function getcargodetailforinvoice()
    {
        $dataCargo = DB::table('cargo')->where('id',$_POST['cargoId'])->where('deleted',0)->first();
        return json_encode($dataCargo);
    }

    public function changeinvoicestatus()
    {
        $status = $_POST['status'];
        $changeStatus = ($status=='Paid') ? 'Pending' : 'Paid';
        $invoiceId = $_POST['invoiceId'];
        $model = Invoices::find($invoiceId);
        $data['payment_status'] = $changeStatus;
        $model->fill($data);
        Activities::log('update','invoicepaymentstatus',$model);
        $userData = DB::table('invoices')->where('id',$invoiceId)->update(['payment_status' => $changeStatus]);
        return 'true';
    }

    public function gethawboffiles()
    {
        $cargoId = $_POST['id'];
        $dataCargoSingle = DB::table('cargo')->where('id',$cargoId)->first();
        $dAwb = explode(',', $dataCargoSingle->hawb_hbl_no);
        $dataHawb = DB::table('hawb_files')->where('deleted',0)->whereIn('id',$dAwb)->get();
        
        if(!empty($dataHawb))
        {
            foreach($dataHawb as $k => $v)
            {
                $dataHawbAll[$k]['value'] = $v->id;
                $dataHawbAll[$k]['hawb_hbl_no'] = ($v->cargo_operation_type) == 1 ? $v->hawb_hbl_no : $v->export_hawb_hbl_no;
                $dataHawbAll[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
                $dataHawbAll[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
            }
        }
        $dataHawbAll = json_encode($dataHawbAll, JSON_NUMERIC_CHECK);           
        return $dataHawbAll;
    }

     public function sendMail(Request $request){
        $emaildata = [];
        $itemid = $request->get('itemId');
        $itemData = DB::table('invoices')->where('id',$itemid)->first();
        $billingPartyData = DB::table('clients')->where('id',$itemData->bill_to)->first();
        $pdf_file = 'printCargoInvoice_'.$itemData->id.'.pdf';
        $pdf_path = 'public/cargoInvoices/'.$pdf_file;
        $emaildata['email'] = $billingPartyData->email;
        $emaildata['invoiceAttachment'] = $pdf_path;
        $send = Mail::to($emaildata['email'])->send(new sendCashierInvoiceMail($emaildata));
        if(!Mail::failures()){
            $send = "Mail has been send successfully.";
        }
        echo $send;
       }  
}
