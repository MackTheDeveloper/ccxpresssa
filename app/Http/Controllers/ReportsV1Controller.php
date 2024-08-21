<?php

namespace App\Http\Controllers;


use App\Exports\ArAging;
use App\Exports\ExportFromArray;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use Carbon\Carbon;


class ReportsV1Controller extends Controller
{
    public function showStatementOfAccount()
    {
        return view('reports/statement-of-accounts');
    }

    public function exportStatementOfAccount($fromDate = null, $toDate = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        // Get all billing parties
        $cargoAllBillingParties = DB::table('invoices')->select(['bill_to'])->where('invoices.deleted', 0)
            ->where('invoices.total', '!=', '0.00')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->whereNull('flag_invoice')
            ->whereBetween('invoices.date', array($fromDate, $toDate))
            ->get()->toArray();

        $data[] = ['CHATELAIN CARGO SERVICES S.A'];
        $data[] = ['Open Invoices'];
        $data[] = [$fromDate . ' -- ' . $toDate];
        $data[] = [''];
        $data[] = ['', 'Date', 'Transaction Type', 'Num', 'Terms', 'Open Balance', 'Foreign Amount'];

        // GET PENDING INVOICES
        $query = DB::table('invoices')
            ->select(DB::raw('clients.company_name as billingParty,invoices.bill_to,invoices.date,"Invoice" as transactionType,invoices.bill_no as num,invoices.type_flag as terms,invoices.balance_of as openBalance,invoices.currency'))
            /* ->leftJoin('cargo', 'cargo.id', '=', 'invoices.cargo_id')
            ->leftJoin('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
            ->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
            ->leftJoin('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id') */
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->where('invoices.deleted', 0)
            ->where('invoices.total', '!=', '0.00')
            /* ->whereNotNull('invoices.cargo_id')
            ->whereNull('invoices.housefile_module') */
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        $query = $query->get()->toArray();

        // FIND THE EXCHANGE RATE IF CURRENCY IS HTG 
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'HTG')
            ->first();

        // PREPARE FINAL DATA FOR EXPORT TO EXCEL
        foreach ($query as $k => $v) {
            $finalOutput[$v->bill_to . ':' . $v->billingParty][] =  $v;
        }

        $finalTotalAmount = 0.00;
        foreach ($finalOutput as $k1 => $v1) {
            $explodeBillingPartyKey = explode(':', $k1);
            $data[] = [$explodeBillingPartyKey[1], '', '', '', '', '', ''];

            // GET ALL PAYMENTS OF BILLING PARTY
            $queryForInvoicePaymentOfBillingParty = DB::table('invoice_payments')
                //->select(DB::raw('SUM(invoice_payments.exchange_amount) as totalAmountReceived'))
                ->select(DB::raw('SUM(CASE WHEN exchange_currency = 3 THEN amount ELSE exchange_amount END) as totalAmountReceived'))
                ->where('client', $explodeBillingPartyKey[0]);
            if (!empty($fromDate) && !empty($toDate)) {
                $queryForInvoicePaymentOfBillingParty = $queryForInvoicePaymentOfBillingParty->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
            }
            $queryForInvoicePaymentOfBillingParty = $queryForInvoicePaymentOfBillingParty->first();



            $data[] = ['', '', 'Payment', '', '', !empty($queryForInvoicePaymentOfBillingParty->totalAmountReceived) ? $queryForInvoicePaymentOfBillingParty->totalAmountReceived : '0.00', ''];

            $totalForOneBillingParty = 0.00;
            foreach ($v1 as $k11 => $v11) {
                $openBalance = $v11->openBalance;;
                if ($v11->currency == 3)
                    $openBalance = $v11->openBalance * $exchangeRateOfUsdToHTH->exchangeRate;

                $data[] = ['', $v11->date, $v11->transactionType, $v11->num, $v11->terms, $openBalance, $v11->openBalance];
                $totalForOneBillingParty +=  $openBalance;
            }

            //$finalTotalAmount += $totalForOneBillingParty;
            $finalTotalAmount += $totalForOneBillingParty - $queryForInvoicePaymentOfBillingParty->totalAmountReceived;

            //$data[] = ['Total for ' . $explodeBillingPartyKey[1], '', '', '', '', $totalForOneBillingParty, ''];
            $data[] = ['Total for ' . $explodeBillingPartyKey[1], '', '', '', '', $totalForOneBillingParty - $queryForInvoicePaymentOfBillingParty->totalAmountReceived, ''];
        }
        $data[] = ['TOTAL', '', '', '', '', $finalTotalAmount, ''];

        /* $excelObj = Excel::create('Open Invoices', function ($excel) use ($data) {
            $excel->setTitle('Open Invoices');
        });
        $excelObj->download('xlsx'); */

        $export = new ExportFromArray([
            $data
        ]);

        return Excel::download($export, 'statement-of-accounts.xlsx');
    }

    public function exportStatementOfAccountNew($fromDate = null, $toDate = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        $data[] = ['CHATELAIN CARGO SERVICES S.A'];
        $data[] = ['Statement Of Accounts'];
        $data[] = [$fromDate . ' -- ' . $toDate];
        $data[] = [''];
        $data[] = ['', 'Date', 'Transaction Type', 'Num', 'Terms', 'Open Balance', 'Foreign Amount'];

        // Get all billing parties
        $allBillingParties = DB::table('invoices')
            //->select(['bill_to'])
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->where('invoices.deleted', 0)
            ->where('invoices.total', '!=', '0.00')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->whereNull('flag_invoice')
            ->whereBetween('invoices.date', array($fromDate, $toDate))
            ->pluck('clients.company_name as billingParty', 'bill_to')
            //->get()
            ->toArray();

        // FIND THE EXCHANGE RATE IF CURRENCY IS HTG 
        $exchangeRateOfHTGToUsd = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'HTG')
            ->first();

        $finalTotalAmount = 0.00;
        foreach ($allBillingParties as $keyParty => $valueParty) {
            $data[] = [$valueParty, '', '', '', '', '', ''];

            $queryInvoice = DB::table('invoices')
                ->select(DB::raw('"" as billingParty,invoices.date as date,"Invoice" as transactionType,invoices.bill_no as num,invoices.type_flag as terms,invoices.balance_of as openBalance,"0.00" as foreignAmount,invoices.currency'))
                //->join('clients', 'clients.id', '=', 'invoices.bill_to')
                ->where('invoices.deleted', 0)
                ->where('invoices.total', '!=', '0.00')
                ->where(function ($queryInvoice) {
                    $queryInvoice->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.bill_to', $keyParty)
                ->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $queryInvoice = $queryInvoice->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $queryPayment = DB::table('invoice_payments')
                ->select(DB::raw('"" as billingParty,invoice_payments.created_at as date,"Payment" as transactionType,payment_via_note as num,"" as terms,CASE WHEN exchange_currency = 3 THEN amount ELSE exchange_amount END as openBalance,"0.00" as foreignAmount,invoices.currency'))
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //->select(DB::raw('SUM(CASE WHEN exchange_currency = 3 THEN amount ELSE exchange_amount END) as totalAmountReceived'))
                ->where('client', $keyParty)
                ->where('invoice_payments.deleted', 0)
                ->groupBy('invoice_payments.id');
            if (!empty($fromDate) && !empty($toDate)) {
                //$queryPayment = $queryPayment->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
                $queryPayment = $queryPayment->whereBetween('invoice_payments.created_at', array($fromDate, $toDate));
            }

            $queryInvoiceAndPayment = $queryPayment->union($queryInvoice)->orderBy('date', 'asc')->get();

            $totalAmoutPendingForOneBillingParty = 0.00;
            $totalAmoutReceivedForOneBillingParty = 0.00;
            $totalForOneBillingParty = 0.00;
            foreach ($queryInvoiceAndPayment as $kInvoiceAndPayment => $vInvoiceAndPayment) {

                $openBalance = $vInvoiceAndPayment->openBalance;
                if ($vInvoiceAndPayment->currency == 3)
                    $openBalance = $vInvoiceAndPayment->openBalance * $exchangeRateOfHTGToUsd->exchangeRate;

                if ($vInvoiceAndPayment->transactionType == 'Invoice')
                    $totalAmoutPendingForOneBillingParty +=  $openBalance;
                else
                    $totalAmoutReceivedForOneBillingParty +=  $openBalance;

                $totalForOneBillingParty = $totalAmoutPendingForOneBillingParty - $totalAmoutReceivedForOneBillingParty;

                $data[] = [$vInvoiceAndPayment->billingParty, date('d-m-Y', strtotime($vInvoiceAndPayment->date)), $vInvoiceAndPayment->transactionType, $vInvoiceAndPayment->num, $vInvoiceAndPayment->terms, $vInvoiceAndPayment->transactionType == 'Payment' ? '-' . $openBalance : $openBalance, $vInvoiceAndPayment->transactionType == 'Payment' ? '-' . $vInvoiceAndPayment->openBalance : $vInvoiceAndPayment->openBalance];
            }

            $finalTotalAmount += $totalForOneBillingParty;

            //$data[] = ['Total for ' . $explodeBillingPartyKey[1], '', '', '', '', $totalForOneBillingParty, ''];
            $data[] = ['Total for ' . $valueParty, '', '', '', '', $totalForOneBillingParty, ''];
        }

        $data[] = ['TOTAL', '', '', '', '', $finalTotalAmount, ''];

        $export = new ExportFromArray([
            $data
        ]);

        return Excel::download($export, 'statement-of-accounts.xlsx');
    }

    public function showPendingInvoices()
    {
        return view('reports/pending-invoices');
    }

    public function exportPendingInvoices($fromDate = null, $toDate = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        $data[] = ['CHATELAIN CARGO SERVICES S.A'];
        $data[] = ['Pending Invoices'];
        $data[] = [$fromDate . ' -- ' . $toDate];
        $data[] = [''];
        $data[] = ['', 'Date', 'Transaction Type', 'Num', 'Terms', 'Open Balance', 'Foreign Amount'];

        // Get all billing parties
        $allBillingParties = DB::table('invoices')
            //->select(['bill_to'])
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->where('invoices.deleted', 0)
            ->where('invoices.total', '!=', '0.00')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->whereNull('flag_invoice')
            ->whereBetween('invoices.date', array($fromDate, $toDate))
            ->pluck('clients.company_name as billingParty', 'bill_to')
            //->get()
            ->toArray();

        // FIND THE EXCHANGE RATE IF CURRENCY IS HTG 
        $exchangeRateOfHTGToUsd = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'HTG')
            ->first();

        $finalTotalAmount = 0.00;
        foreach ($allBillingParties as $keyParty => $valueParty) {
            $data[] = [$valueParty, '', '', '', '', '', ''];

            $queryInvoice = DB::table('invoices')
                ->select(DB::raw('"" as billingParty,invoices.date as date,"Invoice" as transactionType,invoices.bill_no as num,invoices.type_flag as terms,invoices.balance_of as openBalance,"0.00" as foreignAmount,invoices.currency'))
                //->join('clients', 'clients.id', '=', 'invoices.bill_to')
                ->where('invoices.deleted', 0)
                ->where('invoices.total', '!=', '0.00')
                ->where(function ($queryInvoice) {
                    $queryInvoice->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.bill_to', $keyParty)
                ->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $queryInvoice = $queryInvoice->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $queryInvoiceAndPayment = $queryInvoice->orderBy('date', 'asc')->get();

            $totalAmoutPendingForOneBillingParty = 0.00;
            foreach ($queryInvoiceAndPayment as $kInvoiceAndPayment => $vInvoiceAndPayment) {

                $openBalance = $vInvoiceAndPayment->openBalance;
                if ($vInvoiceAndPayment->currency == 3)
                    $openBalance = $vInvoiceAndPayment->openBalance * $exchangeRateOfHTGToUsd->exchangeRate;

                $totalAmoutPendingForOneBillingParty +=  $openBalance;

                $data[] = [$vInvoiceAndPayment->billingParty, date('d-m-Y', strtotime($vInvoiceAndPayment->date)), $vInvoiceAndPayment->transactionType, $vInvoiceAndPayment->num, $vInvoiceAndPayment->terms, $openBalance, $vInvoiceAndPayment->openBalance];
            }

            $finalTotalAmount += $totalAmoutPendingForOneBillingParty;
            $data[] = ['Total for ' . $valueParty, '', '', '', '', $totalAmoutPendingForOneBillingParty, ''];
        }

        $data[] = ['TOTAL', '', '', '', '', $finalTotalAmount, ''];

        $export = new ExportFromArray([
            $data
        ]);

        return Excel::download($export, 'pending-invoices.xlsx');
    }

    public function showArAging()
    {
        return view('reports/ar-aging');
    }

    public function exportArAging($fromDate = null, $toDate = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        $data[] = ['CHATELAIN CARGO SERVICES S.A'];
        $data[] = ['A/R Aging Summary'];
        //$data[] = [$fromDate . ' -- ' . $toDate];
        $data[] = ['As of ' . date('F d, Y', strtotime($toDate))];
        $data[] = [''];
        $data[] = ['', 'Current', '1 - 30', '31 - 60', '61 - 90', '91 and over', 'Total'];

        // GET PENDING INVOICES
        $billingParties = DB::table('invoices')
            ->select(DB::raw('clients.company_name as billingParty,invoices.bill_to,invoices.currency'))
            //->select(DB::raw('clients.company_name as billingParty,invoices.bill_to,invoices.currency'))
            //->select(['invoices.bill_to'])
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->where('clients.deleted', 0)
            ->where('invoices.deleted', 0)
            ->where('invoices.total', '!=', '0.00')
            ->whereNull('flag_invoice')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })->orderBy('invoices.id', 'desc')->groupBy('invoices.bill_to')
            //->pluck('clients.company_name as billingParty', 'bill_to')
            //->all();
            ->get()
            ->toArray();




        // FIND THE EXCHANGE RATE IF CURRENCY IS HTG 
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'HTG')
            ->first();

        // PREPARE FINAL DATA FOR EXPORT TO EXCEL
        foreach ($billingParties as $k => $v) {
            $finalOutput[$v->bill_to . ':' . $v->billingParty] = $v;
        }



        //$today = Carbon::now()->today();
        $today = Carbon::parse($toDate);

        $last1 = Carbon::parse($toDate)->subDay(1);
        $last30 = Carbon::parse($toDate)->subDay(30);

        $last31 = Carbon::parse($toDate)->subDay(31);
        $last60 = Carbon::parse($toDate)->subDay(60);

        $last61 = Carbon::parse($toDate)->subDay(61);
        $last90 = Carbon::parse($toDate)->subDay(90);

        $last91 = Carbon::parse($toDate)->subDay(91);

        $totalTodaysAmountPending = 0.00;
        $totalLast1MonthAmountPending = 0.00;
        $totalLast2MonthAmountPending = 0.00;
        $totalLast3MonthAmountPending = 0.00;
        $totalLast4orOverMonthAmountPending = 0.00;
        foreach ($finalOutput as $k1 => $v1) {

            $explodeBillingPartyKey = explode(':', $k1);

            // today
            $todysPending = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->where('invoices.date', '=', $today)
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->first();


            // last 1 - 30
            $last1MonthPending = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->whereBetween('invoices.date', array($last30, $last1))
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->first();

            // last 31 - 60
            $last2MonthPending = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->whereBetween('invoices.date', array($last60, $last31))
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->first();

            // last 61 - 90
            $last3MonthPending = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->whereBetween('invoices.date', array($last90, $last61))
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->first();

            // last 90 and over
            $last4orOverMonthPending = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->where('invoices.date', '<=', $last91)
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->first();

            $totalTodayPending = $todysPending->totalAmountPending;
            $totalLast1MonthPending = $last1MonthPending->totalAmountPending;
            $totalLast2MonthPending = $last2MonthPending->totalAmountPending;
            $totalLast3MonthPending = $last3MonthPending->totalAmountPending;
            $totalLast4orOverMontPending = $last4orOverMonthPending->totalAmountPending;

            $todaysAmountPending = ($v1->currency == 3) ? $totalTodayPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalTodayPending;

            $last1MonthAmountPending = ($v1->currency == 3) ? $totalLast1MonthPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast1MonthPending;

            $last2MonthAmountPending = ($v1->currency == 3) ? $totalLast2MonthPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast2MonthPending;

            $last3MonthAmountPending = ($v1->currency == 3) ? $totalLast3MonthPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast3MonthPending;

            $last4orOverMonthAmountPending = ($v1->currency == 3) ? $totalLast4orOverMontPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast4orOverMontPending;


            $totolOfAllDurationOfBillingPary = $todaysAmountPending + $last1MonthAmountPending + $last2MonthAmountPending + $last3MonthAmountPending + $last4orOverMonthAmountPending;

            $data[] = [$explodeBillingPartyKey[1], $todaysAmountPending, $last1MonthAmountPending, $last2MonthAmountPending, $last3MonthAmountPending, $last4orOverMonthAmountPending, $totolOfAllDurationOfBillingPary];

            $totalTodaysAmountPending += $todaysAmountPending;
            $totalLast1MonthAmountPending += $last1MonthAmountPending;
            $totalLast2MonthAmountPending += $last2MonthAmountPending;
            $totalLast3MonthAmountPending += $last3MonthAmountPending;
            $totalLast4orOverMonthAmountPending += $last4orOverMonthAmountPending;
        }

        //var_dump($data); exit;

        // total of all the totals
        $allTotal = $totalTodaysAmountPending + $totalLast1MonthAmountPending + $totalLast2MonthAmountPending + $totalLast3MonthAmountPending + $totalLast4orOverMonthAmountPending;

        $data[] = ['TOTAL', $totalTodaysAmountPending, $totalLast1MonthAmountPending, $totalLast2MonthAmountPending, $totalLast3MonthAmountPending, $totalLast4orOverMonthAmountPending, $allTotal];

        /* $excelObj = Excel::create('A R Aging Summary', function ($excel) use ($data) {
            $excel->setTitle('A R Aging Summary');
        });
        $excelObj->download('xlsx'); */

        $export = new ArAging([
            $data
        ]);

        return Excel::download($export, 'ar-aging.xlsx');
    }

    public function exportArAgingNew($fromDate = null, $toDate = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';

        $data[] = ['CHATELAIN CARGO SERVICES S.A'];
        $data[] = ['A/R Aging Summary'];
        //$data[] = [$fromDate . ' -- ' . $toDate];
        $data[] = ['As of ' . date('F d, Y', strtotime($toDate))];
        $data[] = [''];
        $data[] = ['', 'Current', '1 - 30', '31 - 60', '61 - 90', '91 and over', 'Total'];

        // GET PENDING INVOICES
        $billingParties = DB::table('invoices')
            ->select(DB::raw('clients.company_name as billingParty,invoices.bill_to,invoices.currency'))
            //->select(DB::raw('clients.company_name as billingParty,invoices.bill_to,invoices.currency'))
            //->select(['invoices.bill_to'])
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->where('clients.deleted', 0)
            ->where('invoices.deleted', 0)
            ->where('invoices.total', '!=', '0.00')
            ->whereNull('flag_invoice')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            //->orderBy('invoices.id', 'desc')
            ->orderBy('clients.company_name', 'asc')
            ->groupBy('invoices.bill_to')
            //->pluck('clients.company_name as billingParty', 'bill_to')
            //->all();
            ->get()
            ->toArray();


        // FIND THE EXCHANGE RATE IF CURRENCY IS HTG 
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'HTG')
            ->first();

        // PREPARE FINAL DATA FOR EXPORT TO EXCEL
        foreach ($billingParties as $k => $v) {
            $finalOutput[$v->bill_to . ':' . $v->billingParty] = $v;
        }



        //$today = Carbon::now()->today();
        $today = Carbon::parse($toDate);

        $last1 = Carbon::parse($toDate)->subDay(1);
        $last30 = Carbon::parse($toDate)->subDay(30);

        $last31 = Carbon::parse($toDate)->subDay(31);
        $last60 = Carbon::parse($toDate)->subDay(60);

        $last61 = Carbon::parse($toDate)->subDay(61);
        $last90 = Carbon::parse($toDate)->subDay(90);

        $last91 = Carbon::parse($toDate)->subDay(91);

        $totalTodaysAmountPending = 0.00;
        $totalLast1MonthAmountPending = 0.00;
        $totalLast2MonthAmountPending = 0.00;
        $totalLast3MonthAmountPending = 0.00;
        $totalLast4orOverMonthAmountPending = 0.00;
        foreach ($finalOutput as $k1 => $v1) {

            $explodeBillingPartyKey = explode(':', $k1);

            // today
            $todysPending = DB::table('invoices')
                //->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->where('invoices.date', '=', $today)
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.balance_of', '!=', 0.00)
                //->first();
                ->sum('invoices.balance_of');


            // last 1 - 30
            $last1MonthPending = DB::table('invoices')
                //->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->whereBetween('invoices.date', array($last30, $last1))
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.balance_of', '!=', 0.00)
                //->first();
                ->sum('invoices.balance_of');

            // last 31 - 60
            $last2MonthPending = DB::table('invoices')
                //->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->whereBetween('invoices.date', array($last60, $last31))
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.balance_of', '!=', 0.00)
                //->first();
                ->sum('invoices.balance_of');

            // last 61 - 90
            $last3MonthPending = DB::table('invoices')
                //->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->whereBetween('invoices.date', array($last90, $last61))
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.balance_of', '!=', 0.00)
                //->first();
                ->sum('invoices.balance_of');

            // last 90 and over
            $last4orOverMonthPending = DB::table('invoices')
                //->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                ->where('invoices.date', '<=', $last91)
                ->where('bill_to', $explodeBillingPartyKey[0])
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })
                ->where('invoices.balance_of', '!=', 0.00)
                ->sum('invoices.balance_of');
            //->first();

            //pre($last4orOverMonthPending);

            $totalTodayPending = $todysPending;
            $totalLast1MonthPending = $last1MonthPending;
            $totalLast2MonthPending = $last2MonthPending;
            $totalLast3MonthPending = $last3MonthPending;
            $totalLast4orOverMontPending = $last4orOverMonthPending;



            $todaysAmountPending = ($v1->currency == 3) ? $totalTodayPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalTodayPending;

            $last1MonthAmountPending = ($v1->currency == 3) ? $totalLast1MonthPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast1MonthPending;

            $last2MonthAmountPending = ($v1->currency == 3) ? $totalLast2MonthPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast2MonthPending;

            $last3MonthAmountPending = ($v1->currency == 3) ? $totalLast3MonthPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast3MonthPending;

            $last4orOverMonthAmountPending = ($v1->currency == 3) ? $totalLast4orOverMontPending * $exchangeRateOfUsdToHTH->exchangeRate : $totalLast4orOverMontPending;


            $totolOfAllDurationOfBillingPary = $todaysAmountPending + $last1MonthAmountPending + $last2MonthAmountPending + $last3MonthAmountPending + $last4orOverMonthAmountPending;

            $data[] = [$explodeBillingPartyKey[1], $todaysAmountPending, $last1MonthAmountPending, $last2MonthAmountPending, $last3MonthAmountPending, $last4orOverMonthAmountPending, $totolOfAllDurationOfBillingPary];

            $totalTodaysAmountPending += $todaysAmountPending;
            $totalLast1MonthAmountPending += $last1MonthAmountPending;
            $totalLast2MonthAmountPending += $last2MonthAmountPending;
            $totalLast3MonthAmountPending += $last3MonthAmountPending;
            $totalLast4orOverMonthAmountPending += $last4orOverMonthAmountPending;
        }

        //var_dump($data); exit;

        // total of all the totals
        $allTotal = $totalTodaysAmountPending + $totalLast1MonthAmountPending + $totalLast2MonthAmountPending + $totalLast3MonthAmountPending + $totalLast4orOverMonthAmountPending;

        $data[] = ['TOTAL', $totalTodaysAmountPending, $totalLast1MonthAmountPending, $totalLast2MonthAmountPending, $totalLast3MonthAmountPending, $totalLast4orOverMonthAmountPending, $allTotal];

        /* $excelObj = Excel::create('A R Aging Summary', function ($excel) use ($data) {
            $excel->setTitle('A R Aging Summary');
        });
        $excelObj->download('xlsx'); */

        $export = new ArAging([
            $data
        ]);

        return Excel::download($export, 'ar-aging.xlsx');
    }
}
