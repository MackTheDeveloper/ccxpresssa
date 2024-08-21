<?php

namespace App\Http\Controllers;

use App\UpsMaster;
use App\Activities;
use App\Clients;
use App\Customs;
use App\Ups;
use App\upsFreightCommission;
use App\upsImportExportCommission;
use App\UpsInvoiceItemDetails;
use App\UpsInvoices;
use App\Upspackages;
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

class UpsMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("ups-master.index");
    }

    public function listingmasterups(Request $request)
    {
        $permissionUpsMasterEdit = User::checkPermission(['update_ups_master'], '', auth()->user()->id);
        $permissionUpsMasterDelete = User::checkPermission(['delete_ups_master'], '', auth()->user()->id);
        $permissionUpsMasterAddExpense = User::checkPermission(['add_ups_master_expenses'], '', auth()->user()->id);
        $permissionUpsMasterAddInvoice = User::checkPermission(['add_ups_master_invoices'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);
        $req = $request->all();

        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        /* Session::put('upsMasterListingFromDate', $req['fromDate']);
        Session::put('upsMasterListingToDate', $req['toDate']); */
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ups_master.id', 'ups_master.id', 'arrival_date', 'file_number', 'tracking_number', 'c1.company_name', 'c2.company_name', ''];
        $total = UpsMaster::selectRaw('count(*) as total');
        //->where('deleted', '0');
        if (!empty($fileType)) {
            $total = $total->where('ups_operation_type', $fileType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('ups_master')
            ->selectRaw('ups_master.*,c1.company_name as consigneeName,c2.company_name as shipperName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_master.shipper_name');
        //->where('ups_master.deleted', '0');
        if (!empty($fileType)) {
            $query = $query->where('ups_operation_type', $fileType);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('ups_master')
            ->selectRaw('ups_master.*,c1.company_name as consigneeName,c2.company_name as shipperName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_master.shipper_name');
        //->where('ups_master.deleted', '0');
        if (!empty($fileType)) {
            $filteredq = $filteredq->where('ups_operation_type', $fileType);
        }
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
            $invoiceNumbers = UpsMaster::getUpsMasterInvoicesOfFile($value->id);

            $action = '<div class="dropdown"><a title="Click here to print"  target="_blank" href="' . route("printupsmasterfile", [$value->id, $value->ups_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete = route('deleteupsmaster', [$value->id]);
            $edit = route('editupsmaster', [$value->id, $value->ups_operation_type]);
            if ($value->deleted == '0') {
                if ($permissionUpsMasterEdit) {
                    $action .= '<a href="' . $edit . '" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }

                if ($permissionUpsMasterDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['ups-master', $value->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionUpsMasterAddExpense) {
                    $action .= '<li><a href="' . route('createupsmasterexpense', $value->id) . '">Add Expense</a></li>';
                }

                if ($permissionUpsMasterAddInvoice) {
                    $action .= '<li><a href="' . route('createupsmasterinvoice', $value->id) . '">Add Invoice</a></li>';
                }

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['UPSMaster', $value->id]) . '">Close File</a></li>';
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

    public function print($upsId, $upsType)
    {
        $model = DB::table('ups_master')->where('id', $upsId)->first();
        if ($upsType == 1) {
            $pdf = PDF::loadView('ups-master.printimport', ['model' => $model]);
        } else {
            $pdf = PDF::loadView('ups-master.printexport', ['model' => $model]);
        }

        $pdf_file = $model->file_number . '_upsMaster.pdf';
        $pdf_path = 'public/upsMasterFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function checkoperations()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkFileAssgned') {
            $MasterUpsId = $_POST['MasterUpsId'];
            return UpsMaster::checkFileAssgned($MasterUpsId);
        }
        if ($flag == 'checkHawbFiles') {
            $MasterUpsId = $_POST['MasterUpsId'];
            return json_encode(UpsMaster::checkHawbFiles($MasterUpsId));
        }
        if ($flag == 'getMasterUpsData') {
            $MasterUpsId = $_POST['MasterUpsId'];
            return json_encode(UpsMaster::getMasterUpsData($MasterUpsId));
        }
    }

    public function expandhousefiles(Request $request)
    {
        $masterUpsId = $_POST['masterUpsId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('ups_details')->where('master_ups_id', $masterUpsId)->get();
        return view('ups-master.expandhousefiles', ['packageData' => $packageData, 'rowId' => $rowId, 'masterUpsId' => $masterUpsId]);
    }

    public function exporttoexcelhousefiles($masterUpsId = null)
    {
        $otherExpenseArray[] = array('Sr No.', 'File Number', 'AWB Tracking', 'Shipment Number', 'Billing Party', 'File Status', 'Shipper', 'Consignee', 'Package Type', 'Weight', 'Invoice Numbers', 'Origin');
        $masterUpsFile = DB::table('ups_master')->where('id', $masterUpsId)->first();
        $packageData = DB::table('ups_details')->where('master_ups_id', $masterUpsId)->get();
        $i = 1;
        foreach ($packageData as $packageData) {
            $invoiceAmounts = app('App\Expense')::getUpsInvoicesOfFileInExpand($packageData->id, 'forExpandedFiles', 'actionExport');
            $dataBillingParty = app('App\Clients')->getClientData($packageData->billing_party);
            if ($packageData->package_type == 'LTR')
                $packageType = 'Letter';
            else if ($packageData->package_type == 'DOC')
                $packageType = 'Document';
            else
                $packageType = 'Package';

            $shipperData = app('App\Clients')->getClientData($packageData->shipper_name);
            $consigneeData = app('App\Clients')->getClientData($packageData->consignee_name);
            $otherExpenseArray[] = array(
                'Sr No.' => $i,
                'File Number' => $packageData->file_number,
                'AWB Tracking' => $packageData->awb_number,
                'Shipment Number' => !empty($packageData->shipment_number) ? $packageData->shipment_number : '-',
                'Billing Party' => !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-",
                'File Status' => isset(Config::get('app.ups_new_scan_status')[!empty($packageData->ups_scan_status) ? $packageData->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($packageData->ups_scan_status) ? $packageData->ups_scan_status : '-'] : '-',
                'Shipper' => !empty($shipperData->company_name) ? $shipperData->company_name : '-',
                'Consignee' => !empty($consigneeData->company_name) ? $consigneeData->company_name : '-',
                'Package Type' => $packageType,
                'Weight' => !empty($packageData->weight) ? $packageData->weight . ' ' . $packageData->unit : '-',
                'Invoice Numbers' => $invoiceAmounts,
                'Origin' => $packageData->origin,
            );
            $i++;
        }
        $excelObj = Excel::create('UPS House Files - ' . (!empty($masterUpsFile) ? $masterUpsFile->file_number : ''), function ($excel) use ($otherExpenseArray, $masterUpsFile) {
            $excel->setTitle('UPS House Files - ' . (!empty($masterUpsFile) ? $masterUpsFile->file_number : ''));
            $excel->sheet('UPS House Files - ' . (!empty($masterUpsFile) ? $masterUpsFile->file_number : ''), function ($sheet) use ($otherExpenseArray) {
                $sheet->fromArray($otherExpenseArray, null, 'A1', false, false);
            });
        });
        $excelObj->download('xlsx');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new UpsMaster();
        $model->arrival_date = date('d-m-Y');

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        return view('ups-master._form', ['model' => $model, 'agents' => $agents]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $GLOBALS['freightCommission'] = new upsFreightCommission;
        $input = $request->all();
        $dataLast = DB::table('ups_master')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
        if (empty($dataLast)) {
            if ($input['ups_operation_type'] == 1) {
                $input['file_number'] = 'MUPI 1110';
            } else {
                $input['file_number'] = 'MUPE 1110';
            }
        } else {
            if ($input['ups_operation_type'] == 1) {
                $ab = 'MUPI ';
            } else {
                $ab = 'MUPE ';
            }
            $ab .= substr($dataLast->file_number, 5) + 1;
            $input['file_number'] = $ab;
        }
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;

        // Get Consigne Shipper
        $modelUpsMaster = new UpsMaster;
        if ($input['ups_operation_type'] == 1) {
            $consigneeData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterImport')['consignee']);
            $shipperData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterImport')['shipper']);
            $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
            $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';
        } else {
            $consigneeData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterExport')['consignee']);
            $shipperData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterExport')['shipper']);
            $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
            $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';
        }


        $model = UpsMaster::create($input);
        Activities::log('create', 'upsMaster', $model);
        $masterUPSId = $model->id;
        $masterFileNumber = $model->file_number;
        $arrivalDate = $model->arrival_date;
        if ($input['ups_operation_type'] == '2') {
            $inputfile = $request->file('export_file');
            $fileMimeType = $inputfile->getMimeType();
            $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            if (in_array($fileMimeType, $allowMimeTypes)) {
                if ($inputfile->getClientOriginalExtension() == 'xls' || $inputfile->getClientOriginalExtension() == 'xlsx') {
                    // $headercolumnArr = Excel::load($inputfile)->get()->first()->keys()->toArray();
                    $theArray = Excel::toArray(new stdClass(), $inputfile);
                    $theArray = $theArray[0];
                    $this->storeSub($theArray, $masterUPSId, $masterFileNumber, $arrivalDate);
                    // Excel::load($inputfile, function ($reader) use ($request, $masterUPSId, $masterFileNumber, $arrivalDate) {
                    //     $getCommission = $GLOBALS['freightCommission'];
                    //     $commission = 0;
                    //     $dataCommission = [];
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
                    //             $dataExport['master_ups_id'] = $masterUPSId;
                    //             $dataExport['master_file_number'] = $masterFileNumber;
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
                    //             $dataExport['master_ups_id'] = $masterUPSId;
                    //             $dataExport['master_file_number'] = $masterFileNumber;
                    //             $model = Ups::Create($dataExport);
                    //             $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
                    //             $filePath = $dir;
                    //             //pre($filePath);
                    //             //$success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
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
                    //                 ->whereIn('item_code', ['C1071', 'C1071/ Commission fret aerien (UPS)'])
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
                }
            }
        }
        if ($input['ups_operation_type'] == '1') {
            Ups::where('last_action_flag', 1)->update(array('last_action_flag' => 0));
            $dataArray = array();
            $dataPackageArray = array();
            $inputfile = $request->file('import_file');
            $fileMimeType = $inputfile->getMimeType();
            $allowMimeTypes = array('application/zlib', 'text/plain', 'application/octet-stream');
            if (in_array($fileMimeType, $allowMimeTypes)) {
                $handle = fopen($_FILES['import_file']['tmp_name'], "r");
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
                    $handle = fopen($inputfile, "r");

                    while (($line = fgets($handle)) !== false) {
                        $recordType = substr($line, 44, 6);
                        if ($recordType == 200000) {
                            if ($i == 1) {
                                $totalR++;
                                if ($dataArray['last_action'] == 'updated') {
                                    $model = Ups::find($dataArray['last_action_updated_id']);
                                    $model->fill($dataArray);
                                    Activities::log('update', 'ups', $model);
                                    $dataArray['master_ups_id'] = $masterUPSId;
                                    $dataArray['master_file_number'] = $masterFileNumber;
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
                                        ->whereIn('item_code', ['C1071', 'C1071/ Commission fret aerien (UPS)'])
                                        ->count();

                                    if ($invoiceOfCommission == 0 && ($model->fc == 1 || $model->pp == 1))
                                        Ups::generateUpsInvoice($model->id);
                                } else {
                                    $dataArray['master_ups_id'] = $masterUPSId;
                                    $dataArray['master_file_number'] = $masterFileNumber;
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

                            if (trim(substr($line, 81, 3)) == 'LBS') {
                                $dWeight = trim(substr($line, 76, 5));
                                $dataArray['weight'] = number_format($dWeight / 2.2, 2);
                            } else {
                                $dataArray['weight'] = !empty(trim(substr($line, 76, 5))) ? number_format(trim(substr($line, 76, 5)) / 10, 2) : '0.0000';
                            }

                            $invoiceTotal = trim(substr($line, 165, 10));
                            $feightCharges = trim(substr($line, 153, 9));
                            if (trim(substr($line, 175, 3)) == 'USD') {
                                if (is_numeric($invoiceTotal))
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
                        if ($linecount == $j) {
                            if ($dataArray['last_action'] == 'updated') {
                                $model = Ups::find($dataArray['last_action_updated_id']);
                                $dataArray['master_ups_id'] = $masterUPSId;
                                $dataArray['master_file_number'] = $masterFileNumber;
                                $model->fill($dataArray);
                                Activities::log('update', 'ups', $model);
                                $model->update($dataArray);
                            } else {
                                $dataArray['master_ups_id'] = $masterUPSId;
                                $dataArray['master_file_number'] = $masterFileNumber;
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
            }
        }
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('ups-master');
    }

    public function storeSub($dData, $masterUPSId, $masterFileNumber, $arrivalDate){
        $dData = arrayKeyValueFlip($dData);
        $getCommission = $GLOBALS['freightCommission'];
        $commission = 0;
        $dataCommission = [];
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
                $dataExport['master_ups_id'] = $masterUPSId;
                $dataExport['master_file_number'] = $masterFileNumber;
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
                $dataExport['master_ups_id'] = $masterUPSId;
                $dataExport['master_file_number'] = $masterFileNumber;
                $model = Ups::Create($dataExport);
                $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
                $filePath = $dir;
                //pre($filePath);
                //$success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
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

    /**
     * Display the specified resource.
     *
     * @param  \App\UpsMaster  $upsMaster
     * @return \Illuminate\Http\Response
     */
    public function show(UpsMaster $upsMaster, $id)
    {
        $checkPermission = User::checkPermission(['viewdetails_ups_master'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'upsMaster')->orderBy('id', 'desc')->get()->toArray();
        $HouseAWBData = DB::table('ups_details')->where('master_ups_id', $id)->get();

        $model = UpsMaster::find($id);
        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.ups_master_id', $id)
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
            ->whereNotNull('ups_master_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('ups_master_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();

        $attachedFiles = DB::table('ups_uploaded_files')->where('master_file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('ups_master', 'expenses.ups_master_id', '=', 'ups_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ups_master_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('ups_master', 'expenses.ups_master_id', '=', 'ups_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ups_master_id', $id)
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
            ->where('invoices.ups_master_id', $id)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.ups_master_id', $id)
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

        return view("ups-master.view-details", ['model' => $model, 'invoices' => $invoices, 'activityData' => $activityData, 'dataExpense' => $dataExpense, 'id' => $id, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'finalReportData' => $finalReportData, 'HouseAWBData' => $HouseAWBData]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UpsMaster  $upsMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(UpsMaster $upsMaster, $id, $fileType = null)
    {
        $model = DB::table('ups_master')->where('id', $id)->first();
        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);
        return view('ups-master._form', ['model' => $model, 'agents' => $agents, 'fileType' => $fileType, 'billingParty' => $billingParty]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UpsMaster  $upsMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UpsMaster $upsMaster, $id)
    {
        $model = UpsMaster::find($id);
        $GLOBALS['freightCommission'] = new upsFreightCommission;
        $input = $request->all();
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $masterUPSId = $model->id;
        $masterFileNumber = $model->file_number;
        $arrivalDate = $model->arrival_date;
        $model->fill($input);
        $modelUpsMaster = new UpsMaster;
        Activities::log('update', 'upsMaster', $model);
        if ($input['ups_operation_type'] == 1) {
            $consigneeData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterImport')['consignee']);
            $shipperData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterImport')['shipper']);
            $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
            $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';
        } else {
            $consigneeData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterExport')['consignee']);
            $shipperData = $modelUpsMaster->getConsigneeShipper(Config::get('app.upsMasterExport')['shipper']);
            $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
            $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';
        }
        $model->update($input);
        if ($input['ups_operation_type'] == '2') {
            $inputfile = $request->file('export_file');
            if (!empty($inputfile)) {
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() == 'xls' || $inputfile->getClientOriginalExtension() == 'xlsx') {
                        // $headercolumnArr = Excel::load($inputfile)->get()->first()->keys()->toArray();
                        $theArray = Excel::toArray(new stdClass(), $inputfile);
                        $theArray = $theArray[0];
                        $this->updateSub($theArray, $masterUPSId, $masterFileNumber, $arrivalDate);
                        // Excel::load($inputfile, function ($reader) use ($request, $masterUPSId, $masterFileNumber, $arrivalDate) {
                        //     $getCommission = $GLOBALS['freightCommission'];
                        //     $commission = 0;
                        //     $dataCommission = [];
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
                        //             $dataExport['master_ups_id'] = $masterUPSId;
                        //             $dataExport['master_file_number'] = $masterFileNumber;
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
                        //             $dataExport['master_ups_id'] = $masterUPSId;
                        //             $dataExport['master_file_number'] = $masterFileNumber;
                        //             $model = Ups::Create($dataExport);
                        //             $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
                        //             $filePath = $dir;
                        //             //pre($filePath);
                        //             //$success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
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
                        //                 ->whereIn('item_code', ['C1071', 'C1071/ Commission fret aerien (UPS)'])
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
                    }
                }
            }
        }
        if ($input['ups_operation_type'] == '1') {
            Ups::where('last_action_flag', 1)->update(array('last_action_flag' => 0));
            $dataArray = array();
            $dataPackageArray = array();
            $inputfile = $request->file('import_file');
            $fileMimeType = $inputfile->getMimeType();
            $allowMimeTypes = array('application/zlib', 'text/plain', 'application/octet-stream');
            if (in_array($fileMimeType, $allowMimeTypes)) {
                if (!empty($_FILES['import_file']['tmp_name'])) {
                    $handle = fopen($_FILES['import_file']['tmp_name'], "r");
                    $totalRecord = substr_count(file_get_contents($_FILES['import_file']['tmp_name']), "200000");
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
                    $handle = fopen($inputfile, "r");

                    while (($line = fgets($handle)) !== false) {
                        $recordType = substr($line, 44, 6);
                        if ($recordType == 200000) {
                            if ($i == 1) {
                                $totalR++;
                                if ($dataArray['last_action'] == 'updated') {
                                    $model = Ups::find($dataArray['last_action_updated_id']);
                                    $model->fill($dataArray);
                                    Activities::log('update', 'ups', $model);
                                    $dataArray['master_ups_id'] = $masterUPSId;
                                    $dataArray['master_file_number'] = $masterFileNumber;
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
                                        ->whereIn('item_code', ['C1071', 'C1071/ Commission fret aerien (UPS)'])
                                        ->count();

                                    if ($invoiceOfCommission == 0 && ($model->fc == 1 || $model->pp == 1))
                                        Ups::generateUpsInvoice($model->id);
                                } else {
                                    $dataArray['master_ups_id'] = $masterUPSId;
                                    $dataArray['master_file_number'] = $masterFileNumber;
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

                            if (trim(substr($line, 81, 3)) == 'LBS') {
                                $dWeight = trim(substr($line, 76, 5));
                                $dataArray['weight'] = number_format($dWeight / 2.2, 2);
                            } else {
                                $dataArray['weight'] = !empty(trim(substr($line, 76, 5))) ? number_format(trim(substr($line, 76, 5)) / 10, 2) : '0.0000';
                            }

                            $invoiceTotal = trim(substr($line, 165, 10));
                            $feightCharges = trim(substr($line, 153, 9));
                            if (trim(substr($line, 175, 3)) == 'USD') {
                                if (is_numeric($invoiceTotal))
                                    $dataArray['declared_value'] = '$' . number_format($invoiceTotal / 100, 2);
                                else
                                    $dataArray['declared_value'] = '$0.00';
                            } else {
                                $dataArray['declared_value'] = '$0.00';
                            }

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
                        if ($linecount == $j) {
                            if ($dataArray['last_action'] == 'updated') {
                                $model = Ups::find($dataArray['last_action_updated_id']);
                                $dataArray['master_ups_id'] = $masterUPSId;
                                $dataArray['master_file_number'] = $masterFileNumber;
                                $model->fill($dataArray);
                                Activities::log('update', 'ups', $model);
                                $model->update($dataArray);
                            } else {
                                $dataArray['master_ups_id'] = $masterUPSId;
                                $dataArray['master_file_number'] = $masterFileNumber;
                                $model = Ups::create($dataArray);
                                $dir = 'Files/Courier/Ups/Import/' . $model->file_number;
                                $filePath = $dir;
                                //pre($filePath);
                                $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
                                Activities::log('create', 'ups', $model);
                            }
                            if (!empty($dataPackageArray)) {
                                $dataPackageArray['ups_details_id'] = $model->id;
                                $modelPackage = Upspackages::create($dataPackageArray);
                            }
                        }
                        $j++;
                    }
                    fclose($handle);
                }
            }
        }
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('ups-master');
    }

    public function updateSub($dData, $masterUPSId, $masterFileNumber, $arrivalDate){
        $dData = arrayKeyValueFlip($dData);
        $getCommission = $GLOBALS['freightCommission'];
        $commission = 0;
        $dataCommission = [];
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
                $dataExport['master_ups_id'] = $masterUPSId;
                $dataExport['master_file_number'] = $masterFileNumber;
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
                $dataExport['master_ups_id'] = $masterUPSId;
                $dataExport['master_file_number'] = $masterFileNumber;
                $model = Ups::Create($dataExport);
                $dir = 'Files/Courier/Ups/Export/' . $model->file_number;
                $filePath = $dir;
                //pre($filePath);
                //$success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UpsMaster  $upsMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(UpsMaster $upsMaster, $id)
    {
        DB::table('ups_master')->where('id', $id)->update(['deleted' => 1, 'deleted_on' => date('Y-m-d h:i:s'), 'deleted_by' => auth()->user()->id]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'upsMaster';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function checkuniqueupsmasterawbnumber()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit') {
            $upsData = DB::table('ups_master')->where('deleted', '0')->where('tracking_number', $number)->where('id', '<>', $id)->count();
        } else {
            $upsData = DB::table('ups_master')->where('deleted', '0')->where('tracking_number', $number)->count();
        }
        if ($upsData) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getupsmasterdata()
    {
        $id = $_POST['upsMasterId'];
        $aAr = array();
        $dataBilling = DB::table('ups_master')->where('id', $id)->first();
        $dataConsignee = DB::table('clients')->where('id', $dataBilling->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $dataBilling->shipper_name)->first();
        $aAr['consigneeName'] = $dataConsignee->company_name;
        $aAr['shipperName'] = $dataShipper->company_name;
        $aAr['billing_party'] = $dataBilling->billing_party;
        return json_encode($aAr);
    }
}
