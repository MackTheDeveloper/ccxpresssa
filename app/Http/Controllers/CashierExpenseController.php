<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Clients;
use App\ExpenseDetails;
use App\Cargo;
use App\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Session;
use PDF;
use App\User;
use App\CashCredit;
use App\Currency;
use App\Vendors;
use App\Admin;
use App\CheckGuaranteeToPay;
use App\InvoiceItemDetails;
use App\Invoices;
use Illuminate\Support\Facades\Storage;

class CashierExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_expenses'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cargoExpenseDataByVoucher = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('cargo_id')
            ->where('deleted', '0')
            //->where('cashier_id',Auth::user()->id)
            /* ->where(function ($query) {
                                $query->where('expense_request','Approved')
                                      ->orWhere('expense_request','Disbursement done');
                            }) */
            ->orderBy('expense_id', 'desc')
            ->get();
        return view("cashier-role.expenses.index", ['cargoExpenseDataByVoucher' => $cargoExpenseDataByVoucher]);
    }

    public function expandexpensescashier()
    {
        $expenseId = $_POST['expenseId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('expense_details')->where('expense_id', $expenseId)->where('deleted', 0)->get();
        $data = DB::table('expenses')->where('expense_id', $expenseId)->first();
        if (isset($_POST['flagW']) && !empty($_POST['flagW']) && $_POST['flagW'] == 'Ups')
            $dataCargo = DB::table('ups_details')->where('id', $data->ups_details_id)->first();
        else
            $dataCargo = DB::table('cargo')->where('id', $data->cargo_id)->first();
        return view('cashier-role.expenses.renderexpenses', ['packageData' => $packageData, 'rowId' => $rowId, 'dataCargo' => $dataCargo]);
    }

    public function  getprintviewsingleexpensecashier($expenseId = null, $cargoId =  null, $flag = null)
    {
        $checkPermission = User::checkPermission(['change_file_expense_status_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        if ($flag == 'fromNotification')
            Expense::where('expense_id', $expenseId)->update(['display_notification_cashier' => 0]);

        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $expenseStatus = array();
        $expenseStatus['Disbursement done'] = 'Disbursement done';
        //$expenseStatus['Approved'] = 'Approved';
        $expenseStatus['on Hold'] = 'on Hold';
        $expenseStatus['Cancelled'] = 'Cancelled';
        //$expenseStatus['Requested'] = 'Requested';


        $cashCredit = DB::table('cashcredit')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $cashCredit = json_decode($cashCredit, 1);
        ksort($cashCredit);

        return view('cashier-role.expenses.getprintviewsingleexpensecashier', ['cargoExpenseData' => $cargoExpenseData, 'expenseId' => $expenseId, 'cargoId' => $cargoId, 'expenseStatus' => $expenseStatus, 'cashCredit' => $cashCredit]);
    }

    public function getprintsingleexpensecashier($expenseId = null, $cargoId =  null)
    {
        //$myfile = fopen("testwrite.txt", "a"); 
        //fwrite($myfile, '--ooo--'); 
        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();


        $cargoExpenseDetailsData = DB::table('expense_details')->where('voucher_number', $cargoExpenseData[0]->voucher_number)->where('expense_id', $cargoExpenseData[0]->expense_id)->count();

        $scale = [290, 300];

        if ($cargoExpenseDetailsData == 1)
            $scale = [210, 180];

        if ($cargoExpenseDetailsData == 2)
            $scale = [210, 185];

        if ($cargoExpenseDetailsData == 3)
            $scale = [210, 200];

        if ($cargoExpenseDetailsData == 4)
            $scale = [210, 225];

        $dataCargo = DB::table('cargo')->where('id', $cargoId)->first();
        //$pdf = PDF::loadView('cashier-role.expenses.printcargoexpensecashier',['cargoExpenseData'=>$cargoExpenseData,'dataCargo'=>$dataCargo],[],['format' => $scale]);
        $pdf = PDF::loadView('cashier-role.expenses.printcargoexpensecashier', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $expenseId . '_expense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function changestatusbycashier(Request $request)
    {
        session_start();
        $input = $request->all();
        $model = Expense::find($input['id']);
        $input['updated_by'] = Auth::user()->id;
        $input['disbursed_by'] = null;
        if ($input['expense_request'] == 'Disbursement done') {
            $input['disbursed_by'] = Auth::user()->id;
            $input['disbursed_datetime'] = date('Y-m-d H:i:s');
            $totalExpenses = str_replace(',', '', Expense::getExpenseTotal($input['id']));

            $getCashCreditData = DB::table('cashcredit')->where('id', $input['cash_credit_account'])->first();
            $finalAmt = $getCashCreditData->available_balance - $totalExpenses;
            DB::table('cashcredit')->where('id', $input['cash_credit_account'])->update(['available_balance' => $finalAmt, 'qb_sync' => 0]);

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCredit';
            $modelActivities->related_id = $input['cash_credit_account'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = $totalExpenses . '-' . $input['expense_request_status_note'];
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            // Generate invoice for the DECSA
            if ($model->identification_flag == 1) {
                $inserted = CheckGuaranteeToPay::find($model->decsa_id);
                $modelCargo = Cargo::find($inserted->master_cargo_id);
                if ($inserted->check_type == 1)
                    $dataBillingParty = DB::table('clients')->where('company_name', 'DECSA/ Chèque de garantie USD')->first();
                else
                    $dataBillingParty = DB::table('clients')->where('company_name', 'Veconinter USD')->first();

                $dataConsignee = DB::table('clients')->where('id', $modelCargo->consignee_name)->first();
                $dataShipper = DB::table('clients')->where('id', $modelCargo->shipper_name)->first();
                if (!empty($dataBillingParty)) {
                    $invoiceInput['type_flag'] = $modelCargo->cargo_operation_type == '1' ? 'IMPORT' : 'EXPORT';;
                    $invoiceInput['cargo_id'] = $modelCargo->id;
                    $invoiceInput['bill_to'] = $dataBillingParty->id;
                    $invoiceInput['currency'] = $dataBillingParty->currency;
                    $invoiceInput['date'] = $inserted->date;
                    $invoiceInput['email'] = $dataBillingParty->email;
                    $invoiceInput['telephone'] = $dataBillingParty->phone_number;
                    $invoiceInput['consignee_address'] = $dataConsignee->company_name;
                    $invoiceInput['shipper'] = $dataShipper->company_name;
                    $invoiceInput['file_no'] = $modelCargo->file_number;
                    $invoiceInput['awb_no'] = $modelCargo->awb_bl_no;
                    //$invoiceInput['total'] = $input['rental_cost'] * $input['contract_months'];
                    $invoiceInput['total'] = $inserted->amount;
                    $invoiceInput['sub_total'] = $invoiceInput['total'];
                    $invoiceInput['balance_of'] = $invoiceInput['total'];
                    $invoiceInput['payment_status'] = 'Pending';

                    $modelInvoice = new Invoices();
                    $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                    if (empty($getLastInvoice)) {
                        $modelInvoice->bill_no = 'CA-5001';
                    } else {
                        $ab = 'CA-';
                        $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                        $modelInvoice->bill_no = $ab;
                    }

                    $invoiceInput['bill_no'] = $modelInvoice->bill_no;
                    $invoiceInput['created_by'] = auth()->user()->id;
                    $invoiceInput['created_at'] = date('Y-m-d h:i:s');
                    $dataInvoices = Invoices::create($invoiceInput);
                    Activities::log('create', 'cargoinvoice', $dataInvoices);

                    $dataBillingItems = DB::table('billing_items')->select('id', 'item_code', 'description')->where('item_code', '1037/ Garantie (DECSA)')->first();

                    $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                    $dataInvoiceItems['fees_name'] = !empty($dataBillingItems) ? $dataBillingItems->id : '';
                    $dataInvoiceItems['item_code'] = !empty($dataBillingItems) ? $dataBillingItems->item_code : '';
                    $dataInvoiceItems['fees_name_desc'] = !empty($dataBillingItems) ? $dataBillingItems->description : '';
                    $dataInvoiceItems['quantity'] = 1.00;
                    $dataInvoiceItems['unit_price'] = $inserted->amount;
                    $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                    InvoiceItemDetails::create($dataInvoiceItems);

                    // Store deposite activities
                    $modelActivities = new Activities;
                    $modelActivities->type = 'client';
                    $modelActivities->related_id = $dataInvoices->bill_to;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Invoice #' . $dataInvoices->bill_no . ' has been generated';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();

                    // Store invoice activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cargo';
                    $modelActivities->related_id = $dataInvoices->cargo_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Invoice #' . $dataInvoices->bill_no . ' has been generated';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }

            // Store to QB
            $fData['flagModule'] = 'expenses';
            if (isset($_SESSION['sessionAccessToken'])) {
                //pre('test');
                $fData['id'] = $model->expense_id;
                $fData['module'] = '5';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
        /* $input['display_notification_admin'] = '1';
        $input['display_notification_agent'] = '1';
        $input['notification_date_time'] = date('Y-m-d H:i:s'); */
        $input['display_notification_agent'] = '1';
        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $model->update($input);

        $fileData = DB::table('cargo')->where('id', $model->cargo_id)->where('deleted', 0)->first();
        $cargoExpenseData = DB::table('expenses')->where('expense_id', $model->expense_id)->get();
        $dataCargo = DB::table('cargo')->where('id', $model->cargo_id)->first();
        $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $model->expense_id . '_expense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        $s3path = 'Files/Cargo/';
        if ($fileData->cargo_operation_type == 1) {
            $s3path .= 'Import/' . $fileData->file_number . '/Expenses/';
        } else if ($fileData->cargo_operation_type == 2) {
            $s3path .= 'Export/' . $fileData->file_number . '/Expenses/';
        } else {
            $s3path .= 'Local/' . $fileData->file_number . '/Expenses/';
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . $fileData->file_number . '_' . $model->voucher_number . '_expense.pdf', $filecontent, 'public');

        return redirect()->route('getprintviewsingleexpensecashier', [$model->expense_id, $model->cargo_id]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $req = $request->all();

        $status = $req['status'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['expenses.expense_id', 'expenses.expense_id', 'exp_date', 'voucher_number', 'cargo.file_number', 'bl_awb', '', 'note', 'consignee', 'shipper', 'currency.code', '', 'expense_request'];


        $total = Expense::selectRaw('count(*) as total')->where('expenses.deleted', '0')
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->whereNotNull('expenses.cargo_id');
        if (!empty($status)) {
            $total = $total->where('expense_request', $status);
        }
        $total = $total->first();
        $totalfiltered = $total->total;




        $query = DB::table('expenses')
            ->selectRaw('expenses.*')
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.cargo_id')
            ->where('expenses.deleted', '0');
        if (!empty($status)) {
            $query = $query->where('expense_request', $status);
        }
        $filteredq = DB::table('expenses')
            ->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->whereNotNull('expenses.cargo_id')
            ->where('expenses.deleted', '0');
        if (!empty($status)) {
            $query = $query->where('expense_request', $status);
        }



        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('cargo.file_number', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(exp_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('voucher_number', 'like', '%' . $search . '%')
                    ->orWhere('bl_awb', 'like', '%' . $search . '%')
                    ->orWhere('consignee', 'like', '%' . $search . '%')
                    ->orWhere('shipper', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('expense_request', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get()->toArray();


        /* $query1 = array();
        foreach ($query as $key => $row) {
            // replace 0 with the field's index/key
            $query1[$key] = $row->cargo_id;
        }
        
        array_multisort((array)$query1, SORT_DESC,$query); */

        $data1 = [];
        foreach ($query as $key => $items) {

            $dataCargo = Cargo::getCargoData($items->cargo_id);
            if (!empty($dataCargo)) {

                $invoiceOfFile = Expense::getInvoicesOfFile($items->cargo_id);
                $currencyData = CashCredit::getCashCreditData($items->cash_credit_account);
                $totlaExpense = Expense::getExpenseTotal($items->expense_id);
                //$dataCurrency = Currency::getData($items->currency); 
                $dataCurrency = Vendors::getDataFromPaidTo($items->expense_id);
                $dataClientUsingModuleId = Common::getClientDataUsingModuleId('cargo', $items->cargo_id);

                $action = '<div class="dropdown">';

                $delete =  route('deleteexpensevoucher', $items->expense_id);
                if ($items->request_by_role == 12 || $items->request_by_role == 10)
                    $edit =  route('editagentexpensesbyadmin', [$items->expense_id, 'flagFromExpenseListing']);
                else
                    $edit =  route('editexpensevoucher', [$items->expense_id, 'flagFromExpenseListing']);

                $action .= '<a title="Click here to print"  target="_blank" href="' . route('getprintsingleexpense', [$items->expense_id, $items->cargo_id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

                $action .= '</div>';

                $data1[] = [$items->expense_id, '', date('d-m-Y', strtotime($items->exp_date)), $items->voucher_number, $dataCargo->file_number, $items->bl_awb, !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-', $items->note != '' ? $items->note : '-', $dataClientUsingModuleId['consigneeName'], $dataClientUsingModuleId['shipperName'], !empty($dataCurrency->code) ? $dataCurrency->code : "-", $totlaExpense, $items->expense_request, $action];
            }
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data1
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserverside()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkExpense') {
            $expenseId = $_POST['expenseId'];
            return ExpenseDetails::checkExpense($expenseId);
        }
        if ($flag == 'getExpenseData') {
            $expenseId = $_POST['expenseId'];
            return json_encode(Expense::getExpenseData($expenseId));
        }
    }
}
