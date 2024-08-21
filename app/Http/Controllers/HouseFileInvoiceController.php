<?php

namespace App\Http\Controllers;

use App\HouseFileInvoice;
use Illuminate\Http\Request;


use App\Invoices;
use App\Clients;
use App\BillingItems;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use Illuminate\Support\Facades\DB;
use App\User;
use App\HouseFileInvoiceItemDetails;
use App\Activities;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceDetailMail;
use PDF;
use App\Mail\sendCashierInvoiceMail;
use App\Cargo;
use App\Ups;
use App\Aeropost;
use App\ccpack;
use App\Currency;
use App\HawbFiles;
use Illuminate\Support\Facades\Storage;
use App\InvoicePayments;

class HouseFileInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($flagModule = null)
    {
        if ($flagModule == 'cargo') {
            $checkPermission = User::checkPermission(['listing_cargo_house_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ups') {
            $checkPermission = User::checkPermission(['listing_courier_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'aeropost') {
            $checkPermission = User::checkPermission(['listing_aeropost_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ccpack') {
            $checkPermission = User::checkPermission(['listing_ccpack_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        }
        return view("housefile-invoices.index", ['flagModule' => $flagModule]);
    }


    public function getcargoinhousefileinvoiceforinputpicker()
    {
        //pre($_REQUEST,1);
        $limit = $_REQUEST['limit'];
        $start = ($_REQUEST['p'] - 1) * 10;
        $flagModule = $_REQUEST['flagModule'];
        $moduleId = $_REQUEST['moduleId'];
        $valueOfText = $_REQUEST['q'];

        if ($flagModule == 'cargo') {
            $total = Cargo::selectRaw('count(*) as total')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->where('cargo.deleted', 0)
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $total = $total->where('cargo.id', $moduleId);
            if (!empty($valueOfText)) {
                $total->where(function ($total) use ($valueOfText) {
                    $total->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
                });
            }
            $total = $total->first();

            $datas = DB::table('cargo')
                ->select('cargo.id', 'cargo.file_number', 'cargo.consignee_name', 'cargo.shipper_name')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->where('cargo.deleted', 0)
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $datas = $datas->where('cargo.id', $moduleId);
            if (!empty($valueOfText)) {
                $datas->where(function ($datas) use ($valueOfText) {
                    $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
                });
            }
            //$datas = $datas->get();
            $datas = $datas->offset($start)->limit($limit)->get();
            $NdataFileNumber = array();
            foreach ($datas as $k => $v) {
                $dataClientConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
                $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                $NdataFileNumber[$k]['value'] = $v->id;
                $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
                $NdataFileNumber[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
                $NdataFileNumber[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            }
        } else if ($flagModule == 'ups') {
            $total = Ups::selectRaw('count(*) as total')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', 0)
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $total = $total->where('ups_details.id', $moduleId);
            if (!empty($valueOfText)) {
                $total->where(function ($total) use ($valueOfText) {
                    $total->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
                });
            }
            $total = $total->first();

            $datas = DB::table('ups_details')
                ->select('ups_details.id', 'ups_details.file_number', 'ups_details.consignee_name', 'ups_details.shipper_name')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->where('ups_details.deleted', 0)
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $datas = $datas->where('ups_details.id', $moduleId);
            if (!empty($valueOfText)) {
                $datas->where(function ($datas) use ($valueOfText) {
                    $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
                });
            }
            //$datas = $datas->get();
            $datas = $datas->offset($start)->limit($limit)->get();
            $NdataFileNumber = array();
            foreach ($datas as $k => $v) {
                $dataClientConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
                $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                $NdataFileNumber[$k]['value'] = $v->id;
                $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
                $NdataFileNumber[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
                $NdataFileNumber[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            }
        } else if ($flagModule == 'aeropost') {
            $total = Aeropost::selectRaw('count(*) as total')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $total = $total->where('aeropost.id', $moduleId);
            if (!empty($valueOfText)) {
                $total->where(function ($total) use ($valueOfText) {
                    $total->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('from_address', 'like', '%' . $valueOfText . '%');
                });
            }
            $total = $total->first();

            $datas = DB::table('aeropost')
                ->select('aeropost.id', 'aeropost.file_number', 'aeropost.consignee', 'aeropost.from_address')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->where('aeropost.deleted', '0')
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $datas = $datas->where('aeropost.id', $moduleId);
            if (!empty($valueOfText)) {
                $datas->where(function ($datas) use ($valueOfText) {
                    $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('from_address', 'like', '%' . $valueOfText . '%');
                });
            }
            //$datas = $datas->get();
            $datas = $datas->offset($start)->limit($limit)->get();
            $NdataFileNumber = array();
            foreach ($datas as $k => $v) {
                $modelClients = new Clients();
                $data = $modelClients->getClientData($v->consignee);

                $NdataFileNumber[$k]['value'] = $v->id;
                $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
                $NdataFileNumber[$k]['consignee'] = !empty($data->company_name) ? $data->company_name : '-';
                $NdataFileNumber[$k]['shipper'] = !empty($v->from_address) ? $v->from_address : '-';
            }
        } else if ($flagModule == 'ccpack') {
            $total = ccpack::selectRaw('count(*) as total')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $total = $total->where('ccpack.id', $moduleId);
            if (!empty($valueOfText)) {
                $total->where(function ($total) use ($valueOfText) {
                    $total->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
                });
            }
            $total = $total->first();

            $datas = DB::table('ccpack')
                ->select('ccpack.id', 'ccpack.file_number', 'ccpack.consignee', 'ccpack.shipper_name')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->where('ccpack.deleted', '0')
                ->whereNull('file_close');
            if (!empty($moduleId) && empty($valueOfText))
                $datas = $datas->where('ccpack.id', $moduleId);
            if (!empty($valueOfText)) {
                $datas->where(function ($datas) use ($valueOfText) {
                    $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
                });
            }
            //$datas = $datas->get();
            $datas = $datas->offset($start)->limit($limit)->get();
            $NdataFileNumber = array();
            foreach ($datas as $k => $v) {
                $dataClientConsignee = DB::table('clients')->where('id', $v->consignee)->first();
                $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

                $NdataFileNumber[$k]['value'] = $v->id;
                $NdataFileNumber[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
                $NdataFileNumber[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
                $NdataFileNumber[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
            }
        }

        $json_data = array(
            "data"            => $NdataFileNumber,
            'count' => $total->total
        );
        return Response::json($json_data);
    }

    public function getcargohousefileinvoiceforinputpicker()
    {
        //pre($_REQUEST,1);
        $limit = $_REQUEST['limit'];
        $start = ($_REQUEST['p'] - 1) * 10;
        $valueOfText = $_REQUEST['q'];
        $houseId = $_REQUEST['houseId'];

        $total = HawbFiles::selectRaw('count(*) as total')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->where('hawb_files.deleted', 0)
            ->whereNull('file_close');
        if (!empty($houseId) && empty($valueOfText))
            $total = $total->where('hawb_files.id', $houseId);
        if (!empty($valueOfText)) {
            $total->where(function ($total) use ($valueOfText) {
                $total->where('file_number', 'like', '%' . $valueOfText . '%')
                    ->orWhere('hawb_hbl_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('export_hawb_hbl_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
            });
        }
        $total = $total->first();

        $datas = DB::table('hawb_files')
            ->select('hawb_files.id', 'hawb_files.file_number', 'hawb_files.consignee_name', 'hawb_files.shipper_name', 'hawb_files.hawb_hbl_no', 'hawb_files.export_hawb_hbl_no', 'hawb_files.cargo_operation_type')
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->where('hawb_files.deleted', 0)
            ->whereNull('file_close');
        if (!empty($houseId) && empty($valueOfText))
            $datas = $datas->where('hawb_files.id', $houseId);
        if (!empty($valueOfText)) {
            $datas->where(function ($datas) use ($valueOfText) {
                $datas->where('file_number', 'like', '%' . $valueOfText . '%')
                    ->orWhere('hawb_hbl_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('export_hawb_hbl_no', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $valueOfText . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $valueOfText . '%');
            });
        }
        //$datas = $datas->get();
        $datas = $datas->offset($start)->limit($limit)->get();
        $NdataFileNumber = array();
        foreach ($datas as $k => $v) {
            $dataHawbAll[$k]['value'] = $v->id;
            $dataHawbAll[$k]['file_number'] = !empty($v->file_number) ? $v->file_number : '-';
            $dataHawbAll[$k]['hawb_hbl_no'] = ($v->cargo_operation_type) == 1 ? $v->hawb_hbl_no : $v->export_hawb_hbl_no;

            $dataClientConsignee = DB::table('clients')->where('id', $v->consignee_name)->first();
            $dataClientShipper = DB::table('clients')->where('id', $v->shipper_name)->first();

            $dataHawbAll[$k]['consignee'] = !empty($dataClientConsignee->company_name) ? $dataClientConsignee->company_name : '-';
            $dataHawbAll[$k]['shipper'] = !empty($dataClientShipper->company_name) ? $dataClientShipper->company_name : '-';
        }

        $json_data = array(
            "data"            => $dataHawbAll,
            'count' => $total->total
        );
        return Response::json($json_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($flagModule = null, $flagInvoice = null, $houseId = null, $flagFromWhere = null)
    {
        if ($flagModule == 'cargo') {
            $checkPermission = User::checkPermission(['add_cargo_house_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ups') {
            $checkPermission = User::checkPermission(['add_courier_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'aeropost') {
            $checkPermission = User::checkPermission(['add_aeropost_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ccpack') {
            $checkPermission = User::checkPermission(['add_ccpack_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        }

        $model = new HouseFileInvoice;
        $NdataFileNumber = array();
        if ($flagModule == 'cargo') {
            if (!empty($houseId)) {
                $model->hawb_hbl_no = $houseId;
            }
        } else if ($flagModule == 'ups') {
        } else if ($flagModule == 'aeropost') {
        } else if ($flagModule == 'ccpack') {
        }


        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get()->pluck('billing_name', 'id');
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
        if (empty($getLastInvoice)) {
            if ($flagModule == 'cargo')
                $input['bill_no'] = 'HF-5001';
            else if ($flagModule == 'ups')
                $input['bill_no'] = 'UP-5001';
            else if ($flagModule == 'aeropost')
                $input['bill_no'] = 'AP-5001';
            else if ($flagModule == 'ccpack')
                $input['bill_no'] = 'CC-5001';
        } else {
            if ($flagModule == 'cargo')
                $ab = 'HF-';
            else if ($flagModule == 'ups')
                $ab = 'UP-';
            else if ($flagModule == 'aeropost')
                $ab = 'AP-';
            else if ($flagModule == 'ccpack')
                $ab = 'CC-';
            $ab .= substr($getLastInvoice->bill_no, 3) + 1;
            $model->bill_no = $ab;
        }

        $model->sent_on = date('Y-m-d');


        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        //$allUsers = json_decode($allUsers,1);
        //ksort($allUsers);

        $dataBillingItemsAutoComplete = BillingItems::getBillingItemsAutocomplete();
        $model->sub_total = '0.00';
        $model->tca = '0.00';
        $model->total = '0.00';
        $model->credits = '0.00';
        $model->balance_of = '0.00';
        $model->date = date('d-m-Y');
        if ($flagInvoice == '0')
            $flagInvoice = null;
        $model->flag_invoice = $flagInvoice;
        if ($flagInvoice == 'old')
            $model->bill_no = '';

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);

        return view('housefile-invoices.form', ['model' => $model, 'dataBillingItems' => $dataBillingItems, 'allUsers' => $allUsers, 'currency' => $currency, 'NdataFileNumber' => $NdataFileNumber, 'flagModule' => $flagModule, 'flagFromWhere' => $flagFromWhere, 'houseId' => $houseId, 'moduleId' => '']);
    }

    public function gethousedetailforinvoice()
    {
        $dataCargo = DB::table('hawb_files')
            ->select(['hawb_files.*', 'c1.company_name as consignee_full_name', 'c2.company_name as shipper_full_name'])
            ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
            ->where('hawb_files.id', $_POST['id'])->where('hawb_files.deleted', 0)->first();

        $dataCargo = (array) $dataCargo;


        $dataPackages = array();
        $dataPackages = DB::table('hawb_packages')->select(['pweight', 'pvolume', 'ppieces'])->where('hawb_id', $_POST['id'])->first();
        $dataPackages = (array) $dataPackages;

        $houseId = $_POST['id'];
        $dataMaster  = DB::table('cargo')
            ->select(DB::raw('id as MasterCargoFile'))
            ->whereRaw("find_in_set($houseId,hawb_hbl_no)")
            ->first();
        $dataMaster = (array) $dataMaster;


        $all = array_merge($dataCargo, $dataPackages, $dataMaster);
        return json_encode($all);
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
        if ($input['housefile_module'] == 'cargo')
            $fileData = DB::table('hawb_files')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'ups')
            $fileData = DB::table('ups_details')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'aeropost')
            $fileData = DB::table('aeropost')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'ccpack')
            $fileData = DB::table('ccpack')->where('file_number', $input['file_no'])->where('deleted', '0')->first();

        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);

        //$dataInvoice = DB::table('invoices')->where('bill_no',$input['bill_no'])->first();
        $dataInvoice = array();
        if (!empty($dataInvoice)) {
            $model = HouseFileInvoiceItemDetails::where('invoice_id', $dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = auth()->user()->id;
            $model = HouseFileInvoice::find($dataInvoice->id);

            $model->fill($request->input());
            Activities::log('update', 'housefileinvoice', $model);

            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $model->update($input);

            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new HouseFileInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';



            $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => $input]);
            $pdf_file = 'printInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
            $pdf->save($pdf_path);

            if ($input['housefile_module'] == 'cargo') {
                $s3path = 'Files/Cargo/';
                if ($fileData->cargo_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else if ($fileData->cargo_operation_type == 2) {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'ups') {
                $s3path = 'Files/Courier/Ups/';
                if ($fileData->courier_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'aeropost') {
                $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            } else if ($input['housefile_module'] == 'ccpack') {
                $s3path = 'Files/Courier/CCpack/';
                if ($fileData->ccpack_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            }



            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');

            /*$input['invoiceAttachment'] = $pdf_path;
            Mail::to($input['email'])->send(new InvoiceDetailMail($input));*/

            /*if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
            {
            $modelClient = Clients::where('id',$model->bill_to)->first();
            $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
            $modelClient->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            if($input['balance_of'] - $dataInvoice->balance_of < 0)
            {
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of).'-Credit Deposited.';
                $modelActivities->cash_credit_flag = '2';
            }else
            {
                $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of.'-Invoice payment.';
                $modelActivities->cash_credit_flag = '1';
            }
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
            }

            if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }*/

            Session::flash('flash_message', 'Record has been created successfully');
            return redirect('housefileinvoices');
        } else {
            if ($input['flag_invoice'] != 'old') {
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    if ($input['housefile_module'] == 'cargo')
                        $input['bill_no'] = 'HF-5001';
                    else if ($input['housefile_module'] == 'ups')
                        $input['bill_no'] = 'UP-5001';
                    else if ($input['housefile_module'] == 'aeropost')
                        $input['bill_no'] = 'AP-5001';
                    else if ($input['housefile_module'] == 'ccpack')
                        $input['bill_no'] = 'CC-5001';
                } else {
                    if ($input['housefile_module'] == 'cargo')
                        $ab = 'HF-';
                    else if ($input['housefile_module'] == 'ups')
                        $ab = 'UP-';
                    else if ($input['housefile_module'] == 'aeropost')
                        $ab = 'AP-';
                    else if ($input['housefile_module'] == 'ccpack')
                        $ab = 'CC-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $input['bill_no'] = $ab;
                }
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = HouseFileInvoice::create($input);
            Activities::log('create', 'housefileinvoice', $model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new HouseFileInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';


            $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => $input]);
            $pdf_file = 'printInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
            $pdf->save($pdf_path);

            $allowToGenerateInvoicePayment = 1;
            if ($input['housefile_module'] == 'cargo') {
                $s3path = 'Files/Cargo/';
                if ($fileData->cargo_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else if ($fileData->cargo_operation_type == 2) {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                } else {
                    $allowToGenerateInvoicePayment = 0;
                    $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'ups') {
                $s3path = 'Files/Courier/Ups/';
                if ($fileData->courier_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'aeropost') {
                $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            } else if ($input['housefile_module'] == 'ccpack') {
                $s3path = 'Files/Courier/CCpack/';
                if ($fileData->ccpack_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit_stop' && $allowToGenerateInvoicePayment == 1) {
                /* $modelClient->available_balance = $modelClient->available_balance - $model->balance_of;
                $modelClient->save(); */

                $availableBalance = $modelClient->available_balance;
                if ($availableBalance > '0.00' || $availableBalance > '0') {
                    $modelClient->available_balance = $availableBalance - $model->balance_of;
                    if ($modelClient->available_balance < 0) {
                        $modelClient->available_balance = 0.00;
                        $paymentAmount = $availableBalance;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Partial', 'balance_of' => $model->total - $paymentAmount]);
                    } else {
                        $paymentAmount = $model->balance_of;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    }
                    $modelClient->save();

                    if ($input['housefile_module'] == 'cargo') {
                        $inputInvoicePayment['house_file_id'] = $model->hawb_hbl_no;
                    } else if ($input['housefile_module'] == 'ups') {
                        $inputInvoicePayment['ups_id'] = $model->ups_id;
                    } else if ($input['housefile_module'] == 'aeropost') {
                        $inputInvoicePayment['aeropost_id'] = $model->aeropost_id;
                    } else if ($input['housefile_module'] == 'ccpack') {
                        $inputInvoicePayment['ccpack_id'] = $model->ccpack_id;
                    }

                    $inputInvoicePayment['invoice_id'] = $model->id;
                    $inputInvoicePayment['invoice_number'] = $model->bill_no;
                    $inputInvoicePayment['file_number'] = $fileData->file_number;
                    $inputInvoicePayment['amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_rate'] = '0.00';
                    $inputInvoicePayment['payment_via'] = 'DEPOSIT';
                    $inputInvoicePayment['payment_via_note'] = 'C/N';
                    $inputInvoicePayment['created_at'] = gmdate("Y-m-d H:i:s");
                    $inputInvoicePayment['client'] = $model->bill_to;
                    $inputInvoicePayment['payment_accepted_by'] = auth()->user()->id;

                    $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
                    if (empty($getLastReceiptNumber)) {
                        $receiptNumber = '11101';
                    } else {
                        if (empty($getLastReceiptNumber->receipt_number))
                            $receiptNumber = '11101';
                        else
                            $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
                    }

                    $inputInvoicePayment['receipt_number'] = $receiptNumber;
                    $modelInvoicePayment = InvoicePayments::create($inputInvoicePayment);

                    // Store deposite activities
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cashCreditClient';
                    $modelActivities->related_id = $model->bill_to;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = number_format($paymentAmount, 2) . '-Invoice Payment Paid. (Credit Used)';
                    $modelActivities->cash_credit_flag = '1';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }


            // Store deposite activities
            /* $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = number_format($model->balance_of, 2) . '-Invoice Generated.';
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save(); */

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'client';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            // Store invoice activity on file level
            $modelActivities = new Activities;
            $type = '';
            $relatedId = '';
            if ($input['housefile_module'] == 'cargo') {
                $type = 'houseFile';
                $relatedId = $model->hawb_hbl_no;
            } else if ($input['housefile_module'] == 'ups') {
                $type = 'ups';
                $relatedId = $model->ups_id;
            } else if ($input['housefile_module'] == 'aeropost') {
                $type = 'aeropost';
                $relatedId = $model->aeropost_id;
            } else if ($input['housefile_module'] == 'ccpack') {
                $type = 'ccpack';
                $relatedId = $model->ccpack_id;
            }
            $modelActivities->type = $type;
            $modelActivities->related_id = $relatedId;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            Session::flash('flash_message', 'Record has been created successfully');

            //return redirect()->route('housefileinvoices',$model->housefile_module);
            /* if ($model->flag_invoice == 'old')
                return redirect('oldinvoices');
            else
                return redirect('housefileinvoices/' . $model->housefile_module); */

            if ($model->flag_invoice == 'old')
                return redirect('oldinvoices');
            else {
                if ($input['flagFromWhere'] == 'flagFromView') {
                    if ($input['housefile_module'] == 'cargo') {
                        return redirect()->route('viewhawbfile', [$model->hawb_hbl_no]);
                    }
                } else
                    return redirect('housefileinvoices/' . $model->housefile_module);
            }
        }
    }

    public function storehouseinvoiceandprint(Request $request)
    {

        $input = $request->all();

        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);
        if ($input['housefile_module'] == 'cargo')
            $fileData = DB::table('hawb_files')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'ups')
            $fileData = DB::table('ups_details')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'aeropost')
            $fileData = DB::table('aeropost')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'ccpack')
            $fileData = DB::table('ccpack')->where('file_number', $input['file_no'])->where('deleted', '0')->first();
        $dataInvoice = DB::table('invoices')->where('bill_no', $input['bill_no'])->first();
        if ($input['saveandprintinupdate'] == '0')
            $dataInvoice = array();
        if (!empty($dataInvoice)) {
            $model = HouseFileInvoiceItemDetails::where('invoice_id', $dataInvoice->id)->delete();
            $input['updated_at'] = gmdate("Y-m-d H:i:s");
            $model = HouseFileInvoice::find($dataInvoice->id);
            $model->fill($request->input());
            Activities::log('update', 'housefileinvoice', $model);
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model->update($input);

            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new HouseFileInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';



            $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => $input]);
            $pdf_file = 'printInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            if ($input['housefile_module'] == 'cargo') {
                $s3path = 'Files/Cargo/';
                if ($fileData->cargo_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else if ($fileData->cargo_operation_type == 2) {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'ups') {
                $s3path = 'Files/Courier/Ups/';
                if ($fileData->courier_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'aeropost') {
                $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            } else if ($input['housefile_module'] == 'ccpack') {
                $s3path = 'Files/Courier/CCpack/';
                if ($fileData->ccpack_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            /*$input['invoiceAttachment'] = $pdf_path;
            Mail::to($input['email'])->send(new InvoiceDetailMail($input));
            
            if($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of)
            {
            $modelClient = Clients::where('id',$model->bill_to)->first();
            $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
            $modelClient->save();

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            if($input['balance_of'] - $dataInvoice->balance_of < 0)
            {
                $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of).'-Credit Deposited.';
                $modelActivities->cash_credit_flag = '2';
            }else
            {
                $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of.'-Invoice payment.';
                $modelActivities->cash_credit_flag = '1';
            }
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
            }

            if($input['limit_exceed'] == 'yes')
            {
                $input['flag'] = 'limit-exceed';
                Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }*/

            return url('/') . '/' . $pdf_path;
        } else {
            if ($input['flag_invoice'] != 'old') {
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    if ($input['housefile_module'] == 'cargo')
                        $input['bill_no'] = 'HF-5001';
                    else if ($input['housefile_module'] == 'ups')
                        $input['bill_no'] = 'UP-5001';
                    else if ($input['housefile_module'] == 'aeropost')
                        $input['bill_no'] = 'AP-5001';
                    else if ($input['housefile_module'] == 'ccpack')
                        $input['bill_no'] = 'CC-5001';
                } else {
                    if ($input['housefile_module'] == 'cargo')
                        $ab = 'HF-';
                    else if ($input['housefile_module'] == 'ups')
                        $ab = 'UP-';
                    else if ($input['housefile_module'] == 'aeropost')
                        $ab = 'AP-';
                    else if ($input['housefile_module'] == 'ccpack')
                        $ab = 'CC-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $input['bill_no'] = $ab;
                }
            }

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = date('Y-m-d', strtotime($input['date']));
            $input['created_by'] = auth()->user()->id;
            $model = HouseFileInvoice::create($input);
            Activities::log('create', 'housefileinvoice', $model);
            $countInvoiceItems = $_POST['count_invoice_items'];

            $input['fees_name'] = array_values($input['fees_name']);
            $input['fees_name_desc'] = array_values($input['fees_name_desc']);
            $input['quantity'] = array_values($input['quantity']);
            $input['unit_price'] = array_values($input['unit_price']);
            $input['total_of_items'] = array_values($input['total_of_items']);

            for ($i = 0; $i < $countInvoiceItems; $i++) {
                $modelInvoiceDetails = new HouseFileInvoiceItemDetails();
                $modelInvoiceDetails->invoice_id = $model->id;
                $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
                $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
                if (!empty($dataBilling))
                    $modelInvoiceDetails->item_code = $dataBilling->item_code;
                else
                    $modelInvoiceDetails->item_code = null;
                $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
                $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
                $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
                $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
                $modelInvoiceDetails->save();
            }
            $input['payment_status'] = 'Pending';
            $input['id'] = $model->id;
            $input['flag'] = 'invoice-sent';



            $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => $input]);
            $pdf_file = 'printInvoice_' . $model->id . '.pdf';
            $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
            $pdf->save($pdf_path);
            $allowToGenerateInvoicePayment = 1;
            if ($input['housefile_module'] == 'cargo') {
                $s3path = 'Files/Cargo/';
                if ($fileData->cargo_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else if ($fileData->cargo_operation_type == 2) {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                } else {
                    $allowToGenerateInvoicePayment = 0;
                    $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'ups') {
                $s3path = 'Files/Courier/Ups/';
                if ($fileData->courier_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            } else if ($input['housefile_module'] == 'aeropost') {
                $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
            } else if ($input['housefile_module'] == 'ccpack') {
                $s3path = 'Files/Courier/CCpack/';
                if ($fileData->ccpack_operation_type == 1) {
                    $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
                } else {
                    $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
                }
            }

            $filecontent = file_get_contents($pdf_path);
            $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
            //return response()->file($pdf_path);

            $input['invoiceAttachment'] = $pdf_path;
            //Mail::to($input['email'])->send(new InvoiceDetailMail($input));

            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit_stop' && $allowToGenerateInvoicePayment == 1) {
                /* $modelClient->available_balance = $modelClient->available_balance - $model->balance_of;
                $modelClient->save(); */

                $availableBalance = $modelClient->available_balance;
                if ($availableBalance > '0.00' || $availableBalance > '0') {
                    $modelClient->available_balance = $availableBalance - $model->balance_of;
                    if ($modelClient->available_balance < 0) {
                        $modelClient->available_balance = 0.00;
                        $paymentAmount = $availableBalance;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Partial', 'balance_of' => $model->total - $paymentAmount]);
                    } else {
                        $paymentAmount = $model->balance_of;

                        DB::table('invoices')->where('id', $model->id)->update(['credits' => $paymentAmount, 'payment_status' => 'Paid', 'balance_of' => '0.00']);
                    }
                    $modelClient->save();

                    if ($input['housefile_module'] == 'cargo') {
                        $inputInvoicePayment['house_file_id'] = $model->hawb_hbl_no;
                    } else if ($input['housefile_module'] == 'ups') {
                        $inputInvoicePayment['ups_id'] = $model->ups_id;
                    } else if ($input['housefile_module'] == 'aeropost') {
                        $inputInvoicePayment['aeropost_id'] = $model->aeropost_id;
                    } else if ($input['housefile_module'] == 'ccpack') {
                        $inputInvoicePayment['ccpack_id'] = $model->ccpack_id;
                    }

                    $inputInvoicePayment['invoice_id'] = $model->id;
                    $inputInvoicePayment['invoice_number'] = $model->bill_no;
                    $inputInvoicePayment['file_number'] = $fileData->file_number;
                    $inputInvoicePayment['amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_amount'] = $paymentAmount;
                    $inputInvoicePayment['exchange_rate'] = '0.00';
                    $inputInvoicePayment['payment_via'] = 'DEPOSIT';
                    $inputInvoicePayment['payment_via_note'] = 'C/N';
                    $inputInvoicePayment['created_at'] = gmdate("Y-m-d H:i:s");
                    $inputInvoicePayment['client'] = $model->bill_to;
                    $inputInvoicePayment['payment_accepted_by'] = auth()->user()->id;

                    $getLastReceiptNumber = DB::table('invoice_payments')->orderBy('id', 'desc')->first();
                    if (empty($getLastReceiptNumber)) {
                        $receiptNumber = '11101';
                    } else {
                        if (empty($getLastReceiptNumber->receipt_number))
                            $receiptNumber = '11101';
                        else
                            $receiptNumber = $getLastReceiptNumber->receipt_number + 1;
                    }

                    $inputInvoicePayment['receipt_number'] = $receiptNumber;
                    $modelInvoicePayment = InvoicePayments::create($inputInvoicePayment);

                    // Store deposite activities
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cashCreditClient';
                    $modelActivities->related_id = $model->bill_to;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = number_format($paymentAmount, 2) . '-Invoice Payment Paid. (Credit Used)';
                    $modelActivities->cash_credit_flag = '1';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }


            // Store deposite activities
            /* $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = number_format($model->balance_of, 2) . '-Invoice Generated.';
            $modelActivities->cash_credit_flag = '1';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save(); */

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'client';
            $modelActivities->related_id = $model->bill_to;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            if ($input['limit_exceed'] == 'yes') {
                $input['flag'] = 'limit-exceed';
                //Mail::to(\Config::get('app.adminEmail'))->send(new InvoiceDetailMail($input));
            }

            // Store invoice activity on file level
            $modelActivities = new Activities;
            $type = '';
            $relatedId = '';
            if ($input['housefile_module'] == 'cargo') {
                $type = 'houseFile';
                $relatedId = $model->hawb_hbl_no;
            } else if ($input['housefile_module'] == 'ups') {
                $type = 'ups';
                $relatedId = $model->ups_id;
            } else if ($input['housefile_module'] == 'aeropost') {
                $type = 'aeropost';
                $relatedId = $model->aeropost_id;
            } else if ($input['housefile_module'] == 'ccpack') {
                $type = 'ccpack';
                $relatedId = $model->ccpack_id;
            }
            $modelActivities->type = $type;
            $modelActivities->related_id = $relatedId;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been generated';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            return url('/') . '/' . $pdf_path;
        }
    }

    public function copy($id, $flag = null, $flagModule = null)
    {
        $model = HouseFileInvoice::find($id);
        //pre($model);
        $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();

        if ($flagModule == 'cargo')
            $ab = 'HF-';
        else if ($flagModule == 'ups')
            $ab = 'UP-';
        else if ($flagModule == 'aeropost')
            $ab = 'AP-';
        else if ($flagModule == 'ccpack')
            $ab = 'CC-';

        $ab .= substr($getLastInvoice->bill_no, 3) + 1;
        $input['bill_no'] = $ab;

        $model->bill_no = $ab;
        $model->date = date('Y-m-d');
        $model->payment_status = 'Pending';
        $model->credits = '0.00';
        //$model->balance_of = '0.00';
        $newModel = $model->replicate();
        $newModel->push();
        $fileData = DB::table('hawb_files')->where('file_number', $model->file_no)->where('deleted', 0)->first();
        if ($flagModule == 'cargo')
            $fileData = DB::table('hawb_files')->where('file_number', $model->file_no)->where('deleted', 0)->first();
        else if ($flagModule == 'ups')
            $fileData = DB::table('ups_details')->where('file_number', $model->file_no)->where('deleted', 0)->first();
        else if ($flagModule == 'aeropost')
            $fileData = DB::table('aeropost')->where('file_number', $model->file_no)->where('deleted', 0)->first();
        else if ($flagModule == 'ccpack')
            $fileData = DB::table('ccpack')->where('file_number', $model->file_no)->where('deleted', '0')->first();
        $modelInvoiceDetails = DB::table('invoice_item_details')->where('invoice_id', $id)->get();
        foreach ($modelInvoiceDetails as $key => $value) {
            $invoiceDetailModel = HouseFileInvoiceItemDetails::find($value->id);
            $invoiceDetailModel->invoice_id = $newModel->id;
            $newInvoiceDetailModel = $invoiceDetailModel->replicate();
            $newInvoiceDetailModel->push();
        }

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $type = '';
        $relatedId = '';
        if ($flagModule == 'cargo') {
            $type = 'houseFile';
            $relatedId = $newModel->hawb_hbl_no;
        } else if ($flagModule == 'ups') {
            $type = 'ups';
            $relatedId = $newModel->ups_id;
        } else if ($flagModule == 'aeropost') {
            $type = 'aeropost';
            $relatedId = $newModel->aeropost_id;
        } else if ($flagModule == 'ccpack') {
            $type = 'ccpack';
            $relatedId = $newModel->ccpack_id;
        }
        $modelActivities->type = $type;
        $modelActivities->related_id = $relatedId;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $newModel->bill_no . ' has been generated';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => $newModel->getAttributes()]);
        $pdf_file = 'printInvoice_' . $model->id . '.pdf';
        $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
        $pdf->save($pdf_path);

        if ($flagModule == 'cargo') {
            $s3path = 'Files/Cargo/';
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }
        } else if ($flagModule == 'ups') {
            $s3path = 'Files/Courier/Ups/';
            if ($fileData->courier_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            }
        } else if ($flagModule == 'aeropost') {
            $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
        } else if ($flagModule == 'ccpack') {
            $s3path = 'Files/Courier/CCpack/';
            if ($fileData->ccpack_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            }
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');

        Session::flash('flash_message', 'Invoice has been copied successfully');
        return redirect()->route('edithousefileinvoice', ['id' => $newModel->id, $flagModule]);
        /* if($flag == 'fromlisting')
            return redirect()->route('housefileinvoices',[$flagModule]);
        else
            return redirect()->route('edithousefileinvoice', ['id' => $newModel->id]); */
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\HouseFileInvoice  $houseFileInvoice
     * @return \Illuminate\Http\Response
     */
    public function show(HouseFileInvoice $houseFileInvoice, $id, $flagModule = null)
    {
        if ($flagModule == 'cargo') {
            $checkPermission = User::checkPermission(['view_details_cargo_house_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ups') {
            $checkPermission = User::checkPermission(['view_details_ups_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'aeropost') {
            $checkPermission = User::checkPermission(['view_details_aeropost_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ccpack') {
            $checkPermission = User::checkPermission(['view_details_ccpack_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        }
        $model = HouseFileInvoice::find($id);


        /*if($flag == 'fromNotification')
            Invoices::where('id',$id)->update(['display_notification_admin_invoice'=>0]);

        if($flag == 'fromNotificationCargoWarehouseInvoiceStatusChangedByCashier')
            Invoices::where('id',$id)->update(['display_notification_admin_invoice_status_changed'=>0]); */

        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'housefileinvoice')->orderBy('id', 'desc')->get()->toArray();
        return view('housefile-invoices.view', ['model' => $model, 'activityData' => $activityData, 'flagModule' => $flagModule]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\HouseFileInvoice  $houseFileInvoice
     * @return \Illuminate\Http\Response
     */
    public function edit(HouseFileInvoice $houseFileInvoice, $id, $flagModule = null, $flagFromWhere = null)
    {
        $model = DB::table('invoices')->where('id', $id)->first();
        if ($flagModule == 'cargo') {
            $moduleId = $model->cargo_id;
            $checkPermission = User::checkPermission(['update_cargo_house_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ups') {
            $moduleId = $model->ups_id;
            $checkPermission = User::checkPermission(['update_courier_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'aeropost') {
            $moduleId = $model->aeropost_id;
            $checkPermission = User::checkPermission(['update_aeropost_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        } else if ($flagModule == 'ccpack') {
            $moduleId = $model->ccpack_id;
            $checkPermission = User::checkPermission(['update_ccpack_invoices'], '', auth()->user()->id);
            if (!$checkPermission)
                return redirect('/home');
        }

        $dataInvoiceDetails  = DB::table('invoice_item_details')->where('invoice_id', $id)->get();
        $dataInvoiceDetails = json_decode(json_encode($dataInvoiceDetails));


        $dataBillingItems = DB::table('billing_items')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get()->pluck('billing_name', 'id');

        //$allUsers = DB::table('users')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'name');
        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        //$allUsers = json_decode($allUsers,1);
        //ksort($allUsers);

        $model->date = date('d-m-Y', strtotime($model->date));

        $currency = DB::table('currency')->select(['id', 'code'])->where('deleted', 0)->where('status', 1)->pluck('code', 'id');
        $currency = json_decode($currency, 1);
        ksort($currency);


        return view("housefile-invoices.form", ['id' => $id, 'model' => $model, 'dataBillingItems' => $dataBillingItems, 'allUsers' => $allUsers, 'currency' => $currency, 'dataInvoiceDetails' => $dataInvoiceDetails, 'flagModule' => $flagModule, 'flagFromWhere' => $flagFromWhere, 'moduleId' => $moduleId]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\HouseFileInvoice  $houseFileInvoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HouseFileInvoice $houseFileInvoice, $id)
    {
        $model = HouseFileInvoiceItemDetails::where('invoice_id', $id)->delete();
        $model = HouseFileInvoice::find($id);
        $dataInvoice = HouseFileInvoice::find($id);
        $model->fill($request->input());
        Activities::log('update', 'housefileinvoice', $model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        if ($input['housefile_module'] == 'cargo')
            $fileData = DB::table('hawb_files')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'ups')
            $fileData = DB::table('ups_details')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'aeropost')
            $fileData = DB::table('aeropost')->where('file_number', $input['file_no'])->where('deleted', 0)->first();
        else if ($input['housefile_module'] == 'ccpack')
            $fileData = DB::table('ccpack')->where('file_number', $input['file_no'])->where('deleted', '0')->first();
        $input['date'] = date('Y-m-d', strtotime($input['date']));

        $input['sub_total'] = str_replace(',', '', $input['sub_total']);
        $input['tca'] = str_replace(',', '', $input['tca']);
        $input['total'] = str_replace(',', '', $input['total']);
        $input['credits'] = str_replace(',', '', $input['credits']);
        $input['balance_of'] = str_replace(',', '', $input['balance_of']);

        if ($input['balance_of'] == '0.00')
            $input['payment_status'] = 'Paid';

        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);

        $countInvoiceItems = $_POST['count_invoice_items'];

        $input['fees_name'] = array_values($input['fees_name']);
        $input['fees_name_desc'] = array_values($input['fees_name_desc']);
        $input['quantity'] = array_values($input['quantity']);
        $input['unit_price'] = array_values($input['unit_price']);
        $input['total_of_items'] = array_values($input['total_of_items']);


        for ($i = 0; $i < $countInvoiceItems; $i++) {
            $modelInvoiceDetails = new HouseFileInvoiceItemDetails();
            $modelInvoiceDetails->invoice_id = $model->id;
            $modelInvoiceDetails->fees_name = $input['fees_name'][$i];
            $dataBilling = DB::table('billing_items')->where('id', $input['fees_name'][$i])->first();
            if (!empty($dataBilling))
                $modelInvoiceDetails->item_code = $dataBilling->item_code;
            else
                $modelInvoiceDetails->item_code = null;
            $modelInvoiceDetails->fees_name_desc = $input['fees_name_desc'][$i];
            $modelInvoiceDetails->quantity = str_replace(',', '', $input['quantity'][$i]);
            $modelInvoiceDetails->unit_price = str_replace(',', '', $input['unit_price'][$i]);
            $modelInvoiceDetails->total_of_items = str_replace(',', '', $input['total_of_items'][$i]);
            $modelInvoiceDetails->save();
        }
        $input['id'] = $model->id;

        $pdf = PDF::loadView('housefile-invoices.print', ['invoice' => $input]);
        $pdf_file = 'printInvoice_' . $model->id . '.pdf';
        $pdf_path = 'public/houseFileInvoices/' . $pdf_file;
        $pdf->save($pdf_path);

        if ($input['housefile_module'] == 'cargo') {
            $s3path = 'Files/Cargo/';
            if ($fileData->cargo_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else if ($fileData->cargo_operation_type == 2) {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Local/' . $fileData->file_number . '/Invoices/';
            }
        } else if ($input['housefile_module'] == 'ups') {
            $s3path = 'Files/Courier/Ups/';
            if ($fileData->courier_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            }
        } else if ($input['housefile_module'] == 'aeropost') {
            $s3path = 'Files/Courier/Aeropost/' . $fileData->file_number . '/Invoices/';
        } else if ($input['housefile_module'] == 'ccpack') {
            $s3path = 'Files/Courier/CCpack/';
            if ($fileData->ccpack_operation_type == 1) {
                $s3path .= 'Import/' . $fileData->file_number . '/Invoices/';
            } else {
                $s3path .= 'Export/' . $fileData->file_number . '/Invoices/';
            }
        }

        $filecontent = file_get_contents($pdf_path);
        $success = Storage::disk('s3')->put($s3path . 'Invoice_' . $model->bill_no . '.pdf', $filecontent, 'public');
        if ($input['bill_to'] != $dataInvoice->bill_to || $input['balance_of'] != $dataInvoice->balance_of) {
            $modelClient = Clients::where('id', $model->bill_to)->first();
            if ($modelClient->cash_credit == 'Credit') {
                $modelClient->available_balance = $modelClient->available_balance - ($input['balance_of'] - $dataInvoice->balance_of);
                $modelClient->save();
            }

            if ($input['balance_of'] - $dataInvoice->balance_of != 0) {
                // Store deposite activities
                $modelActivities = new Activities;
                $modelActivities->type = 'cashCreditClient';
                $modelActivities->related_id = $model->bill_to;
                $modelActivities->user_id   = auth()->user()->id;
                if ($input['balance_of'] - $dataInvoice->balance_of < 0) {
                    $modelActivities->description = abs($input['balance_of'] - $dataInvoice->balance_of) . '-Amount Deposited.';
                    $modelActivities->cash_credit_flag = '2';
                } else {
                    $modelActivities->description = $input['balance_of'] - $dataInvoice->balance_of . '-Invoice Modified.';
                    $modelActivities->cash_credit_flag = '1';
                }
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        // Store invoice activity on file level
        $modelActivities = new Activities;
        $type = '';
        $relatedId = '';
        if ($input['housefile_module'] == 'cargo') {
            $type = 'houseFile';
            $relatedId = $model->hawb_hbl_no;
        } else if ($input['housefile_module'] == 'ups') {
            $type = 'ups';
            $relatedId = $model->ups_id;
        } else if ($input['housefile_module'] == 'aeropost') {
            $type = 'aeropost';
            $relatedId = $model->aeropost_id;
        } else if ($input['housefile_module'] == 'ccpack') {
            $type = 'ccpack';
            $relatedId = $model->ccpack_id;
        }
        $modelActivities->type = $type;
        $modelActivities->related_id = $relatedId;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $model->bill_no . ' has been modified';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        Session::flash('flash_message', 'Record has been updated successfully');
        //return redirect('housefileinvoices');

        /* if ($model->flag_invoice == 'old')
            return redirect('oldinvoices');
        else
            return redirect('housefileinvoices/' . $model->housefile_module); */

        if ($model->flag_invoice == 'old')
            return redirect('oldinvoices');
        else {
            if ($input['flagFromWhere'] == 'flagFromView') {
                if ($input['housefile_module'] == 'cargo') {
                    return redirect()->route('viewhawbfile', [$model->hawb_hbl_no]);
                }
            } else
                return redirect('housefileinvoices/' . $model->housefile_module);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\HouseFileInvoice  $houseFileInvoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(HouseFileInvoice $houseFileInvoice, $id)
    {
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        $model = HouseFileInvoice::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);
        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'houseFile';
        $modelActivities->related_id = $invoiceData->hawb_hbl_no;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $invoiceData->bill_no . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function deletehousefileinvoicefromedit(houseFileInvoice $houseFileInvoice, $id)
    {
        $invoiceData = DB::table('invoices')->where('id', $id)->first();
        $model = HouseFileInvoice::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);
        // Store invoice activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'houseFile';
        $modelActivities->related_id = $invoiceData->hawb_hbl_no;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = 'Invoice #' . $invoiceData->bill_no . ' has been deleted';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
        Session::flash('flash_message', 'Invoice has been deleted successfully');
        return redirect('housefileinvoices');
    }

    public function viewandprinthousefileinvoice($id)
    {
        $model = DB::table('invoices')->where('id', $id)->first();
        $model = (array) $model;
        return view("housefile-invoices.viewandprint", ['invoice' => $model]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $req = $request->all();
        $flagModule = $req['flagModule'];
        if ($flagModule == 'cargo') {
            $permissionCargoInvoicesEdit = User::checkPermission(['update_cargo_house_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesDelete = User::checkPermission(['delete_cargo_house_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesPaymentAdd = User::checkPermission(['add_cargo_invoice_payments'], '', auth()->user()->id);
            $permissionCargoInvoicesCopy = User::checkPermission(['copy_cargo_house_invoices'], '', auth()->user()->id);
        } else if ($flagModule == 'ups') {
            $permissionCargoInvoicesEdit = User::checkPermission(['update_courier_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesDelete = User::checkPermission(['delete_courier_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesPaymentAdd = User::checkPermission(['add_courier_invoice_payments'], '', auth()->user()->id);
            $permissionCargoInvoicesCopy = User::checkPermission(['copy_courier_invoices'], '', auth()->user()->id);
        } else if ($flagModule == 'aeropost') {
            $permissionCargoInvoicesEdit = User::checkPermission(['update_aeropost_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesDelete = User::checkPermission(['delete_aeropost_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesPaymentAdd = User::checkPermission(['add_aeropost_invoice_payments'], '', auth()->user()->id);
            $permissionCargoInvoicesCopy = User::checkPermission(['copy_aeropost_invoices'], '', auth()->user()->id);
        } else if ($flagModule == 'ccpack') {
            $permissionCargoInvoicesEdit = User::checkPermission(['update_ccpack_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesDelete = User::checkPermission(['delete_ccpack_invoices'], '', auth()->user()->id);
            $permissionCargoInvoicesPaymentAdd = User::checkPermission(['add_ccpack_invoice_payments'], '', auth()->user()->id);
            $permissionCargoInvoicesCopy = User::checkPermission(['copy_ccpack_invoices'], '', auth()->user()->id);
        }
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['invoices.id', 'invoices.date', 'bill_no', $flagModule == 'cargo' ? 'hawb_files.file_number' : ($flagModule == 'ups' ? 'ups_details.file_number' : ($flagModule == 'aeropost' ? 'aeropost.file_number' : 'ccpack.file_number')), 'invoices.awb_no', 'c1.company_name', 'invoices.consignee_address', 'currency.code', 'total', 'credits', 'users.name', 'payment_status'];

        $total = Invoices::selectRaw('count(*) as total');
        //->where('invoices.deleted', '0');
        if ($flagModule == 'cargo')
            $total = $total->where('housefile_module', $flagModule)->whereNull('flag_invoice');
        if ($flagModule == 'ups')
            $total = $total->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id')->whereNotNull('ups_id')->whereNull('flag_invoice');
        if ($flagModule == 'aeropost')
            $total = $total->whereNotNull('aeropost_id')->whereNull('flag_invoice');
        if ($flagModule == 'ccpack')
            $total = $total->whereNotNull('ccpack_id')->whereNull('flag_invoice');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('invoices')
            ->selectRaw('invoices.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency');
        if ($flagModule == 'cargo')
            $query = $query->leftJoin('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no');
        if ($flagModule == 'ups')
            $query = $query->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id');
        if ($flagModule == 'aeropost')
            $query = $query->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id');
        if ($flagModule == 'ccpack')
            $query = $query->leftJoin('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id');
        //$query = $query->where('invoices.deleted', '0');
        if ($flagModule == 'cargo')
            $query = $query->where('housefile_module', $flagModule)->whereNull('flag_invoice');
        if ($flagModule == 'ups')
            $query = $query->whereNotNull('ups_id')->whereNull('flag_invoice');
        if ($flagModule == 'aeropost')
            $query = $query->whereNotNull('aeropost_id')->whereNull('flag_invoice');
        if ($flagModule == 'ccpack')
            $query = $query->whereNotNull('ccpack_id')->whereNull('flag_invoice');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('invoices.date', array($fromDate, $toDate));
        }


        $filteredq = DB::table('invoices')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency');
        if ($flagModule == 'cargo')
            $filteredq = $filteredq->leftJoin('hawb_files', 'hawb_files.id', '=', 'invoices.hawb_hbl_no');
        if ($flagModule == 'ups')
            $filteredq = $filteredq->join('ups_details', 'ups_details.id', '=', 'invoices.ups_id');
        if ($flagModule == 'aeropost')
            $filteredq = $filteredq->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id');
        if ($flagModule == 'ccpack')
            $filteredq = $filteredq->leftJoin('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id');
        //$filteredq = $filteredq->where('invoices.deleted', '0');
        if ($flagModule == 'cargo')
            $filteredq = $filteredq->where('housefile_module', $flagModule)->whereNull('flag_invoice');
        if ($flagModule == 'ups')
            $filteredq = $filteredq->whereNotNull('ups_id')->whereNull('flag_invoice');
        if ($flagModule == 'aeropost')
            $filteredq = $filteredq->whereNotNull('aeropost_id')->whereNull('flag_invoice');
        if ($flagModule == 'ccpack')
            $filteredq = $filteredq->whereNotNull('ccpack_id')->whereNull('flag_invoice');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search, $flagModule) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%');

                if ($flagModule == 'cargo')
                    $query2 = $query2->orWhere('hawb_files.file_number', 'like', '%' . $search . '%');
                if ($flagModule == 'ups')
                    $query2 = $query2->orWhere('ups_details.file_number', 'like', '%' . $search . '%');
                if ($flagModule == 'aeropost')
                    $query2 = $query2->orWhere('aeropost.file_number', 'like', '%' . $search . '%');
                if ($flagModule == 'ccpack')
                    $query2 = $query2->orWhere('ccpack.file_number', 'like', '%' . $search . '%');

                $query2 = $query2->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search, $flagModule) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%');
                if ($flagModule == 'cargo')
                    $query2 = $query2->orWhere('hawb_files.file_number', 'like', '%' . $search . '%');
                if ($flagModule == 'ups')
                    $query2 = $query2->orWhere('ups_details.file_number', 'like', '%' . $search . '%');
                if ($flagModule == 'aeropost')
                    $query2 = $query2->orWhere('aeropost.file_number', 'like', '%' . $search . '%');
                if ($flagModule == 'ccpack')
                    $query2 = $query2->orWhere('ccpack.file_number', 'like', '%' . $search . '%');

                $query2 = $query2->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $dataBillingParty = app('App\Clients')->getClientData($items->bill_to);
            $dataCurrency = Currency::getData($items->currency);
            $dataUser = app('App\User')->getUserName($items->created_by);

            //$houseFileData = app('App\HawbFiles')->getHouseFileData($items->hawb_hbl_no);


            $dataCommonModules = app('App\Common')->getCommonAllModuleData($flagModule, $flagModule == 'cargo' ? $items->hawb_hbl_no : ($flagModule == 'ups' ? $items->ups_id : ($flagModule == 'aeropost' ? $items->aeropost_id : $items->ccpack_id)));

            if (empty($dataCommonModules))
                continue;

            $action = '<div class="dropdown">';

            $delete =  route('deletehousefileinvoice', $items->id);
            $edit =  route('edithousefileinvoice', [$items->id, $flagModule]);

            $action .= '<a title="View & Print"  target="_blank" href="' . route('viewandprinthousefileinvoice', $items->id) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($items->deleted == '0') {
                if ($permissionCargoInvoicesEdit && $dataCommonModules->file_close != 1) {
                    if ($items->type_flag != 'Local') {
                        $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                    }
                }

                if ($permissionCargoInvoicesDelete && checkloggedinuserdata() == 'Other') {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCargoInvoicesCopy) {
                    $action .= '<li><a href="' . route('copyhouseinvoice', [$items->id, 'fromlisting', $flagModule]) . '">Copy Invoice</a></li>';
                }

                if ($items->payment_status == 'Pending' || $items->payment_status == 'Partial') {
                    if ($permissionCargoInvoicesPaymentAdd) {
                        if ($items->type_flag != 'Local') {
                            if ($flagModule == 'cargo') {
                                $action .= '<li><a class="checkhousefiledate" data-id="' . $items->id . '" href="' . route('addinvoicepayment', [$items->hawb_hbl_no, $items->id, 0, '0', 'housefile']) . '">Add Payment</a></li>';
                                $action .= '<li><a href="' . route('addinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                            } elseif ($flagModule == 'ups') {
                                $action .= '<li><a href="' . route('addupsinvoicepayment', [$items->ups_id, $items->id, 0]) . '">Add Payment</a></li>';
                                $action .= '<li><a href="' . route('addupsinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                            } elseif ($flagModule == 'aeropost') {
                                $action .= '<li><a href="' . route('addaeropostinvoicepayment', [$items->aeropost_id, $items->id, 0]) . '">Add Payment</a></li>';
                                $action .= '<li><a href="' . route('addaeropostinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                            } elseif ($flagModule == 'ccpack') {
                                $action .= '<li><a href="' . route('addccpackinvoicepayment', [$items->ccpack_id, $items->id, 0]) . '">Add Payment</a></li>';
                                $action .= '<li><a href="' . route('addccpackinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                            }
                        }
                    }
                } else {

                    $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', $flagModule == 'cargo' ? 'housefile' : $flagModule]) . '">Payment Receipt</i></a>
                    </li>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $data[] = [$items->id, date('d-m-Y', strtotime($items->date)), $items->bill_no, !empty($dataCommonModules) ? $dataCommonModules->file_number : '-', $items->awb_no, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $items->consignee_address, !empty($dataCurrency->code) ? $dataCurrency->code : "-", number_format($items->total, 2), number_format($items->credits, 2), !empty($dataUser->name) ? $dataUser->name : "-", $items->payment_status, $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserverside()
    {
        $flag = $_POST['flag'];
        if ($flag == 'getInvoiceData') {
            $invoiceId = $_POST['invoiceId'];
            return json_encode(Invoices::getInvoiceData($invoiceId));
        }
    }

    public function checkhousefiledate()
    {
        $invoiceId = $_POST['invoiceId'];
        $dataInvoice = Invoices::find($invoiceId);
        if ($dataInvoice->date == date('Y-m-d'))
            return '1';
        else
            return '0';
    }
}
