<?php

namespace App\Http\Controllers;

use App\CcpackMaster;
use App\ccpack;
use App\Clients;
use App\User;
use App\Activities;
use App\Expense;
use App\VerificationInspectionNote;
use Config;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;
use Session;
use Response;

class CcpackMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("ccpack-master.index");
    }

    public function listingmasterccpack(Request $request)
    {
        $permissionCcpackMasterEdit = User::checkPermission(['update_ccpack_master'], '', auth()->user()->id);
        $permissionCcpackMasterDelete = User::checkPermission(['delete_ccpack_master'], '', auth()->user()->id);
        $permissionCcpackMasterAddExpense = User::checkPermission(['add_ccpack_master_expenses'], '', auth()->user()->id);
        $permissionCcpackMasterAddInvoice = User::checkPermission(['add_ccpack_master_invoices'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);
        $req = $request->all();

        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        /* Session::put('ccpackMasterListingFromDate', $req['fromDate']);
        Session::put('ccpackMasterListingToDate', $req['toDate']); */
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ccpack_master.id', 'ccpack_master.id', 'arrival_date', 'file_number', 'tracking_number', 'c1.company_name', 'c2.company_name', ''];
        $total = CcpackMaster::selectRaw('count(*) as total');
        //->where('deleted', '0');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('ccpack_master')
            ->selectRaw('ccpack_master.*,c1.company_name as consigneeName,c2.company_name as shipperName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name');
        //->where('ccpack_master.deleted', '0');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('ccpack_master')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name');
        //->where('ccpack_master.deleted', '0');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('tracking_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(arrival_date,'%d-%m-%Y')"), 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('tracking_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(arrival_date,'%d-%m-%Y')"), 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $invoiceNumbers = CcpackMaster::getCcpackMasterInvoicesOfFile($value->id);
            $action = '<div class="dropdown"><a title="Click here to print"  target="_blank" href="' . route("printccpackmasterfile", [$value->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete = route('deleteccpackmaster', [$value->id]);
            $edit = route('editccpackmaster', [$value->id]);
            if ($value->deleted == '0') {
                if ($permissionCcpackMasterEdit) {
                    $action .= '<a href="' . $edit . '" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }

                if ($permissionCcpackMasterDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['ccpack-master', $value->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCcpackMasterAddExpense) {
                    $action .= '<li><a href="' . route('createccpackmasterexpense', $value->id) . '">Add Expense</a></li>';
                }

                if ($permissionCcpackMasterAddInvoice) {
                    $action .= '<li><a href="' . route('createccpackmasterinvoice', $value->id) . '">Add Invoice</a></li>';
                }

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['CcpackMaster', $value->id]) . '">Close File</a></li>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $closedDetail = '';
            if ($value->file_close == 1) {
                $dataUserCloseFile = DB::table('users')->where('id', $value->close_unclose_by)->first();
                $closedDetail .= !empty($value->close_unclose_date) ? date('d-m-Y', strtotime($value->close_unclose_date)) : '-';
                $closedDetail .= ' | ';
                $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
            }

            $data[] = [$value->id, '', !empty($value->arrival_date) ? date('d-m-Y', strtotime($value->arrival_date)) : '-', $value->file_number, $value->tracking_number, $value->consigneeName, $value->shipperName, $invoiceNumbers, ($value->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function print($ccpackId)
    {
        $model = DB::table('ccpack_master')->where('id', $ccpackId)->first();
        $pdf = PDF::loadView('ccpack-master.printimport', ['model' => $model]);

        $pdf_file = $model->file_number . 'ccpackMaster.pdf';
        $pdf_path = 'public/ccpackMasterFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function checkoperations()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkFileAssgned') {
            $MasterCcpackId = $_POST['MasterCcpackId'];
            return CcpackMaster::checkFileAssgned($MasterCcpackId);
        }
        if ($flag == 'checkHawbFiles') {
            $MasterCcpackId = $_POST['MasterCcpackId'];
            return json_encode(CcpackMaster::checkHawbFiles($MasterCcpackId));
        }
        if ($flag == 'getMasterCcpackData') {
            $MasterCcpackId = $_POST['MasterCcpackId'];
            return json_encode(CcpackMaster::getMasterCcpackData($MasterCcpackId));
        }
    }

    public function expandhousefiles(Request $request)
    {
        $masterCcpackId = $_POST['masterCcpackId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('ccpack')->where('master_ccpack_id', $masterCcpackId)->orderBy('id', 'desc')->get();
        return view('ccpack-master.expandhousefiles', ['packageData' => $packageData, 'rowId' => $rowId]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new CcpackMaster();
        $model->arrival_date = date('d-m-Y');

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $dataCargoForImport = DB::table('ccpack_master')->where('deleted', 0)->whereNotNull('hawb_hbl_no')->get();
        $existingImportHawbFiles = '';
        $existingImportHawbFilesArray = array();
        if (!empty($dataCargoForImport)) {
            foreach ($dataCargoForImport as $key => $value) {
                $existingImportHawbFiles .= $value->hawb_hbl_no . ',';
                //array_push($existingHawbFiles,$dataExp);
            }
        }
        $nexistingImportHawbFiles = rtrim($existingImportHawbFiles, ',');
        $existingImportHawbFilesArray = explode(',', $nexistingImportHawbFiles);

        $dataImportHawb = DB::table('ccpack')->where('deleted', '0')->whereNotIn('id', $existingImportHawbFilesArray)->get();

        $dataImportHawbAll = array();
        foreach ($dataImportHawb as $k => $v) {
            $dataImportHawbAll[$k]['value'] = $v->id;
            $dataImportHawbAll[$k]['hawb_hbl_no'] = $v->awb_number;

            $dataClientConsignee = DB::table('clients')->where('id', $v->consignee)->first();
            $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

            $dataImportHawbAll[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
            $dataImportHawbAll[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
        }
        $dataImportHawbAll = json_encode($dataImportHawbAll, JSON_NUMERIC_CHECK);

        return view('ccpack-master._form', ['model' => $model, 'agents' => $agents, 'dataImportHawbAll' => $dataImportHawbAll]);
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
        $dataLast = DB::table('ccpack_master')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
        if (empty($dataLast)) {
            $input['file_number'] = 'MCCI 1110';
        } else {
            $ab = 'MCCI ';
            $ab .= substr($dataLast->file_number, 5) + 1;
            $input['file_number'] = $ab;
        }
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;

        // Get Consigne Shipper
        $modelCcpackMaster = new CcpackMaster;
        $consigneeData = $modelCcpackMaster->getConsigneeShipper(Config::get('app.ccpackMasterImport')['consignee']);
        $shipperData = $modelCcpackMaster->getConsigneeShipper(Config::get('app.ccpackMasterImport')['shipper']);
        $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
        $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';

        $model = CcpackMaster::create($input);
        Activities::log('create', 'ccpackMaster', $model);
        $masterCcpackId = $model->id;
        $masterFileNumber = $model->file_number;
        $arrivalDate = $model->arrival_date;

        if (!empty($model->hawb_hbl_no)) {
            $exploadedIds = explode(',', $model->hawb_hbl_no);
            foreach ($exploadedIds as $k => $v) {
                ccpack::where('id', $v)->update(['master_ccpack_id' => $masterCcpackId, 'master_file_number' => $masterFileNumber, 'arrival_date' => $arrivalDate]);
            }
        }
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('ccpack-master');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CcpackMaster  $ccpackMaster
     * @return \Illuminate\Http\Response
     */
    public function show(CcpackMaster $ccpackMaster, $id)
    {
        $checkPermission = User::checkPermission(['viewdetails_ccpack_master'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'ccpackMaster')->orderBy('id', 'desc')->get()->toArray();
        $HouseAWBData = DB::table('ccpack')->where('master_ccpack_id', $id)->orderBy('id', 'desc')->get();

        $model = CcpackMaster::find($id);
        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.ccpack_master_id', $id)
            ->orderBy('invoices.id', 'desc')->get();

        $totalInvoiceOfHTG = 0;
        $totalInvoiceOfUSD = 0;

        foreach ($invoices as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInvoiceOfUSD += $v->total;

            if ($v->currencyCode == 'HTG')
                $totalInvoiceOfHTG += $v->total;
        }

        $dataExpense = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('ccpack_master_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('ccpack_master_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();

        $attachedFiles = DB::table('ccpack_uploaded_files')->where('master_file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('ccpack_master', 'expenses.ccpack_master_id', '=', 'ccpack_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ccpack_master_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('ccpack_master', 'expenses.ccpack_master_id', '=', 'ccpack_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ccpack_master_id', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();

        /* Report by billing items */
        $getBillingAssociatedData = $getBillingItemData = DB::table('billing_items')
            //->select(DB::raw("CONCAT(billing_items.id,'-',costs.id) as fullcost"))
            ->select('billing_items.id as billingItemId', DB::raw('group_concat(costs.id) as costIds'))
            ->leftJoin('costs', 'costs.cost_billing_code', '=', 'billing_items.id')
            ->groupBy('billing_items.id')
            ->get();
        foreach ($getBillingAssociatedData as $k => $v) {
            $finalGetBillingAssociatedData[$getBillingAssociatedData[$k]->billingItemId] = $v;
        }
        /* Report by billing items */

        $getBillingItemData = DB::table('invoices')
            ->select(['invoice_item_details.fees_name as biliingItemId', 'invoice_item_details.fees_name_desc as biliingItemDescription', 'invoice_item_details.total_of_items as biliingItemAmount', 'currency.code as currencyCode', 'currency.code as billingCurrencyCode'])
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.ccpack_master_id', $id)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.ccpack_master_id', $id)
            ->where('expenses.deleted', '0')
            ->get();

        /* Report by billing items */
        $finalReportData = array();
        foreach ($finalGetBillingAssociatedData as $k => $v) {
            foreach ($getBillingItemData as $k1 => $v1) {
                if ($k == $v1->biliingItemId) {
                    $finalReportData[$k]['billingData'][] = $v1;
                }
            }

            foreach ($getCostItemData as $k1 => $v1) {
                if (in_array($v1->costItemId, explode(',', $v->costIds))) {
                    $finalReportData[$k]['costData'][] = $v1;
                }
            }
        }

        foreach ($finalReportData as $k => $v) {
            $countBillingData = 0;
            $countCostData = 0;
            if (isset($v['billingData']))
                $countBillingData = count($v['billingData']);
            if (isset($v['costData']))
                $countCostData = count($v['costData']);
            $maxCount = max($countBillingData, $countCostData);
            if ($maxCount == $countBillingData)
                $vG = 'billingGreater';
            else
                $vG = 'costGreater';

            if ($vG == 'costGreater') {
                $v['allData'] = $v['costData'];
                foreach ($v['costData'] as $k1 => $v1) {
                    $v['allData'][$k1]->biliingItemId = isset($v['billingData'][$k1]->biliingItemId) ? $v['billingData'][$k1]->biliingItemId : '';
                    $v['allData'][$k1]->biliingItemDescription = isset($v['billingData'][$k1]->biliingItemDescription) ? $v['billingData'][$k1]->biliingItemDescription : '';
                    $v['allData'][$k1]->biliingItemAmount = isset($v['billingData'][$k1]->biliingItemAmount) ? $v['billingData'][$k1]->biliingItemAmount : '';
                    $v['allData'][$k1]->billingCurrencyCode = isset($v['billingData'][$k1]->billingCurrencyCode) ? $v['billingData'][$k1]->billingCurrencyCode : '';
                }
                $finalReportData[$k] = $v;
            } else {
                $v['allData'] = $v['billingData'];
                foreach ($v['billingData'] as $k1 => $v1) {
                    $v['allData'][$k1]->costItemId = isset($v['costData'][$k1]->costItemId) ? $v['costData'][$k1]->costItemId : '';
                    $v['allData'][$k1]->costDescription = isset($v['costData'][$k1]->costDescription) ? $v['costData'][$k1]->costDescription : '';
                    $v['allData'][$k1]->costAmount = isset($v['costData'][$k1]->costAmount) ? $v['costData'][$k1]->costAmount : '';
                    $v['allData'][$k1]->costCurrencyCode = isset($v['costData'][$k1]->costCurrencyCode) ? $v['costData'][$k1]->costCurrencyCode : '';
                }
                $finalReportData[$k] = $v;
            }
            unset($finalReportData[$k]['costData']);
            unset($finalReportData[$k]['billingData']);
        }

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        return view("ccpack-master.view-details", ['model' => $model, 'invoices' => $invoices, 'activityData' => $activityData, 'dataExpense' => $dataExpense, 'id' => $id, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'finalReportData' => $finalReportData, 'HouseAWBData' => $HouseAWBData]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CcpackMaster  $ccpackMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(CcpackMaster $ccpackMaster, $id, $fileType = null)
    {
        $model = DB::table('ccpack_master')->where('id', $id)->first();
        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $dataCargoForImport = DB::table('ccpack_master')->where('deleted', 0)->where('id', '<>', $id)->whereNotNull('hawb_hbl_no')->get();
        $existingImportHawbFiles = '';
        $existingImportHawbFilesArray = array();
        if (!empty($dataCargoForImport)) {
            foreach ($dataCargoForImport as $key => $value) {
                $existingImportHawbFiles .= $value->hawb_hbl_no . ',';
                //array_push($existingHawbFiles,$dataExp);
            }
        }
        $nexistingImportHawbFiles = rtrim($existingImportHawbFiles, ',');
        $existingImportHawbFilesArray = explode(',', $nexistingImportHawbFiles);

        $dataImportHawb = DB::table('ccpack')->where('deleted', '0')->whereNotIn('id', $existingImportHawbFilesArray)->get();

        $dataImportHawbAll = array();
        foreach ($dataImportHawb as $k => $v) {
            $dataImportHawbAll[$k]['value'] = $v->id;
            $dataImportHawbAll[$k]['hawb_hbl_no'] = $v->awb_number;

            $dataClientConsignee = DB::table('clients')->where('id', $v->consignee)->first();
            $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

            $dataImportHawbAll[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
            $dataImportHawbAll[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
        }
        $dataImportHawbAll = json_encode($dataImportHawbAll, JSON_NUMERIC_CHECK);

        return view('ccpack-master._form', ['model' => $model, 'agents' => $agents, 'fileType' => $fileType, 'billingParty' => $billingParty, 'dataImportHawbAll' => $dataImportHawbAll]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CcpackMaster  $ccpackMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CcpackMaster $ccpackMaster, $id)
    {
        $model = CcpackMaster::find($id);
        $input = $request->all();
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $masterCcpackId = $model->id;
        $masterFileNumber = $model->file_number;
        $arrivalDate = $model->arrival_date;
        $model->fill($input);
        $modelCcpackMaster = new CcpackMaster;
        Activities::log('update', 'ccpackMaster', $model);
        $consigneeData = $modelCcpackMaster->getConsigneeShipper(Config::get('app.ccpackMasterImport')['consignee']);
        $shipperData = $modelCcpackMaster->getConsigneeShipper(Config::get('app.ccpackMasterImport')['shipper']);
        $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
        $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';

        $model->update($input);

        if (!empty($model->hawb_hbl_no)) {
            $exploadedIds = explode(',', $model->hawb_hbl_no);
            foreach ($exploadedIds as $k => $v) {
                ccpack::where('id', $v)->update(['master_ccpack_id' => $masterCcpackId, 'master_file_number' => $masterFileNumber, 'arrival_date' => $arrivalDate]);
            }
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('ccpack-master');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CcpackMaster  $ccpackMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(CcpackMaster $ccpackMaster, $id)
    {
        DB::table('ccpack_master')->where('id', $id)->update(['deleted' => 1, 'deleted_on' => date('Y-m-d h:i:s'), 'deleted_by' => auth()->user()->id]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'ccpackMaster';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function checkuniqueccpackmasterawbnumber()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit') {
            $upsData = DB::table('ccpack_master')->where('deleted', '0')->where('tracking_number', $number)->where('id', '<>', $id)->count();
        } else {
            $upsData = DB::table('ccpack_master')->where('deleted', '0')->where('tracking_number', $number)->count();
        }
        if ($upsData) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getccpackmasterdata()
    {
        $id = $_POST['ccpackMasterId'];
        $aAr = array();
        $dataBilling = DB::table('ccpack_master')->where('id', $id)->first();
        $dataConsignee = DB::table('clients')->where('id', $dataBilling->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $dataBilling->shipper_name)->first();
        $aAr['consigneeName'] = $dataConsignee->company_name;
        $aAr['shipperName'] = $dataShipper->company_name;
        $aAr['billing_party'] = $dataBilling->billing_party;
        return json_encode($aAr);
    }

    public function gettotalweightvolumeandpieces()
    {
        $ids = $_POST['selectedAWB'];
        $ids = explode(',', $ids);
        $packageData = DB::table('ccpack')->whereIn('id', $ids)->get();
        $weight = 0.00;
        $volumne = 0.00;
        $peices = 0;
        foreach ($packageData as $key => $value) {
            $weight += $value->weight;
            $peices += $value->no_of_pcs;
        }
        $data['weight'] = number_format($weight, 2);
        $data['pieces'] = $peices;
        return json_encode($data);
    }
}
