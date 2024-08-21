<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Clients;
use App\Customs;
use App\Ups;
use App\upsFreightCommission;
use App\upsImportExportCommission;
use App\UpsInvoiceItemDetails;
use App\UpsInvoices;
use App\Upspackages;
use App\Currency;
use App\User;
use App\InvoicePayments;
use App\Expense;
use Config;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use Response;
use Session;
use stdClass;

class UpsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /*$upsData = DB::table('ups_details')->get();
        $i = 1110;
        foreach ($upsData as $key => $value) {
        $nV = 'I'.$i;
        $i = $i + 1;
        DB::table('ups_details')
        ->where('id', $value->id)
        ->update(['file_number' => $nV]);
        }
        pre("Tset");*/

        $checkPermission = User::checkPermission(['listing_courier_import'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        /* $upsData = DB::table('ups_details')->where('deleted', '0')->orderBy('id', 'desc')->get();

        $upsFileNames = DB::table('ups_details')->select(['id', 'file_name'])->where('deleted', '0')->whereNotNull('file_name')->orderBy('id', 'desc')->pluck('file_name', 'file_name');

        return view("ups.index", ['upsData' => $upsData, 'upsFileNames' => $upsFileNames]); */
        return view("ups.index");
    }

    public function viewall()
    {
        $checkPermission = User::checkPermission(['listing_courier_import'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $upsData = DB::table('ups_details')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("ups.viewall", ['upsData' => $upsData]);
    }

    public function viewlogfiles()
    {
        $upsData = DB::table('ups_details')->where('deleted', '0')->where('last_action_flag', 1)->orderBy('id', 'desc')->get();
        return view("ups.viewlogfiles", ['upsData' => $upsData]);
    }

    public function exportindex()
    {
        $upsExportData = DB::table('ups_export_details')->where('deleted', 0)->get();
        return view('ups.exportindex', ['upsExportData' => $upsExportData]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_courier_import'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = new Ups;
        $model->arrival_date = date('d-m-Y');

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');
        return view('ups._form', ['model' => $model, 'agents' => $agents, 'warehouses' => $warehouses]);
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

        $courier_operation_type = $input['courier_operation_type'];
        $GLOBALS['freightCommission'] = new upsFreightCommission;
        $getCommission = $GLOBALS['freightCommission'];
        $consignee_name = $input['consignee_name'];
        $shipper_name = $input['shipper_name'];
        $input['unit'] = 'KGS';
        if ($courier_operation_type == 1) {
            $fileType = 'import';
        } else {

            $fileType = 'export';
        }
        $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

        if (empty($clientData)) {

            $newClientData['company_name'] = $consignee_name;
            $newClientData['phone_number'] = $input['consignee_telephone'];
            $newClientData['company_address'] = $input['consignee_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

            $input['consignee_name'] = $clientData->id;
            $input['consignee_telephone'] = $input['consignee_telephone'];
            $input['consignee_address'] = $input['consignee_address'];
            if ($courier_operation_type == 2) {
                $input['consignee_city_state'] = $input['consignee_city_state'];
            }
        } else {
            $input['consignee_name'] = $clientData->id;
            $input['consignee_telephone'] = $input['consignee_telephone'];
            $input['consignee_address'] = $input['consignee_address'];
            if ($courier_operation_type == 2) {
                $input['consignee_city_state'] = $input['consignee_city_state'];
            }
        }

        $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
        if (empty($clientData)) {

            $newClientData['company_name'] = $shipper_name;
            $newClientData['phone_number'] = $input['shipper_telephone'];
            $newClientData['company_address'] = $input['shipper_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            $input['shipper_name'] = $clientData->id;
            $input['shipper_telephone'] = $input['shipper_telephone'];
            $input['shipper_address'] = $input['shipper_address'];
        } else {
            $input['shipper_name'] = $clientData->id;
            $input['shipper_telephone'] = $input['shipper_telephone'];
            $input['shipper_address'] = $input['shipper_address'];
        }

        $commission = '0.00';
        if ($input['billing'] == 1) {
            $input['fc'] = 1;
            $input['fd'] = 0;
            $input['pp'] = 0;

            if ($input['package_type'] == 'LTR') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'fc', 'ltr');
            } else if ($input['package_type'] == 'DOC') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'fc', 'doc');
            } else {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'fc', 'pkg', $input['nbr_pcs']);
            }
        } else if ($input['billing'] == 2) {
            $input['fc'] = 0;
            $input['fd'] = 1;
            $input['pp'] = 0;
        } else {
            $input['fc'] = 0;
            $input['fd'] = 0;
            $input['pp'] = 1;

            if ($input['package_type'] == 'LTR') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'pp', 'ltr');
            } else if ($input['package_type'] == 'DOC') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'pp', 'doc');
            } else {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'pp', 'pkg', $input['nbr_pcs']);
            }
        }

        $dataLast = DB::table('ups_details')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
        if (empty($dataLast)) {
            if ($input['courier_operation_type'] == 1) {
                $input['file_number'] = 'UPI 1110';
            } else {
                $input['file_number'] = 'UPE 1110';
            }
        } else {
            if ($input['courier_operation_type'] == 1) {
                $ab = 'UPI ';
            } else {
                $ab = 'UPE ';
            }
            $ab .= substr($dataLast->file_number, 4) + 1;
            $input['file_number'] = $ab;
        }
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        if ($courier_operation_type == 1) {
            if (!empty($input['agent_id'])) {
                //$input['ups_scan_status'] = 2;
            } else {
                //$input['ups_scan_status'] = 1;
            }
        }

        $model = Ups::create($input);
        //pre($model->courier_operation_type);
        if ($model->courier_operation_type == 1) {
            $dir = "Files/Courier/Ups/Import/" . $model->file_number;
        } else {
            $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
        }
        $filePath = $dir;
        /*
        $path = base64_encode($filePath);
        $urlAction = url('file/mkDir?model='.$path);
        $adminModel = new Admin;
        $adminModel->backgroundPost($urlAction);
         */
        //pre($filePath);
        $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
        $lastUpsExport = DB::table('ups_details')->where('deleted', 0)->orderBy('id', 'DESC')->first();
        $dataCommission['ups_file_id'] = $lastUpsExport->id;
        $dataCommission['freight'] = $input['freight'];
        $dataCommission['commission'] = $commission;
        $dataCommission['created_by'] = auth()->user()->id;
        $dataCommission['created_at'] = date('Y-m-d h:i:s');
        upsFreightCommission::Create($dataCommission);

        if ($courier_operation_type == 1) {
            if ($input['billing'] == 2) {
                $dataInvoice['ups_id'] = $model->id;
                $dataInvoice['date'] = date('Y-m-d', strtotime($model->created_at));
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $dataInvoice['bill_no'] = 'UP-5001';
                } else {
                    $ab = 'UP-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $dataInvoice['bill_no'] = $ab;
                }

                $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                $dataConsignee = DB::table('clients')->where('id', $model->consignee_name)->first();
                $dataShipper = DB::table('clients')->where('id', $model->shipper_name)->first();
                $dataInvoice['bill_to'] = $dataClient->id;
                $dataInvoice['date'] = date('Y-m-d');
                $dataInvoice['email'] = $dataClient->email;
                $dataInvoice['telephone'] = $dataClient->phone_number;
                $dataInvoice['shipper'] = $dataShipper->company_name;
                $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                $dataInvoice['file_no'] = $model->file_number;
                $dataInvoice['awb_no'] = $model->awb_number;
                $dataInvoice['type_flag'] = 'IMPORT';
                $dataInvoice['weight'] = $model->weight;
                $dataInvoice['currency'] = '1';
                $dataInvoice['created_by'] = auth()->user()->id;
                $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                $dataInvoices = UpsInvoices::Create($dataInvoice);

                $allTotal = 0;
                $dataFdCharges = DB::table('fd_charges')->get();
                foreach ($dataFdCharges as $key => $value) {
                    $dataBilling = DB::table('billing_items')->where('item_code', $value->code)->first();
                    $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                    $dataInvoiceItems['fees_name'] = $dataBilling->id;
                    $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                    $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                    $dataInvoiceItems['quantity'] = 1;
                    $dataInvoiceItems['unit_price'] = $value->charge;
                    $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                    UpsInvoiceItemDetails::Create($dataInvoiceItems);

                    $allTotal += $dataInvoiceItems['total_of_items'];
                }

                $modelUpdateUpsInvoice = UpsInvoices::find($dataInvoices->id);
                $modelUpdateUpsInvoice->sub_total = $allTotal;
                $modelUpdateUpsInvoice->total = $allTotal;
                $modelUpdateUpsInvoice->balance_of = $allTotal;
                $modelUpdateUpsInvoice->update();

                $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                $pdf = PDF::loadView('upsinvoices.printupsinvoice', ['invoice' => (array) $dataAll]);
                $pdf_file = 'printUpsInvoice_' . $dataInvoices->id . '.pdf';
                $pdf_path = 'public/upsInvoices/' . $pdf_file;
                $pdf->save($pdf_path);
            }
        }

        if ($input['billing'] == 1 || $input['billing'] == 3)
            Ups::generateUpsInvoice($model->id);

        Activities::log('create', 'ups', $model);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('ups');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Ups  $ups
     * @return \Illuminate\Http\Response
     */
    public function show(Ups $ups)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Ups  $ups
     * @return \Illuminate\Http\Response
     */
    public function edit(Ups $ups, $id, $file_type = null)
    {
        $checkPermission = User::checkPermission(['update_courier_import'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');
        if ($file_type == 2) {
            $model = DB::table('ups_details')->where('id', $id)->where('courier_operation_type', 2)->first();
            $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
            return view("ups.editupsexport", ['model' => $model, 'warehouses' => $warehouses, 'billingParty' => $billingParty]);
        } else {
            $upsData = DB::table('ups_details')->where('id', $id)->first();
            $activityData = DB::table('activities')->where('related_id', $id)->orderBy('updated_on', 'desc')->get()->toArray();
            $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
            $agents = json_decode($agents, 1);
            ksort($agents);

            $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');

            $customData = DB::table('customs')->select(['file_number', 'custom_date'])->where('ups_details_id', $id)->first();
            if (empty($customData)) {
                $customData = new Customs;
            }

            if (empty($customData->file_number)) {
                $customData->file_number = '';
            }

            if (empty($customData->custom_date)) {
                $customData->custom_date = date('d-m-Y');
            }

            $status_before_delivery = DB::table('inprogress_status')->where('deleted', '0')->where('after_or_before', 1)->pluck('status', 'id')->toArray();
            $status_after_delivery = DB::table('inprogress_status')->where('deleted', '0')->where('after_or_before', 2)->pluck('status', 'id')->toArray();
            return view("ups.editups", ['upsData' => $upsData, 'activityData' => $activityData, 'agents' => $agents, 'billingParty' => $billingParty, 'customData' => $customData, 'status_before_delivery' => $status_before_delivery, 'status_after_delivery' => $status_after_delivery, 'warehouses' => $warehouses]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Ups  $ups
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ups $ups, $id)
    {
        $input = $request->all();
        $newClientData = [];
        $courier_operation_type = $input['courier_operation_type'];
        $GLOBALS['freightCommission'] = new upsFreightCommission;
        $getCommission = $GLOBALS['freightCommission'];
        $model = Ups::find($id);
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        if ($courier_operation_type == 1) {
            // if(!empty($input['agent_id']))
            //     $input['ups_scan_status'] = 2;
            // else
            //     $input['ups_scan_status'] = 1;
            //$input['ups_scan_status'] = $model->ups_scan_status;

            $oldAgent = $model->agent_id;
            $newAgent = $request->agent_id;
        }

        if ($courier_operation_type == 1) {
            $fileType = 'import';
        } else {
            $fileType = 'export';
        }
        $model->fill($input);

        // Save activity logs
        Activities::log('update', 'ups', $model);
        //pre($input);

        $clientDetail = DB::table('clients')->where('company_name', $input['consignee_name'])->orderBy('id', 'DESC')->first();
        if (!empty($clientDetail)) {
            //pre('comsignee');
            $input['consignee_name'] = $clientDetail->id;
        } else {
            //pre('empty consignee');
            $newClientData['company_name'] = $input['consignee_name'];
            $newClientData['company_address'] = $input['consignee_address'];
            $newClientData['phone_number'] = $input['consignee_telephone'];
            $newClientData['created_by'] = auth()->user()->id;
            $newClientData['created_at'] = date('Y-m-d h:i:s');
            Clients::Create($newClientData);
            $clientDetail = DB::table('clients')->where('company_name', $input['consignee_name'])->orderBy('id', 'DESC')->first();
            $input['consignee_name'] = $clientDetail->id;
        }
        $shippertDetail = DB::table('clients')->where('company_name', $input['shipper_name'])->orderBy('id', 'DESC')->first();
        if (!empty($shippertDetail)) {
            //pre('shipper');
            $input['shipper_name'] = $shippertDetail->id;
        } else {
            // pre('empty shipper');
            $newClientData['company_name'] = $input['shipper_name'];
            $newClientData['company_address'] = $input['shipper_address'];
            $newClientData['phone_number'] = $input['shipper_telephone'];
            $newClientData['created_by'] = auth()->user()->id;
            $newClientData['created_at'] = date('Y-m-d h:i:s');
            Clients::Create($newClientData);
            $shipperDetail = DB::table('clients')->where('company_name', $input['shipper_name'])->orderBy('id', 'DESC')->first();
            $input['shipper_name'] = $shipperDetail->id;
        }

        if ($courier_operation_type == 1) {
            if ($oldAgent != $newAgent) {
                $input['display_notification'] = '1';
            }
        }
        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $commission = '0.00';
        if ($input['billing'] == 1) {
            $input['fc'] = 1;
            $input['fd'] = 0;
            $input['pp'] = 0;

            if ($input['package_type'] == 'LTR') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'fc', 'ltr');
            } else if ($input['package_type'] == 'DOC') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'fc', 'doc');
            } else {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'fc', 'pkg', $input['nbr_pcs']);
            }
        } else if ($input['billing'] == 2) {
            $input['fc'] = 0;
            $input['fd'] = 1;
            $input['pp'] = 0;
        } else {
            $input['fc'] = 0;
            $input['fd'] = 0;
            $input['pp'] = 1;

            if ($input['package_type'] == 'LTR') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'pp', 'ltr');
            } else if ($input['package_type'] == 'DOC') {
                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'pp', 'doc');
            } else {

                $commission = $getCommission->freightCommission($fileType, $input['freight'], 'pp', 'pkg', $input['nbr_pcs']);
            }
        }

        $model->update($input);

        if ($courier_operation_type == 1) {
            if ($input['billing'] == 2) {

                $checkFDInvoice = DB::table('invoices')->where('ups_id', $model->id)->where('deleted', '0')->first();

                if (empty($checkFDInvoice)) {
                    $dataInvoice['ups_id'] = $model->id;
                    $dataInvoice['date'] = date('Y-m-d', strtotime($model->created_at));
                    $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                    if (empty($getLastInvoice)) {
                        $dataInvoice['bill_no'] = 'UP-5001';
                    } else {
                        $ab = 'UP-';
                        $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                        $dataInvoice['bill_no'] = $ab;
                    }

                    $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                    $dataConsignee = DB::table('clients')->where('id', $model->consignee_name)->first();
                    $dataShipper = DB::table('clients')->where('id', $model->shipper_name)->first();
                    $dataInvoice['bill_to'] = $dataClient->id;
                    $dataInvoice['date'] = date('Y-m-d');
                    $dataInvoice['email'] = $dataClient->email;
                    $dataInvoice['telephone'] = $dataClient->phone_number;
                    $dataInvoice['shipper'] = $dataShipper->company_name;
                    $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                    $dataInvoice['file_no'] = $model->file_number;
                    $dataInvoice['awb_no'] = $model->awb_number;
                    $dataInvoice['type_flag'] = 'IMPORT';
                    $dataInvoice['weight'] = $model->weight;
                    $dataInvoice['currency'] = '1';
                    $dataInvoice['created_by'] = auth()->user()->id;
                    $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                    $dataInvoices = UpsInvoices::Create($dataInvoice);

                    $allTotal = 0;
                    $dataFdCharges = DB::table('fd_charges')->get();
                    foreach ($dataFdCharges as $key => $value) {
                        $dataBilling = DB::table('billing_items')->where('item_code', $value->code)->first();
                        $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                        $dataInvoiceItems['fees_name'] = $dataBilling->id;
                        $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                        $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                        $dataInvoiceItems['quantity'] = 1;
                        $dataInvoiceItems['unit_price'] = $value->charge;
                        $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                        UpsInvoiceItemDetails::Create($dataInvoiceItems);

                        $allTotal += $dataInvoiceItems['total_of_items'];
                    }

                    $modelUpdateUpsInvoice = UpsInvoices::find($dataInvoices->id);
                    $modelUpdateUpsInvoice->sub_total = $allTotal;
                    $modelUpdateUpsInvoice->total = $allTotal;
                    $modelUpdateUpsInvoice->balance_of = $allTotal;
                    $modelUpdateUpsInvoice->update();

                    $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                    $pdf = PDF::loadView('upsinvoices.printupsinvoice', ['invoice' => (array) $dataAll]);
                    $pdf_file = 'printUpsInvoice_' . $dataInvoices->id . '.pdf';
                    $pdf_path = 'public/upsInvoices/' . $pdf_file;
                    $pdf->save($pdf_path);
                }
            }
        }

        $dataCommission['ups_file_id'] = $id;
        $dataCommission['freight'] = $input['freight'];
        $dataCommission['commission'] = $commission;
        //pre($commission);
        $dataCommission['created_by'] = auth()->user()->id;
        $dataCommission['created_at'] = date('Y-m-d h:i:s');

        $commissionData = DB::table('ups_freight_commission')->where('ups_file_id', $id)->first();

        if (!empty($commissionData)) {
            DB::table('ups_freight_commission')->where('ups_file_id', $id)->update($dataCommission);
        } else {

            upsFreightCommission::Create($dataCommission);
        }

        if ($courier_operation_type == 1) {
            $inputCustom = $input['Custom'];

            $modelCustom = DB::table('customs')->where('ups_details_id', $id)->first();
            if (empty($modelCustom)) {
                $modelCustom = new Customs;
                $modelCustom->file_number = $inputCustom['file_number'];
                $modelCustom->custom_date = !empty($inputCustom['custom_date']) ? date('Y-m-d', strtotime($inputCustom['custom_date'])) : date('Y-m-d');
                $modelCustom->ups_details_id = $id;
                $modelCustom->save();

                $modelActivities = new Activities;
                $modelActivities->type = 'ups';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id = auth()->user()->id;
                $modelActivities->description = "Custom number has been added - <strong>" . $inputCustom['file_number'] . "</strong>";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                $modelActivities = new Activities;
                $modelActivities->type = 'ups';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id = auth()->user()->id;
                $modelActivities->description = "Custom Date has been added <strong>(" . date('d-m-Y', strtotime($inputCustom['custom_date'])) . ")</strong>";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                DB::table('customs')
                    ->where('ups_details_id', $id)
                    ->update(['file_number' => $inputCustom['file_number'], 'custom_date' => !empty($inputCustom['custom_date']) ? date('Y-m-d', strtotime($inputCustom['custom_date'])) : date('Y-m-d')]);

                $oldCustomNumber = $modelCustom->file_number;
                $oldCustomDate = date('Y-m-d', strtotime($modelCustom->custom_date));
                $newCustomNumber = $inputCustom['file_number'];
                $newCustomDate = date('Y-m-d', strtotime($inputCustom['custom_date']));

                // Store deposite activities
                if ($oldCustomNumber != $newCustomNumber) {
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $model->id;
                    $modelActivities->user_id = auth()->user()->id;
                    $modelActivities->description = "Custom number changed from <strong>" . $oldCustomNumber . "</strong> To <strong>" . $newCustomNumber . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
                if ($oldCustomDate != $newCustomDate) {
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $model->id;
                    $modelActivities->user_id = auth()->user()->id;
                    $modelActivities->description = "Custom date changed from <strong>" . date('d-m-Y', strtotime($oldCustomDate)) . "</strong> To <strong>" . date('d-m-Y', strtotime($newCustomDate)) . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }

        // Check Commission invoice has generated or not
        $checkInvoice = DB::table('invoices')->select(DB::raw('group_concat(id) as invoiceIds'))->where('ups_id', $id)->first();
        $invoiceOfCommission = DB::table('invoice_item_details')->whereIn('invoice_id', explode(',', $checkInvoice->invoiceIds))
            ->whereIn('item_code', ['C1071','C1071/ Commission fret aerien (UPS)'])
            ->count();

        if ($invoiceOfCommission == 0 && ($input['billing'] == 1 || $input['billing'] == 3))
            Ups::generateUpsInvoice($model->id);

        Session::flash('flash_message', 'Record has been updated successfully');
        if (checkloggedinuserdata() == 'Agent') {
            return redirect()->route('agentups');
        } else {
            return redirect('ups');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Ups  $ups
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $type_flag = null)
    {
        //pre($id);
        DB::table('ups_details')->where('id', $id)->update(['deleted' => 1, 'deleted_on' => date('Y-m-d h:i:s'), 'deleted_by' => auth()->user()->id]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'ups';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function import()
    {
        $checkPermission = User::checkPermission(['upload_courier_import'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = new Ups;
        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->pluck('name', 'id');
        $warehouses = json_decode($warehouses, 1);
        ksort($warehouses);
        return view('ups.import', ['model' => $model, 'agents' => $agents, 'warehouses' => $warehouses]);
    }
    public function importdata(Request $request)
    {
        $GLOBALS['freightCommission'] = new upsFreightCommission;
        $newClientData = [];
        $storage = $request->get('storage');
        if ($request->get('s3file')) {
            $file = $request->get('s3file');
        }
        /*$validater = $this->validate($request, [
        'import_file' => 'required',
        ]);*/
        // pre($request->file('export_file'));
        if ($_POST['actions'] == 'upload_export_file') {
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];

                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
            } else {
                $inputfile = $request->file('export_file');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' && $inputfile->getClientOriginalExtension() != 'xlsx') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                        return redirect('ups/import');
                    }
                }else{
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                    return redirect('ups/import');
                }
            }
            //pre($success);
            // $headercolumnArr =  Excel::load('storage/app/flySystem.xlsx')->get()->first()->keys()->toArray();
            // $headercolumnArr = Excel::load($inputfile)->get()->first()->keys()->toArray();
            // pre($headercolumnArr);
            //$extension = substr($_FILES['export_file']['name'],-5);
            //$dataExport['file_name'] = chop($_FILES['export_file']['name'],$extension);
            $arrivalDate = !empty($request['arrival_date']) ? date('Y-m-d', strtotime($request['arrival_date'])) : null;
            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            $this->ImportUploadExportFileSub($theArray, $arrivalDate);
            // Excel::load($inputfile, function ($reader) use ($request, $arrivalDate) {
            //     $getCommission = $GLOBALS['freightCommission'];
            //     $commission = 0;
            //     $dataCommission = [];
            //     //pre($reader);
            //     foreach ($reader->toArray() as $key => $row) {
            //         if (empty($row['Tracking'])) {
            //             continue;
            //         }

            //         if ($row['Descrition'] == 'letter' || $row['Descrition'] == 'ltr' || $row['Descrition'] == 'LTR') {
            //             $dataExport['package_type'] = 'LTR';
            //         } else if ($row['Descrition'] == 'document' || $row['Descrition'] == 'doc' || $row['Descrition'] == 'DOC') {
            //             $dataExport['package_type'] = 'DOC';
            //         } else {
            //             $dataExport['package_type'] = 'PKG';
            //         }

            //         $duplicateUpsExport = DB::table('ups_details')->where('awb_number', $row['Tracking'])->where('deleted', 0)->first();

            //         if (empty($duplicateUpsExport)) {
            //             $flag = 'create';
            //         } else {
            //             $flag = 'update';
            //             $duplicateId = $duplicateUpsExport->id;
            //         }
            //         if ($row['Billing'] == 'F/D') {
            //             $dataExport['fc'] = 0;
            //             $dataExport['fd'] = 1;
            //             $dataExport['pp'] = 0;
            //         }

            //         if ($row['Billing'] == 'F/C') {
            //             $dataExport['fc'] = 1;
            //             $dataExport['fd'] = 0;
            //             $dataExport['pp'] = 0;

            //             if ($dataExport['package_type'] == 'LTR') {
            //                 $commission = $getCommission->freightCommission('export', $row['Freight'], 'fc', 'ltr');
            //             } else if ($dataExport['package_type'] == 'DOC') {
            //                 $commission = $getCommission->freightCommission('export', $row['Freight'], 'fc', 'doc');
            //             } else {
            //                 $commission = $getCommission->freightCommission('export', $row['Freight'], 'fc', 'pkg', $row['Package Qty..']);
            //             }
            //         }

            //         if ($row['Billing'] == 'P/P') {
            //             $dataExport['fc'] = 0;
            //             $dataExport['fd'] = 0;
            //             $dataExport['pp'] = 1;

            //             if ($dataExport['package_type'] == 'LTR') {
            //                 $commission = $getCommission->freightCommission('export', $row['Freight'], 'pp', 'ltr');
            //             } else if ($dataExport['package_type'] == 'DOC') {
            //                 $commission = $getCommission->freightCommission('export', $row['Freight'], 'pp', 'doc');
            //             } else {
            //                 $commission = $getCommission->freightCommission('export', $row['Freight'], 'pp', 'pkg', $row['Package Qty..']);
            //             }
            //         }

            //         $dataExport['awb_number'] = $row['Tracking'];
            //         $dataExport['description'] = $row['Descrition'];
            //         $dataExport['shipment_number'] = $row['Shipment No.'];
            //         $dataExport['courier_operation_type'] = 2;

            //         $shipper_name = $row['Shipper'];
            //         $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            //         if (empty($clientData)) {

            //             $newClientData['company_name'] = $shipper_name;
            //             $newClientData['company_address'] = $row['Address 1'] . ',' . $row['Address 2'];
            //             Clients::Create($newClientData);
            //             $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            //         }
            //         $dataExport['shipper_name'] = $clientData->id;
            //         $dataExport['shipper_address'] = $row['Address 1'];
            //         $dataExport['shipper_address_2'] = $row['Address 2'];
            //         $dataExport['shipper_contact'] = $row['Contact'];
            //         $dataExport['shipper_address'] = $row['Address 1'];
            //         $dataExport['shipper_address_2'] = $row['Address 2'];
            //         $dataExport['origin'] = $row['Cnty'];
            //         $dataExport['destination'] = $row['Dest Cnty'];
            //         $dataExport['weight'] = $row['Weight'];
            //         $dataExport['unit'] = $row['Unit'];
            //         $dataExport['dim_weight'] = $row['Dim. Weight'];
            //         $dataExport['dim_unit'] = $row['Unit'];
            //         $dataExport['nbr_pcs'] = $row['Package Qty..'];
            //         $dataExport['freight'] = $row['Freight'];
            //         $dataExport['freight_currency'] = $row['Currency'];
            //         $dataExport['ups_scan_status'] = $row['Status'];
            //         $dataExport['created_on'] = date('Y-m-d h:i:s');
            //         $dataExport['created_by'] = auth()->user()->id;
            //         $importUpsData = DB::table('ups_details')->where('awb_number', $row['Tracking'])->first();

            //         $consignee_name = $row['Consignee'];
            //         $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
            //         if (empty($clientData)) {

            //             $newClientData['company_name'] = $consignee_name;
            //             $newClientData['phone_number'] = $row['Phone'];
            //             $newClientData['company_address'] = $row['Address'];
            //             Clients::Create($newClientData);
            //             $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

            //             $dataExport['consignee_name'] = $clientData->id;
            //             $dataExport['consignee_telephone'] = $row['Phone'];
            //             $dataExport['consignee_address'] = $row['Address'];
            //             $dataExport['consignee_city_state'] = $row['City, State'];
            //         } else {
            //             $dataExport['consignee_name'] = $clientData->id;
            //             $dataExport['consignee_telephone'] = $row['Phone'];
            //             $dataExport['consignee_address'] = $row['Address'];
            //             $dataExport['consignee_city_state'] = $row['City, State'];
            //         }
            //         if ($flag == 'update') {
            //             $dataExport['arrival_date'] = $arrivalDate;
            //             $dataExport['file_number'] = $duplicateUpsExport->file_number;
            //             DB::table('ups_details')->where('id', $duplicateId)->update($dataExport);
            //         } else {
            //             $dataLast = DB::table('ups_details')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
            //             if (empty($dataLast)) {
            //                 $dataExport['file_number'] = 'UPE 1110';
            //             } else {
            //                 $ab = 'UPE ';
            //                 $ab .= substr($dataLast->file_number, 4) + 1;
            //                 $dataExport['file_number'] = $ab;
            //             }

            //             $dataExport['arrival_date'] = $arrivalDate;
            //             $model = Ups::Create($dataExport);
            //             $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
            //             $filePath = $dir;
            //             //pre($filePath);
            //             $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
            //         }

            //         if ($flag == 'update') {
            //             //upsFreightCommission::Update($dataCommission);
            //             $dataCommission['ups_file_id'] = $duplicateUpsExport->id;
            //             $dataCommission['freight'] = $row['Freight'];
            //             $dataCommission['commission'] = $commission;
            //             $dataCommission['created_by'] = auth()->user()->id;
            //             $dataCommission['created_at'] = date('Y-m-d h:i:s');
            //             DB::table('ups_freight_commission')->where('ups_file_id', $duplicateId)->update($dataCommission);

            //             // Check Commission invoice has generated or not
            //             $checkInvoice = DB::table('invoices')->select(DB::raw('group_concat(id) as invoiceIds'))->where('ups_id', $duplicateUpsExport->id)->first();
            //             $invoiceOfCommission = DB::table('invoice_item_details')->whereIn('invoice_id', explode(',', $checkInvoice->invoiceIds))
            //                 ->whereIn('item_code', ['C1071','C1071/ Commission fret aerien (UPS)'])
            //                 ->count();

            //             if ($invoiceOfCommission == 0 && ($duplicateUpsExport->fc == 1 || $duplicateUpsExport->pp == 1))
            //                 Ups::generateUpsInvoice($duplicateUpsExport->id);
            //         } else {

            //             $lastUpsExport = DB::table('ups_details')->where('deleted', 0)->orderBy('id', 'DESC')->first();
            //             $dataCommission['ups_file_id'] = $lastUpsExport->id;
            //             $dataCommission['freight'] = $row['Freight'];
            //             $dataCommission['commission'] = $commission;
            //             $dataCommission['created_by'] = auth()->user()->id;
            //             $dataCommission['created_at'] = date('Y-m-d h:i:s');
            //             upsFreightCommission::Create($dataCommission);

            //             if ($model->fc == 1 || $model->pp == 1)
            //                 Ups::generateUpsInvoice($model->id);
            //         }
            //     }
            // });

            if (isset($success)) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            Session::flash('flash_message', 'Record has been imported successfully.');
            return redirect('ups');
        }

        if ($_POST['actions'] == 'ups_commission_file') {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', 30000);
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];

                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
            } else {
                $inputfile = $request->file('ups_commission_file');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' && $inputfile->getClientOriginalExtension() != 'xlsx') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                        return redirect('ups/import');
                    }
                }else{
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                    return redirect('ups/import');
                }
            }
            $arrHeader = array(
                '0' => 'SHIPMENT ID',
                '1' => 'TRACKING',
                '2' => 'TOT COMMISSION',
            );
            
            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            $arrHeader2[0] = $theArray[0][0];
            $arrHeader2[1] = $theArray[0][1];
            $arrHeader2[2] = $theArray[0][2];
            $diff = array_diff($arrHeader, $arrHeader2);
            
            // $commissionfileheadder = Excel::load($inputfile)->get()->first()->keys()->toArray();
            // $commissionfileheadderC[0] = $commissionfileheadder[0];
            // $commissionfileheadderC[1] = $commissionfileheadder[1];
            // $commissionfileheadderC[2] = $commissionfileheadder[2];
            // $diff = array_diff($arrHeader, $commissionfileheadderC);
            
            if (isset($diff) && count($diff) > 0) {
                Session::flash('flash_message_error', 'Wrong File Header');
                return redirect('ups/import');
            }
            $this->ImportUpsCommissionFileSub($theArray);

            // Excel::load($inputfile, function ($reader) {
                // $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                // foreach ($reader->toArray() as $key => $row) {
                //     $awbNumber = $row['TRACKING'];
                //     $upsFileData = DB::table('ups_details')
                //         ->select('ups_freight_commission.commission', 'ups_freight_commission.id as commissionID', 'ups_details.id as upsID', 'ups_details.pp', 'ups_details.fc', 'ups_details.fd', 'ups_details.commission_amount_approve')
                //         ->leftJoin('ups_freight_commission', 'ups_details.id', '=', 'ups_freight_commission.ups_file_id')
                //         ->where('ups_details.awb_number', $awbNumber)
                //         ->orderBy('ups_details.id', 'DESC')
                //         ->first();

                //     $approvedAmount = $row['TOT COMMISSION'];
                //     $pendingAmount = 0.00;
                //     //pre(!empty($upsFileData));
                //     if (!empty($upsFileData)) {
                //         $id = $upsFileData->upsID;

                //         if (($upsFileData->fc == '1' || $upsFileData->pp == '1') && ($upsFileData->commission_amount_approve == 'N' || empty($upsFileData->commission_amount_approve))) {
                //             if ($approvedAmount == $upsFileData->commission) {
                //                 $pendingAmount = 0.00;
                //                 $model = Ups::find($id);
                //                 $commissionModel = upsFreightCommission::find($upsFileData->commissionID);
                //                 $model->update(['commission_amount_approve' => 'Y']);
                //                 //pre($pendingAmount);
                //                 $commissionModel->update(['pending_commission' => $pendingAmount]);

                //                 Session::flash('flash_message', 'Your commission has been approved.');
                //             } else {
                //                 $model = Ups::find($id);
                //                 $commissionModel = upsFreightCommission::find($upsFileData->commissionID);
                //                 $model->update(['commission_amount_approve' => 'N']);
                //                 if ($upsFileData->fd == '1')
                //                     $pendingAmount = '0.00';
                //                 else
                //                     $pendingAmount = $upsFileData->commission - $approvedAmount;
                //                 //pre($pendingAmount);
                //                 $commissionModel->update(['pending_commission' => $pendingAmount]);
                //                 Session::flash('flash_message', 'Your commission has been approved partially.');
                //             }
                //         }

                //         $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
                //         if (empty($getLastReceiptNumber)) {
                //             $receiptNumber = '11101';
                //         } else {
                //             if (empty($getLastReceiptNumber->receipt_number))
                //                 $receiptNumber = '11101';
                //             else
                //                 $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
                //         }

                //         $dataInvoice = DB::table('ups_details')
                //             ->select('invoices.total', 'invoices.balance_of', 'invoices.credits', 'invoices.id as invoiceID', 'invoices.bill_no', 'invoices.currency', 'ups_details.file_number', 'ups_details.id as upsID')
                //             ->join('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                //             ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                //             ->where('invoices.bill_to', $dataClient->id)
                //             ->whereIn('invoice_item_details.item_code', ['C1071','C1071/ Commission fret aerien (UPS)'])
                //             ->where('ups_details.awb_number', $awbNumber)
                //             ->first();
                //         if (!empty($dataInvoice)) {
                //             $input['invoice_id'] = $dataInvoice->invoiceID;
                //             $input['invoice_number'] = $dataInvoice->bill_no;
                //             $input['ups_id'] = $dataInvoice->upsID;
                //             $input['file_number'] = $dataInvoice->file_number;
                //             $input['amount'] = $approvedAmount;
                //             $input['exchange_amount'] = $approvedAmount;
                //             $input['exchange_rate'] = '0.00';
                //             $input['payment_via'] = 'COMPESATION';
                //             $input['payment_via_note'] = 'Accept by manish patel';
                //             $input['created_at'] = gmdate("Y-m-d H:i:s");
                //             $input['client'] = $dataClient->id;
                //             $input['payment_accepted_by'] = auth()->user()->id;
                //             $input['receipt_number'] = $receiptNumber;
                //             $model = InvoicePayments::create($input);

                //             $dataCurrency = Currency::getData($dataInvoice->currency);
                //             // Store payment received activity on file level
                //             $modelActivities = new Activities;
                //             $modelActivities->type = 'ups';
                //             $modelActivities->related_id = $dataInvoice->upsID;
                //             $modelActivities->user_id   = auth()->user()->id;
                //             $modelActivities->description = 'Invoice #' . $dataInvoice->bill_no . " Payment Received " . number_format($approvedAmount, 2) . " (" . $dataCurrency->code . ")";
                //             $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                //             $modelActivities->save();

                //             if ($approvedAmount == $dataInvoice->balance_of)
                //                 DB::table('invoices')->where('id', $dataInvoice->invoiceID)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00', 'payment_received_on' => date('Y-m-d'), 'payment_received_by' => auth()->user()->id]);
                //             else
                //                 DB::table('invoices')->where('id', $dataInvoice->invoiceID)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->credits + $approvedAmount, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $approvedAmount)]);
                //         }
                //     }
                // }
            // });
            if (isset($success)) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            Session::flash('flash_message', 'File has been uploaded successfully.');
            return redirect('ups');
        }

        if ($_POST['actions'] == 'import_scan') {
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];
                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
                $ext = pathinfo($inputfile, PATHINFO_EXTENSION);
            } else {
                $inputfile = $request->file('import_file_import_scan');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' || $inputfile->getClientOriginalExtension() != 'xlsx' || $inputfile->getClientOriginalExtension() != 'txt') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx OR txt file');
                        return redirect('ups/import');
                    }
                }else{
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx OR txt file');
                    return redirect('ups/import');
                }
                $ext = pathinfo($_FILES['import_file_import_scan']['name'], PATHINFO_EXTENSION);
            }

            if ($ext == 'txt') {

                if ($request->hasFile('import_file_import_scan')) {
                    if ($storage == 1) {
                        $handle = fopen($_FILES['import_file_import_scan']['tmp_name'], "r");
                    } else {
                        $handle = fopen($inputfile, "r");
                    }

                    $linecount = 0;
                    while (!feof($handle)) {
                        $line = fgets($handle);
                        if ($line != "") {
                            $linecount++;
                        }
                    }
                    //pre($handle);

                    if ($storage == 1) {
                        $handle = fopen($_FILES['import_file_import_scan']['tmp_name'], "r");
                    } else {
                        $handle = fopen($inputfile, "r");
                    }
                    while (($line = fgets($handle)) !== false) {
                        $Awb = substr($line, 33, 18);
                        $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $Awb)->first();
                        if (!empty($dataUps)) {
                            $oldStatus = $dataUps->ups_scan_status;
                        } else {
                            $oldStatus = "1";
                        }

                        DB::table('ups_details')
                            ->where('awb_number', $Awb)
                            ->update(['ups_scan_status' => '3', 'warehouse' => 5, 'inprogress_scan_status' => 1]);

                        $dataUps = DB::table('ups_details')->where('awb_number', $Awb)->where('courier_operation_type', 1)->first();
                        if (!empty($dataUps)) {
                            $newStatus = $dataUps->ups_scan_status;
                            if ($oldStatus != $newStatus) {
                                if (empty($oldStatus))
                                    $oldStatus = '1';
                                $modelActivities = new Activities;
                                $modelActivities->type = 'ups';
                                $modelActivities->related_id = $dataUps->id;
                                $modelActivities->user_id = auth()->user()->id;
                                $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                                $modelActivities->save();
                            }
                        }
                    }
                }
                Session::flash('flash_message', 'Status has been changed to import scan.');
                if (isset($success)) {
                    $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
                }
                return redirect('ups');
            } else if ($ext == 'xls' || $ext == 'xlsx') {
                $theArray = Excel::toArray(new stdClass(), $inputfile);
                $theArray = $theArray[0];
                if (count($theArray[0]) != 1 || $theArray[0][0] != 'Tracking') {
                    Session::flash('flash_message_error', 'Wrong file format !');
                    return redirect(url('ups/import'));
                }
                $this->ImportScanSub($theArray);
                // $header = Excel::load($inputfile)->get()->first()->keys()->toArray();
                // if (count($header) != 1 || $header[0] != 'Tracking') {
                //     Session::flash('flash_message_error', 'Wrong file format !');
                //     return redirect(url('ups/import'));
                // }
                // Excel::load($inputfile, function ($reader) {
                //     foreach ($reader->toArray() as $key => $row) {
                //         $awbNumber = $row['Tracking'];

                //         $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
                //         if (!empty($dataUps)) {
                //             $oldStatus = $dataUps->ups_scan_status;
                //         } else {
                //             $oldStatus = "1";
                //         }

                //         DB::table('ups_details')
                //             ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                //             ->update(['ups_scan_status' => '3', 'warehouse' => 5, 'inprogress_scan_status' => 1]);
                //         $dataUps = DB::table('ups_details')->where('awb_number', $awbNumber)->where('courier_operation_type', 1)->first();
                //         if (!empty($dataUps)) {

                //             $newStatus = $dataUps->ups_scan_status;
                //             if ($oldStatus != $newStatus) {
                //                 if (empty($oldStatus))
                //                     $oldStatus = '1';
                //                 $modelActivities = new Activities;
                //                 $modelActivities->type = 'ups';
                //                 $modelActivities->related_id = $dataUps->id;
                //                 $modelActivities->user_id = auth()->user()->id;
                //                 $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                //                 $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                //                 $modelActivities->save();
                //             }
                //         }
                //     }
                // });
                Session::flash('flash_message', 'Status has been changed to import scan.');
                if (isset($success)) {
                    $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
                }
                return redirect('ups');
            } else {
            }
        }

        if ($_POST['actions'] == 'warehouse_scan') {
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];
                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
                $ext = pathinfo($inputfile, PATHINFO_EXTENSION);
            } else {
                $inputfile = $request->file('import_file_warehouse_scan');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' || $inputfile->getClientOriginalExtension() != 'xlsx' || $inputfile->getClientOriginalExtension() != 'txt') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx OR txt file');
                        return redirect('ups/import');
                    }
                }else{
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx OR txt file');
                    return redirect('ups/import');
                }
                $ext = pathinfo($_FILES['import_file_warehouse_scan']['name'], PATHINFO_EXTENSION);
            }

            //pre($_FILES['import_file_warehouse_scan']['name']);
            if ($ext == 'txt') {

                if ($request->hasFile('import_file_warehouse_scan')) {
                    if ($storage == 1) {
                        $handle = fopen($_FILES['import_file_warehouse_scan']['tmp_name'], "r");
                    } else {
                        $handle = fopen($inputfile, "r");
                    }

                    $line = '';
                    $linecount = 0;
                    while (!feof($handle)) {
                        $line = fgets($handle);

                        if ($line != "") {
                            $linecount++;
                        }
                    }

                    if ($storage == 1) {
                        $handle = fopen($_FILES['import_file_warehouse_scan']['tmp_name'], "r");
                    } else {
                        $handle = fopen($inputfile, "r");
                    }

                    while (($line = fgets($handle)) !== false) {

                        $Awb = substr($line, 14, 18);
                        $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $Awb)->first();
                        if (!empty($dataUps)) {
                            $oldStatus = $dataUps->ups_scan_status;
                        } else {
                            $oldStatus = "1";
                        }

                        DB::table('ups_details')
                            ->where('awb_number', $Awb)->where('courier_operation_type', 1)
                            ->update(['ups_scan_status' => '4', 'warehouse' => $_POST['warehouse'], 'inprogress_scan_status' => 3]);

                        $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $Awb)->first();
                        if (!empty($dataUps)) {
                            $newStatus = $dataUps->ups_scan_status;
                            if ($oldStatus != $newStatus) {
                                if (empty($oldStatus))
                                    $oldStatus = '1';
                                $modelActivities = new Activities;
                                $modelActivities->type = 'ups';
                                $modelActivities->related_id = $dataUps->id;
                                $modelActivities->user_id = auth()->user()->id;
                                $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                                $modelActivities->save();
                            }
                        }
                    }
                }
                Session::flash('flash_message', 'Status has been changed to warehouse scan.');
                if (isset($success)) {
                    $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
                }
                return redirect('ups');
            } else if ($ext == 'xls' || $ext == 'xlsx') {
                $theArray = Excel::toArray(new stdClass(), $inputfile);
                $theArray = $theArray[0];
                if (count($theArray[0]) != 1 || $theArray[0][0] != 'Tracking') {
                    Session::flash('flash_message_error', 'Wrong file format !');
                    return redirect(url('ups/import'));
                }
                $this->ImportWarehouseScanSub($theArray);

                // $header = Excel::load($inputfile)->get()->first()->keys()->toArray();
                // if (count($header) != 1 || $header[0] != 'Tracking') {
                //     Session::flash('flash_message_error', 'Wrong file format !');
                //     return redirect(url('ups/import'));
                // }
                // Excel::load($inputfile, function ($reader) {
                //     foreach ($reader->toArray() as $key => $row) {
                //         $awbNumber = $row['Tracking'];

                //         $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
                //         if (!empty($dataUps)) {
                //             $oldStatus = $dataUps->ups_scan_status;
                //         } else {
                //             $oldStatus = "1";
                //         }

                //         DB::table('ups_details')
                //             ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                //             ->update(['ups_scan_status' => '4', 'warehouse' => $_POST['warehouse'], 'inprogress_scan_status' => 3]);
                //         $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
                //         if (!empty($dataUps)) {
                //             $newStatus = $dataUps->ups_scan_status;
                //             if ($oldStatus != $newStatus) {
                //                 if (empty($oldStatus))
                //                     $oldStatus = '1';
                //                 $modelActivities = new Activities;
                //                 $modelActivities->type = 'ups';
                //                 $modelActivities->related_id = $dataUps->id;
                //                 $modelActivities->user_id = auth()->user()->id;
                //                 $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                //                 $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                //                 $modelActivities->save();
                //             }
                //         }
                //     }
                // });
                Session::flash('flash_message', 'Status has been changed to warehouse scan.');
                if (isset($success)) {
                    $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
                }
                return redirect('ups');
            } else {
            }
        }

        if ($_POST['actions'] == 'physical_scan') {
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];

                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
            } else {
                $inputfile = $request->file('physical_scan_file');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' && $inputfile->getClientOriginalExtension() != 'xlsx') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                        return redirect('ups/import');
                    }
                }else{
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                    return redirect('ups/import');
                }
            }
            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            if (count($theArray[0]) != 1 || $theArray[0][0] != 'Tracking') {
                Session::flash('flash_message_error', 'Wrong file format !');
                return redirect(url('ups/import'));
            }
            $this->ImportPhysicalScanSub($theArray);

            // $header = Excel::load($inputfile)->get()->first()->keys()->toArray();
            // if (count($header) != 1 || $header[0] != 'Tracking') {
            //     Session::flash('flash_message_error', 'Wrong file format !');
            //     return redirect(url('ups/import'));
            // }
            // Excel::load($inputfile, function ($reader) {
            //     foreach ($reader->toArray() as $key => $row) {
            //         $awbNumber = $row['Tracking'];

            //         $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            //         if (!empty($dataUps)) {
            //             $oldStatus = $dataUps->ups_scan_status;
            //         } else {
            //             $oldStatus = "1";
            //         }

            //         DB::table('ups_details')
            //             ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
            //             ->update(['ups_scan_status' => '5', 'inprogress_scan_status' => 2]);
            //         $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
            //         if (!empty($dataUps)) {
            //             $newStatus = $dataUps->ups_scan_status;
            //             if ($oldStatus != $newStatus) {
            //                 if (empty($oldStatus))
            //                     $oldStatus = '1';
            //                 $modelActivities = new Activities;
            //                 $modelActivities->type = 'ups';
            //                 $modelActivities->related_id = $dataUps->id;
            //                 $modelActivities->user_id = auth()->user()->id;
            //                 $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
            //                 $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            //                 $modelActivities->save();
            //             }
            //         }
            //     }
            // });
            Session::flash('flash_message', 'Status has been changed to physical scan.');
            if (isset($success)) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            return redirect('ups');
        }

        if ($_POST['actions'] == 'delivery_scan') {

            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];

                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
            } else {
                $inputfile = $request->file('delivery_scan_file');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' && $inputfile->getClientOriginalExtension() != 'xlsx') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                        return redirect('ups/import');
                    }
                }else{
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                    return redirect('ups/import');
                }
            }
            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            if (count($theArray[0]) != 1 || $theArray[0][0] != 'Tracking') {
                Session::flash('flash_message_error', 'Wrong file format !');
                return redirect(url('ups/import'));
            }
            $this->ImportDeliveryScanSub($theArray);

            // $header = Excel::load($inputfile)->get()->first()->keys()->toArray();
            // if (count($header) != 1 || $header[0] != 'Tracking') {
            //     Session::flash('flash_message_error', 'Wrong file format !');
            //     return redirect(url('ups/import'));
            // }
            // Excel::load($inputfile, function ($reader) {
            //     foreach ($reader->toArray() as $key => $row) {
            //         $awbNumber = $row['Tracking'];

            //         $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            //         if (!empty($dataUps)) {
            //             $oldStatus = $dataUps->ups_scan_status;
            //         } else {
            //             $oldStatus = "1";
            //         }

            //         DB::table('ups_details')
            //             ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
            //             ->update(['ups_scan_status' => '6', 'inprogress_scan_status' => 4]);
            //         $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
            //         if (!empty($dataUps)) {
            //             $newStatus = $dataUps->ups_scan_status;
            //             if ($oldStatus != $newStatus) {
            //                 if (empty($oldStatus))
            //                     $oldStatus = '1';
            //                 $modelActivities = new Activities;
            //                 $modelActivities->type = 'ups';
            //                 $modelActivities->related_id = $dataUps->id;
            //                 $modelActivities->user_id = auth()->user()->id;
            //                 $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
            //                 $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            //                 $modelActivities->save();
            //             }
            //         }
            //     }
            // });

            Session::flash('flash_message', 'Status has been changed to delivery scan.');
            if (isset($success)) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            return redirect('ups');
        } else {
            if (empty($_FILES['import_file']['name']) && empty($request->get('s3file'))) {
                Session::flash('flash_message_error', 'Please choose file');
                return redirect('ups/import');
            }

            Ups::where('last_action_flag', 1)->update(array('last_action_flag' => 0));
            $dataArray = array();
            $GLOBALS['freightCommission'] = new upsFreightCommission;
            $dataPackageArray = array();
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];

                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
                $handle = fopen($inputfile, "r");
            } else {
                $inputfile = $request->file('import_file');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/zlib','text/plain','application/octet-stream');
                if(!in_array($fileMimeType,$allowMimeTypes))
                {
                    Session::flash('flash_message_error', 'Please select correct file');
                    return redirect('ups/import');
                }
                $handle = fopen($_FILES['import_file']['tmp_name'], "r");
            }

            $totalRecord = substr_count(file_get_contents($_FILES['import_file']['tmp_name']), "200000");
            if ($request->hasFile('import_file')) {

                $linecount = 0;
                while (!feof($handle)) {
                    $line = fgets($handle);
                    if ($line != "") {
                        $linecount++;
                    }
                }
                $i = 0;
                $j = 1;
                $totalR = 1;
                $add = 0;
                $update = 0;
                $commission = 0;
                $dataArray['last_action'] = '';
                if ($storage == 1) {
                    $handle = fopen($_FILES['import_file']['tmp_name'], "r");
                } else {
                    $handle = fopen($inputfile, "r");
                }

                while (($line = fgets($handle)) !== false) {
                    //pre('line--'.$linecount,1);
                    //pre('j--'.$j,1);
                    $recordType = substr($line, 44, 6);
                    if ($recordType == 200000) {
                        if ($i == 1) {
                            $totalR++;
                            if ($dataArray['last_action'] == 'updated') {
                                $model = Ups::find($dataArray['last_action_updated_id']);
                                $model->fill($dataArray);
                                Activities::log('update', 'ups', $model);
                                $model->update($dataArray);

                                $dataCommission['ups_file_id'] = $model->id;
                                $dataCommission['freight'] = $model->freight;
                                $dataCommission['commission'] = $commission;
                                $dataCommission['created_by'] = auth()->user()->id;
                                $dataCommission['created_at'] = date('Y-m-d h:i:s');
                                $checkCommissionFile = DB::table('ups_freight_commission')->where('ups_file_id', $model->id)->count();

                                if ($checkCommissionFile > 0) {
                                    DB::table('ups_freight_commission')->where('ups_file_id', $model->id)->update($dataCommission);
                                } else {
                                    upsFreightCommission::Create($dataCommission);
                                }

                                // Check Commission invoice has generated or not
                                $checkInvoice = DB::table('invoices')->select(DB::raw('group_concat(id) as invoiceIds'))->where('ups_id', $model->id)->first();
                                $invoiceOfCommission = DB::table('invoice_item_details')->whereIn('invoice_id', explode(',', $checkInvoice->invoiceIds))
                                    ->whereIn('item_code', ['C1071','C1071/ Commission fret aerien (UPS)'])
                                    ->count();

                                if ($invoiceOfCommission == 0 && ($model->fc == 1 || $model->pp == 1))
                                    Ups::generateUpsInvoice($model->id);
                            } else {
                                $model = Ups::create($dataArray);
                                $dataCommission['ups_file_id'] = $model->id;
                                $dataCommission['freight'] = $model->freight;
                                $dataCommission['commission'] = $commission;
                                $dataCommission['created_by'] = auth()->user()->id;
                                $dataCommission['created_at'] = date('Y-m-d h:i:s');
                                $checkCommissionFile = DB::table('ups_freight_commission')->where('ups_file_id', $model->id)->count();

                                if ($checkCommissionFile > 0) {
                                    DB::table('ups_freight_commission')->where('ups_file_id', $model->id)->update($dataCommission);
                                } else {
                                    upsFreightCommission::Create($dataCommission);
                                }

                                if ($model->courier_operation_type == 1) {
                                    if ($model->fd == 1) {
                                        $dataInvoice['ups_id'] = $model->id;
                                        $dataInvoice['date'] = date('Y-m-d', strtotime($model->created_at));
                                        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                                        if (empty($getLastInvoice)) {
                                            $dataInvoice['bill_no'] = 'UP-5001';
                                        } else {
                                            $ab = 'UP-';
                                            $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                                            $dataInvoice['bill_no'] = $ab;
                                        }

                                        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                                        $dataConsignee = DB::table('clients')->where('id', $model->consignee_name)->first();
                                        $dataShipper = DB::table('clients')->where('id', $model->shipper_name)->first();
                                        $dataInvoice['bill_to'] = $dataClient->id;
                                        $dataInvoice['date'] = date('Y-m-d');
                                        $dataInvoice['email'] = $dataClient->email;
                                        $dataInvoice['telephone'] = $dataClient->phone_number;
                                        $dataInvoice['shipper'] = $dataShipper->company_name;
                                        $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                                        $dataInvoice['file_no'] = $model->file_number;
                                        $dataInvoice['awb_no'] = $model->awb_number;
                                        $dataInvoice['type_flag'] = 'IMPORT';
                                        $dataInvoice['weight'] = $model->weight;
                                        $dataInvoice['currency'] = '1';
                                        $dataInvoice['created_by'] = auth()->user()->id;
                                        $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                                        $dataInvoices = UpsInvoices::Create($dataInvoice);

                                        $allTotal = 0;
                                        $dataFdCharges = DB::table('fd_charges')->get();
                                        foreach ($dataFdCharges as $key => $value) {
                                            $dataBilling = DB::table('billing_items')->where('item_code', $value->code)->first();
                                            if (!empty($dataBilling)) {
                                                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                                                $dataInvoiceItems['fees_name'] = $dataBilling->id;
                                                $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                                                $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                                                $dataInvoiceItems['quantity'] = 1;
                                                $dataInvoiceItems['unit_price'] = $value->charge;
                                                $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                                                UpsInvoiceItemDetails::Create($dataInvoiceItems);

                                                $allTotal += $dataInvoiceItems['total_of_items'];
                                            }
                                        }

                                        $modelUpdateUpsInvoice = UpsInvoices::find($dataInvoices->id);
                                        $modelUpdateUpsInvoice->sub_total = $allTotal;
                                        $modelUpdateUpsInvoice->total = $allTotal;
                                        $modelUpdateUpsInvoice->balance_of = $allTotal;
                                        $modelUpdateUpsInvoice->update();

                                        $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                                        $pdf = PDF::loadView('upsinvoices.printupsinvoice', ['invoice' => (array) $dataAll]);
                                        $pdf_file = 'printUpsInvoice_' . $dataInvoices->id . '.pdf';
                                        $pdf_path = 'public/upsInvoices/' . $pdf_file;
                                        $pdf->save($pdf_path);
                                    }
                                }

                                Activities::log('create', 'ups', $model);
                                if ($model->fc == 1 || $model->pp == 1)
                                    Ups::generateUpsInvoice($model->id);
                            }
                            if (!empty($dataPackageArray)) {
                                $dataPackageArray['ups_details_id'] = $model->id;
                                $modelPackage = Upspackages::create($dataPackageArray);
                            }
                        }
                        $dataArray = array();
                        $dataPackageArray = array();
                        $dataArray['record_type'] = $recordType;
                        $dataArray['company'] = 'UPS';
                        $dataArray['courier_operation_type'] = '1';
                        $dataArray['tdate'] = date('Y-m-d', strtotime(trim(substr($line, 191, 9))));
                        $dataArray['no_manifeste'] = trim(substr($line, 50, 11));
                        $dataArray['shipment_number'] = trim(substr($line, 50, 11));
                        $dataArray['destination'] = trim(substr($line, 6, 2));
                        $dataArray['origin'] = trim(substr($line, 0, 2));
                        $dataArray['nbr_pcs'] = trim(substr($line, 74, 2));
                        $dataArray['agent_id'] = $_POST['agent_id'];
                        //$dataArray['ups_scan_status'] = 2;
                        $dataArray['file_name'] = $_POST['file_name'];

                        if (trim(substr($line, 81, 3)) == 'LBS') {
                            $dWeight = trim(substr($line, 76, 5));
                            $dataArray['weight'] = number_format($dWeight / 2.2, 2);
                        } else {
                            $dataArray['weight'] = !empty(trim(substr($line, 76, 5))) ? number_format(trim(substr($line, 76, 5)) / 10, 2) : '0.0000';
                        }

                        $invoiceTotal = trim(substr($line, 165, 10));
                        $feightCharges = trim(substr($line, 153, 9));
                        if (trim(substr($line, 175, 3)) == 'USD') {
                            if(is_numeric($invoiceTotal))
                            $dataArray['declared_value'] = '$' . number_format($invoiceTotal / 100, 2);
                            else
                            $dataArray['declared_value'] = '$0.00';
                        } else {
                            $dataArray['declared_value'] = '$0.00';
                        }

                        /* if(trim(substr($line,162,3)) == 'USD')
                        {
                        //$dataArray['freight'] = '$'.number_format($feightCharges/100,2);
                        $dataArray['freight'] = number_format($feightCharges/100,2);
                        }else
                        {
                        //$dataArray['freight'] = '$0.00';
                        $dataArray['freight'] = '0.00';
                        } */
                        $dataArray['freight'] = $feightCharges;

                        $dataArray['Insurance'] = '$0.00';
                        $dataArray['customs_value'] = '0.00';
                        $dataArray['us_fees'] = '0.00';
                        $dataArray['htg_fees'] = '0.00';

                        $fileType = '';
                        if (trim(substr($line, 318, 3)) == 'F/C') {
                            $dataArray['fc'] = 1;
                            $dataArray['fd'] = 0;
                            $dataArray['pp'] = 0;
                            $fileType = 'fc';
                        }
                        if (trim(substr($line, 318, 3)) == 'F/D') {
                            $dataArray['fc'] = 0;
                            $dataArray['fd'] = 1;
                            $dataArray['pp'] = 0;
                            $fileType = 'fd';
                        }
                        if (trim(substr($line, 318, 3)) == 'P/P') {
                            $dataArray['fc'] = 0;
                            $dataArray['fd'] = 0;
                            $dataArray['pp'] = 1;
                            $fileType = 'pp';
                        }

                        $getCommission = $GLOBALS['freightCommission'];
                        $dataArray['package_type'] = trim(substr($line, 72, 1));
                        if ($fileType == 'fc') {
                            if ($dataArray['package_type'] == 'L') {
                                $dataArray['package_type'] = 'LTR';
                                $commission = $getCommission->freightCommission('import', $dataArray['freight'], 'fc', 'ltr');
                            } else if ($dataArray['package_type'] == 'D') {
                                $dataArray['package_type'] = 'DOC';
                                $commission = $getCommission->freightCommission('import', $dataArray['freight'], 'fc', 'doc');
                            } else if ($dataArray['package_type'] == 'N' || $dataArray['package_type'] == 'P') {
                                $dataArray['package_type'] = 'PKG';
                                $commission = $getCommission->freightCommission('import', $dataArray['freight'], 'fc', 'pkg', $dataArray['nbr_pcs']);
                            } else {
                                $commission = 0;
                            }
                        }
                        if ($fileType == 'pp') {
                            if ($dataArray['package_type'] == 'L') {
                                $dataArray['package_type'] = 'LTR';
                                $commission = $getCommission->freightCommission('import', $dataArray['freight'], 'pp', 'ltr');
                            } else if ($dataArray['package_type'] == 'D') {
                                $dataArray['package_type'] = 'DOC';
                                $commission = $getCommission->freightCommission('import', $dataArray['freight'], 'pp', 'doc');
                            } else if ($dataArray['package_type'] == 'N' || $dataArray['package_type'] == 'P') {
                                $dataArray['package_type'] = 'PKG';
                                $commission = $getCommission->freightCommission('import', $dataArray['freight'], 'pp', 'pkg', $dataArray['nbr_pcs']);
                            } else {
                                $commission = 0;
                            }
                        }
                        $i = 1;
                    }
                    /*if($recordType == 201000)
                    {
                    $dataArray['freight'] = number_format((float)trim(substr($line,50,35)),2);
                    }*/
                    if ($recordType == 202000) {
                        $dataArray['tdate'] = date('Y-m-d', strtotime(trim(substr($line, 234, 10))));
                        $dataArray['arrival'] = 'UPS' . date('y-m-d', strtotime(trim(substr($line, 234, 10))));
                        $dataArray['awb_number'] = trim(substr($line, 50, 35));
                        $checkAwbFlag = Ups::checkAwbExist($dataArray['awb_number']);

                        if ($checkAwbFlag != 0) {
                            $dataArray['last_action'] = 'updated';
                            $dataArray['last_action_updated_id'] = $checkAwbFlag;
                            $dataArray['last_action_flag'] = 1;
                            $update++;
                            $j++;
                            continue;
                        } else {
                            $dataArray['last_action'] = 'added';
                            $dataArray['last_action_flag'] = 1;
                            $add++;

                            $dataLast = DB::table('ups_details')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
                            $ab = 'I';
                            if (empty($dataLast)) {
                                $dataArray['file_number'] = 'UPI 1110';
                            } else {
                                $ab = 'UPI ';
                                $ab .= substr($dataLast->file_number, 4) + 1;
                                $dataArray['file_number'] = $ab;
                            }
                        }
                        $dataArray['arrival_date'] = date('Y-m-d', strtotime(trim(substr($line, 234, 10))));
                        $dataArray['shipment_received_date'] = !empty($dataArray['arrival_date']) ? date('Y-m-d', strtotime($dataArray['arrival_date'])) : null;
                        $dataArray['shipment_status'] = '1';
                        $dataArray['shipment_status_changed_by'] = auth()->user()->id;
                    }
                    if ($recordType == 300000) {
                        $shipper_name = preg_replace("/[^a-zA-Z0-9\s]/", "", trim(trim(substr($line, 68, 35))));
                        $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
                        if (empty($clientData)) {

                            $newClientData['company_name'] = $shipper_name;
                            $newClientData['phone_number'] = trim(substr($line, 242, 14));
                            $newClientData['company_address'] = trim(trim(substr($line, 103, 35)) . '  ' . mb_strtolower(trim(substr($line, 138, 35))) . '  ' . trim(substr($line, 208, 20)) . '  ' . trim(substr($line, 173, 35)) . '  ' . trim(substr($line, 228, 2)) . '  ' . trim(substr($line, 230, 9)) . '  ' . trim(substr($line, 239, 3)));
                            Clients::Create($newClientData);
                            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
                        }

                        //$dataArray['shipper_name'] = trim(trim(substr($line,68,35)).' / '.trim(substr($line,367,11)));
                        $dataArray['shipper_name'] = $clientData->id;
                        $dataArray['shipper_address'] = trim(trim(substr($line, 103, 35)) . '  ' . mb_strtolower(trim(substr($line, 138, 35))) . '  ' . trim(substr($line, 208, 20)) . '  ' . trim(substr($line, 173, 35)) . '  ' . trim(substr($line, 228, 2)) . '  ' . trim(substr($line, 230, 9)) . '  ' . trim(substr($line, 239, 3)));
                        $dataArray['shipper_telephone'] = trim(substr($line, 242, 14));
                        $dataArray['shipper_city'] = trim(substr($line, 173, 35));
                    }
                    if ($recordType == 400000) {
                        $dataArray['consignee_name'] = preg_replace("/[^a-zA-Z0-9\s]/", "", trim(substr($line, 68, 35)));
                        $dataArray['consignee_contact_name'] = !empty(trim(substr($line, 103, 25))) ? trim(substr($line, 103, 25)) : trim(substr($line, 68, 35));
                        $dataArray['consignee_address'] = trim(trim(substr($line, 128, 35)) . '  ' . trim(substr($line, 163, 35)) . '  ' . trim(substr($line, 233, 20)) . '  ' . trim(substr($line, 198, 35)) . '  ' . trim(substr($line, 253, 2)) . '  ' . trim(substr($line, 255, 9)) . '  ' . trim(substr($line, 264, 3)));
                        $dataArray['consignee_telephone'] = trim(substr($line, 267, 14));

                        $clientData = DB::table('clients')->where('company_name', $dataArray['consignee_name'])->first();
                        if (empty($clientData)) {

                            $newClientData['company_name'] = $dataArray['consignee_name'];
                            $newClientData['phone_number'] = $dataArray['consignee_telephone'];
                            $newClientData['company_address'] = $dataArray['consignee_address'];
                            Clients::Create($newClientData);
                            $clientData = DB::table('clients')->where('company_name', $dataArray['consignee_name'])->first();

                            $dataArray['consignee_name'] = $clientData->id;
                            $dataArray['consignee_contact_name'] = !empty(trim(substr($line, 103, 25))) ? trim(substr($line, 103, 25)) : trim(substr($line, 68, 35));
                            $dataArray['consignee_address'] = trim(trim(substr($line, 128, 35)) . '  ' . trim(substr($line, 163, 35)) . '  ' . trim(substr($line, 233, 20)) . '  ' . trim(substr($line, 198, 35)) . '  ' . trim(substr($line, 253, 2)) . '  ' . trim(substr($line, 255, 9)) . '  ' . trim(substr($line, 264, 3)));
                            $dataArray['consignee_telephone'] = trim(substr($line, 267, 14));
                        } else {
                            $dataArray['consignee_name'] = $clientData->id;
                            $dataArray['consignee_contact_name'] = !empty(trim(substr($line, 103, 25))) ? trim(substr($line, 103, 25)) : trim(substr($line, 68, 35));
                            $dataArray['consignee_address'] = trim(trim(substr($line, 128, 35)) . '  ' . trim(substr($line, 163, 35)) . '  ' . trim(substr($line, 233, 20)) . '  ' . trim(substr($line, 198, 35)) . '  ' . trim(substr($line, 253, 2)) . '  ' . trim(substr($line, 255, 9)) . '  ' . trim(substr($line, 264, 3)));
                            $dataArray['consignee_telephone'] = trim(substr($line, 267, 14));
                        }
                    }
                    if ($recordType == 401000) {
                        $dataArray['no_manifeste'] = trim(substr($line, 295, 11));
                    }
                    if ($recordType >= 600000 && $recordType <= 699997) {
                        $dataPackageArray['record_type'] = $recordType;
                        $dataPackageArray['shipment_number'] = trim(substr($line, 50, 11));
                        $dataPackageArray['package_weight'] = trim(substr($line, 65, 3));
                        $dataPackageArray['package_weight_unit'] = trim(substr($line, 68, 3));
                        $dataPackageArray['currency_code_package_revenue'] = trim(substr($line, 80, 3));
                        $dataPackageArray['currency_code_insurance_charges'] = trim(substr($line, 92, 2));
                        $dataPackageArray['currency_code_register_charges'] = trim(substr($line, 105, 3));
                        $dataPackageArray['inbound_container_number'] = trim(substr($line, 137, 11));
                        $dataPackageArray['incomplete_shipping_flag'] = trim(substr($line, 164, 1));
                        $dataPackageArray['container_flag'] = trim(substr($line, 165, 1));
                        $dataPackageArray['package_tracking_number'] = trim(substr($line, 166, 35));
                        $dataPackageArray['package_load'] = trim(substr($line, 208, 12));
                    }
                    if ($recordType >= 500000 && $recordType <= 511000) {
                        /* $getCommission = $GLOBALS['freightCommission'];
                        $dataArray['package_type'] = trim(substr($line,54,3));
                        if($fileType == 'fc'){
                        if($dataArray['package_type'] == 'LTR'){
                        $commission = $getCommission->freightCommission('import',$dataArray['freight'],'fc','ltr');
                        } else if($dataArray['package_type'] == 'DOC'){
                        $commission = $getCommission->freightCommission('import',$dataArray['freight'],'fc','doc');
                        } else if($dataArray['package_type'] == 'PKG'){
                        $commission = $getCommission->freightCommission('import',$dataArray['freight'],'fc','pkg',$dataArray['nbr_pcs']);
                        } else {
                        $commission = 0;
                        }
                        }
                        if($fileType == 'pp'){
                        if($dataArray['package_type'] == 'LTR'){
                        $commission = $getCommission->freightCommission('import',$dataArray['freight'],'pp','ltr');
                        } else if($dataArray['package_type'] == 'DOC'){
                        $commission = $getCommission->freightCommission('import',$dataArray['freight'],'pp','doc');
                        } else if($dataArray['package_type'] == 'PKG'){
                        $commission = $getCommission->freightCommission('import',$dataArray['freight'],'pp','pkg');
                        } else {
                        $commission = 0;
                        }
                        } */

                        //pre($fileType.'----'.$dataArray['package_type'].'----'.$dataArray['freight'].'---'.$feightCharges,1);

                    }
                    if ($linecount == $j) {
                        if ($dataArray['last_action'] == 'updated') {
                            $model = Ups::find($dataArray['last_action_updated_id']);
                            $model->fill($dataArray);
                            Activities::log('update', 'ups', $model);
                            $model->update($dataArray);
                        } else {
                            $model = Ups::create($dataArray);
                            $dir = 'Files/Courier/Ups/Import/' . $model->file_number;
                            $filePath = $dir;
                            //pre($filePath);
                            $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
                            Activities::log('create', 'ups', $model);
                        }
                        //pre($model);
                        if (!empty($dataPackageArray)) {
                            $dataPackageArray['ups_details_id'] = $model->id;
                            $modelPackage = Upspackages::create($dataPackageArray);
                        }
                    }
                    $j++;
                }
                fclose($handle);
            }

            $flashMessage['totalUploaded'] = 'Total number of uploaded records : ' . $totalR;
            $flashMessage['totalAdded'] = 'Total number of added records : ' . $add;
            $flashMessage['totalUpdated'] = 'Total number of updated records : ' . $update;
            Session::flash('flash_message_import', $flashMessage);
            return redirect('ups');
        }
    }

    public function ImportUploadExportFileSub($dData, $arrivalDate){
        $dData = arrayKeyValueFlip($dData);
        $getCommission = $GLOBALS['freightCommission'];
        $commission = 0;
        $dataCommission = [];
        //pre($reader);
        foreach ($dData as $key => $row) {
            if (empty($row['Tracking'])) {
                continue;
            }

            if ($row['Descrition'] == 'letter' || $row['Descrition'] == 'ltr' || $row['Descrition'] == 'LTR') {
                $dataExport['package_type'] = 'LTR';
            } else if ($row['Descrition'] == 'document' || $row['Descrition'] == 'doc' || $row['Descrition'] == 'DOC') {
                $dataExport['package_type'] = 'DOC';
            } else {
                $dataExport['package_type'] = 'PKG';
            }

            $duplicateUpsExport = DB::table('ups_details')->where('awb_number', $row['Tracking'])->where('deleted', 0)->first();

            if (empty($duplicateUpsExport)) {
                $flag = 'create';
            } else {
                $flag = 'update';
                $duplicateId = $duplicateUpsExport->id;
            }
            if ($row['Billing'] == 'F/D') {
                $dataExport['fc'] = 0;
                $dataExport['fd'] = 1;
                $dataExport['pp'] = 0;
            }

            if ($row['Billing'] == 'F/C') {
                $dataExport['fc'] = 1;
                $dataExport['fd'] = 0;
                $dataExport['pp'] = 0;

                if ($dataExport['package_type'] == 'LTR') {
                    $commission = $getCommission->freightCommission('export', $row['Freight'], 'fc', 'ltr');
                } else if ($dataExport['package_type'] == 'DOC') {
                    $commission = $getCommission->freightCommission('export', $row['Freight'], 'fc', 'doc');
                } else {
                    $commission = $getCommission->freightCommission('export', $row['Freight'], 'fc', 'pkg', $row['Package Qty..']);
                }
            }

            if ($row['Billing'] == 'P/P') {
                $dataExport['fc'] = 0;
                $dataExport['fd'] = 0;
                $dataExport['pp'] = 1;

                if ($dataExport['package_type'] == 'LTR') {
                    $commission = $getCommission->freightCommission('export', $row['Freight'], 'pp', 'ltr');
                } else if ($dataExport['package_type'] == 'DOC') {
                    $commission = $getCommission->freightCommission('export', $row['Freight'], 'pp', 'doc');
                } else {
                    $commission = $getCommission->freightCommission('export', $row['Freight'], 'pp', 'pkg', $row['Package Qty..']);
                }
            }

            $dataExport['awb_number'] = $row['Tracking'];
            $dataExport['description'] = $row['Descrition'];
            $dataExport['shipment_number'] = $row['Shipment No.'];
            $dataExport['courier_operation_type'] = 2;

            $shipper_name = $row['Shipper'];
            $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            if (empty($clientData)) {

                $newClientData['company_name'] = $shipper_name;
                $newClientData['company_address'] = $row['Address 1'] . ',' . $row['Address 2'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $shipper_name)->first();
            }
            $dataExport['shipper_name'] = $clientData->id;
            $dataExport['shipper_address'] = $row['Address 1'];
            $dataExport['shipper_address_2'] = $row['Address 2'];
            $dataExport['shipper_contact'] = $row['Contact'];
            $dataExport['shipper_address'] = $row['Address 1'];
            $dataExport['shipper_address_2'] = $row['Address 2'];
            $dataExport['origin'] = $row['Cnty'];
            $dataExport['destination'] = $row['Dest Cnty'];
            $dataExport['weight'] = $row['Weight'];
            $dataExport['unit'] = $row['Unit'];
            $dataExport['dim_weight'] = $row['Dim. Weight'];
            $dataExport['dim_unit'] = $row['Unit'];
            $dataExport['nbr_pcs'] = $row['Package Qty..'];
            $dataExport['freight'] = $row['Freight'];
            $dataExport['freight_currency'] = $row['Currency'];
            $dataExport['ups_scan_status'] = $row['Status'];
            $dataExport['created_on'] = date('Y-m-d h:i:s');
            $dataExport['created_by'] = auth()->user()->id;
            $importUpsData = DB::table('ups_details')->where('awb_number', $row['Tracking'])->first();

            $consignee_name = $row['Consignee'];
            $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();
            if (empty($clientData)) {

                $newClientData['company_name'] = $consignee_name;
                $newClientData['phone_number'] = $row['Phone'];
                $newClientData['company_address'] = $row['Address'];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $consignee_name)->first();

                $dataExport['consignee_name'] = $clientData->id;
                $dataExport['consignee_telephone'] = $row['Phone'];
                $dataExport['consignee_address'] = $row['Address'];
                $dataExport['consignee_city_state'] = $row['City, State'];
            } else {
                $dataExport['consignee_name'] = $clientData->id;
                $dataExport['consignee_telephone'] = $row['Phone'];
                $dataExport['consignee_address'] = $row['Address'];
                $dataExport['consignee_city_state'] = $row['City, State'];
            }
            if ($flag == 'update') {
                $dataExport['arrival_date'] = $arrivalDate;
                $dataExport['file_number'] = $duplicateUpsExport->file_number;
                DB::table('ups_details')->where('id', $duplicateId)->update($dataExport);
            } else {
                $dataLast = DB::table('ups_details')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
                if (empty($dataLast)) {
                    $dataExport['file_number'] = 'UPE 1110';
                } else {
                    $ab = 'UPE ';
                    $ab .= substr($dataLast->file_number, 4) + 1;
                    $dataExport['file_number'] = $ab;
                }

                $dataExport['arrival_date'] = $arrivalDate;
                $model = Ups::Create($dataExport);
                $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
                $filePath = $dir;
                //pre($filePath);
                $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
            }

            if ($flag == 'update') {
                //upsFreightCommission::Update($dataCommission);
                $dataCommission['ups_file_id'] = $duplicateUpsExport->id;
                $dataCommission['freight'] = $row['Freight'];
                $dataCommission['commission'] = $commission;
                $dataCommission['created_by'] = auth()->user()->id;
                $dataCommission['created_at'] = date('Y-m-d h:i:s');
                DB::table('ups_freight_commission')->where('ups_file_id', $duplicateId)->update($dataCommission);

                // Check Commission invoice has generated or not
                $checkInvoice = DB::table('invoices')->select(DB::raw('group_concat(id) as invoiceIds'))->where('ups_id', $duplicateUpsExport->id)->first();
                $invoiceOfCommission = DB::table('invoice_item_details')->whereIn('invoice_id', explode(',', $checkInvoice->invoiceIds))
                    ->whereIn('item_code', ['C1071', 'C1071/ Commission fret aerien (UPS)'])
                    ->count();

                if ($invoiceOfCommission == 0 && ($duplicateUpsExport->fc == 1 || $duplicateUpsExport->pp == 1))
                    Ups::generateUpsInvoice($duplicateUpsExport->id);
            } else {

                $lastUpsExport = DB::table('ups_details')->where('deleted', 0)->orderBy('id', 'DESC')->first();
                $dataCommission['ups_file_id'] = $lastUpsExport->id;
                $dataCommission['freight'] = $row['Freight'];
                $dataCommission['commission'] = $commission;
                $dataCommission['created_by'] = auth()->user()->id;
                $dataCommission['created_at'] = date('Y-m-d h:i:s');
                upsFreightCommission::Create($dataCommission);

                if ($model->fc == 1 || $model->pp == 1)
                    Ups::generateUpsInvoice($model->id);
            }
        }
    }

    public function ImportUpsCommissionFileSub($dData){
        $dData = arrayKeyValueFlip($dData);
        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        foreach ($dData as $key => $row) {
            $awbNumber = $row['TRACKING'];
            $upsFileData = DB::table('ups_details')
                ->select('ups_freight_commission.commission', 'ups_freight_commission.id as commissionID', 'ups_details.id as upsID', 'ups_details.pp', 'ups_details.fc', 'ups_details.fd', 'ups_details.commission_amount_approve')
                ->leftJoin('ups_freight_commission', 'ups_details.id', '=', 'ups_freight_commission.ups_file_id')
                ->where('ups_details.awb_number', $awbNumber)
                ->orderBy('ups_details.id', 'DESC')
                ->first();

            $approvedAmount = $row['TOT COMMISSION'];
            $pendingAmount = 0.00;
            //pre(!empty($upsFileData));
            if (!empty($upsFileData)) {
                $id = $upsFileData->upsID;

                if (($upsFileData->fc == '1' || $upsFileData->pp == '1') && ($upsFileData->commission_amount_approve == 'N' || empty($upsFileData->commission_amount_approve))) {
                    if ($approvedAmount == $upsFileData->commission) {
                        $pendingAmount = 0.00;
                        $model = Ups::find($id);
                        $commissionModel = upsFreightCommission::find($upsFileData->commissionID);
                        $model->update(['commission_amount_approve' => 'Y']);
                        //pre($pendingAmount);
                        $commissionModel->update(['pending_commission' => $pendingAmount]);

                        Session::flash('flash_message', 'Your commission has been approved.');
                    } else {
                        $model = Ups::find($id);
                        $commissionModel = upsFreightCommission::find($upsFileData->commissionID);
                        $model->update(['commission_amount_approve' => 'N']);
                        if ($upsFileData->fd == '1')
                            $pendingAmount = '0.00';
                        else
                            $pendingAmount = $upsFileData->commission - $approvedAmount;
                        //pre($pendingAmount);
                        $commissionModel->update(['pending_commission' => $pendingAmount]);
                        Session::flash('flash_message', 'Your commission has been approved partially.');
                    }
                }

                $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
                if (empty($getLastReceiptNumber)) {
                    $receiptNumber = '11101';
                } else {
                    if (empty($getLastReceiptNumber->receipt_number))
                        $receiptNumber = '11101';
                    else
                        $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
                }

                $dataInvoice = DB::table('ups_details')
                    ->select('invoices.total', 'invoices.balance_of', 'invoices.credits', 'invoices.id as invoiceID', 'invoices.bill_no', 'invoices.currency', 'ups_details.file_number', 'ups_details.id as upsID')
                    ->join('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                    ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                    ->where('invoices.bill_to', $dataClient->id)
                    ->whereIn('invoice_item_details.item_code', ['C1071', 'C1071/ Commission fret aerien (UPS)'])
                    ->where('ups_details.awb_number', $awbNumber)
                    ->first();
                if (!empty($dataInvoice)) {
                    $input['invoice_id'] = $dataInvoice->invoiceID;
                    $input['invoice_number'] = $dataInvoice->bill_no;
                    $input['ups_id'] = $dataInvoice->upsID;
                    $input['file_number'] = $dataInvoice->file_number;
                    $input['amount'] = $approvedAmount;
                    $input['exchange_amount'] = $approvedAmount;
                    $input['exchange_rate'] = '0.00';
                    $input['payment_via'] = 'COMPESATION';
                    $input['payment_via_note'] = 'Accept by manish patel';
                    $input['created_at'] = gmdate("Y-m-d H:i:s");
                    $input['client'] = $dataClient->id;
                    $input['payment_accepted_by'] = auth()->user()->id;
                    $input['receipt_number'] = $receiptNumber;
                    $model = InvoicePayments::create($input);

                    $dataCurrency = Currency::getData($dataInvoice->currency);
                    // Store payment received activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $dataInvoice->upsID;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Invoice #' . $dataInvoice->bill_no . " Payment Received " . number_format($approvedAmount, 2) . " (" . $dataCurrency->code . ")";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();

                    if ($approvedAmount == $dataInvoice->balance_of)
                        DB::table('invoices')->where('id', $dataInvoice->invoiceID)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->total, 'payment_status' => 'Paid', 'balance_of' => '0.00', 'payment_received_on' => date('Y-m-d'), 'payment_received_by' => auth()->user()->id]);
                    else
                        DB::table('invoices')->where('id', $dataInvoice->invoiceID)->update(['invoice_status_changed_by' => auth()->user()->id, 'credits' => $dataInvoice->credits + $approvedAmount, 'payment_status' => 'Partial', 'balance_of' => $dataInvoice->total - ($dataInvoice->credits + $approvedAmount)]);
                }
            }
        }
    }

    public function ImportScanSub($dData){
        $dData = arrayKeyValueFlip($dData);
        foreach ($dData as $key => $row) {
            $awbNumber = $row['Tracking'];

            $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $oldStatus = $dataUps->ups_scan_status;
            } else {
                $oldStatus = "1";
            }

            DB::table('ups_details')
                ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                ->update(['ups_scan_status' => '3', 'warehouse' => 5, 'inprogress_scan_status' => 1]);
            $dataUps = DB::table('ups_details')->where('awb_number', $awbNumber)->where('courier_operation_type', 1)->first();
            if (!empty($dataUps)) {

                $newStatus = $dataUps->ups_scan_status;
                if ($oldStatus != $newStatus) {
                    if (empty($oldStatus))
                        $oldStatus = '1';
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $dataUps->id;
                    $modelActivities->user_id = auth()->user()->id;
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
    }
    
    public function ImportWarehouseScanSub($dData){
        $dData = arrayKeyValueFlip($dData);
        foreach ($dData as $key => $row) {
            $awbNumber = $row['Tracking'];

            $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $oldStatus = $dataUps->ups_scan_status;
            } else {
                $oldStatus = "1";
            }

            DB::table('ups_details')
                ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                ->update(['ups_scan_status' => '4', 'warehouse' => $_POST['warehouse'], 'inprogress_scan_status' => 3]);
            $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $newStatus = $dataUps->ups_scan_status;
                if ($oldStatus != $newStatus) {
                    if (empty($oldStatus))
                        $oldStatus = '1';
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $dataUps->id;
                    $modelActivities->user_id = auth()->user()->id;
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
    }

    public function ImportPhysicalScanSub($dData){
        $dData = arrayKeyValueFlip($dData);
        foreach ($dData as $key => $row) {
            $awbNumber = $row['Tracking'];

            $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $oldStatus = $dataUps->ups_scan_status;
            } else {
                $oldStatus = "1";
            }

            DB::table('ups_details')
                ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                ->update(['ups_scan_status' => '5', 'inprogress_scan_status' => 2]);
            $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $newStatus = $dataUps->ups_scan_status;
                if ($oldStatus != $newStatus) {
                    if (empty($oldStatus))
                        $oldStatus = '1';
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $dataUps->id;
                    $modelActivities->user_id = auth()->user()->id;
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
    }

    public function ImportDeliveryScanSub($dData){
        $dData = arrayKeyValueFlip($dData);
        foreach ($dData as $key => $row) {
            $awbNumber = $row['Tracking'];

            $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $oldStatus = $dataUps->ups_scan_status;
            } else {
                $oldStatus = "1";
            }

            DB::table('ups_details')
                ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                ->update(['ups_scan_status' => '6', 'inprogress_scan_status' => 4]);
            $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $newStatus = $dataUps->ups_scan_status;
                if ($oldStatus != $newStatus) {
                    if (empty($oldStatus))
                        $oldStatus = '1';
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $dataUps->id;
                    $modelActivities->user_id = auth()->user()->id;
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
    }

    public function expandpackage(Request $request)
    {
        $upsId = $_POST['upsId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('ups_details_package')->where('ups_details_id', $upsId)->get();
        return view('ups.renderpackage', ['packageData' => $packageData, 'rowId' => $rowId]);
    }

    public function getupsdata()
    {
        $id = $_POST['upsId'];
        $aAr = array();
        $dataBilling = DB::table('ups_details')->where('id', $id)->first();
        $dataConsignee = DB::table('clients')->where('id', $dataBilling->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $dataBilling->shipper_name)->first();
        $aAr['consigneeName'] = $dataConsignee->company_name;
        $aAr['shipperName'] = $dataShipper->company_name;
        $aAr['billing_party'] = $dataBilling->billing_party;
        $aAr['id'] = $dataBilling->id;
        $aAr['awb_number'] = $dataBilling->awb_number;
        return json_encode($aAr);
    }

    public function filderbydaterange()
    {
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];

        $upsData = DB::table('ups_details')->whereBetween('tdate', [$startDate, $endDate])->get();

        return view("ups.filterdata", ['upsData' => $upsData]);
    }

    public function fildergetalldata()
    {
        $upsData = DB::table('ups_details')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("ups.filterdata", ['upsData' => $upsData]);
    }

    public function filderbyfilename()
    {
        $fileName = $_POST['fileName'];
        if (empty($fileName)) {
            $upsData = DB::table('ups_details')->where('deleted', '0')->orderBy('id', 'desc')->get();
        } else {
            $upsData = DB::table('ups_details')->where('file_name', $fileName)->orderBy('id', 'desc')->get();
        }

        return view("ups.filterdata", ['upsData' => $upsData]);
    }

    public function viewdetails($id)
    {
        $checkPermission = User::checkPermission(['viewdetails_courier_import'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'ups')->orderBy('id', 'desc')->get()->toArray();
        $model = Ups::find($id);
        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.ups_id', $id)
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
            ->whereNotNull('ups_details_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('ups_details_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();

        if ($model->courier_operation_type == 1) {
            $path = 'Files/Courier/Ups/Import/' . $model->file_number;
        } else {
            $path = 'Files/Courier/Ups/Export/' . $model->file_number;
        }

        $attachedFiles = DB::table('ups_uploaded_files')->where('file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        /* $files = Storage::disk('s3')->files($path);
        $newArr = [];
        if ($files) {
            $fileName = explode('/', $files[0]);
            $afc = count($attachedFiles);

            for ($i = 0; $i < count($files); $i++) {
                if (count($attachedFiles) != $i) {
                    $newArr[$i][0] = $attachedFiles[$i]->file_type;
                } else {
                    $newArr[$i][0] = '';
                }
                $tempArr = explode('/', $files[$i]);

                $newArr[$i][1] = $tempArr[(count($tempArr) - 1)];
            }
        } else {
            $newArr = [];
        } */

        $fileTypes = Config::get('app.fileTypes');

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ups_details_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ups_details_id', $id)
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
            ->where('invoices.ups_id', $id)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.ups_details_id', $id)
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

        /* if ($model->fc == 1 || $model->pp == 1) {
            $commissionData = array();
            $dataCommission = Ups::getCommissionData($id);
            if (!empty($dataCommission)) {
                $commissionData['allData'][0]['biliingItemId'] = '';
                $commissionData['allData'][0]['biliingItemDescription'] = 'Commission';
                $commissionData['allData'][0]['biliingItemAmount'] = number_format($dataCommission->commission - $dataCommission->pending_commission, 2);
                $commissionData['allData'][0]['currencyCode'] = '';
                $commissionData['allData'][0]['billingCurrencyCode'] = '';
                $commissionData['allData'][0]['costItemId'] = '';
                $commissionData['allData'][0]['costDescription'] = '';
                $commissionData['allData'][0]['costAmount'] = '';
                $commissionData['allData'][0]['costCurrencyCode'] = '';
                array_push($finalReportData, $commissionData);
            }
        } */
        /* Report by billing items */

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        return view("ups.view-details", ['model' => $model, 'invoices' => $invoices, 'activityData' => $activityData, 'dataExpense' => $dataExpense, 'id' => $id, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'path' => $path, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'finalReportData' => $finalReportData]);
    }

    public function checkuniqueawbnumber()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit') {
            $upsData = DB::table('ups_details')->where('deleted', '0')->where('awb_number', $number)->where('id', '<>', $id)->count();
        } else {
            $upsData = DB::table('ups_details')->where('deleted', '0')->where('awb_number', $number)->count();
        }

        if ($upsData) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getupsclientdetail(Request $request)
    {
        $clientData = [];
        $clientName = $request->get('clientName');
        $clientDetail = DB::table('clients')->where('company_name', $clientName)->orderBy('id', 'DESC')->first();
        if (!empty($clientDetail)) {
            $clientData = ['client_telephone' => $clientDetail->phone_number, 'client_address' => $clientDetail->company_address];
        } else {
            $clientData = [];
        }

        echo json_encode($clientData);
    }

    // For Ups commission
    public function createCommission()
    {
        $model = new upsImportExportCommission;
        return view('upscommission._form', ['model' => $model]);
    }

    public function storeUpsCommission(Request $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->user()->id;
        $input['created_at'] = date('Y-m-d H:i:s');
        upsImportExportCommission::Create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('upscommissiondetails');
    }
    public function upsCommissionDetail()
    {
        $upsCommissionData = DB::table('ups_import_export_commission')->where('deleted', '0')->get();

        return view('upscommission.index', ['upsCommissionData' => $upsCommissionData]);
    }

    public function deleteupscommission($id)
    {
        DB::table('ups_import_export_commission')->where('id', $id)->update(['deleted' => '1', 'deleted_by' => auth()->user()->id, 'deleted_at' => date('Y-m-d h:i:s')]);
        return redirect(route('upscommissiondetails'));
    }

    public function editupscommission($id)
    {
        $model = DB::table('ups_import_export_commission')->where('id', $id)->first();
        return view('upscommission._form', ['model' => $model]);
    }

    public function updateupscommission(Request $request, $id)
    {
        $input = $request->all();
        $model = upsImportExportCommission::find($id);
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('upscommissiondetails');
    }

    //End for ups commission

    public function filterbyfiletype(Request $request)
    {
        $file_type = $request->get('upsId');

        if ($file_type == 0) {
            $upsData = DB::table('ups_details')->where('deleted', '0')->get();
            return view('ups.upsallajax', ['upsData' => $upsData]);
        } else if ($file_type == 1) {
            $upsData = DB::table('ups_details')->where('courier_operation_type', 1)->where('deleted', '0')->get();
            return view('ups.importindexajax', ['upsData' => $upsData]);
        } else {
            $upsData = DB::table('ups_details')->where('courier_operation_type', 2)->where('deleted', '0')->get();
            return view('ups.exportindexajax', ['upsData' => $upsData]);
        }
    }

    public function filterbyscan(Request $request)
    {
        $scan_type = $request->get('upsId');
        $query = DB::table('ups_details')->where('deleted', '0');
        if ($scan_type == 2) {
            $query = $query;
        } else {
            $query = $query->where('ups_scan_status', $scan_type);
        }

        $upsData = $query->get();
        return view('ups.upsallajax', ['upsData' => $upsData]);
    }

    public function checkuniqueupscommission()
    {
        $fileType = $_POST['fileType'];
        $billingTerm = $_POST['billingTerm'];
        $courierType = $_POST['courierType'];
        $id = $_POST['id'];

        if (!empty($id)) {
            $data = DB::table('ups_import_export_commission')->where('deleted', '0')
                ->where('file_type', $fileType)
                ->where('billing_term', $billingTerm)
                ->where('courier_type', $courierType)
                ->where('id', '<>', $id)->count();
        } else {
            $data = DB::table('ups_import_export_commission')->where('deleted', '0')
                ->where('file_type', $fileType)
                ->where('billing_term', $billingTerm)
                ->where('courier_type', $courierType)
                ->count();
        }

        if ($data) {
            return 1;
        } else {
            return 0;
        }
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCourierImportEdit = User::checkPermission(['update_courier_import'], '', auth()->user()->id);
        $permissionCourierImportDelete = User::checkPermission(['delete_courier_import'], '', auth()->user()->id);
        $permissionCourierAddExpense = User::checkPermission(['add_courier_expenses'], '', auth()->user()->id);
        $permissionCourierAddInvoice = User::checkPermission(['add_courier_invoices'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $upsType = $req['upsType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        /* Session::put('upsListingFromDate', $req['fromDate']);
        Session::put('upsListingToDate', $req['toDate']); */
        $billingTermPost = $req['billingTerm'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $billingTerm = '';
        if ($search == 'FD' || $search == 'F/D')
            $billingTerm = 'fd';
        else if ($search == 'FC' || $search == 'F/C')
            $billingTerm = 'fc';
        else if ($search == 'PP' || $search == 'P/P')
            $billingTerm = 'pp';

        if (!empty($billingTermPost)) {
            if ($billingTermPost == 'P/P')
                $col = 'pp';
            else if ($billingTermPost == 'F/C')
                $col = 'fc';
            else if ($billingTermPost == 'F/D')
                $col = 'fd';
        }


        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ups_details.id', 'ups_details.id', 'file_number', 'master_file_number',  'c3.company_name', 'ups_scan_status', 'c2.company_name', 'c1.company_name', 'shipment_number', '', 'arrival_date', 'awb_number', 'package_type', 'origin', 'weight', '', 'commission_amount_approve'];

        $total = Ups::selectRaw('count(*) as total');
        //->where('deleted', '0');
        if (!empty($fileStatus)) {
            $total = $total->where('ups_scan_status', $fileStatus);
        }
        if (!empty($upsType)) {
            $total = $total->where('courier_operation_type', $upsType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        if (!empty($billingTermPost)) {
            $total = $total->where($col, '1');
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('ups_details')
            ->selectRaw('ups_details.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party');
        //->where('ups_details.deleted', '0');
        if (!empty($fileStatus)) {
            $query = $query->where('ups_scan_status', $fileStatus);
        }
        if (!empty($upsType)) {
            $query = $query->where('courier_operation_type', $upsType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        if (!empty($billingTermPost)) {
            $query = $query->where($col, '1');
        }
        $filteredq = DB::table('ups_details')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party');
        //->where('ups_details.deleted', '0');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('ups_scan_status', $fileStatus);
        }
        if (!empty($upsType)) {
            $filteredq = $filteredq->where('courier_operation_type', $upsType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        if (!empty($billingTermPost)) {
            $filteredq = $filteredq->where($col, '1');
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search, $billingTerm) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('shipment_number', 'like', '%' . $search . '%')
                    ->orWhere('origin', 'like', '%' . $search . '%')
                    ->orWhere('weight', 'like', '%' . $search . '%')
                    ->orWhere('package_type', 'like', '%' . $search . '%');
                //->orWhere('ups_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
                if (!empty($billingTerm))
                    $query2 = $query2->orWhere($billingTerm, 'like', '%1%');
            });
            $filteredq->where(function ($query2) use ($search, $billingTerm) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('shipment_number', 'like', '%' . $search . '%')
                    ->orWhere('origin', 'like', '%' . $search . '%')
                    ->orWhere('weight', 'like', '%' . $search . '%')
                    ->orWhere('package_type', 'like', '%' . $search . '%');
                //->orWhere('ups_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
                if (!empty($billingTerm))
                    $query2 = $query2->orWhere($billingTerm, 'like', '%1%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $couriers) {
            $dataBillingParty = app('App\Clients')->getClientData($couriers->billing_party);
            $consigneeData = app('App\Clients')->getClientData($couriers->consignee_name);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $shipperData = app('App\Clients')->getClientData($couriers->shipper_name);
            $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
            //$date = $couriers->courier_operation_type == 1 ? (!empty($couriers->arrival_date) ? date('d-m-Y', strtotime($couriers->arrival_date)) : '-') : (!empty($couriers->tdate) ? date('d-m-Y', strtotime($couriers->tdate)) : '-');
            $date = !empty($couriers->arrival_date) ? date('d-m-Y', strtotime($couriers->arrival_date)) : '-';
            $billingTerm = Ups::getBillingTerm($couriers->id);
            $invoiceNumbers = Expense::getUpsInvoicesOfFile($couriers->id);

            if ($couriers->package_type == 'LTR')
                $packageType = 'Letter';
            else if ($couriers->package_type == 'DOC')
                $packageType = 'Document';
            else
                $packageType = 'Package';

            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printupsfile", [$couriers->id, $couriers->courier_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete = route('deleteups', [$couriers->id, '']);
            $edit = route('editups', [$couriers->id, $couriers->courier_operation_type]);

            if ($couriers->deleted == '0') {
                if ($permissionCourierImportEdit) {
                    $action .= '<a href="' . $edit . '" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }

                if ($permissionCourierImportDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['ups', $couriers->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCourierAddExpense) {
                    $action .= '<li><a href="' . route('createupsexpense', $couriers->id) . '">Add Expense</a></li>';
                }

                if ($permissionCourierAddInvoice) {
                    $action .= '<li><a href="' . route('createupsinvoice', $couriers->id) . '">Add Invoice</a></li>';
                }

                $action .= '<li><button id="btnAddWarehouseInFile" data-module ="Warehouse" class="btnModalPopup" value="' . route('addwarehouseinfile', [$couriers->id, 'ups']) . '">Add Warehouse</button></li>';

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['UPS', $couriers->id]) . '">Close File</a></li>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $closedDetail = '';
            if ($couriers->file_close == 1) {
                $dataUserCloseFile = DB::table('users')->where('id', $couriers->close_unclose_by)->first();
                $closedDetail .= !empty($couriers->close_unclose_date) ? date('d-m-Y', strtotime($couriers->close_unclose_date)) : '-';
                $closedDetail .= ' | ';
                $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
            }

            $data[] = [$couriers->id, '', $couriers->file_number, !empty($couriers->master_file_number) ? $couriers->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($couriers->ups_scan_status) ? $couriers->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($couriers->ups_scan_status) ? $couriers->ups_scan_status : '-'] : '-', $shipper, $consignee, !empty($couriers->shipment_number) ? $couriers->shipment_number : '-', $invoiceNumbers, $date, $couriers->awb_number, $packageType, $couriers->origin, !empty($couriers->weight) ? $couriers->weight . ' ' . $couriers->unit : '-', $billingTerm, $couriers->commission_amount_approve == 'Y' ? 'Yes' : 'No', ($couriers->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserverside()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkFileAssgned') {
            $UpsId = $_POST['UpsId'];
            return Ups::checkFileAssgned($UpsId);
        }
        if ($flag == 'checkPakckages') {
            $UpsId = $_POST['UpsId'];
            return Ups::checkPakckages($UpsId);
        }
        if ($flag == 'getUpsData') {
            $UpsId = $_POST['UpsId'];
            return json_encode(Ups::getUpsData($UpsId));
        }
        if ($flag == 'getUpsDataWithInvoice') {
            $UpsId = $_POST['UpsId'];
            return json_encode(Ups::getUpsDataWithInvoice($UpsId));
        }
    }

    public function printupsfile($upsId, $upsType)
    {
        $model = DB::table('ups_details')->where('id', $upsId)->first();
        if ($upsType == 1) {
            $pdf = PDF::loadView('ups.printimport', ['model' => $model]);
        } else {
            $pdf = PDF::loadView('ups.printexport', ['model' => $model]);
        }

        $pdf_file = $model->file_number . '.pdf';
        $pdf_path = 'public/upsFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function removeDuplicateFiles()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        //SELECT group_concat(id),count(*),file_number FROM ups_details group by file_number having count(*) >= 2 limit 5000
        $data = DB::table('ups_details')
            ->select(DB::raw('group_concat(id) as ids,count(*) as total,file_number'))
            ->groupBy('file_number')
            ->having('total', '>=', 2)
            ->get();
        //pre($data);
        foreach ($data as $k => $v) {
            $exploadFiles = explode(',', $v->ids);
            unset($exploadFiles[0]);
            //pre($exploadFiles,1);
            foreach ($exploadFiles as $k1 => $v1) {
                $getlastNumber = DB::table('ups_details')->select('file_number')->get();
                $allNumber = array();
                foreach ($getlastNumber as $a1 => $b1) {
                    $allNumber[] = preg_replace('/[^0-9]/', '', $b1->file_number);;
                }
                $lastfileNumber = max($allNumber);

                $dataUps = DB::table('ups_details')->where('id', $v1)->first();
                $dataUps = (array) $dataUps;
                if ($dataUps['courier_operation_type'] == 1) {
                    $ab = 'UPI ';
                } else {
                    $ab = 'UPE ';
                }
                $ab .= $lastfileNumber + 1;
                $fileNumber = $ab;
                DB::table('ups_details')->where('id', $v1)->update(['file_number' => $fileNumber]);
            }
        }
        pre("Success");
    }
}
