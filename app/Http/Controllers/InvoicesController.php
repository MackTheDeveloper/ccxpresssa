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
use PDF;
use App\Mail\sendCashierInvoiceMail;
use App\CheckGuaranteeToPay;
use App\Cargo;
use App\Currency;
use Illuminate\Support\Facades\Storage;
use App\Admin;
use App\InvoicePayments;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view("invoices.index");
    }

    public function indexpendinginvoices()
    {
        $checkPermission = User::checkPermission(['listing_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $pendingInvoices = DB::table('invoices')->where('deleted', '0')->where('payment_status', 'Pending')->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            /* ->where(function ($query) {
            $query->where('hawb_hbl_no', '==', '')
                ->orWhereNull('hawb_hbl_no');
        }) */
            ->orderBy('id', 'desc')->get();
        return view("invoices.pendinginvoiceindex", ['pendingInvoices' => $pendingInvoices]);
    }

    public function getcargoinvoiceforinputpicker()
    {
        //pre($_REQUEST,1);
        $limit = $_REQUEST['limit'];
        $start = ($_REQUEST['p'] - 1) * 10;
        $cargoId = $_REQUEST['cargoId'];
        $valueOfText = $_REQUEST['q'];

        $total = Cargo::selectRaw('count(*) as total')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->where('cargo.deleted', 0)
            ->whereNull('file_close');
        if (!empty($cargoId) && empty($valueOfText))
            $total = $total->where('cargo.id', $cargoId);
        if (!empty($valueOfText)) {
            $total->where(function ($total) use ($valueOfText) {
                $total->where('file_number', 'like', '%' . $valueOfText . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
            });
        }
        $total = $total->first();

        $datas = DB::table('cargo')
            ->select('cargo.id', 'cargo.file_number', 'cargo.consignee_name', 'cargo.shipper_name', 'cargo.awb_bl_no')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->where('cargo.deleted', 0)
            ->whereNull('file_close');
        if (!empty($cargoId) && empty($valueOfText))
            $datas = $datas->where('cargo.id', $cargoId);
        if (!empty($valueOfText)) {
            $datas->where(function ($datas) use ($valueOfText) {
                $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
            });
        }
        //$dataUps = $dataUps->get();
        $datas = $datas->offset($start)->limit($limit)->get();
        $NdataFileNumber = array();
        foreach ($datas as $k => $v) {
            $dataClientConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
            $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

            $NdataFileNumber[$k]['value'] = $v->id;
            $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
            $NdataFileNumber[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
            $NdataFileNumber[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            $NdataFileNumber[$k]['awb_number'] = !empty($v->awb_bl_no) ? $v->awb_bl_no : '-';;
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
    public function create($cargoId = null, $flagInvoice = null, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Invoices;

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get()->pluck('billing_name', 'id');
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
        if (empty($getLastInvoice)) {
            $model->bill_no = 'CA-5001';
        } else {
            $ab = 'CA-';
            $ab .= substr($getLastInvoice->bill_no, 3) + 1;
            $model->bill_no = $ab;
        }

        if (!empty($cargoId)) {
            $model->file_number = $cargoId;
            $model->cargo_id = $cargoId;
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

        $dataHawbAll = array();
        if (!empty($flagFromWhere)) {
            $dataCargoSingle = DB::table('cargo')->where('id', $cargoId)->first();
            $dAwb = explode(',', $dataCargoSingle->hawb_hbl_no);
            $dataHawb = DB::table('hawb_files')->where('deleted', 0)->whereIn('id', $dAwb)->get();
            if (!empty($dataHawb)) {
                foreach ($dataHawb as $k => $v) {
                    $dataHawbAll[$k]['value'] = $v->id;
                    $dataHawbAll[$k]['hawb_hbl_no'] = $v->cargo_operation_type == 1 ? $v->hawb_hbl_no : $v->export_hawb_hbl_no;
                    $dataHawbAll[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
                    $dataHawbAll[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
                }
            }
            $dataHawbAll = json_encode($dataHawbAll, JSON_NUMERIC_CHECK);
        } else
            $dataCargoSingle = new Cargo();

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');

        return view('invoices.form', ['model' => $model, 'dataBillingItems' => $dataBillingItems, 'allUsers' => $allUsers, 'dataBillingItemsAutoComplete' => $dataBillingItemsAutoComplete, 'currency' => $currency, 'billingParty' => $billingParty, 'flagFromWhere' => $flagFromWhere, 'dataCargoSingle' => $dataCargoSingle, 'dataHawbAll' => $dataHawbAll, 'cargoId' => $cargoId]);
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

        $fileData = DB::table('cargo')->where('file_number', $input['file_no'])->where('deleted', 0)->first();

        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);

        //$dataInvoice = DB::table('invoices')->where('bill_no',$input['bill_no'])->first();
        $dataInvoice = array();
        if (!empty($dataInvoice)) {
            $model = InvoiceItemDetails::where('invoice_id', $dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = auth()->user()->id;
            $model = Invoices::find($dataInvoice->id);
            $model->fill($request->input());
            Activities::log('update', 'cargoinvoice', $model);
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $model->update($input);

            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new InvoiceItemDetails();
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



            $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $input]);
            $pdf_file = 'printCargoInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/cargoInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Cargo/';
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            if ($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of) {
                $modelClient = Clients::where('id', $model->bill_to)->first();
                $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
                $modelClient->save();

                if ($input['balance_of'] - $dataInvoice->balance_of != 0) {
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
            }

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            if (isset($_SESSION['sessionAccessToken'])) {
                // pre($model);
                $fData['id'] = $model->id;
                $fData['module'] = '6';
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
            return redirect('invoices');
        } else {
            if ($input['flag_invoice'] != 'old') {
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $input['bill_no'] = 'CA-5001';
                } else {
                    $ab = 'CA-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $input['bill_no'] = $ab;
                }
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = Invoices::create($input);
            Activities::log('create', 'cargoinvoice', $model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new InvoiceItemDetails();
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

                if ($modelInvoiceDetails->item_code == '1042')
                    CheckGuaranteeToPay::where('master_cargo_id', $model->cargo_id)->update(['billed' => 1]);
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';



            $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $input]);
            $pdf_file = 'printCargoInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/cargoInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $allowToGenerateInvoicePayment = 1;
            $s3path = 'Files/Cargo/';
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $allowToGenerateInvoicePayment = 0;
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit_stop' && $allowToGenerateInvoicePayment == 1) {
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
                    $inputInvoicePayment['cargo_id'] = $model->cargo_id;
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

            // Store invoice activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'cargo';
            $modelActivities->related_id = $model->cargo_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            if (isset($_SESSION['sessionAccessToken'])) {
                // pre($model);
                $fData['id'] = $model->id;
                $fData['module'] = '6';
                $fData['flagModule'] = 'invoice';
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
            if ($model->flag_invoice == 'old')
                return redirect('oldinvoices');
            else {
                if ($input['flagFromWhere'] == 'flagFromView') {
                    if ($fileData->cargo_operation_type == 3)
                        return redirect()->route('viewcargolocalfiledetailforcashier', [$model->cargo_id]);
                    else
                        return redirect()->route('viewcargo', [$model->cargo_id, $fileData->cargo_operation_type]);
                } else
                    return redirect('invoices');
            }
        }
    }


    public function storecargoinvoiceandprint(Request $request)
    {
        session_start();
        $validater = $this->validate($request, [
            'file_number' => 'required',
        ]);
        $input = $request->all();

        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);
        $fileData = DB::table('cargo')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        $dataInvoice = DB::table('invoices')->where('bill_no', $input['bill_no'])->first();
        if ($input['saveandprintinupdate'] == '0')
            $dataInvoice = array();
        if (!empty($dataInvoice)) {
            $model = InvoiceItemDetails::where('invoice_id', $dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $model = Invoices::find($dataInvoice->id);
            $model->fill($request->input());
            Activities::log('update', 'cargoinvoice', $model);
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
                $modelInvoiceDetails = new InvoiceItemDetails();
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



            $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $input]);
            $pdf_file = 'printCargoInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/cargoInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Cargo/';
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            if ($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of) {
                $modelClient = Clients::where('id', $model->bill_to)->first();
                $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
                $modelClient->save();

                if ($input['balance_of'] - $dataInvoice->balance_of != 0) {
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
            }

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            if (isset($_SESSION['sessionAccessToken'])) {
                // pre($model);
                $fData['id'] = $model->id;
                $fData['module'] = '6';
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

            return url('/') . '/' . $pdf_path;
        } else {
            if ($input['flag_invoice'] != 'old') {
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $input['bill_no'] = 'CA-5001';
                } else {
                    $ab = 'CA-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $input['bill_no'] = $ab;
                }
            }
            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = Invoices::create($input);
            Activities::log('create', 'cargoinvoice', $model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new InvoiceItemDetails();
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



            $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $input]);
            $pdf_file = 'printCargoInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/cargoInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $allowToGenerateInvoicePayment = 1;
            $s3path = 'Files/Cargo/';
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $allowToGenerateInvoicePayment = 0;
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit_stop' && $allowToGenerateInvoicePayment == 1) {
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
                    $inputInvoicePayment['cargo_id'] = $model->cargo_id;
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

            // Store invoice activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = 'cargo';
            $modelActivities->related_id = $model->cargo_id;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            if (isset($_SESSION['sessionAccessToken'])) {
                // pre($model);
                $fData['id'] = $model->id;
                $fData['module'] = '6';
                $fData['flagModule'] = 'invoice';
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

            return url('/') . '/' . $pdf_path;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function show(Invoices $invoices, $id, $flag = null)
    {
        $checkPermission = User::checkPermission(['view_details_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $modelInvoices = new Invoices;
        $model = $modelInvoices->find($id);

        if ($flag == 'fromNotification')
            Invoices::where('id', $id)->update(['display_notification_admin_invoice' => 0]);

        if ($flag == 'fromNotificationCargoWarehouseInvoiceStatusChangedByCashier')
            Invoices::where('id', $id)->update(['display_notification_admin_invoice_status_changed' => 0]);

        $paymentDetail = DB::table('invoice_payments')
            ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency'])
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoice_payments.deleted', '0')
            ->where('invoice_payments.invoice_id', $id)->get();
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
        $htgTousd = $totalOfCurrency[1] * $exchangeRateOfUsdToHTH->exchangeRate;;
        $totalOfCurrency['total'] = $totalOfCurrency[3] + $htgTousd;

        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'cargoinvoice')->orderBy('id', 'desc')->get()->toArray();
        $paymentData = DB::table('activities')->where('related_id', $id)->where('type', 'invoicePayment')->orderBy('id', 'desc')->get()->toArray();
        $allActivityData = array_merge($activityData, $paymentData);
        $query1 = array();
        foreach ($allActivityData as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->updated_on;
        }

        array_multisort((array) $query1, SORT_DESC, $allActivityData);

        return view('invoices.view', ['model' => $model, 'paymentDetail' => $paymentDetail, 'totalOfCurrency' => $totalOfCurrency, 'activityData' => $allActivityData]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoices $invoices, $id, $flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $model = DB::table('invoices')->where('id', $id)->first();

        $dataInvoiceDetails  = DB::table('invoice_item_details')->where('invoice_id', $id)->get();
        $dataInvoiceDetails = json_decode(json_encode($dataInvoiceDetails));

        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get()->pluck('billing_name', 'id');

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

        $dataCargoSingle = DB::table('cargo')->where('id', $model->cargo_id)->first();
        $dAwb = explode(',', $dataCargoSingle->hawb_hbl_no);
        $dataHawb = DB::table('hawb_files')->where('deleted', 0)->whereIn('id', $dAwb)->get();
        $dataHawbAll = array();
        if (!empty($dataHawb)) {
            foreach ($dataHawb as $k => $v) {
                $dataHawbAll[$k]['value'] = $v->id;
                $dataHawbAll[$k]['hawb_hbl_no'] = $v->cargo_operation_type == 1 ? $v->hawb_hbl_no : $v->export_hawb_hbl_no;
                $dataHawbAll[$k]['consignee'] = !empty($v->consignee_name) ? $v->consignee_name : '-';
                $dataHawbAll[$k]['shipper'] = !empty($v->shipper_name) ? $v->shipper_name : '-';
            }
        }
        $dataHawbAll = json_encode($dataHawbAll, JSON_NUMERIC_CHECK);


        return view("invoices.form", ['model' => $model, 'dataBillingItems' => $dataBillingItems, 'id' => $id, 'dataInvoiceDetails' => $dataInvoiceDetails, 'allUsers' => $allUsers, 'dataBillingItemsAutoComplete' => $dataBillingItemsAutoComplete, 'currency' => $currency, 'billingParty' => $billingParty, 'dataHawbAll' => $dataHawbAll, 'cargoId' => $model->cargo_id, 'dataCargoSingle' => $dataCargoSingle, 'flagFromWhere' => $flagFromWhere]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoices $invoices, $id)
    {
        session_start();
        $model = InvoiceItemDetails::where('invoice_id', $id)->delete();
        $model = Invoices::find($id);
        $dataInvoice = Invoices::find($id);
        $model->fill($request->input());
        Activities::log('update', 'cargoinvoice', $model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $fileData = DB::table('cargo')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
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
            $modelInvoiceDetails = new InvoiceItemDetails();
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

            if ($modelInvoiceDetails->item_code == '1042')
                CheckGuaranteeToPay::where('master_cargo_id', $model->cargo_id)->update(['billed' => 1]);
        }
        $input['id'] = $model->id;
        $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $input]);
        $pdf_file = 'printCargoInvoice_' . $model->id . '.pdf';
        $pdf_path = 'public/cargoInvoices/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';

        if (!empty($fileData)) {
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
        }

        if ($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of) {
            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit') {
                $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
                $modelClient->save();
            }


            if ($input['balance_of'] - $dataInvoice->balance_of != 0) {
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
        }

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'cargo';
        $modelActivities->related_id = $model->cargo_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been modified';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '6';
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
                if ($fileData->cargo_operation_type == 3)
                    return redirect()->route('viewcargolocalfiledetailforcashier', [$model->cargo_id]);
                else
                    return redirect()->route('viewcargo', [$model->cargo_id, $fileData->cargo_operation_type]);
            } else
                return redirect('invoices');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Invoices  $invoices
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoices $invoices, $id)
    {
        session_start();
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        $model = Invoices::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'cargo';
        $modelActivities->related_id = $invoiceData->cargo_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $invoiceData->bill_no . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '6';
            $fData['flagModule'] = 'deleteInvoice';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function destroyfromedit(Invoices $invoices, $id)
    {
        session_start();
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        $model = Invoices::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'cargo';
        $modelActivities->related_id = $invoiceData->cargo_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $invoiceData->bill_no . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '6';
            $fData['flagModule'] = 'deleteInvoice';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
            $newModel = base64_encode(serialize($fData));
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        Session::flash('flash_message', 'Invoice has been deleted successfully');
        return redirect('invoices');
    }

    public function getcargodetailforinvoice()
    {
        /* $dataCargo = DB::table('cargo')->where('id',$_POST['cargoId'])->where('deleted',0)->first();
        $dataCargo = (array) $dataCargo; */

        $dataCargo = DB::table('cargo')
            ->select(['cargo.*', 'c1.company_name as consignee_full_name', 'c2.company_name as shipper_full_name'])
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->where('cargo.id', $_POST['cargoId'])->where('cargo.deleted', 0)->first();

        $dataCargo = (array) $dataCargo;



        $dataPackages = array();
        $dataPackages = DB::table('cargo_packages')->select(['pweight', 'pvolume', 'ppieces'])->where('cargo_id', $_POST['cargoId'])->first();
        $dataPackages = (array) $dataPackages;


        $all = array_merge($dataCargo, $dataPackages);
        return json_encode($all);
    }

    public function getcargohouseawbdetailforinvoice()
    {
        $dataPackages = array();
        $dataPackages = DB::table('hawb_packages')->select(['pweight', 'pvolume', 'ppieces'])->where('hawb_id', $_POST['id'])->first();
        return json_encode($dataPackages);
    }



    public function changeinvoicestatus()
    {
        $status = $_POST['status'];
        $changeStatus = ($status == 'Paid') ? 'Pending' : 'Paid';
        $invoiceId = $_POST['invoiceId'];
        $model = Invoices::find($invoiceId);
        $data['payment_status'] = $changeStatus;
        $model->fill($data);
        Activities::log('update', 'invoicepaymentstatus', $model);
        $userData = DB::table('invoices')->where('id', $invoiceId)->update(['payment_status' => $changeStatus]);

        Invoices::where('id', $invoiceId)->update(['display_notification_warehouse_invoice' => 1, 'notification_date_time' => date('Y-m-d H:i:s'), 'invoice_status_changed_by' => auth()->user()->id]);
        return 'true';
    }

    public function viewandprintcargoinvoice($id)
    {
        $model = DB::table('invoices')->where('id', $id)->first();
        $model = (array) $model;
        return view("invoices.viewandprint", ['invoice' => $model]);
    }

    public function copy($id, $flag = null)
    {
        $model = Invoices::find($id);
        //pre($model);
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
        $ab = 'CA-';
        $ab .= substr($getLastInvoice->bill_no, 3) + 1;
        $model->bill_no = $ab;
        $model->date = date('Y-m-d');
        $model->payment_status = 'Pending';
        $model->credits = '0.00';
        //$model->balance_of = '0.00';
        $newModel = $model->replicate();
        $newModel->push();
        $fileData = DB::table('cargo')->where('file_number', $model->file_no)->where('deleted', 0)->first();
        $modelInvoiceDetails = DB::table('invoice_item_details')->where('invoice_id', $id)->get();
        foreach ($modelInvoiceDetails as $key => $value) {
            $invoiceDetailModel = InvoiceItemDetails::find($value->id);
            $invoiceDetailModel->invoice_id = $newModel->id;
            $newInvoiceDetailModel = $invoiceDetailModel->replicate();
            $newInvoiceDetailModel->push();
        }

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'cargo';
        $modelActivities->related_id = $newModel->cargo_id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $newModel->bill_no . ' has been generated';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        /* $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $newModel->getAttributes()]);
        $pdf_file = 'printCargoInvoice_' . $newModel->id . '.pdf';
        $pdf_path = 'public/cargoInvoices/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';
        if ($fileData->cargo_operation_type == 1) {
            $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
        } else if ($fileData->cargo_operation_type == 2) {
            $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
        } else {
            $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public'); */

        Session::flash('flash_message', 'Invoice has been copied successfully');
        return redirect()->route('editinvoice', ['id' => $newModel->id]);
        /* if($flag == 'fromlisting')
            return redirect()->route('invoices');
        else
            return redirect()->route('editinvoice', ['id' => $newModel->id]); */
    }

    public function printpendinginvoices()
    {
        $pendingInvoiceData = DB::table('invoices')->where('deleted', '0')->where('payment_status', 'Pending')->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            /* ->where(function ($query) {
            $query->where('hawb_hbl_no', '==', '')
                ->orWhereNull('hawb_hbl_no');
        }) */
            ->orderBy('id', 'desc')->get();
        $pdf = PDF::loadView('invoices.printallpendinginvoices', ['pendingInvoiceData' => $pendingInvoiceData]);
        $pdf_file = 'pendingInvoices.pdf';
        $pdf_path = 'public/pendingInvoicesPdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'PendingInvoice.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function importcargoinvoices()
    {
        $checkPermission = User::checkPermission(['import_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        /*if($request->hasFile('import_file_destination_scan')){
                    $handle = fopen($_FILES['import_file_destination_scan']['tmp_name'], "r");
                    $linecount = 0;
                        while(!feof($handle)){
                          $line = fgets($handle);
                          if($line != "")
                          $linecount++;
                        }

                    
                    $handle = fopen($_FILES['import_file_destination_scan']['tmp_name'], "r");
                    while (($line = fgets($handle)) !== false) {
                    }
        }*/
    }

    public function sendMail(Request $request)
    {
        $itemid = $request->get('itemId');
        $itemData = DB::table('invoices')->where('id', $itemid)->first();
        $billingPartyData = DB::table('clients')->where('id', $itemData->bill_to)->first();
        $pdf_file = 'printCargoInvoice_' . $itemData->id . '.pdf';
        $pdf_path = 'public/cargoInvoices/' . $pdf_file;
        $emaildata['email'] = $billingPartyData->email;
        $emaildata['invoiceAttachment'] = $pdf_path;
        $send = Mail::to($emaildata['email'])->send(new sendCashierInvoiceMail($emaildata));
        if (!Mail::failures()) {
            $send = "Mail has been send successfully.";
        }
        echo $send;
    }


    public function invoiceReportIndex()
    {
        $checkPermission = User::checkPermission(['view_details_cargo_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $invoices = DB::table('invoices')->where('deleted', '0')->where('payment_status', '!=', 'Pending')
            ->whereNotNull('cargo_id')
            ->where(function ($query) {
                $query->where('type_flag', 'IMPORT')
                    ->orWhere('type_flag', 'EXPORT');
            })
            ->orderBy('id', 'desc')->get();
        return view("invoices.invoiceReportIndex", ['invoices' => $invoices]);
    }

    public function filter(Request $request)
    {
        $date = $request->get('date');
        if (empty($date)) {
            $data = DB::table('invoices')->where('deleted', '0')
                ->where(function ($query) {
                    $query->where('type_flag', 'IMPORT')
                        ->orWhere('type_flag', 'EXPORT');
                })
                ->where('payment_status', '!=', 'Pending')->get();
        } else {
            $date = date('Y-m-d', strtotime($date));
            $data = DB::table('invoices')->where('deleted', '0')->where('date', $date)
                ->where(function ($query) {
                    $query->where('type_flag', 'IMPORT')
                        ->orWhere('type_flag', 'EXPORT');
                })
                ->where('payment_status', '!=', 'Pending')->get();
        }

        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            $dataUser = app('App\Clients')->getClientData($data[$i]->bill_to);
            $data[$i]->bill_to = $dataUser->company_name;
            $dataCurrency = Currency::getData($data[$i]->currency);
            $data[$i]->currency = $dataCurrency->code;
        }

        return view("invoices.invoiceReportIndexAjax", ['data' => $data]);
    }

    public function viewDetail($id)
    {

        $details = DB::table('invoices')->where('deleted', '0')->where('id', $id)->get();
        $paymentDetail = DB::table('invoice_payments')->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency'])->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')->where('invoice_payments.deleted', '0')->where('invoice_payments.invoice_id', $id)->get();
        $modelInvoices = new Invoices;
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
        $htgTousd = $totalOfCurrency[1] * 83.97;
        $totalOfCurrency['total'] = $totalOfCurrency[3] + $htgTousd;
        //pre($totalOfCurrency);
        return view('invoices.allDetailInvoice', compact('details', 'paymentDetail', 'totalOfCurrency'));
    }

    public function invoiceoutsidefiltering()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $flag = $_POST['flag'];

        if ($flag == 'cargoInvoice') {
            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->where('deleted', '0')->whereNotNull('cargo_id')->whereNull('housefile_module')->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->where('deleted', '0')->whereNotNull('cargo_id')->whereNull('housefile_module')->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }
        if ($flag == 'cashierCargoInvoice') {
            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->where('deleted', '0')->whereNotNull('cargo_id')->whereNull('housefile_module')->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->where('deleted', '0')->whereNotNull('cargo_id')->whereNull('housefile_module')->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }
        if ($flag == 'warehouseCargoInvoice') {
            $getWarehouseOfUser =  DB::table('users')
                ->select('warehouses')
                ->where('id', auth()->user()->id)
                ->first();
            $wh = explode(',', $getWarehouseOfUser->warehouses);
            $dataCargo = DB::table('cargo')->select(DB::raw('group_concat(id) as consolidate'))->where('deleted', 0)->whereIn('warehouse', $wh)->first();
            $dataS = explode(',', $dataCargo->consolidate);

            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->where('deleted', '0')->whereNotNull('cargo_id')->whereIn('cargo_id', $dataS)->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->where('deleted', '0')->whereNotNull('cargo_id')->whereIn('cargo_id', $dataS)->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }

        if ($flag == 'upsInvoice') {
            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->whereNotNull('ups_id')->where('deleted', '0')->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->whereNotNull('ups_id')->where('deleted', '0')->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }

        if ($flag == 'aeropostInvoice') {
            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->whereNotNull('aeropost_id')->where('deleted', '0')->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->whereNotNull('aeropost_id')->where('deleted', '0')->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }

        if ($flag == 'ccpackInvoice') {
            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->whereNotNull('ccpack_id')->where('deleted', '0')->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->whereNotNull('ccpack_id')->where('deleted', '0')->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }

        if ($flag == 'houseFileInvoice') {
            $flagModule = $_POST['flagModule'];
            if (empty($flagModule))
                $flagModule = 'cargo';

            if (empty($fromDate) && empty($toDate))
                $data = DB::table('invoices')->where('housefile_module', $flagModule)->where('deleted', '0')->orderBy('id', 'desc')->get();
            else
                $data = DB::table('invoices')->where('housefile_module', $flagModule)->where('deleted', '0')->whereBetween('date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        }

        return view("invoices.allservicesinvoiceoutsidefilter", ['invoices' => $data, 'fromDate' => $fromDate, 'toDate' => $toDate, 'flag' => $flag]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoInvoicesEdit = User::checkPermission(['update_cargo_invoices'], '', auth()->user()->id);
        $permissionCargoInvoicesDelete = User::checkPermission(['delete_cargo_invoices'], '', auth()->user()->id);
        $permissionCargoInvoicesPaymentAdd = User::checkPermission(['add_cargo_invoice_payments'], '', auth()->user()->id);
        $permissionCargoInvoicesCopy = User::checkPermission(['copy_cargo_invoices'], '', auth()->user()->id);

        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['invoices.id', 'invoices.date', 'bill_no', 'cargo.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.consignee_address', 'currency.code', 'total', 'credits', 'users.name', 'payment_status'];

        $total = Invoices::selectRaw('count(*) as total')
            //->where('invoices.deleted', '0')
            ->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            ->whereNull('flag_invoice');
        /* ->where(function ($query) {
            $query->where('hawb_hbl_no', '=', '')
                ->orWhereNull('hawb_hbl_no');
        }) */
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('invoices')
            ->selectRaw('invoices.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->leftJoin('cargo', 'cargo.id', '=', 'invoices.cargo_id')
            //->where('invoices.deleted', '0')
            ->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            ->whereNull('flag_invoice');
        /* ->where(function ($query) {
            $query->where('invoices.hawb_hbl_no', '=', '')
                ->orWhereNull('invoices.hawb_hbl_no');
        }); */
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('invoices')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->leftJoin('cargo', 'cargo.id', '=', 'invoices.cargo_id')
            //->where('invoices.deleted', '0')
            ->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            ->whereNull('flag_invoice');
        /* ->where(function ($query) {
            $query->where('invoices.hawb_hbl_no', '=', '')
                ->orWhereNull('invoices.hawb_hbl_no');
        }); */
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('date', array($fromDate, $toDate));
        }



        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%')
                    ->orWhere('cargo.file_number', 'like', '%' . $search . '%')
                    ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%')
                    ->orWhere('cargo.file_number', 'like', '%' . $search . '%')
                    ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $dataBillingParty = app('App\Clients')->getClientData($items->bill_to);
            $dataCurrency = Currency::getData($items->currency);
            $dataUser = app('App\User')->getUserName($items->created_by);
            $cargoData = app('App\Cargo')->getCargoData($items->cargo_id);

            if (empty($cargoData))
                continue;

            $action = '<div class="dropdown">';

            $delete =  url('invoices/delete', [$items->id]);
            $edit =  route('editinvoice', $items->id);

            $action .= '<a title="View & Print"  target="_blank" href="' . route('viewandprintcargoinvoice', $items->id) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($items->deleted == '0') {
                if ($permissionCargoInvoicesEdit && $cargoData->file_close != 1) {
                    if ($items->type_flag != 'Local') {
                        $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                    }
                }

                if ($permissionCargoInvoicesDelete && checkloggedinuserdata() == 'Other') {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCargoInvoicesCopy) {
                    $action .= '<li><a href="' . route('copyinvoice', [$items->id, 'fromlisting']) . '">Copy Invoice</a></li>';
                }

                $action .= '<li><a href="javascript:void(0)"  data-value="' . $items->id . '" class="sendmailonlocalfile"> Send Mail </a></li>';

                if ($items->payment_status == 'Pending' || $items->payment_status == 'Partial') {
                    if ($permissionCargoInvoicesPaymentAdd) {
                        if ($items->type_flag != 'Local') {
                            $action .= '<li><a href="' . route('addinvoicepayment', [$items->cargo_id, $items->id, 0]) . '">Add Payment</a></li>';
                            $action .= '<li><a href="' . route('addinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                        }
                    }
                } else {
                    if ($items->type_flag != 'Local') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'cargo']) . '">Payment Receipt</i></a>
                    </li>';
                    }
                }

                $action .= '</ul>';
            }
            $action .= '</div>';

            $data[] = [$items->id, date('d-m-Y', strtotime($items->date)), $items->bill_no, !empty($cargoData) ? $cargoData->file_number : '-', $items->awb_no, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $items->consignee_address, !empty($dataCurrency->code) ? $dataCurrency->code : "-", number_format($items->total, 2), number_format($items->credits, 2), !empty($dataUser->name) ? $dataUser->name : "-", $items->payment_status, $action];
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
        if ($flag == 'getInvoiceData') {
            $invoiceId = $_POST['invoiceId'];
            return json_encode(Invoices::getInvoiceData($invoiceId));
        }
    }

    public function printinvoice($invoiceId, $flag = null)
    {
        $input = DB::table('invoices')->where('id', $invoiceId)->first();
        if ($flag == 'housefile') {
            $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => (array) $input]);
            $pdf_file = 'printInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'cargo') {
            $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printCargoInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/cargoInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'ups') {
            $pdf = PDF::loadView('upsinvoices.printupsinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printUpsInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/upsInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'upsMaster') {
            $pdf = PDF::loadView('ups-master-invoices.printupsmasterinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printUpsMasterInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/upsMasterInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'aeropost') {
            $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printAeropostInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'aeropostMaster') {
            $pdf = PDF::loadView('aeropost-master-invoices.printaeropostmasterinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printAeropostMasterInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/aeropostMasterInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'ccpack') {
            $pdf = PDF::loadView('ccpackinvoices.printccpackinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printCCpackInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/ccpackInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        if ($flag == 'ccpackMaster') {
            $pdf = PDF::loadView('ccpack-master-invoices.printccpackmasterinvoice', ['invoice' => (array) $input]);
            $pdf_file = 'printCcpackMasterInvoice_' . $invoiceId . '.pdf';
            $pdf_path = 'public/ccpackMasterInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
        }
        return response()->file($pdf_path);
    }

    public function checkexistingbillno()
    {
        if ($_POST['flag'] == 'cargo')
            $prefix = 'CA-';
        if ($_POST['flag'] == 'ups')
            $prefix = 'UP-';
        if ($_POST['flag'] == 'aeropost')
            $prefix = 'AP-';
        if ($_POST['flag'] == 'ccpack')
            $prefix = 'CC-';
        if ($_POST['flag'] == 'housefile')
            $prefix = 'HF-';


        $billNo = $_POST['billNo'];
        $dataInvoices = DB::table('invoices')->orderBy('id', 'desc')->first();
        $bData = explode('-', $dataInvoices->bill_no);
        $check = DB::table('invoices')->where('bill_no', $billNo)->where('deleted', '0')->count();
        $data = array();
        if ($check > 0) {
            $data['exist'] = '1';
            $data['billNo'] = $prefix . ($bData[1] + 1);
        } else {
            $data['exist'] = '0';
        }
        return json_encode($data);
    }
}
