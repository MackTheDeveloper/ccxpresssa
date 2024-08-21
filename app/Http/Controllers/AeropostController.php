<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Aeropost;
use App\AeropostFreightCommission;
use App\AeropostInvoiceItemDetails;
use App\AeropostInvoices;
use App\Clients;
use App\User;
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

class AeropostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_aeropost'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        return view("aeropost.index");
    }

    public function import()
    {
        $checkPermission = User::checkPermission(['upload_aeropost'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = new Aeropost;
        return view('aeropost.import', ['model' => $model]);
    }

    public function importdata(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        $storage = $request->get('storage');
        if ($request->get('s3file')) {
            $file = $request->get('s3file');
        }
        //$data = Excel::load($request->file('import_file'))->get()->toArray();
        /*if(empty($data))
        pre("fff");
        else
        pre($data); */

        try {
            if ($storage == 2) {
                $filecontent = file_get_contents('https://s3.us-east-1.amazonaws.com/cargo-live-site' . $file);
                $basepath = 'storage/app/temp/';
                $arr = explode('/', $file);
                $tmpname = $arr[count($arr) - 1];

                $success = Storage::disk('local')->put('/temp/' . $tmpname, $filecontent, 'public');
                //pre($success);
                $inputfile = $basepath . $tmpname;
            } else {
                $inputfile = $request->file('import_file');
                $fileMimeType = $inputfile->getMimeType();
                $allowMimeTypes = array('application/vnd.ms-excel', 'application/vnd.ms-office', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                if (in_array($fileMimeType, $allowMimeTypes)) {
                    if ($inputfile->getClientOriginalExtension() != 'xls' && $inputfile->getClientOriginalExtension() != 'xlsx') {
                        Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                        return redirect('importaeropost');
                    }
                } else {
                    Session::flash('flash_message_error', 'Please select a xls OR xlsx file');
                    return redirect('importaeropost');
                }
            }
            // $allData = array();
            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            $this->importDataSub($theArray);
            
            Session::flash('flash_message', 'Record has been imported successfully');
            if ($storage == 2 && $success) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            return redirect('aeroposts');
        } catch (Exception $e) {
            Session::flash('flash_message_error', 'Record has been imported successfully');
            if ($success) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            return redirect('aeroposts');
        }
    }

    public function importDataSub($dData){
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
            // dd(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valueN[2][2])->getTimestamp());
            // dd($datestring);
            $input['date'] = $date;
            // $input['date'] = date('Y-m-d', strtotime($valueN[1][2]));
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
        // dd($allData);die;
        foreach ($allData as $k => $v) {
            //$allInput = array();
            $totalFiles = count($v);
            //pre($totalFiles,1);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_aeropost'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = new Aeropost;
        $model->date = date('d-m-Y');
        $clientDatas = Clients::getClientsAutocomplete();

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');
        return view('aeropost._form', ['model' => $model, 'clientDatas' => $clientDatas, 'warehouses' => $warehouses]);
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
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $input['created_by'] = auth()->user()->id;
        $newClientData = array();
        if (empty($input['consignee'])) {
            $newClientData['company_name'] = $input['consignee_autocomplete'];
            $newClientData['phone_number'] = $input['consignee_phone'];
            $newClientData['company_address'] = $input['consignee_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $input['consignee_autocomplete'])->first();
            $input['consignee'] = $clientData->id;
        }
        $input['date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;


        $dataLast = DB::table('aeropost')->orderBy('id', 'desc')->first();
        $ab = 'A';
        if (empty($dataLast)) {
            $input['file_number'] = 'API 1110';
        } else {
            $ab = 'API ';
            $ab .= substr($dataLast->file_number, 4) + 1;
            $input['file_number'] = $ab;
        }

        $data = Aeropost::create($input);
        Activities::log('create', 'aeropost', $data);
        $dir = 'Files/Courier/Aeropost/' . $data->file_number;
        $filePath = $dir;
        //pre($filePath);
        $success = Storage::disk('s3')->makeDirectory($filePath, '', 'public');
        //pre($success.' '.'test');
        $dataCommission['aeropost_id'] = $data->id;
        $dataCommission['total_freight'] = $input['total_freight'];

        $getCommission = DB::table('aeropost_commission')->first();
        if (!empty($getCommission)) {
            $dataCommission['commission'] = $input['total_freight'] * $getCommission->commission / 100;
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

        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('aeroposts');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Aeropost  $aeropost
     * @return \Illuminate\Http\Response
     */
    public function show(Aeropost $aeropost)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Aeropost  $aeropost
     * @return \Illuminate\Http\Response
     */
    public function edit(Aeropost $aeropost, $id)
    {
        $model = DB::table('aeropost')->where('id', $id)->first();

        $clientDatas = Clients::getClientsAutocomplete();
        $clients = new Clients();

        $getClientData = $clients->getClientData($model->consignee);
        $model->consignee_autocomplete = $getClientData->company_name;


        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->pluck('company_name', 'id');
        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');
        return view('aeropost._form', ['model' => $model, 'clientDatas' => $clientDatas, 'warehouses' => $warehouses, 'billingParty' => $billingParty, 'warehouses' => $warehouses]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Aeropost  $aeropost
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Aeropost $aeropost, $id)
    {
        $model = Aeropost::find($id);
        $model->fill($request->input());
        Activities::log('update', 'aeropost', $model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $request['updated_by'] = auth()->user()->id;
        $input = $request->all();

        $newClientData = array();
        if (empty($input['consignee'])) {
            $newClientData['company_name'] = $input['consignee_autocomplete'];
            $newClientData['phone_number'] = $input['consignee_phone'];
            $newClientData['company_address'] = $input['consignee_address'];
            Clients::Create($newClientData);
            $clientData = DB::table('clients')->where('company_name', $input['consignee_autocomplete'])->first();
            $input['consignee'] = $clientData->id;
        }
        $input['date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;
        $model->update($input);

        /* $getCommission = DB::table('aeropost_commission')->first();
        if (!empty($getCommission)) {
            $commission = $input['total_freight'] * $getCommission->commission / 100;
        } else {
            $commission['commission'] = 0.00;
        }

        DB::table('aeropost_freight_commission')
            ->where('aeropost_id', $model->id)
            ->update(['commission' => $commission, 'freight' => $model->total_freight, 'updated_at' => gmdate("Y-m-d H:i:s"), 'updated_by' => auth()->user()->id]); */

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('aeroposts');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Aeropost  $aeropost
     * @return \Illuminate\Http\Response
     */
    public function destroy(Aeropost $aeropost, $id)
    {
        $model = Aeropost::where('id', $id)->update(['deleted' => '1', 'deleted_at' => gmdate("Y-m-d H:i:s"), 'deleted_by' => auth()->user()->id]);

        // Store payment deleted activity on file level
        $modelActivities = new Activities;
        $modelActivities->type = 'aeropost';
        $modelActivities->related_id = $id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File has been Cancelled";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
    }

    public function viewdetails($id)
    {
        $model = Aeropost::find($id);

        $dataExpense = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('aeropost_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('aeropost_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();

        $aeropostInvoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.aeropost_id', $id)
            ->orderBy('invoices.id', 'desc')
            ->get();

        $totalInvoiceOfHTG = 0;
        $totalInvoiceOfUSD = 0;

        foreach ($aeropostInvoices as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInvoiceOfUSD += $v->total;

            if ($v->currencyCode == 'HTG')
                $totalInvoiceOfHTG += $v->total;
        }


        $path = 'Files/Courier/Aeropost/' . $model->file_number;
        $attachedFiles = DB::table('aeropost_uploaded_files')->where('file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
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
            ->join('aeropost', 'expenses.aeropost_id', '=', 'aeropost.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.aeropost_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('aeropost', 'expenses.aeropost_id', '=', 'aeropost.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.aeropost_id', $id)
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
            ->where('invoices.aeropost_id', $id)
            ->where('invoices.deleted', '0')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.aeropost_id', $id)
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
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'aeropost')->orderBy('id', 'desc')->get()->toArray();
        return view('aeropost.view-details', ['id' => $id, 'model' => $model, 'aeropostInvoices' => $aeropostInvoices, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'path' => $path, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'dataExpense' => $dataExpense, 'activityData' => $activityData, 'finalReportData' => $finalReportData]);
    }

    public function checkuniqueawbnumberofaeropost()
    {
        $number = $_POST['number'];
        $flag = $_POST['flag'];
        $id = $_POST['idz'];
        if ($flag == 'edit') {
            $upsData = DB::table('aeropost')->where('deleted', '0')->where('tracking_no', $number)->where('id', '<>', $id)->count();
        } else {
            $upsData = DB::table('aeropost')->where('deleted', '0')->where('tracking_no', $number)->count();
        }

        if ($upsData) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getaeropostdata()
    {
        $id = $_POST['aeropostId'];
        $aAr = array();
        $dataAeropost = DB::table('aeropost')->where('id', $id)->first();

        $dataConsignee = DB::table('clients')->where('id', $dataAeropost->consignee)->first();
        $dataShipper = $dataAeropost->from_location;

        $aAr['consigneeName'] = !empty($dataConsignee->company_name) ? $dataConsignee->company_name : '-';
        $aAr['shipperName'] = !empty($dataShipper) ? $dataShipper : '-';
        $aAr['billing_party'] = $dataAeropost->billing_party;
        return json_encode($aAr);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionAeropostEdit = User::checkPermission(['update_aeropost'], '', auth()->user()->id);
        $permissionAeropostDelete = User::checkPermission(['delete_aeropost'], '', auth()->user()->id);
        $permissionAeropostAddInvoice = User::checkPermission(['add_aeropost_invoices'], '', auth()->user()->id);
        $permissionAeropostExpensesAdd = User::checkPermission(['add_aeropost_expenses'], '', auth()->user()->id);
        $permissionCloseFile = User::checkPermission(['close_file'], '', auth()->user()->id);

        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        /* Session::put('aeropostListingFromDate', $req['fromDate']);
        Session::put('aeropostListingToDate', $req['toDate']); */
        $fileStatus = $req['fileStatus'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['aeropost.id', 'aeropost.file_number', 'master_file_number', 'c3.company_name', 'aeropost_scan_status', '', 'aeropost.date', 'from_location', 'c1.company_name', 'total_freight', 'aeropost.tracking_no'];

        if (checkloggedinuserdata() == 'Warehouse') {
            $getWarehouseOfUser =  DB::table('users')
                ->select('warehouses')
                ->where('id', auth()->user()->id)
                ->first();
            $wh = explode(',', $getWarehouseOfUser->warehouses);
        }

        $total = Aeropost::selectRaw('count(*) as total');
        //->where('deleted', '0');
        /* if (checkloggedinuserdata() == 'Warehouse')
            $total = $total->whereIn('warehouse', $wh); */
        if (!empty($fileStatus)) {
            $total = $total->where('aeropost_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('aeropost.date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('aeropost')
            ->selectRaw('aeropost.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
            ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party');
        //->where('aeropost.deleted', '0');
        /* if (checkloggedinuserdata() == 'Warehouse')
            $query = $query->whereIn('warehouse', $wh); */
        if (!empty($fileStatus)) {
            $query = $query->where('aeropost_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('aeropost.date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('aeropost')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
            ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party');
        //->where('aeropost.deleted', '0');
        /* if (checkloggedinuserdata() == 'Warehouse')
            $filteredq = $filteredq->whereIn('warehouse', $wh); */
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('aeropost_scan_status', $fileStatus);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('aeropost.date', array($fromDate, $toDate));
        }

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(aeropost.date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('from_location', 'like', '%' . $search . '%')
                    ->orWhere('total_freight', 'like', '%' . $search . '%')
                    ->orWhere('aeropost.tracking_no', 'like', '%' . $search . '%');
                //->orWhere('aeropost_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('aeropost.file_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("date_format(aeropost.date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
                    ->orWhere('from_location', 'like', '%' . $search . '%')
                    ->orWhere('total_freight', 'like', '%' . $search . '%')
                    ->orWhere('aeropost.tracking_no', 'like', '%' . $search . '%');
                //->orWhere('aeropost_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $dataBillingParty = app('App\Clients')->getClientData($items->billing_party);
            $consigneeData = app('App\Clients')->getClientData($items->consignee);
            $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
            $invoiceNumbers = Expense::getAeropostInvoicesOfFile($items->id);

            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printaeropostfile", [$items->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            $delete =  route('deleteaeropost', $items->id);
            $edit =  route('editaeropost', $items->id);
            if ($items->deleted == '0') {
                if ($permissionAeropostEdit) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }
                if ($permissionAeropostDelete) {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
                $action .= '<a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="' . url('files/upload', ['aeropost', $items->id]) . '" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>';
                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionAeropostExpensesAdd) {
                    $action .= '<li><a href="' . route('aeropostexpensecreate', $items->id) . '">Add Expense</a></li>';
                }

                if ($permissionAeropostAddInvoice) {
                    $action .= '<li><a href="' . route('createaeropostinvoice', $items->id) . '">Add Invoice</a></li>';
                }

                $action .= '<li><button id="btnAddWarehouseInFile" data-module ="Warehouse" class="btnModalPopup" value="' . route('addwarehouseinfile', [$items->id, 'aeropost']) . '">Add Warehouse</button></li>';

                if ($permissionCloseFile) {
                    $action .= '<li><a href="' . route('closefilessubmitsingle', ['Aeropost', $items->id]) . '">Close File</a></li>';
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $closedDetail = '';
            if ($items->file_close == 1) {
                $dataUserCloseFile = DB::table('users')->where('id', $items->close_unclose_by)->first();
                $closedDetail .= !empty($items->close_unclose_date) ? date('d-m-Y', strtotime($items->close_unclose_date)) : '-';
                $closedDetail .= ' | ';
                $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
            }

            $data[] = [$items->id, $items->file_number, !empty($items->master_file_number) ? $items->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($items->aeropost_scan_status) ? $items->aeropost_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($items->aeropost_scan_status) ? $items->aeropost_scan_status : '-'] : '-', $invoiceNumbers, date('d-m-Y', strtotime($items->date)), !empty($items->from_location) ? $items->from_location : '-', $consignee, $items->total_freight, $items->tracking_no, ($items->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function checkoperationfordatatableserversideaeropost()
    {
        $flag = $_POST['flag'];
        if ($flag == 'getFileData') {
            $aeropostId = $_POST['aeropostId'];
            return json_encode(Aeropost::getAeropostData($aeropostId));
        }
    }

    public function viewaeropostdetailforagent($id)
    {
        $checkPermission = User::checkPermission(['assign_billingparty_cashcredit_aeropost'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = Aeropost::find($id);
        //Aeropost::where('id',$id)->update(['display_notification'=>0]);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'aeropost')->orderBy('id', 'desc')->get()->toArray();
        $attachedFiles = DB::table('aeropost_uploaded_files')->where('file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');
        return view('agent-role.aeropost.viewdetail', ['model' => $model, 'billingParty' => $billingParty, 'activityData' => $activityData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes]);
    }

    public function assignbillingparty(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $oldStatus = $model->aeropost_scan_status;
        $oldDate = $model->date;
        $oldBillingParty = $model->billing_party;
        $newBillingParty = $request->billing_party;
        $input['date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;
        $input['shipment_received_date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;
        $input['shipment_status'] = '1';
        $input['shipment_status_changed_by'] = auth()->user()->id;
        if ($input['aeropost_scan_status'] == '6') {
            $input['warehouse_status'] = '3';
            $input['shipment_delivered_date'] = date('Y-m-d');
        }

        $model->update($input);
        $inputNotes['flag_note'] = 'R';
        $inputNotes['aeropost_id'] = $input['id'];
        $inputNotes['notes'] = $input['shipment_notes_for_return'];
        $inputNotes['created_on'] = date('Y-m-d');
        $inputNotes['created_by'] = auth()->user()->id;
        VerificationInspectionNote::create($inputNotes);

        if (!empty($model)) {
            $newStatus = $model->aeropost_scan_status;
            if ($oldStatus != $newStatus) {
                if (empty($oldStatus))
                    $oldStatus = '1';
                $modelActivities = new Activities;
                $modelActivities->type = 'aeropost';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . " )";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'aeropost';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        if ($oldDate != $input['date']) {
            $modelActivities = new Activities;
            $modelActivities->type = 'aeropost';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Date has been updated to ' . date('d-m-Y', strtotime($input['date']));
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        if ($oldBillingParty != $newBillingParty) {
            $oldBillingPartyName = DB::table('clients')->where('id', $oldBillingParty)->first();
            $oldBillingPartyNameA = !empty($oldBillingPartyName) ? $oldBillingPartyName->company_name : 'N/A';
            $newBillingPartyName = DB::table('clients')->where('id', $newBillingParty)->first();
            $newBillingPartyNameA = !empty($newBillingPartyName) ? $newBillingPartyName->company_name : 'N/A';
            $modelActivities = new Activities;
            $modelActivities->type = 'aeropost';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Updated Billing Party From <b>' . $oldBillingPartyNameA . '</b> To <b>' . $newBillingPartyNameA . '</b>';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        return 'true';
    }

    public function printaeropostfile($aeropostId)
    {
        $model = DB::table('aeropost')->where('id', $aeropostId)->first();
        $pdf = PDF::loadView('aeropost.printfile', ['model' => $model]);

        $pdf_file = $model->file_number . '.pdf';
        $pdf_path = 'public/aeropostFilePdf/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }
}
