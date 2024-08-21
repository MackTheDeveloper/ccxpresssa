<?php

namespace App\Http\Controllers;

use App\AeropostInvoices;
use App\Clients;
use App\BillingItems;
use App\Activities;
use App\AeropostInvoiceItemDetails;
use Illuminate\Http\Request;
use App\User;
use App\Currency;
use App\Invoices;
use DB;
use PDF;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceDetailMail;
use Session;
use Illuminate\Support\Facades\Storage;
use App\Admin;
use App\Aeropost;
use Response;
use App\InvoicePayments;
class AeropostInvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_aeropost_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view("aeropost-invoices.index");
    }

    public function getaeropostinvoiceforinputpicker()
    {
        //pre($_REQUEST,1);
        $limit = $_REQUEST['limit'];
        $start = ($_REQUEST['p'] - 1 )* 10;
        $aeropostId = $_REQUEST['aeropostId'];
        $valueOfText = $_REQUEST['q'];
        
        $total = Aeropost::selectRaw('count(*) as total')
        ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
        ->where('aeropost.deleted', '0')
        ->whereNull('file_close');
        if (!empty($aeropostId) && empty($valueOfText))
            $total = $total->where('aeropost.id', $aeropostId);
        if (!empty($valueOfText))
        {
            $total->where(function ($total) use ($valueOfText) {
                $total->where('file_number', 'like', '%' . $valueOfText . '%')
                    ->orWhere('tracking_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                    ->orWhere('from_address', 'like', '%' . $valueOfText . '%');
            });
        }
        $total = $total->first();

        $datas = DB::table('aeropost')
            ->select('aeropost.id', 'aeropost.file_number', 'aeropost.consignee', 'aeropost.from_location', 'aeropost.tracking_no')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
            ->where('aeropost.deleted', '0')
            ->whereNull('file_close');
        if (!empty($aeropostId) && empty($valueOfText))
            $datas = $datas->where('aeropost.id', $aeropostId);
        if (!empty($valueOfText))
        {
            $datas->where(function ($datas) use ($valueOfText) {
                $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                    ->orWhere('tracking_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                    ->orWhere('from_address', 'like', '%' . $valueOfText . '%');
            });
        }
        //$dataUps = $dataUps->get();
        $datas = $datas->offset($start)->limit($limit)->get();
        $NdataFileNumber = array();
        foreach ($datas as $k => $v) {
            $modelClients = new Clients();
            $data = $modelClients->getClientData($v->consignee);

            $NdataFileNumber[$k]['value'] = $v->id;
            $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
            $NdataFileNumber[$k]['consignee'] = !empty($data->company_name) ? $data->company_name : '-';
            $NdataFileNumber[$k]['from_address'] = !empty($v->from_address) ? $v->from_address : '-';
            $NdataFileNumber[$k]['awb_number'] = !empty($v->tracking_no) ? $v->tracking_no : '-';
        }

        $json_data = array(
            "data"            => $NdataFileNumber,
            'count' => $total->total
        );
        return Response::json($json_data);
        //return json_encode($NdataFileNumber, JSON_NUMERIC_CHECK);
        //return Response::json(['NdataFileNumber' => $NdataFileNumber]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($aeropostId = null, $flagInvoice = null, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_aeropost_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new AeropostInvoices;

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->where('item_code', '<>', 'SCC')->orderBy('id', 'desc')->get()->pluck('billing_name', 'id');

        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
        if (empty($getLastInvoice)) {
            $model->bill_no = 'AP-5001';
        } else {
            $ab = 'AP-';
            $ab .= substr($getLastInvoice->bill_no, 3) + 1;
            $model->bill_no = $ab;
        }

        if (!empty($aeropostId)) {
            $model->file_number = $aeropostId;
            $model->aeropost_id = $aeropostId;
        }

        $model->sent_on = date('Y-m-d');

        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        //$allUsers = json_decode($allUsers,1);
        //ksort($allUsers);

        $dataBillingItemsAutoComplete = BillingItems::getBillingItemsAutocomplete();
        $model->sub_total = '0.00';
        $model->tca = '0.00';
        $model->total = '0.00';
        $model->credits = '0.00';
        $model->balance_of = '0.00';
        $model->date = date('d-m-Y');
        if ($flagInvoice == '0')
            $flagInvoice = null;
        $model->flag_invoice = $flagInvoice;
        if ($flagInvoice == 'old')
            $model->bill_no = '';

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        return view('aeropost-invoices.form', ['model' => $model, 'dataBillingItems' => $dataBillingItems, 'allUsers' => $allUsers, 'dataBillingItemsAutoComplete' => $dataBillingItemsAutoComplete, 'currency' => $currency, 'billingParty' => $billingParty, 'flagFromWhere' => $flagFromWhere,'aeropostId' => $aeropostId]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        session_start();
        $validater = $this->validate($request, [
            'file_number' => 'required',
        ]);
        $input = $request->all();
        $fileData = DB::table('aeropost')->where('file_number', $input['file_no'])->where('deleted', '0')->first();
        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);

        //$dataInvoice = DB::table('invoices')->where('bill_no',$input['bill_no'])->first();
        $dataInvoice = array();
        if (!empty($dataInvoice)) {
            $model = AeropostInvoiceItemDetails::where('invoice_id', $dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = auth()->user()->id;
            $model = AeropostInvoices::find($dataInvoice->id);
            /*$model->fill($request->input());
            Activities::log('update','upsinvoice',$model);*/
            $model->date = date('d-m-Y', strtotime($model->date));
            $model->update($input);

            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new AeropostInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
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

            $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $input]);
            $pdf_file = 'printAeropostInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';


            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            /*if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
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
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of).'-Credit Deposited.';
                $modelActivities->cash_credit_flag = '2';
            }else
            {
                $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of.'-Invoice payment.';
                $modelActivities->cash_credit_flag = '1';
            }
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
            }*/

            /*if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }*/

            if (isset($_SESSION['sessionAccessToken'])) {
                // pre($model);
                $fData['id'] = $model->id;
                $fData['module'] = '9';
                $fData['flagModule'] = 'updateInvoice';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
                // pre($fData);
                // Store expense to QB
                $newModel = base64_encode(serialize($fData));
                //$newTest = unserialize(base64_decode($newModel, true));
                //pre($newTest);
                $urlAction = url('call/qb?model=' . $newModel);
                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }

            Session::flash('flash_message', 'Record has been created successfully');
            return redirect('aeropostinvoices');
        } else {
            if ($input['flag_invoice'] != 'old') {
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $input['bill_no'] = 'AP-5001';
                } else {
                    $ab = 'AP-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $input['bill_no'] = $ab;
                }
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = AeropostInvoices::create($input);
            Activities::log('create', 'aeropostinvoice', $model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new AeropostInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
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

            $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $input]);
            $pdf_file = 'printAeropostInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit_stop') {
                /* $modelClient->available_balance = $modelClient->available_balance - $model->balance_of;
                $modelClient->save(); */

                $availableBalance = $modelClient->available_balance;
                if ($availableBalance > '0.00' || $availableBalance > '0') {
                    $modelClient->available_balance = $availableBalance - $model->balance_of;
                    if ($modelClient->available_balance < 0) {
                        $modelClient->available_balance = 0.00;
                        $paymentAmount = $availableBalance;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Partial', 'balance_of' => $model->total - $paymentAmount]);
                    } else {
                        $paymentAmount = $model->balance_of;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    }
                    $modelClient->save();

                    $inputInvoicePayment['invoice_id'] = $model->id;
                    $inputInvoicePayment['invoice_number'] = $model->bill_no;
                    $inputInvoicePayment['aeropost_id'] = $model->aeropost_id;
                    $inputInvoicePayment['file_number'] = $fileData->file_number;
                    $inputInvoicePayment['amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_rate'] = '0.00';
                    $inputInvoicePayment['payment_via'] = 'DEPOSIT';
                    $inputInvoicePayment['payment_via_note'] = 'C/N';
                    $inputInvoicePayment['created_at'] = gmdate("Y-m-d H:i:s");
                    $inputInvoicePayment['client'] = $model->bill_to;
                    $inputInvoicePayment['payment_accepted_by'] = auth()->user()->id;

                    $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
                    if (empty($getLastReceiptNumber)) {
                        $receiptNumber = '11101';
                    } else {
                        if (empty($getLastReceiptNumber->receipt_number))
                            $receiptNumber = '11101';
                        else
                            $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
                    }

                    $inputInvoicePayment['receipt_number'] = $receiptNumber;
                    $modelInvoicePayment = InvoicePayments::create($inputInvoicePayment);

                    // Store deposite activities
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cashCreditClient';
                    $modelActivities->related_id = $model->bill_to;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = number_format($paymentAmount, 2) . '-Invoice Payment Paid. (Credit Used)';
                    $modelActivities->cash_credit_flag = '1';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }


            // Store deposite activities
            /* $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = number_format($model->balance_of, 2) . '-Invoice Generated.';
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save(); */

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'client';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            // Store invoice activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'aeropost';
            $modelActivities->related_id = $model->aeropost_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if (isset($_SESSION['sessionAccessToken'])) {
                $fData['id'] = $model->id;
                $fData['module'] = '9';
                $fData['flagModule'] = 'invoice';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }

            Session::flash('flash_message', 'Record has been created successfully');
            if ($model->flag_invoice == 'old')
                return redirect('oldinvoices');
            else {
                if ($input['flagFromWhere'] == 'flagFromView') {
                    return redirect()->route('viewdetailsaeropost', [$model->aeropost_id]);
                } else
                    return redirect('aeropostinvoices');
            }
        }
    }

    public function storeaeropostinvoiceandprint(Request $request)
    {
        session_start();
        $validater = $this->validate($request, [
            'file_number' => 'required',
        ]);
        $input = $request->all();
        $fileData = DB::table('aeropost')->where('file_number', $input['file_no'])->where('deleted', '0')->first();
        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);

        $dataInvoice = DB::table('invoices')->where('bill_no', $input['bill_no'])->first();
        if ($input['saveandprintinupdate'] == '0')
            $dataInvoice = array();
        if (!empty($dataInvoice)) {
            $model = AeropostInvoiceItemDetails::where('invoice_id', $dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $model = AeropostInvoices::find($dataInvoice->id);
            $model->fill($request->input());
            Activities::log('update', 'aeropostinvoice', $model);
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model->update($input);

            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new AeropostInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
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


            $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $input]);
            $pdf_file = 'printAeropostInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            /*if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
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
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of).'-Credit Deposited.';
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
            }*/
            if (isset($_SESSION['sessionAccessToken'])) {
                $fData['id'] = $model->id;
                $fData['module'] = '9';
                $fData['flagModule'] = 'updateInvoice';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
            return url('/') . '/' . $pdf_path;
        } else {
            if ($input['flag_invoice'] != 'old') {
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $input['bill_no'] = 'AP-5001';
                } else {
                    $ab = 'AP-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $input['bill_no'] = $ab;
                }
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = AeropostInvoices::create($input);
            Activities::log('create', 'aeropostinvoice', $model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new AeropostInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
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


            $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $input]);
            $pdf_file = 'printAeropostInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit_stop') {
                /* $modelClient->available_balance = $modelClient->available_balance - $model->balance_of;
                $modelClient->save(); */

                $availableBalance = $modelClient->available_balance;
                if ($availableBalance > '0.00' || $availableBalance > '0') {
                    $modelClient->available_balance = $availableBalance - $model->balance_of;
                    if ($modelClient->available_balance < 0) {
                        $modelClient->available_balance = 0.00;
                        $paymentAmount = $availableBalance;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Partial', 'balance_of' => $model->total - $paymentAmount]);
                    } else {
                        $paymentAmount = $model->balance_of;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    }
                    $modelClient->save();

                    $inputInvoicePayment['invoice_id'] = $model->id;
                    $inputInvoicePayment['invoice_number'] = $model->bill_no;
                    $inputInvoicePayment['aeropost_id'] = $model->aeropost_id;
                    $inputInvoicePayment['file_number'] = $fileData->file_number;
                    $inputInvoicePayment['amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_rate'] = '0.00';
                    $inputInvoicePayment['payment_via'] = 'DEPOSIT';
                    $inputInvoicePayment['payment_via_note'] = 'C/N';
                    $inputInvoicePayment['created_at'] = gmdate("Y-m-d H:i:s");
                    $inputInvoicePayment['client'] = $model->bill_to;
                    $inputInvoicePayment['payment_accepted_by'] = auth()->user()->id;

                    $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
                    if (empty($getLastReceiptNumber)) {
                        $receiptNumber = '11101';
                    } else {
                        if (empty($getLastReceiptNumber->receipt_number))
                            $receiptNumber = '11101';
                        else
                            $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
                    }

                    $inputInvoicePayment['receipt_number'] = $receiptNumber;
                    $modelInvoicePayment = InvoicePayments::create($inputInvoicePayment);

                    // Store deposite activities
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cashCreditClient';
                    $modelActivities->related_id = $model->bill_to;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = number_format($paymentAmount, 2) . '-Invoice Payment Paid. (Credit Used)';
                    $modelActivities->cash_credit_flag = '1';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }


            // Store deposite activities
            /* $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = number_format($model->balance_of, 2) . '-Invoice Generated.';
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save(); */

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'client';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            // Store invoice activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'aeropost';
            $modelActivities->related_id = $model->aeropost_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if (isset($_SESSION['sessionAccessToken'])) {
                $fData['id'] = $model->id;
                $fData['module'] = '9';
                $fData['flagModule'] = 'invoice';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
            return url('/') . '/' . $pdf_path;
        }
    }

    public function edit(AeropostInvoices $invoices, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_aeropost_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $model = DB::table('invoices')->where('id', $id)->first();

        $dataInvoiceDetails  = DB::table('invoice_item_details')->where('invoice_id', $id)->get();
        $dataInvoiceDetails = json_decode(json_encode($dataInvoiceDetails));

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->where('item_code', '<>', 'SCC')->orderBy('id', 'desc')->get()->pluck('billing_name', 'id');

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'name');
        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        //$allUsers = json_decode($allUsers,1);
        //ksort($allUsers);

        $dataBillingItemsAutoComplete = BillingItems::getBillingItemsAutocomplete();
        $model->date = date('d-m-Y', strtotime($model->date));
        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        return view("aeropost-invoices.form", ['model' => $model, 'dataBillingItems' => $dataBillingItems, 'id' => $id, 'dataInvoiceDetails' => $dataInvoiceDetails, 'allUsers' => $allUsers, 'dataBillingItemsAutoComplete' => $dataBillingItemsAutoComplete, 'currency' => $currency, 'billingParty' => $billingParty, 'flagFromWhere' => $flagFromWhere, 'aeropostId' => $model->aeropost_id]);
    }

    public function update(Request $request, AeropostInvoices $invoices, $id)
    {
        session_start();
        $model = AeropostInvoiceItemDetails::where('invoice_id', $id)->delete();
        $model = AeropostInvoices::find($id);
        $dataInvoice = AeropostInvoices::find($id);
        $model->fill($request->input());
        Activities::log('update', 'aeropostinvoice', $model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $fileData = DB::table('aeropost')->where('file_number', $input['file_no'])->where('deleted', '0')->first();
        $input['date'] = date('Y-m-d', strtotime($input['date']));

        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);

        if ($input['balance_of'] == '0.00')
            $input['payment_status'] = 'Paid';

        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);

        $countInvoiceItems = $_POST['count_invoice_items'];

        $input['fees_name'] = array_values($input['fees_name']);
        $input['fees_name_desc'] = array_values($input['fees_name_desc']);
        $input['quantity'] = array_values($input['quantity']);
        $input['unit_price'] = array_values($input['unit_price']);
        $input['total_of_items'] = array_values($input['total_of_items']);

        for ($i = 0; $i < $countInvoiceItems; $i++) {
            $modelInvoiceDetails = new AeropostInvoiceItemDetails();
            $modelInvoiceDetails->invoice_id = $model->id;
            $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
            $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
            if (!empty($dataBilling))
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

        $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $input]);
        $pdf_file = 'printAeropostInvoice_' . $model->id . '.pdf';
        $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
        if ($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of) {
            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit') {
                $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
                $modelClient->save();
            }


            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            if ($input['balance_of'] - $dataInvoice->balance_of < 0) {
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of) . '-Amount Deposited.';
                $modelActivities->cash_credit_flag = '2';
            } else {
                $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of . '-Invoice Modified.';
                $modelActivities->cash_credit_flag = '1';
            }
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'aeropost';
        $modelActivities->related_id = $model->aeropost_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been modified';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '9';
            $fData['flagModule'] = 'updateInvoice';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        Session::flash('flash_message', 'Record has been updated successfully');
        if ($model->flag_invoice == 'old')
            return redirect('oldinvoices');
        else {
            if ($input['flagFromWhere'] == 'flagFromView') {
                return redirect()->route('viewdetailsaeropost', [$model->aeropost_id]);
            } else
                return redirect('aeropostinvoices');
        }
    }

    public function copy($id, $flag = null)
    {
        $model = AeropostInvoices::find($id);
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
        $fileData = DB::table('aeropost')->where('file_number', $model->file_no)->where('deleted', '0')->first();
        $ab = 'AP-';
        $ab .= substr($getLastInvoice->bill_no, 3) + 1;
        $model->bill_no = $ab;
        $model->date = date('Y-m-d');
        $model->payment_status = 'Pending';
        $model->credits = '0.00';
        $model->created_at = gmdate("Y-m-d H:i:s");
        //$model->balance_of = '0.00';
        $newModel = $model->replicate();
        $newModel->push();

        $modelInvoiceDetails = DB::table('invoice_item_details')->where('invoice_id', $id)->get();
        foreach ($modelInvoiceDetails as $key => $value) {
            $invoiceDetailModel = AeropostInvoiceItemDetails::find($value->id);
            $invoiceDetailModel->invoice_id = $newModel->id;
            $newInvoiceDetailModel = $invoiceDetailModel->replicate();
            $newInvoiceDetailModel->push();
        }

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'aeropost';
        $modelActivities->related_id = $newModel->aeropost_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $newModel->bill_no . ' has been generated';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $newModel->getAttributes()]);
        $pdf_file = 'printAeropostInvoice_' . $newModel->id . '.pdf';
        $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
        Session::flash('flash_message', 'Invoice has been copied successfully');
        return redirect()->route('editaeropostinvoice', ['id' => $newModel->id]);
        /* if($flag == 'fromlisting')
            return redirect()->route('aeropostinvoices');
        else
            return redirect()->route('editaeropostinvoice', ['id' => $newModel->id]); */
    }


    public function viewandprintaeropostinvoice($id)
    {
        $model = DB::table('invoices')->where('id', $id)->first();
        $model = (array) $model;
        return view("aeropost-invoices.viewandprint", ['invoice' => $model]);
    }

    public function destroy($id)
    {
        session_start();
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        $model = AeropostInvoices::where('id', $id)->update(['deleted' => '1', 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'aeropost';
        $modelActivities->related_id = $invoiceData->aeropost_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $invoiceData->bill_no . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '9';
            $fData['flagModule'] = 'deleteInvoice';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function deleteaeropostinvoicefromedit(AeropostInvoices $invoices, $id)
    {
        session_start();
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        $model = AeropostInvoices::where('id', $id)->update(['deleted' => '1', 'deleted_at' => gmdate("Y-m-d H:i:s")]);
        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'aeropost';
        $modelActivities->related_id = $invoiceData->aeropost_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $invoiceData->bill_no . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '9';
            $fData['flagModule'] = 'deleteInvoice';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        Session::flash('flash_message', 'Invoice has been deleted successfully');
        return redirect('aeropostinvoices');
    }

    public function getaeropostdetailforinvoice()
    {
        $dataAeropost = DB::table('aeropost')->where('id', $_POST['aeropostId'])->where('deleted', '0')->first();
        return json_encode($dataAeropost);
    }

    public function show($id)
    {
        $checkPermission = User::checkPermission(['view_details_aeropost_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $modelInvoices = new AeropostInvoices;
        $model = $modelInvoices->find($id);
        //pre($model->bill_no);
        $paymentDetail = DB::table('invoice_payments')->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency'])->join('invoices', 'invoices.bill_no', '=', 'invoice_payments.invoice_number')->where('invoice_payments.deleted', '0')->where('invoice_payments.aeropost_id', $model->aeropost_id)->where('invoice_payments.invoice_number', $model->bill_no)->get();
        //pre($paymentDetail);


        $totalOfHTG = 0;
        $totalOfUSD = 0;
        $totalOfCurrency[1] = '0.00';
        $totalOfCurrency[3] = '0.00';
        $i = 0;
        $count = count($paymentDetail);
        for ($i = 0; $i < $count; $i++) {
            if (!empty($paymentDetail[$i]->exchange_currency)) {
                if ($paymentDetail[$i]->exchange_currency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentDetail[$i]->id);
                    $totalOfCurrency[$paymentDetail[$i]->exchange_currency] =  $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentDetail[$i]->id);
                    $totalOfCurrency[$paymentDetail[$i]->exchange_currency] =  $totalOfHTG;
                }
            } else {
                $paymentDetail[$i]->exchange_currency = $paymentDetail[$i]->invoiceCurrency;
                if ($paymentDetail[$i]->invoiceCurrency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentDetail[$i]->id);
                    $totalOfCurrency[$paymentDetail[$i]->invoiceCurrency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentDetail[$i]->id);
                    $totalOfCurrency[$paymentDetail[$i]->invoiceCurrency] =  $totalOfHTG;
                }
            }
            /*$paymentReceivedByCashier[$i]->total = DB::table('invoice_payments')
                                                    ->where('invoice_id','=',$paymentReceivedByCashier[$i]->invoice_id)
                                                    ->where('payment_accepted_by',$id)
                                                    ->sum('amount');*/
        }
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();
        $htgTousd = $totalOfCurrency[1] * $exchangeRateOfUsdToHTH->exchangeRate;
        $totalOfCurrency['total'] = $totalOfCurrency[3] + $htgTousd;
        //pre($totalOfCurrency);

        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'aeropostinvoice')->orderBy('id', 'desc')->get()->toArray();
        $paymentData = DB::table('activities')->where('related_id', $id)->where('type', 'invoicePayment')->orderBy('id', 'desc')->get()->toArray();
        $allActivityData = array_merge($activityData, $paymentData);
        $query1 = array();
        foreach ($allActivityData as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->updated_on;
        }

        array_multisort((array) $query1, SORT_DESC, $allActivityData);
        return view('aeropost-invoices.view', ['model' => $model, 'paymentDetail' => $paymentDetail, 'totalOfCurrency' => $totalOfCurrency, 'activityData' => $allActivityData]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionAeropostInvoicesEdit = User::checkPermission(['update_aeropost_invoices'], '', auth()->user()->id);
        $permissionAeropostInvoicesDelete = User::checkPermission(['delete_aeropost_invoices'], '', auth()->user()->id);
        $permissionAeropostInvoicePaymentsAdd = User::checkPermission(['add_aeropost_invoice_payments'], '', auth()->user()->id);
        $permissionAeropostInvoicesCopy = User::checkPermission(['copy_aeropost_invoices'], '', auth()->user()->id);

        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['invoices.id', 'invoices.date', 'bill_no', 'aeropost.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.consignee_address', 'type_flag', 'currency.code', 'total', 'credits', 'payment_status'];

        $total = Invoices::selectRaw('count(*) as total')
            //->where('invoices.deleted', '0')
            ->whereNotNull('aeropost_id')
            ->whereNull('flag_invoice');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('invoices')
            ->selectRaw('invoices.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
            //->where('invoices.deleted', '0')
            ->whereNotNull('aeropost_id')
            ->whereNull('flag_invoice');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('invoices')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
            //->where('invoices.deleted', '0')
            ->whereNotNull('aeropost_id')
            ->whereNull('flag_invoice');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
        }



        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%')
                    ->orWhere('aeropost.file_number', 'like', '%' . $search . '%')
                    ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('type_flag', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%')
                    ->orWhere('aeropost.file_number', 'like', '%' . $search . '%')
                    ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('type_flag', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $dataBillingParty = app('App\Clients')->getClientData($items->bill_to);
            $dataCurrency = Currency::getData($items->currency);
            $aeropostData = app('App\Aeropost')->getAeropostData($items->aeropost_id);

            if (empty($aeropostData))
                continue;

            $action = '<div class="dropdown">';

            $delete =  route('deleteaeropostinvoice', $items->id);
            $edit =  route('editaeropostinvoice', $items->id);

            $action .= '<a title="View & Print"  target="_blank" href="' . route('viewandprintaeropostinvoice', $items->id) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($items->deleted == '0') {
                if ($permissionAeropostInvoicesEdit && $aeropostData->file_close != 1) {
                    if ($items->type_flag != 'Local') {
                        $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                    }
                }

                if ($permissionAeropostInvoicesDelete && checkloggedinuserdata() == 'Other') {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionAeropostInvoicesCopy) {
                    $action .= '<li><a href="' . route('copyaeropostinvoice', [$items->id, 'fromlisting']) . '">Copy Invoice</a></li>';
                }

                if ($items->payment_status == 'Pending' || $items->payment_status == 'Partial') {
                    if ($permissionAeropostInvoicePaymentsAdd) {

                        $action .= '<li><a href="' . route('addaeropostinvoicepayment', [$items->aeropost_id, $items->id, 0]) . '">Add Payment</a></li>';
                        $action .= '<li><a href="' . route('addaeropostinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                    }
                } else {
                    if ($items->type_flag != 'Local') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'aeropost']) . '">Payment Receipt</i></a>
                    </li>';
                    }
                }

                $action .= '</ul>';
            }
            $action .= '</div>';

            $data[] = [$items->id, date('d-m-Y', strtotime($items->date)), $items->bill_no, !empty($aeropostData) ? $aeropostData->file_number : '-', $items->awb_no, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $items->consignee_address, $items->type_flag, !empty($dataCurrency->code) ? $dataCurrency->code : "-", number_format($items->total, 2), number_format($items->credits, 2), $items->payment_status, $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserverside()
    {
        $flag = $_POST['flag'];
        if ($flag == 'getAeropostInvoiceData') {
            $invoiceId = $_POST['invoiceId'];
            return json_encode(AeropostInvoices::getAeropostInvoiceData($invoiceId));
        }
    }
}
