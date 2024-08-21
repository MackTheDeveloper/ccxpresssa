<?php

namespace App\Http\Controllers;

use App\InvoicePayments;
use App\Invoices;
use App\Currency;
use App\InvoiceItemDetails;
use App\HawbFiles;
use App\Activities;
use App\Ups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use Config;
use PDF;
use Response;
use App\Clients;

class InvoicePaymentsController extends Controller
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


    public function listbyall(Request $request)
    {
        $req = $request->all();
        $clientId = $req['clientId'];
        $flagV = isset($req['flagV']) ? $req['flagV'] : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['id', 'bill_no', '', '', 'date', '', '', '', '', '', ''];
        $cond1 = [['cargo_id', '<>', null], ['housefile_module', null]];
        $total = Invoices::selectRaw('count(*) as total')
            ->where('bill_to', $clientId)
            ->where('deleted', 0)
            ->where('payment_status', '!=', 'Paid')
            ->where('invoices.total', '!=', '0.00')
            ->where(function ($query2) use ($cond1) {
                $query2->where($cond1)
                    ->orWhere('housefile_module', 'cargo')
                    ->orWhere(function ($subQ1){
                        $subQ1->whereNotNull('ups_id')
                        ->whereIn('ups_id',function($whereInUps){
                            $whereInUps->select('id')->from('ups_details');
                        });
                    })
                    // ->orWhereNotNull('ups_id')
                    ->orWhere(function ($subQ2){
                        $subQ2->whereNotNull('aeropost_id')
                        ->whereIn('aeropost_id',function($whereInAero){
                            $whereInAero->select('id')->from('aeropost');
                        });
                    })
                    // ->orWhereNotNull('aeropost_id')
                    ->orWhere(function ($subQ3){
                        $subQ3->whereNotNull('ccpack_id')
                        ->whereIn('ccpack_id',function($whereInCcpack){
                            $whereInCcpack->select('id')->from('ccpack');
                        });
                    });
                    // ->orWhereNotNull('ccpack_id');
            });
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('invoices')
            ->selectRaw('invoices.*')
            ->where('bill_to', $clientId)
            ->where('deleted', 0)
            ->where('payment_status', '!=', 'Paid')
            ->where('invoices.total', '!=', '0.00')
            ->where(function ($query2) use ($cond1) {
                $query2->where($cond1)
                    ->orWhere('housefile_module', 'cargo')
                    ->orWhere(function ($subQ1){
                        $subQ1->whereNotNull('ups_id')
                        ->whereIn('ups_id',function($whereInUps){
                            $whereInUps->select('id')->from('ups_details');
                        });
                    })
                    // ->orWhereNotNull('ups_id')
                    ->orWhere(function ($subQ2){
                        $subQ2->whereNotNull('aeropost_id')
                        ->whereIn('aeropost_id',function($whereInAero){
                            $whereInAero->select('id')->from('aeropost');
                        });
                    })
                    // ->orWhereNotNull('aeropost_id')
                    ->orWhere(function ($subQ3){
                        $subQ3->whereNotNull('ccpack_id')
                        ->whereIn('ccpack_id',function($whereInCcpack){
                            $whereInCcpack->select('id')->from('ccpack');
                        });
                    });
                    // ->orWhereNotNull('ccpack_id');
            });

        $filteredq = DB::table('invoices')
            ->where('bill_to', $clientId)
            ->where('deleted', 0)
            ->where('payment_status', '!=', 'Paid')
            ->where('invoices.total', '!=', '0.00')
            ->where(function ($query2) use ($cond1) {
                $query2->where($cond1)
                    ->orWhere('housefile_module', 'cargo')
                    ->orWhere(function ($subQ1){
                        $subQ1->whereNotNull('ups_id')
                        ->whereIn('ups_id',function($whereInUps){
                            $whereInUps->select('id')->from('ups_details');
                        });
                    })
                    // ->orWhereNotNull('ups_id')
                    ->orWhere(function ($subQ2){
                        $subQ2->whereNotNull('aeropost_id')
                        ->whereIn('aeropost_id',function($whereInAero){
                            $whereInAero->select('id')->from('aeropost');
                        });
                    })
                    // ->orWhereNotNull('aeropost_id')
                    ->orWhere(function ($subQ3){
                        $subQ3->whereNotNull('ccpack_id')
                        ->whereIn('ccpack_id',function($whereInCcpack){
                            $whereInCcpack->select('id')->from('ccpack');
                        });
                    });
                    // ->orWhereNotNull('ccpack_id');
            });

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('bill_no', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('bill_no', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $allInvoices = [];
        foreach ($query as $k => $v) {
            if (isset($v->cargo_id) && !empty($v->cargo_id) && empty($v->housefile_module)) {
                $bill_no = $v->bill_no . ' (Cargo)';
                $typeFlag = 'Cargo';
                $shipmentNumber = '-';
            }
            if (isset($v->hawb_hbl_no) && !empty($v->hawb_hbl_no) && !empty($v->housefile_module) && $v->housefile_module == 'cargo') {
                $bill_no = $v->bill_no . ' (Cargo HouseFile)';
                $typeFlag = 'Cargo Housefile';
                $shipmentNumber = '-';
            }
            if (isset($v->ups_id) && !empty($v->ups_id)) {
                $bill_no = $v->bill_no . ' (UPS)';
                $typeFlag = 'UPS';
                $upsData = Ups::getUpsData($v->ups_id);
                if (!empty($upsData) && !empty($upsData->shipment_number))
                    $shipmentNumber = $upsData->shipment_number;
                else
                    $shipmentNumber = '-';
            }
            if (isset($v->aeropost_id) && !empty($v->aeropost_id)) {
                $bill_no = $v->bill_no . ' (Aeropost)';
                $typeFlag = 'Aeropost';
                $shipmentNumber = '-';
            }
            if (isset($v->ccpack_id) && !empty($v->ccpack_id)) {
                $bill_no = $v->bill_no . ' (CCpack)';
                $typeFlag = 'CCpack';
                $shipmentNumber = '-';
            }

            $checkBoxes = '<input type="checkbox" data-currency="' . $v->currency . '" name="singlecheckbox" class="singlecheckbox" id="' . $v->id . '" value="' . $v->id . '" />';

            $billingParty = app('App\Clients')->getClientData($v->bill_to);
            $dataCurrency = Currency::getData($v->currency);

            $input5 = '<input style="text-align:right;width: 200px;float: right;" type="text" id="paymentCredit-' . $v->id . '" class="form-control paymentCredit" name="paymentCredit[' . $v->id . ']" value="">';

            $input1 = '<input style="text-align:right;width: 200px;float: right;" type="text" id="due-amt-fill-' . $v->id . '" class="form-control input-due-amt" name="payment[' . $v->id . ']" value="">';

            $input2 = '<input style="text-align:right;width: 200px;float: right;" type="text" class="form-control exchange_amount" id="exchange_amount-' . $v->id . '" name="exchange_amount[' . $v->id . ']" value="">';

            $input3 = '<input type="hidden" name="courierorcargo[' . $v->id . ']" value="' . $typeFlag . '">';

            if ($typeFlag == 'Cargo') {
                $input4 = '<input type="hidden" name="cargoInvoices[]" value="' . $v->id . '">';
            }
            if ($typeFlag == 'Cargo Housefile') {
                $input4 = '<input type="hidden" name="CargoHousefileInvoices[]" value="' . $v->id . '">';
            }
            if ($typeFlag == 'UPS') {
                $input4 = '<input type="hidden" name="upsInvoices[]" value="' . $v->id . '">';
            }
            if ($typeFlag == 'Aeropost') {
                $input4 = '<input type="hidden" name="aeropostInvoices[]" value="' . $v->id . '">';
            }
            if ($typeFlag == 'CCpack') {
                $input4 = '<input type="hidden" name="ccpackInvoices[]" value="' . $v->id . '">';
            }

            $allInvoices[] = [$checkBoxes, 'Invoice #' . $bill_no, $shipmentNumber, !empty($billingParty->company_name) ? $billingParty->company_name : "-", date('d-m-Y', strtotime($v->date)), !empty($dataCurrency->code) ? $dataCurrency->code : "-", number_format($v->total, 2), number_format(($v->total - $v->credits), 2), $input5, $input1, $input2, $typeFlag, $v->id, $input3, $input4];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $allInvoices
        );
        return Response::json($json_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($cargoId = null, $invoceId = null, $billingParty = null, $fromMenu = null, $flagModule = null)
    {
        $checkPermission = User::checkPermission(['add_cargo_invoice_payments'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new InvoicePayments;

        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $dataFileNumber = DB::table('cargo')->where('deleted', 0)->get()->pluck('file_number', 'id');

        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        $model->amount = '0.00';
        $model->cargo_id = $cargoId;
        $model->invoice_id = $invoceId;
        $model->client = $billingParty;


        $invoiceArray = DB::table('invoices')

            ->where(function ($query) {
                $query->where('housefile_module', 'cargo')
                    ->orWhereNotNull('cargo_id');
            })
            //->where('type_flag', '!=', 'Local')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->where('deleted', 0)
            ->get()->pluck('bill_no', 'id');


        $pendingInvoicesClients = DB::table('invoices')
            ->select(DB::raw("bill_to"))
            ->where(function ($query) {
                $query->where('housefile_module', 'cargo')
                    ->orWhereNotNull('cargo_id');
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
        //$dataeExploded = explode(',', $pendingInvoicesClients->billTo);    


        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)
            ->whereIn('id', $dataeExploded)
            ->orderBy('id', 'desc')->pluck('company_name', 'id');


        $paymentVia = Config::get('app.paymentMethod');

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');

        return view("invoicepayments.form", ['cashCredit' => $cashCredit, 'model' => $model, 'dataFileNumber' => $dataFileNumber, 'allUsers' => $allUsers, 'cashCredit' => $cashCredit, 'cargoId' => $cargoId, 'invoceId' => $invoceId, 'invoiceArray' => $invoiceArray, 'paymentVia' => $paymentVia, 'billingParty' => $billingParty, 'fromMenu' => $fromMenu, 'currency' => $currency, 'flagModule' => $flagModule]);
    }

    public function getclientsforallpayment()
    {
        $pendingInvoicesClients = DB::table('invoices')
            ->select(DB::raw("bill_to"))
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
        $allUsers = DB::table('clients')->select('id', 'company_name')->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)
            ->whereIn('id', $dataeExploded)
            ->orderBy('id', 'desc')->get();
        return json_encode($allUsers);
    }

    public function createforall($clientId = null)
    {
        $checkPermission = User::checkPermission(['add_cargo_invoice_payments', 'add_courier_invoice_payments'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new InvoicePayments;

        /* $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit); */

        //$dataFileNumber = DB::table('cargo')->where('deleted', 0)->get()->pluck('file_number', 'id');

        /* $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id'); */
        $allUsers = array();
        /* $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit); */

        /* $invoiceArray = DB::table('invoices')->whereNotNull('cargo_id')
            //->where('type_flag', '!=', 'Local')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->where('deleted', 0)
            ->get()->pluck('bill_no', 'id'); */


        $paymentVia = Config::get('app.paymentMethod');

        $courierCargo['Cargo'] = 'Cargo';
        $courierCargo['Courier'] = 'Courier';

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $invoiceArray = array();
        return view("invoicepayments.formforall", ['model' => $model, 'allUsers' => $allUsers, 'paymentVia' => $paymentVia, 'courierCargo' => $courierCargo, 'currency' => $currency, 'clientId' => $clientId, 'invoiceArray' => $invoiceArray]);
    }

    public function getinvoicesusingfilenumber()
    {
        $cargoId = $_POST['cargoId'];
        $invoiceNumbers = DB::table('invoices')->where('cargo_id', $cargoId)->where('payment_status', 'Pending')->where('deleted', '0')->get();
        $dt = '';
        foreach ($invoiceNumbers as $key => $value) {
            $dt .=  '<option value="' . $value->id . '">' . $value->bill_no . '</option>';
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
                if (!empty($dataInvoice->housefile_module) && $dataInvoice->housefile_module == 'cargo') {
                    $input['flagModule'] = 'housefile';
                } else if (empty($dataInvoice->housefile_module) && !empty($dataInvoice->cargo_id)) {
                    $input['flagModule'] = 'cargo';
                }

                if ($input['flagModule'] == 'housefile')
                    $dataCargo = DB::table('hawb_files')->where('id', $dataInvoice->hawb_hbl_no)->first();
                else
                    $dataCargo = DB::table('cargo')->where('id', $dataInvoice->cargo_id)->first();

                $input['invoice_id'] = $key;
                $input['invoice_number'] = $dataInvoice->bill_no;
                if ($input['flagModule'] == 'housefile')
                    $input['house_file_id'] = $dataInvoice->hawb_hbl_no;
                else
                    $input['cargo_id'] = $dataInvoice->cargo_id;
                $input['file_number'] = $dataCargo->file_number;
                $input['amount'] =  str_replace(',', '', $value);
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
                if (!empty($input['exchange_currency']) && $input['exchange_currency'] != $dataInvoice->currency) {
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
                } else {
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
                if ($input['flagModule'] == 'housefile') {
                    $modelActivities->type = 'houseFile';
                    $relatedID = $dataInvoice->hawb_hbl_no;
                } else {
                    $modelActivities->type = 'cargo';
                    $relatedID = $dataInvoice->cargo_id;
                }
                $modelActivities->related_id = $relatedID;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = 'Invoice #' . $dataInvoice->bill_no . " Payment Received " . number_format($input['exchange_amount'], 2) . " (" . $dataCurrency->code . ")";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                if ($value == $dataInvoice->balance_of)
                    DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'display_notification_warehouse_invoice' => 1, 'notification_date_time' => date('Y-m-d H:i:s'), 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00', 'payment_received_on' => date('Y-m-d'), 'payment_received_by' => auth()->user()->id]);
                else
                    DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'display_notification_warehouse_invoice' => 1, 'notification_date_time' => date('Y-m-d H:i:s'), 'credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);

                $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $dataInvoiceForPrint]);
                $pdf_file = 'printCargoInvoice_' . $key . '.pdf';
                $pdf_path = 'public/cargoInvoices/' . $pdf_file;
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

                /* $dataclient = DB::table('clients')->where('id', $dataInvoice->bill_to)->first();
                $lastBalance = $dataclient->available_balance + $value;
                DB::table('clients')->where('id', $dataInvoice->bill_to)->update(['available_balance' => $lastBalance]); */
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
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.invoice_id', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0');
                if ($input['flagModule'] == 'housefile')
                    $data = $data->whereNotNull('invoice_payments.house_file_id');
                else
                    $data = $data->whereNotNull('invoice_payments.cargo_id');
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
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0');
                if ($input['flagModule'] == 'housefile')
                    $data = $data->whereNotNull('invoice_payments.house_file_id');
                else
                    $data = $data->whereNotNull('invoice_payments.cargo_id');

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


        //return redirect('invoices');
    }

    public function storeall(Request $request)
    {
        $input = $request->all();
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

                if ($input['courierorcargo'][$key] == 'Cargo') {
                    $dataInvoice = DB::table('invoices')->where('id', $key)->first();
                    $dataAll = DB::table('cargo')->where('id', $dataInvoice->cargo_id)->first();
                    $input['house_file_id'] = null;
                    $input['ups_id'] = null;
                    $input['aeropost_id'] = null;
                    $input['ccpack_id'] = null;
                    $input['cargo_id'] = $dataInvoice->cargo_id;
                }

                if ($input['courierorcargo'][$key] == 'Cargo Housefile') {
                    $dataInvoice = DB::table('invoices')->where('id', $key)->first();
                    $dataAll = DB::table('hawb_files')->where('id', $dataInvoice->hawb_hbl_no)->first();
                    $input['ups_id'] = null;
                    $input['aeropost_id'] = null;
                    $input['ccpack_id'] = null;
                    $input['cargo_id'] = null;
                    $input['house_file_id'] = $dataInvoice->hawb_hbl_no;
                }

                if ($input['courierorcargo'][$key] == 'UPS') {
                    $dataInvoice = DB::table('invoices')->where('id', $key)->first();
                    $dataAll = DB::table('ups_details')->where('id', $dataInvoice->ups_id)->first();
                    $input['house_file_id'] = null;
                    $input['aeropost_id'] = null;
                    $input['ccpack_id'] = null;
                    $input['cargo_id'] = null;
                    $input['ups_id'] = $dataInvoice->ups_id;
                }

                if ($input['courierorcargo'][$key] == 'Aeropost') {
                    $dataInvoice = DB::table('invoices')->where('id', $key)->first();
                    $dataAll = DB::table('aeropost')->where('id', $dataInvoice->aeropost_id)->first();
                    $input['house_file_id'] = null;
                    $input['ccpack_id'] = null;
                    $input['cargo_id'] = null;
                    $input['ups_id'] = null;
                    $input['aeropost_id'] = $dataInvoice->aeropost_id;
                }

                if ($input['courierorcargo'][$key] == 'CCpack') {
                    $dataInvoice = DB::table('invoices')->where('id', $key)->first();
                    $dataAll = DB::table('ccpack')->where('id', $dataInvoice->ccpack_id)->first();
                    $input['house_file_id'] = null;
                    $input['cargo_id'] = null;
                    $input['ups_id'] = null;
                    $input['aeropost_id'] = null;
                    $input['ccpack_id'] = $dataInvoice->ccpack_id;
                }

                $input['invoice_id'] = $key;
                $input['invoice_number'] = $dataInvoice->bill_no;

                $input['file_number'] = $dataAll->file_number;
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
                if (!empty($input['exchange_currency']) && $input['exchange_currency'] != $dataInvoice->currency) {
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
                } else {
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

                if ($input['courierorcargo'][$key] == 'Cargo') {
                    if ($value == $dataInvoice->balance_of)
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'display_notification_warehouse_invoice' => 1, 'notification_date_time' => date('Y-m-d H:i:s'), 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    else
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'display_notification_warehouse_invoice' => 1, 'notification_date_time' => date('Y-m-d H:i:s'), 'credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);

                    $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                    $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                    $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $dataInvoiceForPrint]);
                    $pdf_file = 'printCargoInvoice_' . $key . '.pdf';
                    $pdf_path = 'public/cargoInvoices/' . $pdf_file;
                    $pdf->save($pdf_path);
                }

                if ($input['courierorcargo'][$key] == 'Cargo Housefile') {
                    if ($value == $dataInvoice->balance_of)
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    else
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);
                }

                if ($input['courierorcargo'][$key] == 'UPS') {
                    if ($value == $dataInvoice->balance_of)
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    else
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);

                    $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                    $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                    $pdf = PDF::loadView('upsinvoices.printupsinvoice', ['invoice' => $dataInvoiceForPrint]);
                    $pdf_file = 'printUpsInvoice_' . $key . '.pdf';
                    $pdf_path = 'public/upsInvoices/' . $pdf_file;
                    $pdf->save($pdf_path);
                }

                if ($input['courierorcargo'][$key] == 'Aeropost') {
                    if ($value == $dataInvoice->balance_of)
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    else
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);

                    $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                    $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                    $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => $dataInvoiceForPrint]);
                    $pdf_file = 'printAeropostInvoice_' . $key . '.pdf';
                    $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
                    $pdf->save($pdf_path);
                }

                if ($input['courierorcargo'][$key] == 'CCpack') {
                    if ($value == $dataInvoice->balance_of)
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    else
                        DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->credits + $value, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $value)]);

                    $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                    $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                    $pdf = PDF::loadView('ccpackinvoices.printccpackinvoice', ['invoice' => $dataInvoiceForPrint]);
                    $pdf_file = 'printCCpackInvoice_' . $key . '.pdf';
                    $pdf_path = 'public/ccpackInvoices/' . $pdf_file;
                    $pdf->save($pdf_path);
                }

                // Store deposite activities
                $modelActivities = new Activities;
                $modelActivities->type = 'cashCreditClient';
                $modelActivities->related_id = $dataInvoice->bill_to;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = number_format($value, 2) . '-Invoice Payment Paid.' . $descForClientActivity;
                $modelActivities->cash_credit_flag = '1';
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                /* $dataclient = DB::table('clients')->where('id', $dataInvoice->bill_to)->first();
                $lastBalance = $dataclient->available_balance + $value;
                DB::table('clients')->where('id', $dataInvoice->bill_to)->update(['available_balance' => $lastBalance]); */
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
            $dataCargoInv = array();
            $dataCargoHouseFileInv = array();
            $dataUpsInv = array();
            $dataAeropostInv = array();
            $dataCcpackInv = array();
            if (isset($input['cargoInvoices']) && !empty($input['cargoInvoices'])) {
                $selectedInvoices = array_filter($input['cargoInvoices']);
                $id = $input['client'];
                $dataClient = DB::table('clients')->where('id', $id)->first();
                $dataCargoInv = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0')
                    ->whereNotNull('invoice_payments.cargo_id')
                    ->whereIn('invoices.id', $selectedInvoices)
                    ->get()
                    ->toArray();
            }

            if (isset($input['CargoHousefileInvoices']) && !empty($input['CargoHousefileInvoices'])) {
                $selectedInvoices = array_filter($input['CargoHousefileInvoices']);
                $id = $input['client'];
                $dataClient = DB::table('clients')->where('id', $id)->first();
                $dataCargoHouseFileInv = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0')
                    ->whereNotNull('invoice_payments.house_file_id')
                    ->whereIn('invoices.id', $selectedInvoices)
                    ->get()
                    ->toArray();
            }

            if (isset($input['upsInvoices']) && !empty($input['upsInvoices'])) {
                $selectedInvoices = array_filter($input['upsInvoices']);
                $id = $input['client'];
                $dataClient = DB::table('clients')->where('id', $id)->first();
                $dataUpsInv = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0')
                    ->whereNotNull('invoice_payments.ups_id')
                    ->whereIn('invoices.id', $selectedInvoices)
                    ->get()
                    ->toArray();
            }

            if (isset($input['aeropostInvoices']) && !empty($input['aeropostInvoices'])) {
                $selectedInvoices = array_filter($input['aeropostInvoices']);
                $id = $input['client'];
                $dataClient = DB::table('clients')->where('id', $id)->first();
                $dataAeropostInv = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0')
                    ->whereNotNull('invoice_payments.aeropost_id')
                    ->whereIn('invoices.id', $selectedInvoices)
                    ->get()
                    ->toArray();
            }

            if (isset($input['ccpackInvoices']) && !empty($input['ccpackInvoices'])) {
                $selectedInvoices = array_filter($input['ccpackInvoices']);
                $id = $input['client'];
                $dataClient = DB::table('clients')->where('id', $id)->first();
                $dataCcpackInv = DB::table('invoice_payments')
                    ->select(DB::raw('
                                IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                                IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                                IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                                invoices.currency as invoiceCurrency,
                                invoicec.code as invoiceCurrencyCode,
                                invoice_payments.created_at as paymentDate,
                                invoice_payments.id as paymentId,
                                invoice_payments.receipt_number,
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0')
                    ->whereNotNull('invoice_payments.ccpack_id')
                    ->whereIn('invoices.id', $selectedInvoices)
                    ->get()
                    ->toArray();
            }

            $allData = array_merge($dataCargoInv, $dataCargoHouseFileInv, $dataUpsInv, $dataAeropostInv, $dataCcpackInv);
            $totalHTG = 0;
            $totalUSD = 0;
            foreach ($allData as $key => $value) {
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
            $pdf = PDF::loadView('invoicepayments.printreceipt', ['data' => $allData, 'dataClient' => $dataClient, 'flag' => 'Client', 'total' => $allTotal, 'flagChangeLayout' => $flagChangeLayout, 'creditedAmount' => $creditedAmount], [], ['format' => $scale]);

            $pdf_file = 'payment_receipt.pdf';
            $pdf_path = 'public/paymentReceipt/' . $pdf_file;
            $pdf->save($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else {
            Session::flash('flash_message', 'Payment has been received successfully');
        }
    }

    public function getinvoicesofclient()
    {
        $clientId = $_POST['clientId'];
        $flagV = isset($_POST['flagV']) ? $_POST['flagV'] : '';
        if (!empty($flagV)) {
            if ($flagV == 'Cargo') {
                $dataInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)
                    ->where('payment_status', '!=', 'Paid')
                    //->where('type_flag', '!=', 'Local')
                    ->whereNotNull('cargo_id')->where('invoices.total', '!=', '0.00')->get();
            } else {
                $dataInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)->where('payment_status', '!=', 'Paid')->whereNotNull('ups_id')->where('invoices.total', '!=', '0.00')->get();
            }
        } else {
            $dataInvoice = DB::table('invoices')->where('bill_to', $clientId)
                ->where('deleted', 0)
                ->where('payment_status', '!=', 'Paid')
                //->where('type_flag', '!=', 'Local')
                ->whereNotNull('cargo_id')->where('invoices.total', '!=', '0.00')->get();
        }
        return view("invoicepayments.getclientinvoicesajax", ['dataInvoice' => $dataInvoice, 'flagModule' => 'Cargo']);
    }

    public function getinvoicesofclientineditmode()
    {
        $receiptNumber = $_POST['receiptNumber'];
        $invoicePayments = DB::table('invoice_payments')->select(DB::raw('GROUP_CONCAT(invoice_id) as invoice_number'))->where('receipt_number', $receiptNumber)->where('deleted', '0')->first();

        $dataInvoice = DB::table('invoices')->whereIn('id', explode(',', $invoicePayments->invoice_number))->get();
        foreach ($dataInvoice as $k => $v) {
            $paymentReceived = DB::table('invoice_payments')->where('invoice_id', $v->id)->where('receipt_number', $receiptNumber)->first();
            $dataInvoice[$k]->receivedAmount = $paymentReceived->amount;
            $dataInvoice[$k]->exchangeReceivedAmount = $paymentReceived->amount;
            $dataInvoice[$k]->invoicePaymentId = $paymentReceived->id;
        }


        return view("invoicepayments.getclientinvoicesajaxineditmode", ['dataInvoice' => $dataInvoice, 'flagModule' => 'Cargo']);
    }



    public function getcargoandcourierinvoicesofclient()
    {
        $clientId = $_POST['clientId'];
        $flagV = isset($_POST['flagV']) ? $_POST['flagV'] : '';
        $dataCargoInvoice = DB::table('invoices')
            ->where('bill_to', $clientId)
            ->where('deleted', 0)
            ->where('payment_status', '!=', 'Paid')
            //->where('type_flag', '!=', 'Local')
            ->whereNotNull('cargo_id')->whereNull('housefile_module')->where('invoices.total', '!=', '0.00')->get()->toArray();
        $dataHouseFileInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)
            ->where('payment_status', '!=', 'Paid')
            //->where('type_flag', '!=', 'Local')
            ->where('housefile_module', 'cargo')->where('invoices.total', '!=', '0.00')->get()->toArray();
        $dataCourierInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)->where('payment_status', '!=', 'Paid')->whereNotNull('ups_id')->where('invoices.total', '!=', '0.00')->get()->toArray();
        $dataAeropostInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)->where('payment_status', '!=', 'Paid')->whereNotNull('aeropost_id')->where('invoices.total', '!=', '0.00')->get()->toArray();
        $dataCcpackInvoice = DB::table('invoices')->where('bill_to', $clientId)->where('deleted', 0)->where('payment_status', '!=', 'Paid')->whereNotNull('ccpack_id')->where('invoices.total', '!=', '0.00')->get()->toArray();
        $allInvoices = array_merge($dataCargoInvoice, $dataHouseFileInvoice, $dataCourierInvoice, $dataAeropostInvoice, $dataCcpackInvoice);

        foreach ($allInvoices as $k => $v) {
            if (isset($v->cargo_id) && !empty($v->cargo_id) && empty($v->housefile_module)) {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (Cargo)';
                $allInvoices[$k]->typeFlag = 'Cargo';
            }
            if (isset($v->hawb_hbl_no) && !empty($v->hawb_hbl_no) && !empty($v->housefile_module) && $v->housefile_module == 'cargo') {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (Cargo HouseFile)';
                $allInvoices[$k]->typeFlag = 'Cargo Housefile';
            }
            if (isset($v->ups_id) && !empty($v->ups_id) && empty($v->housefile_module)) {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (UPS)';
                $allInvoices[$k]->typeFlag = 'UPS';
            }
            if (isset($v->ups_id) && !empty($v->ups_id) && !empty($v->housefile_module) && $v->housefile_module == 'ups') {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (UPS HouseFile)';
                $allInvoices[$k]->typeFlag = 'UPS Housefile';
            }
            if (isset($v->aeropost_id) && !empty($v->aeropost_id) && empty($v->housefile_module)) {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (Aeropost)';
                $allInvoices[$k]->typeFlag = 'Aeropost';
            }
            if (isset($v->aeropost_id) && !empty($v->aeropost_id) && !empty($v->housefile_module) && $v->housefile_module == 'aeropost') {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (Aeropost HouseFile)';
                $allInvoices[$k]->typeFlag = 'Aeropost Housefile';
            }
            if (isset($v->ccpack_id) && !empty($v->ccpack_id) && empty($v->housefile_module)) {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (CCpack)';
                $allInvoices[$k]->typeFlag = 'CCpack';
            }
            if (isset($v->ccpack_id) && !empty($v->ccpack_id) && !empty($v->housefile_module) && $v->housefile_module == 'aeropost') {
                $allInvoices[$k]->bill_no = $v->bill_no . ' (CCPack HouseFile)';
                $allInvoices[$k]->typeFlag = 'CCpack Housefile';
            }
        }

        return view("invoicepayments.getclientinvoicesajax", ['dataInvoice' => $allInvoices, 'flagModule' => 'Cargo']);
    }

    public function getselectedinvoicedata()
    {
        $invoiceId = $_POST['invoiceId'];
        /* if($_POST['flagModule'] == 'housefile')
        {
            $dataHouseFileInvoice = DB::table('invoices')->where('id',$invoiceId)->first();
            if($dataHouseFileInvoice->date != date('Y-m-d'))
            {
                $dataBilling = DB::table('billing_items')->where('item_code','SCC')->first();
                $storageChargeData = DB::table('storage_charges')->where('measure','M')->first();

                $model = HawbFiles::find($dataHouseFileInvoice->hawb_hbl_no);

                $fromDate = $model->shipment_received_date;
                $toDate = date('Y-m-d');

                $now = time();
                $your_date = strtotime($fromDate);
                $datediff = strtotime($toDate) - $your_date;
                $dayDifference = round($datediff / (60 * 60 * 24));

                $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id',$dataHouseFileInvoice->hawb_hbl_no)->first();
                $measureWeight = $modelCargoPackage->measure_weight;
                if(empty($measureWeight))
                    $measureWeight = '0.00';
                $measureVolume = $modelCargoPackage->measure_volume;
                if(empty($measureVolume))
                    $measureVolume = '0.00';

                $storageChargeDataWeight = DB::table('storage_charges')->where('measure',strtoupper($measureWeight))->first();
                $chageDaysWeight = $dayDifference-$storageChargeDataWeight->grace_period;
                $chargeWeightPerKgOrPound = $storageChargeDataWeight->charge;
                if($chageDaysWeight > 0)
                    $totalChargeWeight = $chargeWeightPerKgOrPound * $modelCargoPackage->pweight * $chageDaysWeight;
                else
                    $totalChargeWeight = '0.00';

                $storageChargeDataVolume = DB::table('storage_charges')->where('measure',strtoupper($measureVolume))->first();
                
                $chageDaysVolume = $dayDifference-(!empty($storageChargeDataVolume) ? $storageChargeDataVolume->grace_period : '0');
                $chargeVolumePerMeterOrFeet = !empty($storageChargeDataVolume) ? $storageChargeDataVolume->charge : '0';
                if($chageDaysVolume > 0)
                    $totalChargeVolume = $chargeVolumePerMeterOrFeet * $modelCargoPackage->pvolume * $chageDaysVolume;
                else
                    $totalChargeVolume = '0.00';

                if($totalChargeVolume > $totalChargeWeight)
                {
                    $finalChargeDays = $chageDaysVolume;
                    $finalCharge = $chargeVolumePerMeterOrFeet * $modelCargoPackage->pvolume;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : '.date('d-m-Y',strtotime($fromDate)).' - '.date('d-m-Y',strtotime($toDate)).'('.$dayDifference.' days - '.$storageChargeDataVolume->grace_period.' Grace days = '.$finalChargeDays.' Days)';
                }else if($totalChargeWeight > $totalChargeVolume){
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $modelCargoPackage->pweight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : '.date('d-m-Y',strtotime($fromDate)).' - '.date('d-m-Y',strtotime($toDate)).'('.$dayDifference.' days - '.$storageChargeDataWeight->grace_period.' Grace days = '.$finalChargeDays.' Days)';
                }else if($totalChargeWeight == $totalChargeVolume){
                    $finalChargeDays = $chageDaysWeight;
                    $finalCharge = $chargeWeightPerKgOrPound * $modelCargoPackage->pweight;
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : Duration : '.date('d-m-Y',strtotime($fromDate)).' - '.date('d-m-Y',strtotime($toDate)).'('.$dayDifference.' days - '.$storageChargeDataWeight->grace_period.' Grace days = '.$finalChargeDays.' Days)';
                }else{
                    $finalChargeDays = '0.00';
                    $finalCharge = '0.00';
                    $totalCharge = $finalChargeDays * $finalCharge;
                    $desc = 'Storage Charge : No Charge (In Grace Period)';
                }

                $dataInvoiceItems['quantity'] = $finalChargeDays;
                $dataInvoiceItems['unit_price'] = $finalCharge;
                $dataInvoiceItems['fees_name_desc'] = $desc;
                $dataInvoiceItems['total_of_items'] = $totalCharge;

                $dataInvoice['date'] = date('Y-m-d');
                $dataInvoice['sub_total'] = $dataInvoiceItems['total_of_items'];
                $dataInvoice['total'] = $dataInvoiceItems['total_of_items'];
                $dataInvoice['balance_of'] = $dataInvoiceItems['total_of_items'];
                Invoices::where('id', $invoiceId)->update($dataInvoice);
                $dataInvoices = Invoices::find($invoiceId);
                

                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                $dataInvoiceItems['fees_name'] = $dataBilling->id;
                $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                InvoiceItemDetails::where('invoice_id', $dataInvoices->id)->update($dataInvoiceItems);
        
                $invoiceData = DB::table('invoices')->where('id',$dataInvoices->id)->first();
                $pdf = PDF::loadView('housefile-invoices.print',['invoice'=>(array)$invoiceData]);
                $pdf_file = 'printInvoice_'.$dataInvoices->id.'.pdf';
                $pdf_path = 'public/houseFileInvoices/'.$pdf_file;
                $pdf->save($pdf_path);
            }
        } */
        $dataInvoice = DB::table('invoices')->where('id', $invoiceId)->get();
        return view("invoicepayments.getclientinvoicesajax", ['dataInvoice' => $dataInvoice, 'flagModule' => 'Cargo']);
    }

    public function getcourierorcargodata()
    {
        $flag = $_POST['flag'];
        if ($flag == 'Cargo') {
            $invoiceArray = DB::table('invoices')->whereNotNull('cargo_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('deleted', 0)
                ->get()
                ->toArray();
        } else {
            $invoiceArray = DB::table('invoices')->whereNotNull('ups_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('deleted', 0)
                ->get()
                ->toArray();
        }


        $dt = '<option value="">-- Select</option>';
        foreach ($invoiceArray as $key => $value) {
            $dt .=  '<option value="' . $value->id . '">' . $value->bill_no . '</option>';
        }
        return $dt;
    }

    public function getcurrencyratesection()
    {
        $id = $_POST['id'];
        $selectedInvoiceIds = $_POST['selectedInvoiceIds'];
        $explodedselectedInvoiceIds =  explode(',', $selectedInvoiceIds);

        $dataInvoice = DB::table('invoices')->whereIn('id', $explodedselectedInvoiceIds)->get();
        foreach ($dataInvoice as $key => $value) {
            $currenies[] = $value->currency;
        }
        //pre($currenies,1);

        $currencyExchangeData = DB::table('currency_exchange')
            ->select('currency_exchange.*')
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->whereIn('from_currency', $currenies)
            ->where('to_currency', $id)
            ->get();

        // pre($currencyExchangeData);


        return view("invoicepayments.getcurrencyratesecion", ['currencyExchangeData' => $currencyExchangeData]);
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
    public function edit(InvoicePayments $invoicePayments, $receiptNumber, $flagModule = null)
    {
        $invoiceArray = array();
        $allUsers = array();
        $model = new InvoicePayments;
        $checkViaInvoiceOrBP = DB::table('invoice_payments')->where('receipt_number', $receiptNumber)->where('deleted', '0')->count();
        $dataPayment = DB::table('invoice_payments')->where('receipt_number', $receiptNumber)->where('deleted', '0')->first();
        $receivedVia = '';
        $amountReceived = DB::table('invoice_payments')->where('receipt_number', $receiptNumber)->where('deleted', '0')->sum('exchange_amount');
        if ($checkViaInvoiceOrBP > 1) {
            $receivedVia = 'billingParty';
            $model->client = $dataPayment->client;
            $invoceId = '';
            $billingParty = $model->client;
            $allUsers = DB::table('clients')
                ->select(['id', 'company_name'])
                ->where('id', $billingParty)
                ->pluck('company_name', 'id');
        } else {
            $receivedVia = 'invoice';
            $model->invoice_id = $dataPayment->invoice_id;
            $invoceId = $model->invoice_id;
            $billingParty = '';
            $invoiceArray = DB::table('invoices')
                ->where('id', $invoceId)
                ->get()->pluck('bill_no', 'id');
        }
        $model->payment_via = $dataPayment->payment_via;
        $model->payment_via_note = $dataPayment->payment_via_note;

        if (!empty($dataPayment->exchange_currency)) {
            $exchageOrNot = '1';
            $exchageCurrency = $dataPayment->exchange_currency;
        } else {
            $exchageOrNot = '0';
            $exchageCurrency = '';
        }

        $paymentVia = Config::get('app.paymentMethod');

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');

        return view("invoicepayments.edit", ['model' => $model, 'allUsers' => $allUsers, 'invoiceArray' => $invoiceArray, 'paymentVia' => $paymentVia, 'currency' => $currency, 'flagModule' => $flagModule, 'invoceId' => $invoceId, 'billingParty' => $billingParty, 'receiptNumber' => $receiptNumber, 'amountReceived' => $amountReceived, 'exchageOrNot' => $exchageOrNot, 'exchageCurrency' => $exchageCurrency]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InvoicePayments  $invoicePayments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoicePayments $invoicePayments, $receiptNumber)
    {
        $input = $request->all();
        if (!empty($input['client']) || !empty($input['invoice_id'])) {
            foreach ($input['payment'] as $k => $v) {
                $exploadedKey = explode('-', $k);
                $key = $exploadedKey[1];
                $paymentId = $exploadedKey[0];
                if (empty($v)) {
                    InvoicePayments::where('id', $paymentId)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);
                    $dataInvoice = Invoices::where('id', $key)->first();
                    //$credit = abs($dataInvoice->credits - ($dataInvoice->total - $v->amount));
                    $invoicesPayment = InvoicePayments::where('id', $paymentId)->first();
                    $credit = abs($dataInvoice->credits - $invoicesPayment->amount);
                    $balanceOf = $dataInvoice->total - $credit;
                    if ($credit == '0' || $credit == '0.00')
                        $rStatus = 'Pending';
                    else
                        $rStatus = 'Partial';
                    Invoices::where('id', $key)->update(['payment_status' => $rStatus, 'credits' => $credit, 'balance_of' => $balanceOf]);
                }
            }

            $input['payment'] = array_filter($input['payment']);
            $exchageValues = array_filter($input['exchange_amount']);
            foreach ($input['payment'] as $key => $value) {
                $exploadedKey = explode('-', $key);
                $paymentId = $exploadedKey[0];
                $key = $exploadedKey[1];

                $value = str_replace(',', '', $value);
                $dataInvoice = DB::table('invoices')->where('id', $key)->first();

                $input['amount'] =  str_replace(',', '', $value);
                $input['exchange_amount'] = str_replace(',', '', $exchageValues[$paymentId . '-' . $key]);
                $input['exchange_currency'] = $input['exchange_currency'];
                $input['updated_at'] = gmdate("Y-m-d H:i:s");
                if (empty($input['client']))
                    $input['client'] = $dataInvoice->bill_to;
                $input['invoice_number'] = $dataInvoice->bill_no;
                $input['invoice_id'] = $key;

                $invoicesPayment = InvoicePayments::where('id', $paymentId)->first();
                $aReceivedPayment = $invoicesPayment->amount;

                $model = InvoicePayments::updateOrCreate(['id' => $paymentId], $input);

                $credit = $dataInvoice->credits - ($aReceivedPayment - $value);
                $balanceOf = $dataInvoice->total - $credit;
                if ($dataInvoice->total == $credit)
                    $status = 'Paid';
                else
                    $status = 'Partial';

                DB::table('invoices')->where('id', $key)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $credit, 'payment_status' => $status, 'balance_of' => $balanceOf, 'payment_received_on' => date('Y-m-d'), 'payment_received_by' => auth()->user()->id]);

                $dataInvoiceForPrint = DB::table('invoices')->where('id', $key)->first();
                $dataInvoiceForPrint = (array) $dataInvoiceForPrint;
                $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $dataInvoiceForPrint]);
                $pdf_file = 'printCargoInvoice_' . $key . '.pdf';
                $pdf_path = 'public/cargoInvoices/' . $pdf_file;
                $pdf->save($pdf_path);

                // Store deposite activities
                /* $modelActivities = new Activities;
                $modelActivities->type = 'cashCreditClient';
                $modelActivities->related_id = $dataInvoice->bill_to;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = number_format($value, 2) . '-Invoice Payment Paid.';
                $modelActivities->cash_credit_flag = '2';
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save(); */

                /* $dataclient = DB::table('clients')->where('id', $dataInvoice->bill_to)->first();
                $lastBalance = $dataclient->available_balance + $value;
                DB::table('clients')->where('id', $dataInvoice->bill_to)->update(['available_balance' => $lastBalance]); */
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
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.invoice_id', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0');
                /* if ($input['flagModule'] == 'housefile')
                    $data = $data->whereNotNull('invoice_payments.house_file_id');
                else
                    $data = $data->whereNotNull('invoice_payments.cargo_id'); */
                $data = $data->orderBy('invoice_payments.id', 'DESC')
                    ->get();
            } else {
                $selectedInvoices = array();
                foreach ($input['payment'] as $k => $v) {
                    $exploadedKey = explode('-', $k);
                    $selectedInvoices[] = $exploadedKey[1];
                }
                //$selectedInvoices = array_filter(array_keys($allKeys));
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
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                    ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                    ->where('invoice_payments.client', $id)
                    ->where('invoice_payments.receipt_number', $receiptNumber)
                    ->where('invoice_payments.deleted', '0');
                /* if ($input['flagModule'] == 'housefile')
                    $data = $data->whereNotNull('invoice_payments.house_file_id');
                else
                    $data = $data->whereNotNull('invoice_payments.cargo_id'); */

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
     * Remove the specified resource from storage.
     *
     * @param  \App\InvoicePayments  $invoicePayments
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoicePayments $invoicePayments, $id = null, $receiptNumber = null)
    {
        InvoicePayments::where('receipt_number', $receiptNumber)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'deleted_by' => auth()->user()->id]);
        //InvoicePayments::where('invoice_id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);
        $invoices = InvoicePayments::where('receipt_number', $receiptNumber)->get();
        foreach ($invoices as $k => $v) {
            $dataInvoice = Invoices::where('id', $v->invoice_id)->first();
            //$credit = abs($dataInvoice->credits - ($dataInvoice->total - $v->amount));
            $credit = abs($dataInvoice->credits - $v->amount);
            $balanceOf = $dataInvoice->total - $credit;
            Invoices::where('id', $v->invoice_id)->update(['payment_status' => 'Pending', 'credits' => $credit, 'balance_of' => $balanceOf]);

            $type = '';
            if (!empty($dataInvoice->housefile_module) && $dataInvoice->housefile_module == 'cargo') {
                $type = 'houseFile';
                $relatedID = $dataInvoice->hawb_hbl_no;
            } else if (empty($dataInvoice->housefile_module) && !empty($dataInvoice->cargo_id)) {
                $type = 'cargo';
                $relatedID = $dataInvoice->cargo_id;
            } else if (!empty($dataInvoice->ups_id)) {
                $type = 'ups';
                $relatedID = $dataInvoice->ups_id;
            } else if (!empty($dataInvoice->ups_master_id)) {
                $type = 'upsMaster';
                $relatedID = $dataInvoice->ups_master_id;
            } else if (!empty($dataInvoice->aeropost_id)) {
                $type = 'aeropost';
                $relatedID = $dataInvoice->aeropost_id;
            } else if (!empty($dataInvoice->aeropost_master_id)) {
                $type = 'aeropostMaster';
                $relatedID = $dataInvoice->aeropost_master_id;
            } else if (!empty($dataInvoice->ccpack_id)) {
                $type = 'ccpack';
                $relatedID = $dataInvoice->ccpack_id;
            } else if (!empty($dataInvoice->ccpack_master_id)) {
                $type = 'ccpackMaster';
                $relatedID = $dataInvoice->ccpack_master_id;
            }

            if (!empty($v->exchange_currency) && $v->exchange_currency != $dataInvoice->currency)
                $paymentCurrency = $v->exchange_currency;
            else
                $paymentCurrency = $dataInvoice->currency;

            $dataCurrency = Currency::getData($paymentCurrency);

            // Store payment deleted activity on file level
            $modelActivities = new Activities;
            $modelActivities->type = $type;
            $modelActivities->related_id = $relatedID;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $dataInvoice->bill_no . " Payment Deleted " . number_format($v->exchange_amount, 2) . " (" . $dataCurrency->code . ")";
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
    }

    public function invoicepaymentslisting($flag = null)
    {
        return view("invoicepayments.index", ['flag' => $flag]);
    }

    public function printreceiptofinvoicepayment($id = null, $flag = null, $flagmodule = null)
    {
        if ($flagmodule == 'cargo')
            $moduleId = 'cargo_id';
        else if ($flagmodule == 'housefile')
            $moduleId = 'house_file_id';
        else if ($flagmodule == 'ups')
            $moduleId = 'ups_id';
        else if ($flagmodule == 'aeropost')
            $moduleId = 'aeropost_id';
        else if ($flagmodule == 'ccpack')
            $moduleId = 'ccpack_id';
        if ($flag == 'invoice') {
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
                            invoice_payments.payment_via,
                            invoice_payments.payment_via_note,
                            invoices.awb_no as awb_no,
                            invoices.bill_no as bill_no
                    '))
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                ->where('invoice_payments.invoice_id', $id)
                ->where('invoice_payments.deleted', '0')
                ->whereNotNull('invoice_payments.' . $moduleId)
                ->orderBy('invoice_payments.id', 'DESC')
                ->get();
        } else {
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
                                invoice_payments.payment_via,
                                invoice_payments.payment_via_note,
                                invoices.awb_no as awb_no,
                                invoices.bill_no as bill_no
                        '))
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                ->where('invoice_payments.client', $id)
                ->where('invoice_payments.deleted', '0')
                ->whereNotNull('invoice_payments.' . $moduleId)
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
        if (count($data) >= 2) {
            $flagChangeLayout = 1;
        }

        $scale = [76, 236];
        $pdf = PDF::loadView('invoicepayments.printreceipt', ['data' => $data, 'dataClient' => $dataClient, 'flag' => $flag, 'total' => $allTotal, 'flagChangeLayout' => $flagChangeLayout, 'creditedAmount' => ''], [], ['format' => $scale]);



        $pdf_file = 'payment_receipt.pdf';
        $pdf_path = 'public/paymentReceipt/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function printsinglereceipt($receiptNumber = null, $flag = null, $flagmodule = null)
    {
        if ($flagmodule == 'cargo')
            $moduleId = 'cargo_id';
        else if ($flagmodule == 'housefile')
            $moduleId = 'house_file_id';
        else if ($flagmodule == 'ups')
            $moduleId = 'ups_id';
        else if ($flagmodule == 'aeropost')
            $moduleId = 'aeropost_id';
        else if ($flagmodule == 'ccpack')
            $moduleId = 'ccpack_id';
        $creditedAmount = 0;
        if ($flag == 'invoice') {
            $dataInvoice = DB::table('invoice_payments')->where('receipt_number', $receiptNumber)->first();
            $creditedAmount = $dataInvoice->credited_amount;
            $dataClient = DB::table('clients')->where('id', $dataInvoice->client)->first();
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
                            invoice_payments.payment_via,
                            invoice_payments.payment_via_note,
                            invoices.awb_no as awb_no,
                            invoices.bill_no as bill_no
                    '))
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
                ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
                ->where('invoice_payments.receipt_number', $receiptNumber)
                ->where('invoice_payments.deleted', '0')
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
        $pdf = PDF::loadView('invoicepayments.printreceipt', ['data' => $data, 'dataClient' => $dataClient, 'flag' => $flag, 'total' => $allTotal, 'flagChangeLayout' => $flagChangeLayout, 'creditedAmount' => $creditedAmount], [], ['format' => $scale]);



        $pdf_file = 'payment_receipt.pdf';
        $pdf_path = 'public/paymentReceipt/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function invoicepaymentoutsidefiltering()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $filterBy = !empty($_POST['filterBy']) ? $_POST['filterBy'] : '';
        $flag = $_POST['flag'];

        if ($flag == 'cargo')
            $moduleId = 'cargo_id';
        else if ($flag == 'ups')
            $moduleId = 'ups_id';
        else if ($flag == 'aeropost')
            $moduleId = 'aeropost_id';
        else if ($flag == 'ccpack')
            $moduleId = 'ccpack_id';

        $data = DB::table('invoice_payments')
            ->select(
                'clients.company_name',
                'clients.id',
                DB::raw('GROUP_CONCAT(invoices.bill_no) as invoice_number'),
                'invoice_payments.file_number',
                'invoice_payments.created_at',
                'invoice_payments.invoice_id',
                'invoice_payments.receipt_number',
                'invoices.consignee_address',
                'invoices.total',
                DB::raw('SUM(invoice_payments.exchange_amount) AS exchange_amount'),
                DB::raw('
                            IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as paymentCurrencyCode
                            ')
            )
            ->join('clients', 'clients.id', '=', 'invoice_payments.client')
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
            ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
            ->where('invoice_payments.deleted', 0)
            ->groupBy('invoice_payments.receipt_number')
            ->orderBy('invoice_payments.created_at', 'DESC');
        if ($flag == 'cargo') {
            $data = $data->where(function ($query) {
                $query->whereNotNull('invoice_payments.cargo_id')
                    ->orWhereNotNull('invoice_payments.house_file_id');
            });
        } else {
            $data = $data->whereNotNull('invoice_payments.' . $moduleId);
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $data = $data->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
        }
        $data = $data->get();

        return view("invoicepayments.allinvoicepaymentoutsidefilter", ['data' => $data, 'fromDate' => $fromDate, 'toDate' => $toDate, 'flag' => $flag, 'filterBy' => $filterBy]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $req = $request->all();
        $flag = $req['flag'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';



        if ($flag == 'cargo') {
            $permissionInvoicePaymentDelete = User::checkPermission(['delete_cargo_invoice_payments'], '', auth()->user()->id);
            $permissionModifyPayment = User::checkPermission(['modify_cargo_invoice_payments'], '', auth()->user()->id);
            $moduleId = 'cargo_id';
            $moduleId1 = 'house_file_id';
        }
        if ($flag == 'ups') {
            $permissionInvoicePaymentDelete = User::checkPermission(['delete_courier_invoice_payments'], '', auth()->user()->id);
            $permissionModifyPayment = User::checkPermission(['modify_courier_invoice_payments'], '', auth()->user()->id);
            $moduleId = 'ups_id';
            $moduleId1 = 'ups_master_id';
        }
        if ($flag == 'aeropost') {
            $permissionInvoicePaymentDelete = User::checkPermission(['delete_aeropost_invoice_payments'], '', auth()->user()->id);
            $permissionModifyPayment = User::checkPermission(['modify_aeropost_invoice_payments'], '', auth()->user()->id);
            $moduleId = 'aeropost_id';
            $moduleId1 = 'aeropost_master_id';
        }
        if ($flag == 'ccpack') {
            $permissionInvoicePaymentDelete = User::checkPermission(['delete_ccpack_invoice_payments'], '', auth()->user()->id);
            $permissionModifyPayment = User::checkPermission(['modify_ccpack_invoice_payments'], '', auth()->user()->id);
            $moduleId = 'ccpack_id';
            $moduleId1 = 'ccpack_master_id';
        }



        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['invoice_payments.receipt_number', 'invoice_payments.receipt_number', 'invoice_payments.created_at', 'invoice_payments.invoice_number',  'clients.company_name', '', '', '', ''];


        $total = $data = DB::table('invoice_payments')
            ->select(DB::raw('count(DISTINCT invoice_payments.receipt_number) as total'))
            ->join('clients', 'clients.id', '=', 'invoice_payments.client')
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
            ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
            ->leftJoin('users', 'users.id', '=', 'invoice_payments.payment_accepted_by')
            //->where('invoice_payments.deleted', 0)
            //->groupBy('invoice_payments.receipt_number')
            ->orderBy('invoice_payments.created_at', 'DESC');
        $total = $total->where(function ($query) use ($moduleId, $moduleId1) {
            $query->whereNotNull('invoice_payments.' . $moduleId)
                ->orWhereNotNull('invoice_payments.' . $moduleId1);
        });
         /* if ($flag == 'cargo') {
            $total = $total->where(function ($query) {
                $query->whereNotNull('invoice_payments.cargo_id')
                    ->orWhereNotNull('invoice_payments.house_file_id');
            });
        } else {
            $total = $total->whereNotNull('invoice_payments.' . $moduleId);
        } */
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
        }

        $total = $total->first();
        $totalfiltered = $total->total;

        /* $total = $total->get();
        $totalfiltered = count($total); */

        $query = DB::table('invoice_payments')
            ->select(
                'clients.company_name',
                'clients.id',
                'users.name as collectedBy',
                DB::raw('GROUP_CONCAT(invoices.bill_no) as invoice_number'),
                'invoice_payments.deleted',
                'invoice_payments.cargo_id',
                'invoice_payments.ups_id',
                'invoice_payments.ccpack_id',
                'invoice_payments.aeropost_id',
                'invoice_payments.house_file_id',
                'invoice_payments.file_number',
                'invoice_payments.created_at',
                'invoice_payments.invoice_id',
                'invoice_payments.receipt_number',
                'invoices.consignee_address',
                'invoices.total',
                DB::raw('SUM(invoice_payments.exchange_amount) AS exchange_amount'),
                DB::raw('
                        IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as paymentCurrencyCode
                        ')
            )
            ->join('clients', 'clients.id', '=', 'invoice_payments.client')
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
            ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
            ->leftJoin('users', 'users.id', '=', 'invoice_payments.payment_accepted_by')
            ->where('invoice_payments.deleted', 0)
            ->groupBy('invoice_payments.receipt_number')
            ->orderBy('invoice_payments.created_at', 'DESC');
        $query = $query->where(function ($query) use ($moduleId, $moduleId1) {
            $query->whereNotNull('invoice_payments.' . $moduleId)
                ->orWhereNotNull('invoice_payments.' . $moduleId1);
        });
        /* if ($flag == 'cargo') {
            $query = $query->where(function ($query) {
                $query->whereNotNull('invoice_payments.cargo_id')
                    ->orWhereNotNull('invoice_payments.house_file_id');
            });
        } else {
            $query = $query->whereNotNull('invoice_payments.' . $moduleId);
        } */
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
        }

        $filteredq = DB::table('invoice_payments')
            ->join('clients', 'clients.id', '=', 'invoice_payments.client')
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
            ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
            ->leftJoin('users', 'users.id', '=', 'invoice_payments.payment_accepted_by')
            //->where('invoice_payments.deleted', 0)
            //->groupBy('invoice_payments.receipt_number')
            ->orderBy('invoice_payments.created_at', 'DESC');
        $filteredq = $filteredq->where(function ($query) use ($moduleId, $moduleId1) {
            $query->whereNotNull('invoice_payments.' . $moduleId)
                ->orWhereNotNull('invoice_payments.' . $moduleId1);
        });
        /* if ($flag == 'cargo') {
            $filteredq = $filteredq->where(function ($query) {
                $query->whereNotNull('invoice_payments.cargo_id')
                    ->orWhereNotNull('invoice_payments.house_file_id');
            });
        } else {
            $filteredq = $filteredq->whereNotNull('invoice_payments.' . $moduleId);
        } */
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('invoice_payments.receipt_number', 'like', '%' . $search . '%')
                    ->orWhere('invoice_payments.invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('clients.company_name', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('invoice_payments.receipt_number', 'like', '%' . $search . '%')
                    ->orWhere('invoice_payments.invoice_number', 'like', '%' . $search . '%')
                    ->orWhere('clients.company_name', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(DISTINCT invoice_payments.receipt_number) as total')->first();
            $totalfiltered = $filteredq->total;

            /* $filteredq = $filteredq->get();
            $totalfiltered = count($filteredq); */
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();


        $data = [];
        foreach ($query as $key => $value) {


            $action = '<div class="dropdown">';
            $action .= '<a title="View & Print"  target="_blank" href="' . route('printsinglereceipt', [$value->receipt_number, 'invoice', $flag]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete =  route('deleteinvoicepayment', [$value->invoice_id, $value->receipt_number]);
            $edit =  route('editinvoicepayment', [$value->receipt_number, $flag]);

            if ($permissionModifyPayment && $value->deleted == '0') {
                $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
            }
            if ($permissionInvoicePaymentDelete && $value->deleted == '0') {
                $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
            }

            $action .= '</div>';

            $data[] = [$value->receipt_number, $value->receipt_number, date('d-m-Y', strtotime($value->created_at)), $value->invoice_number, $value->company_name, $value->paymentCurrencyCode, number_format($value->exchange_amount, 2), !empty($value->collectedBy) ? $value->collectedBy : '-', $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function checkoperations()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkReceipt') {
            $receiptNumber = $_POST['receiptNumber'];
            return InvoicePayments::checkReceipt($receiptNumber);
        }
    }

    public function getclientdataforcredit()
    {
        $clientId = $_POST['clientId'];
        $clientData = DB::table('clients')->join('currency', 'currency.id', '=', 'clients.currency')->where('clients.id', $clientId)->first();
        $finalData = array();
        $finalData['clientCurrencyCode'] = $clientData->code;
        if ($clientData->cash_credit == 'Cash') {
            $finalData['status'] = '0';
        } else {
            $finalData['status'] = '1';
            if ($clientData->code == 'USD') {
                $exchangeRateOfUsdToHTG = DB::table('currency_exchange')
                    ->select(['currency_exchange.exchange_value as exchangeRate'])
                    ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
                    ->where('currency.code', 'USD')
                    ->first();

                $totalHTG = $clientData->available_balance * $exchangeRateOfUsdToHTG->exchangeRate;
                $finalData['amountInUSD'] = number_format($clientData->available_balance, 2);
                $finalData['amountInHTG'] = number_format($totalHTG, 2);
            } else {
                $exchangeRateOfHTGToUsd = DB::table('currency_exchange')
                    ->select(['currency_exchange.exchange_value as exchangeRate'])
                    ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
                    ->where('currency.code', 'HTG')
                    ->first();

                $totalUSD = $clientData->available_balance * $exchangeRateOfHTGToUsd->exchangeRate;
                $finalData['amountInHTG'] = number_format($clientData->available_balance, 2);
                $finalData['amountInUSD'] = number_format($totalUSD, 2);
            }
        }
        return json_encode($finalData);
    }

    public function getclientdataforcreditfrominvoice()
    {
        $invoiceId = $_POST['invoiceId'];
        $dataInvoice = DB::table('invoices')->where('id', $invoiceId)->first();
        $clientId = $dataInvoice->bill_to;;
        $clientData = DB::table('clients')->join('currency', 'currency.id', '=', 'clients.currency')->where('clients.id', $clientId)->first();
        $finalData = array();
        $finalData['clientCurrencyCode'] = $clientData->code;
        if ($clientData->cash_credit == 'Cash') {
            $finalData['status'] = '0';
        } else {
            $finalData['status'] = '1';
            if ($clientData->code == 'USD') {
                $exchangeRateOfUsdToHTG = DB::table('currency_exchange')
                    ->select(['currency_exchange.exchange_value as exchangeRate'])
                    ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
                    ->where('currency.code', 'USD')
                    ->first();

                $totalHTG = $clientData->available_balance * $exchangeRateOfUsdToHTG->exchangeRate;
                $finalData['amountInUSD'] = number_format($clientData->available_balance, 2);
                $finalData['amountInHTG'] = number_format($totalHTG, 2);
            } else {
                $exchangeRateOfHTGToUsd = DB::table('currency_exchange')
                    ->select(['currency_exchange.exchange_value as exchangeRate'])
                    ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
                    ->where('currency.code', 'HTG')
                    ->first();

                $totalUSD = $clientData->available_balance * $exchangeRateOfHTGToUsd->exchangeRate;
                $finalData['amountInHTG'] = number_format($clientData->available_balance, 2);
                $finalData['amountInUSD'] = number_format($totalUSD, 2);
            }
        }
        return json_encode($finalData);
    }
}
