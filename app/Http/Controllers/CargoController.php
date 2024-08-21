<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Cargo;
use App\CargoConsolidateAwbHawb;
use App\CargoContainers;
use App\CargoPackages;
use App\CargoProductDetails;
use App\Clients;
use App\Expense;
use App\HawbContainers;
use App\HawbFiles;
use App\HawbPackages;
use App\Invoices;
use App\InvoiceItemDetails;
use App\localInvoicePayment;
use App\Mail\localInvoiceMail;
use App\Mail\monthlyInvoiceMail;
use App\Mail\sendCargoInfoMail;
use App\User;
use App\CargoRenewContract;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PDF;
use Response;
use Session;
use App\Mail\sendCashierInvoiceMail;

class CargoController extends Controller
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

    public function cargoimportsindex()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cargos = DB::table('cargo')->where('deleted', '0')->where('cargo_operation_type', '1')->orderBy('id', 'desc')->get();
        return view("cargo.importindex", ['cargos' => $cargos]);
    }
    public function cargoimportsindexajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cargos = DB::table('cargo')->where('deleted', '0')->where('cargo_operation_type', '1')->orderBy('id', 'desc')->get();
        return view("cargo.importindexajax", ['cargos' => $cargos]);
    }
    public function cargoexportsindex()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cargos = DB::table('cargo')->where('deleted', '0')->where('cargo_operation_type', '2')->orderBy('id', 'desc')->get();
        return view("cargo.exportindex", ['cargos' => $cargos]);
    }
    public function cargoexportsindexajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cargos = DB::table('cargo')->where('deleted', '0')->where('cargo_operation_type', '2')->orderBy('id', 'desc')->get();
        return view("cargo.exportindexajax", ['cargos' => $cargos]);
    }
    public function cargolocalesindex()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cargos = DB::table('cargo')->where('deleted', '0')->where('cargo_operation_type', '3')->orderBy('id', 'desc')->get();
        return view("cargo.localeindex", ['cargos' => $cargos]);
    }
    public function cargolocalesindexajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $cargos = DB::table('cargo')->where('deleted', '0')->where('cargo_operation_type', '3')->orderBy('id', 'desc')->get();
        return view("cargo.localeindexajax", ['cargos' => $cargos]);
    }
    public function cargoallajax()
    {
        $cargos = DB::table('cargo')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("cargo.cargoallajax", ['cargos' => $cargos, 'flagFileType' => '']);
    }
    public function cargoall($flagCargo = null)
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        return view("cargo.cargoall", ['flagCargo' => $flagCargo]);
    }

    public function filterusingcargofiletype()
    {
        $flagFileType = $_POST['flagFileType'];

        if ($flagFileType == '1') {
            $cargos = DB::table('cargo')->where('deleted', '0')->where('consolidate_flag', $flagFileType)->orderBy('id', 'desc')->get();
        } else if ($flagFileType == '0') {
            $cargos = DB::table('cargo')->where('deleted', '0')->where('consolidate_flag', $flagFileType)->orderBy('id', 'desc')->get();
        } else {
            $cargos = DB::table('cargo')->where('deleted', '0')->orderBy('id', 'desc')->get();
        }

        return view("cargo.filetypefiltering", ['cargos' => $cargos, 'flagFileType' => $flagFileType]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $checkPermission = User::checkPermission(['add_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        if ($id == 1 && empty(User::checkPermission(['add_cargo'], '', auth()->user()->id))) {
            return redirect('/home');
        }

        if ($id == 2 && empty(User::checkPermission(['add_cargo'], '', auth()->user()->id))) {
            return redirect('/home');
        }

        if ($id == 3 && empty(User::checkPermission(['add_cargo'], '', auth()->user()->id))) {
            return redirect('/home');
        }

        $model = new Cargo;
        $modelConsolidateAw = new CargoConsolidateAwbHawb;
        $modelCargoContainer = new CargoContainers;
        $modelCargoPackage = new CargoPackages;
        $modelHawb = new HawbFiles;
        $modelHawbCargoPackage = new HawbPackages;
        $modelHawbCargoContainer = new HawbContainers;
        $dataImportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '1')->get()->pluck('awb_bl_no', 'id');
        $dataExportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '2')->get()->pluck('awb_bl_no', 'id');
        $modelHawb->weight = '0.00';
        $modelHawb->hdate = date('d-m-Y');

        $natureOfServices = DB::table('nature_of_services')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $natureOfServices = json_decode($natureOfServices, 1);
        ksort($natureOfServices);

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $dataCargoForImport = DB::table('cargo')->where('deleted', 0)->where('cargo_operation_type', 1)->whereNotNull('hawb_hbl_no')->get();
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

        $dataCargoForExport = DB::table('cargo')->where('deleted', 0)->where('cargo_operation_type', 2)->whereNotNull('hawb_hbl_no')->get();
        $existingExportHawbFiles = '';
        $existingExportHawbFilesArray = array();
        if (!empty($dataCargoForExport)) {
            foreach ($dataCargoForExport as $key => $value) {
                $existingExportHawbFiles .= $value->hawb_hbl_no . ',';
                //array_push($existingHawbFiles,$dataExp);
            }
        }

        $nexistingExportHawbFiles = rtrim($existingExportHawbFiles, ',');
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

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Cargo')->orderBy('id', 'desc')->pluck('name', 'id');

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        return view('cargo._form', ['model' => $model, 'id' => $id, 'modelConsolidateAw' => $modelConsolidateAw, 'modelCargoPackage' => $modelCargoPackage, 'modelCargoContainer' => $modelCargoContainer, 'natureOfServices' => $natureOfServices, 'agents' => $agents, 'dataImportHawbAll' => $dataImportHawbAll, 'dataExportHawbAll' => $dataExportHawbAll, 'warehouses' => $warehouses, 'billingParty' => $billingParty, 'modelHawb' => $modelHawb, 'modelHawbCargoPackage' => $modelHawbCargoPackage, 'modelHawbCargoContainer' => $modelHawbCargoContainer, 'dataImportAwbNos' => $dataImportAwbNos]);
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
        $prodData = isset($input['prodDetail']) ? $input['prodDetail'] : array();
        if ($input['cargo_operation_type'] == 1) {
            $cargoPackage = $input['modalCargoPackage'];
            unset($input['modalCargoPackage']);
            $cargoContainer = $input['modalCargoContainer'];
            unset($input['modalCargoContainer']);

            if ($input['consolidate_flag'] == 1) {
                $input['awb_bl_no'] = $input['awb_bl_no'];
            }
        }

        // Save file number
        $dataLast = DB::table('cargo')->orderBy('id', 'desc')->first();
        if (empty($dataLast)) {
            if ($input['cargo_operation_type'] == 1) {
                $input['file_number'] = 'CAI 1110';
            } else if ($input['cargo_operation_type'] == 2) {
                $input['file_number'] = 'CAE 1110';
            } else {
                $input['file_number'] = 'CAL 1110';
            }
        } else {
            if ($input['cargo_operation_type'] == 1) {
                $ab = 'CAI ';
            } else if ($input['cargo_operation_type'] == 2) {
                $ab = 'CAE ';
            } else {
                $ab = 'CAL ';
            }
            $ab .= substr($dataLast->file_number, 4) + 1;
            $input['file_number'] = $ab;
        }

        if ($input['cargo_operation_type'] == 1) {
            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
            $input['rental'] = 0;

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
        } else if ($input['cargo_operation_type'] == 2) {
            $input['rental'] = 0;

            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['sent_on'] = !empty($input['sent_on']) ? date('Y-m-d', strtotime($input['sent_on'])) : null;
            $input['awb_bl_no'] = $input['export_awb_bl_no'];
            $input['hawb_hbl_no'] = $input['export_hawb_hbl_no'];

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
        } else {
            if ($input['rental'] == 1) {
                $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
                $input['rental'] = $input['rental'];
                $input['contract_months'] = $input['contract_months'];
                $input['rental_paid_status'] = $input['rental_paid_status'];
                if (!empty($input['rental_starting_date'])) {
                    $input['rental_starting_date'] = date('Y-m-d', strtotime($input['rental_starting_date']));
                } else {
                    $input['rental_starting_date'] = $input['rental_starting_date'];
                }
                if (!empty($input['rental_ending_date'])) {
                    $input['rental_ending_date'] = date('Y-m-d', strtotime($input['rental_ending_date']));
                } else {
                    $input['rental_ending_date'] = $input['rental_ending_date'];
                }

                $input['rental_cost'] = $input['rental_cost'];
                $input['billing_party'] = $input['billing_party'];
                $input['awb_bl_no'] = $input['locale_awb_bl_no'];
            } else {
                //pre($input);
                $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
                $input['rental'] = $input['rental'];
                $input['contract_months'] = '';
                $input['rental_ending_date'] = null;
                $input['billing_party'] = $input['billing_party'];
                $input['awb_bl_no'] = $input['locale_awb_bl_no'];
                $input['rental_paid_status'] = null;
            }

            $consignee_name = $input['consignee_name'];
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
        }

        if (isset($input['warehouse']) && !empty($input['warehouse'])) {
            $input['display_notification_warehouse'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }
        if (isset($input['agent_id']) && !empty($input['agent_id'])) {
            $input['display_notification'] = '1';
            $input['notification_date_time'] = date('Y-m-d H:i:s');
        }

        $input['created_by'] = auth()->user()->id;
        //pre($input);
        $model = Cargo::create($input);
        if ($model->cargo_operation_type == 1) {
            $dir = 'Files/Cargo/Import/' . $model->file_number;
        } else if ($model->cargo_operation_type == 2) {
            $dir = 'Files/Cargo/Export/' . $model->file_number;
        } else {
            $dir = 'Files/Cargo/Local/' . $model->file_number;
        }
        $filePath = $dir;
        //pre($filePath);

        $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
        //pre($success.' '.'test');
        //Send mail to admin and user
        if ($input['rental'] == 1 && $input['cargo_operation_type'] == 3) {
            $modelLocal = DB::table('cargo')->where('consignee_name', $input['consignee_name'])->orderBy('id', 'DESC')->first();
            //pre($modelLocal->id);
            $input['cargo_id'] = $model->id;
            $adminId = auth()->user()->id;
            $admins_mail = User::where('id', $adminId)->first();
            $clients_mail = Clients::where('id', $model->consignee_name)->first();
            $input['client_email'] = $clients_mail->email;
            $input['billing_party'] = $model->billing_party;
            /* $pdf = PDF::loadView('cargo.printlocale', ['model' => $model]);
            $pdf_file = $input['file_number'] . '_local.pdf';
            $pdf_path = 'public/cargoFilePdf/' . $pdf_file;
            $pdf->save($pdf_path);
            $input['invoiceAttachment'] = $pdf_path;
            if (!empty($clients_mail->email)) {
                Mail::to($clients_mail->email)->cc($admins_mail->email)->send(new localInvoiceMail($input));
            } */

            $input['created_by'] = $adminId;
            if ($input['rental_paid_status'] == 'p') {
                $localFileDate = $input['opening_date'];
                $localInvoice['total'] = $input['rental_cost'];
                $localInvoice['local_invoice_id'] = $model->id;
                $localInvoice['status'] = 'p';
                $localInvoice['mail_send'] = '1';
                $localInvoice['created_by'] = auth()->user()->id;
                $localInvoice['created_at'] = gmdate('Y-m-d H:i:s');
                $mainFileDate = $input['rental_ending_date'];
                $datediff = date_diff(date_create($mainFileDate), date_create($localFileDate));
                $diff = $datediff->format("%m");
                if ($diff != 0) {
                    for ($i = 0; $i < $diff; $i++) {
                        //$totalPaidAmount = $totalPaidAmount + $input['total'];
                        $localInvoice['duration'] = date('d-m-Y', strtotime($localFileDate)) . ' TO ';
                        $localFileDate = date('d-m-Y', strtotime('+1month', strtotime($localFileDate)));

                        $localInvoice['date'] = date('Y-m-d', strtotime($localFileDate));
                        //pre($localInvoice['date']);
                        $localInvoice['duration'] = $localInvoice['duration'] . $localFileDate;
                        localInvoicePayment::Create($localInvoice);
                    }
                }
            }
            $this->storeLocalinvoice($input);
        }

        if (!empty($model->hawb_hbl_no)) {
            $exploadedIds = explode(',', $model->hawb_hbl_no);
            HawbFiles::whereIn('id', $exploadedIds)->update(['cargo_id' => $model->id]);
        }

        // Save cargo package detail
        if ($input['cargo_operation_type'] == 1) {
            if ($input['flag_package_container'] == 1) {
                $modelCargoPackageDetail = new CargoPackages();
                $modelCargoPackageDetail->cargo_id = $model->id;
                $modelCargoPackageDetail->pweight = $cargoPackage['pweight'];
                $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
                $modelCargoPackageDetail->measure_volume = $input['measure_volume'];
                $modelCargoPackageDetail->pvolume = $cargoPackage['pvolume'];
                $modelCargoPackageDetail->ppieces = $cargoPackage['ppieces'];
                $modelCargoPackageDetail->save();
            } else {
                $countContainer = count($cargoContainer['container_number']);
                for ($i = 0; $i < $countContainer; $i++) {
                    $modelCargoContainerDetails = new CargoContainers();
                    $modelCargoContainerDetails->cargo_id = $model->id;
                    $modelCargoContainerDetails->container_number = $cargoContainer['container_number'][$i];
                    $modelCargoContainerDetails->save();
                }
            }
        } else if ($input['cargo_operation_type'] == 2) {
            $modelCargoPackageDetail = new CargoPackages();
            $modelCargoPackageDetail->cargo_id = $model->id;
            $modelCargoPackageDetail->pweight = $input['weight'];
            $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
            $modelCargoPackageDetail->ppieces = $input['no_of_pieces'];
            $modelCargoPackageDetail->save();
        }

        // Save product details
        $countDetails = isset($prodData['prod_date']) ? count($prodData['prod_date']) : 0;
        for ($i = 0; $i < $countDetails; $i++) {
            $modelDetails = new CargoProductDetails();
            $modelDetails->cargo_id = $model->id;
            if (!empty($prodData['prod_date'][$i])) {
                $modelDetails->prod_date = $prodData['prod_date'][$i];
            }

            if (!empty($prodData['prod_description'][$i])) {
                $modelDetails->prod_description = $prodData['prod_description'][$i];
            }

            if (!empty($prodData['pro_expense'][$i])) {
                $modelDetails->pro_expense = $prodData['pro_expense'][$i];
            }

            if (!empty($prodData['pro_expense_gdes'][$i])) {
                $modelDetails->pro_expense_gdes = $prodData['pro_expense_gdes'][$i];
            }

            if (!empty($prodData['pro_expense_usd'][$i])) {
                $modelDetails->pro_expense_usd = $prodData['pro_expense_usd'][$i];
            }

            if (!empty($prodData['to_bill_gdes'][$i])) {
                $modelDetails->to_bill_gdes = $prodData['to_bill_gdes'][$i];
            }

            if (!empty($prodData['to_bill_usd'][$i])) {
                $modelDetails->to_bill_usd = $prodData['to_bill_usd'][$i];
            }

            if (!empty($prodData['credit_gdes_usd'][$i])) {
                $modelDetails->credit_gdes_usd = $prodData['credit_gdes_usd'][$i];
            }

            if (!empty($prodData['credit_gdes'][$i])) {
                $modelDetails->credit_gdes = $prodData['credit_gdes'][$i];
            }

            if (!empty($prodData['credit_usd'][$i])) {
                $modelDetails->credit_usd = $prodData['credit_usd'][$i];
            }

            $modelDetails->save();
        }

        $data = json_decode($model, 1);
        if ($model->cargo_operation_type == 1) {
            $data['flagExpense'] = 'Cargo Import';
        } else if ($model->cargo_operation_type == 2) {
            $data['flagExpense'] = 'Cargo Export';
        } else {
            $data['flagExpense'] = 'Cargo Locale';
        }

        Activities::log('create', 'cargo', (object) $data);
        if (isset($input['flagBtn']) && !empty($input['flagBtn']) && $input['flagBtn'] == 'saveprint') {
            if ($model->cargo_operation_type == 1) {
                $pdf = PDF::loadView('cargo.printimport', ['model' => $model]);
            } else if ($model->cargo_operation_type == 2) {
                $pdf = PDF::loadView('cargo.printexport', ['model' => $model]);
            } else {
                $pdf = PDF::loadView('cargo.printlocale', ['model' => $model]);
            }

            $pdf_file = $model->file_number . '_expense.pdf';
            $pdf_path = 'public/cargoFilePdf/' . $pdf_file;
            $pdf->save($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else {
            return Response::json(['success' => '1']);
        }
        /*Session::flash('flash_message', 'Record has been created successfully');
    if($request->cargo_operation_type == 1)
    {
    return redirect()->route('cargoimports');
    }
    elseif($request->cargo_operation_type == 2)
    {
    return redirect()->route('cargoexports');
    }
    else{
    return redirect()->route('cargolocales');
    }*/
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cargo  $cargo
     * @return \Illuminate\Http\Response
     */
    public function show(Cargo $cargo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cargo  $cargo
     * @return \Illuminate\Http\Response
     */
    public function editcargo(Cargo $cargo, $rid, $id)
    {
        $checkPermission = User::checkPermission(['update_cargo'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        if ($id == 1 && empty(User::checkPermission(['update_cargo'], '', auth()->user()->id))) {
            return redirect('/home');
        }

        if ($id == 2 && empty(User::checkPermission(['update_cargo'], '', auth()->user()->id))) {
            return redirect('/home');
        }

        if ($id == 3 && empty(User::checkPermission(['update_cargo'], '', auth()->user()->id))) {
            return redirect('/home');
        }

        $model = Cargo::find($rid);
        $modelConsolidateAw = DB::table('cargo_consolidate_awb_hawb')->where('cargo_id', $rid)->first();
        if (empty($modelConsolidateAw)) {
            $modelConsolidateAw = new CargoConsolidateAwbHawb;
        }

        $modelCargoPackage = DB::table('cargo_packages')->where('cargo_id', $rid)->first();
        //$modelCargoPackage->pweight;
        if (empty($modelCargoPackage)) {
            $modelCargoPackage = new CargoPackages;
        }

        $modelCargoContainer = new CargoContainers;

        $natureOfServices = DB::table('nature_of_services')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $natureOfServices = json_decode($natureOfServices, 1);
        ksort($natureOfServices);

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        $dataCargoForImport = DB::table('cargo')->where('deleted', 0)->where('cargo_operation_type', 1)->where('id', '<>', $rid)->whereNotNull('hawb_hbl_no')->get();
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

        $dataCargoForExport = DB::table('cargo')->where('deleted', 0)->where('cargo_operation_type', 2)->where('id', '<>', $rid)->whereNotNull('hawb_hbl_no')->get();
        $existingExportHawbFiles = '';
        $existingExportHawbFilesArray = array();
        if (!empty($dataCargoForExport)) {
            foreach ($dataCargoForExport as $key => $value) {
                $existingExportHawbFiles .= $value->hawb_hbl_no . ',';
                //array_push($existingHawbFiles,$dataExp);
            }
        }

        $nexistingExportHawbFiles = rtrim($existingExportHawbFiles, ',');
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

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Cargo')->orderBy('id', 'desc')->pluck('name', 'id');

        $cashier = DB::table('users')->select(['id', 'name'])->where('department', '11')->orderBy('id', 'desc')->pluck('name', 'id');
        // if($id == 3){
        //     $localinvoicecount = DB::table('local_invoice_payment_detail')->where('local_invoice_id',$id)->get();
        //     if(count($localinvoicecount)>0){
        //         $updatePermission = 0;
        //     } else {
        //         $updatePermission = 1;
        //     }
        // }

        $dataForLocal = DB::table('cargo')->where('id', $rid)->first();
        //pre($dataForLocal->rental);
        $invoiceForLocal = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $rid)->get();

        if ($dataForLocal->rental == '1' && count($invoiceForLocal) > 0) {
            $premissionToUpdateLocal = 0;
        } else {
            $premissionToUpdateLocal = 1;
        }

        $cargoRenewContract = CargoRenewContract::where('cargo_id', $rid)->get()->toArray();
        // pre($model);

        return view('cargo._form', ['model' => $model, 'id' => $id, 'modelConsolidateAw' => $modelConsolidateAw, 'modelCargoPackage' => $modelCargoPackage, 'modelCargoContainer' => $modelCargoContainer, 'natureOfServices' => $natureOfServices, 'agents' => $agents, 'dataImportHawbAll' => $dataImportHawbAll, 'dataExportHawbAll' => $dataExportHawbAll, 'billingParty' => $billingParty, 'warehouses' => $warehouses, 'cashier' => $cashier, 'premissionToUpdateLocal' => $premissionToUpdateLocal, 'cargoRenewContract' => $cargoRenewContract]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cargo  $cargo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $model = Cargo::find($id);
        $oldAgent = $model->agent_id;
        $newAgent = $request->agent_id;

        $oldWarehouse = $model->warehouse;
        $newWarehouse = $request->warehouse;

        // pre($request->input());
        $model->fill($request->input());
        // pre($model);
        // Save activity logs
        Activities::log('update', 'cargo', $model);
        $input = $request->all();
        $prodData = isset($input['prodDetail']) ? $input['prodDetail'] : array();
        if ($input['cargo_operation_type'] == 1) {
            $cargoPackage = $input['modalCargoPackage'];
            unset($input['modalCargoPackage']);
            $cargoContainer = $input['modalCargoContainer'];
            unset($input['modalCargoContainer']);
        }

        if ($input['cargo_operation_type'] == 1) {
            if ($input['consolidate_flag'] != 1) {
                $input['hawb_hbl_no'] = null;
            }

            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        } else if ($input['cargo_operation_type'] == 2) {
            if ($input['consolidate_flag'] != 1) {
                $input['hawb_hbl_no'] = null;
            }

            $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
            $input['sent_on'] = !empty($input['sent_on']) ? date('Y-m-d', strtotime($input['sent_on'])) : null;
        } else {
            if ($input['rental'] == 1) {
                if ($input['contract_renew'] == 'Y') {
                    $previous_date = date('Y-m-d', strtotime($input['rental_hidden_ending_date']));
                    $new_date = date('Y-m-d', strtotime($input['rental_ending_date']));
                    $create = [
                        'cargo_id' => $model->id,
                        'previous_date' => $previous_date,
                        'renew_months' => $input['no_of_months'],
                        'new_date' => $new_date,
                        'updated_by' => auth()->user()->id,
                        'updated_by_name' => auth()->user()->name,
                    ];
                    CargoRenewContract::create($create);
                    $input['rental_ending_date'] = $new_date;
                }
                $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
                if (!empty($input['rental_starting_date'])) {
                    $input['rental_starting_date'] = date('Y-m-d', strtotime($input['rental_starting_date']));
                } else {
                    $input['rental_starting_date'] = $input['rental_starting_date'];
                }

                if (!empty($input['rental_ending_date'])) {
                    $input['rental_ending_date'] = date('Y-m-d', strtotime($input['rental_ending_date']));
                } else {
                    $input['rental_ending_date'] = $input['rental_ending_date'];
                }
                // if (!empty($input['rental_hidden_ending_date'])) {
                //     $input['rental_ending_date'] = date('Y-m-d', strtotime($input['rental_hidden_ending_date']));
                // } else {
                //     $input['rental_ending_date'] = $input['rental_hidden_ending_date'];
                // }
                $input['awb_bl_no'] = $input['awb_bl_no'];
            } else {
                $input['opening_date'] = !empty($input['opening_date']) ? date('Y-m-d', strtotime($input['opening_date'])) : null;
                $input['awb_bl_no'] = $input['awb_bl_no'];
                $input['rental_ending_date'] = null;
            }
        }

        if ($oldAgent != $newAgent) {
            $input['display_notification'] = '1';
        }

        if ($oldWarehouse != $newWarehouse) {
            $input['display_notification_warehouse'] = '1';
        }

        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $input['updated_by'] = auth()->user()->id;

        if ($input['cargo_operation_type'] == 1) {
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
        } else if ($input['cargo_operation_type'] == 2) {
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
        } else {
            $consignee_name = $input['consignee_name'];
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
        }

        // $model->save();
        // pre($input);
        $model->update($input);

        if (!empty($model->hawb_hbl_no)) {
            $exploadedIds = explode(',', $model->hawb_hbl_no);
            HawbFiles::whereIn('id', $exploadedIds)->update(['cargo_id' => $model->id]);
        }

        // Save cargo package detail
        if ($input['cargo_operation_type'] == 1) {
            if ($input['flag_package_container'] == 1) {
                CargoContainers::where('cargo_id', $id)->delete();
                CargoPackages::where('cargo_id', $id)->delete();
                $modelCargoPackageDetail = new CargoPackages();
                $modelCargoPackageDetail->cargo_id = $model->id;
                $modelCargoPackageDetail->pweight = $cargoPackage['pweight'];
                $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
                $modelCargoPackageDetail->measure_volume = $input['measure_volume'];
                $modelCargoPackageDetail->pvolume = $cargoPackage['pvolume'];
                $modelCargoPackageDetail->ppieces = $cargoPackage['ppieces'];
                $modelCargoPackageDetail->save();
            } else {
                CargoPackages::where('cargo_id', $id)->delete();
                CargoContainers::where('cargo_id', $id)->delete();
                $countContainer = count($cargoContainer['container_number']);
                for ($i = 0; $i < $countContainer; $i++) {
                    $modelCargoContainerDetails = new CargoContainers();
                    $modelCargoContainerDetails->cargo_id = $model->id;
                    $modelCargoContainerDetails->container_number = $cargoContainer['container_number'][$i];
                    $modelCargoContainerDetails->save();
                }
            }
        } else if ($input['cargo_operation_type'] == 2) {
            CargoPackages::where('cargo_id', $id)->delete();
            $modelCargoPackageDetail = new CargoPackages();
            $modelCargoPackageDetail->cargo_id = $model->id;
            $modelCargoPackageDetail->pweight = $input['weight'];
            $modelCargoPackageDetail->measure_weight = $input['measure_weight'];
            $modelCargoPackageDetail->ppieces = $input['no_of_pieces'];
            $modelCargoPackageDetail->save();
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        $dept = auth()->user()->department;

        if (checkloggedinuserdata() == 'Cashier') {
            return redirect()->route('cashiercargoall');
        } else if (checkloggedinuserdata() == 'Agent') {
            return redirect()->route('agentcargoall');
        } else {
            return redirect()->route('cargoall');
        }

        /*if($request->cargo_operation_type == 1)
    {
    return redirect()->route('cargoimports');
    }
    elseif($request->cargo_operation_type == 2)
    {
    return redirect()->route('cargoexports');
    }
    else{
    return redirect()->route('cargolocales');
    }*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cargo  $cargo
     * @return \Illuminate\Http\Response
     */
    public function destroy($rid, $id)
    {
        $model = Cargo::where('id', $rid)->update(['deleted' => 1, 'deleted_on' => date('Y-m-d h:i:s'), 'deleted_by' => auth()->user()->id]);
        /* CargoProductDetails::where('cargo_id', $rid)->delete();
        Invoices::where('cargo_id', $rid)->update(['deleted' => 1]);
        if ($id == 1) {
            return redirect()->route('cargoimports');
        } elseif ($id == 2) {
            return redirect()->route('cargoexports');
        } else {
            return redirect()->route('cargolocales');
        } */
        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'cargo';
        $modelActivities->related_id = $rid;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function viewcargo($rid, $id, $flag = null)
    {
        if ($flag == 'fromNotification') {
            Cargo::where('id', $rid)->update(['display_notification_admin' => 0]);
        }

        $model = Cargo::find($rid);
        $activityData = DB::table('activities')->where('related_id', $rid)->where('type', 'cargo')->orderBy('id', 'desc')->get()->toArray();
        //$dataExpense = DB::table('expenses')->where('cargo_id',$rid)->where('deleted',0)->orderBy('expense_id', 'desc')->get();
        $dataExpense = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('cargo_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('cargo_id', $rid)
            ->orderBy('expense_id', 'desc')
            ->get();
        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.cargo_id', $rid)
            ->whereNull('invoices.housefile_module')
            ->orderBy('invoices.id', 'desc')->get();
        $totalInvoiceOfHTG = 0;
        $totalInvoiceOfUSD = 0;

        foreach ($invoices as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInvoiceOfUSD += $v->total;

            if ($v->currencyCode == 'HTG')
                $totalInvoiceOfHTG += $v->total;
        }

        $dataCargo = DB::table('cargo')->where('id', $rid)->first();
        $dataHawbIds = explode(',', $dataCargo->hawb_hbl_no);

        $HouseAWBData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->get();

        if ($model->cargo_operation_type == 1) {
            $path = 'Files/Cargo/Import/' . $model->file_number;
        } else if ($model->cargo_operation_type == 2) {
            $path = 'Files/Cargo/Export/' . $model->file_number;
        } else {
            $path = 'Files/Cargo/Local/' . $model->file_number;
        }

        $attachedFiles = DB::table('cargo_uploaded_files')->where('file_id', $rid)->where('flag_module', 'cargo')->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        /* $newArr = array();
        $files = Storage::disk('s3')->files($path);

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
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $rid)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $rid)
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

        //pre($getBillingAssociatedData);
        /* $getBillingAssociatedData = $getBillingItemData = DB::table('billing_items')
            ->select(DB::raw("CONCAT(billing_items.id,'-',costs.id) as fullcost"))
            ->join('costs', 'costs.cost_billing_code', '=', 'billing_items.id')
            ->get();

        $getCostsAssociatedData = $getBillingItemData = DB::table('costs')
            ->select(['costs.id as costItemId'])
            ->join('billing_items', 'billing_items.code', '=', 'costs.id')
            ->get(); */

        $getBillingItemData = DB::table('invoices')
            ->select(['invoice_item_details.fees_name as biliingItemId', 'invoice_item_details.fees_name_desc as biliingItemDescription', 'invoice_item_details.total_of_items as biliingItemAmount', 'currency.code as currencyCode', 'currency.code as billingCurrencyCode'])
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.cargo_id', $rid)
            ->where('invoices.deleted', '0')
            ->whereNull('housefile_module')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.cargo_id', $rid)
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


        $basicDetail = DB::table('cargo')->where('id', $rid)->first();

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        $checkData = DB::table('check_guarantee_to_pay')->where('master_cargo_id', $rid)->where('deleted', '0')->get();

        return view('cargo.view', ['model' => $model, 'id' => $id, 'activityData' => $activityData, 'rid' => $rid, 'dataExpense' => $dataExpense, 'invoices' => $invoices, 'HouseAWBData' => $HouseAWBData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'path' => $path, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'basicDetail' => $basicDetail, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'finalReportData' => $finalReportData, 'checkData' => $checkData]);
    }

    public function cargoexpensedetail($rid, $id)
    {
        $model = Cargo::find($rid);
        $dataExpense = DB::table('expenses')->where('cargo_id', $rid)->where('deleted', 0)->get();
        return view('cargo.cargoexpensedetail', ['model' => $model, 'id' => $id, 'dataExpense' => $dataExpense, 'rid' => $rid]);
    }

    public function invoicedetail($rid, $id)
    {
        $model = Cargo::find($rid);
        return view('cargo.invoicedetail', ['model' => $model, 'id' => $id, 'rid' => $rid]);
    }

    public function costdetail($rid, $id)
    {
        $model = Cargo::find($rid);
        return view('cargo.costdetail', ['model' => $model, 'id' => $id, 'rid' => $rid]);
    }

    public function reportdetail($rid, $id)
    {
        $model = Cargo::find($rid);
        return view('cargo.reportdetail', ['model' => $model, 'id' => $id, 'rid' => $rid]);
    }

    public function shipmentoutsidefiltering()
    {
        $fromDate = date('Y-m-d', strtotime($_POST['fromDate']));
        $toDate = date('Y-m-d', strtotime($_POST['toDate']));
        Session::put('cargoListingFromDate', $_POST['fromDate']);
        Session::put('cargoListingToDate', $_POST['toDate']);
        $cargos = DB::table('cargo')->where('deleted', '0')->whereBetween('opening_date', array($fromDate, $toDate))->orderBy('id', 'desc')->get();
        return view("cargo.cargoallajax", ['cargos' => $cargos, 'fromDate' => $fromDate, 'toDate' => $toDate, 'flagFileType' => '']);
    }

    public function getcargodata()
    {
        $id = $_POST['cargoId'];
        $aAr = array();
        $dataBilling = DB::table('cargo')->where('id', $id)->first();

        $dataClientConsignee = DB::table('clients')->where('id', $dataBilling->consignee_name)->first();
        $dataClientShipper = DB::table('clients')->where('id', $dataBilling->shipper_name)->first();

        $aAr['consigneeName'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
        $aAr['shipperName'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
        $aAr['cargo_operation_type'] = $dataBilling->cargo_operation_type;
        $aAr['billing_party'] = $dataBilling->billing_party;
        return json_encode($aAr);
    }

    public function expandhawbnumber()
    {
        $cargoid = $_POST['cargoid'];
        $rowId = $_POST['rowId'];

        if (!empty($_POST['flagcargo'])) {
            $flagCargo = $_POST['flagcargo'];
        } else {
            $flagCargo = '';
        }

        $dataCargo = DB::table('cargo')->where('id', $cargoid)->first();
        $dataHawbIds = explode(',', $dataCargo->hawb_hbl_no);

        $packageData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->get();
        return view('cargo.renderhawbnumbers', ['packageData' => $packageData, 'rowId' => $rowId, 'flagCargo' => $flagCargo]);
    }

    public function printcargofile($cargoId, $cargoType)
    {
        $model = DB::table('cargo')->where('id', $cargoId)->first();
        if ($cargoType == 1) {
            $pdf = PDF::loadView('cargo.printimport', ['model' => $model]);
        } else if ($cargoType == 2) {
            $pdf = PDF::loadView('cargo.printexport', ['model' => $model]);
        } else {
            $pdf = PDF::loadView('cargo.printlocale', ['model' => $model]);
        }

        $pdf_file = $model->file_number . '_expense.pdf';
        $pdf_path = 'public/cargoFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function gettotalweightvolumeandpieces()
    {
        $ids = $_POST['selectedAWB'];
        $ids = explode(',', $ids);
        $packageData = DB::table('hawb_packages')->whereIn('hawb_id', $ids)->get();
        $weight = 0.00;
        $volumne = 0.00;
        $peices = 0;
        foreach ($packageData as $key => $value) {
            $measure_weight = $value->measure_weight;
            if ($measure_weight == 'p') {
                $value->pweight = ($value->pweight) / 2.20462;
            }
            $measure_volume = $value->measure_volume;
            if ($measure_volume == 'f') {
                $value->pvolume = ($value->pvolume) / 35.3147;
            }
            $weight += $value->pweight;
            $volumne += $value->pvolume;
            $peices += $value->ppieces;
        }
        $data['weight'] = number_format($weight, 2);
        $data['volume'] = number_format($volumne, 2);
        $data['pieces'] = $peices;
        return json_encode($data);
    }

    public function gettotalweightpiecesinexport()
    {
        $ids = $_POST['selectedAWB'];
        $ids = explode(',', $ids);
        //$package = DB::table('hawb_files')->whereIn('id',$ids)->get();
        $packageData = DB::table('hawb_packages')->whereIn('hawb_id', $ids)->get();
        $weight = 0.00;
        $peices = 0;
        foreach ($packageData as $key => $value) {
            $measure_weight_export = $value->measure_weight;
            if ($measure_weight_export == 'p') {
                $value->pweight = ($value->pweight) / 2.20462;
            }
            $weight += $value->pweight;
            $peices += $value->ppieces;
        }
        $data['weight'] = number_format($weight, 2);
        $data['pieces'] = $peices;
        return json_encode($data);
    }
    public function sendMail(Request $request)
    {
        $emaildata = [];
        $cargoId = $request->get('cargoId');

        $cargoData = DB::table('cargo')->where('id', $cargoId)->first();

        if ($cargoData->billing_party == '') {
            $send = "fail";
        } else {
            $cargoEmail = DB::table('clients')->where('id', $cargoData->billing_party)->first();
            $fileName = $cargoData->file_number;
            $emaildata['email'] = $cargoEmail->email;
            $emaildata['file_number'] = $cargoData->file_number;
            $pdf_file = $emaildata['file_number'] . '_local.pdf';
            $pdf_path = 'public/cargoFilePdf/' . $pdf_file;

            $emaildata['invoiceAttachment'] = $pdf_path;

            Mail::to($emaildata['email'])->send(new sendCargoInfoMail($emaildata));

            if (!Mail::failures()) {
                $send = "Mail has been sent successfully.";
            }
        }
        //pre($cargoData->billing_party);

        echo $send;
    }

    public function storeLocalinvoice($input)
    {
        if (date('Y-m-d') == $input['opening_date']) {
            if ($input['cargo_operation_type'] == 3) {
                $invoiceInput['type_flag'] = 'LOCALE';
            }
            $invoiceInput['cargo_id'] = $input['cargo_id'];
            $clients_mail = Clients::where('id', $input['billing_party'])->first();
            $invoiceInput['bill_to'] = $input['billing_party'];
            $invoiceInput['email'] = $clients_mail->email;
            $invoiceInput['telephone'] = $clients_mail->phone_number;
            $invoiceInput['currency'] = '1';
            $invoiceInput['date'] = $input['opening_date'];
            $invoiceInput['consignee_address'] = $input['consignee_address'];
            $invoiceInput['file_no'] = $input['file_number'];
            $invoiceInput['awb_no'] = $input['awb_bl_no'];
            //$invoiceInput['total'] = $input['rental_cost'] * $input['contract_months'];
            $invoiceInput['total'] = $input['rental_cost'];
            $invoiceInput['sub_total'] = $invoiceInput['total'];
            $invoiceInput['balance_of'] = $invoiceInput['total'];
            if ($input['rental_paid_status'] == 'up') {
                $invoiceInput['payment_status'] = 'Pending';
            } else {
                $invoiceInput['payment_status'] = 'Paid';
            }

            $model = new Invoices;
            $dataAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->get()->pluck('awb_bl_no', 'id');
            $dataBillingItems = DB::table('billing_items')->select('id', 'item_code', 'description')->where('item_code', '1058')->first();
            $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
            if (empty($getLastInvoice)) {
                $model->bill_no = 'CA-5001';
            } else {
                $ab = 'CA-';
                $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                $model->bill_no = $ab;
            }

            $cargoId = $input['cargo_id'];
            if (!empty($cargoId)) {
                $model->file_number = $cargoId;
                $model->cargo_id = $cargoId;
            }
            $invoiceInput['bill_no'] = $model->bill_no;
            $invoiceInput['created_by'] = auth()->user()->id;
            $invoiceInput['created_at'] = date('Y-m-d h:i:s');
            $dataInvoices = Invoices::create($invoiceInput);

            $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
            $dataInvoiceItems['fees_name'] = !empty($dataBillingItems) ? $dataBillingItems->id : '';
            $dataInvoiceItems['item_code'] = !empty($dataBillingItems) ? $dataBillingItems->item_code : '';
            //$dataInvoiceItems['fees_name_desc'] = 'Loyer ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date']));
            $dataInvoiceItems['fees_name_desc'] = !empty($dataBillingItems) ? $dataBillingItems->description . ' ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date'])) : 'Loyer ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date']));
            $dataInvoiceItems['quantity'] = 1.00;
            $dataInvoiceItems['unit_price'] = $input['rental_cost'];
            $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
            InvoiceItemDetails::create($dataInvoiceItems);

            $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $dataInvoices]);
            $pdf_file = 'printCargoInvoice_' . $dataInvoices->id . '.pdf';
            $pdf_path = 'public/cargoInvoices/' . $pdf_file;
            $pdf->save($pdf_path);

            $emaildata['email'] = [$clients_mail->email, $clients_mail->email_two, $clients_mail->email_three];
            $emaildata['invoiceAttachment'] = $pdf_path;
            if ($emaildata['email'])
                Mail::to(array_filter($emaildata['email']))->send(new sendCashierInvoiceMail($emaildata));

            $localFileDate = $input['opening_date'];
            $localInvoice['total'] = $input['rental_cost'];
            $localInvoice['local_invoice_id'] = $input['cargo_id'];
            $localInvoice['status'] = 'p';
            $localInvoice['mail_send'] = '1';
            $localInvoice['created_by'] = auth()->user()->id;
            $localInvoice['created_at'] = gmdate('Y-m-d H:i:s');

            $localInvoice['duration'] = date('d-m-Y', strtotime($localFileDate)) . ' TO ';
            $localFileDate = date('d-m-Y', strtotime('+1month', strtotime($localFileDate)));
            $localInvoice['date'] = date('Y-m-d', strtotime($localFileDate));
            $localInvoice['duration'] = $localInvoice['duration'] . $localFileDate;
            localInvoicePayment::Create($localInvoice);
        }
    }

    public function cronforgeneratelocalinvoicemonthly()
    {
        $localUnclosedFiles = DB::table('cargo')->select('id', 'opening_date', 'billing_party', 'consignee_name', 'consignee_address', 'file_number', 'awb_bl_no', 'rental_cost', 'rental_ending_date')->where('rental', '1')->where('rental_paid_status', 'up')->where('deleted', '0')->where('rental_ending_date', '>=', date('Y-m-d'))->orderBy('id', 'asc')
            //->limit(1)
            ->get();
        //pre($localUnclosedFiles, 1);
        foreach ($localUnclosedFiles as $k => $v) {

            $dayOfOpeningDate = date('d', strtotime($v->opening_date));
            $today = date("Y-m-d");
            $month = date('m');
            $totalDaysMonth = date('t');

            if ($dayOfOpeningDate == date('d') || ($totalDaysMonth == date('d') && $dayOfOpeningDate > date('d'))) {
                if ($month == 01) {
                    $invoiceMonthStartDate = $today;
                    if ($dayOfOpeningDate > 28) {
                        $invoiceMonthEndDate = date("Y-m-d", strtotime("last day of +1 month", strtotime($invoiceMonthStartDate)));
                    } else {
                        $invoiceMonthEndDate = date("Y-m-d", strtotime("+1month", strtotime($invoiceMonthStartDate)));
                    }
                } else if ($month == 02) {
                    if ($dayOfOpeningDate > 28) {
                        $invoiceMonthStartDate = date('Y') . '-' . $month . date('t', strtotime(date('Y') . '-02-01'));
                    } else {
                        $invoiceMonthStartDate = $today;
                    }
                    $invoiceMonthEndDate = date('Y') . '-03-' . $dayOfOpeningDate;
                    //$invoiceMonthEndDate = date("Y-m-d", strtotime("+1month", strtotime($invoiceMonthStartDate)));
                } else {
                    $invoiceMonthStartDate = $today;
                    $invoiceMonthEndDate = date("Y-m-d", strtotime("+1month", strtotime($invoiceMonthStartDate)));
                }

                /* pre($invoiceMonthStartDate,1);
                pre($invoiceMonthEndDate); */

                $invoiceInput['type_flag'] = 'LOCALE';
                $invoiceInput['cargo_id'] = $v->id;
                $invoiceInput['bill_to'] = $v->billing_party;
                $invoiceInput['currency'] = '1';
                $invoiceInput['date'] = $invoiceMonthStartDate;
                $clients_mail = Clients::where('id', $v->billing_party)->first();
                $invoiceInput['email'] = $clients_mail->email;
                $invoiceInput['telephone'] = $clients_mail->phone_number;
                $invoiceInput['consignee_address'] = $v->consignee_address;
                $invoiceInput['file_no'] = $v->file_number;
                $invoiceInput['awb_no'] = $v->awb_bl_no;
                //$invoiceInput['total'] = $input['rental_cost'] * $input['contract_months'];
                $invoiceInput['total'] = $v->rental_cost;
                $invoiceInput['sub_total'] = $invoiceInput['total'];
                $invoiceInput['balance_of'] = $invoiceInput['total'];
                $invoiceInput['payment_status'] = 'Pending';

                $dataBillingItems = DB::table('billing_items')->select('id', 'item_code', 'description')->where('item_code', '1058')->first();

                $model = new Invoices;
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $model->bill_no = 'CA-5001';
                } else {
                    $ab = 'CA-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $model->bill_no = $ab;
                }

                $invoiceInput['bill_no'] = $model->bill_no;
                //$invoiceInput['created_by'] = auth()->user()->id;
                $invoiceInput['created_at'] = date('Y-m-d h:i:s');
                $dataInvoices = Invoices::create($invoiceInput);

                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                $dataInvoiceItems['fees_name'] = !empty($dataBillingItems) ? $dataBillingItems->id : '';
                $dataInvoiceItems['item_code'] = !empty($dataBillingItems) ? $dataBillingItems->item_code : '';
                //$dataInvoiceItems['fees_name_desc'] = 'Loyer ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date']));
                $dataInvoiceItems['fees_name_desc'] = !empty($dataBillingItems) ? $dataBillingItems->description . ' ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date'])) : 'Loyer ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date']));
                $dataInvoiceItems['quantity'] = 1.00;
                $dataInvoiceItems['unit_price'] = $v->rental_cost;
                $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                InvoiceItemDetails::create($dataInvoiceItems);

                $pdf = PDF::loadView('invoices.printcargoinvoice', ['invoice' => $dataInvoices]);
                $pdf_file = 'printCargoInvoice_' . $dataInvoices->id . '.pdf';
                $pdf_path = 'public/cargoInvoices/' . $pdf_file;
                $pdf->save($pdf_path);

                $emaildata['email'] = [$clients_mail->email, $clients_mail->email_two, $clients_mail->email_three];
                $emaildata['invoiceAttachment'] = $pdf_path;
                if ($emaildata['email'])
                    Mail::to(array_filter($emaildata['email']))->send(new sendCashierInvoiceMail($emaildata));

                // Store invoice activity on file level
                $modelActivities = new Activities;
                $modelActivities->type = 'cargo';
                $modelActivities->related_id = $v->id;
                //$modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();

                $localFileDate = $invoiceMonthStartDate;
                $localInvoice['total'] = $v->rental_cost;
                $localInvoice['local_invoice_id'] = $v->id;
                $localInvoice['status'] = 'p';
                $localInvoice['mail_send'] = '1';
                //$localInvoice['created_by'] = auth()->user()->id;
                $localInvoice['created_at'] = gmdate('Y-m-d H:i:s');

                $localInvoice['duration'] = date('d-m-Y', strtotime($localFileDate)) . ' TO ';
                //$localFileDate = date('d-m-Y', strtotime('+1month', strtotime($localFileDate)));
                $localFileDate = date('d-m-Y', strtotime($invoiceMonthEndDate));
                $localInvoice['date'] = date('Y-m-d', strtotime($localFileDate));
                $localInvoice['duration'] = $localInvoice['duration'] . $localFileDate;
                localInvoicePayment::Create($localInvoice);
            }





            /* $dataOfGeneratedInvoice = DB::table('local_invoice_payment_detail')->select('date')->where('local_invoice_id', $v->id)->orderBy('id', 'desc')->first();

            if (date('Y-m-d') >= $dataOfGeneratedInvoice->date && date('Y-m-d') < $v->rental_ending_date)
            {
                $invoiceInput['type_flag'] = 'Local';
                $invoiceInput['cargo_id'] = $v->id;
                $invoiceInput['bill_to'] = $v->billing_party;
                $invoiceInput['currency'] = '1';
                $invoiceInput['date'] = $dataOfGeneratedInvoice->date;
                $clients_mail = Clients::where('id', $v->consignee_name)->first();
                $invoiceInput['email'] = $clients_mail->email;
                $invoiceInput['consignee_address'] = $v->consignee_address;
                $invoiceInput['file_no'] = $v->file_number;
                $invoiceInput['awb_no'] = $v->awb_bl_no;
                //$invoiceInput['total'] = $input['rental_cost'] * $input['contract_months'];
                $invoiceInput['total'] = $v->rental_cost;
                $invoiceInput['sub_total'] = $invoiceInput['total'];
                $invoiceInput['balance_of'] = $invoiceInput['total'];
                $invoiceInput['payment_status'] = 'Pending';

                $model = new Invoices;
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $model->bill_no = 'CA-5001';
                } else {
                    $ab = 'CA-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $model->bill_no = $ab;
                }

                $invoiceInput['bill_no'] = $model->bill_no;
                $invoiceInput['created_by'] = auth()->user()->id;
                $invoiceInput['created_at'] = date('Y-m-d h:i:s');
                $dataInvoices = Invoices::create($invoiceInput);

                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                $dataInvoiceItems['fees_name'] = '';
                $dataInvoiceItems['item_code'] = '';
                $dataInvoiceItems['fees_name_desc'] = 'Loyer ' . date('M', strtotime($invoiceInput['date'])) . ' ' . date('Y', strtotime($invoiceInput['date']));
                $dataInvoiceItems['quantity'] = 1.00;
                $dataInvoiceItems['unit_price'] = $v->rental_cost;
                $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
                InvoiceItemDetails::create($dataInvoiceItems);

                $localFileDate = $dataOfGeneratedInvoice->date;
                $localInvoice['total'] = $v->rental_cost;
                $localInvoice['local_invoice_id'] = $v->id;
                $localInvoice['status'] = 'p';
                $localInvoice['mail_send'] = '1';
                $localInvoice['created_by'] = auth()->user()->id;
                $localInvoice['created_at'] = gmdate('Y-m-d H:i:s');

                $localInvoice['duration'] = date('d-m-Y', strtotime($localFileDate)) . ' TO ';
                $localFileDate = date('d-m-Y', strtotime('+1month', strtotime($localFileDate)));
                $localInvoice['date'] = date('Y-m-d', strtotime($localFileDate));
                $localInvoice['duration'] = $localInvoice['duration'] . $localFileDate;
                localInvoicePayment::Create($localInvoice);
            } */
        }
    }

    public function getDate(Request $request)
    {
        $months = $request->get('months');
        $date = $request->get('date');
        //pre($months);
        /* $flag = $request->get('flage');
        if ($flag = 'bm') {
            for ($i = 0; $i < $months; $i++) {
                $date = date('d-m-Y', strtotime('+1months', strtotime($date)));
            }
        } else {
            for ($i = 0; $i < $months; $i++) {
                $date = date('d-m-Y', strtotime('+1months', strtotime($date)));
            }
            //$date = date('d-m-Y', strtotime('+'.$months.'month', strtotime($date)));
        } */
        $date = date('d-m-Y', strtotime('+' . $months . 'month', strtotime($date)));
        return $date;
    }

    public function sendinvoiceoflocalstorage()
    {
        $date = "2019-04-27";
        for ($i = 0; $i < 4; $i++) {
            $date = date("Y-m-d", strtotime('+1months', strtotime($date)));
            echo $date . "<br>";
        }
        //$openingDate = "2019-03-31";
        //$oldMonth = date('m',strtotime($openingDate));
        //$date = date("d-m-Y",strtotime('+1months',strtotime($openingDate)));
        //echo $date."<br>";
        $today = "2019-07-13";
        $currentMonth = date('m', strtotime($today));
        // $diff = date_diff(date_create($today),date_create($openingDate));
        // echo ($diff->format('%a'))."<br>";

        $monthArr = Config::get('app.monthsPrefixZero');
        $localFileData = DB::table('cargo')->where('rental', '1')->where('cargo_operation_type', '3')->where('opening_date', '!=', '')->orderBy('id', 'DESC')->get();

        //pre($localFileData);
        if (count($localFileData) > 0) {

            foreach ($localFileData as $localFileData) {
                //pre($localFileData->id);
                $cont_over_or_not = strtotime($localFileData->rental_ending_date) - strtotime($today);
                $cont_over_or_not_diff = round($cont_over_or_not / (60 * 60 * 24));
                //pre($cont_over_or_not_diff);
                if ($cont_over_or_not_diff >= 0) {
                    $localInvoiceData = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $localFileData->id)->get();
                    if ($localFileData->rental_paid_status == 'up') {
                        if (count($localInvoiceData) <= 0) {

                            $input['local_invoice_id'] = $localFileData->id;
                            $input['date'] = $today;
                            $input['total'] = $localFileData->rental_cost;
                            $input['status'] = 'up';
                            $input['mail_send'] = '1';
                            $input['created_by'] = auth()->user()->id;
                            $input['created_at'] = date('Y-m-d h:i:s');
                            $openingDate = $localFileData->opening_date;
                            $oldMonth = date('m', strtotime($openingDate));
                            $client_detail = Clients::where('company_name', $localFileData->consignee_name)->first();
                            $client_mail = $client_detail->email;
                            $file_path = "public/cargoFilePdf/" . $localFileData->file_number . "_expense.pdf";
                            $invoiceData['invoiceAttachment'] = $file_path;
                            $invoiceData['total'] = $localFileData->rental_cost;
                            $invoiceData['company_name'] = $localFileData->consignee_name;
                            $input['duration'] = date('d-m-Y', strtotime($openingDate)) . ' TO ' . date('d-m-Y', strtotime($today));
                            $modelActivities = new Activities;
                            $modelActivities->type = 'localInvoicePayment';
                            $modelActivities->related_id = $localFileData->id;
                            $modelActivities->user_id = auth()->user()->id;
                            $modelActivities->description = 'Invoice for the duration ' . date('d-m-Y', strtotime($openingDate)) . ' to ' . date('d-m-Y', strtotime($today)) . ' has been generated';
                            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                            //pre ($openingDate);
                            $this->perfectDate($monthArr, $openingDate, $today, $oldMonth, $client_mail, $currentMonth, $input, $invoiceData, $modelActivities);
                        } else {
                            $localInvoiceData = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $localFileData->id)->where('mail_send', '1')->orderBy('id', 'DESC')->first();
                            //pre($localInvoiceData);
                            $pendingCount = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $localFileData->id)->where('status', 'up')->count();
                            $totalPayment = $localFileData->rental_cost * ($pendingCount + 1);
                            $input['local_invoice_id'] = $localFileData->id;
                            $input['date'] = $today;
                            $input['total'] = $localFileData->rental_cost;
                            $input['status'] = 'up';
                            $input['mail_send'] = '1';
                            $input['created_by'] = auth()->user()->id;
                            $input['created_at'] = date('Y-m-d h:i:s');
                            $openingDate = $localInvoiceData->date;
                            $oldMonth = date('m', strtotime($openingDate));
                            $client_detail = Clients::where('company_name', $localFileData->consignee_name)->first();
                            $client_mail = $client_detail->email;
                            $file_path = "public/cargoFilePdf/" . $localFileData->file_number . "_expense.pdf";
                            $invoiceData['invoiceAttachment'] = $file_path;
                            $totalPayment = $localFileData->rental_cost * ($pendingCount + 1);
                            $invoiceData['total'] = $totalPayment;
                            $invoiceData['company_name'] = $localFileData->consignee_name;
                            //pre($invoiceData['company_name']);

                            $input['duration'] = date('d-m-Y', strtotime($openingDate)) . ' TO ' . date('d-m-Y', strtotime($today));
                            $modelActivities = new Activities;
                            $modelActivities->type = 'localInvoicePayment';
                            $modelActivities->related_id = $localFileData->id;
                            $modelActivities->user_id = auth()->user()->id;
                            $modelActivities->description = 'Invoice for the duration ' . date('d-m-Y', strtotime($openingDate)) . ' to ' . date('d-m-Y', strtotime($today)) . ' has been generated';
                            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                            echo "again.<br>";
                            $this->perfectDate($monthArr, $openingDate, $today, $oldMonth, $client_mail, $currentMonth, $input, $invoiceData, $modelActivities);
                        }
                    }
                }
            }
        }
    }

    //perfectDate($monthArr,$openingDate,$today,$oldMonth,$currentMonth);
    public function perfectDate($monthArr, $openingDate, $today, $oldMonth, $client_mail, $currentMonth, $input, $invoiceData, $modelActivities)
    {
        foreach ($monthArr as $key => $item) {
            if ($currentMonth == $key) {
                $currentMonth = $monthArr[$key];
                echo $currentMonth . "<br>";
            }

            if ($oldMonth == $key) {
                $oldMonth = $monthArr[$key];
                echo $oldMonth . "<br>";
            }
        }
        $diff = date_diff(date_create($today), date_create($openingDate));
        $diffOfDate = $diff->format('%a');
        if (($oldMonth >= 1 && $oldMonth <= 7) && ($currentMonth >= 2 && $currentMonth <= 8)) {
            echo "You are in." . "<br>";
            //echo $currentMonth % 2;
            if ($oldMonth % 2 == 0 && $currentMonth % 2 != 0) {
                if ($oldMonth == 2) {
                    if ($diffOfDate == 28 || $diffOfDate == 29) {
                        if (date('d', strtotime($today)) == date('d', strtotime($openingDate))) {
                            $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);

                            echo "Now you are finally in February condition.<br>";
                        } else {
                        }
                    } else {
                    }
                } else if ($diffOfDate == 30) {
                    $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);
                    echo "Done here for 30 days.<br>";
                } else {
                }
            } else if ($oldMonth % 2 != 0 && $currentMonth % 2 == 0) {
                echo "Done for odd even conndition.<br>";
                if ($diffOfDate == 31) {
                    $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);

                    echo "Done for 31 Days.<br>";
                }
            } else if ($oldMonth % 2 != 0 && $currentMonth % 2 != 0) {
                if ($diffOfDate == 31) {
                    $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);
                    echo "Done for 31 Days when date is 31.<br>";
                }
            } else {
            }
        } else if (($oldMonth >= 8 && $oldMonth <= 12)) {
            echo "You are now in second senario." . "<br>";
            if ($oldMonth % 2 == 0 && $currentMonth % 2 != 0) {
                echo "done for august condition.(E-O)<br>";
                if ($diffOfDate == 31) {
                    $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);

                    echo "Done for august condition for 31 days.(E-O)<br>";
                } else {
                }
            } else if ($oldMonth % 2 == 0 && $currentMonth % 2 == 0) {
                echo "Done for august condition(E-E).<br>";
                if ($diffOfDate == 31) {
                    $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);

                    echo "Done for august condition for 31 days.(E-E)<br>";
                }
            } else if ($oldMonth % 2 != 0 && $currentMonth % 2 == 0) {

                if ($diffOfDate == 30) {
                    $this->send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail);

                    echo "Done for august condition for 30 days.(O-E)<br>";
                }
            }
        } else {
        }
    }

    public function send_monthly_mail($input, $invoiceData, $modelActivities, $client_mail)
    {
        echo "Email Sent Before";
        $checkLocal = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $input['local_invoice_id'])->where('date', $input['date'])->orderBy('id', 'DESC')->first();

        Mail::to($client_mail)->send(new monthlyInvoiceMail($invoiceData));
        if (empty($checkLocal)) {
            localInvoicePayment::create($input);
        } else {
        }

        $modelActivities->save();
        echo "Hello";
    }

    public function checkuniqueawbnumber()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit') {
            $upsData = DB::table('cargo')->where('deleted', '0')->where('awb_bl_no', $number)->where('id', '<>', $id)->whereNotNull('awb_bl_no')->count();
        } else {
            $upsData = DB::table('cargo')->where('deleted', '0')->where('awb_bl_no', $number)->whereNotNull('awb_bl_no')->count();
        }

        if ($upsData) {
            return 1;
        } else {
            return 0;
        }
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoEdit = User::checkPermission(['update_cargo'], '', auth()->user()->id);
        $permissionCargoDelete = User::checkPermission(['delete_cargo'], '', auth()->user()->id);
        $permissionCargoExpensesAdd = User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
        $permissionCargoInvoicesAdd = User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $cargoFileType = $req['cargoFileType'];
        $localRentalType = $req['localRentalType'];
        $cargoConsolidateType = $req['cargoConsolidateType'];
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

        $orderby = ['cargo.id', 'cargo.id', 'file_number', 'c3.company_name', 'cargo_master_scan_status', 'users.name', 'opening_date', 'awb_bl_no', 'c1.company_name', 'c2.company_name'];

        $total = Cargo::selectRaw('count(*) as total');
        //->where('deleted', '0');
        if (!empty($fileStatus)) {
            $total = $total->where('cargo_master_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($cargoFileType)) {
            $total = $total->where('cargo_operation_type', $cargoFileType);
        }
        if (!empty($cargoConsolidateType) || $cargoConsolidateType == '0') {
            $total = $total->where('consolidate_flag', $cargoConsolidateType);
        }
        if ($cargoFileType == 3 && (!empty($localRentalType) || $localRentalType == '0')) {
            $total = $total->where('rental', $localRentalType);
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('cargo')
            ->selectRaw('cargo.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'cargo.billing_party')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id');
        //->where('cargo.deleted', '0');
        if (!empty($fileStatus)) {
            $query = $query->where('cargo_master_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($cargoFileType)) {
            $query = $query->where('cargo_operation_type', $cargoFileType);
        }
        if (!empty($cargoConsolidateType)) {
            $query = $query->where('consolidate_flag', $cargoConsolidateType);
        }
        if ($cargoFileType == 3 && (!empty($localRentalType) || $localRentalType == '0')) {
            $query = $query->where('rental', $localRentalType);
        }
        $filteredq = DB::table('cargo')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'cargo.billing_party')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id');
        //->where('cargo.deleted', '0');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('cargo_master_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('opening_date', array($fromDate, $toDate));
        }
        if (!empty($cargoFileType)) {
            $filteredq = $filteredq->where('cargo_operation_type', $cargoFileType);
        }
        if (!empty($cargoConsolidateType)) {
            $filteredq = $filteredq->where('consolidate_flag', $cargoConsolidateType);
        }
        if ($cargoFileType == 3 && (!empty($localRentalType) || $localRentalType == '0')) {
            $filteredq = $filteredq->where('rental', $localRentalType);
        }
        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $dataBillingParty = app('App\Clients')->getClientData($value->billing_party);
            $agentData = app('App\User')->getUserName($value->agent_id);
            $consigneeData = app('App\Clients')->getClientData($value->consignee_name);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $shipperData = app('App\Clients')->getClientData($value->shipper_name);
            $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
            $agent = !empty($agentData->name) ? $agentData->name : '-';
            $invoiceNumbers = Expense::getInvoicesOfFile($value->id, $value->cargo_operation_type);

            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printcargofile", [$value->id, $value->cargo_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete = route('deletecargo', [$value->id, $value->cargo_operation_type]);
            $edit = route('editcargo', [$value->id, $value->cargo_operation_type]);
            if ($value->deleted == '0') {
                if ($permissionCargoEdit) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }
                if ($permissionCargoDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['cargo', $value->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';
                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';
                if ($permissionCargoExpensesAdd) {
                    $countPending = 0;
                    $countPending = Expense::getPendingExpenses($value->id);
                    if ($value->cargo_operation_type != 3) {
                        $action .= '<li><a href="' . route('createexpenseusingawl', ['cargo', $value->id, 'flagFromListing']) . '">Add Expense</a></li>';
                    }
                }

                if ($permissionCargoInvoicesAdd) {
                    if ($value->cargo_operation_type != 3) {
                        $action .= '<li><a href="' . route('createinvoice', $value->id) . '">Add Invoice</a></li>';
                    }
                }

                if ($value->consolidate_flag == 1 && ($value->cargo_operation_type == 1 || $value->cargo_operation_type == 2)) {
                    $action .= '<li><button id="btnAddWarehouseInFile" data-module ="Warehouse" class="btnModalPopup" value="' . route('addwarehouseinfile', [$value->id, 'cargo']) . '">Add Warehouse</button></li>';
                }

                if ($value->cargo_operation_type == 3) {
                    $action .= '<li><a href="javascript:void(0)"  data-value="' . $value->id . '" class="sendmailonlocalfile">Send Invoice</a></li>';
                }

                if ($value->cargo_operation_type != 3) {
                    $action .= '<li><button id="btnAddCashCreditInFile" data-module ="Payment Mode" class="btnModalPopup" value="' . route('addcashcreditinfile', $value->id) . '">Add Payment Mode</button></li>';
                }

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['Cargo', $value->id]) . '">Close File</a></li>';
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

            $data[] = [$value->id, '', $value->file_number, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($value->cargo_master_scan_status) ? $value->cargo_master_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($value->cargo_master_scan_status) ? $value->cargo_master_scan_status : '-'] : '-', $agent, !empty($value->opening_date) ? date('d-m-Y', strtotime($value->opening_date)) : '-', !empty($value->awb_bl_no) ? $value->awb_bl_no : '-', $consignee, $shipper, $invoiceNumbers, ($value->file_close) == 1 ? $closedDetail : $action];
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
            $cargoId = $_POST['cargoid'];
            return Cargo::checkFileAssgned($cargoId);
        }
        if ($flag == 'checkHawbFiles') {
            $cargoId = $_POST['cargoid'];
            return json_encode(HawbFiles::checkHawbFiles($cargoId));
        }
        if ($flag == 'getCargoData') {
            $cargoId = $_POST['cargoid'];
            return json_encode(Cargo::getCargoData($cargoId));
        }
    }

    public function viewdifferencereport($cargoId = '')
    {
        /* $data = DB::table('cargo')
        ->select(['cargo.file_number', 'cargo.consignee_name', 'cargo.cargo_operation_type', 'invoices.total', 'invoices.id as invoice_id', 'invoice_item_details.fees_name_desc', 'billing_items.billing_name as revenue', 'costs.cost_name as costs_name', 'invoice_item_details.total_of_items as item_cost', 'expenses.expense_id as expenses_id', 'costs.id as costs_id'])
        ->leftjoin('invoices', 'cargo.id', '=', 'invoices.cargo_id')
        ->join('invoice_item_details', 'invoices.id', '=', 'invoice_item_details.invoice_id')
        ->join('billing_items', 'invoice_item_details.fees_name', '=', 'billing_items.id')
        ->leftjoin('costs', 'billing_items.code', '=', 'costs.id')
        ->leftjoin('expenses', 'expenses.cargo_id', '=', 'cargo.id')
        ->where('cargo.deleted', 0)
        ->where('expenses.deleted', 0)
        ->whereNotNull('invoices.cargo_id')
        ->where('cargo.id', $cargoId)
        ->where(function ($query) {
        $query->where('invoices.hawb_hbl_no', '==', '')
        ->orWhereNull('invoices.hawb_hbl_no');
        })
        ->groupBy('invoice_item_details.id')
        ->get(); */

        $getBillingAssociatedData = $getBillingItemData = DB::table('billing_items')
            ->select(DB::raw("CONCAT(billing_items.id,'-',costs.id) as fullcost"))
            ->join('costs', 'costs.cost_billing_code', '=', 'billing_items.id')
            ->get();

        $getCostsAssociatedData = $getBillingItemData = DB::table('costs')
            ->select(['costs.id as costItemId'])
            ->join('billing_items', 'billing_items.code', '=', 'costs.id')
            ->get();

        $getBillingItemData = DB::table('invoices')
            ->select(['invoice_item_details.fees_name as biliingItemId', 'invoice_item_details.fees_name_desc as biliingItemDescription', 'invoice_item_details.total_of_items as biliingItemAmount'])
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->where('invoices.cargo_id', $cargoId)
            ->where(function ($query) {
                $query->where('invoices.hawb_hbl_no', '=', '')
                    ->orWhereNull('invoices.hawb_hbl_no');
            })
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->where('expenses.cargo_id', $cargoId)
            ->get();

        $basicDetail = DB::table('cargo')->where('id', $cargoId)->first();

        /* for ($i = 0; $i < count($data); $i++) {
        $dataExpense = DB::table('expense_details')->where('expense_id', $data[$i]->expenses_id)->where('expense_type', $data[$i]->costs_id)->first();
        if (!empty($dataExpense)) {
        $data[$i]->expences_item_amount = $dataExpense->amount;
        } else {
        $data[$i]->expences_item_amount = 0.00;
        }
        } */

        return view('cargo.viewdifferencereport', ['basicDetail' => $basicDetail, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData]);
        //return view('cargo.viewdifferencereport', ['basicDetail' => $basicDetail,'data'=>$data]);

    }
}
