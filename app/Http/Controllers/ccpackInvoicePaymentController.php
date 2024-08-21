<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\InvoicePayments;
use App\Invoices;
use App\Currency;
use App\Activities;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use Config;
use PDF;
use App\Clients;

class ccpackInvoicePaymentController extends Controller
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

    public function getclients()
    {
        $pendingInvoicesClients = DB::table('invoices')
            ->select(DB::raw("bill_to"))
            ->where(function ($query) {
                $query->whereNotNull('ccpack_id')
                    ->orWhereNotNull('ccpack_master_id');
            })
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->where('deleted', 0)
            ->distinct('bill_to')
            ->get();

        $dataeExploded = array();
        foreach ($pendingInvoicesClients as $k => $v) {
            $dataeExploded[] = $v->bill_to;
        }

        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)
            ->whereIn('id', $dataeExploded)
            ->orderBy('id', 'desc')->get();
        return json_encode($allUsers);
    }

    public function getccpackinvoices()
    {
        $invoiceArray = DB::table('invoices')
            ->select('bill_no', 'id')
            ->where(function ($query) {
                $query->whereNotNull('ccpack_id')
                    ->orWhereNotNull('ccpack_master_id');
            })
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->where('deleted', 0)
            ->get();
        return json_encode($invoiceArray);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($ccpackId = null, $invoceId = null, $billingParty = null, $fromMenu = null)
    {
        $checkPermission = User::checkPermission(['add_ccpack_invoice_payments'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new InvoicePayments;

        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        //$dataFileNumber = DB::table('ccpack')->where('deleted',0)->get()->pluck('file_number','id');
        $dataFileNumber = array();


        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $model->amount = '0.00';
        $model->ccpack_id = $ccpackId;
        $model->invoice_id = $invoceId;
        $model->client = $billingParty;

        /* $invoiceArray = DB::table('invoices')->whereNotNull('ccpack_id')
            ->where(function ($query) {
                    $query->where('payment_status','Pending')
                          ->orWhere('payment_status','Partial');
                })
            ->where('deleted',0)
            ->get()->pluck('bill_no','id'); */
        $invoiceArray = array();

        /* $pendingInvoicesClients = DB::table('invoices')
            ->select(DB::raw("bill_to"))
            ->whereNotNull('ccpack_id')
            ->where(function ($query) {
                    $query->where('payment_status','Pending')
                          ->orWhere('payment_status','Partial');
                })
            ->where('deleted',0)
            ->distinct('bill_to')
            ->get();

        $dataeExploded = array();
        foreach($pendingInvoicesClients as $k => $v)            
        {
            $dataeExploded[] = $v->bill_to;
        } */
        //$dataeExploded = explode(',', $pendingInvoicesClients->billTo);    


        /* $allUsers = DB::table('clients')->select(['id','company_name'])->where('client_flag','B')->where('deleted',0)->where('status',1)
        ->whereIn('id',$dataeExploded)
        ->orderBy('id','desc')->pluck('company_name', 'id'); */
        $allUsers = array();

        $paymentVia = Config::get('app.paymentMethod');

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');

        return view("ccpackinvoicepayments.form", ['cashCredit' => $cashCredit, 'model' => $model, 'dataFileNumber' => $dataFileNumber, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'ccpackId' => $ccpackId, 'invoceId' => $invoceId, 'invoiceArray' => $invoiceArray, 'paymentVia' => $paymentVia, 'billingParty' => $billingParty, 'fromMenu' => $fromMenu, 'currency' => $currency]);
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
        //pre($input);

        if (!empty($input['client']) || !empty($input['invoice_id'])) {
            $input['payment'] = array_filter($input['payment']);
            $exchageValues = array_filter($input['exchange_amount']);
            $paymentCredit = array_filter($input['paymentCredit']);
            $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
            if (empty($getLastReceiptNumber)) {
                $receiptNumber = '11101';
            } else {
                if (empty($getLastReceiptNumber->receipt_number))
                    $receiptNumber = '11101';
                else
                    $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
            }
            foreach ($input['payment'] as $key => $value) {

                $value = str_replace(',', '', $value);
                $dataInvoice = DB::table('invoices')->where('id', $key)->first();
                if (!empty($dataInvoice->ccpack_id)) {
                    $input['flagModule'] = 'CCPack';
                } else {
                    $input['flagModule'] = 'CCPack Master';
                }

                if ($input['flagModule'] == 'CCPack')
                    $dataCCpack = DB::table('ccpack')->where('id', $dataInvoice->ccpack_id)->first();
                else
                    $dataCCpack = DB::table('ccpack_master')->where('id', $dataInvoice->ccpack_master_id)->first();


                $input['invoice_id'] = $key;
                $input['invoice_number'] = $dataInvoice->bill_no;
                if ($input['flagModule'] == 'CCPack')
                    $input['ccpack_id'] = $dataInvoice->ccpack_id;
                else
                    $input['ccpack_master_id'] = $dataInvoice->ccpack_master_id;
                $input['file_number'] = $dataCCpack->file_number;
                $input['amount'] = $value;
                $input['exchange_amount'] = str_replace(',', '', $exchageValues[$key]);
                $input['exchange_currency'] = $input['exchange_currency'];
                $input['credited_amount'] = !empty($input['amt-credit-to-client']) ? str_replace(',', '', $input['amt-credit-to-client']) : '';
                $input['created_at'] = gmdate("Y-m-d H:i:s");
                if (empty($input['client']))
                    $input['client'] = $dataInvoice->bill_to;
                $input['payment_accepted_by'] = auth()->user()->id;
                $input['receipt_number'] = $receiptNumber;
                $model = InvoicePayments::create($input);

                // Store activities
                $modelActivities = new Activities;
                $modelActivities->type = 'invoicePayment';
                $modelActivities->related_id = $key;
                $modelActivities->user_id = auth()->user()->id;
                $descForClientActivity = '';
                if (!empty($input['exchange_currency']) && $input['exchange_currency'] != $dataInvoice->currency)
                {
                    $paymentCurrency = $input['exchange_currency'];
                    if (isset($paymentCredit[$key])) {
                        $paymentCredit[$key] = str_replace(',', '', $paymentCredit[$key]);
                        $clientData = DB::table('clients')->join('currency', 'currency.id', '=', 'clients.currency')->where('clients.id', $dataInvoice->bill_to)->first();
                        if ($clientData->cash_credit == 'Credit') {
                            if ($clientData->code == 'USD') {
                                $exchangeRateOfHTGToUsd = DB::table('currency_exchange')
                                    ->select(['currency_exchange.exchange_value as exchangeRate'])
                                    ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
                                    ->where('currency.code', 'HTG')
                                    ->first();

                                $deductAmount = $paymentCredit[$key] * $exchangeRateOfHTGToUsd->exchangeRate;
                            } else {
                                $exchangeRateOfUsdToHTG = DB::table('currency_exchange')
                                    ->select(['currency_exchange.exchange_value as exchangeRate'])
                                    ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
                                    ->where('currency.code', 'USD')
                                    ->first();

                                $deductAmount = $paymentCredit[$key] * $exchangeRateOfUsdToHTG->exchangeRate;
                            }
                            $modelClient = Clients::where('id', $dataInvoice->bill_to)->first();
                            $modelClient->available_balance = $modelClient->available_balance - $deductAmount;
                            $modelClient->save();
                        }
                        $descForClientActivity = ' (Credit Used ' . number_format($paymentCredit[$key], 2) . ')';
                    }
                }
                else
                {
                    $paymentCurrency = $dataInvoice->currency;
                    if (isset($paymentCredit[$key])) {
                        $paymentCredit[$key] = str_replace(',', '', $paymentCredit[$key]);
                        $modelClient = Clients::where('id', $dataInvoice->bill_to)->first();
                        $modelClient->available_balance = $modelClient->available_balance - $paymentCredit[$key];
                        $modelClient->save();

                        $descForClientActivity = ' (Credit Used ' . number_format($paymentCredit[$key], 2) . ')';
                    }
                }

                $dataCurrency = Currency::getData($paymentCurrency);
                $modelActivities->description = "Payment Received " . number_format($input['exchange_amount'], 2) . " (" . $dataCurrency->code . ")";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                // Store payment received activity on file level
                $modelActivities = new Activities;
                if ($input['flagModule'] == 'CCPack') {
                    $modelActivities->type = 'ccpack';
                    $relatedID = $dataInvoice->ccpack_id;
                } else {
                    $modelActivities->type = 'ccpackMaster';
                    $relatedID = $dataInvoice->ccpack_master_id;
                }
                $modelActivities->related_id = $relatedID;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = 'Invoice #' . $dataInvoice->bill_no . " Payment Received " . number_format($input['exchange_amount'], 2) . " (" . $dataCurrency->code . ")";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                if ($value == $dataInvoice->balance_of)
                    DB::table('invoices')->where('id', $key)->update(['credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00', 'payment_received_on' => date('Y-m-d'), 'payment_received_by' => auth()->user()->id]);
                else
                    DB::table('invoices')->where('id', $key)->update(['credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);

                $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                $pdf = PDF::loadView('ccpackinvoices.printccpackinvoice', ['invoice' => $dataInvoiceForPrint]);
                $pdf_file = 'printCCpackInvoice_' . $key . '.pdf';
                $pdf_path = 'public/ccpackInvoices/' . $pdf_file;
                $pdf->save($pdf_path);


                // Store deposite activities
                $modelActivities = new Activities;
                $modelActivities->type = 'cashCreditClient';
                $modelActivities->related_id = $dataInvoice->bill_to;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = number_format($value, 2) . '-Invoice Payment Paid.' . $descForClientActivity;
                $modelActivities->cash_credit_flag = '1';
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                /* $dataclient = DB::table('clients')->where('id',$dataInvoice->bill_to)->first();
                $lastBalance = $dataclient->available_balance + $value;
                DB::table('clients')->where('id',$dataInvoice->bill_to)->update(['available_balance' => $lastBalance]); */
            }
        }

        $creditedAmount = 0;
        if (!empty($input['amt-credit-to-client']) && $input['amt-credit-to-client'] > 0) {
            $creditedAmount = str_replace(',', '', $input['amt-credit-to-client']);
            if (empty($input['client']))
                $input['client'] = $dataInvoice->bill_to;

            $dataclient = DB::table('clients')->where('id', $input['client'])->first();
            $lastBalance = $dataclient->available_balance + str_replace(',', '', $input['amt-credit-to-client']);
            DB::table('clients')->where('id', $input['client'])->update(['available_balance' => $lastBalance]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $input['client'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $input['amt-credit-to-client'] . '- Amount deposited.';
            $modelActivities->cash_credit_flag = '2';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {
            if ($input['flagInvoiceOrClient'] == 'invoice') {
                $id = $input['invoice_id'];
                $dataInvoice = DB::table('invoices')->where('id', $id)->first();
                $dataClient = DB::table('clients')->where('id', $dataInvoice->bill_to)->first();
                $data = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.invoice_id', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0');
                if ($input['flagModule'] == 'CCPack')
                    $data = $data->whereNotNull('invoice_payments.ccpack_id');
                else
                    $data = $data->whereNotNull('invoice_payments.ccpack_master_id');

                $data = $data->orderBy('invoice_payments.id', 'DESC')
                    ->get();
            } else {
                $selectedInvoices = array_filter(array_keys($input['payment']));
                $id = $input['client'];
                $dataClient = DB::table('clients')->where('id', $id)->first();
                $data = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0');

                if ($input['flagModule'] == 'CCPack')
                    $data = $data->whereNotNull('invoice_payments.ccpack_id');
                else
                    $data = $data->whereNotNull('invoice_payments.ccpack_master_id');

                $data = $data->whereIn('invoices.id', $selectedInvoices)
                    ->orderBy('invoice_payments.id', 'DESC')
                    ->get();
            }
            $totalHTG = 0;
            $totalUSD = 0;
            foreach ($data as $key => $value) {
                if ($value->exchangeCurrencyCode == 'HTG')
                    $totalHTG += $value->total_payments_collected;
                if ($value->exchangeCurrencyCode == 'USD')
                    $totalUSD += $value->total_payments_collected;
            }

            $allTotal = array();
            if (!empty($totalHTG))
                $allTotal['HTG'] = $totalHTG;
            if (!empty($totalUSD))
                $allTotal['USD'] = $totalUSD;
            $flagChangeLayout = 0;
            $scale = [76, 236];
            $pdf = PDF::loadView('invoicepayments.printreceipt', ['data' => $data, 'dataClient' => $dataClient, 'flag' => $input['flagInvoiceOrClient'], 'total' => $allTotal, 'flagChangeLayout' => $flagChangeLayout, 'creditedAmount' => $creditedAmount], [], ['format' => $scale]);

            $pdf_file = 'payment_receipt.pdf';
            $pdf_path = 'public/paymentReceipt/' . $pdf_file;
            $pdf->save($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else {
            Session::flash('flash_message', 'Payment has been received successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getselectedccpackinvoicedata()
    {
        $invoiceId = $_POST['invoiceId'];
        $invoiceData = DB::table('invoices')->where('id', $invoiceId)->first();
        $flagModule = 'CCPack';
        $dataInvoice = DB::table('invoices')->where('id', $invoiceId)->get();
        return view("invoicepayments.getclientinvoicesajax", ['dataInvoice' => $dataInvoice, 'flagModule' => $flagModule]);
    }


    public function getccpackinvoicesofclient()
    {
        $clientId = $_POST['clientId'];
        $dataInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)->where('payment_status', '!=', 'Paid')
            ->where(function ($query) {
                $query->whereNotNull('ccpack_id')
                    ->orWhereNotNull('ccpack_master_id');
            })
            ->where('invoices.total', '!=', '0.00')->get();
        return view("invoicepayments.getclientinvoicesajax", ['dataInvoice' => $dataInvoice, 'flagModule' => 'CCPack']);
    }


    public function getcurrencyratesection()
    {
        $id = $_POST['id'];
        $selectedInvoiceIds = $_POST['selectedInvoiceIds'];
        $explodedselectedInvoiceIds =  explode(',', $selectedInvoiceIds);
        $currenies = [];
        $dataInvoice = DB::table('invoices')->whereIn('id', $explodedselectedInvoiceIds)->get();
        foreach ($dataInvoice as $key => $value) {
            $currenies[] = $value->currency;
        }


        $currencyExchangeData = DB::table('currency_exchange')
            ->select('currency_exchange.*')
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->whereIn('from_currency', $currenies)
            ->where('to_currency', $id)
            ->get();

        //pre($currencyExchangeData);


        return view("invoicepayments.getcurrencyratesecion", ['currencyExchangeData' => $currencyExchangeData]);
    }
}
