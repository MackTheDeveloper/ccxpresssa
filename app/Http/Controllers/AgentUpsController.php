<?php

namespace App\Http\Controllers;

use App\Ups;
use App\Upspackages;
use Illuminate\Http\Request;
use App\User;
use App\Expense;
use DB;
use Session;
use App\Activities;
use Config;
use Excel;
use App\VerificationInspectionNote;
use Illuminate\Support\Facades\Storage;
use Response;
use stdClass;

class AgentUpsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_courier_import'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        /*$upsData = DB::table('ups_details')->where('agent_id',auth()->user()->id)->where('deleted',0)->where('status',1)->get();*/

        //$upsData = DB::table('ups_details')->where('deleted', 0)->where('status', 1)->get();
        //return view("agent-role.ups.index", ['upsData' => $upsData]);
        return view("agent-role.ups.index");
    }

    public function viewcourierdetailforagent($id)
    {
        $checkPermission = User::checkPermission(['assign_billingparty_cashcredit_courier_import'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = Ups::find($id);
        Ups::where('id', $id)->update(['display_notification' => 0]);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'ups')->orderBy('id', 'desc')->get()->toArray();
        $attachedFiles = DB::table('ups_uploaded_files')->where('file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');
        return view('agent-role.ups.viewcourierdetailforagent', ['model' => $model, 'billingParty' => $billingParty, 'activityData' => $activityData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes]);
    }

    public function assignonconsolidationbyagentcourier(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $oldStatus = $model->ups_scan_status;
        $oldArrivalDate = $model->arrival_date;
        $oldBillingParty = $model->billing_party;
        $newBillingParty = $request->billing_party;
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['shipment_received_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['shipment_status'] = '1';
        $input['shipment_status_changed_by'] = auth()->user()->id;
        if ($input['ups_scan_status'] == '6') {
            $input['warehouse_status'] = '3';
            $input['shipment_delivered_date'] = date('Y-m-d');
        }
        $model->update($input);

        $inputNotes['flag_note'] = 'R';
        $inputNotes['ups_id'] = $input['id'];
        $inputNotes['notes'] = $input['shipment_notes_for_return'];
        $inputNotes['created_on'] = date('Y-m-d');
        $inputNotes['created_by'] = auth()->user()->id;
        VerificationInspectionNote::create($inputNotes);

        if (!empty($model)) {
            $newStatus = $model->ups_scan_status;
            if ($oldStatus != $newStatus) {
                if (empty($oldStatus))
                    $oldStatus = '1';
                $modelActivities = new Activities;
                $modelActivities->type = 'ups';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . " )";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'ups';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        if ($oldArrivalDate != $input['arrival_date']) {
            $modelActivities = new Activities;
            $modelActivities->type = 'ups';
            $modelActivities->related_id = $model->id;
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Arrival date has been updated to ' . date('d-m-Y', strtotime($input['arrival_date']));
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        if ($oldBillingParty != $newBillingParty) {
            $oldBillingPartyName = DB::table('clients')->where('id', $oldBillingParty)->first();
            $oldBillingPartyNameA = !empty($oldBillingPartyName) ? $oldBillingPartyName->company_name : 'N/A';
            $newBillingPartyName = DB::table('clients')->where('id', $newBillingParty)->first();
            $newBillingPartyNameA = !empty($newBillingPartyName) ? $newBillingPartyName->company_name : 'N/A';
            $modelActivities = new Activities;
            $modelActivities->type = 'ups';
            $modelActivities->related_id = $model->id;
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Updated Billing Party From <b>' . $oldBillingPartyNameA . '</b> To <b>' . $newBillingPartyNameA . '</b>';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        return 'true';
    }

    public function import()
    {
        $checkPermission = User::checkPermission(['upload_courier_import'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $model = new Ups;
        return view('agent-role.ups.import', ['model' => $model]);
    }

    public function importdataagent(Request $request)
    {

        /*if(empty($_FILES['import_file_destination_scan']['name']))
            {
                Session::flash('flash_message_error', 'Please choose file');
                return redirect('agentups/importupsagent');
            }else{
                if($request->hasFile('import_file_destination_scan')){
                    $handle = fopen($_FILES['import_file_destination_scan']['tmp_name'], "r");
                    $linecount = 0;
                        while(!feof($handle)){
                          $line = fgets($handle);
                          if($line != "")
                          $linecount++;
                        }

                    
                    $handle = fopen($_FILES['import_file_destination_scan']['tmp_name'], "r");
                    while (($line = fgets($handle)) !== false) {
                        $Awb = substr($line,33,18);
                        $dataUps = DB::table('ups_details')->where('awb_number', $Awb)->first();
                        if(!empty($dataUps))
                            $oldStatus = $dataUps->ups_scan_status;
                        else
                            $oldStatus = "-";
                        DB::table('ups_details')
                                ->where('awb_number', $Awb)
                                ->update(['ups_scan_status' => '3']);    

                        $dataUps = DB::table('ups_details')->where('awb_number', $Awb)->first();
                        if(!empty($dataUps))
                        {
                            $newStatus = $dataUps->ups_scan_status;
                            if($oldStatus != $newStatus)
                            {
                            $modelActivities = new Activities;
                            $modelActivities->type = 'ups';
                            $modelActivities->related_id = $dataUps->id;
                            $modelActivities->user_id   = auth()->user()->id;
                            $modelActivities->description = "Status has been changed from - <strong>".Config::get('app.upsStatus')[$oldStatus]."</strong> To <strong>".Config::get('app.upsStatus')[$newStatus]."</strong>";
                            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                            $modelActivities->save();                                
                            }
                        }                                
                    }
                }
                Session::flash('flash_message', 'Record has been created successfully');
                return redirect('agentups');
            }*/
        $storage = $request->get('storage');
        if ($request->get('s3file')) {
            $file = $request->get('s3file');
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
            }
            // $header  = Excel::load($inputfile)->get()->first()->keys()->toArray();
            // if (count($header) != 1 || $header[0] != 'Tracking') {
            //     Session::flash('flash_message_error', 'Wrong file format !');
            //     return redirect(url('agentups/importupsagent'));
            // }
            // Excel::load($inputfile, function ($reader) {
            //     foreach ($reader->toArray() as $key => $row) {
            //         $awbNumber = $row['Tracking'];

            //         $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            //         if (!empty($dataUps))
            //             $oldStatus = $dataUps->ups_scan_status;
            //         else
            //             $oldStatus = "1";
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
            //                 $modelActivities->user_id   = auth()->user()->id;
            //                 $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
            //                 $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            //                 $modelActivities->save();
            //             }
            //         }
            //     }
            // });

            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            if (count($theArray[0]) != 1 || $theArray[0][0] != 'Tracking') {
                Session::flash('flash_message_error', 'Wrong file format !');
                return redirect(url('agentups/importupsagent'));
            }
            $this->importDataAgentSub($theArray);

            Session::flash('flash_message', 'Status has been changed to delivery scan.');
            if (isset($success)) {
                $deleted = Storage::disk('local')->delete('/temp/' . $tmpname);
            }
            return redirect('agentups');
        }
    }

    public function importDataAgentSub($dData){
        unset($dData[0]);
        $dData = array_values($dData);
        foreach ($dData as $key => $row) {
            $awbNumber = $row[0];
            // $awbNumber = $row['Tracking'];

            $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps))
                $oldStatus = $dataUps->ups_scan_status;
            else
                $oldStatus = "1";
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
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCourierImportEdit = User::checkPermission(['update_courier_import'], '', auth()->user()->id);
        $permissionCourierImportDelete = User::checkPermission(['delete_courier_import'], '', auth()->user()->id);
        $permissionCourierAddExpense = User::checkPermission(['add_courier_expenses'], '', auth()->user()->id);
        $permissionCourierAddInvoice = User::checkPermission(['add_courier_invoices'], '', auth()->user()->id);

        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
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
        $orderby = ['ups_details.id', 'ups_details.id', 'file_number', 'master_file_number',  'c3.company_name', 'ups_scan_status', 'c2.company_name', 'c1.company_name', 'shipment_number', '', '', 'awb_number', 'package_type', 'origin', 'weight', '', 'commission_amount_approve'];

        $total = Ups::selectRaw('count(*) as total');
        //->where('deleted', '0');
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
            if ($couriers->deleted == '0') {
                $edit = route('editups', [$couriers->id, $couriers->courier_operation_type]);
                if ($permissionCourierImportEdit) {
                    $action .= '<a href="' . $edit . '" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCourierAddExpense) {
                    $action .= '<li><a href="' . route('createagentupsexpenses', $couriers->id) . '">Add File Expense</a></li>';
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

            $data[] = [$couriers->id, '', $couriers->file_number, !empty($couriers->master_file_number) ? $couriers->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($couriers->ups_scan_status) ? $couriers->ups_scan_status : '1']) ? Config::get('app.ups_new_scan_status')[!empty($couriers->ups_scan_status) ? $couriers->ups_scan_status : '1'] : '-', $shipper, $consignee, !empty($couriers->shipment_number) ? $couriers->shipment_number : '-', $invoiceNumbers, $date, $couriers->awb_number, $packageType, $couriers->origin, !empty($couriers->weight) ? $couriers->weight . ' ' . $couriers->unit : '-', $billingTerm, $couriers->commission_amount_approve == 'Y' ? 'Yes' : 'No', ($couriers->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }
}
