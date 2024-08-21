<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\Invoices;
use App\Aeropost;
use App\ccpack;
use App\Ups;
use App\Activities;

class CommonController extends Controller
{
    public function viewInvoiceDetailsWithCollection($invoiceId = null)
    {
        $details = DB::table('invoices')->where('deleted', '0')->where('id', $invoiceId)->get();
        $paymentDetail = DB::table('invoice_payments')
            ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'users.name as paymentReceivedBy', 'invoice_payments.id as paymentId'])
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->join('users', 'users.id', '=', 'invoice_payments.payment_accepted_by')
            ->where('invoice_payments.deleted', '0')
            ->where('invoice_payments.invoice_id', $invoiceId)
            ->get();
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
        }

        foreach ($paymentDetail as $k => $v) {
            $paymentDetail[$k]->paymentId = '10' . $v->paymentId;
        }

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();
        $htgTousd = $totalOfCurrency[1] * $exchangeRateOfUsdToHTH->exchangeRate;
        $totalOfCurrency['total'] = $totalOfCurrency[3] + $htgTousd;
        return view('common.viewinvoicedetailswithcollection', compact('details', 'paymentDetail', 'totalOfCurrency'));
    }

    public function acceptfiles($moduleId, $flagFromWhere = null, $flagModule = null)
    {
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        if ($flagModule == 'Aeropost') {
            return redirect('warehouseaeropost/viewcourieraeropostdetailforwarehouse/' . $moduleId);
        }
        if ($flagModule == 'CCPack') {
            return redirect('warehousccpack/viewcourierccpackdetailforwarehouse/' . $moduleId);
        }
        if ($flagModule == 'Ups') {
            return redirect('warehouseups/viewcourierdetailforwarehouse/' . $moduleId);
        }
    }

    public function acceptfilessubmit($moduleId, $flagModule = null, $tblName = null)
    {
        DB::table($tblName)
            ->where('id', $moduleId)
            ->update(['nonbounded_wh_confirmation' => 1, 'nonbounded_wh_confirmation_by' => auth()->user()->id, 'nonbounded_wh_confirmation_on' => date('Y-m-d'), 'display_notification_nonbounded_wh' => 0]);
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');

        if ($flagModule == 'Aeropost') {
            $activitiesType = 'aeropost';
        }
        if ($flagModule == 'CCPack') {
            $activitiesType = 'ccpack';
        }
        if ($flagModule == 'Ups') {
            $activitiesType = 'ups';
        }

        $modelActivities = new Activities;
        $modelActivities->type = $activitiesType;
        $modelActivities->related_id = $moduleId;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "Non Bounded warehouse file status | <strong>Received</strong> | On " . date('d-m-Y');
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if ($flagModule == 'Aeropost') {
            return redirect('warehouseaeropost/viewcourieraeropostdetailforwarehouse/' . $moduleId);
        }
        if ($flagModule == 'CCPack') {
            return redirect('warehousccpack/viewcourierccpackdetailforwarehouse/' . $moduleId);
        }
        if ($flagModule == 'Ups') {
            return redirect('warehouseups/viewcourierdetailforwarehouse/' . $moduleId);
        }
    }
}
