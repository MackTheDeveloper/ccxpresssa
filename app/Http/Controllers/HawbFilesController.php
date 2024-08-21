<?php

namespace App\Http\Controllers;

use App\HawbFiles;
use App\Activities;
use App\HawbPackages;
use App\HawbContainers;
use App\CargoPackages;
use App\Expense;
use App\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
use Illuminate\Validation\Rule;
use PDF;
use Config;

class HawbFilesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_cargo_hawb'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        return view("hawb-files.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_cargo_hawb'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');


        $model = new HawbFiles;
        $modelCargoPackage = new HawbPackages;
        $modelCargoContainer = new HawbContainers;
        $dataImportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '1')->get()->pluck('awb_bl_no', 'id');
        $dataExportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '2')->get()->pluck('awb_bl_no', 'id');
        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);
        $model->weight = '0.00';
        $model->hdate = date('d-m-Y');
        return view('hawb-files.form', ['model' => $model, 'dataImportAwbNos' => $dataImportAwbNos, 'modelCargoPackage' => $modelCargoPackage, 'modelCargoContainer' => $modelCargoContainer, 'dataExportAwbNos' => $dataExportAwbNos, 'agents' => $agents]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->cargo_operation_type == 1) {
            $customMessage = array('hawb_hbl_no.required' => 'HAWB No. field is required', 'hawb_hbl_no.unique' => 'HAWB No. already exist', 'shipper_name.required' => 'Shipper name is required.', 'consignee_name.required' => 'Client name is required.');
            $validator = Validator::make($request->all(), [
                'hawb_hbl_no' => ['required', Rule::unique('hawb_files')->where('deleted', '0')->where('cargo_operation_type', '1')],
                'shipper_name' => 'required',
                'consignee_name' => 'required',
            ], $customMessage);
            if (!$validator->passes()) {
                return Response::json(['errors' => $validator->errors()]);
            }
        } else {
            $customMessage = array(
                'export_hawb_hbl_no.required' => 'HAWB No. field is required', 'export_hawb_hbl_no.unique' => 'HAWB No. already exist', 'shipper_name.required' => 'Shipper name is required.', 'consignee_name.required' => 'Client name is required.'
            );
            $validator = Validator::make($request->all(), [
                'export_hawb_hbl_no' => ['required', Rule::unique('hawb_files')->where('deleted', '0')->where('cargo_operation_type', '2')],
                'shipper_name' => 'required',
                'consignee_name' => 'required',
            ], $customMessage);
            if (!$validator->passes()) {
                return Response::json(['errors' => $validator->errors()]);
            }
        }
        $input = $request->all();
        // Save file number
        $dataLast = DB::table('hawb_files')->orderBy('id', 'desc')->first();
        if (empty($dataLast)) {
            $input['file_number'] = 'H 1110';
            /*if($input['cargo_operation_type'] == 1)
                $input['file_number'] = 'H 1110';
            else
                $input['file_number'] = 'E1110';*/
        } else {
            $ab = 'H ';
            $ab .= substr($dataLast->file_number, 2) + 1;
            $input['file_number'] = $ab;
        }

        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $input['created_by'] = auth()->user()->id;

        if ($input['cargo_operation_type'] == 1) {
            $cargoPackage = $input['modalCargoPackage'];
            unset($input['modalCargoPackage']);
            $cargoContainer = $input['modalCargoContainer'];
            unset($input['modalCargoContainer']);

            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;

            $consignee_name = $input['consignee_name'];
            $shipper_name = $input['shipper_name'];

            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
            if (empty($clientData)) {

                $newClientData['company_name'] = $consignee_name;
                $newClientData['company_address'] = $input['consignee_address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            } else {
                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            }

            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            if (empty($clientData)) {
                $newClientData['company_name'] = $shipper_name;
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
                $input['shipper_name'] = $clientData->id;
            } else {
                $input['shipper_name'] = $clientData->id;
            }
        }
        if ($input['cargo_operation_type'] == 2) {

            $input['cargo_id'] = $input['export_cargo_id'];

            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['sent_on'] = !empty($input['sent_on']) ? date('Y-m-d', strtotime($input['sent_on'])) : null;

            $consignee_name = $input['consignee_name'];
            $shipper_name = $input['shipper_name'];

            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
            if (empty($clientData)) {

                $newClientData['company_name'] = $consignee_name;
                $newClientData['company_address'] = $input['consignee_address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            } else {
                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            }

            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            if (empty($clientData)) {
                $newClientData['company_name'] = $shipper_name;
                $newClientData['company_address'] = $input['shipper_address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
                $input['shipper_name'] = $clientData->id;
                $input['shipper_address'] = $input['shipper_address'];
            } else {
                $input['shipper_name'] = $clientData->id;
                $input['shipper_address'] = $input['shipper_address'];
            }
        }
        $model = HawbFiles::create($input);
        Activities::log('create', 'houseFile', $model);

        if ($input['cargo_operation_type'] == 1) {
            if ($input['flag_package_container'] == 1) {
                $dataExportHawbAll = array();
                $modelCargoPackageDetail = new HawbPackages();
                $modelCargoPackageDetail->cargo_id = $model->cargo_id;
                $modelCargoPackageDetail->hawb_id = $model->id;
                $modelCargoPackageDetail->pweight = $cargoPackage['pweight'];
                $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
                $modelCargoPackageDetail->pvolume = $cargoPackage['pvolume'];
                $modelCargoPackageDetail->measure_volume = $input['measure_volume'];
                $modelCargoPackageDetail->ppieces = $cargoPackage['ppieces'];
                $modelCargoPackageDetail->save();
                //HawbPackages::create($modelCargoPackageDetail);
            } else {
                $countContainer = count($cargoContainer['container_number']);
                for ($i = 0; $i < $countContainer; $i++) {
                    $modelCargoContainerDetails = new HawbContainers();
                    $modelCargoContainerDetails->cargo_id = $model->cargo_id;
                    $modelCargoContainerDetails->hawb_id = $model->id;
                    $modelCargoContainerDetails->container_number = $cargoContainer['container_number'][$i];
                    $modelCargoContainerDetails->save();
                }
            }
            $dataCargoForImport = DB::table('cargo')->where('deleted', 0)->where('cargo_operation_type', 1)->whereNotNull('hawb_hbl_no')->get();
            $existingImportHawbFiles = '';
            $existingImportHawbFilesArray = array();
            if (!empty($dataCargoForImport)) {
                foreach ($dataCargoForImport as $key => $value) {
                    $existingImportHawbFiles .= $value->hawb_hbl_no . ',';
                    //array_push($existingHawbFiles,$dataExp);
                }
            }
            $nexistingImportHawbFiles =  rtrim($existingImportHawbFiles, ',');
            $existingImportHawbFilesArray = explode(',', $nexistingImportHawbFiles);

            $dataImportHawb = DB::table('hawb_files')->where('deleted', 0)->where('cargo_operation_type', 1)->whereNotIn('id', $existingImportHawbFilesArray)->get();

            $dataImportHawbAll = array();
            foreach ($dataImportHawb as $k => $v) {
                $dataImportHawbAll[$k]['value'] = $v->id;
                $dataImportHawbAll[$k]['hawb_hbl_no'] = $v->hawb_hbl_no;

                $dataClientConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
                $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                $dataImportHawbAll[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
                $dataImportHawbAll[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            }
            $dataImportHawbAll = json_encode($dataImportHawbAll, JSON_NUMERIC_CHECK);
        } else {
            $dataImportHawbAll = array();
            $modelCargoPackageDetail = new HawbPackages();
            $modelCargoPackageDetail->cargo_id = $model->cargo_id;
            $modelCargoPackageDetail->hawb_id = $model->id;
            $modelCargoPackageDetail->pweight = $input['weight'];
            $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
            $modelCargoPackageDetail->ppieces = $input['no_of_pieces'];
            $modelCargoPackageDetail->save();

            $dataCargoForExport = DB::table('cargo')->where('deleted', 0)->where('cargo_operation_type', 2)->whereNotNull('hawb_hbl_no')->get();
            $existingExportHawbFiles = '';
            $existingExportHawbFilesArray = array();
            if (!empty($dataCargoForExport)) {
                foreach ($dataCargoForExport as $key => $value) {
                    $existingExportHawbFiles .= $value->hawb_hbl_no . ',';
                    //array_push($existingHawbFiles,$dataExp);
                }
            }

            $nexistingExportHawbFiles =  rtrim($existingExportHawbFiles, ',');
            $existingExportHawbFilesArray = explode(',', $nexistingExportHawbFiles);

            $dataExportHawb = DB::table('hawb_files')->where('deleted', 0)->where('cargo_operation_type', 2)->whereNotIn('id', $existingExportHawbFilesArray)->get();
            $dataExportHawbAll = array();
            foreach ($dataExportHawb as $k => $v) {
                $dataExportHawbAll[$k]['value'] = $v->id;
                $dataExportHawbAll[$k]['hawb_hbl_no'] = $v->export_hawb_hbl_no;

                $dataClientConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
                $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                $dataExportHawbAll[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
                $dataExportHawbAll[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            }
            $dataExportHawbAll = json_encode($dataExportHawbAll, JSON_NUMERIC_CHECK);
        }

        if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {
            if ($model->cargo_operation_type == 1)
                $pdf = PDF::loadView('hawb-files.printimport', ['model' => $model]);
            else
                $pdf = PDF::loadView('hawb-files.printexport', ['model' => $model]);

            $pdf_file = $model->id . '_hawbfile.pdf';
            $pdf_path = 'public/hawbFilePdf/' . $pdf_file;
            $pdf->save($pdf_path);
            return Response::json(['success' => '1', 'printUrl' => url('/') . '/' . $pdf_path]);
        } else {
            return Response::json(['success' => '1', 'dataImportHawbAll' => $dataImportHawbAll, 'dataExportHawbAll' => $dataExportHawbAll]);
        }

        //Session::flash('flash_message', 'Record has been created successfully');
        //return redirect('hawbfiles');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\HawbFiles  $hawbFiles
     * @return \Illuminate\Http\Response
     */
    public function show($rid, $flag = null)
    {
        $model = HawbFiles::find($rid);
        $activityData = DB::table('activities')->where('related_id', $rid)->where('type', 'houseFile')->orderBy('id', 'desc')->get()->toArray();
        $attachedFiles = DB::table('cargo_uploaded_files')->where('file_id', $rid)->where('flag_module', 'houseFile')->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $dataExpense = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('house_file_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('house_file_id', $rid)
            ->orderBy('expense_id', 'desc')
            ->get();

        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('hawb_hbl_no', $rid)
            ->orderBy('invoices.id', 'desc')->get();

        $totalInvoiceOfHTG = 0;
        $totalInvoiceOfUSD = 0;

        foreach ($invoices as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInvoiceOfUSD += $v->total;

            if ($v->currencyCode == 'HTG')
                $totalInvoiceOfHTG += $v->total;
        }

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('hawb_files', 'expenses.house_file_id', '=', 'hawb_files.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.house_file_id', $rid)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('hawb_files', 'expenses.house_file_id', '=', 'hawb_files.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.house_file_id', $rid)
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
            ->where('invoices.hawb_hbl_no', $rid)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.house_file_id', $rid)
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
        /* Report by billing items */

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        $fileTypes = Config::get('app.fileTypes');
        $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $rid)->first();
        if (empty($modelCargoPackage))
            $modelCargoPackage = new HawbPackages;
        return view('hawb-files.view', ['model' => $model, 'rid' => $rid, 'dataExpense' => $dataExpense, 'invoices' => $invoices, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'activityData' => $activityData, 'finalReportData' => $finalReportData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'modelCargoPackage' => $modelCargoPackage]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\HawbFiles  $hawbFiles
     * @return \Illuminate\Http\Response
     */
    public function edit(HawbFiles $hawbFiles, $id)
    {
        $checkPermission = User::checkPermission(['update_cargo_hawb'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('hawb_files')->where('id', $id)->first();
        $dataAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no', 'id');


        $dataImportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '1')->get()->pluck('awb_bl_no', 'id');
        $dataExportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '2')->get()->pluck('awb_bl_no', 'id');

        $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $id)->first();
        if (empty($modelCargoPackage))
            $modelCargoPackage = new HawbPackages;

        $modelCargoContainer = new HawbContainers;

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $model->hdate = date('d-m-Y', strtotime($model->hdate));
        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        return view("hawb-files.form", ['model' => $model, 'dataAwbNos' => $dataAwbNos, 'modelCargoPackage' => $modelCargoPackage, 'modelCargoContainer' => $modelCargoContainer, 'dataImportAwbNos' => $dataImportAwbNos, 'dataExportAwbNos' => $dataExportAwbNos, 'billingParty' => $billingParty, 'agents' => $agents]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\HawbFiles  $hawbFiles
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HawbFiles $hawbFiles, $id)
    {
        if ($request->cargo_operation_type == 1) {
            $id = $request->all()['id'];
            $customMessage = array('hawb_hbl_no.required' => 'HAWB No. field is required', 'hawb_hbl_no.unique' => 'HAWB No. already exist');
            $validator = Validator::make($request->all(), [
                'hawb_hbl_no' => ['required', Rule::unique('hawb_files')->where('deleted', '0')->where('cargo_operation_type', '1')->ignore($id)]
            ], $customMessage);
            if (!$validator->passes()) {
                return Response::json(['errors' => $validator->errors()]);
            }
        } else {
            $id = $request->all()['id'];
            $customMessage = array(
                'export_hawb_hbl_no.required' => 'HAWB No. field is required', 'export_hawb_hbl_no.unique' => 'HAWB No. already exist'
            );
            $validator = Validator::make($request->all(), [
                'export_hawb_hbl_no' => ['required', Rule::unique('hawb_files')->where('deleted', '0')->where('cargo_operation_type', '2')->ignore($id)]
            ], $customMessage);
            if (!$validator->passes()) {
                return Response::json(['errors' => $validator->errors()]);
            }
        }

        $model = hawbFiles::find($id);
        $model->fill($request->input());
        Activities::log('update', 'houseFile', $model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        if ($input['cargo_operation_type'] == 1) {
            $cargoPackage = $input['modalCargoPackage'];
            unset($input['modalCargoPackage']);
            $cargoContainer = $input['modalCargoContainer'];
            unset($input['modalCargoContainer']);

            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;

            $consignee_name = $input['consignee_name'];
            $shipper_name = $input['shipper_name'];

            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
            if (empty($clientData)) {

                $newClientData['company_name'] = $consignee_name;
                $newClientData['company_address'] = $input['consignee_address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            } else {
                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            }

            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            if (empty($clientData)) {
                $newClientData['company_name'] = $shipper_name;
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
                $input['shipper_name'] = $clientData->id;
            } else {
                $input['shipper_name'] = $clientData->id;
            }
        }
        if ($input['cargo_operation_type'] == 2) {
            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['sent_on'] = !empty($input['sent_on']) ? date('Y-m-d', strtotime($input['sent_on'])) : null;

            $consignee_name = $input['consignee_name'];
            $shipper_name = $input['shipper_name'];

            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
            if (empty($clientData)) {

                $newClientData['company_name'] = $consignee_name;
                $newClientData['company_address'] = $input['consignee_address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            } else {
                $input['consignee_name'] = $clientData->id;
                $input['consignee_address'] = $input['consignee_address'];
            }

            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            if (empty($clientData)) {
                $newClientData['company_name'] = $shipper_name;
                $newClientData['company_address'] = $input['shipper_address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
                $input['shipper_name'] = $clientData->id;
                $input['shipper_address'] = $input['shipper_address'];
            } else {
                $input['shipper_name'] = $clientData->id;
                $input['shipper_address'] = $input['shipper_address'];
            }
        }
        $model->update($input);

        if ($input['cargo_operation_type'] == 1) {
            if ($input['flag_package_container'] == 1) {
                HawbPackages::where('hawb_id', $id)->delete();
                HawbContainers::where('hawb_id', $id)->delete();
                $modelCargoPackageDetail = new HawbPackages();
                $PackagesCargo = new CargoPackages();
                $modelCargoPackageDetail->cargo_id = $model->cargo_id;
                $modelCargoPackageDetail->hawb_id = $model->id;
                $modelCargoPackageDetail->pweight = $cargoPackage['pweight'];;
                $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
                $modelCargoPackageDetail->pvolume = $cargoPackage['pvolume'];;
                $modelCargoPackageDetail->measure_volume = $input['measure_volume'];
                $modelCargoPackageDetail->ppieces = $cargoPackage['ppieces'];;


                // $PackagesCargo->pweight = $cargoPackage['pweight'];;
                // $PackagesCargo->measure_weight = $input['measure_weight'];
                // $PackagesCargo->pvolume = $cargoPackage['pvolume'];;
                // $PackagesCargo->measure_volume = $input['measure_volume'];
                // $PackagesCargo->ppieces = $cargoPackage['ppieces'];;
                $modelCargoPackageDetail->save();
                //$PackagesCargo->save();
            } else {
                HawbPackages::where('hawb_id', $id)->delete();
                HawbContainers::where('hawb_id', $id)->delete();
                $countContainer = count($cargoContainer['container_number']);
                for ($i = 0; $i < $countContainer; $i++) {
                    $modelCargoContainerDetails = new HawbContainers();
                    $modelCargoContainerDetails->cargo_id = $model->cargo_id;
                    $modelCargoContainerDetails->hawb_id = $model->id;
                    $modelCargoContainerDetails->container_number = $cargoContainer['container_number'][$i];
                    $modelCargoContainerDetails->save();
                }
            }
        } else {
            HawbPackages::where('hawb_id', $id)->delete();
            $modelCargoPackageDetail = new HawbPackages();
            $PackagesCargo = new CargoPackages();
            $modelCargoPackageDetail->cargo_id = $model->cargo_id;
            $modelCargoPackageDetail->hawb_id = $model->id;
            $modelCargoPackageDetail->pweight = $input['weight'];;
            $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
            $modelCargoPackageDetail->ppieces = $input['no_of_pieces'];;
            $modelCargoPackageDetail->save();
        }
        return Response::json(['success' => '1']);
        //Session::flash('flash_message', 'Record has been updated successfully');
        //return redirect('hawbfiles');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\HawbFiles  $hawbFiles
     * @return \Illuminate\Http\Response
     */
    public function destroy(HawbFiles $hawbFiles, $id)
    {
        $model = HawbFiles::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'houseFile';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function print($id, $cargoType)
    {
        $model = DB::table('hawb_files')->where('id', $id)->first();
        if ($cargoType == 1)
            $pdf = PDF::loadView('hawb-files.printimport', ['model' => $model]);
        else
            $pdf = PDF::loadView('hawb-files.printexport', ['model' => $model]);

        $pdf_file = $model->id . '_hawbfile.pdf';
        $pdf_path = 'public/hawbFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function gethousedata()
    {
        $id = $_POST['houseId'];
        $aAr = array();
        $dataBilling = DB::table('hawb_files')->where('id', $id)->first();
        $dataConsignee = DB::table('clients')->where('id', $dataBilling->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $dataBilling->shipper_name)->first();
        if (empty($dataConsignee->company_name))
            $aAr['consigneeName'] = '';
        else
            $aAr['consigneeName'] = $dataConsignee->company_name;

        if (empty($dataShipper->company_name))
            $aAr['shipperName'] = '';
        else
            $aAr['shipperName'] = $dataShipper->company_name;

        $aAr['billing_party'] = $dataBilling->billing_party;

        return json_encode($aAr);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoHAWBEdit = User::checkPermission(['update_cargo_hawb'], '', auth()->user()->id);
        $permissionCargoHAWBDelete = User::checkPermission(['delete_cargo_hawb'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($column == 2) {
            $column = 0;
        }

        $orderby = ['hawb_files.id', 'cargo_operation_type', 'file_number', 'c3.company_name', 'hawb_scan_status','opening_date', '', 'c1.company_name', 'c2.company_name', '', ''];

        $total = HawbFiles::selectRaw('count(*) as total');
        //->where('deleted', '0')
        if (!empty($fileStatus)) {
            $total = $total->where('hawb_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('hawb_files')
            ->selectRaw('hawb_files.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'hawb_files.billing_party');
        //->where('hawb_files.deleted', '0');
        if (!empty($fileStatus)) {
            $query = $query->where('hawb_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('hawb_files')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'hawb_files.billing_party');
        //->where('hawb_files.deleted', '0');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('hawb_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->where('hawb_hbl_no', 'like', '%' . $search . '%')
                            ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere(function ($query) use ($search) {
                        $query->where('hawb_hbl_no', 'like', '%' . $search . '%')
                            ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%');
                    })
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $dataBillingParty = app('App\Clients')->getClientData($value->billing_party);
            $consigneeData = app('App\Clients')->getClientData($value->consignee_name);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $shipperData = app('App\Clients')->getClientData($value->shipper_name);
            $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
            $invoiceNumbers = Expense::getHouseFileInvoicesOfFile($value->id);
            $dataConsolidate  = DB::table('cargo')
                ->select(DB::raw('group_concat(file_number) as MasterFiles'))
                ->whereRaw("find_in_set($value->id,hawb_hbl_no)")
                ->first();


            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printhawbfiles", [$value->id, $value->cargo_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete =  route('deletehawbfile', $value->id);
            $edit =  route('edithawbfile', $value->id);
            if ($value->deleted == '0') {
                if ($permissionCargoHAWBEdit) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }
                if ($permissionCargoHAWBDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';
                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['houseFile', $value->id]) . '">Close File</a></li>';
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

            $data[] = [$value->id, $value->cargo_operation_type == 1 ? 'Import' :  'Export', $value->file_number, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($value->hawb_scan_status) ? $value->hawb_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($value->hawb_scan_status) ? $value->hawb_scan_status : '-'] : '-', date('d-m-Y', strtotime($value->opening_date)), $value->cargo_operation_type == 1 ? $value->hawb_hbl_no : $value->export_hawb_hbl_no, $consignee, $shipper, !empty($dataConsolidate->MasterFiles) ? $dataConsolidate->MasterFiles : 'Not Assigned', $invoiceNumbers, ($value->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserversidehawbfiles()
    {
        $flag = $_POST['flag'];
        if ($flag == 'getHouseFileData') {
            $houseId = $_POST['houseId'];
            return json_encode(HawbFiles::getHouseFileData($houseId));
        }
    }
}
