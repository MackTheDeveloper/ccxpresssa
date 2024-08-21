<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Aeropost;
use App\AeropostMaster;
use App\UpsMaster;
use App\CcpackMaster;
use App\BillingItems;
use App\Costs;
use App\CashCredit;
use App\Currency;
use App\Invoices;
use App\Mail\upsCommissionMail;
use App\Ups;
use App\AeropostFreightCommission;
use App\User;
use App\Cargo;
use App\ccpack;
use App\Clients;
use App\Expense;
use App\Vendors;
use App\ExpenseDetails;
use App\Exports\ArAging;
use App\Exports\ExportFromArray;
use App\Exports\OpenInvoicesExportWithDetaultStyles;
use App\HawbFiles;
use App\InvoiceItemDetails;
use Config;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PDF;
use QuickBooksOnline\API\Facades\Invoice;
use Response;
use Carbon\Carbon;


class ReportsController extends Controller
{
    public function cashcreditallreport()
    {
        $checkPermission = User::checkPermission(['cash_credit_reports'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cashcredit = DB::table('cashcredit')->where('deleted', '0')->orderBy('id', 'desc')->where('status', 1)->get();
        $model = new CashCredit;
        return view("reports.cashcreditallreport", ['model' => $model, 'cashcredit' => $cashcredit]);
    }

    public function getcashcreditdataonclick($accountId, $accountName)
    {
        $cashCreditId = $accountId;
        $accountName = $accountName;
        $dataCashCredit = DB::table('activities')->where('type', 'cashCredit')->where('related_id', $cashCreditId)->get();

        $pdf = PDF::loadView('reports.printcashcreditreport', ['dataCashCredit' => $dataCashCredit, 'accountName' => $accountName]);
        $pdf_file = 'cashCredit-' . $cashCreditId . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);

        return view("reports.cashcreditallonclickreport", ['dataCashCredit' => $dataCashCredit, 'pdf_file' => $pdf_file]);
    }

    public function cashcreditreport()
    {
        $checkPermission = User::checkPermission(['cash_credit_reports'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);
        $model = new CashCredit;
        return view("reports.cashcreditreport", ['model' => $model, 'cashCredit' => $cashCredit]);
    }

    public function getcashcreditdata()
    {
        $cashCreditId = $_POST['cashCreditId'];
        $accountName = $_POST['accountName'];
        $dataCashCredit = DB::table('activities')->where('type', 'cashCredit')->where('related_id', $cashCreditId)->get();

        $pdf = PDF::loadView('reports.printcashcreditreport', ['dataCashCredit' => $dataCashCredit, 'accountName' => $accountName]);
        $pdf_file = 'cashCredit-' . $cashCreditId . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);

        return view("reports.cashcreditreportajax", ['dataCashCredit' => $dataCashCredit, 'pdf_file' => $pdf_file]);
    }

    public function clientcreditallreport()
    {
        /* $clients = DB::table('clients')->where('deleted', 0)->where('client_flag', 'B')->where('status', 1)->where('cash_credit', 'Credit')->orderBy('id', 'desc')->get();
        $model = new CashCredit; */
        return view("reports.clientcreditallreport");
    }

    public function listclientcreditallreport(Request $request)
    {
        $req = $request->all();
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['clients.id', 'clients.company_name', 'clients.credit_limit', 'clients.available_balance'];

        $total = Clients::selectRaw('count(*) as total')
            ->where('client_flag', 'B')
            ->where('status', 1)
            ->where('cash_credit', 'Credit')
            ->where('clients.deleted', '0');
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('clients')
            ->selectRaw('clients.id,clients.company_name,clients.credit_limit,clients.available_balance')
            ->where('client_flag', 'B')
            ->where('status', 1)
            ->where('cash_credit', 'Credit')
            ->where('clients.deleted', '0');

        $filteredq = DB::table('clients')
            ->where('client_flag', 'B')
            ->where('status', 1)
            ->where('cash_credit', 'Credit')
            ->where('clients.deleted', '0');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('clients.company_name', 'like', '%' . $search . '%')
                    ->orWhere('clients.credit_limit', 'like', '%' . $search . '%')
                    ->orWhere('clients.available_balance', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('clients.company_name', 'like', '%' . $search . '%')
                    ->orWhere('clients.credit_limit', 'like', '%' . $search . '%')
                    ->orWhere('clients.available_balance', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $dueToPay = Invoices::getDueAmount($items->id);
            $data[] = [$items->id, $items->company_name, $items->credit_limit, $items->available_balance, $dueToPay];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function getclientcreditdataonclick($clientId, $clientName)
    {
        /* $cashCreditId = $clientId;
        $clientName = $clientName;
        $dataCashCredit = DB::table('activities')->where('type', 'cashCreditClient')->where('related_id', $cashCreditId)->get();

        $pdf = PDF::loadView('reports.printclientcreditreport', ['dataCashCredit' => $dataCashCredit, 'clientName' => $clientName]);
        $pdf_file = 'clientCredit-' . $cashCreditId . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path); */

        return view("reports.getclientcreditdataonclick", ['clientId' => $clientId, 'clientName' => $clientName]);
    }

    public function listgetclientcreditdataonclick(Request $request)
    {
        $req = $request->all();
        $clientId = $req['clientId'];
        $clientName = $req['clientName'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['activities.id', 'activities.description', '', ''];

        $total = Activities::selectRaw('count(*) as total')
            ->where('type', 'cashCreditClient')->where('related_id', $clientId);
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('activities')
            ->selectRaw('activities.description,activities.updated_on,activities.cash_credit_flag')
            ->where('type', 'cashCreditClient')->where('related_id', $clientId);

        $filteredq = DB::table('activities')
            ->where('type', 'cashCreditClient')->where('related_id', $clientId);

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('activities.description', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('activities.description', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $amtDesc = explode('-', $items->description);
            $data[] = [date('d-m-Y h:i:s', strtotime($items->updated_on)), $amtDesc[1], $items->cash_credit_flag == 1 ? $amtDesc[0] : '-', $items->cash_credit_flag == 2 ? $amtDesc[0] : '-'];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function clientcreditreport()
    {
        $checkPermission = User::checkPermission(['client_credit_reports'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $clients = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $clients = json_decode($clients, 1);
        ksort($clients);
        $model = new CashCredit;
        return view("reports.clientcreditreport", ['model' => $model, 'clients' => $clients]);
    }

    public function getclientcreditdata()
    {
        $cashCreditId = $_POST['cashCreditId'];
        $clientName = $_POST['clientName'];
        $dataCashCredit = DB::table('activities')->where('type', 'cashCreditClient')->where('related_id', $cashCreditId)->get();

        $pdf = PDF::loadView('reports.printclientcreditreport', ['dataCashCredit' => $dataCashCredit, 'clientName' => $clientName]);
        $pdf_file = 'clientCredit-' . $cashCreditId . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);

        return view("reports.clientcreditreportajax", ['dataCashCredit' => $dataCashCredit, 'pdf_file' => $pdf_file]);
    }

    public function missingInvoiceReport()
    {
        return view("reports.missinginvoiceReports");
    }

    public function listmissingInvoiceReport(Request $request)
    {
        $req = $request->all();
        $cargoType = $req['cargoType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($cargoType == 'Cargo')
            $orderby = ['cargo.id', 'cargo.id', 'exp_date', 'cargo.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];
        else
            $orderby = ['hawb_files.id', 'hawb_files.id', 'exp_date', 'hawb_files.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];
        $unpaidExpenses = BillingItems::getUnpaidExpenses($cargoType);
        if ($cargoType == 'Cargo') {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->whereNotNull('expenses.cargo_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('expenses.exp_date', array($fromDate, $toDate));
            }

            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.cargo_id,expenses.voucher_number,cargo.file_number,cargo.cargo_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,cargo.id as cargoId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->whereNotNull('expenses.cargo_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('expenses.exp_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->whereNotNull('expenses.cargo_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('expenses.exp_date', array($fromDate, $toDate));
            }
        } else {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->whereNotNull('expenses.house_file_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('expenses.exp_date', array($fromDate, $toDate));
            }

            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.house_file_id,expenses.voucher_number,hawb_files.file_number,hawb_files.cargo_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,hawb_files.id as houseId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->whereNotNull('expenses.house_file_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('expenses.exp_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->whereNotNull('expenses.house_file_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('expenses.exp_date', array($fromDate, $toDate));
            }
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $cargoType) {
                if ($cargoType == 'Cargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else {
                    $query2->where('hawb_files.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $cargoType) {
                if ($cargoType == 'Cargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else {
                    $query2->where('hawb_files.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $checkAssociateBillingItemGeneratedORNot = '';
            if ($cargoType == 'Cargo') {
                $moduleId = $value->cargoId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('Cargo', $moduleId, $value->expense_type);
            } else {
                $moduleId = $value->houseId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('House File', $moduleId, $value->expense_type);
            }

            $data[] = [$moduleId, $checkAssociateBillingItemGeneratedORNot, !empty($value->exp_date) ? date('d-m-Y', strtotime($value->exp_date)) : '-', $value->file_number,  $value->voucher_number, $value->description, $value->consigneeCompany, $value->shipperCompany, $value->code, number_format($value->amount, 2)];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);

        /* $expenses_details = DB::table('expenses')
            ->select(DB::raw('expense_details.*,expense_details.amount , expenses.cargo_id,expenses.voucher_number,cargo.file_number,cargo.cargo_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,cargo.id as cargoId'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->join('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.cargo_id')
            ->where('expenses.deleted', '0')
            ->orderBy('expenses.cargo_id', 'desc')
            ->get();
        //pre($expenses_details);
        return view("reports.missinginvoiceReports", ['expenses_details' => $expenses_details]); */
    }

    public function missingInoiceReportPdf()
    {
        /*  $expenses_details = DB::table('expenses')
            ->select(DB::raw('SUM(expense_details.amount) as total_expense , expenses.cargo_id,GROUP_CONCAT(DISTINCT(expense_details.voucher_number)) AS voucherNumberList,cargo.file_number'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->whereNotNull('expenses.cargo_id')
            ->where('expenses.deleted', '0')
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expense_details.deleted', '0')
            ->whereNotIn('expenses.cargo_id', function ($query) {
                $query->select(DB::raw('cargo_id'))
                    ->from('invoices')
                    ->where('deleted', '0')->whereNotNull('cargo_id')->groupBy('cargo_id');
            })
            ->orderBy('expenses.cargo_id', 'desc')
            ->groupBy('expenses.cargo_id')
            ->get(); */

        $expenses_details = DB::table('expenses')
            ->select(DB::raw('SUM(expense_details.amount) as total_expense , expenses.cargo_id,expenses.voucher_number,cargo.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->whereNotNull('expenses.cargo_id')
            ->where('expenses.deleted', '0')
            //->where('expenses.expense_request', 'Disbursement done')
            ->where('expense_details.deleted', '0')
            ->whereNotIn('expenses.cargo_id', function ($query) {
                $query->select(DB::raw('cargo_id'))
                    ->from('invoices')
                    ->where('deleted', '0')->whereNotNull('cargo_id')->groupBy('cargo_id');
            })
            //->orderBy('expenses.cargo_id', 'desc')
            //->groupBy('expenses.cargo_id')
            ->orderBy('expenses.cargo_id', 'desc')
            ->groupBy('expenses.expense_id')
            ->get();

        $pdf = PDF::loadView('reports.printmissinginvoiceReports', ['expenses_details' => $expenses_details]);
        $pdf_file = 'printmissinginvoiceReports.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Cargo/';

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Missing_Invoice_Report.pdf', $filecontent, 'public');
        //return response()->file($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function upsMissingInvoiceReport()
    {
        return view("reports.courier.upsMissinginvoiceReports");
    }

    public function listupsMissingInvoiceReport(Request $request)
    {
        $req = $request->all();
        $courierType = $req['courierType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($courierType == 'UPS')
            $orderby = ['ups_details.id', 'ups_details.id', 'exp_date', 'ups_details.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];
        else if ($courierType == 'upsMaster')
            $orderby = ['ups_master.id', 'ups_master.id', 'exp_date', 'ups_master.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];
        else if ($courierType == 'Aeropost')
            $orderby = ['aeropost.id', 'aeropost.id', 'exp_date', 'aeropost.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'from_location', 'currency.code', 'expense_details.amount'];
        else if ($courierType == 'aeropostMaster')
            $orderby = ['aeropost_master.id', 'aeropost_master.id', 'exp_date', 'aeropost_master.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];
        else if ($courierType == 'ccpackMaster')
            $orderby = ['ccpack_master.id', 'ccpack_master.id', 'exp_date', 'ccpack_master.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];
        else
            $orderby = ['ccpack.id', 'ccpack.id', 'exp_date', 'ccpack.file_number', 'expenses.voucher_number', 'expense_details.description', 'c1.company_name', 'c2.company_name', 'currency.code', 'expense_details.amount'];

        $unpaidExpenses = BillingItems::getUnpaidExpenses($courierType);
        if ($courierType == 'UPS') {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->whereNotNull('expenses.ups_details_id')
                ->where('expenses.deleted', '0')
                ->where('ups_details.fd', 0)
                ->where('ups_details.package_type', '!=', 'DOC')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'upsMaster') {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->whereNotNull('expenses.ups_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'Aeropost') {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->whereNotNull('expenses.aeropost_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'aeropostMaster') {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->whereNotNull('expenses.aeropost_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'ccpackMaster') {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->whereNotNull('expenses.ccpack_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else {
            $total = Expense::selectRaw('count(*) as total')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->whereNotNull('expenses.ccpack_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($courierType == 'UPS') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ups_details_id,expenses.voucher_number,ups_details.file_number,ups_details.courier_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->whereNotNull('expenses.ups_details_id')
                ->where('expenses.deleted', '0')
                ->where('ups_details.fd', 0)
                ->where('ups_details.package_type', '!=', 'DOC')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'upsMaster') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ups_master_id,expenses.voucher_number,ups_master.file_number,ups_master.ups_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ups_master.id as upsMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->whereNotNull('expenses.ups_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'Aeropost') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.aeropost_id,expenses.voucher_number,aeropost.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->whereNotNull('expenses.aeropost_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'aeropostMaster') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.aeropost_master_id,expenses.voucher_number,aeropost_master.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,aeropost_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->whereNotNull('expenses.aeropost_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'ccpackMaster') {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ccpack_master_id,expenses.voucher_number,ccpack_master.file_number,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ccpack_master.id as ccpackMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->whereNotNull('expenses.ccpack_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else {
            $query = DB::table('expenses')
                ->selectRaw(DB::raw('expense_details.*,expense_details.amount , expenses.ccpack_id,expenses.voucher_number,ccpack.file_number,ccpack.ccpack_operation_type,expenses.exp_date,expenses.consignee,expenses.shipper,expenses.expense_id,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,currency.code'))
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->whereNotNull('expenses.ccpack_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }

        if ($courierType == 'UPS') {
            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->whereNotNull('expenses.ups_details_id')
                ->where('expenses.deleted', '0')
                ->where('ups_details.fd', 0)
                ->where('ups_details.package_type', '!=', 'DOC')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'upsMaster') {
            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->whereNotNull('expenses.ups_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'Aeropost') {
            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->whereNotNull('expenses.aeropost_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'aeropostMaster') {
            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->whereNotNull('expenses.aeropost_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else if ($courierType == 'ccpackMaster') {
            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->whereNotNull('expenses.ccpack_master_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        } else {
            $filteredq = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')
                ->join('currency', 'currency.id', '=', 'expenses.currency')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->whereNotNull('expenses.ccpack_id')
                ->where('expenses.deleted', '0')
                ->where('expense_details.deleted', '0')
                ->whereIn('expense_details.id', $unpaidExpenses);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }


        if ($search != '') {
            $query->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'upsMaster') {
                    $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'Aeropost') {
                    $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'aeropostMaster') {
                    $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'ccpackMaster') {
                    $query2->where('ccpack_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else {
                    $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'upsMaster') {
                    $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'Aeropost') {
                    $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'aeropostMaster') {
                    $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else if ($courierType == 'ccpackMaster') {
                    $query2->where('ccpack_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                } else {
                    $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                        ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('currency.code', 'like', '%' . $search . '%')
                        ->orWhere('expense_details.amount', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $checkAssociateBillingItemGeneratedORNot = '';
            /* if ($checkAssociateBillingItemGeneratedORNot == "1")
                continue; */
            if ($courierType == 'UPS') {
                $moduleId = $value->upsId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('Ups', $moduleId, $value->expense_type);
            } else if ($courierType == 'upsMaster') {
                $moduleId = $value->upsMasterId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('upsMaster', $moduleId, $value->expense_type);
            } else if ($courierType == 'Aeropost') {
                $moduleId = $value->aeropostId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('Aeropost', $moduleId, $value->expense_type);
            } else if ($courierType == 'aeropostMaster') {
                $moduleId = $value->aeropostMasterId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('aeropostMaster', $moduleId, $value->expense_type);
            } else if ($courierType == 'ccpackMaster') {
                $moduleId = $value->ccpackMasterId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('ccpackMaster', $moduleId, $value->expense_type);
            } else {
                $moduleId = $value->ccpackId;
                //$checkAssociateBillingItemGeneratedORNot = BillingItems::checkAssociateBillingCreatedOrNot('CCPack', $moduleId, $value->expense_type);
            }

            $data[] = [$moduleId, $checkAssociateBillingItemGeneratedORNot, !empty($value->exp_date) ? date('d-m-Y', strtotime($value->exp_date)) : '-', $value->file_number,  $value->voucher_number, $value->description, $value->consigneeCompany, $value->shipperCompany, $value->code, number_format($value->amount, 2)];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);


        /* $expenses_details = DB::table('expenses')
            ->select(DB::raw('SUM(expense_details.amount) as total_expense , expenses.ups_details_id,GROUP_CONCAT(DISTINCT(expense_details.voucher_number)) AS voucherNumberList,ups_details.file_number'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->whereNotNull('expenses.ups_details_id')
            ->where('expenses.deleted', '0')
            //->where('expenses.expense_request', 'Disbursement done')
            ->where('expense_details.deleted', '0')
            ->whereNotIn('expenses.ups_details_id', function ($query) {
                $query->select(DB::raw('ups_id'))
                    ->from('invoices')
                    ->whereNotNull('ups_id')
                    ->where('deleted', '0')->groupBy('ups_id');
            })
            //->orderBy('expenses.ups_details_id', 'desc')
            //->groupBy('expenses.ups_details_id')
            ->orderBy('expenses.cargo_id', 'desc')
            ->groupBy('expenses.expense_id')
            ->get();
        return view("reports.courier.upsMissinginvoiceReports", ['expenses_details' => $expenses_details]); */
    }

    public function upsMissingInoiceReportPdf()
    {
        $expenses_details = DB::table('expenses')
            ->select(DB::raw('SUM(expense_details.amount) as total_expense , expenses.ups_details_id,GROUP_CONCAT(DISTINCT(expense_details.voucher_number)) AS voucherNumberList,ups_details.file_number'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->whereNotNull('expenses.ups_details_id')
            ->where('expenses.deleted', '0')
            //->where('expenses.expense_request', 'Disbursement done')
            ->where('expense_details.deleted', '0')
            ->whereNotIn('expenses.ups_details_id', function ($query) {
                $query->select(DB::raw('ups_id'))
                    ->from('invoices')
                    ->whereNotNull('ups_id')
                    ->where('deleted', '0')->groupBy('ups_id');
            })
            ->orderBy('expenses.ups_details_id', 'desc')
            ->groupBy('expenses.ups_details_id')
            ->get();

        $pdf = PDF::loadView('reports.courier.printupsmissinginvoiceReports', ['expenses_details' => $expenses_details]);
        $pdf_file = 'printupsmissinginvoiceReports.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Courier/Ups/';

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Missing_Invoice_Report.pdf', $filecontent, 'public');
        //return response()->file($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function customreport()
    {
        $checkPermission = User::checkPermission(['custom_reports'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        /* $customFileIds = DB::table('customs')->select('ups_details_id')->where('deleted',0)->whereNotNull('ups_details_id')->get();
        $arCustom = array();
        foreach ($customFileIds as $key => $value) {
        $arCustom[] = $value->ups_details_id;
        } */
        /* $dataFileNumber = DB::table('ups_details')->where('deleted',0)->whereIn('id',$arCustom)->get()
        ->pluck('file_number','id'); */
        /* $dataFileNumber = DB::table('ups_details')->where('deleted',0)->get()
        ->pluck('file_number','id'); */

        /* $dataInvoices = array();
        $dataImportUpsFiles = DB::table('ups_details')->where('deleted', 0)->where('courier_operation_type', '1')->get();

        return view("reports.customreport", ['dataInvoices' => $dataInvoices, 'dataImportUpsFiles' => $dataImportUpsFiles]); */
        return view("reports.customreport");
    }

    public function listcustomreport(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $paymentStatus = $req['paymentStatus'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ups_details.file_number', 'customs.file_number', 'invoices.date', 'invoices.payment_status', '', '', '', 'c1.company_name', 'ups_details.awb_number', ''];

        $total = Ups::selectRaw('invoices.ups_id as total')
            ->leftJoin('customs', 'customs.ups_details_id', '=', 'ups_details.id')
            //->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('invoices', function ($join) {
                $join->on('invoices.ups_id', '=', 'ups_details.id')
                    ->whereNotNull('invoices.ups_id');
            })
            //->leftJoin('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('invoice_item_details', function ($join) {
                $join->on('invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoice_item_details.fees_name', '=', '26');
            })
            ->leftJoin('custom_expenses', 'custom_expenses.ups_details_id', '=', 'ups_details.id')
            ->leftJoin('custom_expense_details', 'custom_expense_details.expense_id', '=', 'custom_expenses.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->where('ups_details.deleted', 0)
            ->where('ups_details.courier_operation_type', '1')
            //->where('invoice_item_details.fees_name', '26')
            //->whereNotNull('invoices.ups_id');
            ->groupBy('ups_details.id');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        if (!empty($paymentStatus)) {
            $total = $total->where('invoices.payment_status', $paymentStatus);
        }

        $total = $total->get();
        $totalfiltered = count($total);
        //pre($totalfiltered);

        $query = DB::table('ups_details')
            ->selectRaw(DB::raw('SUM(invoice_item_details.total_of_items) as totalOfInvoice,invoices.id as invoiceId,invoices.date as invoiceDate,invoices.payment_status as invoicePaymentStatus,invoices.id as invoiceId,ups_details.file_number as upsFileNumber,ups_details.awb_number as awbNumber,ups_details.id as upsId,custom_expense_details.amount as totalOfExpense,c1.company_name as consigneeName,customs.file_number as customFileNumber'))
            ->leftJoin('customs', 'customs.ups_details_id', '=', 'ups_details.id')
            //->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('invoices', function ($join) {
                $join->on('invoices.ups_id', '=', 'ups_details.id')
                    ->whereNotNull('invoices.ups_id');
            })
            //->leftJoin('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('invoice_item_details', function ($join) {
                $join->on('invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoice_item_details.fees_name', '=', '26');
            })
            ->leftJoin('custom_expenses', 'custom_expenses.ups_details_id', '=', 'ups_details.id')
            ->leftJoin('custom_expense_details', 'custom_expense_details.expense_id', '=', 'custom_expenses.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->where('ups_details.deleted', 0)
            ->where('ups_details.courier_operation_type', '1')
            /* ->where('invoice_item_details.fees_name', '26')
            ->whereNotNull('invoices.ups_id') */
            ->groupBy('ups_details.id');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        if (!empty($paymentStatus)) {
            $query = $query->where('invoices.payment_status', $paymentStatus);
        }


        $filteredq = DB::table('ups_details')
            ->leftJoin('customs', 'customs.ups_details_id', '=', 'ups_details.id')
            //->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('invoices', function ($join) {
                $join->on('invoices.ups_id', '=', 'ups_details.id')
                    ->whereNotNull('invoices.ups_id');
            })
            //->leftJoin('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('invoice_item_details', function ($join) {
                $join->on('invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoice_item_details.fees_name', '=', '26');
            })
            ->leftJoin('custom_expenses', 'custom_expenses.ups_details_id', '=', 'ups_details.id')
            ->leftJoin('custom_expense_details', 'custom_expense_details.expense_id', '=', 'custom_expenses.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->where('ups_details.deleted', 0)
            ->where('ups_details.courier_operation_type', '1')
            /* ->where('invoice_item_details.fees_name', '26')
            ->whereNotNull('invoices.ups_id') */
            ->groupBy('ups_details.id');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        if (!empty($paymentStatus)) {
            $filteredq = $filteredq->where('invoices.payment_status', $paymentStatus);
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                    ->orWhere('customs.file_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                    ->orWhere('customs.file_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $action = '<div class="dropdown">';
            $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';
            $action .= '<li>';
            if (!empty($value->invoicePaymentStatus) && ($value->invoicePaymentStatus == 'Pending' || $value->invoicePaymentStatus == 'Partial')) {
                $action .= '<a target="_blank" href="' . route('addupsinvoicepayment', [$value->upsId, $value->invoiceId, 0]) . '">Add Payment</a>';
            }
            $action .= '</li></ul></div>';
            $difference = $value->totalOfInvoice - $value->totalOfExpense;

            $data[] = [$value->upsFileNumber, !empty($value->customFileNumber) ? $value->customFileNumber : '-', !empty($value->invoiceDate) ? date('d-m-Y', strtotime($value->invoiceDate)) : '-', $value->invoicePaymentStatus, number_format($value->totalOfInvoice, 2), number_format($value->totalOfExpense, 2), number_format($difference, 2), !empty($value->consigneeName) ? $value->consigneeName : '-', $value->awbNumber, $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval(count($total)),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }



    public function getcustomreportdata()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $paymentStatus = $_POST['paymentStatus'];

        if (!empty($paymentStatus)) {

            $dataInvoices = DB::table('ups_details')
                ->select('ups_details.*')
                ->join('invoices', 'ups_details.id', '=', 'invoices.ups_id')
                ->join('invoice_item_details', 'invoices.id', '=', 'invoice_item_details.invoice_id')
                ->where('invoices.payment_status', $paymentStatus)
                ->whereBetween('invoices.date', array($fromDate, $toDate))
                ->where('ups_details.deleted', 0)->where('ups_details.courier_operation_type', '1')
                ->where('invoice_item_details.fees_name', '26')
                ->whereNotNull('invoices.ups_id')
                ->groupBy('invoices.ups_id')
                ->get();
        } else {

            $dataInvoices = DB::table('ups_details')
                ->select('ups_details.*')
                ->join('invoices', 'ups_details.id', '=', 'invoices.ups_id')
                ->join('invoice_item_details', 'invoices.id', '=', 'invoice_item_details.invoice_id')
                ->whereBetween('invoices.date', array($fromDate, $toDate))
                ->where('ups_details.deleted', 0)->where('ups_details.courier_operation_type', '1')
                ->where('invoice_item_details.fees_name', '26')
                ->whereNotNull('invoices.ups_id')
                ->groupBy('invoices.ups_id')
                ->get();
        }

        return view("reports.customreportajax", ['dataImportUpsFiles' => $dataInvoices]);
    }

    public function getallcustomreportdata()
    {

        $dataInvoices = DB::table('invoices')
            ->select(DB::raw('SUM(invoice_item_details.total_of_items) as total_invoice , ups_details.id,ups_details.file_number,ups_details.consignee_name,ups_details.consignee_address,ups_details.awb_number'))
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
            ->whereNotNull('invoices.ups_id')
            ->where('invoices.deleted', '0')
            ->where('invoice_item_details.fees_name', '26')
            ->groupBy('invoice_id')
            ->get();

        return view("reports.customreportajax", ['dataInvoices' => $dataInvoices]);
    }

    public function warehousereport()
    {
        return view("reports.warehousereport");
    }

    public function listwarehousereport(Request $request)
    {
        $req = $request->all();
        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['cargo.id', 'file_number', 'c1.company_name', '', 'user.name', 'awb_bl_no', '', 'warehouse.name', 'warehouse_status'];
        $total = Cargo::selectRaw('count(*) as total')->where('cargo.deleted', 0)->whereNull('file_close')->where('consolidate_flag', 1)->where('cargo_operation_type', '<>', '3');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $total = $total->where('cargo_operation_type', $fileType);
        }

        $query = DB::table('cargo')
            ->selectRaw('cargo.*,cargo.id as cargoId,c1.company_name as consigneeCompany,users.name as agentName,warehouse.name as warehouseName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'cargo.warehouse')
            ->where('cargo.deleted', 0)->whereNull('file_close')->where('consolidate_flag', 1)->where('cargo_operation_type', '<>', '3');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $query = $query->where('cargo_operation_type', $fileType);
        }

        $filteredq = DB::table('cargo')
            ->selectRaw('cargo.*,cargo.id as cargoId,c1.company_name as consigneeCompany,users.name as agentName,warehouse.name as warehouseName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'cargo.warehouse')
            ->where('cargo.deleted', 0)->whereNull('file_close')->where('consolidate_flag', 1)->where('cargo_operation_type', '<>', '3');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $filteredq = $filteredq->where('cargo_operation_type', $fileType);
        }

        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%')
                    ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                    ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%')
                    ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                    ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $invoices = Expense::getInvoicesOfFile($value->id);
            $dataHAWNo = app('App\HawbFiles')->getHawbFilesNumbers($value->id);

            $data[] = [$value->cargoId, $value->file_number, !empty($value->consigneeCompany) ? $value->consigneeCompany : '-', $invoices, !empty($value->agentName) ? $value->agentName : '-', !empty($value->awb_bl_no) ? $value->awb_bl_no : '-', !empty($dataHAWNo) ? $dataHAWNo : "-", !empty($value->warehouseName) ? $value->warehouseName : "-", !empty($value->warehouse_status) ? Config::get('app.warehouseStatus')[$value->warehouse_status] : '-'];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function warehousereportpdf()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileType = !empty($_POST['fileType']) ? $_POST['fileType'] : '';
        $warehouseData = DB::table('cargo')
            ->selectRaw('cargo.*,cargo.id as cargoId,c1.company_name as consigneeCompany,users.name as agentName,warehouse.name as warehouseName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id')
            ->leftJoin('warehouse', 'warehouse.id', '=', 'cargo.warehouse')
            ->where('cargo.deleted', 0)->whereNull('file_close')->where('consolidate_flag', 1)->where('cargo_operation_type', '<>', '3');
        if (!empty($fromDate) && !empty($toDate)) {
            $warehouseData = $warehouseData->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $warehouseData = $warehouseData->where('cargo_operation_type', $fileType);
        }
        $warehouseData = $warehouseData->orderBy('cargo.id', 'desc')->get();
        $pdf = PDF::loadView('reports.printwarehouseReports', ['warehouseData' => $warehouseData]);
        $pdf_file = 'printwarehouseReports.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Cargo/';
        $filecontent = file_get_contents($pdf_path);
        //$success = Storage::disk('s3')->put($s3path . 'Warehouse_Report.pdf', $filecontent, 'public');
        return url('/') . '/' . $pdf_path;
    }

    public function warehousereportcourier()
    {
        $warehouses = DB::table('warehouse')->where('deleted', 0)->where('warehouse_for', 'Courier')->pluck('name', 'id')->toArray();
        return view("reports.courier.warehousereport", ['warehouses' => $warehouses]);
    }

    public function listwarehousereportcourier(Request $request)
    {
        $req = $request->all();
        $courierType = $req['courierType'];
        $warehouse = $req['warehouse'];
        $fileType = $req['fileType'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];

        if ($courierType == 'UPS')
            $orderby = ['ups_details.id', 'ups_details.file_number', 'ups_details.awb_number', 'c1.company_name', 'c2.company_name', 'warehouse.name', 'warehouse_status'];
        else if ($courierType == 'Aeropost')
            $orderby = ['aeropost.id', 'aeropost.file_number', 'aeropost.tracking_no', 'c1.company_name', 'from_location', 'warehouse.name', 'warehouse_status'];
        else
            $orderby = ['ccpack.id', 'ccpack.file_number', 'ccpack.awb_number', 'c1.company_name', 'c2.company_name', 'warehouse.name', 'warehouse_status'];

        if ($courierType == 'UPS') {
            $total = Ups::selectRaw('count(*) as total')->where('ups_details.deleted', 0)->whereNull('file_close');
            if (!empty($fileType)) {
                $total = $total->where('courier_operation_type', $fileType);
            }
            if (!empty($warehouse)) {
                $total = $total->where('warehouse', $warehouse);
            }

            $query = DB::table('ups_details')
                ->selectRaw('ups_details.*,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'ups_details.warehouse')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close');
            if (!empty($fileType)) {
                $query = $query->where('courier_operation_type', $fileType);
            }
            if (!empty($warehouse)) {
                $query = $query->where('warehouse', $warehouse);
            }

            $filteredq = DB::table('ups_details')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'ups_details.warehouse')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close');
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('courier_operation_type', $fileType);
            }
            if (!empty($warehouse)) {
                $filteredq = $filteredq->where('warehouse', $warehouse);
            }
        } else if ($courierType == 'Aeropost') {
            $total = Aeropost::selectRaw('count(*) as total')->where('aeropost.deleted', '0')->whereNull('file_close');
            if (!empty($warehouse)) {
                $total = $total->where('warehouse', $warehouse);
            }

            $query = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'aeropost.warehouse')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close');
            if (!empty($warehouse)) {
                $query = $query->where('warehouse', $warehouse);
            }

            $filteredq = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'aeropost.warehouse')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close');
            if (!empty($warehouse)) {
                $filteredq = $filteredq->where('warehouse', $warehouse);
            }
        } else {
            $total = ccpack::selectRaw('count(*) as total')->where('ccpack.deleted', '0')->whereNull('file_close');
            if (!empty($warehouse)) {
                $total = $total->where('warehouse', $warehouse);
            }
            if (!empty($fileType)) {
                $total = $total->where('ccpack_operation_type', $fileType);
            }

            $query = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'ccpack.warehouse')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close');
            if (!empty($warehouse)) {
                $query = $query->where('warehouse', $warehouse);
            }
            if (!empty($fileType)) {
                $query = $query->where('ccpack_operation_type', $fileType);
            }

            $filteredq = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'ccpack.warehouse')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close');
            if (!empty($warehouse)) {
                $filteredq = $filteredq->where('warehouse', $warehouse);
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('ccpack_operation_type', $fileType);
            }
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
                }
                if ($courierType == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
                }
            });
            $filteredq->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
                }
                if ($courierType == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse.name', 'like', '%' . $search . '%')
                        ->orWhere('warehouse_status', array_search($search, Config::get('app.warehouseStatus')));
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {

            if ($courierType == 'UPS') {
                $moduleId = $value->upsId;
                $awbNo = $value->awb_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            } else if ($courierType == 'Aeropost') {
                $moduleId = $value->aeropostId;
                $awbNo = $value->tracking_no;
                $date = !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '-';
            } else {
                $moduleId = $value->ccpackId;
                $awbNo = $value->awb_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            }

            $data[] = [$moduleId, $value->file_number, !empty($awbNo) ? $awbNo : '-', $value->consigneeCompany, $value->shipperCompany, !empty($value->warehouseName) ? $value->warehouseName : "-", !empty($value->warehouse_status) ? Config::get('app.warehouseStatus')[$value->warehouse_status] : '-'];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function warehousereportcourierpdf()
    {
        $courierType = !empty($_POST['courierType']) ? $_POST['courierType'] : '';
        $fileType = !empty($_POST['fileType']) ? $_POST['fileType'] : '';
        $warehouse = !empty($_POST['warehouse']) ? $_POST['warehouse'] : '';

        if ($courierType == 'UPS') {
            $query = DB::table('ups_details')
                ->selectRaw('ups_details.*,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'ups_details.warehouse')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close');
            if (!empty($fileType)) {
                $query = $query->where('courier_operation_type', $fileType);
            }
            if (!empty($warehouse)) {
                $query = $query->where('warehouse', $warehouse);
            }
            $query = $query->orderBy('ups_details.id', 'desc')->get();
        } else if ($courierType == 'Aeropost') {
            $query = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'aeropost.warehouse')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close');
            if (!empty($warehouse)) {
                $query = $query->where('warehouse', $warehouse);
            }
            $query = $query->orderBy('aeropost.id', 'desc')->get();
        } else {
            $query = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,warehouse.name as warehouseName')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('warehouse', 'warehouse.id', '=', 'ccpack.warehouse')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close');
            if (!empty($warehouse)) {
                $query = $query->where('warehouse', $warehouse);
            }
            if (!empty($fileType)) {
                $query = $query->where('ccpack_operation_type', $fileType);
            }
            $query = $query->orderBy('ccpack.id', 'desc')->get();
        }

        $pdf = PDF::loadView('reports.courier.printwarehouseReports', ['data' => $query, 'courierType' => $courierType]);
        $pdf_file = 'printcourierwarehouseReports.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Courier/';
        $filecontent = file_get_contents($pdf_path);
        //$success = Storage::disk('s3')->put($s3path . 'Warehouse_Report.pdf', $filecontent, 'public');
        return url('/') . '/' . $pdf_path;
    }

    public function nonbilledfiles()
    {
        /* $invoices = DB::table('invoices')
            ->select(DB::raw('GROUP_CONCAT(DISTINCT cargo_id) AS cargoIds'))
            ->where('deleted','0')
            ->whereNotNull('cargo_id')
            ->first(); 
         $exploadedIds = explode(',', $invoices->cargoIds);   
            */

        /* $invoices = DB::table('invoices')
            ->select(DB::raw('cargo_id AS cargoIds'))
            ->where('deleted', '0')
            ->whereNotNull('cargo_id')
            ->get();
        foreach ($invoices as $k => $v) {
            $exploadedIds[] = $v->cargoIds;
        }

        $dataNonBilledFiles = DB::table('cargo')->where('deleted', 0)->whereNotIn('id', $exploadedIds)->get();
        return view("reports.nonbilledfilesreport", ['dataNonBilledFiles' => $dataNonBilledFiles]); */
        return view("reports.nonbilledfilesreport");
    }

    public function listnonbilledfiles(Request $request)
    {
        $req = $request->all();
        $cargoType = $req['cargoType'];
        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($cargoType == 'Cargo')
            $orderby = ['cargo.id', 'file_number', 'awb_bl_no', 'c1.company_name', 'c2.company_name'];
        else
            $orderby = ['hawb_files.id', 'file_number', 'hawb_hbl_no', 'c1.company_name', 'c2.company_name'];

        if ($cargoType == 'Cargo') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('cargo_id AS cargoIds'))
                ->where('deleted', '0')
                ->whereNotNull('cargo_id')
                ->whereNull('housefile_module')
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->cargoIds;
            }
            $total = Cargo::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereNotIn('cargo.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('cargo_operation_type', $fileType);
            }

            $query = DB::table('cargo')
                ->selectRaw('cargo.*,cargo.id as cargoId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->where('cargo.deleted', '0')
                ->whereNull('file_close')->whereNotIn('cargo.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('cargo_operation_type', $fileType);
            }

            $filteredq = DB::table('cargo')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->where('cargo.deleted', '0')
                ->whereNull('file_close')->whereNotIn('cargo.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('cargo_operation_type', $fileType);
            }
        } else {
            $invoices = DB::table('invoices')
                ->select(DB::raw('hawb_hbl_no AS houseIds'))
                ->where('deleted', '0')
                ->where('housefile_module', 'cargo')
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->houseIds;
            }
            $total = HawbFiles::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereNotIn('hawb_files.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('cargo_operation_type', $fileType);
            }

            $query = DB::table('hawb_files')
                ->selectRaw('hawb_files.*,hawb_files.id as houseId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->where('hawb_files.deleted', '0')
                ->whereNull('file_close')->whereNotIn('hawb_files.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('cargo_operation_type', $fileType);
            }

            $filteredq = DB::table('hawb_files')
                ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->where('hawb_files.deleted', '0')
                ->whereNull('file_close')->whereNotIn('hawb_files.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('cargo_operation_type', $fileType);
            }
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $cargoType) {
                if ($cargoType == 'Cargo') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $cargoType) {
                if ($cargoType == 'Cargo') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {

            if ($cargoType == 'Cargo') {
                $moduleId = $value->cargoId;
                $awbNo = $value->awb_bl_no;
            } else {
                $moduleId = $value->houseId;
                if ($value->cargo_operation_type == '1')
                    $awbNo = $value->hawb_hbl_no;
                else
                    $awbNo = $value->export_hawb_hbl_no;
            }
            $data[] = [$moduleId, !empty($value->opening_date) ? date('d-m-Y', strtotime($value->opening_date)) : '-', $value->file_number, !empty($awbNo) ? $awbNo : '-', $value->consigneeCompany, $value->shipperCompany];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }


    public function nonBilledFilesReportsPDF()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileType = !empty($_POST['fileType']) ? $_POST['fileType'] : '';
        $cargoType = !empty($_POST['cargoType']) ? $_POST['cargoType'] : '';

        if ($cargoType == 'Cargo') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('cargo_id AS cargoIds'))
                ->where('deleted', '0')
                ->whereNotNull('cargo_id')
                ->whereNull('housefile_module')
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->cargoIds;
            }

            $dataNonBilledFiles = DB::table('cargo')
                ->select(['cargo.*', 'c1.company_name as consigneeCompany', 'c2.company_name as shipperCompany'])
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->where('cargo.deleted', 0)->whereNull('file_close')->whereNotIn('cargo.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('cargo_operation_type', $fileType);
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('cargo.id', 'desc')->get();
        } else {
            $invoices = DB::table('invoices')
                ->select(DB::raw('hawb_hbl_no AS houseIds'))
                ->where('deleted', '0')
                ->where('housefile_module', 'cargo')
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->houseIds;
            }
            $dataNonBilledFiles = DB::table('hawb_files')
                ->select(['hawb_files.*', 'c1.company_name as consigneeCompany', 'c2.company_name as shipperCompany'])
                ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->where('hawb_files.deleted', 0)->whereNull('file_close')->whereNotIn('hawb_files.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('opening_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('cargo_operation_type', $fileType);
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('hawb_files.id', 'desc')->get();
        }

        $pdf = PDF::loadView('reports.printnonbilledfilesReports', ['dataNonBilledFiles' => $dataNonBilledFiles, 'fromDate' => $fromDate, 'toDate' => $toDate, 'cargoType' => $cargoType]);
        $pdf_file = 'printnonbilledfilesReports.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        /* $s3path = 'Files/Reports/Cargo/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'NonBilledFiles_Report.pdf', $filecontent, 'public'); */
        //return response()->file($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function nonbilledfilescourier()
    {
        return view("reports.courier.nonbilledfilesreport");
    }

    public function listnonbilledfilescourier(Request $request)
    {
        $req = $request->all();
        $courierType = $req['courierType'];
        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($courierType == 'UPS')
            $orderby = ['ups_details.id', 'ups_details.arrival_date', 'ups_details.file_number', 'master_file_number', 'c3.company_name', 'ups_scan_status', 'ups_details.awb_number', 'c1.company_name', 'c2.company_name', 'package_type', 'origin', 'weight', ''];
        else if ($courierType == 'upsMaster')
            $orderby = ['ups_master.id', 'ups_master.arrival_date', 'ups_master.file_number', '', 'c3.company_name', '', 'ups_master.tracking_number', 'c1.company_name', 'c2.company_name', '', '', 'weight', ''];
        else if ($courierType == 'Aeropost')
            $orderby = ['aeropost.id', 'aeropost.date', 'aeropost.file_number', 'master_file_number', 'c3.company_name', 'aeropost_scan_status', 'aeropost.tracking_no', 'c1.company_name', 'from_location', '', '', 'real_weight', ''];
        else if ($courierType == 'aeropostMaster')
            $orderby = ['aeropost_master.id', 'aeropost_master.arrival_date', 'aeropost_master.file_number', '', 'c3.company_name', '', 'aeropost_master.tracking_number', 'c1.company_name', 'c2.company_name', '', '', 'weight', ''];
        else if ($courierType == 'ccpackMaster')
            $orderby = ['ccpack_master.id', 'ccpack_master.arrival_date', 'ccpack_master.file_number', '', 'c3.company_name', '', 'ccpack_master.tracking_number', 'c1.company_name', 'c2.company_name', '', '', 'weight', ''];
        else
            $orderby = ['ccpack.id', 'ccpack.arrival_date', 'ccpack.file_number', 'master_file_number', 'c3.company_name', 'ccpack_scan_status', 'ccpack.awb_number', 'c1.company_name', 'c2.company_name', '', '', 'weight', ''];

        if ($courierType == 'UPS') {
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_id AS upsIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_id')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->upsIds;
            }
            $total = Ups::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereNotIn('ups_details.id', $exploadedIds)->where('ups_details.package_type', '!=', 'DOC');
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('courier_operation_type', $fileType);
            }

            $query = DB::table('ups_details')
                ->selectRaw('ups_details.*,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ups_details.id', $exploadedIds)->where('ups_details.package_type', '!=', 'DOC');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('courier_operation_type', $fileType);
            }

            $filteredq = DB::table('ups_details')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ups_details.id', $exploadedIds)->where('ups_details.package_type', '!=', 'DOC');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('courier_operation_type', $fileType);
            }
        } else if ($courierType == 'upsMaster') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_master_id AS upsMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_master_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->upsMasterIds;
            }
            $total = UpsMaster::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereNotIn('ups_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $query = DB::table('ups_master')
                ->selectRaw('ups_master.*,ups_master.id as upsMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ups_master.billing_party')
                ->where('ups_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ups_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('ups_master')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ups_master.billing_party')
                ->where('ups_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ups_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'Aeropost') {
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;

            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_id AS aeropostIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_id')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->aeropostIds;
            }
            $total = Aeropost::selectRaw('count(*) as total')->where('deleted', '0')->whereNull('file_close')->whereNotIn('aeropost.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('aeropost.date', array($fromDate, $toDate));
            }

            $query = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close')->whereNotIn('aeropost.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('aeropost.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('aeropost')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close')->whereNotIn('aeropost.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('aeropost.date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'aeropostMaster') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_master_id AS aeropostMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_master_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->aeropostMasterIds;
            }
            $total = AeropostMaster::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereNotIn('aeropost_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $query = DB::table('aeropost_master')
                ->selectRaw('aeropost_master.*,aeropost_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost_master.billing_party')
                ->where('aeropost_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('aeropost_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('aeropost_master')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost_master.billing_party')
                ->where('aeropost_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('aeropost_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'ccpackMaster') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_master_id AS ccpackMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_master_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->ccpackMasterIds;
            }
            $total = CcpackMaster::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereNotIn('ccpack_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $query = DB::table('ccpack_master')
                ->selectRaw('ccpack_master.*,ccpack_master.id as ccpackMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack_master.billing_party')
                ->where('ccpack_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ccpack_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('ccpack_master')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack_master.billing_party')
                ->where('ccpack_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ccpack_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_id AS ccpackIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->ccpackIds;
            }
            $total = ccpack::selectRaw('count(*) as total')->where('deleted', '0')->whereNull('file_close')->whereNotIn('ccpack.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('ccpack_operation_type', $fileType);
            }

            $query = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ccpack.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('ccpack_operation_type', $fileType);
            }

            $filteredq = DB::table('ccpack')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ccpack.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('ccpack_operation_type', $fileType);
            }
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'upsMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'aeropostMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'ccpackMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'upsMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'aeropostMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'ccpackMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c3.company_name', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        $data = [];
        foreach ($query as $key => $value) {

            if ($courierType == 'UPS') {
                $moduleId = $value->upsId;
                $awbNo = $value->awb_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
                $billingTerm = Ups::getBillingTerm($value->upsId);
                if ($value->package_type == 'LTR')
                    $packageType = 'Letter';
                else if ($value->package_type == 'DOC')
                    $packageType = 'Document';
                else
                    $packageType = 'Package';
                $origin = $value->origin;
                $weight = $value->weight;
                $fileStatus = !empty($value->ups_scan_status) ? (isset(Config::get('app.ups_new_scan_status')[$value->ups_scan_status]) ? Config::get('app.ups_new_scan_status')[$value->ups_scan_status] : '-') : '-';
            } else if ($courierType == 'upsMaster') {
                $moduleId = $value->upsMasterId;
                $awbNo = $value->tracking_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
                $billingTerm = '';
                $packageType = '';
                $origin = '';
                $weight = $value->weight;
                $fileStatus = '';
            } else if ($courierType == 'Aeropost') {
                $moduleId = $value->aeropostId;
                $awbNo = $value->tracking_no;
                $date = !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '-';
                $billingTerm = '';
                $packageType = '';
                $origin = '';
                $weight = $value->real_weight;
                $fileStatus = !empty($value->aeropost_scan_status) ? Config::get('app.ups_new_scan_status')[$value->aeropost_scan_status] : '-';
            } else if ($courierType == 'aeropostMaster') {
                $moduleId = $value->aeropostMasterId;
                $awbNo = $value->tracking_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
                $billingTerm = '';
                $packageType = '';
                $origin = '';
                $weight = $value->weight;
                $fileStatus = '';
            } else if ($courierType == 'ccpackMaster') {
                $moduleId = $value->ccpackMasterId;
                $awbNo = $value->tracking_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
                $billingTerm = '';
                $packageType = '';
                $origin = '';
                $weight = $value->weight;
                $fileStatus = '';
            } else {
                $moduleId = $value->ccpackId;
                $awbNo = $value->awb_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
                $billingTerm = '';
                $packageType = '';
                $origin = '';
                $weight = $value->weight;
                $fileStatus = !empty($value->ccpack_scan_status) ? Config::get('app.ups_new_scan_status')[$value->ccpack_scan_status] : '-';
            }
            $data[] = [$moduleId, $date, $value->file_number, !empty($value->master_file_number) ? $value->master_file_number : '-', !empty($value->billingParty) ? $value->billingParty : '-', $fileStatus, !empty($awbNo) ? $awbNo : '-', !empty($value->consigneeCompany) ? $value->consigneeCompany : '-', !empty($value->shipperCompany) ? $value->shipperCompany : '-', !empty($packageType) ? $packageType : '-', !empty($origin) ? $origin : '-', !empty($weight) ? $weight : '-', !empty($billingTerm) ? $billingTerm : '-'];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function nonBilledFilesCourierReportsPDF()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileType = !empty($_POST['fileType']) ? $_POST['fileType'] : '';
        $courierType = !empty($_POST['courierType']) ? $_POST['courierType'] : '';

        if ($courierType == 'UPS') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_id AS upsIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->upsIds;
            }

            $dataNonBilledFiles = DB::table('ups_details')
                ->selectRaw('ups_details.*,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ups_details.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('courier_operation_type', $fileType);
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('ups_details.id', 'desc')->get();
        } else if ($courierType == 'upsMaster') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_master_id AS upsMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_master_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->upsMasterIds;
            }

            $dataNonBilledFiles = DB::table('ups_master')
                ->selectRaw('ups_master.*,ups_master.id as upsMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ups_master.billing_party')
                ->where('ups_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ups_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('ups_operation_type', $fileType);
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('ups_master.id', 'desc')->get();
        } else if ($courierType == 'Aeropost') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_id AS aeropostIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->aeropostIds;
            }

            $dataNonBilledFiles = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close')->whereNotIn('aeropost.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('aeropost.date', array($fromDate, $toDate));
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('aeropost.id', 'desc')->get();
        } else if ($courierType == 'aeropostMaster') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_master_id AS aeropostMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_master_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->aeropostMasterIds;
            }

            $dataNonBilledFiles = DB::table('aeropost_master')
                ->selectRaw('aeropost_master.*,aeropost_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost_master.billing_party')
                ->where('aeropost_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('aeropost_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('aeropost_master.id', 'desc')->get();
        } else if ($courierType == 'ccpackMaster') {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_master_id AS ccpackMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_master_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->ccpackMasterIds;
            }

            $dataNonBilledFiles = DB::table('ccpack_master')
                ->selectRaw('ccpack_master.*,ccpack_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack_master.billing_party')
                ->where('ccpack_master.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ccpack_master.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('ccpack_master.id', 'desc')->get();
        } else {
            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_id AS ccpackIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_id')
                ->get();
            $exploadedIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedIds[] = $v->ccpackIds;
            }
            $dataNonBilledFiles = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany,c3.company_name as billingParty')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close')->whereNotIn('ccpack.id', $exploadedIds);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('ccpack_operation_type', $fileType);
            }
            $dataNonBilledFiles = $dataNonBilledFiles->orderBy('ccpack.id', 'desc')->get();
        }

        $pdf = PDF::loadView('reports.courier.printnonbilledfilesReports', ['dataNonBilledFiles' => $dataNonBilledFiles, 'fromDate' => $fromDate, 'toDate' => $toDate, 'courierType' => $courierType]);
        $pdf_file = 'printnonbilledfilesCourierReports.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        /* $s3path = 'Files/Reports/Cargo/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'NonBilledFiles_Report.pdf', $filecontent, 'public'); */
        //return response()->file($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function filesWithExpenseNoInvoices($module)
    {
        return view("reports.filesWithExpenseNoInvoices");
    }

    public function listfilesWithExpenseNoInvoices(Request $request)
    {
        $req = $request->all();
        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['cargo.id', 'file_number', 'awb_bl_no', 'c1.company_name', 'c2.company_name'];

        $expenses = DB::table('expenses')
            ->select(DB::raw('cargo_id AS cargoIds'))
            ->where('deleted', '0')
            ->whereNotNull('cargo_id')
            ->get();
        foreach ($expenses as $k => $v) {
            $exploadedExpenseIds[] = $v->cargoIds;
        }

        $invoices = DB::table('invoices')
            ->select(DB::raw('cargo_id AS cargoIds'))
            ->where('deleted', '0')
            ->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            ->get();
        foreach ($invoices as $k => $v) {
            $exploadedInvoiceIds[] = $v->cargoIds;
        }

        $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);
        $total = Cargo::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('cargo.id', $fArray);
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $total = $total->where('cargo_operation_type', $fileType);
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('cargo')
            ->selectRaw('cargo.*,cargo.id as cargoId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->where('cargo.deleted', '0')
            ->whereNull('file_close')->whereIn('cargo.id', $fArray);
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $query = $query->where('cargo_operation_type', $fileType);
        }
        $filteredq = DB::table('cargo')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->where('cargo.deleted', '0')
            ->whereNull('file_close')->whereIn('cargo.id', $fArray);
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $filteredq = $filteredq->where('cargo_operation_type', $fileType);
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {

            $data[] = [$value->cargoId, !empty($value->opening_date) ? date('d-m-Y', strtotime($value->opening_date)) : '-', $value->file_number, !empty($value->awb_bl_no) ? $value->awb_bl_no : '-', $value->consigneeCompany, $value->shipperCompany];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function filesWithExpenseNoInvoicesPDF()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileType = !empty($_POST['fileType']) ? $_POST['fileType'] : '';

        $expenses = DB::table('expenses')
            ->select(DB::raw('cargo_id AS cargoIds'))
            ->where('deleted', '0')
            ->whereNotNull('cargo_id')
            ->get();
        foreach ($expenses as $k => $v) {
            $exploadedExpenseIds[] = $v->cargoIds;
        }

        $invoices = DB::table('invoices')
            ->select(DB::raw('cargo_id AS cargoIds'))
            ->where('deleted', '0')
            ->whereNotNull('cargo_id')
            ->whereNull('housefile_module')
            ->get();
        foreach ($invoices as $k => $v) {
            $exploadedInvoiceIds[] = $v->cargoIds;
        }

        $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

        $dataNonBilledFiles = DB::table('cargo')->where('deleted', 0)->whereNull('file_close')->whereIn('id', $fArray);
        if (!empty($fromDate) && !empty($toDate)) {
            $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($fileType)) {
            $dataNonBilledFiles = $dataNonBilledFiles->where('cargo_operation_type', $fileType);
        }

        $dataNonBilledFiles = $dataNonBilledFiles->orderBy('id', 'desc')->get();

        $pdf = PDF::loadView('reports.printfilesWithExpenseNoInvoices', ['dataNonBilledFiles' => $dataNonBilledFiles, 'fromDate' => $fromDate, 'toDate' => $toDate]);
        $pdf_file = 'printfilesWithExpenseNoInvoices.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        /* $s3path = 'Files/Reports/Cargo/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'NonBilledFiles_Report.pdf', $filecontent, 'public'); */
        //return response()->file($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function filesWithExpenseNoInvoicesCourier($module)
    {
        return view("reports.courier.filesWithExpenseNoInvoices");
    }

    public function listfilesWithExpenseNoInvoicesCourier(Request $request)
    {
        $req = $request->all();
        $courierType = $req['courierType'];
        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($courierType == 'UPS')
            $orderby = ['ups_details.id', 'ups_details.arrival_date', 'ups_details.file_number', 'ups_details.awb_number', 'c1.company_name', 'c2.company_name'];
        else if ($courierType == 'upsMaster')
            $orderby = ['ups_master.id', 'ups_master.arrival_date', 'ups_master.file_number', 'ups_master.tracking_number', 'c1.company_name', 'c2.company_name'];
        else if ($courierType == 'Aeropost')
            $orderby = ['aeropost.id', 'aeropost.date', 'aeropost.file_number', 'aeropost.tracking_no', 'c1.company_name', 'from_location'];
        else if ($courierType == 'aeropostMaster')
            $orderby = ['aeropost_master.id', 'aeropost_master.arrival_date', 'aeropost_master.file_number', 'aeropost_master.tracking_number', 'c1.company_name', 'c2.company_name'];
        else if ($courierType == 'ccpackMaster')
            $orderby = ['ccpack_master.id', 'ccpack_master.arrival_date', 'ccpack_master.file_number', 'ccpack_master.tracking_number', 'c1.company_name', 'c2.company_name'];
        else
            $orderby = ['ccpack.id', 'ccpack.arrival_date', 'ccpack.file_number', 'ccpack.awb_number', 'c1.company_name', 'c2.company_name'];

        if ($courierType == 'UPS') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ups_details_id AS upsIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_details_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->upsIds;
            }

            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;

            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_id AS upsIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_id')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->upsIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $total = Ups::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('ups_details.id', $fArray)->where('ups_details.package_type', '!=', 'DOC');
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('courier_operation_type', $fileType);
            }

            $query = DB::table('ups_details')
                ->selectRaw('ups_details.*,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close')->whereIn('ups_details.id', $fArray)->where('ups_details.package_type', '!=', 'DOC');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('courier_operation_type', $fileType);
            }

            $filteredq = DB::table('ups_details')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', '0')
                ->whereNull('file_close')->whereIn('ups_details.id', $fArray)->where('ups_details.package_type', '!=', 'DOC');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('courier_operation_type', $fileType);
            }
        } else if ($courierType == 'upsMaster') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ups_master_id AS upsMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_master_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->upsMasterIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_master_id AS upsMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_master_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->upsMasterIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $total = UpsMaster::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('ups_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('ups_operation_type', $fileType);
            }

            $query = DB::table('ups_master')
                ->selectRaw('ups_master.*,ups_master.id as upsMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->where('ups_master.deleted', '0')
                ->whereNull('file_close')->whereIn('ups_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('ups_operation_type', $fileType);
            }

            $filteredq = DB::table('ups_master')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->where('ups_master.deleted', '0')
                ->whereNull('file_close')->whereIn('ups_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('ups_operation_type', $fileType);
            }
        } else if ($courierType == 'Aeropost') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('aeropost_id AS aeropostIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->aeropostIds;
            }

            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_id AS aeropostIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_id')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->aeropostIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);
            $total = Aeropost::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('aeropost.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('aeropost.date', array($fromDate, $toDate));
            }

            $query = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close')->whereIn('aeropost.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('aeropost.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('aeropost')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close')->whereIn('aeropost.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('aeropost.date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'aeropostMaster') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('aeropost_master_id AS aeropostMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_master_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->aeropostMasterIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_master_id AS aeropostMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_master_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->aeropostMasterIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $total = AeropostMaster::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('aeropost_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $query = DB::table('aeropost_master')
                ->selectRaw('aeropost_master.*,aeropost_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->where('aeropost_master.deleted', '0')
                ->whereNull('file_close')->whereIn('aeropost_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('aeropost_master')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->where('aeropost_master.deleted', '0')
                ->whereNull('file_close')->whereIn('aeropost_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'ccpackMaster') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ccpack_master_id AS ccpackMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_master_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->ccpackMasterIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_master_id AS ccpackMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_master_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->ccpackMasterIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $total = CcpackMaster::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('ccpack_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $query = DB::table('ccpack_master')
                ->selectRaw('ccpack_master.*,ccpack_master.id as ccpackMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->where('ccpack_master.deleted', '0')
                ->whereNull('file_close')->whereIn('ccpack_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('ccpack_master')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->where('ccpack_master.deleted', '0')
                ->whereNull('file_close')->whereIn('ccpack_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ccpack_id AS ccpackIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->ccpackIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_id AS ccpackIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->ccpackIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);
            $total = ccpack::selectRaw('count(*) as total')->where('deleted', 0)->whereNull('file_close')->whereIn('ccpack.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $total = $total->where('ccpack_operation_type', $fileType);
            }

            $query = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close')->whereIn('ccpack.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $query = $query->where('ccpack_operation_type', $fileType);
            }

            $filteredq = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close')->whereIn('ccpack.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $filteredq = $filteredq->where('ccpack_operation_type', $fileType);
            }
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'upsMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%');
                } else if ($courierType == 'aeropostMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'ccpackMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $courierType) {
                if ($courierType == 'UPS') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'upsMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%');
                } else if ($courierType == 'aeropostMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else if ($courierType == 'ccpackMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                } else {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        $data = [];
        foreach ($query as $key => $value) {

            if ($courierType == 'UPS') {
                $moduleId = $value->upsId;
                $awbNo = $value->awb_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            } else if ($courierType == 'upsMaster') {
                $moduleId = $value->upsMasterId;
                $awbNo = $value->tracking_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            } else if ($courierType == 'Aeropost') {
                $moduleId = $value->aeropostId;
                $awbNo = $value->tracking_no;
                $date = !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '-';
            } else if ($courierType == 'aeropostMaster') {
                $moduleId = $value->aeropostMasterId;
                $awbNo = $value->tracking_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            } else if ($courierType == 'ccpackMaster') {
                $moduleId = $value->ccpackMasterId;
                $awbNo = $value->tracking_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            } else {
                $moduleId = $value->ccpackId;
                $awbNo = $value->awb_number;
                $date = !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-';
            }
            $data[] = [$moduleId, $date, $value->file_number, !empty($awbNo) ? $awbNo : '-', $value->consigneeCompany, $value->shipperCompany];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function filesWithExpenseNoInvoicesCourierPDF()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileType = !empty($_POST['fileType']) ? $_POST['fileType'] : '';
        $courierType = !empty($_POST['courierType']) ? $_POST['courierType'] : '';

        if ($courierType == 'UPS') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ups_details_id AS upsIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_details_id')
                ->get();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->upsIds;
            }

            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;

            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_id AS upsIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_id')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->upsIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $dataNonBilledFiles = DB::table('ups_details')
                ->selectRaw('ups_details.*,ups_details.id as upsId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', 0)->whereNull('file_close')->whereIn('ups_details.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('courier_operation_type', $fileType);
            }
        } else if ($courierType == 'upsMaster') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ups_master_id AS upsMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_master_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->upsMasterIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('ups_master_id AS upsMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ups_master_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->upsMasterIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $dataNonBilledFiles = DB::table('ups_master')
                ->selectRaw('ups_master.*,ups_master.id as upsMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->where('ups_master.deleted', 0)->whereNull('file_close')->whereIn('ups_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('ups_operation_type', $fileType);
            }
        } else if ($courierType == 'Aeropost') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('aeropost_id AS aeropostIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_id')
                ->get();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->aeropostIds;
            }

            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_id AS aeropostIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_id')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->aeropostIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);
            $dataNonBilledFiles = DB::table('aeropost')
                ->selectRaw('aeropost.*,aeropost.id as aeropostId,c1.company_name as consigneeCompany,from_location as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->where('aeropost.deleted', '0')->whereNull('file_close')->whereIn('aeropost.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('aeropost.date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'aeropostMaster') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('aeropost_master_id AS aeropostMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_master_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->aeropostMasterIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('aeropost_master_id AS aeropostMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('aeropost_master_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->aeropostMasterIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $dataNonBilledFiles = DB::table('aeropost_master')
                ->selectRaw('aeropost_master.*,aeropost_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->where('aeropost_master.deleted', 0)->whereNull('file_close')->whereIn('aeropost_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else if ($courierType == 'ccpackMaster') {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ccpack_master_id AS ccpackMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_master_id')
                ->get();
            $exploadedExpenseIds = array();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->ccpackMasterIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_master_id AS ccpackMasterIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_master_id')
                ->get();
            $exploadedInvoiceIds = array();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->ccpackMasterIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);

            $dataNonBilledFiles = DB::table('ccpack_master')
                ->selectRaw('ccpack_master.*,ccpack_master.id as aeropostMasterId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->join('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->where('ccpack_master.deleted', 0)->whereNull('file_close')->whereIn('ccpack_master.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('arrival_date', array($fromDate, $toDate));
            }
        } else {
            $expenses = DB::table('expenses')
                ->select(DB::raw('ccpack_id AS ccpackIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_id')
                ->get();
            foreach ($expenses as $k => $v) {
                $exploadedExpenseIds[] = $v->ccpackIds;
            }

            $invoices = DB::table('invoices')
                ->select(DB::raw('ccpack_id AS ccpackIds'))
                ->where('deleted', '0')
                ->whereNotNull('ccpack_id')
                ->get();
            foreach ($invoices as $k => $v) {
                $exploadedInvoiceIds[] = $v->ccpackIds;
            }

            $fArray  = array_diff($exploadedExpenseIds, $exploadedInvoiceIds);
            $dataNonBilledFiles = DB::table('ccpack')
                ->selectRaw('ccpack.*,ccpack.id as ccpackId,c1.company_name as consigneeCompany,c2.company_name as shipperCompany')
                ->join('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->join('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close')->whereIn('ccpack.id', $fArray);
            if (!empty($fromDate) && !empty($toDate)) {
                $dataNonBilledFiles = $dataNonBilledFiles->whereBetween('ccpack.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($fileType)) {
                $dataNonBilledFiles = $dataNonBilledFiles->where('ccpack_operation_type', $fileType);
            }
        }
        $dataNonBilledFiles = $dataNonBilledFiles->orderBy('id', 'desc')->get();

        $pdf = PDF::loadView('reports.courier.printfilesWithExpenseNoInvoices', ['dataNonBilledFiles' => $dataNonBilledFiles, 'fromDate' => $fromDate, 'toDate' => $toDate, 'courierType' => $courierType]);
        $pdf_file = 'printCourierfilesWithExpenseNoInvoices.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        /* $s3path = 'Files/Reports/Cargo/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'NonBilledFiles_Report.pdf', $filecontent, 'public'); */
        //return response()->file($pdf_path);
        return url('/') . '/' . $pdf_path;
    }


    public function cashierReport()
    {
        $cashierDetail = DB::table('users')->where('department', 11)->where('deleted', '0')->get();
        return view('reports.cashierReport', compact('cashierDetail'));
    }

    public function cashierAllDetail($id)
    {
        $cashierDetail = DB::table('users')->where('id', $id)->get();
        $paymentReceivedByCashier = DB::table('invoice_payments')
            ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('payment_accepted_by', $id)->where('invoice_payments.deleted', 0)->get();
        $i = 0;
        $count = count($paymentReceivedByCashier);
        $modelInvoices = new Invoices;
        $totalOfHTG = 0;
        $totalOfUSD = 0;
        $totalOfCurrency[1] = '0.00';
        $totalOfCurrency[3] = '0.00';
        for ($i = 0; $i < $count; $i++) {
            if (!empty($paymentReceivedByCashier[$i]->exchange_currency)) {
                if ($paymentReceivedByCashier[$i]->exchange_currency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfHTG;
                }
            } else {
                $paymentReceivedByCashier[$i]->exchange_currency = $paymentReceivedByCashier[$i]->invoiceCurrency;
                if ($paymentReceivedByCashier[$i]->invoiceCurrency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfHTG;
                }
            }
            /*$paymentReceivedByCashier[$i]->total = DB::table('invoice_payments')
        ->where('invoice_id','=',$paymentReceivedByCashier[$i]->invoice_id)
        ->where('payment_accepted_by',$id)
        ->sum('amount');*/
        }

        $expancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)->get()->toArray();

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfHtgCount = $totalExpenseOfHtg->total;


        $totalExpenseOfUSD = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSDCount = $totalExpenseOfUSD->total;

        /* $expancesDetailOl = array();
        foreach ($expancesDetail as $key => $row) {
            // replace 0 with the field's index/key
            $expancesDetailOl[$key] = $row->cargo_id;
        }
        
        array_multisort((array)$expancesDetailOl, SORT_DESC,$expancesDetail); */

        /*$fileDetail = DB::table('expenses')
        ->leftJoin('cargo', 'expenses.file_number', '=', 'cargo.id')
        ->where('expenses.disbursed_by',$id)->get();*/
        $countEx = count($expancesDetail);
        //$countExF = count($fileDetail);
        // $arr = [];
        // for($i = 0;$i<$countEx;$i++){
        //     if(!in_array($expancesDetail[$i]->expense_id,$arr)){
        //         $expancesDetail->file_number = $fileDetail[$i]->file_number;
        //         array_push($arr,$expancesDetail[$i]->expense_id);
        //     }
        // }
        //$cashCreditAc = DB::table('expenses')->where('cashier_id',$id)->get();
        /*for($i = 0;$i<$countEx;$i++){
        //$expancesDetail[$i]->file_number = $fileDetail[$i]->file_number;
        //$expancesDetail[$i]->cash_credit_account = $cashCreditAc[$i]->cash_credit_account;
        $expancesDetail[$i]->total = DB::table('expenses')
        ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
        ->where('expenses.cashier_id',$id)
        ->where('expenses.expense_id','=',$expancesDetail[$i]->expense_id)
        ->sum('amount');
        }*/

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        $htgTousd = $totalOfCurrency[1] * $exchangeRateOfUsdToHTH->exchangeRate;
        $totalOfCurrency['total'] = $totalOfCurrency[3] + $htgTousd;

        $localInvoicePaymentDetail = DB::table('local_invoice_payment_detail')->select(['local_invoice_payment_detail.*', 'cargo.*'])->join('cargo', 'local_invoice_payment_detail.local_invoice_id', '=', 'cargo.id')->where('local_invoice_payment_detail.updated_by', $id)->get();
        $totalAcceptedRent = DB::table('local_invoice_payment_detail')->where('updated_by', $id)->sum('total');

        return view('reports.cashierAllDetail', compact('cashierDetail', 'paymentReceivedByCashier', 'total', 'count', 'expancesDetail', 'countEx', 'totalOfCurrency', 'localInvoicePaymentDetail', 'totalAcceptedRent', 'totalExpenseOfHtgCount', 'totalExpenseOfUSDCount'));
    }

    public function filterreceivedpaymentbydate()
    {

        if (!empty($_POST['date'])) {
            $date = date('Y-m-d', strtotime($_POST['date']));
        } else {
            $date = '';
        }

        $id = $_POST['cashierId'];

        if (empty($date)) {
            $paymentReceivedByCashier = DB::table('invoice_payments')
                ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoice_payments.payment_accepted_by', $id)->where('invoice_payments.deleted', 0)
                ->get();

            $i = 0;
            $count = count($paymentReceivedByCashier);
            $modelInvoices = new Invoices;
            $totalOfHTG = 0;
            $totalOfUSD = 0;
            $totalOfCurrency[1] = '0.00';
            $totalOfCurrency[3] = '0.00';
            for ($i = 0; $i < $count; $i++) {
                if (!empty($paymentReceivedByCashier[$i]->exchange_currency)) {
                    if ($paymentReceivedByCashier[$i]->exchange_currency == 1) {
                        $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                    } else {
                        $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                    }
                } else {
                    $paymentReceivedByCashier[$i]->exchange_currency = $paymentReceivedByCashier[$i]->invoiceCurrency;
                    if ($paymentReceivedByCashier[$i]->invoiceCurrency == 1) {
                        $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfUSD;
                    } else {
                        $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfHTG;
                    }
                }
                /*$paymentReceivedByCashier[$i]->total = DB::table('invoice_payments')
            ->where('invoice_id','=',$paymentReceivedByCashier[$i]->invoice_id)
            ->where('payment_accepted_by',$id)
            ->sum('amount');*/
            }
        } else {
            $paymentReceivedByCashier = DB::table('invoice_payments')
                ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoice_payments.payment_accepted_by', $id)->where('invoice_payments.deleted', 0)
                ->where('invoice_payments.created_at', 'LIKE', "$date%")
                ->get();

            $i = 0;
            $count = count($paymentReceivedByCashier);
            $modelInvoices = new Invoices;
            $totalOfHTG = 0;
            $totalOfUSD = 0;
            $totalOfCurrency = array();
            for ($i = 0; $i < $count; $i++) {
                if (!empty($paymentReceivedByCashier[$i]->exchange_currency)) {
                    if ($paymentReceivedByCashier[$i]->exchange_currency == 1) {
                        $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                    } else {
                        $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                    }
                } else {
                    $paymentReceivedByCashier[$i]->exchange_currency = $paymentReceivedByCashier[$i]->invoiceCurrency;
                    if ($paymentReceivedByCashier[$i]->invoiceCurrency == 1) {
                        $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfUSD;
                    } else {
                        $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                        $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfHTG;
                    }
                }
                /*$paymentReceivedByCashier[$i]->total = DB::table('invoice_payments')
            ->where('invoice_id','=',$paymentReceivedByCashier[$i]->invoice_id)
            ->where('payment_accepted_by',$id)
            ->sum('amount');*/
            }
        }

        return view("reports.getfilterdatareceivedpaymentbydate", ['paymentReceivedByCashier' => $paymentReceivedByCashier]);
    }

    public function gettotalsincurrencies()
    {
        if (!empty($_POST['date'])) {
            $date = date('Y-m-d', strtotime($_POST['date']));
        } else {
            $date = '';
        }

        $id = $_POST['cashierId'];
        if (empty($date)) {
            $paymentReceivedByCashier = DB::table('invoice_payments')
                ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoice_payments.payment_accepted_by', $id)
                ->get();
        } else {
            $paymentReceivedByCashier = DB::table('invoice_payments')
                ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoice_payments.payment_accepted_by', $id)
                ->where('invoice_payments.created_at', 'LIKE', "$date%")
                ->get();
        }
        $count = count($paymentReceivedByCashier);
        $modelInvoices = new Invoices;
        $totalOfHTG = 0;
        $totalOfUSD = 0;
        $totalOfCurrency[1] = '0.00';
        $totalOfCurrency[3] = '0.00';
        for ($i = 0; $i < $count; $i++) {
            if (!empty($paymentReceivedByCashier[$i]->exchange_currency)) {
                if ($paymentReceivedByCashier[$i]->exchange_currency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = number_format($totalOfUSD, 2);
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = number_format($totalOfHTG, 2);
                }
            } else {
                $paymentReceivedByCashier[$i]->exchange_currency = $paymentReceivedByCashier[$i]->invoiceCurrency;
                if ($paymentReceivedByCashier[$i]->invoiceCurrency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = number_format($totalOfUSD, 2);
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = number_format($totalOfHTG, 2);
                }
            }
        }
        $htgTousd = $totalOfCurrency[1] * 83.97;
        $totalOfCurrency['total'] = $totalOfCurrency[3] + $htgTousd;
        return json_encode($totalOfCurrency);
    }

    public function filterdisbursementbydate()
    {

        if (!empty($_POST['date'])) {
            $date = date('Y-m-d', strtotime($_POST['date']));
        } else {
            $date = '';
        }

        $id = $_POST['cashierId'];

        if (empty($date)) {
            $expancesDetail = DB::table('expenses')
                ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
                ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->where('expenses.disbursed_by', $id)->where('expenses.deleted', 0)->get();
        } else {
            $expancesDetail = DB::table('expenses')
                ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
                ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->where('expenses.disbursed_by', $id)->where('expenses.deleted', 0)
                ->where('expenses.disbursed_datetime', 'LIKE', "$date%")
                ->get();
        }

        return view("reports.getfilterdatadisbursementbydate", ['expancesDetail' => $expancesDetail]);
    }

    public function filterlocalrentbydate(Request $request)
    {
        $id = $request->get('cashierId');
        if (!empty($request->get('date'))) {

            $date = date('Y-m-d', strtotime($request->get('date')));
            $localInvoicePaymentFilterDetail = DB::table('local_invoice_payment_detail')->select(['local_invoice_payment_detail.*', 'cargo.*'])->join('cargo', 'local_invoice_payment_detail.local_invoice_id', '=', 'cargo.id')->where('local_invoice_payment_detail.updated_by', $id)->where('local_invoice_payment_detail.updated_at', 'LIKE', $date . '%')->orderBy('local_invoice_payment_detail.id', 'DESC')->get();
            //pre($localInvoicePaymentDetail);
            $totalAcceptedRent = DB::table('local_invoice_payment_detail')->where('updated_by', $id)->where('local_invoice_payment_detail.updated_at', 'LIKE', $date . '%')->sum('total');
        } else {

            $date = '';
            $localInvoicePaymentFilterDetail = DB::table('local_invoice_payment_detail')->select(['local_invoice_payment_detail.*', 'cargo.*'])->join('cargo', 'local_invoice_payment_detail.local_invoice_id', '=', 'cargo.id')->where('local_invoice_payment_detail.updated_by', $id)->orderBy('local_invoice_payment_detail.id', 'DESC')->get();
            $totalAcceptedRent = DB::table('local_invoice_payment_detail')->where('updated_by', $id)->sum('total');
        }

        //pre($date);

        return view("reports.getfilterlocalrentalbydate", ['localInvoicePaymentFilterDetail' => $localInvoicePaymentFilterDetail, 'totalAcceptedRent' => $totalAcceptedRent]);
    }
    public function gettotalsofdisbursement()
    {
        if (!empty($_POST['date'])) {
            $date = date('Y-m-d', strtotime($_POST['date']));
        } else {
            $date = '';
        }

        $id = $_POST['cashierId'];
        if (empty($date)) {
            $dataAll = array();
            $expancesDetail = DB::table('expenses')
                ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
                ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->where('expenses.disbursed_by', $id)->where('expenses.expense_request', 'Disbursement done')->get();

            $totalPayment = '0.00';
            foreach ($expancesDetail as $expancesDetailCount) {
                $totalPayment += $expancesDetailCount->amount;
            }

            $dataAll['totalPayment'] = number_format($totalPayment, 2);

            $totalExpenseOfHtg = DB::table('expenses')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
                ->join('currency', 'currency.id', '=', 'vendors.currency')
                ->select(DB::raw('sum(expense_details.amount) as total'))
                ->where('expenses.expense_request', 'Disbursement done')
                ->where('expenses.deleted', 0)
                ->where('expenses.disbursed_by', $id)
                ->where('currency.code', 'HTG')
                ->where('expense_details.deleted', '0')
                //->groupBy('expenses.cargo_id')
                ->get()
                ->first();
            $totalExpenseOfHtgCount = $totalExpenseOfHtg->total;
            $dataAll['totalExpenseOfHtgCount'] = number_format($totalExpenseOfHtgCount, 2);


            $totalExpenseOfUSD = DB::table('expenses')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
                ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
                ->select(DB::raw('sum(expense_details.amount) as total'))
                ->where('expenses.expense_request', 'Disbursement done')
                ->where('expenses.deleted', 0)
                ->where('expenses.disbursed_by', $id)
                ->where('currency.code', 'USD')
                ->where('expense_details.deleted', '0')
                //->groupBy('expenses.cargo_id')
                ->get()
                ->first();
            $totalExpenseOfUSDCount = $totalExpenseOfUSD->total;
            $dataAll['totalExpenseOfUSDCount'] = number_format($totalExpenseOfUSDCount, 2);
        } else {
            $dataAll = array();
            $expancesDetail = DB::table('expenses')
                ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
                ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->where('expenses.disbursed_by', $id)
                ->where('expenses.expense_request', 'Disbursement done')
                ->where('expenses.disbursed_datetime', 'LIKE', "$date%")
                ->get();

            $totalPayment = '0.00';
            foreach ($expancesDetail as $expancesDetailCount) {
                $totalPayment += $expancesDetailCount->amount;
            }

            $dataAll['totalPayment'] = number_format($totalPayment, 2);

            $totalExpenseOfHtg = DB::table('expenses')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
                ->join('currency', 'currency.id', '=', 'vendors.currency')
                ->select(DB::raw('sum(expense_details.amount) as total'))
                ->where('expenses.expense_request', 'Disbursement done')
                ->where('expenses.deleted', 0)
                ->where('expenses.disbursed_by', $id)
                ->where('currency.code', 'HTG')
                ->where('expense_details.deleted', '0')
                ->where('expenses.disbursed_datetime', 'LIKE', "$date%")
                //->groupBy('expenses.cargo_id')
                ->get()
                ->first();
            $totalExpenseOfHtgCount = $totalExpenseOfHtg->total;
            $dataAll['totalExpenseOfHtgCount'] = number_format($totalExpenseOfHtgCount, 2);


            $totalExpenseOfUSD = DB::table('expenses')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
                ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
                ->select(DB::raw('sum(expense_details.amount) as total'))
                ->where('expenses.expense_request', 'Disbursement done')
                ->where('expenses.deleted', 0)
                ->where('expenses.disbursed_by', $id)
                ->where('currency.code', 'USD')
                ->where('expense_details.deleted', '0')
                ->where('expenses.disbursed_datetime', 'LIKE', "$date%")
                //->groupBy('expenses.cargo_id')
                ->get()
                ->first();
            $totalExpenseOfUSDCount = $totalExpenseOfUSD->total;
            $dataAll['totalExpenseOfUSDCount'] = number_format($totalExpenseOfUSDCount, 2);
        }
        return json_encode($dataAll);
    }

    public function printCashierReport($id, $date = '')
    {

        $cashierDetail = DB::table('users')->where('id', $id)->get();
        $paymentReceivedByCashier = DB::table('invoice_payments')
            ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('payment_accepted_by', $id)->get();
        if ($date != '') {
            $date = date('Y-m-d', strtotime($date));
            $paymentReceivedByCashier = DB::table('invoice_payments')
                ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
                ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('payment_accepted_by', $id)->where('invoice_payments.created_at', 'like', $date . '%')->get();
            if ($paymentReceivedByCashier == '') {
                $paymentReceivedByCashier = DB::table('invoice_payments')
                    ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount'])
                    ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where('payment_accepted_by', $id)->get();
            }
        }

        $i = 0;
        $count = count($paymentReceivedByCashier);
        $modelInvoices = new Invoices;
        $totalOfHTG = 0;
        $totalOfUSD = 0;
        $totalOfCurrency[1] = '0.00';
        $totalOfCurrency[3] = '0.00';
        for ($i = 0; $i < $count; $i++) {
            if (!empty($paymentReceivedByCashier[$i]->exchange_currency)) {
                if ($paymentReceivedByCashier[$i]->exchange_currency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                }
            } else {
                $paymentReceivedByCashier[$i]->exchange_currency = $paymentReceivedByCashier[$i]->invoiceCurrency;
                if ($paymentReceivedByCashier[$i]->invoiceCurrency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfHTG;
                }
            }
            /*$paymentReceivedByCashier[$i]->total = DB::table('invoice_payments')
        ->where('invoice_id','=',$paymentReceivedByCashier[$i]->invoice_id)
        ->where('payment_accepted_by',$id)
        ->sum('amount');*/
        }

        $expancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->where('expenses.disbursed_by', $id)->get();
        /*$fileDetail = DB::table('expenses')
        ->leftJoin('cargo', 'expenses.file_number', '=', 'cargo.id')
        ->where('expenses.disbursed_by',$id)->get();*/
        $countEx = count($expancesDetail);
        //$countExF = count($fileDetail);
        // $arr = [];
        // for($i = 0;$i<$countEx;$i++){
        //     if(!in_array($expancesDetail[$i]->expense_id,$arr)){
        //         $expancesDetail->file_number = $fileDetail[$i]->file_number;
        //         array_push($arr,$expancesDetail[$i]->expense_id);
        //     }
        // }
        //$cashCreditAc = DB::table('expenses')->where('cashier_id',$id)->get();
        /*for($i = 0;$i<$countEx;$i++){
        //$expancesDetail[$i]->file_number = $fileDetail[$i]->file_number;
        //$expancesDetail[$i]->cash_credit_account = $cashCreditAc[$i]->cash_credit_account;
        $expancesDetail[$i]->total = DB::table('expenses')
        ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
        ->where('expenses.cashier_id',$id)
        ->where('expenses.expense_id','=',$expancesDetail[$i]->expense_id)
        ->sum('amount');
        }*/

        $pdf = PDF::loadView('reports.printCashierReport', compact('cashierDetail', 'paymentReceivedByCashier', 'total', 'count', 'expancesDetail', 'countEx', 'totalOfCurrency'));
        $pdf_file = 'receivedPaymentDetail_' . $id . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Cargo/Cashier/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'RecievedPayment_Report.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function printCashierDisbursementReport($id = '', $date = '')
    {

        $cashierDetail = DB::table('users')->where('id', $id)->get();
        $expancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->where('expenses.disbursed_by', $id)->get();
        if ($date != '') {
            $date = date('Y-m-d', strtotime($date));
            $expancesDetail = DB::table('expenses')
                ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
                ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->where('expenses.disbursed_by', $id)->where('expenses.disbursed_datetime', 'like', $date . '%')->get();

            if ($expancesDetail == '') {
                $expancesDetail = DB::table('expenses')
                    ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account'])
                    ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
                    ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                    ->where('expenses.disbursed_by', $id)->get();
            }
        }
        $countEx = count($expancesDetail);
        $modelInvoices = new Invoices;
        $pdf = PDF::loadView('reports.printCashierDisbursementReport', compact('cashierDetail', 'expancesDetail', 'countEx', 'date'));
        $pdf_file = 'receivedDisbursementDetail_' . $id . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Cargo/Cashier/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Disbursement_Report.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function printLocatRentReport($id = '', $date = '')
    {
        if (!empty($date)) {
            $date = date('Y-m-d', strtotime($date));
            $localInvoicePaymentDetail = DB::table('local_invoice_payment_detail')->select(['local_invoice_payment_detail.*', 'cargo.*'])->join('cargo', 'local_invoice_payment_detail.local_invoice_id', '=', 'cargo.id')->where('local_invoice_payment_detail.updated_by', $id)->where('local_invoice_payment_detail.updated_at', 'LIKE', $date . '%')->get();
            $totalAcceptedRent = DB::table('local_invoice_payment_detail')->where('updated_by', $id)->where('local_invoice_payment_detail.updated_at', 'LIKE', $date . '%')->sum('total');
            $pdf = PDF::loadview('reports.printLocalRentReport', compact('localInvoicePaymentDetail'));
            $pdf_file = 'receivedRentDetail_' . $id . '.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Reports/Cargo/Cashier/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'RecievedRent_Report.pdf', $filecontent, 'public');
            return response()->file($pdf_path);
        } else {
            $localInvoicePaymentDetail = DB::table('local_invoice_payment_detail')->select(['local_invoice_payment_detail.*', 'cargo.*'])->join('cargo', 'local_invoice_payment_detail.local_invoice_id', '=', 'cargo.id')->where('local_invoice_payment_detail.updated_by', $id)->get();
            $pdf = PDF::loadview('reports.printLocalRentReport', compact('localInvoicePaymentDetail'));
            $pdf_file = 'receivedRentDetail_' . $id . '.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Reports/Cargo/Cashier/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'RecievedRent_Report.pdf', $filecontent, 'public');
            return response()->file($pdf_path);
        }
    }

    public function getCommissionReport()
    {
        /* $upsData = DB::table('ups_details')->where('deleted', 0)
            ->where(function ($query) {
                $query->where('fc', 1)
                    ->orWhere('pp', 1);
            })->orderBy('id', 'DESC')->get(); */
        return view('reports.courier.commissionReport');
    }

    public function listgetcommissionReport(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $file_type = $req['file_type'];
        $typeimpexp = $req['typeimpexp'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];

        if ($file_type == 'ups')
            $orderby = ['ups_details.file_number', '', 'ups_details.awb_number', '', 'ups_details.freight', ''];
        else
            $orderby = ['aeropost.file_number', '', 'aeropost.tracking_no', '', 'aeropost.freight', ''];

        if ($file_type == 'ups') {
            $total = Ups::selectRaw('count(*) as total')
                ->where('deleted', 0)
                ->where(function ($query) {
                    $query->where('fc', 1)
                        ->orWhere('pp', 1);
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($typeimpexp)) {
                $total = $total->where('ups_details.courier_operation_type', $typeimpexp);
            }
            $total = $total->first();
            $totalfiltered = $total->total;

            $query = DB::table('ups_details')
                ->selectRaw('ups_details.id as id,ups_details.file_number as fileNumber,ups_details.arrival_date as date,ups_details.awb_number as trackingNumber,ups_details.freight as freight')
                ->where('deleted', 0)
                ->where(function ($query) {
                    $query->where('fc', 1)
                        ->orWhere('pp', 1);
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($typeimpexp)) {
                $query = $query->where('ups_details.courier_operation_type', $typeimpexp);
            }

            $filteredq = DB::table('ups_details')
                ->where('deleted', 0)
                ->where(function ($query) {
                    $query->where('fc', 1)
                        ->orWhere('pp', 1);
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
            }
            if (!empty($typeimpexp)) {
                $filteredq = $filteredq->where('ups_details.courier_operation_type', $typeimpexp);
            }

            if ($search != '') {
                $query->where(function ($query2) use ($search) {
                    $query2->Where('ups_details.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_details.freight', 'like', '%' . $search . '%');
                });
                $filteredq->where(function ($query2) use ($search) {
                    $query2->Where('ups_details.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_details.freight', 'like', '%' . $search . '%');
                });
                $filteredq = $filteredq->selectRaw('count(*) as total')->first();
                $totalfiltered = $filteredq->total;
            }
            $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        } else {
            $total = Aeropost::selectRaw('count(*) as total')
                ->where('deleted', '0');

            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('aeropost.date', array($fromDate, $toDate));
            }
            $total = $total->first();
            $totalfiltered = $total->total;

            $query = DB::table('aeropost')
                ->selectRaw('aeropost.id as id,aeropost.file_number as fileNumber,aeropost.date as date,aeropost.tracking_no as trackingNumber,aeropost.freight as freight')
                ->where('deleted', '0');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('aeropost.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('aeropost')
                ->where('deleted', '0');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('aeropost.date', array($fromDate, $toDate));
            }

            if ($search != '') {
                $query->where(function ($query2) use ($search) {
                    $query2->Where('aeropost.file_number', 'like', '%' . $search . '%')
                        ->orWhere('aeropost.tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('aeropost.freight', 'like', '%' . $search . '%');
                });
                $filteredq->where(function ($query2) use ($search) {
                    $query2->Where('aeropost.file_number', 'like', '%' . $search . '%')
                        ->orWhere('aeropost.tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('aeropost.freight', 'like', '%' . $search . '%');
                });
                $filteredq = $filteredq->selectRaw('count(*) as total')->first();
                $totalfiltered = $filteredq->total;
            }
            $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        }

        $data = [];
        foreach ($query as $key => $items) {

            if ($file_type == 'ups') {
                $commission = number_format(Ups::getCommission($items->id), 2);
                $billingTerm = Ups::getBillingTerm($items->id);
            } else {
                $commission = number_format(app('App\AeropostFreightCommission')->getCommission($items->id), 2);
                $billingTerm = '-';
            }

            $data[] = [$items->fileNumber, !empty($items->date) ? date('d-m-Y', strtotime($items->date)) : '-', !empty($items->trackingNumber) ? $items->trackingNumber : '-', $billingTerm, !empty($items->freight) ? $items->freight : '0.00', $commission];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function printandexportcommissionReport($fromDate = null, $toDate = null, $file_type = null, $typeimpexp = null, $submitButtonName = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';
        if ($submitButtonName == 'clsPrint') {
            if ($file_type == 'ups') {
                $upsData = DB::table('ups_details')
                    ->selectRaw('ups_details.id as id,ups_details.file_number as fileNumber,ups_details.arrival_date as date,ups_details.awb_number as trackingNumber,ups_details.freight as freight')
                    ->where('deleted', 0)
                    ->where(function ($query) {
                        $query->where('fc', 1)
                            ->orWhere('pp', 1);
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $upsData = $upsData->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
                }
                if (!empty($typeimpexp)) {
                    $upsData = $upsData->where('ups_details.courier_operation_type', $typeimpexp);
                }
                $upsData = $upsData->orderBy('id', 'DESC')->get();

                $pdf = PDF::loadview('reports.courier.printCommissionReport', compact('upsData'));
                $id = auth()->user()->id;
                $pdf_file = 'commissionDetail_' . $id . '.pdf';
                $pdf_path = 'public/reports_pdf/' . $pdf_file;
                $pdf->save($pdf_path);
                $s3path = 'Files/Reports/Courier/';
                $filecontent = file_get_contents($pdf_path);
                $success = Storage::disk('s3')->put($s3path . 'UpsCommission_Report.pdf', $filecontent, 'public');
                //return response()->file($pdf_path);
                return url('/') . '/' . $pdf_path;
            } else {
                $aeroPostData = DB::table('aeropost')
                    ->selectRaw('aeropost.id as id,aeropost.file_number as fileNumber,aeropost.date as date,aeropost.tracking_no as trackingNumber,aeropost.freight as freight')
                    ->where('deleted', '0');
                if (!empty($fromDate) && !empty($toDate)) {
                    $aeroPostData = $aeroPostData->whereBetween('aeropost.date', array($fromDate, $toDate));
                }
                $aeroPostData = $aeroPostData->orderBy('id', 'DESC')->get();

                $pdf = PDF::loadview('reports.courier.printAeropostCommissionReport', compact('aeroPostData'));
                $id = auth()->user()->id;
                $pdf_file = 'aeropostCommissionDetail_' . $id . '.pdf';
                $pdf_path = 'public/reports_pdf/' . $pdf_file;
                $pdf->save($pdf_path);
                $s3path = 'Files/Reports/Courier/';
                $filecontent = file_get_contents($pdf_path);
                $success = Storage::disk('s3')->put($s3path . 'AeropostCommission_Report.pdf', $filecontent, 'public');
                //return response()->file($pdf_path);
                return url('/') . '/' . $pdf_path;
            }
        } else if ($submitButtonName == 'clsExportToExcel' || $submitButtonName == 'clsMail') {
            $commissionDetail = [];
            if ($file_type == 'ups') {
                $moduleData = DB::table('ups_details')
                    ->selectRaw('ups_details.id as id,ups_details.file_number as fileNumber,ups_details.arrival_date as date,ups_details.awb_number as trackingNumber,ups_details.freight as freight')
                    ->where('deleted', 0)
                    ->where(function ($query) {
                        $query->where('fc', 1)
                            ->orWhere('pp', 1);
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $moduleData = $moduleData->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
                }
                if (!empty($typeimpexp)) {
                    $moduleData = $moduleData->where('ups_details.courier_operation_type', $typeimpexp);
                }
                $moduleData = $moduleData->orderBy('id', 'DESC')->get()->toArray();
            } else {
                $moduleData = DB::table('aeropost')
                    ->selectRaw('aeropost.id as id,aeropost.file_number as fileNumber,aeropost.date as date,aeropost.tracking_no as trackingNumber,aeropost.freight as freight')
                    ->where('deleted', '0');
                if (!empty($fromDate) && !empty($toDate)) {
                    $moduleData = $moduleData->whereBetween('aeropost.date', array($fromDate, $toDate));
                }
                $moduleData = $moduleData->orderBy('id', 'DESC')->get()->toArray();
            }
            $all_array[] = array('File Number', 'Date', 'Awb Number', 'Billing Term', 'Freight Rev', 'Commission');
            foreach ($moduleData as $data) {
                if ($file_type == 'ups') {
                    $dataCommission = number_format(Ups::getCommission($data->id), 2);
                    $billingTerm = Ups::getBillingTerm($data->id);
                } else {
                    $dataCommission = number_format(app('App\AeropostFreightCommission')->getCommission($data->id), 2);
                    $billingTerm = '-';
                }

                $all_array[] = array(
                    'File Number' => !empty($data->fileNumber) ? $data->fileNumber : '-',
                    'Date' => !empty($data->date) ? date('d-m-Y', strtotime($data->date)) : '-',
                    'Awb Number' => !empty($data->trackingNumber) ? $data->trackingNumber : '-',
                    'Billing Type' => $billingTerm,
                    'Freight Rev' => !empty($data->freight) ? $data->freight : '0.00',
                    'Commission' => $dataCommission,
                );
            }
            $excelObj = Excel::create('commissionReport', function ($excel) use ($all_array) {
                $excel->setTitle('commission Report');
                $excel->sheet('commission Data', function ($sheet) use ($all_array) {
                    $sheet->fromArray($all_array, null, 'A1', false, false);
                });
            });
            $excelObj->store('xlsx', 'public/commissionReports/', true);
            $filecontent = file_get_contents('public/commissionReports/commissionReport.xlsx');
            $filepath = 'Files/Downloads/commissionReport.xlsx';
            $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
            if ($submitButtonName == 'clsMail') {

                $commissionAttachment['attachment'] = 'public/commissionReports/commissionReport.xlsx';
                $commissionAttachment['flag'] = $file_type == 'ups' ? 'Ups' : 'aeroPost';
                Mail::to('mphp.magneto@gmail.com')->send(new upsCommissionMail($commissionAttachment));
            } else {
                $excelObj->download('xlsx');
            }
        }
    }

    public function filterbycouriertype(Request $request)
    {
        $courier_type = $request->get('courier_type');
        $impExpType = $request->get('impExpType');
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';

        if ($courier_type == 'ups') {
            if (!empty($impExpType)) {
                $filteredData = DB::table('ups_details')->where('courier_operation_type', $impExpType)
                    ->where('deleted', 0)
                    ->where(function ($query) {
                        $query->where('fc', 1)
                            ->orWhere('pp', 1);
                    })
                    ->orderBy('id', 'DESC');
                if (!empty($fromDate) && !empty($toDate)) {
                    if ($impExpType == 1)
                        $filteredData->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
                    else
                        $filteredData->whereBetween('ups_details.tdate', array($fromDate, $toDate));
                }
                $filteredData = $filteredData->get();
            } else {
                $filteredData = DB::table('ups_details')->where('deleted', 0)
                    ->where(function ($query) {
                        $query->where('fc', 1)
                            ->orWhere('pp', 1);
                    })
                    ->orderBy('id', 'DESC');
                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredData->where(function ($query) {
                        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
                        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
                        $query->whereBetween('ups_details.arrival_date', array($fromDate, $toDate))
                            ->orWhereBetween('ups_details.tdate', array($fromDate, $toDate));
                    });
                }
                $filteredData = $filteredData->get();
            }
            return view('reports.courier.filteredUpsData', compact('filteredData'));
        } else {
            $filteredData = DB::table('aeropost')->where('deleted', '0')->orderBy('id', 'DESC');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredData->whereBetween('aeropost.date', array($fromDate, $toDate));
            }
            $filteredData = $filteredData->get();
            return view('reports.courier.filteredAeropostData', compact('filteredData'));
        }
    }

    public function printCommissionReport($type_flag = 'ups')
    {
        if ($type_flag == 'ups') {
            $upsData = DB::table('ups_details')->where('deleted', 0)->where(function ($query) {
                $query->where('fc', 1)
                    ->orWhere('pp', 1);
            })->orderBy('id', 'DESC')->get();

            $pdf = PDF::loadview('reports.courier.printCommissionReport', compact('upsData'));
            $id = auth()->user()->id;
            $pdf_file = 'commissionDetail_' . $id . '.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Reports/Courier/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'UpsCommission_Report.pdf', $filecontent, 'public');
            return response()->file($pdf_path);
        } else {
            $aeroPostData = DB::table('aeropost')->where('deleted', '0')->orderBy('id', 'DESC')->get();
            $pdf = PDF::loadview('reports.courier.printAeropostCommissionReport', compact('aeroPostData'));
            $id = auth()->user()->id;
            $pdf_file = 'aeropostCommissionDetail_' . $id . '.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;
            $pdf->save($pdf_path);
            $s3path = 'Files/Reports/Courier/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'AeropostCommission_Report.pdf', $filecontent, 'public');
            return response()->file($pdf_path);
        }
    }

    public function getExcelFile(Request $request, $mailflag, $type = null)
    {
        //$fileType = $request->get('filetype');
        $fileType = $type;
        $commissionDetail = [];
        if ($fileType == 'ups') {
            $upsData = DB::table('ups_details')->where('deleted', 0)->where(function ($query) {
                $query->where('fc', 1)
                    ->orWhere('pp', 1);
            })->orderBy('id', 'DESC')->get()->toArray();
            $ups_array[] = array('Awb Number', 'Date', 'Billing Type', 'Nature Of Shipment', 'Multiple Package', 'Freight Rev', 'Currency', 'Commission', 'Received', 'Weight');

            foreach ($upsData as $data) {

                $dataCommission = Ups::getCommissionData($data->id);
                if (empty($dataCommission))
                    $receivedCommission = '0.00';
                else
                    $receivedCommission = number_format((!empty($dataCommission->commission) ? $dataCommission->commission : '0.00') - (is_null($dataCommission->pending_commission) ? $dataCommission->commission : (!empty($dataCommission->pending_commission) ? $dataCommission->pending_commission : '0.00')), 2);


                $ups_array[] = array(

                    'Awb Number' => !empty($data->awb_number) ? $data->awb_number : '-',
                    'Date' => $data->courier_operation_type == 1 ? (!empty($data->arrival_date) ? date('d-m-Y', strtotime($data->arrival_date)) : '-') : (!empty($data->tdate) ? date('d-m-Y', strtotime($data->tdate)) : '-'),
                    'Billing Type' => Ups::getBillingTerm($data->id),
                    'Nature Of Shipment' => $data->package_type,
                    'Multiple Package' => $data->nbr_pcs > 1 ? 'Y' : 'N',
                    'Freight Rev' => !empty($data->freight) ? $data->freight : '0.00',
                    'Currency' => !empty($data->freight_currency) ? $data->freight_currency : '-',
                    'Commission' => Ups::getCommission($data->id),
                    'Received' => $receivedCommission,
                    'Weight' => !empty($data->weight) ? $data->weight : '-',
                );
            }
            $excelObj = Excel::create('UpsCommissionReport', function ($excel) use ($ups_array) {
                $excel->setTitle('Ups Commission Report');
                $excel->sheet('Ups Data', function ($sheet) use ($ups_array) {
                    $sheet->fromArray($ups_array, null, 'A1', false, false);
                });
            });
            $excelObj->store('xlsx', 'public/commissionReports/', true);
            $filecontent = file_get_contents('public/commissionReports/UpsCommissionReport.xlsx');
            $filepath = 'Files/Downloads/UpsCommissionReport.xlsx';
            $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
            if ($mailflag == 1) {

                $commissionAttachment['attachment'] = 'public/commissionReports/UpsCommissionReport.xlsx';
                $commissionAttachment['flag'] = 'Ups';
                Mail::to('mphp.magneto@gmail.com')->send(new upsCommissionMail($commissionAttachment));
            } else {
                $excelObj->download('xlsx');
            }
        } else {
            $aeropostData = DB::table('aeropost')->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
            $aeropostArray[] = array('Awb Number', 'Date', 'From', 'Consignee', 'Freight Rev', 'Commission', 'Flight Date&Time');

            foreach ($aeropostData as $data) {
                $dataClient = app('App\Clients')->getClientData($data->consignee);
                $aeropostArray[] = array(

                    'Awb Number' => !empty($data->tracking_no) ? $data->tracking_no : '-',
                    'Date' => !empty($data->date) ? date('d-m-Y', strtotime($data->date)) : '-',
                    'From' => $data->from_address,
                    'Consignee' => !empty($dataClient->company_name) ? $dataClient->company_name : '-',
                    'Freight Rev' => !empty($data->freight) ? $data->freight : '0.00',
                    'Commission' => app('App\AeropostFreightCommission')->getCommission($data->id),
                    'Flight Date&Time' => date('d-m-Y H:i:s', strtotime($data->flight_date_time)),

                );
            }
            $excelObj = Excel::create('AeropostCommissionReport', function ($excel) use ($aeropostArray) {
                $excel->setTitle('Aeropost Commission Report');
                $excel->sheet('Aeropost Data', function ($sheet) use ($aeropostArray) {
                    $sheet->fromArray($aeropostArray, null, 'A1', false, false);
                });
            });
            $excelObj->store('xlsx', 'public/commissionReports/', true);
            $filecontent = file_get_contents('public/commissionReports/AeropostCommissionReport.xlsx');
            $filepath = 'Files/Downloads/AeropostCommissionReport.xlsx';
            $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
            if ($mailflag == 1) {

                $commissionAttachment['attachment'] = 'public/commissionReports/AeropostCommissionReport.xlsx';
                $commissionAttachment['flag'] = 'aeroPost';
                Mail::to('mphp.magneto@gmail.com')->send(new upsCommissionMail($commissionAttachment));
            } else {
                $excelObj->download('xlsx');
            }
        }
    }

    public function freedomicilereport()
    {
        //$freeDomicileFiles = DB::table('ups_details')->where('deleted', 0)->where('courier_operation_type', 1)->where('fd', 1)->get();
        return view('reports.courier.freeDomicileReport');
    }

    public function listfreedomicilereport(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ups_details.file_number', '', 'ups_details.awb_number', '', 'c1.company_name', 'c2.company_name', 'ups_details.origin', 'ups_details.destination'];

        $total = Ups::selectRaw('count(*) as total')
            ->where('deleted', 0)
            ->where('courier_operation_type', 1)->where('fd', 1);
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('ups_details')
            ->selectRaw('ups_details.id as id,ups_details.file_number as fileNumber,ups_details.arrival_date as date,ups_details.awb_number as trackingNumber,ups_details.origin as origin,ups_details.destination as destination,c1.company_name as consigneeName,c2.company_name as shipperName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->where('ups_details.deleted', 0)
            ->where('courier_operation_type', 1)->where('fd', 1);
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('ups_details')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->where('ups_details.deleted', 0)
            ->where('courier_operation_type', 1)->where('fd', 1);
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->Where('ups_details.file_number', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.origin', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.destination', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->Where('ups_details.file_number', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.origin', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.destination', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {

            $billingTerm = Ups::getBillingTerm($items->id);

            $data[] = [$items->fileNumber, !empty($items->date) ? date('d-m-Y', strtotime($items->date)) : '-', !empty($items->trackingNumber) ? $items->trackingNumber : '-', $billingTerm, !empty($items->shipperName) ? $items->shipperName : '-', !empty($items->consigneeName) ? $items->consigneeName : '-', $items->origin, $items->destination];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function printandexportfreedomicilereport($fromDate = null, $toDate = null, $submitButtonName = null)
    {
        $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
        $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';
        if ($submitButtonName == 'clsPrint') {
            $upsData = DB::table('ups_details')
                ->selectRaw('ups_details.id as id,ups_details.file_number as fileNumber,ups_details.arrival_date as date,ups_details.awb_number as trackingNumber,ups_details.origin as origin,ups_details.destination as destination,c1.company_name as consigneeName,c2.company_name as shipperName')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', 0)
                ->where('courier_operation_type', 1)->where('fd', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $upsData = $upsData->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
            }
            $upsData = $upsData->orderBy('id', 'DESC')->get()->toArray();

            $pdf = PDF::loadview('reports.courier.printfreedomicilereport', compact('upsData'));
            $id = auth()->user()->id;
            $pdf_file = 'freeDomicileCommissionDetail_' . $id . '.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;

            $pdf->save($pdf_path);
            $s3path = 'Files/Reports/Courier/';
            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Freedomicile_Report.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else if ($submitButtonName == 'clsExportToExcel' || $submitButtonName == 'clsMail') {
            $upsData = DB::table('ups_details')
                ->selectRaw('ups_details.id as id,ups_details.file_number as fileNumber,ups_details.arrival_date as date,ups_details.awb_number as trackingNumber,ups_details.origin as origin,ups_details.destination as destination,c1.company_name as consigneeName,c2.company_name as shipperName')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', 0)
                ->where('courier_operation_type', 1)->where('fd', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $upsData = $upsData->whereBetween('ups_details.arrival_date', array($fromDate, $toDate));
            }
            $upsData = $upsData->orderBy('id', 'DESC')->get()->toArray();

            foreach ($upsData as $key => $value) {
                $newArray[$value->origin][] = $value;
            }

            if (empty($newArray)) {
                return "Empty";
            }

            foreach ($newArray as $key => $value) {
                $i = $key;
                $allData[$i][] = array('', 'REF NUMBER', 'CCX020919US', 'FREE DOMICILE CHARGES', '', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', '(DUTY CHARGES TO BE PAID BY SHIPPER)', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', 'WEEKLY SUMMARY FOR WEEK ENDING', '', date('d-M-y', strtotime($toDate)), '', '', '', '', 'W/E', date('d-M-y', strtotime($toDate)), '');
                $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', 'UPS APPROVED WEEKLY', '');
                $allData[$i][] = array('', '', '', '', 'Import Country:', '', 'HT', '', '', '', '', '', 'EXCHANGE RATE', '');
                $allData[$i][] = array('', '', '', '', 'Export Country:', '', $key, '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', 'EXPORT', 'SHIPMENT', '', '', 'BROKERAGE', 'DUTY CHARGES', 'TAX', 'OTHER GOVERNMENT', "OTHER GOV'T CHARGE", 'TOTAL', 'CONVERTED AMOUNT', 'CONVERTED AMOUNT', '');
                $allData[$i][] = array('', 'DATE(DD/MM/YY)', 'HAWB NUMBER', 'SHIPPERS NAME', 'CONSIGNEE NAME', 'CHARGES', 'CHARGES', 'CHARGES', 'CHARGES', "DESCRIPTION", 'PAID', 'US$ DOLLARS', 'ORIGIN CURRENCY', '');
                $total = 0;
                foreach ($value as $skey => $data) {

                    $consignee = $data->consigneeName;
                    $shipper = $data->shipperName;

                    $dataInvoice = DB::table('invoices')->where('ups_id', $data->id)->first();
                    if (empty($dataInvoice))
                        continue;

                    $chargeBrokerage = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDBC')->first();

                    $chargeDuty = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDDC')->first();
                    //pre($chargeBrokerage);
                    $chargeTax = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDTC')->first();

                    $chargeOtherGovt = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDOGC')->first();

                    $allData[$i][] = array(
                        '' => '',
                        'EXPORT DATE(DD/MM/YY)' => !empty($data->date) ? date('d-M-Y', strtotime($data->date)) : '-',
                        'SHIPMENT HAWB NUMBER' => !empty($data->trackingNumber) ? $data->trackingNumber : '-',
                        'SHIPPERS NAME' => !empty($shipper) ? $shipper : '-',
                        'CONSIGNEE NAME' => !empty($consignee) ? $consignee : '-',
                        'BROKERAGE CHARGES' => !empty($chargeBrokerage->total_of_items) ? $chargeBrokerage->total_of_items : '-',
                        'DUTY CHARGES' => !empty($chargeDuty->total_of_items) ? $chargeDuty->total_of_items : '-',
                        'TAX CHARGES' => !empty($chargeTax->total_of_items) ? $chargeTax->total_of_items : '-',
                        'OTHER GOVERNMENT CHARGES' => !empty($chargeOtherGovt->total_of_items) ? $chargeOtherGovt->total_of_items : '-',
                        "OTHER GOV'T CHARGE DESCRIPTION" => !empty($chargeOtherGovt->fees_name_desc) ? $chargeOtherGovt->fees_name_desc : '-',
                        'TOTAL PAID' => !empty($dataInvoice->balance_of) ? $dataInvoice->balance_of : '-',
                        'CONVERTED AMOUNT US$ DOLLARS' => !empty($dataInvoice->balance_of) ? $dataInvoice->balance_of : '-',
                        'CONVERTED AMOUNT ORIGIN CURRENCY' => '',
                        '' => '',
                    );

                    $total += $dataInvoice->balance_of;
                }

                $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
                $allData[$i][] = array('', '', '', '', 'Sub Total', '0.00', '0.00', '0.00', '0.00', '', '0.00', number_format($total, 2), '', '');
            }

            $excelObj = Excel::create('freeDomicileData', function ($excel) use ($allData) {
                $excel->setTitle('Free Domicile Report');
                foreach ($allData as $key => $value) {
                    $ar = $allData[$key];
                    $excel->sheet($key, function ($sheet) use ($ar) {
                        $sheet->setAutoSize(false);
                        $sheet->setFontSize(10);
                        $sheet->setWidth('A', 5);
                        $sheet->cell(1, function ($row) {
                            $row->setBackground('#CCCCCC');
                        });
                        $sheet->cell('A', function ($row) {
                            $row->setBackground('#767676');
                        });
                        $sheet->cell(10, function ($row) {
                            $row->setFontColor('#008000');
                        });
                        $sheet->cell(11, function ($row) {
                            $row->setFontColor('#008000');
                        });
                        $sheet->cell('L3', function ($row) {
                            $row->setFontColor('#0000ff');
                        });
                        $sheet->cell('G6', function ($row) {
                            $row->setFontColor('#0000ff');
                        });
                        $sheet->setBorder('B10:M10', 'thin');
                        $sheet->setBorder('B11:M11', 'thin');
                        $sheet->setHeight(1, 20);

                        $sheet->cells('D1', function ($cells) {
                            $cells->setFont(array(
                                'family' => 'Calibri',
                                'size' => '15',
                                'bold' => true,
                            ));
                        });
                        $sheet->fromArray($ar, null, 'A1', false, false);
                    });
                }
            });
            $excelObj->store('xlsx', 'public/freedomicileReport/', true);
            $filecontent = file_get_contents('public/freedomicileReport/freeDomicileData.xlsx');
            $filepath = 'Files/Downloads/freeDomicileData.xlsx';
            $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
            if ($submitButtonName == 'clsMail') {
                $commissionAttachment['attachment'] = 'public/freedomicileReport/freeDomicileData.xlsx';
                $commissionAttachment['flag'] = 'Free Domicile';
                Mail::to('mphp.magneto@gmail.com')->send(new upsCommissionMail($commissionAttachment));
            } else {
                $excelObj->download('xlsx');
            }
        }
    }

    public function printfreedomicilereport($fromDate, $toDate)
    {
        $upsData = DB::table('ups_details')->where('deleted', 0)->where('courier_operation_type', 1)->whereBetween('tdate', array(date('Y-m-d', strtotime($fromDate)), date('Y-m-d', strtotime($toDate))))->where('fd', 1)->get()->toArray();
        //pre($upsData);
        $pdf = PDF::loadview('reports.courier.printfreedomicilereport', compact('upsData'));
        $id = auth()->user()->id;
        $pdf_file = 'freeDomicileCommissionDetail_' . $id . '.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;

        $pdf->save($pdf_path);
        $s3path = 'Files/Reports/Courier/';
        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Freedomicile_Report.pdf', $filecontent, 'public');
        return response()->file($pdf_path);
    }

    public function fdreportweekly($mailflag, $fromDate, $toDate)
    {
        $fromDate = date('Y-m-d', strtotime($fromDate));
        $toDate = date('Y-m-d', strtotime($toDate));

        $upsData = DB::table('ups_details')->where('deleted', 0)->where('courier_operation_type', 1)->whereBetween('tdate', array(date('Y-m-d', strtotime($fromDate)), date('Y-m-d', strtotime($toDate))))->where('fd', 1)->get()->toArray();

        foreach ($upsData as $key => $value) {
            $newArray[$value->origin][] = $value;
        }

        if (empty($newArray)) {
            return "Empty";
        }

        foreach ($newArray as $key => $value) {
            $i = $key;
            $allData[$i][] = array('', 'REF NUMBER', 'CCX020919US', 'FREE DOMICILE CHARGES', '', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', '(DUTY CHARGES TO BE PAID BY SHIPPER)', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', 'WEEKLY SUMMARY FOR WEEK ENDING', '', date('d-M-y', strtotime($toDate)), '', '', '', '', 'W/E', date('d-M-y', strtotime($toDate)), '');
            $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', 'UPS APPROVED WEEKLY', '');
            $allData[$i][] = array('', '', '', '', 'Import Country:', '', 'HT', '', '', '', '', '', 'EXCHANGE RATE', '');
            $allData[$i][] = array('', '', '', '', 'Export Country:', '', $key, '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', 'EXPORT', 'SHIPMENT', '', '', 'BROKERAGE', 'DUTY CHARGES', 'TAX', 'OTHER GOVERNMENT', "OTHER GOV'T CHARGE", 'TOTAL', 'CONVERTED AMOUNT', 'CONVERTED AMOUNT', '');
            $allData[$i][] = array('', 'DATE(DD/MM/YY)', 'HAWB NUMBER', 'SHIPPERS NAME', 'CONSIGNEE NAME', 'CHARGES', 'CHARGES', 'CHARGES', 'CHARGES', "DESCRIPTION", 'PAID', 'US$ DOLLARS', 'ORIGIN CURRENCY', '');
            $total = 0;
            foreach ($value as $skey => $data) {

                $consignee = app('App\Clients')->getClientData($data->consignee_name);
                $shipper = app('App\Clients')->getClientData($data->shipper_name);

                $dataInvoice = DB::table('invoices')->where('ups_id', $data->id)->first();
                if (empty($dataInvoice))
                    continue;

                $chargeBrokerage = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDBC')->first();

                $chargeDuty = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDDC')->first();
                //pre($chargeBrokerage);
                $chargeTax = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDTC')->first();

                $chargeOtherGovt = DB::table('invoice_item_details')->where('invoice_id', $dataInvoice->id)->where('item_code', 'FDOGC')->first();

                $allData[$i][] = array(
                    '' => '',
                    'EXPORT DATE(DD/MM/YY)' => !empty($data->tdate) ? date('d-M-Y', strtotime($data->tdate)) : '-',
                    'SHIPMENT HAWB NUMBER' => !empty($data->awb_number) ? $data->awb_number : '-',
                    'SHIPPERS NAME' => !empty($shipper->company_name) ? $shipper->company_name : '-',
                    'CONSIGNEE NAME' => !empty($consignee->company_name) ? $consignee->company_name : '-',
                    'BROKERAGE CHARGES' => !empty($chargeBrokerage->total_of_items) ? $chargeBrokerage->total_of_items : '-',
                    'DUTY CHARGES' => !empty($chargeDuty->total_of_items) ? $chargeDuty->total_of_items : '-',
                    'TAX CHARGES' => !empty($chargeTax->total_of_items) ? $chargeTax->total_of_items : '-',
                    'OTHER GOVERNMENT CHARGES' => !empty($chargeOtherGovt->total_of_items) ? $chargeOtherGovt->total_of_items : '-',
                    "OTHER GOV'T CHARGE DESCRIPTION" => !empty($chargeOtherGovt->fees_name_desc) ? $chargeOtherGovt->fees_name_desc : '-',
                    'TOTAL PAID' => !empty($dataInvoice->balance_of) ? $dataInvoice->balance_of : '-',
                    'CONVERTED AMOUNT US$ DOLLARS' => !empty($dataInvoice->balance_of) ? $dataInvoice->balance_of : '-',
                    'CONVERTED AMOUNT ORIGIN CURRENCY' => '',
                    '' => '',
                );

                $total += $dataInvoice->balance_of;
            }

            $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', '', '', '', '', '', '', '', '', '', '');
            $allData[$i][] = array('', '', '', '', 'Sub Total', '0.00', '0.00', '0.00', '0.00', '', '0.00', number_format($total, 2), '', '');
        }

        $excelObj = Excel::create('freeDomicileData', function ($excel) use ($allData) {
            $excel->setTitle('Free Domicile Report');
            foreach ($allData as $key => $value) {
                $ar = $allData[$key];
                $excel->sheet($key, function ($sheet) use ($ar) {
                    $sheet->setAutoSize(false);
                    $sheet->setFontSize(10);
                    $sheet->setWidth('A', 5);
                    $sheet->cell(1, function ($row) {
                        $row->setBackground('#CCCCCC');
                    });
                    $sheet->cell('A', function ($row) {
                        $row->setBackground('#767676');
                    });
                    $sheet->cell(10, function ($row) {
                        $row->setFontColor('#008000');
                    });
                    $sheet->cell(11, function ($row) {
                        $row->setFontColor('#008000');
                    });
                    $sheet->cell('L3', function ($row) {
                        $row->setFontColor('#0000ff');
                    });
                    $sheet->cell('G6', function ($row) {
                        $row->setFontColor('#0000ff');
                    });
                    $sheet->setBorder('B10:M10', 'thin');
                    $sheet->setBorder('B11:M11', 'thin');
                    $sheet->setHeight(1, 20);

                    $sheet->cells('D1', function ($cells) {
                        $cells->setFont(array(
                            'family' => 'Calibri',
                            'size' => '15',
                            'bold' => true,
                        ));
                    });
                    $sheet->fromArray($ar, null, 'A1', false, false);
                });
            }
        });
        $excelObj->store('xlsx', 'public/freedomicileReport/', true);
        $filecontent = file_get_contents('public/freedomicileReport/freeDomicileData.xlsx');
        $filepath = 'Files/Downloads/freeDomicileData.xlsx';
        $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
        if ($mailflag == 1) {
            $commissionAttachment['attachment'] = 'public/freedomicileReport/freeDomicileData.xlsx';
            $commissionAttachment['flag'] = 'Free Domicile';
            Mail::to('mphp.magneto@gmail.com')->send(new upsCommissionMail($commissionAttachment));
        } else {
            $excelObj->download('xlsx');
        }
    }

    public function settodateinfdreport()
    {
        $fromDate = $_POST['fromDate'];
        $date = strtotime($fromDate);
        $toDate = strtotime("+6 day", $date);
        return date('d-m-Y', $toDate);
    }

    public function combinedetailreportindex()
    {
        $clientdata = DB::table('clients')->where('deleted', 0)->where('client_flag', 'B')->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        return view("reports.combinereport", ['billingParty' => $clientdata]);
    }

    public function listcombinereport(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $moduleType = $req['moduleType'];
        $courierType = $req['courierType'];
        $operationType = $req['operationType'];
        $billingParty = $req['billingParty'];

        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];

        if ($moduleType == 'cargo') {
            $orderby = ['cargo.id', 'invoices.id', 'cargo.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
        } else {
            if ($courierType == '1')
                $orderby = ['ups_details.id', 'invoices.id', 'ups_details.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
            else if ($courierType == '4')
                $orderby = ['ups_master.id', 'invoices.id', 'ups_master.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
            else if ($courierType == '2')
                $orderby = ['aeropost.id', 'invoices.id', 'aeropost.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
            else if ($courierType == '5')
                $orderby = ['aeropost_master.id', 'invoices.id', 'aeropost_master.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
            else if ($courierType == '6')
                $orderby = ['ccpack_master.id', 'invoices.id', 'ccpack_master.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
            else if ($courierType == '3')
                $orderby = ['ccpack.id', 'invoices.id', 'ccpack.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.total', 'invoices.credits', 'invoices.payment_status'];
        }

        if ($moduleType == 'cargo') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('cargo', 'invoices.cargo_id', '=', 'cargo.id')
                ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                ->where('invoices.deleted', 0)
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->whereNull('file_close');

            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            if (!empty($billingParty)) {
                $total = $total->where('bill_to', $billingParty);
            }
            if (!empty($operationType)) {
                $total = $total->where('cargo.cargo_operation_type', $operationType);
            }


            $query = DB::table('invoices')
                ->selectRaw(DB::raw('invoices.*,cargo.file_number,cargo.id as cargoId,c1.company_name as billingParty'))
                ->join('cargo', 'invoices.cargo_id', '=', 'cargo.id')
                ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                ->where('invoices.deleted', 0)
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->whereNull('file_close');

            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            if (!empty($billingParty)) {
                $query = $query->where('bill_to', $billingParty);
            }
            if (!empty($operationType)) {
                $query = $query->where('cargo.cargo_operation_type', $operationType);
            }

            $filteredq = DB::table('invoices')
                ->join('cargo', 'invoices.cargo_id', '=', 'cargo.id')
                ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                ->where('invoices.deleted', 0)
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->whereNull('file_close');

            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            if (!empty($billingParty)) {
                $filteredq = $filteredq->where('bill_to', $billingParty);
            }
            if (!empty($operationType)) {
                $filteredq = $filteredq->where('cargo.cargo_operation_type', $operationType);
            }
        } else {
            if ($courierType == '1') {
                $total = Invoices::selectRaw('count(*) as total')
                    ->join('ups_details', 'invoices.ups_id', '=', 'ups_details.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $total = $total->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $total = $total->where('ups_details.courier_operation_type', $operationType);
                }

                $query = DB::table('invoices')
                    ->selectRaw(DB::raw('invoices.*,ups_details.file_number,ups_details.id as upsId,c1.company_name as billingParty'))
                    ->join('ups_details', 'invoices.ups_id', '=', 'ups_details.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $query = $query->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $query = $query->where('ups_details.courier_operation_type', $operationType);
                }

                $filteredq = DB::table('invoices')
                    ->join('ups_details', 'invoices.ups_id', '=', 'ups_details.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $filteredq = $filteredq->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $filteredq = $filteredq->where('ups_details.courier_operation_type', $operationType);
                }
            } else if ($courierType == '4') {
                $total = Invoices::selectRaw('count(*) as total')
                    ->join('ups_master', 'invoices.ups_master_id', '=', 'ups_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $total = $total->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $total = $total->where('ups_master.ups_operation_type', $operationType);
                }

                $query = DB::table('invoices')
                    ->selectRaw(DB::raw('invoices.*,ups_master.file_number,ups_master.id as upsMasterId,c1.company_name as billingParty'))
                    ->join('ups_master', 'invoices.ups_master_id', '=', 'ups_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $query = $query->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $query = $query->where('ups_master.ups_operation_type', $operationType);
                }

                $filteredq = DB::table('invoices')
                    ->join('ups_master', 'invoices.ups_master_id', '=', 'ups_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $filteredq = $filteredq->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $filteredq = $filteredq->where('ups_master.ups_operation_type', $operationType);
                }
            } else if ($courierType == '2') {
                $total = Invoices::selectRaw('count(*) as total')
                    ->join('aeropost', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $total = $total->where('bill_to', $billingParty);
                }

                $query = DB::table('invoices')
                    ->selectRaw(DB::raw('invoices.*,aeropost.file_number,aeropost.id as aeropostId,c1.company_name as billingParty'))
                    ->join('aeropost', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $query = $query->where('bill_to', $billingParty);
                }

                $filteredq = DB::table('invoices')
                    ->join('aeropost', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $filteredq = $filteredq->where('bill_to', $billingParty);
                }
            } else if ($courierType == '5') {
                $total = Invoices::selectRaw('count(*) as total')
                    ->join('aeropost_master', 'invoices.aeropost_master_id', '=', 'aeropost_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $total = $total->where('bill_to', $billingParty);
                }

                $query = DB::table('invoices')
                    ->selectRaw(DB::raw('invoices.*,aeropost_master.file_number,aeropost_master.id as aeropostMasterId,c1.company_name as billingParty'))
                    ->join('aeropost_master', 'invoices.aeropost_master_id', '=', 'aeropost_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $query = $query->where('bill_to', $billingParty);
                }

                $filteredq = DB::table('invoices')
                    ->join('aeropost_master', 'invoices.aeropost_master_id', '=', 'aeropost_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $filteredq = $filteredq->where('bill_to', $billingParty);
                }
            } else if ($courierType == '6') {
                $total = Invoices::selectRaw('count(*) as total')
                    ->join('ccpack_master', 'invoices.ccpack_master_id', '=', 'ccpack_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $total = $total->where('bill_to', $billingParty);
                }

                $query = DB::table('invoices')
                    ->selectRaw(DB::raw('invoices.*,ccpack_master.file_number,ccpack_master.id as ccpackMasterId,c1.company_name as billingParty'))
                    ->join('ccpack_master', 'invoices.ccpack_master_id', '=', 'ccpack_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $query = $query->where('bill_to', $billingParty);
                }

                $filteredq = DB::table('invoices')
                    ->join('ccpack_master', 'invoices.ccpack_master_id', '=', 'ccpack_master.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $filteredq = $filteredq->where('bill_to', $billingParty);
                }
            } else if ($courierType == '3') {
                $total = Invoices::selectRaw('count(*) as total')
                    ->join('ccpack', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $total = $total->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $total = $total->where('ccpack.ccpack_operation_type', $operationType);
                }

                $query = DB::table('invoices')
                    ->selectRaw(DB::raw('invoices.*,ccpack.file_number,ccpack.id as ccpackId,c1.company_name as billingParty'))
                    ->join('ccpack', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $query = $query->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $query = $query->where('ccpack.ccpack_operation_type', $operationType);
                }

                $filteredq = DB::table('invoices')
                    ->join('ccpack', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->join('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $filteredq = $filteredq->where('bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $filteredq = $filteredq->where('ccpack.ccpack_operation_type', $operationType);
                }
            }
        }
        $total = $total->first();
        //pre($total);
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $moduleType, $courierType) {
                if ($moduleType == 'cargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('invoices.total', 'like', '%' . $search . '%')
                        ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                        ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                } else {
                    if ($courierType == '1') {
                        $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '2') {
                        $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '3') {
                        $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '4') {
                        $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '5') {
                        $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '6') {
                        $query2->where('ccpack_master.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    }
                }
            });

            $filteredq->where(function ($query2) use ($search, $moduleType, $courierType) {
                if ($moduleType == 'cargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('invoices.total', 'like', '%' . $search . '%')
                        ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                        ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                } else {
                    if ($courierType == '1') {
                        $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '2') {
                        $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '3') {
                        $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '4') {
                        $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '5') {
                        $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    } else if ($courierType == '6') {
                        $query2->where('ccpack_master.file_number', 'like', '%' . $search . '%')
                            ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                            ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                            ->orWhere('invoices.total', 'like', '%' . $search . '%')
                            ->orWhere('invoices.credits', 'like', '%' . $search . '%')
                            ->orWhere('invoices.payment_status', 'like', '%' . $search . '%');
                    }
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {

            if ($moduleType == 'cargo') {
                $moduleId = $value->cargoId;
            } else {
                if ($courierType == '1') {
                    $moduleId = $value->upsId;
                } else if ($courierType == '2') {
                    $moduleId = $value->aeropostId;
                } else if ($courierType == '3') {
                    $moduleId = $value->ccpackId;
                } else if ($courierType == '4') {
                    $moduleId = $value->upsMasterId;
                } else if ($courierType == '5') {
                    $moduleId = $value->aeropostMasterId;
                } else if ($courierType == '6') {
                    $moduleId = $value->ccpackMasterId;
                }
            }

            $data[] = [$moduleId, $value->id,  date('d-m-Y', strtotime($value->date)), $value->file_number, $value->awb_no, $value->billingParty, $value->total, $value->credits, $value->payment_status];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }


    public function filtercombinereport(Request $request, $op)
    {
        $input = $request->all('form_data');
        $moduleType = $input['moduleType'];
        $courierType = $input['courierType'];
        $operationType = $request->input('operationType');
        $billingParty = $input['billingParty'];
        $fromDate = !empty($input['fromDate']) ? date('Y-m-d', strtotime($input['fromDate'])) : '';
        $toDate = !empty($input['toDate']) ? date('Y-m-d', strtotime($input['toDate'])) : '';

        if ($moduleType == 'cargo') {
            $heading = "Cargo Invoices";
            $data = DB::table('invoices')
                ->select(['invoices.*', 'cargo.*', 'cargo.file_number as file_no'])
                ->leftjoin('cargo', 'invoices.cargo_id', '=', 'cargo.id')
                ->where('invoices.deleted', 0)
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->whereNull('file_close');

            if (!empty($fromDate) && !empty($toDate)) {
                $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            if (!empty($billingParty)) {
                $data = $data->where('invoices.bill_to', $billingParty);
            }
            if (!empty($operationType)) {
                $data = $data->where('cargo.cargo_operation_type', $operationType);
            }
        } else {
            $heading = 'Courier Invoices';
            if ($courierType == 1) {
                $heading = 'Courier Ups Invoices';
                $data = DB::table('invoices')
                    ->select(['invoices.*', 'ups_details.*', 'ups_details.file_number as file_no'])
                    ->join('ups_details', 'invoices.ups_id', '=', 'ups_details.id')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $data = $data->where('invoices.bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $data = $data->where('ups_details.courier_operation_type', $operationType);
                }
            } else if ($courierType == 2) {
                $heading = 'Courier Aeropost Invoices';
                $data = DB::table('invoices')
                    ->select(['invoices.*', 'aeropost.*', 'aeropost.file_number as file_no'])
                    ->join('aeropost', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $data = $data->where('bill_to', $billingParty);
                }
            } else if ($courierType == 3) {
                $heading = 'Courier CCPack Invoices';
                $data = DB::table('invoices')
                    ->select(['invoices.*', 'ccpack.*', 'ccpack.file_number as file_no'])
                    ->join('ccpack', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $data = $data->where('invoices.bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $data = $data->where('ccpack.ccpack_operation_type', $operationType);
                }
            } else if ($courierType == 4) {
                $heading = 'Courier UPS Master Invoices';
                $data = DB::table('invoices')
                    ->select(['invoices.*', 'ups_master.*', 'ups_master.file_number as file_no'])
                    ->join('ups_master', 'invoices.ups_master_id', '=', 'ups_master.id')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ups_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $data = $data->where('invoices.bill_to', $billingParty);
                }
                if (!empty($operationType)) {
                    $data = $data->where('ups_master.ups_operation_type', $operationType);
                }
            } else if ($courierType == 5) {
                $heading = 'Courier Aeropost Master Invoices';
                $data = DB::table('invoices')
                    ->select(['invoices.*', 'aeropost_master.*', 'aeropost_master.file_number as file_no'])
                    ->join('aeropost_master', 'invoices.aeropost_master_id', '=', 'aeropost_master.id')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.aeropost_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $data = $data->where('invoices.bill_to', $billingParty);
                }
            } else if ($courierType == 6) {
                $heading = 'Courier CCPack Master Invoices';
                $data = DB::table('invoices')
                    ->select(['invoices.*', 'ccpack_master.*', 'ccpack_master.file_number as file_no'])
                    ->join('ccpack_master', 'invoices.ccpack_master_id', '=', 'ccpack_master.id')
                    ->where('invoices.deleted', 0)
                    ->whereNotNull('invoices.ccpack_master_id')
                    ->whereNull('file_close');

                if (!empty($fromDate) && !empty($toDate)) {
                    $data = $data->whereBetween('invoices.date', array($fromDate, $toDate));
                }
                if (!empty($billingParty)) {
                    $data = $data->where('invoices.bill_to', $billingParty);
                }
            } else {
                $first = DB::table('invoices')
                    ->select(['file_no', 'awb_no', 'bill_to', 'total', 'credits', 'payment_status', 'ups_details.file_number as file_no'])
                    ->join('ups_details', 'invoices.ups_id', '=', 'ups_details.id')
                    ->whereNotNull('ups_id')
                    ->where('invoices.deleted', 0)
                    ->whereNull('file_close');

                if (!empty('courier_operation_type') && $operationType != 0) {
                    $first = $first->where('courier_operation_type', $operationType);
                }
                if (!empty($billing_party)) {
                    $first = $first->where('bill_to', $billingParty);
                }

                $second = DB::table('invoices')
                    ->select(['file_no', 'awb_no', 'bill_to', 'total', 'credits', 'payment_status', 'aeropost.file_number as file_no'])
                    ->join('aeropost', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->whereNotNull('aeropost_id')
                    ->where('invoices.deleted', 0)
                    ->whereNull('file_close');

                if (!empty($billing_party)) {
                    $second = $second->where('bill_to', $billingParty);
                }

                $third = DB::table('invoices')
                    ->select(['file_no', 'awb_no', 'bill_to', 'total', 'credits', 'payment_status', 'ups_master.file_number as file_no'])
                    ->join('ups_master', 'invoices.ups_master_id', '=', 'ups_master.id')
                    ->whereNotNull('ups_master_id')
                    ->where('invoices.deleted', 0)
                    ->whereNull('file_close');

                if (!empty('courier_operation_type') && $operationType != 0) {
                    $third = $third->where('ups_operation_type', $operationType);
                }
                if (!empty($billing_party)) {
                    $third = $third->where('bill_to', $billingParty);
                }

                $fourth = DB::table('invoices')
                    ->select(['file_no', 'awb_no', 'bill_to', 'total', 'credits', 'payment_status', 'aeropost_master.file_number as file_no'])
                    ->join('aeropost_master', 'invoices.aeropost_master_id', '=', 'aeropost_master.id')
                    ->whereNotNull('aeropost_master_id')
                    ->where('invoices.deleted', 0)
                    ->whereNull('file_close');

                if (!empty($billing_party)) {
                    $fourth = $fourth->where('bill_to', $billingParty);
                }

                $fifth = DB::table('invoices')
                    ->select(['file_no', 'awb_no', 'bill_to', 'total', 'credits', 'payment_status', 'ccpack_master.file_number as file_no'])
                    ->join('ccpack_master', 'invoices.ccpack_master_id', '=', 'ccpack_master.id')
                    ->whereNotNull('ccpack_master_id')
                    ->where('invoices.deleted', 0)
                    ->whereNull('file_close');

                if (!empty($billing_party)) {
                    $fifth = $fifth->where('bill_to', $billingParty);
                }

                $data = DB::table('invoices')
                    ->select(['file_no', 'awb_no', 'bill_to', 'total', 'credits', 'payment_status', 'ccpack.file_number as file_no'])
                    ->join('ccpack', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->whereNotNull('ccpack_id')
                    ->where('invoices.deleted', 0)
                    ->whereNull('file_close');

                if (!empty('courier_operation_type') && $operationType != 0) {
                    $data = $data->where('ccpack_operation_type', $operationType);
                }
                if (!empty($billing_party)) {
                    $data = $data->where('bill_to', $billingParty);
                }
                $data = $data->union($first)->union($second)->union($third)->union($fourth)->union($fifth);
            }
        }

        $data = $data->orderBy('invoices.id', 'desc')->get();
        if ($op == 'f') {
            return view('reports.filteredcombinereports', ['data' => $data, 'heading' => $heading]);
        } else {
            $pdf = PDF::loadview('reports.printcombinereport', ['data' => $data, 'moduleType' => $moduleType, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate]);
            $id = auth()->user()->id;
            $pdf_file = 'combinereport' . $id . '.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;

            $pdf->save($pdf_path);
            $s3path = 'Files/Reports/';
            $filecontent = file_get_contents($pdf_path);
            //$success = Storage::disk('s3')->put($s3path . 'Combine_Report(Cargo & Courier).pdf', $filecontent, 'public');
            return url('/') . '/' . $pdf_path;
        }
    }

    public function upsexpensepayments()
    {
        $today = date('Y-m-d');
        /*$dataExpenses  = DB::table('expenses')
        ->select(DB::raw('SUM(expense_details.amount) as total_expense ,ups_details.file_number'))
        ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
        ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
        ->whereNotNull('expenses.ups_details_id')
        ->where('expenses.deleted','0')
        ->where('expenses.expense_request','Disbursement done')
        ->where('expense_details.deleted','0')
        ->orderBy('expenses.ups_details_id', 'desc')
        ->groupBy('expenses.ups_details_id')
        ->get();*/

        $dataExpenses = DB::table('expenses')
            ->select(DB::raw('SUM(expense_details.amount) as total_expense,expenses.expense_id,ups_details.file_number,cashcredit.name as accountName,currency.code'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('cashcredit', 'expenses.cash_credit_account', '=', 'cashcredit.id')
            ->join('currency', 'cashcredit.currency', '=', 'currency.id')
            ->whereNotNull('expenses.ups_details_id')
            ->where('expenses.deleted', '0')
            ->where('expenses.exp_date', $today)
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expense_details.deleted', '0')
            ->orderBy('expenses.ups_details_id', 'desc')
            ->groupBy('expense_details.expense_id')
            ->get();

        $totalOfExpenseHTG = 0;
        $totalOfExpenseUSD = 0;
        foreach ($dataExpenses as $key => $value) {
            if ($value->code == 'HTG') {
                $totalOfExpenseHTG += $value->total_expense;
            } else {
                $totalOfExpenseUSD += $value->total_expense;
            }
        }

        $dataInvoices = DB::table('invoices')
            ->select(DB::raw('invoices.total,currency.code,clients.company_name'))
            ->join('clients', 'invoices.bill_to', '=', 'clients.id')
            ->join('currency', 'invoices.currency', '=', 'currency.id')
            ->whereNotNull('invoices.ups_id')
            ->where('invoices.deleted', '0')
            ->where('invoices.date', $today)
            ->orderBy('invoices.ups_id', 'desc')
            ->get();

        $totalOfInvoicesHTG = 0;
        $totalOfInvoicesUSD = 0;
        foreach ($dataInvoices as $key => $value) {
            if ($value->code == 'HTG') {
                $totalOfInvoicesHTG += $value->total;
            } else {
                $totalOfInvoicesUSD += $value->total;
            }
        }

        $dataInvoicesAmountColected = DB::table('invoices')
            ->select(DB::raw('
                        IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoice_payments.amount) as total_payments_collected,
                        invoices.id as upsInvoiceId,
                        invoice_payments.id as invoicePaymentId,
                        invoice_payments.payment_via,
                        IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                        IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                        invoices.currency as invoiceCurrency,
                        invoicec.code as invoiceCurrencyCode'))
            ->join('invoice_payments', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
            ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
            ->whereNotNull('invoices.ups_id')
            ->where('invoices.deleted', '0')
            ->where('invoices.date', $today)
            ->whereNotNull('invoice_payments.ups_id')
            ->orderBy('invoices.ups_id', 'desc')
            ->groupBy('invoice_payments.id')
            ->get();

        $totalAmountCollectedOfInvoicesHTG = 0;
        $totalAmountCollectedOfInvoicesUSD = 0;
        foreach ($dataInvoicesAmountColected as $key => $value) {
            if ($value->exchangeCurrencyCode == 'HTG') {
                $totalAmountCollectedOfInvoicesHTG += $value->total_payments_collected;
            } else {
                $totalAmountCollectedOfInvoicesUSD += $value->total_payments_collected;
            }
        }

        $max = max(count($dataInvoices), count($dataInvoices), count($dataInvoicesAmountColected)) + 2;
        /*pre(count($dataInvoices),1);
        pre(count($dataExpenses),1);
        pre(count($dataInvoicesAmountColected),1);
        pre($max);*/

        return view('reports.courier.upsexpensepayments', ['dataInvoices' => $dataInvoices, 'totalOfInvoicesHTG' => $totalOfInvoicesHTG, 'totalOfInvoicesUSD' => $totalOfInvoicesUSD, 'dataExpenses' => $dataExpenses, 'totalOfExpenseHTG' => $totalOfExpenseHTG, 'totalOfExpenseUSD' => $totalOfExpenseUSD, 'dataInvoicesAmountColected' => $dataInvoicesAmountColected, 'totalAmountCollectedOfInvoicesHTG' => $totalAmountCollectedOfInvoicesHTG, 'totalAmountCollectedOfInvoicesUSD' => $totalAmountCollectedOfInvoicesUSD, 'max' => $max]);
    }

    public function upsexpensepaymentsoutsidefiltering()
    {
        $date = date('Y-m-d', strtotime($_POST['date']));
        $today = $date;

        $dataExpenses = DB::table('expenses')
            ->select(DB::raw('SUM(expense_details.amount) as total_expense,expenses.expense_id,ups_details.file_number,cashcredit.name as accountName,currency.code'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('cashcredit', 'expenses.cash_credit_account', '=', 'cashcredit.id')
            ->join('currency', 'cashcredit.currency', '=', 'currency.id')
            ->whereNotNull('expenses.ups_details_id')
            ->where('expenses.deleted', '0')
            ->where('expenses.exp_date', $today)
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expense_details.deleted', '0')
            ->orderBy('expenses.ups_details_id', 'desc')
            ->groupBy('expense_details.expense_id')
            ->get();

        $totalOfExpenseHTG = 0;
        $totalOfExpenseUSD = 0;
        foreach ($dataExpenses as $key => $value) {
            if ($value->code == 'HTG') {
                $totalOfExpenseHTG += $value->total_expense;
            } else {
                $totalOfExpenseUSD += $value->total_expense;
            }
        }

        $dataInvoices = DB::table('invoices')
            ->select(DB::raw('invoices.total,currency.code,clients.company_name'))
            ->join('clients', 'invoices.bill_to', '=', 'clients.id')
            ->join('currency', 'invoices.currency', '=', 'currency.id')
            ->whereNotNull('invoices.ups_id')
            ->where('invoices.deleted', '0')
            ->where('invoices.date', $today)
            ->orderBy('invoices.ups_id', 'desc')
            ->groupBy('invoices.ups_id')
            ->get();

        $totalOfInvoicesHTG = 0;
        $totalOfInvoicesUSD = 0;
        foreach ($dataInvoices as $key => $value) {
            if ($value->code == 'HTG') {
                $totalOfInvoicesHTG += $value->total;
            } else {
                $totalOfInvoicesUSD += $value->total;
            }
        }

        $dataInvoicesAmountColected = DB::table('invoices')
            ->select(DB::raw('
                        IF(invoice_payments.exchange_currency IS NOT NULL, invoice_payments.exchange_amount, invoices.credits) as total_payments_collected,
                        invoices.id as upsInvoiceId,
                        invoice_payments.id as invoicePaymentId,
                        invoice_payments.payment_via,
                        IF(invoice_payments.exchange_currency IS NULL, invoices.currency, invoice_payments.exchange_currency) as exchange_currency,
                        IF(invoice_payments.exchange_currency IS NULL, invoicec.code, currency.code) as exchangeCurrencyCode,
                        invoices.currency as invoiceCurrency,
                        invoicec.code as invoiceCurrencyCode'))
            ->join('invoice_payments', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->leftjoin('currency', 'invoice_payments.exchange_currency', '=', 'currency.id')
            ->leftjoin('currency as invoicec', 'invoices.currency', '=', 'invoicec.id')
            ->whereNotNull('invoices.ups_id')
            ->where('invoices.deleted', '0')
            ->where('invoices.date', $today)
            ->whereNotNull('invoice_payments.ups_id')
            ->orderBy('invoices.ups_id', 'desc')
            ->groupBy('invoices.id')
            ->get();

        $totalAmountCollectedOfInvoicesHTG = 0;
        $totalAmountCollectedOfInvoicesUSD = 0;
        foreach ($dataInvoicesAmountColected as $key => $value) {
            if ($value->exchangeCurrencyCode == 'HTG') {
                $totalAmountCollectedOfInvoicesHTG += $value->total_payments_collected;
            } else {
                $totalAmountCollectedOfInvoicesUSD += $value->total_payments_collected;
            }
        }

        $max = max(count($dataInvoices), count($dataInvoices), count($dataInvoicesAmountColected)) + 2;

        return view('reports.courier.upsexpensepaymentsoutsidefiltering', ['dataInvoices' => $dataInvoices, 'totalOfInvoicesHTG' => $totalOfInvoicesHTG, 'totalOfInvoicesUSD' => $totalOfInvoicesUSD, 'dataExpenses' => $dataExpenses, 'totalOfExpenseHTG' => $totalOfExpenseHTG, 'totalOfExpenseUSD' => $totalOfExpenseUSD, 'dataInvoicesAmountColected' => $dataInvoicesAmountColected, 'totalAmountCollectedOfInvoicesHTG' => $totalAmountCollectedOfInvoicesHTG, 'totalAmountCollectedOfInvoicesUSD' => $totalAmountCollectedOfInvoicesUSD, 'max' => $max]);
    }

    public function upsprofitreports()
    {
        $UpsFileData = DB::table('ups_details')->where('deleted', 0)->orderBy('id', 'DESC')->get();
        //pre($UpsFileData);
        return view('reports.courier.upsprofitreports', ['UpsFileData' => $UpsFileData]);
    }

    public function viewDetail($flag, $id = '')
    {

        $querystr = DB::table('ups_details')
            ->select(['ups_details.file_number', 'ups_details.consignee_name', 'ups_details.courier_operation_type', 'invoices.total', 'invoices.id as invoice_id', 'invoice_item_details.fees_name_desc', 'billing_items.billing_name as revenue', 'costs.cost_name as costs_name', 'invoice_item_details.total_of_items as item_cost', 'expenses.expense_id as expenses_id', 'costs.id as costs_id'])
            ->leftjoin('invoices', 'ups_details.id', '=', 'invoices.ups_id')
            ->join('invoice_item_details', 'invoices.id', '=', 'invoice_item_details.invoice_id')
            ->join('billing_items', 'invoice_item_details.fees_name', '=', 'billing_items.id')
            ->leftjoin('costs', 'billing_items.code', '=', 'costs.id')
            ->leftjoin('expenses', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->where('ups_details.deleted', 0)
            ->whereNotNull('invoices.ups_id')
            ->groupBy('invoice_item_details.id');
        if ($flag == 'viewUpsDetail') {

            $basicDetail = DB::table('ups_details')->where('id', $id)->first();
            $data = $querystr->where('ups_details.id', $id)->get();
            //pre($data);

            for ($i = 0; $i < count($data); $i++) {
                $dataExpense = DB::table('expense_details')->where('expense_id', $data[$i]->expenses_id)->where('expense_type', $data[$i]->costs_id)->first();
                if (!empty($dataExpense)) {
                    $data[$i]->expences_item_amount = $dataExpense->amount;
                } else {
                    $data[$i]->expences_item_amount = 0.00;
                }
            }
            return view('reports.courier.viewUpsDetail', ['data' => $data, 'basicDetail' => $basicDetail]);
        } else {
            $data = $querystr->get();

            for ($i = 0; $i < count($data); $i++) {
                $dataExpense = DB::table('expense_details')->where('expense_id', $data[$i]->expenses_id)->where('expense_type', $data[$i]->costs_id)->first();
                if (!empty($dataExpense)) {
                    $data[$i]->expences_item_amount = $dataExpense->amount;
                } else {
                    $data[$i]->expences_item_amount = 0.00;
                }
            }
            foreach ($data as $key => $value) {
                $newArray[$value->file_number][] = $value;
            }
            //pre($newArray);
            foreach ($newArray as $key => $value) {
                $i = $key;
                $fileData = DB::table('ups_details')->where('file_number', $i)->first();
                $fileType = $fileData->courier_operation_type == 1 ? 'Import File' : 'Export File';
                $clientData = app('App\Clients')->getClientData($fileData->consignee_name);
                $clientName = !empty($clientData->company_name) ? $clientData->company_name : '-';
                $allData[] = array('File Number', $key, $fileType, '', '');
                $allData[] = array('Name Of Client', $clientName, '', '', '');
                $allData[] = array('', '', '', '', '');
                $allData[] = array('List Of Revenues', 'Amounts', 'List Of Expenses', '', 'Balance');
                $invoiceTotal = 0;
                $expenseTotal = 0;
                $balanceTotal = 0;
                foreach ($value as $skey => $data) {
                    $invoiceTotal += $data->item_cost;
                    $expenseTotal += $data->expences_item_amount;
                    $balance = $data->item_cost - $data->expences_item_amount;
                    $balanceTotal += $balance;
                    $allData[] = array(
                        'List Of Revenues' => $data->revenue,
                        'Amounts' => number_format($data->item_cost, 2),
                        'List Of Expenses' => $data->costs_name,
                        'Expense_amount' => number_format($data->expences_item_amount, 2),
                        'Balance' => number_format($balance, 2),

                    );
                }
                $allData[] = array('', number_format($invoiceTotal, 2), '', number_format($expenseTotal, 2), number_format($balanceTotal, 2));
                $allData[] = array('', '', '', '', '');
                $allData[] = array('', '', '', '', '');
            }

            $excelObj = Excel::create('upsProfitReport', function ($excel) use ($allData) {
                $excel->setTitle('Free Domicile Report');

                $excel->sheet('key', function ($sheet) use ($allData) {
                    $sheet->setAutoSize(array('A', 'B', 'C', 'D', 'E'));

                    // $sheet->setBorder('A1:E27', 'thin');
                    // $sheet->setBorder('B11:M11', 'thin');
                    $sheet->fromArray($allData, null, 'A1', false, false);
                });
            });

            $excelObj->store('xlsx', 'public/upsProfitReport/', true);
            $filecontent = file_get_contents('public/upsProfitReport/upsProfitReport.xlsx');
            $filepath = 'Files/Downloads/upsProfitReport.xlsx';
            $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
            $excelObj->download('xlsx');
        }
    }

    public function tmpReport()
    {
        $dataUps = DB::table('ups_details')
            ->select([
                'ups_details.id as id', 'ups_details.file_number', 'ups_details.courier_operation_type as opType', 'ups_details.awb_number as awb_no', 'ups_details.freight as Freight', 'invoices.id as invoice_id',
                'invoices.bill_no',
                'ups_freight_commission.commission as commission', 'invoice_item_details.fees_name_desc as billing_item', 'invoice_item_details.total_of_items'
            ])
            ->leftjoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftjoin('ups_freight_commission', 'ups_freight_commission.ups_file_id', '=', 'ups_details.id')
            ->leftjoin('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->where('ups_details.deleted', 0);

        // pre($dataUps);

        $combineData = DB::table('aeropost')
            ->select(['aeropost.id as id', 'aeropost.file_number', 'aeropost.manifest_no as opType', 'aeropost.tracking_no as awb_no', 'aeropost.freight as Freight', 'invoices.id as invoice_id', 'invoices.bill_no', 'aeropost_freight_commission.commission as commission', 'invoice_item_details.fees_name_desc as billing_item', 'invoice_item_details.total_of_items'])
            ->leftjoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
            ->leftjoin('aeropost_freight_commission', 'aeropost_freight_commission.aeropost_id', '=', 'aeropost.id')
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->where('aeropost.deleted', '0')->unionAll($dataUps)->get();

        for ($i = 0; $i < count($combineData); $i++) {
            $file_number = $combineData[$i]->file_number;
            if ($file_number[0] == 'A') {
                $combineData[$i]->file_type = 'A';
            } else {
                $combineData[$i]->file_type = 'U';
            }
        }
        //pre($combineData);
        return view('reports.courier.templeteReport', ["combineData" => $combineData]);
    }

    public function tmpReportByInvoice()
    {
        $dataUps = DB::table('invoices')->select(['invoices.bill_no', 'invoice_item_details.fees_name_desc as billing_item', 'invoice_item_details.total_of_items', 'invoices.file_no', 'invoices.currency'])
            ->leftjoin('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->whereNotNull('invoices.ups_id')
            ->where('invoices.deleted', 0);

        $combineData = DB::table('invoices')->select(['invoices.bill_no', 'invoice_item_details.fees_name_desc as billing_item', 'invoice_item_details.total_of_items', 'invoices.file_no', 'invoices.currency'])
            ->leftjoin('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->whereNotNull('invoices.aeropost_id')
            ->where('invoices.deleted', 0)->unionAll($dataUps)->get();

        //pre($combineData);
        for ($i = 0; $i < count($combineData); $i++) {
            $file_no = $combineData[$i]->file_no;
            if ($file_no[0] == 'A') {
                $combineData[$i]->file_type = 'A';
            } else {
                $combineData[$i]->file_type = 'U';
            }
        }
        //pre($combineData);

        foreach ($combineData as $key => $value) {
            $newData[$value->bill_no . '-' . $value->file_type][] = $value;
        }
        //pre($newData);
        foreach ($newData as $key => $value) {
            $currency = $value[0]->currency;
            $i = $key;
            $arr = explode('-', $i);
            $invoiceNum = $arr[0];
            $file_type = $arr[1];
            if ($file_type == 'A') {
                $invoiceData = DB::table('invoices')->where('bill_no', $invoiceNum)->first();
                $file_data = DB::table('aeropost')->where('id', $invoiceData->aeropost_id)->first();
                $billing_term = "Prepaid";
                $aeropost_file_type = 'Import';
                $client = app('App\Clients')->getClientData($file_data->consignee);
                $consignee = !empty($client->company_name) ? $client->company_name : '-';
                $date = date('d-M-Y', strtotime($file_data->date));
                $awb_no = $file_data->tracking_no;
                $billData = app('App\Clients')->getClientData($invoiceData->bill_to);
                $billingParty = !empty($billData->company_name) ? $billData->company_name : '';
                $freight = number_format($file_data->freight, 2);

                $commission_on_freight = DB::table('aeropost_freight_commission')->where('aeropost_id', $file_data->id)->first();

                if (!empty($commission_on_freight)) {
                    $commission = number_format($commission_on_freight->commission, 2);
                } else {
                    $commission = number_format(0.00, 2);
                }
                $allData[] = array('TEMPLATE', '', '', '', '', '', '');
                $allData[] = array('Prepaid/Import', '', '', '', '', '', '');
                $allData[] = array('Billing party : ' . $billingParty, '', '', 'Consignee : ' . $consignee, '', '', '');
                $allData[] = array('Facture No', 'Date', 'Agent', 'Shipment ID', 'Tracking # ', 'AWB #', '');
                $allData[] = array($invoiceNum, $date, 'Aeropost', '1ZY08746252', '', $awb_no, '');
                $allData[] = array('Description', '', 'Qte', 'Amounts', 'TCA', 'Amounts USD', '');
                $allData[] = array('Comission on freight Aeropost', '', '1', $commission, '', $commission, '');
                $allData[] = array('Freight to be paid to Aeropost', '', '1', $freight, '', $freight, '');
                $total_in_usd = $commission + $freight;
                foreach ($value as $skey => $data) {
                    $exchange_rate = DB::table('currency_exchange')->where('id', 15)->first();
                    if ($currency == 1) {
                        $item_price = $data->total_of_items;
                    } else {
                        $item_price = ($data->total_of_items) * ($exchange_rate->exchange_value);
                    }
                    $total_in_usd += $item_price;
                    $allData[] = array(
                        $data->billing_item, '', '',
                        number_format($item_price, 2), '', number_format($item_price, 2),

                    );
                }

                $allData[] = array('Total montant ', '', '', '', '', '$' . ' ' . number_format($total_in_usd, 2), '');
                $allData[] = array('', '', '', '', '', '', '');
                $allData[] = array('', '', '', '', '', '', '');
            } else {
                $invoiceData = DB::table('invoices')->where('bill_no', $invoiceNum)->first();
                $file_data = DB::table('ups_details')->where('id', $invoiceData->ups_id)->first();
                if ($file_data->fc == 1) {
                    $billing_term = "Collect";
                } else if ($file_data->fd == 1) {
                    $billing_term = "Free Domicile";
                } else if ($file_data->pp == 1) {
                    $billing_term = "Prepaid";
                }

                if ($file_data->courier_operation_type == 1) {
                    $ups_file_type = 'Import';
                } else {
                    $ups_file_type = 'Export';
                }
                $client = app('App\Clients')->getClientData($file_data->consignee_name);
                $consignee = !empty($client->company_name) ? $client->company_name : '-';
                $date = date('d-M-Y', strtotime($file_data->tdate));
                $awb_no = $file_data->awb_number;
                $billData = app('App\Clients')->getClientData($invoiceData->bill_to);
                $billingParty = !empty($billData->company_name) ? $billData->company_name : '';
                $freight = number_format($file_data->freight, 2);
                $commission_on_freight = DB::table('ups_freight_commission')->where('ups_file_id', $file_data->id)->first();
                if (!empty($commission_on_freight)) {
                    $commission = number_format($commission_on_freight->commission, 2);
                } else {
                    $commission = number_format(0.00, 2);
                }

                $allData[] = array('TEMPLATE', '', '', '', '', '', '');
                $allData[] = array($billing_term . '/' . $ups_file_type, '', '', '', '', '', '');
                $allData[] = array('Billing party : ' . $billingParty, '', '', 'Consignee : ' . $consignee, '', '', '');
                $allData[] = array('Facture No', 'Date', 'Agent', 'Shipment ID', 'Tracking # ', 'AWB #', '');
                $allData[] = array($invoiceNum, $date, 'UPS', '1ZY08746252', '', $awb_no, '');
                $allData[] = array('Description', '', 'Qte', 'Amounts', 'TCA', 'Amounts USD', '');
                $allData[] = array('Comission on freight UPS', '', '1', $commission, '', $commission, '');
                $allData[] = array('Freight to be paid to  UPS', '', '1', $freight, '', $freight, '');
                $total_in_usd = $commission + $freight;
                foreach ($value as $skey => $data) {
                    $exchange_rate = DB::table('currency_exchange')->where('id', 15)->first();
                    if ($currency == 1) {
                        $item_price = $data->total_of_items;
                    } else {
                        $item_price = ($data->total_of_items) * ($exchange_rate->exchange_value);
                    }
                    $total_in_usd += $item_price;
                    $allData[] = array(
                        $data->billing_item, '', '',
                        number_format($item_price, 2), '', number_format($item_price, 2),

                    );
                }

                $allData[] = array('Total montant ', '', '', '', '', '$' . ' ' . number_format($total_in_usd, 2), '');
                $allData[] = array('', '', '', '', '', '', '');
                $allData[] = array('', '', '', '', '', '', '');
            }
        }
        //pre($allData);
        $excelObj = Excel::create('ConsolidationReport', function ($excel) use ($allData) {
            $excel->setTitle('Consolidation Report');

            $excel->sheet('Consolidation Report', function ($sheet) use ($allData) {
                $sheet->setAutoSize(array('A', 'B', 'C', 'D', 'E', 'F'));
                $sheet->setWidth('G', 20);

                // $sheet->setBorder('A1:E27', 'thin');
                // $sheet->setBorder('B11:M11', 'thin');
                //$sheet->mergeCells('A3:C3');
                //$sheet->mergeCells('D3:F3');
                $count = 0;
                foreach ($allData as $key => $value) {
                    $row = $key + 1;
                    foreach ($value as $key => $val) {
                        $newArr = explode(' ', trim($val));
                        if ($newArr[0] == "Billing") {
                            $str = 'A' . $row . ':' . 'C' . $row;
                            $strcon = 'D' . $row . ':' . 'F' . $row;
                            $sheet->mergeCells($str);
                            $sheet->setHeight($row, 20);
                            $sheet->cells($str, function ($cells) {
                                $cells->setFont(array(
                                    'family' => 'Calibri',
                                    'size' => '14',
                                    'bold' => true,
                                ));
                            });
                            $sheet->mergeCells($strcon);
                            $sheet->setHeight($row, 20);
                            $sheet->cells($strcon, function ($cells) {
                                $cells->setFont(array(
                                    'family' => 'Calibri',
                                    'size' => '14',
                                    'bold' => true,
                                ));
                            });
                        }
                        if ($newArr[0] == "AWB") {
                            $nextRow = $row + 1;
                            $str = 'F' . $row . ':' . 'G' . $row;
                            $strcon = 'F' . $nextRow . ':' . 'G' . $nextRow;
                            $sheet->mergeCells($str);

                            $sheet->cells('A' . $row . ':' . 'G' . $row, function ($cells) {
                                $cells->setFont(array(
                                    'family' => 'Calibri',
                                    'size' => '11',
                                    'bold' => true,
                                ));
                                $cells->setValignment('center');
                            });
                            $sheet->mergeCells($strcon);

                            $sheet->cells('A' . $nextRow . ':' . 'G' . $nextRow, function ($cells) {
                                $cells->setFont(array(
                                    'family' => 'Calibri',
                                    'size' => '11',
                                    'bold' => true,
                                ));
                                $cells->setValignment('center');
                            });
                        }
                        if ($newArr[0] == "Description") {
                            for ($i = 0; $i < 3; $i++) {
                                $str = 'A' . ($row + $i) . ':' . 'B' . ($row + $i);
                                $sheet->mergeCells($str);
                            }
                        }

                        if ($newArr[0] == 'Total') {
                            $sheet->cells('A' . $row . ':' . 'G' . $row, function ($cells) {
                                $cells->setFont(array(
                                    'family' => 'Calibri',
                                    'size' => '11',
                                    'bold' => true,
                                ));
                            });
                        }
                    }
                }
                // pre($count);
                $sheet->fromArray($allData, null, 'A1', false, false);
            });
        });

        $excelObj->store('xlsx', 'public/templateReport/', true);
        $filecontent = file_get_contents('public/templateReport/ConsolidationReport.xlsx');
        $filepath = 'Files/Downloads/ConsolidationReport.xlsx';
        $success = Storage::disk('s3')->put($filepath, $filecontent, 'public');
        $excelObj->download('xlsx');
        // pre($newData);

    }

    public function statementofaccounts()
    {
        $clients = DB::table('clients')
            ->select(DB::raw('clients.id,clients.company_name'))
            ->join('invoices', 'invoices.bill_to', '=', 'clients.id')
            ->where(function ($query) {
                $query->where('payment_status', 'Pending')
                    ->orWhere('payment_status', 'Partial');
            })
            ->where('invoices.deleted', 0)
            ->where('clients.deleted', 0)
            ->where('clients.client_flag', 'B')
            ->where('clients.status', 1)
            ->groupBy('invoices.bill_to')
            ->orderBy('clients.id', 'desc')->get();

        /* $clientAr = array();
        foreach($clients as $k => $v)
        {
        $clientAr[] = $v->id;
        }

        $totalDueAmountOfCargoInvoice = DB::table('invoices')
        ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue'))
        ->where('deleted',0)
        ->whereIn('bill_to',$clientAr)
        ->where(function ($query) {
        $query->where('payment_status','Pending')
        ->orWhere('payment_status','Partial');
        })->orderBy('id', 'desc')->get(); */

        return view("reports.statementofaccounts", ['clients' => $clients]);
    }

    public function getdueinvoicesofclient($clientId = null)
    {
        return view("reports.getdueinvoicesofclient", ['clientId' => $clientId]);
    }

    public function listgetdueinvoicesofclient(Request $request)
    {
        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $moduleInvoice = $req['moduleInvoice'];
        $clientId = $req['clientId'];

        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];

        if ($moduleInvoice == 'Cargo') {
            $orderby = ['cargo.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'House File') {
            $orderby = ['hawb_files.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'UPS') {
            $orderby = ['ups_details.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'upsMaster') {
            $orderby = ['ups_master.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'Aeropost') {
            $orderby = ['aeropost.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'aeropostMaster') {
            $orderby = ['aeropost_master.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'ccpackMaster') {
            $orderby = ['ccpack_master.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        } else if ($moduleInvoice == 'CCPack') {
            $orderby = ['ccpack.id', 'invoices.id', 'invoices.date', '', 'invoices.bill_no', 'invoices.total', 'invoices.balance_of'];
        }


        if ($moduleInvoice == 'Cargo') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('cargo', 'cargo.id', '=', 'invoices.cargo_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.cargo_id as modeuleCargoId, cargo.file_number as fileNumber, cargo.awb_bl_no as trackingNumber, invoices.total as totalAmount'))
                ->join('cargo', 'cargo.id', '=', 'invoices.cargo_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('cargo', 'cargo.id', '=', 'invoices.cargo_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'House File') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.hawb_hbl_no')
                ->where('housefile_module', 'cargo')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });

            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.hawb_hbl_no as modeuleHouseFileId, hawb_files.file_number as fileNumber, 
            IF(hawb_files.cargo_operation_type = 1, hawb_files.hawb_hbl_no, hawb_files.export_hawb_hbl_no) as trackingNumber,invoices.total as totalAmount'))
                ->join('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.hawb_hbl_no')
                ->where('housefile_module', 'cargo')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.hawb_hbl_no')
                ->where('housefile_module', 'cargo')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'UPS') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ups_id as modeuleUpsId, ups_details.file_number as fileNumber, ups_details.awb_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'upsMaster') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('ups_master', 'ups_master.id', '=', 'invoices.ups_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ups_master_id as modeuleUpsMasterId, ups_master.file_number as fileNumber, ups_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ups_master', 'ups_master.id', '=', 'invoices.ups_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('ups_master', 'ups_master.id', '=', 'invoices.ups_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'Aeropost') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.aeropost_id as modeuleAeropostId, aeropost.file_number as fileNumber, aeropost.tracking_no as trackingNumber, invoices.total as totalAmount'))
                ->join('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'aeropostMaster') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('aeropost_master', 'aeropost_master.id', '=', 'invoices.aeropost_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.aeropost_master_id as modeuleAeropostMasterId, aeropost_master.file_number as fileNumber, aeropost_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                ->join('aeropost_master', 'aeropost_master.id', '=', 'invoices.aeropost_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('aeropost_master', 'aeropost_master.id', '=', 'invoices.aeropost_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'ccpackMaster') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('ccpack_master', 'ccpack_master.id', '=', 'invoices.ccpack_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ccpack_master_id as modeuleCcpackMasterId, ccpack_master.file_number as fileNumber, ccpack_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ccpack_master', 'ccpack_master.id', '=', 'invoices.ccpack_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('ccpack_master', 'ccpack_master.id', '=', 'invoices.ccpack_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        } else if ($moduleInvoice == 'CCPack') {
            $total = Invoices::selectRaw('count(*) as total')
                ->join('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $query = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ccpack_id as modeuleCcpackId, ccpack.file_number as fileNumber, ccpack.awb_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('invoices')
                ->join('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                })->orderBy('invoices.id', 'desc')->groupBy('invoices.id');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
            }
        }
        //$total = $total->orderBy('invoices.id', 'desc')->groupBy('invoices.id')->first();
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $moduleInvoice) {
                if ($moduleInvoice == 'Cargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('cargo.awb_bl_no', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'House File') {
                    $query2->where('hawb_files.file_number', 'like', '%' . $search . '%')
                        ->orWhere('hawb_files.hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('hawb_files.export_hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'UPS') {
                    $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'upsMaster') {
                    $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_master.tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'Aeropost') {
                    $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                        ->orWhere('aeropost.tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'aeropostMaster') {
                    $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('aeropost_master.tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'ccpackMaster') {
                    $query2->where('ccpack_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ccpack_master.tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'CCPack') {
                    $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ccpack.awb_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                }
            });

            $filteredq->where(function ($query2) use ($search, $moduleInvoice) {
                if ($moduleInvoice == 'Cargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('cargo.awb_bl_no', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'House File') {
                    $query2->where('hawb_files.file_number', 'like', '%' . $search . '%')
                        ->orWhere('hawb_files.hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('hawb_files.export_hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'UPS') {
                    $query2->where('ups_details.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_details.awb_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'upsMaster') {
                    $query2->where('ups_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ups_master.tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'Aeropost') {
                    $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                        ->orWhere('aeropost.tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'aeropostMaster') {
                    $query2->where('aeropost_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('aeropost_master.tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'ccpackMaster') {
                    $query2->where('ccpack_master.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ccpack_master.tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                } else if ($moduleInvoice == 'CCPack') {
                    $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
                        ->orWhere('ccpack.awb_number', 'like', '%' . $search . '%')
                        ->orWhere('invoices.bill_no', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }

        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {

            $action = '<div class="dropdown">';
            $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';
            $action .= '<li>';
            if ($moduleInvoice == 'Cargo') {
                $moduleId = $value->modeuleCargoId;
                $action .= '<a target="_blank" href="' . route('addinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'House File') {
                $moduleId = $value->modeuleHouseFileId;
                $action .= '<a target="_blank" href="' . route('addinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'UPS') {
                $moduleId = $value->modeuleUpsId;
                $action .= '<a target="_blank" href="' . route('addupsinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'upsMaster') {
                $moduleId = $value->modeuleUpsMasterId;
                $action .= '<a target="_blank" href="' . route('addupsinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'Aeropost') {
                $moduleId = $value->modeuleAeropostId;
                $action .= '<a target="_blank" href="' . route('addaeropostinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'aeropostMaster') {
                $moduleId = $value->modeuleAeropostMasterId;
                $action .= '<a target="_blank" href="' . route('addupsinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'ccpackMaster') {
                $moduleId = $value->modeuleCcpackMasterId;
                $action .= '<a target="_blank" href="' . route('addupsinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            } else if ($moduleInvoice == 'CCPack') {
                $moduleId = $value->modeuleCcpackId;
                $action .= '<a target="_blank" href="' . route('addccpackinvoicepayment', [$moduleId, $value->invoiceId, 0]) . '">Add Payment</a>';
            }
            $action .= '</li></ul></div>';

            $data[] = [$moduleId, $value->invoiceId,  date('d-m-Y', strtotime($value->date)), '#' . $value->fileNumber . ', ' . $value->trackingNumber, $value->bill_no, number_format($value->totalAmount, 2), number_format($value->totalDue, 2), $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function getduefilteredinvoicesofclient()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $moduleId = $_POST['moduleInvoice'];
        $clientId = $_POST['clientId'];
        $submitButtonName = $_POST['submitButtonName'];

        $clientData = DB::table('clients')->where('id', $clientId)->first();

        if ($submitButtonName == 'clsPrintAll') {
            // Cargo
            $allDueCargo = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.cargo_id as modeuleCargoId, cargo.file_number as fileNumber, cargo.awb_bl_no as trackingNumber, invoices.total as totalAmount'))
                ->join('cargo', 'cargo.id', '=', 'invoices.cargo_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.cargo_id')
                ->whereNull('invoices.housefile_module')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueCargo = $allDueCargo->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueCargo = $allDueCargo->groupBy('invoices.id')->get()->toArray();

            // House File
            $allDueHouseFile = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.hawb_hbl_no as modeuleHouseFileId, hawb_files.file_number as fileNumber, 
            IF(hawb_files.cargo_operation_type = 1, hawb_files.hawb_hbl_no, hawb_files.export_hawb_hbl_no) as trackingNumber,invoices.total as totalAmount'))
                ->join('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.hawb_hbl_no')
                ->where('housefile_module', 'cargo')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueHouseFile = $allDueHouseFile->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueHouseFile = $allDueHouseFile->groupBy('invoices.id')->get()->toArray();

            // UPS
            $allDueUps = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ups_id as modeuleUpsId, ups_details.file_number as fileNumber, ups_details.awb_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueUps = $allDueUps->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueUps = $allDueUps->groupBy('invoices.id')->get()->toArray();

            // UPS Master
            $allDueUpsMaster = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ups_master_id as modeuleUpsMasterId, ups_master.file_number as fileNumber, ups_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ups_master', 'ups_master.id', '=', 'invoices.ups_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ups_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueUpsMaster = $allDueUpsMaster->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueUpsMaster = $allDueUpsMaster->groupBy('invoices.id')->get()->toArray();

            // Aeropost Master
            $allDueAeropostMaster = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.aeropost_master_id as modeuleAeropostMasterId, aeropost_master.file_number as fileNumber, aeropost_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                ->join('aeropost_master', 'aeropost_master.id', '=', 'invoices.aeropost_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueAeropostMaster = $allDueAeropostMaster->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueAeropostMaster = $allDueAeropostMaster->groupBy('invoices.id')->get()->toArray();

            // CCPack Master
            $allDueCcpackMaster = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ccpack_master_id as modeuleCcpackMasterId, ccpack_master.file_number as fileNumber, ccpack_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ccpack_master', 'ccpack_master.id', '=', 'invoices.ccpack_master_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_master_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueCcpackMaster = $allDueCcpackMaster->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueCcpackMaster = $allDueCcpackMaster->groupBy('invoices.id')->get()->toArray();

            // Aeropost
            $allDueAeropost = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.aeropost_id as modeuleAeropostId, aeropost.file_number as fileNumber, aeropost.tracking_no as trackingNumber, invoices.total as totalAmount'))
                ->join('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.aeropost_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueAeropost = $allDueAeropost->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueAeropost = $allDueAeropost->groupBy('invoices.id')->get()->toArray();

            // CCPack
            $allDueCCPack = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ccpack_id as modeuleCcpackId, ccpack.file_number as fileNumber, ccpack.awb_number as trackingNumber, invoices.total as totalAmount'))
                ->join('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
                ->where('invoices.deleted', 0)
                ->where('bill_to', $clientId)
                ->where('invoices.total', '!=', '0.00')
                ->whereNotNull('invoices.ccpack_id')
                ->where(function ($query) {
                    $query->where('payment_status', 'Pending')
                        ->orWhere('payment_status', 'Partial');
                });
            if (!empty($fromDate) && !empty($toDate)) {
                $allDueCCPack = $allDueCCPack->whereBetween('invoices.date', array($fromDate, $toDate));
            }
            $allDueCCPack = $allDueCCPack->groupBy('invoices.id')->get()->toArray();

            $allDue = array_merge($allDueCargo, $allDueHouseFile, $allDueUps, $allDueAeropost, $allDueCCPack, $allDueUpsMaster, $allDueAeropostMaster, $allDueCcpackMaster);
            $query1 = array();
            foreach ($allDue as $key => $row) {
                // replace 0 with the field's index/key
                $query1[$key] = $row->date;
            }
            array_multisort((array) $query1, SORT_ASC, $allDue);
        } else {
            if ($moduleId == 'Cargo') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.cargo_id as modeuleCargoId, cargo.file_number as fileNumber, cargo.awb_bl_no as trackingNumber, invoices.total as totalAmount'))
                    ->join('cargo', 'cargo.id', '=', 'invoices.cargo_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.cargo_id')
                    ->whereNull('invoices.housefile_module')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            }
            if ($moduleId == 'House File') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.hawb_hbl_no as modeuleHouseFileId, hawb_files.file_number as fileNumber, 
            IF(hawb_files.cargo_operation_type = 1, hawb_files.hawb_hbl_no, hawb_files.export_hawb_hbl_no) as trackingNumber,invoices.total as totalAmount'))
                    ->join('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.hawb_hbl_no')
                    ->where('housefile_module', 'cargo')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            } elseif ($moduleId == 'UPS') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ups_id as modeuleUpsId, ups_details.file_number as fileNumber, ups_details.awb_number as trackingNumber, invoices.total as totalAmount'))
                    ->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.ups_id')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            } elseif ($moduleId == 'upsMaster') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ups_master_id as modeuleUpsMasterId, ups_master.file_number as fileNumber, ups_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                    ->join('ups_master', 'ups_master.id', '=', 'invoices.ups_master_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.ups_master_id')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            } elseif ($moduleId == 'Aeropost') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.aeropost_id as modeuleAeropostId, aeropost.file_number as fileNumber, aeropost.tracking_no as trackingNumber, invoices.total as totalAmount'))
                    ->join('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.aeropost_id')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            } elseif ($moduleId == 'aeropostMaster') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.aeropost_master_id as modeuleAeropostMasterId, aeropost_master.file_number as fileNumber, aeropost_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                    ->join('aeropost_master', 'aeropost_master.id', '=', 'invoices.aeropost_master_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.aeropost_master_id')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            } elseif ($moduleId == 'ccpackMaster') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ccpack_master_id as modeuleCcpackMasterId, ccpack_master.file_number as fileNumber, ccpack_master.tracking_number as trackingNumber, invoices.total as totalAmount'))
                    ->join('ccpack_master', 'ccpack_master.id', '=', 'invoices.ccpack_master_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.ccpack_master_id')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            } elseif ($moduleId == 'CCPack') {
                $allDue = DB::table('invoices')
                    ->select(DB::raw('SUM(invoices.total-invoices.credits) as totalDue,invoices.date,invoices.bill_no,invoices.file_no,invoices.currency,invoices.bill_to,invoices.id as invoiceId,invoices.ccpack_id as modeuleCcpackId, ccpack.file_number as fileNumber, ccpack.awb_number as trackingNumber, invoices.total as totalAmount'))
                    ->join('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
                    ->where('invoices.deleted', 0)
                    ->where('bill_to', $clientId)
                    ->where('invoices.total', '!=', '0.00')
                    ->whereNotNull('invoices.ccpack_id')
                    ->where(function ($query) {
                        $query->where('payment_status', 'Pending')
                            ->orWhere('payment_status', 'Partial');
                    });
                if (!empty($fromDate) && !empty($toDate)) {
                    $allDue = $allDue->whereBetween('invoices.date', array($fromDate, $toDate));
                }
            }
            $allDue = $allDue->orderBy('invoices.date', 'asc')->groupBy('invoices.id')->get()->toArray();
        }
        $pdf = PDF::loadView('reports.printDueInvoices', ['allDue' => $allDue, 'clientData' => $clientData]);
        $pdf_file = 'dueInvoices.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function reportsinvoicesfiles()
    {
        return view("reports.invoicesandfiles");
    }

    public function listbydatatableserversideinreports(Request $request)
    {

        $req = $request->all();

        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($column == 1)
            $column = 0;
        $orderby = ['invoices.id', 'bill_no', 'invoices.date', 'c1.company_name', 'total'];

        if ($req['typeFileOrInvoice'] == 'Invoices') {
            $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
            $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';

            $total = Invoices::selectRaw('count(*) as total')->where('invoices.deleted', '0');
            if (!empty($fromDate) && !empty($toDate))
                $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));

            $total = $total->first();
            $totalfiltered = $total->total;

            $query = DB::table('invoices')
                ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                ->selectRaw('invoices.*')
                ->where('invoices.deleted', '0');
            if (!empty($fromDate) && !empty($toDate))
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));



            $filteredq = DB::table('invoices')
                ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
                ->where('invoices.deleted', '0');
            if (!empty($fromDate) && !empty($toDate))
                $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));

            if ($search != '') {
                $query->where(function ($query2) use ($search) {
                    $query2->where('invoices.date', 'like', '%' . $search . '%')
                        ->orWhere('bill_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('total', 'like', '%' . $search . '%');
                });
                $filteredq->where(function ($query2) use ($search) {
                    $query2->where('invoices.date', 'like', '%' . $search . '%')
                        ->orWhere('bill_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('total', 'like', '%' . $search . '%');
                });
                $filteredq = $filteredq->selectRaw('count(*) as total')->first();
                $totalfiltered = $filteredq->total;
            }


            $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
            $data = [];
            foreach ($query as $key => $items) {
                $dataBillingParty = app('App\Clients')->getClientData($items->bill_to);
                $data[] = [$items->id, !empty($items->bill_no) ? $items->bill_no : '-', date('d-m-Y', strtotime($items->date)), !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", number_format($items->total, 2)];
            }
        } else {
            $cargoData = Cargo::selectRaw('count(*) as total')->where('cargo.deleted', '0')
                ->first();
            $upsData = Ups::selectRaw('count(*) as total')->where('cargo.deleted', '0')
                ->first();
            $aeropostData = Aeropost::selectRaw('count(*) as total')->where('cargo.deleted', '0')
                ->first();
            $ccPackData = ccpack::selectRaw('count(*) as total')->where('cargo.deleted', '0')
                ->first();
            $houseFileData = HawbFiles::selectRaw('count(*) as total')->where('cargo.deleted', '0')
                ->first();
            $totalfiltered = $cargoData->total + $upsData->total + $aeropostData->total + $ccPackData->total + $houseFileData->total;

            $query = DB::table('cargo')
                ->selectRaw('cargo.*')
                ->where('cargo.deleted', '0');


            $filteredq = DB::table('invoices')
                ->where('invoices.deleted', '0');


            $query = $query->orderBy('id', 'desc')->offset($start)->limit($length)->get();
        }

        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function genericdisbursementreport($flag = null, $fromDate = null, $toDate = null, $cashBank = null, $cashier = null)
    {
        $cashBankSingle = array();
        if (!empty($flag)) {
            $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
            $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';
            $cashBankP = !empty($cashBank) ? $cashBank : '';
            $cashierP = !empty($cashier) ? explode(',', $cashier) : '';

            $cashBankSingle = DB::table('cashcredit')->select(['cashcredit.id', 'cashcredit.name', 'currency.code as currencyCode'])
                ->join('currency', 'currency.id', '=', 'cashcredit.currency')
                ->where('cashcredit.id', $cashBankP)->first();
        } else {
            if (!empty($_POST['submit'])) {
                $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
                $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
                $cashBankP = !empty($_POST['cashBank']) ? $_POST['cashBank'] : '';

                if (!empty($_POST['cashier']) && !is_array($_POST['cashier']))
                    $_POST['cashier'] = (array) $_POST['cashier'];

                $cashierP = !empty($_POST['cashier']) ? $_POST['cashier'] : '';

                $cashBankSingle = DB::table('cashcredit')->select(['cashcredit.id', 'cashcredit.name', 'currency.code as currencyCode'])
                    ->join('currency', 'currency.id', '=', 'cashcredit.currency')
                    ->where('cashcredit.id', $cashBankP)->first();
            } else {
                if (checkloggedinuserdata() == 'Cashier') {
                    $fromDate = date('Y-m-d');
                    $toDate = date('Y-m-d');
                    $cashierP[] = auth()->user()->id;
                    $cashBankP = !empty(auth()->user()->default_cashbank_account_for_report) ? auth()->user()->default_cashbank_account_for_report : '000';

                    $cashBankSingle = DB::table('cashcredit')->select(['cashcredit.id', 'cashcredit.name', 'currency.code as currencyCode'])
                        ->join('currency', 'currency.id', '=', 'cashcredit.currency')
                        ->where('cashcredit.id', $cashBankP)->first();
                } else {
                    $fromDate = '';
                    $toDate = '';
                }
            }
        }



        $cashBank = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashBank = json_decode($cashBank, 1);
        ksort($cashBank);

        /* $cashBank = DB::table('cashcredit')->select(DB::raw('id,CONCAT(name," - ",currency_code) as cashcreditData'))->where('deleted',0)->where('status',1)->pluck('cashcreditData', 'id');
        $cashBank = json_decode($cashBank,1);
        ksort($cashBank); */

        $cashier = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');

        $cargoExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'cargo.file_number', 'expenses.voucher_number'])
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $cargoExpancesDetail = $cargoExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $cargoExpancesDetail = $cargoExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $cargoExpancesDetail = $cargoExpancesDetail->get()->toArray();

        $houseFileExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'hawb_files.file_number', 'expenses.voucher_number'])
            ->join('hawb_files', 'expenses.house_file_id', '=', 'hawb_files.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $houseFileExpancesDetail = $houseFileExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $houseFileExpancesDetail = $houseFileExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $houseFileExpancesDetail = $houseFileExpancesDetail->get()->toArray();

        $upsExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'ups_details.file_number', 'expenses.voucher_number'])
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $upsExpancesDetail = $upsExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $upsExpancesDetail = $upsExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $upsExpancesDetail = $upsExpancesDetail->get()->toArray();

        $upsMasterExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'ups_master.file_number', 'expenses.voucher_number'])
            ->join('ups_master', 'expenses.ups_master_id', '=', 'ups_master.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $upsMasterExpancesDetail = $upsMasterExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $upsMasterExpancesDetail = $upsMasterExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $upsMasterExpancesDetail = $upsMasterExpancesDetail->get()->toArray();

        $aeropostExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'aeropost.file_number', 'expenses.voucher_number'])
            ->join('aeropost', 'expenses.aeropost_id', '=', 'aeropost.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $aeropostExpancesDetail = $aeropostExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $aeropostExpancesDetail = $aeropostExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $aeropostExpancesDetail = $aeropostExpancesDetail->get()->toArray();

        $aeropostMasterExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'aeropost_master.file_number', 'expenses.voucher_number'])
            ->join('aeropost_master', 'expenses.aeropost_master_id', '=', 'aeropost_master.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $aeropostMasterExpancesDetail = $aeropostMasterExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $aeropostMasterExpancesDetail = $aeropostMasterExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $aeropostMasterExpancesDetail = $aeropostMasterExpancesDetail->get()->toArray();

        $ccpackExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'ccpack.file_number', 'expenses.voucher_number'])
            ->join('ccpack', 'expenses.ccpack_id', '=', 'ccpack.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $ccpackExpancesDetail = $ccpackExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $ccpackExpancesDetail = $ccpackExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $ccpackExpancesDetail = $ccpackExpancesDetail->get()->toArray();

        $ccpackMasterExpancesDetail = DB::table('expenses')
            ->select(['expenses.*', 'expense_details.*', 'expenses.cash_credit_account as c_credit_account', 'users.name', 'ccpack_master.file_number', 'expenses.voucher_number'])
            ->join('ccpack_master', 'expenses.ccpack_master_id', '=', 'ccpack_master.id')
            ->join('users', 'users.id', '=', 'expenses.disbursed_by')
            ->leftJoin('expense_details', 'expenses.expense_id', '=', 'expense_details.expense_id')
            //->where('expenses.disbursed_by', $id)
            ->where('expenses.expense_request', 'Disbursement done')
            ->orderBy('expenses.disbursed_datetime', 'desc')
            ->where('expenses.deleted', 0)
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $ccpackMasterExpancesDetail = $ccpackMasterExpancesDetail->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $ccpackMasterExpancesDetail = $ccpackMasterExpancesDetail->whereIn('expenses.disbursed_by', $cashierP);
        $ccpackMasterExpancesDetail = $ccpackMasterExpancesDetail->get()->toArray();

        // Total cargo expense
        $totalCargoExpenseOfHtg = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalCargoExpenseOfHtg = $totalCargoExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalCargoExpenseOfHtg = $totalCargoExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalCargoExpenseOfHtg = $totalCargoExpenseOfHtg->get()->first();
        $totalCargoExpenseOfHtgCount = $totalCargoExpenseOfHtg->total;


        $totalCargoExpenseOfUSD = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalCargoExpenseOfUSD = $totalCargoExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalCargoExpenseOfUSD = $totalCargoExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalCargoExpenseOfUSD = $totalCargoExpenseOfUSD->get()->first();
        $totalCargoExpenseOfUSDCount = $totalCargoExpenseOfUSD->total;

        // Total Housefile expense
        $totalHouseFileExpenseOfHtg = DB::table('expenses')
            ->join('hawb_files', 'expenses.house_file_id', '=', 'hawb_files.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalHouseFileExpenseOfHtg = $totalHouseFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalHouseFileExpenseOfHtg = $totalHouseFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalHouseFileExpenseOfHtg = $totalHouseFileExpenseOfHtg->get()->first();
        $totalHouseFileExpenseOfHtgCount = $totalHouseFileExpenseOfHtg->total;


        $totalHouseFileExpenseOfUSD = DB::table('expenses')
            ->join('hawb_files', 'expenses.house_file_id', '=', 'hawb_files.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalHouseFileExpenseOfUSD = $totalHouseFileExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalHouseFileExpenseOfUSD = $totalHouseFileExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalHouseFileExpenseOfUSD = $totalHouseFileExpenseOfUSD->get()->first();
        $totalHouseFileExpenseOfUSDCount = $totalHouseFileExpenseOfUSD->total;

        // Total Ups expense
        $totalUpsFileExpenseOfHtg = DB::table('expenses')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalUpsFileExpenseOfHtg = $totalUpsFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalUpsFileExpenseOfHtg = $totalUpsFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalUpsFileExpenseOfHtg = $totalUpsFileExpenseOfHtg->get()->first();
        $totalUpsExpenseOfHtgCount = $totalUpsFileExpenseOfHtg->total;


        $totalUpsExpenseOfUSD = DB::table('expenses')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalUpsExpenseOfUSD = $totalUpsExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalUpsExpenseOfUSD = $totalUpsExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalUpsExpenseOfUSD = $totalUpsExpenseOfUSD->get()->first();
        $totalUpsExpenseOfUSDCount = $totalUpsExpenseOfUSD->total;


        // Total Ups Master expense
        $totalUpsMasterFileExpenseOfHtg = DB::table('expenses')
            ->join('ups_master', 'expenses.ups_master_id', '=', 'ups_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalUpsMasterFileExpenseOfHtg = $totalUpsMasterFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalUpsMasterFileExpenseOfHtg = $totalUpsMasterFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalUpsMasterFileExpenseOfHtg = $totalUpsMasterFileExpenseOfHtg->get()->first();
        $totalUpsMasterExpenseOfHtgCount = $totalUpsMasterFileExpenseOfHtg->total;


        $totalUpsMasterFileExpenseOfUSD = DB::table('expenses')
            ->join('ups_master', 'expenses.ups_master_id', '=', 'ups_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalUpsMasterFileExpenseOfUSD = $totalUpsMasterFileExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalUpsMasterFileExpenseOfUSD = $totalUpsMasterFileExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalUpsMasterFileExpenseOfUSD = $totalUpsMasterFileExpenseOfUSD->get()->first();
        $totalUpsMasterExpenseOfUSDCount = $totalUpsMasterFileExpenseOfUSD->total;

        // Total Aeropost expense
        $totalAeropostFileExpenseOfHtg = DB::table('expenses')
            ->join('aeropost', 'expenses.aeropost_id', '=', 'aeropost.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalAeropostFileExpenseOfHtg = $totalAeropostFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalAeropostFileExpenseOfHtg = $totalAeropostFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalAeropostFileExpenseOfHtg = $totalAeropostFileExpenseOfHtg->get()->first();
        $totalAeropostExpenseOfHtgCount = $totalAeropostFileExpenseOfHtg->total;


        $totalAeropostExpenseOfUSD = DB::table('expenses')
            ->join('aeropost', 'expenses.aeropost_id', '=', 'aeropost.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalAeropostExpenseOfUSD = $totalAeropostExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalAeropostExpenseOfUSD = $totalAeropostExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalAeropostExpenseOfUSD = $totalAeropostExpenseOfUSD->get()->first();
        $totalAeropostExpenseOfUSDCount = $totalAeropostExpenseOfUSD->total;

        // Total Aeropost Master expense
        $totalAeropostMasterFileExpenseOfHtg = DB::table('expenses')
            ->join('aeropost_master', 'expenses.aeropost_master_id', '=', 'aeropost_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalAeropostMasterFileExpenseOfHtg = $totalAeropostMasterFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalAeropostMasterFileExpenseOfHtg = $totalAeropostMasterFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalAeropostMasterFileExpenseOfHtg = $totalAeropostMasterFileExpenseOfHtg->get()->first();
        $totalAeropostMasterExpenseOfHtgCount = $totalAeropostMasterFileExpenseOfHtg->total;


        $totalAeropostMasterFileExpenseOfUSD = DB::table('expenses')
            ->join('aeropost_master', 'expenses.aeropost_master_id', '=', 'aeropost_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalAeropostMasterFileExpenseOfUSD = $totalAeropostMasterFileExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalAeropostMasterFileExpenseOfUSD = $totalAeropostMasterFileExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalAeropostMasterFileExpenseOfUSD = $totalAeropostMasterFileExpenseOfUSD->get()->first();
        $totalAeropostMasterExpenseOfUSDCount = $totalAeropostMasterFileExpenseOfUSD->total;

        // Total CCPack expense
        $totalCCPackFileExpenseOfHtg = DB::table('expenses')
            ->join('ccpack', 'expenses.ccpack_id', '=', 'ccpack.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalCCPackFileExpenseOfHtg = $totalCCPackFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalCCPackFileExpenseOfHtg = $totalCCPackFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalCCPackFileExpenseOfHtg = $totalCCPackFileExpenseOfHtg->get()->first();
        $totalCCPackExpenseOfHtgCount = $totalCCPackFileExpenseOfHtg->total;


        $totalCCPackExpenseOfUSD = DB::table('expenses')
            ->join('ccpack', 'expenses.ccpack_id', '=', 'ccpack.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalCCPackExpenseOfUSD = $totalCCPackExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalCCPackExpenseOfUSD = $totalCCPackExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalCCPackExpenseOfUSD = $totalCCPackExpenseOfUSD->get()->first();
        $totalCCPackExpenseOfUSDCount = $totalCCPackExpenseOfUSD->total;

        // Total CCPack Master expense
        $totalCcpackMasterFileExpenseOfHtg = DB::table('expenses')
            ->join('ccpack_master', 'expenses.ccpack_master_id', '=', 'ccpack_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->join('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalCcpackMasterFileExpenseOfHtg = $totalCcpackMasterFileExpenseOfHtg->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalCcpackMasterFileExpenseOfHtg = $totalCcpackMasterFileExpenseOfHtg->whereIn('expenses.disbursed_by', $cashierP);
        $totalCcpackMasterFileExpenseOfHtg = $totalCcpackMasterFileExpenseOfHtg->get()->first();
        $totalCcpackMasterExpenseOfHtgCount = $totalCcpackMasterFileExpenseOfHtg->total;


        $totalCcpackMasterFileExpenseOfUSD = DB::table('expenses')
            ->join('ccpack_master', 'expenses.ccpack_master_id', '=', 'ccpack_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            //->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            //->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->join('cashcredit', 'cashcredit.id', '=', 'expenses.cash_credit_account')
            ->join('currency', 'currency.id', '=', 'cashcredit.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.expense_request', 'Disbursement done')
            ->where('expenses.deleted', 0)
            //->where('expenses.disbursed_by', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            ->whereBetween(DB::raw("DATE(expenses.disbursed_datetime)"), array($fromDate, $toDate));
        if (!empty($cashBankP))
            $totalCcpackMasterFileExpenseOfUSD = $totalCcpackMasterFileExpenseOfUSD->where('expenses.cash_credit_account', $cashBankP);
        if (!empty($cashierP))
            $totalCcpackMasterFileExpenseOfUSD = $totalCcpackMasterFileExpenseOfUSD->whereIn('expenses.disbursed_by', $cashierP);
        $totalCcpackMasterFileExpenseOfUSD = $totalCcpackMasterFileExpenseOfUSD->get()->first();
        $totalCcpackMasterExpenseOfUSDCount = $totalCcpackMasterFileExpenseOfUSD->total;



        $allExpenseDetails = array_merge($cargoExpancesDetail, $upsExpancesDetail, $houseFileExpancesDetail, $aeropostExpancesDetail, $ccpackExpancesDetail, $upsMasterExpancesDetail, $aeropostMasterExpancesDetail, $ccpackMasterExpancesDetail);
        $query1 = array();
        foreach ($allExpenseDetails as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->voucher_number;
        }
        array_multisort((array) $query1, SORT_ASC, $allExpenseDetails);

        $allTotalExpenseOfHtgCount = $totalCargoExpenseOfHtgCount + $totalHouseFileExpenseOfHtgCount + $totalUpsExpenseOfHtgCount + $totalAeropostExpenseOfHtgCount + $totalCCPackExpenseOfHtgCount + $totalUpsMasterExpenseOfHtgCount + $totalAeropostMasterExpenseOfHtgCount + $totalCcpackMasterExpenseOfHtgCount;
        $allTotalExpenseOfUSDCount = $totalCargoExpenseOfUSDCount + $totalHouseFileExpenseOfUSDCount + $totalUpsExpenseOfUSDCount + $totalAeropostExpenseOfUSDCount + $totalCCPackExpenseOfUSDCount + $totalUpsMasterExpenseOfUSDCount + $totalAeropostMasterExpenseOfUSDCount + $totalCcpackMasterExpenseOfUSDCount;

        //pre($_POST);
        if (!empty($flag)) {
            $pdf = PDF::loadView('reports.printgenericdisbursementreport', compact('allExpenseDetails', 'allTotalExpenseOfHtgCount', 'allTotalExpenseOfUSDCount', 'fromDate', 'toDate', 'cashBankSingle'));
            $pdf_file = 'printgenericdisbursementreport.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;
            $pdf->save($pdf_path);
            return response()->file($pdf_path);
        } else if (!empty($_POST['submit'])) {
            return view("reports.genericdisbursementreportfilter", ['allExpenseDetails' => $allExpenseDetails, 'allTotalExpenseOfHtgCount' => $allTotalExpenseOfHtgCount, 'allTotalExpenseOfUSDCount' => $allTotalExpenseOfUSDCount, 'cashBankSingle' => $cashBankSingle]);
        } else {
            return view("reports.genericdisbursementreport", ['cashBank' => $cashBank, 'cashier' => $cashier, 'allExpenseDetails' => $allExpenseDetails, 'allTotalExpenseOfHtgCount' => $allTotalExpenseOfHtgCount, 'allTotalExpenseOfUSDCount' => $allTotalExpenseOfUSDCount, 'cashBankSingle' => $cashBankSingle]);
        }
    }

    public function genericcollectionreport($flag = null, $fromDate = null, $toDate = null, $currency = null)
    {
        if (!empty($flag)) {
            $fromDate = !empty($fromDate) ? date('Y-m-d', strtotime($fromDate)) : '';
            $toDate = !empty($toDate) ? date('Y-m-d', strtotime($toDate)) : '';
            $currencyP = !empty($currency) ? $currency : '';
            $currencySingle = DB::table('currency')->where('id', $currencyP)->first();
        } else {
            if (!empty($_POST['submit'])) {
                $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
                $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
                $currencyP = !empty($_POST['currency']) ? $_POST['currency'] : '';
                $currencySingle = DB::table('currency')->where('id', $currencyP)->first();
            } else {
                /* $fromDate = date('Y-m-d');
                    $toDate = date('Y-m-d'); */
                $fromDate = '';
                $toDate = '';

                $currencyP = '0';
            }
        }

        $currency = DB::table('currency')->where('deleted', '0')->where('status', '1')->pluck('code', 'id')->toArray();

        $paymentReceivedByCashier = DB::table('invoice_payments')
            ->select(['invoice_payments.*', 'invoices.currency as invoiceCurrency', 'invoices.total as originalAmount', 'users.name as paymentReceivedBy', 'invoices.consignee_address', 'invoice_payments.id as paymentId'])
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->join('users', 'users.id', '=', 'invoice_payments.payment_accepted_by')
            //->where('payment_accepted_by', $id)
            ->where('invoice_payments.deleted', 0)
            ->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
        if (checkloggedinuserdata() == 'Cashier') {
            $cashierId = auth()->user()->id;
            $paymentReceivedByCashier = $paymentReceivedByCashier->where('payment_accepted_by', $cashierId);
        }
        /* if(!empty($currencyP))   
                $paymentReceivedByCashier = $paymentReceivedByCashier->where('exchange_currency',$currencyP); */
        $paymentReceivedByCashier = $paymentReceivedByCashier->get()->toArray();

        foreach ($paymentReceivedByCashier as $k => $v) {
            $paymentReceivedByCashier[$k]->paymentId = '10' . $v->paymentId;
        }


        $query1 = array();
        foreach ($paymentReceivedByCashier as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->paymentId;
        }
        array_multisort((array) $query1, SORT_ASC, $paymentReceivedByCashier);

        $i = 0;
        $count = count($paymentReceivedByCashier);
        $modelInvoices = new Invoices;
        $totalOfHTG = 0;
        $totalOfUSD = 0;
        $totalOfCurrency[1] = '0.00';
        $totalOfCurrency[3] = '0.00';
        for ($i = 0; $i < $count; $i++) {
            if (!empty($paymentReceivedByCashier[$i]->exchange_currency)) {
                if ($paymentReceivedByCashier[$i]->exchange_currency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->exchange_currency] = $totalOfHTG;
                }
            } else {
                $paymentReceivedByCashier[$i]->exchange_currency = $paymentReceivedByCashier[$i]->invoiceCurrency;
                if ($paymentReceivedByCashier[$i]->invoiceCurrency == 1) {
                    $totalOfUSD += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfUSD;
                } else {
                    $totalOfHTG += $modelInvoices->totalInCurrency($paymentReceivedByCashier[$i]->id);
                    $totalOfCurrency[$paymentReceivedByCashier[$i]->invoiceCurrency] = $totalOfHTG;
                }
            }
        }

        if (!empty($currencyP)) {
            $paymentReceivedByCashierNew = array();
            foreach ($paymentReceivedByCashier as $k => $v) {
                if ($v->exchange_currency == $currencyP)
                    $paymentReceivedByCashierNew[$k] = $v;
            }
            if (count($paymentReceivedByCashierNew) <= 0) {
                $paymentReceivedByCashierNew = array();
            }
        } else {
            $paymentReceivedByCashierNew = $paymentReceivedByCashier;
        }

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        $htgTousd = $totalOfCurrency[1] * $exchangeRateOfUsdToHTH->exchangeRate;
        $totalOfCurrency['totalInHtg'] = $totalOfCurrency[3] + $htgTousd;

        if (!empty($flag)) {
            $pdf = PDF::loadView('reports.printgenericcollectionreport', compact('paymentReceivedByCashierNew', 'count', 'totalOfCurrency', 'fromDate', 'toDate', 'currencySingle'));
            $pdf_file = 'printgenericcollectionreport.pdf';
            $pdf_path = 'public/reports_pdf/' . $pdf_file;
            $pdf->save($pdf_path);
            return response()->file($pdf_path);
        } else if (!empty($_POST['submit'])) {
            return view("reports.genericcollectionreportfilter", compact('paymentReceivedByCashierNew', 'count', 'totalOfCurrency', 'currencySingle'));
        } else {
            return view("reports.genericcollectionreport", compact('paymentReceivedByCashierNew', 'count', 'totalOfCurrency', 'currency'));
        }
    }

    public function makedefaultcashbankofcashierinreport($id = null)
    {
        $cashierId = auth()->user()->id;
        User::where('id', $cashierId)->update(['default_cashbank_account_for_report' => $id]);
        return redirect('reports/genericdisbursementreport');
    }

    public function billingitemsdetailreport()
    {
        return view("reports.billing-items-detail-report.index");
    }

    public function listbillingitemsdetailreport(Request $request)
    {
        $req = $request->all();
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['billing_items.id', 'billing_name', 'item_code', 'costs.code', 'flag_prod_tax_type', 'billing_items.status'];

        $total = BillingItems::selectRaw('count(*) as total')
            ->where('billing_items.deleted', '0');
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('billing_items')
            ->selectRaw('billing_items.id,billing_items.billing_name,billing_items.item_code,costs.code,billing_items.flag_prod_tax_type,billing_items.status')
            ->leftJoin('costs', 'costs.id', '=', 'billing_items.code')
            ->where('billing_items.deleted', '0');

        $filteredq = DB::table('billing_items')
            ->leftJoin('costs', 'costs.id', '=', 'billing_items.code')
            ->where('billing_items.deleted', '0');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('billing_name', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%')
                    ->orWhere('flag_prod_tax_type', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('billing_name', 'like', '%' . $search . '%')
                    ->orWhere('item_code', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%')
                    ->orWhere('flag_prod_tax_type', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $data[] = [$items->id, $items->billing_name, $items->item_code, !empty($items->code) ? $items->code : '-', $items->flag_prod_tax_type == 1 ? 'Yes' : 'No', ($items->status == 1) ? 'Active' : 'Inactive'];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function fetchbillingitemsdetailreport($id = null)
    {
        /* $items = DB::table('invoice_item_details')
            ->select(['invoices.bill_no', 'invoices.date', 'clients.company_name', 'invoice_item_details.*', 'invoice_item_details.fees_name_desc as Description', 'currency.code as currencyCode'])
            ->join('invoices', 'invoices.id', '=', 'invoice_item_details.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoice_item_details.fees_name', $id)
            ->orderBy('invoices.id', 'desc')->get(); */
        $dataBillingItem = DB::table('billing_items')->where('id', $id)->first();
        return view("reports.billing-items-detail-report.fetchbillingitemsdetailreport", ['id' => $id, 'dataBillingItem' => $dataBillingItem]);
    }

    public function listfetchbillingitemsdetailreport(Request $request)
    {
        $req = $request->all();

        $billingItemId = $req['billingItemId'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : date('Y-m-01');
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : date('Y-m-d');
        //$fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        //$toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['invoices.id', 'invoices.date', 'invoices.bill_no', 'currency.code', 'clients.company_name', 'invoice_item_details.fees_name_desc', 'invoice_item_details.quantity', 'invoice_item_details.unit_price', 'invoice_item_details.total_of_items'];

        $total = InvoiceItemDetails::selectRaw('count(*) as total')->join('invoices', 'invoices.id', '=', 'invoice_item_details.invoice_id')->where('invoice_item_details.fees_name', $billingItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('invoices.date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('invoice_item_details')
            ->selectRaw('invoices.id as invoiceId,invoices.bill_no, invoices.date, clients.company_name, invoice_item_details.*, invoice_item_details.fees_name_desc as Description, currency.code as currencyCode')
            ->join('invoices', 'invoices.id', '=', 'invoice_item_details.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoice_item_details.fees_name', $billingItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('invoice_item_details')
            ->join('invoices', 'invoices.id', '=', 'invoice_item_details.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoice_item_details.fees_name', $billingItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('invoices.date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->Where(DB::raw("date_format(invoices.date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('invoices.bill_no', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.fees_name_desc', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.quantity', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.unit_price', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.total_of_items', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('clients.company_name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->Where(DB::raw("date_format(invoices.date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('invoices.bill_no', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.fees_name_desc', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.quantity', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.unit_price', 'like', '%' . $search . '%')
                    ->orWhere('invoice_item_details.total_of_items', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('clients.company_name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $queryForTotal = $query->get()->toArray();
        $query1 = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get()->toArray();

        $totalInUsd = '0.00';
        $totalInHtg = '0.00';
        foreach ($queryForTotal as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInUsd += str_replace(',', '', $v->total_of_items);
            if ($v->currencyCode == 'HTG')
                $totalInHtg += str_replace(',', '', $v->total_of_items);
        }

        $data1 = [];
        foreach ($query1 as $key => $items) {
            $data1[] = [$items->invoiceId, date('d-m-Y', strtotime($items->date)), $items->bill_no, !empty($items->currencyCode) ? $items->currencyCode : '-', !empty($items->company_name) ? $items->company_name : '-', $items->fees_name_desc, $items->quantity, $items->unit_price, $items->total_of_items];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "totalUsd" => number_format($totalInUsd, 2),
            "totalHtg" => number_format($totalInHtg, 2),
            "data" => $data1,
        );
        return Response::json($json_data);
        //return view("reports.billing-items-detail-report.fetchbillingitemsdetailreport", ['items' => $items]);
    }

    public function printfetchbillingitemsdetailreport()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $billingItemId = $_POST['billingItemId'];
        $dataBillingItem = DB::table('billing_items')->where('id', $billingItemId)->first();
        $query = DB::table('invoice_item_details')
            ->selectRaw('invoices.id as invoiceId,invoices.bill_no, invoices.date, clients.company_name, invoice_item_details.*, invoice_item_details.fees_name_desc as Description, currency.code as currencyCode')
            ->join('invoices', 'invoices.id', '=', 'invoice_item_details.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.bill_to')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoice_item_details.fees_name', $billingItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
        }

        $query = $query->orderBy('invoices.date', 'desc')->groupBy('invoices.id')->get()->toArray();
        $totalInUsd = '0.00';
        $totalInHtg = '0.00';
        foreach ($query as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInUsd += str_replace(',', '', $v->total_of_items);
            if ($v->currencyCode == 'HTG')
                $totalInHtg += str_replace(',', '', $v->total_of_items);
        }
        $pdf = PDF::loadView('reports.billing-items-detail-report.print-billing-items-details', ['data' => $query, 'totalInUsd' => $totalInUsd, 'totalInHtg' => $totalInHtg, 'fromDate' => $fromDate, 'toDate' => $toDate, 'dataBillingItem' => $dataBillingItem]);
        $pdf_file = 'billingItemsDetails.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function costitemsdetailreport()
    {
        return view("reports.cost-items-detail-report.index");
    }

    public function listcostitemsdetailreport(Request $request)
    {
        $req = $request->all();
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['costs.id', 'costs.cost_name', 'costs.code', 'billing_items.billing_name', 'costs.status'];

        $total = Costs::selectRaw('count(*) as total')
            ->where('costs.deleted', '0');
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('costs')
            ->selectRaw('costs.id,costs.cost_name,billing_items.billing_name,costs.code,costs.status')
            ->leftJoin('billing_items', 'billing_items.id', '=', 'costs.cost_billing_code')
            ->where('costs.deleted', '0');

        $filteredq = DB::table('costs')
            ->leftJoin('billing_items', 'billing_items.id', '=', 'costs.cost_billing_code')
            ->where('costs.deleted', '0');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('costs.cost_name', 'like', '%' . $search . '%')
                    ->orWhere('billing_items.billing_name', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('costs.cost_name', 'like', '%' . $search . '%')
                    ->orWhere('billing_items.billing_name', 'like', '%' . $search . '%')
                    ->orWhere('costs.code', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $data[] = [$items->id, $items->cost_name, $items->code, !empty($items->billing_name) ? $items->billing_name : '-', ($items->status == 1) ? 'Active' : 'Inactive'];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function fetchcostitemsdetailreport($id = null)
    {
        $dataCostItem = DB::table('costs')->where('id', $id)->first();
        /* $items = DB::table('expense_details')
            ->select(['expenses.voucher_number', 'expenses.exp_date', 'vendors.company_name', 'expense_details.*', 'expense_details.description as Description'])
            ->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->where('expenses.deleted', '0')
            ->where('expense_details.expense_type', $id)
            ->orderBy('expenses.expense_id', 'desc')->get(); */
        return view("reports.cost-items-detail-report.fetchcostitemsdetailreport", ['id' => $id, 'dataCostItem' => $dataCostItem]);
    }

    public function listfetchcostitemsdetailreport(Request $request)
    {
        $req = $request->all();

        $costItemId = $req['costItemId'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : date('Y-m-01');
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : date('Y-m-d');
        //$fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        //$toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['expenses.voucher_number', 'expenses.exp_date', 'expenses.voucher_number', 'currency.code', 'vendors.company_name', 'expense_details.description', 'expense_details.amount'];

        $total = ExpenseDetails::selectRaw('count(*) as total')->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')->where('expense_details.expense_type', $costItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('expense_details')
            ->selectRaw('expenses.expense_id as expenseId,expenses.voucher_number, expenses.exp_date, vendors.company_name, expense_details.description, expense_details.amount, currency.code as currencyCode')
            ->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('expenses.deleted', '0')
            ->where('expense_details.expense_type', $costItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('expense_details')
            ->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('expenses.deleted', '0')
            ->where('expense_details.expense_type', $costItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->Where(DB::raw("date_format(expenses.exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('vendors.company_name', 'like', '%' . $search . '%')
                    ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                    ->orWhere('expense_details.amount', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->Where(DB::raw("date_format(expenses.exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('expenses.voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('vendors.company_name', 'like', '%' . $search . '%')
                    ->orWhere('expense_details.description', 'like', '%' . $search . '%')
                    ->orWhere('expense_details.amount', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $queryForTotal = $query->get()->toArray();
        $query1 = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get()->toArray();

        $totalInUsd = '0.00';
        $totalInHtg = '0.00';
        foreach ($queryForTotal as $k => $v) {
            $dataCurrency = Vendors::getDataFromPaidTo($v->expenseId);
            if ($dataCurrency->code == 'USD')
                $totalInUsd += str_replace(',', '', $v->amount);
            if ($dataCurrency->code == 'HTG')
                $totalInHtg += str_replace(',', '', $v->amount);
        }

        $data1 = [];
        foreach ($query1 as $key => $items) {
            $dataCurrency = Vendors::getDataFromPaidTo($items->expenseId);
            $data1[] = [$items->voucher_number, date('d-m-Y', strtotime($items->exp_date)), $items->voucher_number, !empty($dataCurrency->code) ? $dataCurrency->code : "-", !empty($items->company_name) ? $items->company_name : '-', $items->description, $items->amount];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "totalUsd" => number_format($totalInUsd, 2),
            "totalHtg" => number_format($totalInHtg, 2),
            "data" => $data1,
        );
        return Response::json($json_data);
    }

    public function printfetchcostitemsdetailreport()
    {
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $costItemId = $_POST['costItemId'];
        $dataCostItem = DB::table('costs')->where('id', $costItemId)->first();
        $query = DB::table('expense_details')
            ->selectRaw('expenses.expense_id as expenseId,expenses.voucher_number, expenses.exp_date, vendors.company_name, expense_details.description, expense_details.amount, currency.code as currencyCode')
            ->join('expenses', 'expenses.expense_id', '=', 'expense_details.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('expenses.deleted', '0')
            ->where('expense_details.expense_type', $costItemId);
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        $query = $query->orderBy('expenses.voucher_number', 'desc')->groupBy('expenses.expense_id')->get()->toArray();
        $totalInUsd = '0.00';
        $totalInHtg = '0.00';
        foreach ($query as $k => $v) {
            $dataCurrency = Vendors::getDataFromPaidTo($v->expenseId);
            if ($dataCurrency->code == 'USD')
                $totalInUsd += str_replace(',', '', $v->amount);
            if ($dataCurrency->code == 'HTG')
                $totalInHtg += str_replace(',', '', $v->amount);
        }
        $pdf = PDF::loadView('reports.cost-items-detail-report.print-cost-items-details', ['data' => $query, 'totalInUsd' => $totalInUsd, 'totalInHtg' => $totalInHtg, 'fromDate' => $fromDate, 'toDate' => $toDate, 'dataCostItem' => $dataCostItem]);
        $pdf_file = 'costItemsDetails.pdf';
        $pdf_path = 'public/reports_pdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return url('/') . '/' . $pdf_path;
    }

    public function checkguaranteetopayreport()
    {
        return view("reports.checkguaranteetopayreport");
    }

    public function checkguaranteetopayreportlist(Request $request)
    {
        $req = $request->all();
        $moduleType = $req['moduleType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($moduleType == 'masterCargo')
            $orderby = ['cargo.id', '', 'cargo.file_number', 'check_guarantee_to_pay.check_number', 'check_guarantee_to_pay.date', 'check_guarantee_to_pay.invoice_number', 'check_guarantee_to_pay.amount', 'check_guarantee_to_pay.amount_deducted', 'check_guarantee_to_pay.amount_balance'];

        if ($moduleType == 'masterCargo') {
            $total = Cargo::selectRaw('count(*) as total')
                ->join('check_guarantee_to_pay', 'check_guarantee_to_pay.master_cargo_id', '=', 'cargo.id')
                ->where('cargo.deleted', '0');
            if (!empty($fromDate) && !empty($toDate)) {
                $total = $total->whereBetween('check_guarantee_to_pay.date', array($fromDate, $toDate));
            }

            $query = DB::table('cargo')
                ->selectRaw(DB::raw('cargo.id as moduleId,cargo.file_number,check_guarantee_to_pay.check_number, check_guarantee_to_pay.date, check_guarantee_to_pay.invoice_number, check_guarantee_to_pay.amount, check_guarantee_to_pay.amount_deducted, check_guarantee_to_pay.amount_balance'))
                ->join('check_guarantee_to_pay', 'check_guarantee_to_pay.master_cargo_id', '=', 'cargo.id')
                ->where('cargo.deleted', '0');
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('check_guarantee_to_pay.date', array($fromDate, $toDate));
            }

            $filteredq = DB::table('cargo')
                ->join('check_guarantee_to_pay', 'check_guarantee_to_pay.master_cargo_id', '=', 'cargo.id')
                ->where('cargo.deleted', '0');
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('check_guarantee_to_pay.date', array($fromDate, $toDate));
            }
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search, $moduleType) {
                if ($moduleType == 'masterCargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.check_number', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.invoice_number', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.amount', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.amount_deducted', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.amount_balance', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $moduleType) {
                if ($moduleType == 'masterCargo') {
                    $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.check_number', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.invoice_number', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.amount', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.amount_deducted', 'like', '%' . $search . '%')
                        ->orWhere('check_guarantee_to_pay.amount_balance', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        $data = [];
        foreach ($query as $key => $value) {
            $otherOption1 = '';
            if ($moduleType == 'masterCargo') {
                $moduleId = $value->moduleId;
                $dataV = DB::table('cargo')->where('id', $value->moduleId)->first();
                $otherOption1 = $dataV->cargo_operation_type;
            }

            $data[] = [$moduleId, $otherOption1, $value->file_number, $value->check_number, !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '-', $value->invoice_number,  $value->amount, $value->amount_deducted, $value->amount_balance];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function showOpenInvoices()
    {
        return view('reports/open-invoices');
    }

    public function exportOpenInvoices($fromDate = null, $toDate = null)
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
                ->select(DB::raw('SUM(invoice_payments.exchange_amount) as totalAmountReceived'))
                ->where('client', $explodeBillingPartyKey[0]);
            if (!empty($fromDate) && !empty($toDate)) {
                $queryForInvoicePaymentOfBillingParty = $queryForInvoicePaymentOfBillingParty->whereBetween(DB::raw("DATE(invoice_payments.created_at)"), array($fromDate, $toDate));
            }
            $queryForInvoicePaymentOfBillingParty = $queryForInvoicePaymentOfBillingParty->first();



            $data[] = ['', '', 'Payment', '', '', !empty($queryForInvoicePaymentOfBillingParty->totalAmountReceived) ? $queryForInvoicePaymentOfBillingParty->totalAmountReceived : '0.00', ''];

            $totalForOneBillingParty = 0.00;
            foreach ($v1 as $k11 => $v11) {
                $openBalance = number_format($v11->openBalance, 2);
                if ($v11->currency == 3)
                    $openBalance = number_format($v11->openBalance * $exchangeRateOfUsdToHTH->exchangeRate, 2);

                $data[] = ['', $v11->date, $v11->transactionType, $v11->num, $v11->terms, $openBalance, number_format($v11->openBalance, 2)];
                $totalForOneBillingParty +=  $openBalance;
            }
            $finalTotalAmount += $totalForOneBillingParty;
            $data[] = ['Total for ' . $explodeBillingPartyKey[1], '', '', '', '', number_format($totalForOneBillingParty, 2), ''];
        }
        $data[] = ['TOTAL', '', '', '', '', $finalTotalAmount, ''];

        $export = new ExportFromArray([
            $data
        ]);

        return Excel::download($export, 'open-invoices.xlsx');
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
        $data[] = [$fromDate . ' -- ' . $toDate];
        $data[] = [''];
        $data[] = ['', 'Current', '1 - 30', '31 - 60', '61 - 90', '91 and over', 'Total'];

        // GET PENDING INVOICES
        $billingParties = DB::table('invoices')
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
            })->orderBy('invoices.id', 'desc')->groupBy('invoices.bill_to')->pluck('clients.company_name as billingParty', 'bill_to')->toArray();

            
        foreach ($billingParties as $k => $v) {

            
            // Get all Pending amount of billing parties for today
            $todaysPendingInvoices = DB::table('invoices')
                ->select(DB::raw('SUM(invoices.balance_of) as totalAmountPending'))
                //->where('invoices.date', '>', Carbon::now()->subDays(30)->endOfDay())
                ->where('invoices.date', '=', Carbon::now()->today())
                ->where('bill_to',$k)
                ->first();

                $data[] = [$v, $todaysPendingInvoices->totalAmountPending];
                
        }

        pre($data);











        $export = new ArAging([
            $data
        ]);

        return Excel::download($export, 'ar-aging.xlsx');
    }
}
