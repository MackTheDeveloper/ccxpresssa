<?php

namespace App\Http\Controllers;

use App\AeropostMaster;
use App\Aeropost;
use App\AeropostFreightCommission;
use App\AeropostInvoiceItemDetails;
use App\AeropostInvoices;
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
use stdClass;

class AeropostMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("aeropost-master.index");
    }

    public function listingmasteraeropost(Request $request)
    {
        $permissionAeropostMasterEdit = User::checkPermission(['update_aeropost_master'], '', auth()->user()->id);
        $permissionAeropostMasterDelete = User::checkPermission(['delete_aeropost_master'], '', auth()->user()->id);
        $permissionAeropostMasterAddExpense = User::checkPermission(['add_aeropost_master_expenses'], '', auth()->user()->id);
        $permissionAeropostMasterAddInvoice = User::checkPermission(['add_aeropost_master_invoices'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);
        $req = $request->all();

        $fileType = $req['fileType'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        /* Session::put('aeropostMasterListingFromDate', $req['fromDate']);
        Session::put('aeropostMasterListingToDate', $req['toDate']); */
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['aeropost_master.id', 'aeropost_master.id', 'arrival_date', 'file_number', 'tracking_number', 'c1.company_name', 'c2.company_name', ''];
        $total = AeropostMaster::selectRaw('count(*) as total');
        //->where('deleted', '0');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('aeropost_master')
            ->selectRaw('aeropost_master.*,c1.company_name as consigneeName,c2.company_name as shipperName')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name');
        //->where('aeropost_master.deleted', '0');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('aeropost_master')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name');
        //->where('aeropost_master.deleted', '0');
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
            $invoiceNumbers = AeropostMaster::getAeropostMasterInvoicesOfFile($value->id);
            $action = '<div class="dropdown"><a title="Click here to print"  target="_blank" href="' . route("printaeropostmasterfile", [$value->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete = route('deleteaeropostmaster', [$value->id]);
            $edit = route('editaeropostmaster', [$value->id]);
            if ($value->deleted == '0') {
                if ($permissionAeropostMasterEdit) {
                    $action .= '<a href="' . $edit . '" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }

                if ($permissionAeropostMasterDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['aeropost-master', $value->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionAeropostMasterAddExpense) {
                    $action .= '<li><a href="' . route('createaeropostmasterexpense', $value->id) . '">Add Expense</a></li>';
                }

                if ($permissionAeropostMasterAddInvoice) {
                    $action .= '<li><a href="' . route('createaeropostmasterinvoice', $value->id) . '">Add Invoice</a></li>';
                }

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['AeropostMaster', $value->id]) . '">Close File</a></li>';
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

    public function print($aeropostId)
    {
        $model = DB::table('aeropost_master')->where('id', $aeropostId)->first();
        $pdf = PDF::loadView('aeropost-master.printimport', ['model' => $model]);

        $pdf_file = $model->file_number . '_aeropostMaster.pdf';
        $pdf_path = 'public/aeropostMasterFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }

    public function checkoperations()
    {
        $flag = $_POST['flag'];
        if ($flag == 'checkFileAssgned') {
            $MasterAeropostId = $_POST['MasterAeropostId'];
            return AeropostMaster::checkFileAssgned($MasterAeropostId);
        }
        if ($flag == 'checkHawbFiles') {
            $MasterAeropostId = $_POST['MasterAeropostId'];
            return json_encode(AeropostMaster::checkHawbFiles($MasterAeropostId));
        }
        if ($flag == 'getMasterAeropostData') {
            $MasterAeropostId = $_POST['MasterAeropostId'];
            return json_encode(AeropostMaster::getMasterAeropostData($MasterAeropostId));
        }
    }

    public function expandhousefiles(Request $request)
    {
        $masterAeropostId = $_POST['masterAeropostId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('aeropost')->where('master_aeropost_id', $masterAeropostId)->orderBy('id', 'desc')->get();
        return view('aeropost-master.expandhousefiles', ['packageData' => $packageData, 'rowId' => $rowId]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model = new AeropostMaster();
        $model->arrival_date = date('d-m-Y');

        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);

        return view('aeropost-master._form', ['model' => $model, 'agents' => $agents]);
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
        $dataLast = DB::table('aeropost_master')->orderBy('id', 'desc')->whereNotNull('file_number')->first();
        if (empty($dataLast)) {
            $input['file_number'] = 'MAPI 1110';
        } else {
            $ab = 'MAPI ';
            $ab .= substr($dataLast->file_number, 5) + 1;
            $input['file_number'] = $ab;
        }
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;

        // Get Consigne Shipper
        $modelAeropostMaster = new AeropostMaster;
        $consigneeData = $modelAeropostMaster->getConsigneeShipper(Config::get('app.aeropostMasterImport')['consignee']);
        $shipperData = $modelAeropostMaster->getConsigneeShipper(Config::get('app.aeropostMasterImport')['shipper']);
        $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
        $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';

        $model = AeropostMaster::create($input);
        Activities::log('create', 'aeropostMaster', $model);
        $masterAeropostId = $model->id;
        $masterFileNumber = $model->file_number;
        $arrivalDate = $model->arrival_date;

        ini_set('memory_limit', '-1');
        $inputfile = $request->file('import_file');
        $fileMimeType = $inputfile->getMimeType();
        $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if (in_array($fileMimeType, $allowMimeTypes)) {
            if ($inputfile->getClientOriginalExtension() == 'xls' || $inputfile->getClientOriginalExtension() == 'xlsx') {
                $theArray = Excel::toArray(new stdClass(), $inputfile);
                $theArray = $theArray[0];
                $this->storeSub($theArray, $masterAeropostId, $masterFileNumber, $arrivalDate);
                // $allData = array();
                // Excel::load($inputfile, function ($reader) use ($request, $masterAeropostId, $masterFileNumber, $arrivalDate) {
                //     $reader->formatDates(true, 'Y-m-d');

                //     $dData = $reader->toArray();
                //     unset($dData[0]);
                //     $i = 1;
                //     foreach ($dData as $key => $row) {
                //         if ($i > 25) {
                //             break;
                //         }

                //         $allData[0][$key] = $row;
                //         unset($dData[$key]);
                //         $i++;
                //     }

                //     $counter = 1;
                //     $unset = 0;
                //     $j = 1;
                //     $k = 1;
                //     $l = 1;
                //     foreach ($dData as $key => $row) {

                //         if ($j == 30) {
                //             $l++;
                //             $k = 1;
                //             $j = 1;
                //         }
                //         $allData[$l][$k] = $row;
                //         $k++;
                //         $j++;
                //     }

                //     foreach ($allData as $key => $value) {

                //         if (count($allData[$key]) == 25) {
                //             $newData[$key] = $allData[$key];
                //         } elseif (count($allData[$key]) == 29) {
                //             unset($allData[$key][1]);
                //             unset($allData[$key][2]);
                //             unset($allData[$key][3]);
                //             unset($allData[$key][4]);

                //             $kl = 1;
                //             foreach ($allData[$key] as $key1 => $value) {
                //                 $newData[$key][$kl] = $value;
                //                 $kl++;
                //             }
                //         } else {
                //             unset($allData[$key]);
                //         }
                //     }

                //     $allData = array();
                //     foreach ($newData as $keyN => $valueN) {
                //         $input['date'] = date('Y-m-d', strtotime($valueN[1]['Custom Ticket']));
                //         $input['manifest_no'] = $valueN[4]['Custom Ticket'];
                //         $input['tracking_no'] = $valueN[5]['Custom Ticket'];
                //         $input['from_location'] = $valueN[7][1] . ',' . $valueN[8][1];
                //         $input['from_address'] = $valueN[9][1];
                //         $input['from_phone'] = $valueN[10][1];
                //         $input['from_city'] = $valueN[11][1];
                //         $input['airline'] = $valueN[12][1];
                //         $input['flight_date_time'] = date('Y-m-d H:i:s', strtotime($valueN[13][1]));
                //         $input['destination_port'] = $valueN[7][3];

                //         $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
                //         if (empty($clientData)) {
                //             $newClientData['company_name'] = $valueN[8][3];
                //             $newClientData['phone_number'] = $valueN[13][3];
                //             $newClientData['company_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
                //             Clients::Create($newClientData);
                //             $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
                //         }

                //         $input['consignee'] = $clientData->id;
                //         $input['account'] = rtrim($valueN[9][3]);
                //         $input['consignee_id'] = $valueN[10][3];
                //         $input['consignee_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
                //         $input['consignee_phone'] = $valueN[13][3];
                //         $input['destination_city'] = $valueN[14][3];
                //         $input['description'] = $valueN[15][3];
                //         $input['route'] = $valueN[16][3];
                //         $input['piece'] = $valueN[17][3];
                //         $input['total_pieces'] = $valueN[18][3];
                //         $input['real_weight'] = number_format($valueN[19][3], 2);
                //         $input['shipment_real_weight'] = $valueN[20][3];
                //         $input['volumetric_weight'] = $valueN[21][3];
                //         $input['declared_value'] = $valueN[22][3];
                //         $input['freight'] = '$' . number_format($valueN[23][3], 2);
                //         $input['total_freight'] = $valueN[23][3];
                //         $input['insurance'] = $valueN[24][3];
                //         $input['custom_value'] = $valueN[25][3];

                //         $input['created_at'] = gmdate("Y-m-d H:i:s");
                //         $input['created_by'] = auth()->user()->id;
                //         $allData[$input['account']][] = $input;
                //     }

                //     foreach ($allData as $k => $v) {
                //         //$allInput = array();
                //         $totalFiles = count($v);
                //         $allInput = $v[0];
                //         unset($v[0]);
                //         foreach ($v as $k1 => $v1) {
                //             if ($totalFiles > 1) {
                //                 $allInput['tracking_no'] .= ' / ' . $v1['tracking_no'];
                //                 $allInput['from_location'] .= ' / ' . $v1['from_location'];
                //                 $allInput['from_address'] .= ' / ' . $v1['from_address'];
                //                 $allInput['from_phone'] .= ' / ' . $v1['from_phone'];
                //                 $allInput['from_city'] .= ' / ' . $v1['from_city'];
                //                 $allInput['piece'] .= ' / ' . $v1['piece'];
                //                 $allInput['total_pieces'] += $v1['total_pieces'];
                //                 $allInput['real_weight'] .= ' / ' . $v1['real_weight'];
                //                 $allInput['shipment_real_weight'] += $v1['shipment_real_weight'];
                //                 $allInput['freight'] .= ' / ' . $v1['freight'];
                //                 $allInput['total_freight'] += $v1['total_freight'];
                //                 $allInput['description'] .= ' / ' . $v1['description'];
                //             }
                //         }

                //         $checkExist = DB::table('aeropost')->where('deleted', '0')->where('account', $allInput['account'])->where('manifest_no', $allInput['manifest_no'])->whereNotNull('manifest_no')->first();
                //         if (!empty($checkExist)) {
                //             $model = Aeropost::find($checkExist->id);
                //             $allInput['date'] = $arrivalDate;
                //             $allInput['master_aeropost_id'] = $masterAeropostId;
                //             $allInput['master_file_number'] = $masterFileNumber;
                //             $model->update($allInput);
                //         } else {
                //             $dataLast = DB::table('aeropost')->orderBy('id', 'desc')->first();
                //             $ab = 'A';
                //             if (empty($dataLast)) {
                //                 $allInput['file_number'] = 'API 1110';
                //             } else {
                //                 $ab = 'API ';
                //                 $ab .= substr($dataLast->file_number, 4) + 1;
                //                 $allInput['file_number'] = $ab;
                //             }

                //             $allInput['date'] = $arrivalDate;
                //             $allInput['master_aeropost_id'] = $masterAeropostId;
                //             $allInput['master_file_number'] = $masterFileNumber;
                //             $data = Aeropost::create($allInput);
                //             Activities::log('create', 'aeropost', $data);
                //             $dir = 'Files/Courier/Aeropost/' . $data->file_number;
                //             $filePath = $dir;
                //             //pre($filePath);
                //             $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
                //             $dataCommission['aeropost_id'] = $data->id;
                //             $dataCommission['freight'] = $allInput['total_freight'];

                //             $getCommission = DB::table('aeropost_commission')->first();
                //             if (!empty($getCommission)) {
                //                 $dataCommission['commission'] = $allInput['total_freight'] * $getCommission->commission / 100;
                //             } else {
                //                 $dataCommission['commission'] = 0.00;
                //             }

                //             $dataCommission['created_by'] = auth()->user()->id;
                //             $dataCommission['created_at'] = date('Y-m-d h:i:s');
                //             AeropostFreightCommission::Create($dataCommission);

                //             $dataInvoice['aeropost_id'] = $data->id;
                //             $dataInvoice['date'] = date('Y-m-d', strtotime($data->created_at));
                //             $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                //             if (empty($getLastInvoice)) {
                //                 $dataInvoice['bill_no'] = 'AP-5001';
                //             } else {
                //                 $ab = 'AP-';
                //                 $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                //                 $dataInvoice['bill_no'] = $ab;
                //             }

                //             $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
                //             $dataConsignee = DB::table('clients')->where('id', $data->consignee)->first();
                //             $dataInvoice['bill_to'] = $dataClient->id;
                //             $dataInvoice['email'] = $dataClient->email;
                //             $dataInvoice['telephone'] = $dataClient->phone_number;
                //             $dataInvoice['shipper'] = $data->from_address;
                //             $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                //             $dataInvoice['file_no'] = $data->file_number;
                //             $dataInvoice['awb_no'] = $data->tracking_no;
                //             $dataInvoice['type_flag'] = 'IMPORT';
                //             $dataInvoice['weight'] = $data->shipment_real_weight;
                //             $dataInvoice['currency'] = '1';
                //             $dataInvoice['created_by'] = auth()->user()->id;
                //             $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                //             $dataInvoices = AeropostInvoices::Create($dataInvoice);
                //             $dataBilling = DB::table('billing_items')->where('billing_name', 'C023/ Commission sur fret Aeropost')->first();
                //             if (!empty($dataBilling)) {
                //                 $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                //                 $dataInvoiceItems['fees_name'] = $dataBilling->id;
                //                 $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                //                 $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                //                 $dataInvoiceItems['quantity'] = 1;
                //                 $dataInvoiceItems['unit_price'] = $dataCommission['commission'];
                //                 $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];

                //                 $modelUpdateUpsInvoice = AeropostInvoices::find($dataInvoices->id);
                //                 $modelUpdateUpsInvoice->sub_total = $dataInvoiceItems['total_of_items'];
                //                 $modelUpdateUpsInvoice->total = $dataInvoiceItems['total_of_items'];
                //                 $modelUpdateUpsInvoice->balance_of = $dataInvoiceItems['total_of_items'];
                //                 $modelUpdateUpsInvoice->update();
                //             }
                //             $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                //             AeropostInvoiceItemDetails::Create($dataInvoiceItems);

                //             $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                //             $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => (array) $dataAll]);
                //             $pdf_file = 'printAeropostInvoice_' . $dataInvoices->id . '.pdf';
                //             $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
                //             $pdf->save($pdf_path);
                //         }
                //     }
                // });
            }
        }
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('aeropost-master');
    }

    public function storeSub($dData, $masterAeropostId, $masterFileNumber, $arrivalDate){
        $allData = array();
        unset($dData[0]);
        $dData = array_values($dData);
        unset($dData[0]);
        $i = 1;
        foreach ($dData as $key => $row) {
            if ($i > 25) {
                break;
            }

            $allData[0][$key] = $row;
            unset($dData[$key]);
            $i++;
        }

        $counter = 1;
        $unset = 0;
        $j = 1;
        $k = 1;
        $l = 1;
        foreach ($dData as $key => $row) {

            if ($j == 30) {
                $l++;
                $k = 1;
                $j = 1;
            }
            $allData[$l][$k] = $row;
            $k++;
            $j++;
        }

        foreach ($allData as $key => $value) {

            if (count($allData[$key]) == 25) {
                $newData[$key] = $allData[$key];
            } elseif (count($allData[$key]) == 29) {
                unset($allData[$key][1]);
                unset($allData[$key][2]);
                unset($allData[$key][3]);
                unset($allData[$key][4]);

                $kl = 1;
                foreach ($allData[$key] as $key1 => $value) {
                    $newData[$key][$kl] = $value;
                    $kl++;
                }
            } else {
                unset($allData[$key]);
            }
        }

        $allData = array();
        foreach ($newData as $keyN => $valueN) {
            $date = excelDateToDate($valueN[1][2]);
            $input['date'] = $date;
            // $input['date'] = date('Y-m-d', strtotime($valueN[1]['Custom Ticket']));
            $input['manifest_no'] = $valueN[4][2];
            $input['tracking_no'] = $valueN[5][2];
            $input['from_location'] = $valueN[7][1] . ',' . $valueN[8][1];
            $input['from_address'] = $valueN[9][1];
            $input['from_phone'] = $valueN[10][1];
            $input['from_city'] = $valueN[11][1];
            $input['airline'] = $valueN[12][1];
            $input['flight_date_time'] = date('Y-m-d H:i:s', strtotime($valueN[13][1]));
            $input['destination_port'] = $valueN[7][3];

            $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
            if (empty($clientData)) {
                $newClientData['company_name'] = $valueN[8][3];
                $newClientData['phone_number'] = $valueN[13][3];
                $newClientData['company_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
            }

            $input['consignee'] = $clientData->id;
            $input['account'] = rtrim($valueN[9][3]);
            $input['consignee_id'] = $valueN[10][3];
            $input['consignee_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
            $input['consignee_phone'] = $valueN[13][3];
            $input['destination_city'] = $valueN[14][3];
            $input['description'] = $valueN[15][3];
            $input['route'] = $valueN[16][3];
            $input['piece'] = $valueN[17][3];
            $input['total_pieces'] = $valueN[18][3];
            $input['real_weight'] = number_format($valueN[19][3], 2);
            $input['shipment_real_weight'] = $valueN[20][3];
            $input['volumetric_weight'] = $valueN[21][3];
            $input['declared_value'] = $valueN[22][3];
            $input['freight'] = '$' . number_format($valueN[23][3], 2);
            $input['total_freight'] = $valueN[23][3];
            $input['insurance'] = $valueN[24][3];
            $input['custom_value'] = $valueN[25][3];

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = auth()->user()->id;
            $allData[$input['account']][] = $input;
        }

        foreach ($allData as $k => $v) {
            //$allInput = array();
            $totalFiles = count($v);
            $allInput = $v[0];
            unset($v[0]);
            foreach ($v as $k1 => $v1) {
                if ($totalFiles > 1) {
                    $allInput['tracking_no'] .= ' / ' . $v1['tracking_no'];
                    $allInput['from_location'] .= ' / ' . $v1['from_location'];
                    $allInput['from_address'] .= ' / ' . $v1['from_address'];
                    $allInput['from_phone'] .= ' / ' . $v1['from_phone'];
                    $allInput['from_city'] .= ' / ' . $v1['from_city'];
                    $allInput['piece'] .= ' / ' . $v1['piece'];
                    $allInput['total_pieces'] += $v1['total_pieces'];
                    $allInput['real_weight'] .= ' / ' . $v1['real_weight'];
                    $allInput['shipment_real_weight'] += $v1['shipment_real_weight'];
                    $allInput['freight'] .= ' / ' . $v1['freight'];
                    $allInput['total_freight'] += $v1['total_freight'];
                    $allInput['description'] .= ' / ' . $v1['description'];
                }
            }

            $checkExist = DB::table('aeropost')->where('deleted', '0')->where('account', $allInput['account'])->where('manifest_no', $allInput['manifest_no'])->whereNotNull('manifest_no')->first();
            if (!empty($checkExist)) {
                $model = Aeropost::find($checkExist->id);
                $allInput['date'] = $arrivalDate;
                $allInput['master_aeropost_id'] = $masterAeropostId;
                $allInput['master_file_number'] = $masterFileNumber;
                $model->update($allInput);
            } else {
                $dataLast = DB::table('aeropost')->orderBy('id', 'desc')->first();
                $ab = 'A';
                if (empty($dataLast)) {
                    $allInput['file_number'] = 'API 1110';
                } else {
                    $ab = 'API ';
                    $ab .= substr($dataLast->file_number, 4) + 1;
                    $allInput['file_number'] = $ab;
                }

                $allInput['date'] = $arrivalDate;
                $allInput['master_aeropost_id'] = $masterAeropostId;
                $allInput['master_file_number'] = $masterFileNumber;
                $data = Aeropost::create($allInput);
                Activities::log('create', 'aeropost', $data);
                $dir = 'Files/Courier/Aeropost/' . $data->file_number;
                $filePath = $dir;
                //pre($filePath);
                $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
                $dataCommission['aeropost_id'] = $data->id;
                $dataCommission['freight'] = $allInput['total_freight'];

                $getCommission = DB::table('aeropost_commission')->first();
                if (!empty($getCommission)) {
                    $dataCommission['commission'] = $allInput['total_freight'] * $getCommission->commission / 100;
                } else {
                    $dataCommission['commission'] = 0.00;
                }

                $dataCommission['created_by'] = auth()->user()->id;
                $dataCommission['created_at'] = date('Y-m-d h:i:s');
                AeropostFreightCommission::Create($dataCommission);

                $dataInvoice['aeropost_id'] = $data->id;
                $dataInvoice['date'] = date('Y-m-d', strtotime($data->created_at));
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $dataInvoice['bill_no'] = 'AP-5001';
                } else {
                    $ab = 'AP-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $dataInvoice['bill_no'] = $ab;
                }

                $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
                $dataConsignee = DB::table('clients')->where('id', $data->consignee)->first();
                $dataInvoice['bill_to'] = $dataClient->id;
                $dataInvoice['email'] = $dataClient->email;
                $dataInvoice['telephone'] = $dataClient->phone_number;
                $dataInvoice['shipper'] = $data->from_address;
                $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                $dataInvoice['file_no'] = $data->file_number;
                $dataInvoice['awb_no'] = $data->tracking_no;
                $dataInvoice['type_flag'] = 'IMPORT';
                $dataInvoice['weight'] = $data->shipment_real_weight;
                $dataInvoice['currency'] = '1';
                $dataInvoice['created_by'] = auth()->user()->id;
                $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                $dataInvoices = AeropostInvoices::Create($dataInvoice);
                $dataBilling = DB::table('billing_items')->where('billing_name', 'C023/ Commission sur fret Aeropost')->first();
                if (!empty($dataBilling)) {
                    $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                    $dataInvoiceItems['fees_name'] = $dataBilling->id;
                    $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                    $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                    $dataInvoiceItems['quantity'] = 1;
                    $dataInvoiceItems['unit_price'] = $dataCommission['commission'];
                    $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];

                    $modelUpdateUpsInvoice = AeropostInvoices::find($dataInvoices->id);
                    $modelUpdateUpsInvoice->sub_total = $dataInvoiceItems['total_of_items'];
                    $modelUpdateUpsInvoice->total = $dataInvoiceItems['total_of_items'];
                    $modelUpdateUpsInvoice->balance_of = $dataInvoiceItems['total_of_items'];
                    $modelUpdateUpsInvoice->update();
                }
                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                AeropostInvoiceItemDetails::Create($dataInvoiceItems);

                $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => (array) $dataAll]);
                $pdf_file = 'printAeropostInvoice_' . $dataInvoices->id . '.pdf';
                $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
                $pdf->save($pdf_path);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AeropostMaster  $aeropostMaster
     * @return \Illuminate\Http\Response
     */
    public function show(AeropostMaster $aeropostMaster, $id)
    {
        $checkPermission = User::checkPermission(['viewdetails_aeropost_master'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'aeropostMaster')->orderBy('id', 'desc')->get()->toArray();
        $HouseAWBData = DB::table('aeropost')->where('master_aeropost_id', $id)->orderBy('id', 'desc')->get();

        $model = AeropostMaster::find($id);
        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.aeropost_master_id', $id)
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
            ->whereNotNull('aeropost_master_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('aeropost_master_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();

        $attachedFiles = DB::table('aeropost_uploaded_files')->where('master_file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('aeropost_master', 'expenses.aeropost_master_id', '=', 'aeropost_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.aeropost_master_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('aeropost_master', 'expenses.aeropost_master_id', '=', 'aeropost_master.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.aeropost_master_id', $id)
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
            ->where('invoices.aeropost_master_id', $id)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.aeropost_master_id', $id)
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

        return view("aeropost-master.view-details", ['model' => $model, 'invoices' => $invoices, 'activityData' => $activityData, 'dataExpense' => $dataExpense, 'id' => $id, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'finalReportData' => $finalReportData, 'HouseAWBData' => $HouseAWBData]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AeropostMaster  $aeropostMaster
     * @return \Illuminate\Http\Response
     */
    public function edit(AeropostMaster $aeropostMaster, $id, $fileType = null)
    {
        $model = DB::table('aeropost_master')->where('id', $id)->first();
        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        $agents = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('department', 12)->pluck('name', 'id');
        $agents = json_decode($agents, 1);
        ksort($agents);
        return view('aeropost-master._form', ['model' => $model, 'agents' => $agents, 'fileType' => $fileType, 'billingParty' => $billingParty]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AeropostMaster  $aeropostMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AeropostMaster $aeropostMaster, $id)
    {
        $model = AeropostMaster::find($id);
        $input = $request->all();
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $masterAeropostId = $model->id;
        $masterFileNumber = $model->file_number;
        $arrivalDate = $model->arrival_date;
        $model->fill($input);
        $modelAeropostMaster = new AeropostMaster;
        Activities::log('update', 'aeropostMaster', $model);
        $consigneeData = $modelAeropostMaster->getConsigneeShipper(Config::get('app.aeropostMasterImport')['consignee']);
        $shipperData = $modelAeropostMaster->getConsigneeShipper(Config::get('app.aeropostMasterImport')['shipper']);
        $input['consignee_name'] = !empty($consigneeData) ? $consigneeData->id : '';
        $input['shipper_name'] = !empty($shipperData) ? $shipperData->id : '';

        $model->update($input);
        $inputfile = $request->file('import_file');
        $fileMimeType = $inputfile->getMimeType();
        $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        if (in_array($fileMimeType, $allowMimeTypes)) {
            if ($inputfile->getClientOriginalExtension() == 'xls' || $inputfile->getClientOriginalExtension() == 'xlsx') {
                if (!empty($inputfile)) {
                    $theArray = Excel::toArray(new stdClass(), $inputfile);
                    $theArray = $theArray[0];
                    $this->updateSub($theArray, $masterAeropostId, $masterFileNumber, $arrivalDate);
                    // $allData = array();
                    // Excel::load($inputfile, function ($reader) use ($request, $masterAeropostId, $masterFileNumber, $arrivalDate) {
                    //     $reader->formatDates(true, 'Y-m-d');

                    //     $dData = $reader->toArray();
                    //     unset($dData[0]);
                    //     $i = 1;
                    //     foreach ($dData as $key => $row) {
                    //         if ($i > 25) {
                    //             break;
                    //         }

                    //         $allData[0][$key] = $row;
                    //         unset($dData[$key]);
                    //         $i++;
                    //     }

                    //     $counter = 1;
                    //     $unset = 0;
                    //     $j = 1;
                    //     $k = 1;
                    //     $l = 1;
                    //     foreach ($dData as $key => $row) {

                    //         if ($j == 30) {
                    //             $l++;
                    //             $k = 1;
                    //             $j = 1;
                    //         }
                    //         $allData[$l][$k] = $row;
                    //         $k++;
                    //         $j++;
                    //     }

                    //     foreach ($allData as $key => $value) {

                    //         if (count($allData[$key]) == 25) {
                    //             $newData[$key] = $allData[$key];
                    //         } elseif (count($allData[$key]) == 29) {
                    //             unset($allData[$key][1]);
                    //             unset($allData[$key][2]);
                    //             unset($allData[$key][3]);
                    //             unset($allData[$key][4]);

                    //             $kl = 1;
                    //             foreach ($allData[$key] as $key1 => $value) {
                    //                 $newData[$key][$kl] = $value;
                    //                 $kl++;
                    //             }
                    //         } else {
                    //             unset($allData[$key]);
                    //         }
                    //     }

                    //     $allData = array();
                    //     foreach ($newData as $keyN => $valueN) {
                    //         $input['date'] = date('Y-m-d', strtotime($valueN[1]['Custom Ticket']));
                    //         $input['manifest_no'] = $valueN[4]['Custom Ticket'];
                    //         $input['tracking_no'] = $valueN[5]['Custom Ticket'];
                    //         $input['from_location'] = $valueN[7][1] . ',' . $valueN[8][1];
                    //         $input['from_address'] = $valueN[9][1];
                    //         $input['from_phone'] = $valueN[10][1];
                    //         $input['from_city'] = $valueN[11][1];
                    //         $input['airline'] = $valueN[12][1];
                    //         $input['flight_date_time'] = date('Y-m-d H:i:s', strtotime($valueN[13][1]));
                    //         $input['destination_port'] = $valueN[7][3];

                    //         $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
                    //         if (empty($clientData)) {
                    //             $newClientData['company_name'] = $valueN[8][3];
                    //             $newClientData['phone_number'] = $valueN[13][3];
                    //             $newClientData['company_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
                    //             Clients::Create($newClientData);
                    //             $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
                    //         }

                    //         $input['consignee'] = $clientData->id;
                    //         $input['account'] = rtrim($valueN[9][3]);
                    //         $input['consignee_id'] = $valueN[10][3];
                    //         $input['consignee_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
                    //         $input['consignee_phone'] = $valueN[13][3];
                    //         $input['destination_city'] = $valueN[14][3];
                    //         $input['description'] = $valueN[15][3];
                    //         $input['route'] = $valueN[16][3];
                    //         $input['piece'] = $valueN[17][3];
                    //         $input['total_pieces'] = $valueN[18][3];
                    //         $input['real_weight'] = number_format($valueN[19][3], 2);
                    //         $input['shipment_real_weight'] = $valueN[20][3];
                    //         $input['volumetric_weight'] = $valueN[21][3];
                    //         $input['declared_value'] = $valueN[22][3];
                    //         $input['freight'] = '$' . number_format($valueN[23][3], 2);
                    //         $input['total_freight'] = $valueN[23][3];
                    //         $input['insurance'] = $valueN[24][3];
                    //         $input['custom_value'] = $valueN[25][3];

                    //         $input['created_at'] = gmdate("Y-m-d H:i:s");
                    //         $input['created_by'] = auth()->user()->id;
                    //         $allData[$input['account']][] = $input;
                    //     }

                    //     foreach ($allData as $k => $v) {
                    //         //$allInput = array();
                    //         $totalFiles = count($v);
                    //         $allInput = $v[0];
                    //         unset($v[0]);
                    //         foreach ($v as $k1 => $v1) {
                    //             if ($totalFiles > 1) {
                    //                 $allInput['tracking_no'] .= ' / ' . $v1['tracking_no'];
                    //                 $allInput['from_location'] .= ' / ' . $v1['from_location'];
                    //                 $allInput['from_address'] .= ' / ' . $v1['from_address'];
                    //                 $allInput['from_phone'] .= ' / ' . $v1['from_phone'];
                    //                 $allInput['from_city'] .= ' / ' . $v1['from_city'];
                    //                 $allInput['piece'] .= ' / ' . $v1['piece'];
                    //                 $allInput['total_pieces'] += $v1['total_pieces'];
                    //                 $allInput['real_weight'] .= ' / ' . $v1['real_weight'];
                    //                 $allInput['shipment_real_weight'] += $v1['shipment_real_weight'];
                    //                 $allInput['freight'] .= ' / ' . $v1['freight'];
                    //                 $allInput['total_freight'] += $v1['total_freight'];
                    //                 $allInput['description'] .= ' / ' . $v1['description'];
                    //             }
                    //         }

                    //         $checkExist = DB::table('aeropost')->where('deleted', '0')->where('account', $allInput['account'])->where('manifest_no', $allInput['manifest_no'])->whereNotNull('manifest_no')->first();
                    //         if (!empty($checkExist)) {
                    //             $model = Aeropost::find($checkExist->id);
                    //             $allInput['date'] = $arrivalDate;
                    //             $allInput['master_aeropost_id'] = $masterAeropostId;
                    //             $allInput['master_file_number'] = $masterFileNumber;
                    //             $model->update($allInput);
                    //         } else {
                    //             $dataLast = DB::table('aeropost')->orderBy('id', 'desc')->first();
                    //             $ab = 'A';
                    //             if (empty($dataLast)) {
                    //                 $allInput['file_number'] = 'API 1110';
                    //             } else {
                    //                 $ab = 'API ';
                    //                 $ab .= substr($dataLast->file_number, 4) + 1;
                    //                 $allInput['file_number'] = $ab;
                    //             }

                    //             $allInput['date'] = $arrivalDate;
                    //             $allInput['master_aeropost_id'] = $masterAeropostId;
                    //             $allInput['master_file_number'] = $masterFileNumber;
                    //             $data = Aeropost::create($allInput);
                    //             Activities::log('create', 'aeropost', $data);
                    //             $dir = 'Files/Courier/Aeropost/' . $data->file_number;
                    //             $filePath = $dir;
                    //             //pre($filePath);
                    //             $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
                    //             $dataCommission['aeropost_id'] = $data->id;
                    //             $dataCommission['freight'] = $allInput['total_freight'];

                    //             $getCommission = DB::table('aeropost_commission')->first();
                    //             if (!empty($getCommission)) {
                    //                 $dataCommission['commission'] = $allInput['total_freight'] * $getCommission->commission / 100;
                    //             } else {
                    //                 $dataCommission['commission'] = 0.00;
                    //             }

                    //             $dataCommission['created_by'] = auth()->user()->id;
                    //             $dataCommission['created_at'] = date('Y-m-d h:i:s');
                    //             AeropostFreightCommission::Create($dataCommission);

                    //             $dataInvoice['aeropost_id'] = $data->id;
                    //             $dataInvoice['date'] = date('Y-m-d', strtotime($data->created_at));
                    //             $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                    //             if (empty($getLastInvoice)) {
                    //                 $dataInvoice['bill_no'] = 'AP-5001';
                    //             } else {
                    //                 $ab = 'AP-';
                    //                 $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    //                 $dataInvoice['bill_no'] = $ab;
                    //             }

                    //             $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
                    //             $dataConsignee = DB::table('clients')->where('id', $data->consignee)->first();
                    //             $dataInvoice['bill_to'] = $dataClient->id;
                    //             $dataInvoice['email'] = $dataClient->email;
                    //             $dataInvoice['telephone'] = $dataClient->phone_number;
                    //             $dataInvoice['shipper'] = $data->from_address;
                    //             $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                    //             $dataInvoice['file_no'] = $data->file_number;
                    //             $dataInvoice['awb_no'] = $data->tracking_no;
                    //             $dataInvoice['type_flag'] = 'IMPORT';
                    //             $dataInvoice['weight'] = $data->shipment_real_weight;
                    //             $dataInvoice['currency'] = '1';
                    //             $dataInvoice['created_by'] = auth()->user()->id;
                    //             $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                    //             $dataInvoices = AeropostInvoices::Create($dataInvoice);
                    //             $dataBilling = DB::table('billing_items')->where('billing_name', 'C023/ Commission sur fret Aeropost')->first();
                    //             if (!empty($dataBilling)) {
                    //                 $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                    //                 $dataInvoiceItems['fees_name'] = $dataBilling->id;
                    //                 $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                    //                 $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                    //                 $dataInvoiceItems['quantity'] = 1;
                    //                 $dataInvoiceItems['unit_price'] = $dataCommission['commission'];
                    //                 $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];

                    //                 $modelUpdateUpsInvoice = AeropostInvoices::find($dataInvoices->id);
                    //                 $modelUpdateUpsInvoice->sub_total = $dataInvoiceItems['total_of_items'];
                    //                 $modelUpdateUpsInvoice->total = $dataInvoiceItems['total_of_items'];
                    //                 $modelUpdateUpsInvoice->balance_of = $dataInvoiceItems['total_of_items'];
                    //                 $modelUpdateUpsInvoice->update();
                    //             }
                    //             $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                    //             AeropostInvoiceItemDetails::Create($dataInvoiceItems);

                    //             $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                    //             $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => (array) $dataAll]);
                    //             $pdf_file = 'printAeropostInvoice_' . $dataInvoices->id . '.pdf';
                    //             $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
                    //             $pdf->save($pdf_path);
                    //         }
                    //     }
                    // });
                }
            }
        }
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('aeropost-master');
    }
    public function updateSub($dData, $masterAeropostId, $masterFileNumber, $arrivalDate){
        $allData = array();
        unset($dData[0]);
        $dData = array_values($dData);
        unset($dData[0]);
        $i = 1;
        foreach ($dData as $key => $row) {
            if ($i > 25) {
                break;
            }

            $allData[0][$key] = $row;
            unset($dData[$key]);
            $i++;
        }

        $counter = 1;
        $unset = 0;
        $j = 1;
        $k = 1;
        $l = 1;
        foreach ($dData as $key => $row) {

            if ($j == 30) {
                $l++;
                $k = 1;
                $j = 1;
            }
            $allData[$l][$k] = $row;
            $k++;
            $j++;
        }

        foreach ($allData as $key => $value) {

            if (count($allData[$key]) == 25) {
                $newData[$key] = $allData[$key];
            } elseif (count($allData[$key]) == 29) {
                unset($allData[$key][1]);
                unset($allData[$key][2]);
                unset($allData[$key][3]);
                unset($allData[$key][4]);

                $kl = 1;
                foreach ($allData[$key] as $key1 => $value) {
                    $newData[$key][$kl] = $value;
                    $kl++;
                }
            } else {
                unset($allData[$key]);
            }
        }

        $allData = array();
        foreach ($newData as $keyN => $valueN) {
            $date = excelDateToDate($valueN[1][2]);
            $input['date'] = $date;
            // $input['date'] = date('Y-m-d', strtotime($valueN[1]['Custom Ticket']));
            $input['manifest_no'] = $valueN[4][2];
            $input['tracking_no'] = $valueN[5][2];
            $input['from_location'] = $valueN[7][1] . ',' . $valueN[8][1];
            $input['from_address'] = $valueN[9][1];
            $input['from_phone'] = $valueN[10][1];
            $input['from_city'] = $valueN[11][1];
            $input['airline'] = $valueN[12][1];
            $input['flight_date_time'] = date('Y-m-d H:i:s', strtotime($valueN[13][1]));
            $input['destination_port'] = $valueN[7][3];

            $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
            if (empty($clientData)) {
                $newClientData['company_name'] = $valueN[8][3];
                $newClientData['phone_number'] = $valueN[13][3];
                $newClientData['company_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
                Clients::Create($newClientData);
                $clientData = DB::table('clients')->where('company_name', $valueN[8][3])->first();
            }

            $input['consignee'] = $clientData->id;
            $input['account'] = rtrim($valueN[9][3]);
            $input['consignee_id'] = $valueN[10][3];
            $input['consignee_address'] = $valueN[11][3] . ', ' . $valueN[12][3];
            $input['consignee_phone'] = $valueN[13][3];
            $input['destination_city'] = $valueN[14][3];
            $input['description'] = $valueN[15][3];
            $input['route'] = $valueN[16][3];
            $input['piece'] = $valueN[17][3];
            $input['total_pieces'] = $valueN[18][3];
            $input['real_weight'] = number_format($valueN[19][3], 2);
            $input['shipment_real_weight'] = $valueN[20][3];
            $input['volumetric_weight'] = $valueN[21][3];
            $input['declared_value'] = $valueN[22][3];
            $input['freight'] = '$' . number_format($valueN[23][3], 2);
            $input['total_freight'] = $valueN[23][3];
            $input['insurance'] = $valueN[24][3];
            $input['custom_value'] = $valueN[25][3];

            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['created_by'] = auth()->user()->id;
            $allData[$input['account']][] = $input;
        }

        foreach ($allData as $k => $v) {
            //$allInput = array();
            $totalFiles = count($v);
            $allInput = $v[0];
            unset($v[0]);
            foreach ($v as $k1 => $v1) {
                if ($totalFiles > 1) {
                    $allInput['tracking_no'] .= ' / ' . $v1['tracking_no'];
                    $allInput['from_location'] .= ' / ' . $v1['from_location'];
                    $allInput['from_address'] .= ' / ' . $v1['from_address'];
                    $allInput['from_phone'] .= ' / ' . $v1['from_phone'];
                    $allInput['from_city'] .= ' / ' . $v1['from_city'];
                    $allInput['piece'] .= ' / ' . $v1['piece'];
                    $allInput['total_pieces'] += $v1['total_pieces'];
                    $allInput['real_weight'] .= ' / ' . $v1['real_weight'];
                    $allInput['shipment_real_weight'] += $v1['shipment_real_weight'];
                    $allInput['freight'] .= ' / ' . $v1['freight'];
                    $allInput['total_freight'] += $v1['total_freight'];
                    $allInput['description'] .= ' / ' . $v1['description'];
                }
            }

            $checkExist = DB::table('aeropost')->where('deleted', '0')->where('account', $allInput['account'])->where('manifest_no', $allInput['manifest_no'])->whereNotNull('manifest_no')->first();
            if (!empty($checkExist)) {
                $model = Aeropost::find($checkExist->id);
                $allInput['date'] = $arrivalDate;
                $allInput['master_aeropost_id'] = $masterAeropostId;
                $allInput['master_file_number'] = $masterFileNumber;
                $model->update($allInput);
            } else {
                $dataLast = DB::table('aeropost')->orderBy('id', 'desc')->first();
                $ab = 'A';
                if (empty($dataLast)) {
                    $allInput['file_number'] = 'API 1110';
                } else {
                    $ab = 'API ';
                    $ab .= substr($dataLast->file_number, 4) + 1;
                    $allInput['file_number'] = $ab;
                }

                $allInput['date'] = $arrivalDate;
                $allInput['master_aeropost_id'] = $masterAeropostId;
                $allInput['master_file_number'] = $masterFileNumber;
                $data = Aeropost::create($allInput);
                Activities::log('create', 'aeropost', $data);
                $dir = 'Files/Courier/Aeropost/' . $data->file_number;
                $filePath = $dir;
                //pre($filePath);
                $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
                $dataCommission['aeropost_id'] = $data->id;
                $dataCommission['freight'] = $allInput['total_freight'];

                $getCommission = DB::table('aeropost_commission')->first();
                if (!empty($getCommission)) {
                    $dataCommission['commission'] = $allInput['total_freight'] * $getCommission->commission / 100;
                } else {
                    $dataCommission['commission'] = 0.00;
                }

                $dataCommission['created_by'] = auth()->user()->id;
                $dataCommission['created_at'] = date('Y-m-d h:i:s');
                AeropostFreightCommission::Create($dataCommission);

                $dataInvoice['aeropost_id'] = $data->id;
                $dataInvoice['date'] = date('Y-m-d', strtotime($data->created_at));
                $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
                if (empty($getLastInvoice)) {
                    $dataInvoice['bill_no'] = 'AP-5001';
                } else {
                    $ab = 'AP-';
                    $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                    $dataInvoice['bill_no'] = $ab;
                }

                $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
                $dataConsignee = DB::table('clients')->where('id', $data->consignee)->first();
                $dataInvoice['bill_to'] = $dataClient->id;
                $dataInvoice['email'] = $dataClient->email;
                $dataInvoice['telephone'] = $dataClient->phone_number;
                $dataInvoice['shipper'] = $data->from_address;
                $dataInvoice['consignee_address'] = $dataConsignee->company_name;
                $dataInvoice['file_no'] = $data->file_number;
                $dataInvoice['awb_no'] = $data->tracking_no;
                $dataInvoice['type_flag'] = 'IMPORT';
                $dataInvoice['weight'] = $data->shipment_real_weight;
                $dataInvoice['currency'] = '1';
                $dataInvoice['created_by'] = auth()->user()->id;
                $dataInvoice['created_at'] = date('Y-m-d h:i:s');
                $dataInvoices = AeropostInvoices::Create($dataInvoice);
                $dataBilling = DB::table('billing_items')->where('billing_name', 'C023/ Commission sur fret Aeropost')->first();
                if (!empty($dataBilling)) {
                    $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                    $dataInvoiceItems['fees_name'] = $dataBilling->id;
                    $dataInvoiceItems['item_code'] = $dataBilling->item_code;
                    $dataInvoiceItems['fees_name_desc'] = $dataBilling->billing_name;
                    $dataInvoiceItems['quantity'] = 1;
                    $dataInvoiceItems['unit_price'] = $dataCommission['commission'];
                    $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];

                    $modelUpdateUpsInvoice = AeropostInvoices::find($dataInvoices->id);
                    $modelUpdateUpsInvoice->sub_total = $dataInvoiceItems['total_of_items'];
                    $modelUpdateUpsInvoice->total = $dataInvoiceItems['total_of_items'];
                    $modelUpdateUpsInvoice->balance_of = $dataInvoiceItems['total_of_items'];
                    $modelUpdateUpsInvoice->update();
                }
                $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
                AeropostInvoiceItemDetails::Create($dataInvoiceItems);

                $dataAll = DB::table('invoices')->where('id', $dataInvoices->id)->first();
                $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice', ['invoice' => (array) $dataAll]);
                $pdf_file = 'printAeropostInvoice_' . $dataInvoices->id . '.pdf';
                $pdf_path = 'public/aeropostInvoices/' . $pdf_file;
                $pdf->save($pdf_path);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AeropostMaster  $aeropostMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy(AeropostMaster $aeropostMaster, $id)
    {
        DB::table('aeropost_master')->where('id', $id)->update(['deleted' => 1, 'deleted_on' => date('Y-m-d h:i:s'), 'deleted_by' => auth()->user()->id]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'aeropostMaster';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function checkuniqueaeropostmasterawbnumber()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit') {
            $upsData = DB::table('aeropost_master')->where('deleted', '0')->where('tracking_number', $number)->where('id', '<>', $id)->count();
        } else {
            $upsData = DB::table('aeropost_master')->where('deleted', '0')->where('tracking_number', $number)->count();
        }
        if ($upsData) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getaeropostmasterdata()
    {
        $id = $_POST['aeropostMasterId'];
        $aAr = array();
        $dataBilling = DB::table('aeropost_master')->where('id', $id)->first();
        $dataConsignee = DB::table('clients')->where('id', $dataBilling->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $dataBilling->shipper_name)->first();
        $aAr['consigneeName'] = !empty($dataConsignee) ? $dataConsignee->company_name : '';
        $aAr['shipperName'] = !empty($dataShipper) ? $dataShipper->company_name : '';
        $aAr['billing_party'] = !empty($dataBilling) ? $dataBilling->billing_party : '';
        return json_encode($aAr);
    }
}
