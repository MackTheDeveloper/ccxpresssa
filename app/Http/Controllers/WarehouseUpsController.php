<?php

namespace App\Http\Controllers;

use App\Ups;
use App\UpsMaster;
use App\Invoices;
use App\DeliveryBoy;
use App\Upspackages;
use App\Expense;
use Illuminate\Http\Request;
use App\User;
use DB;
use Session;
use App\Activities;
use App\DeliveryBoyActivities;
use App\Aeropost;
use App\ccpack;
use App\VerificationInspectionNote;
use Config;
use Excel;
use Illuminate\Support\Facades\Storage;
use Response;
use PDF;
use stdClass;

class WarehouseUpsController extends Controller
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

        /* $getWarehouseOfUser =  DB::table('users')
            ->select('warehouses')
            ->where('id', auth()->user()->id)
            ->first();

        $wh = explode(',', $getWarehouseOfUser->warehouses);

        $dataWarehouseCourier = DB::table('ups_details')
            //->whereIn('warehouse', $wh)
            ->where('deleted', 0)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get(); */

        $warehouses = DB::table('warehouse')->where('deleted', 0)->where('warehouse_for', 'Courier')->pluck('name', 'id')->toArray();

        return view("warehouse-role.ups.index", ['warehouses' => $warehouses]);
    }

    public function viewcourierdetailforwarehouse($id)
    {
        $checkPermission = User::checkPermission(['viewdetails_courier_import'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = Ups::find($id);
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.ups.viewcourierdetailforwarehouse', ['items' => $model, 'deliveryBoys' => $deliveryBoys]);
    }

    public function courierupswarehouseflow($masterId = null, $houseId = null)
    {
        $model = UpsMaster::find($masterId);
        $modelUpsHouse = Ups::find($houseId);
        $HouseAWBData = DB::table('ups_details')->where('id', $houseId)->get();
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.ups.warehouseflow', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId, 'masterId' => $masterId, 'items' => $modelUpsHouse]);
    }

    public function viewcourierdetailforwarehousemaster($id, $flag = null, $houseId = null)
    {
        $model = UpsMaster::find($id);
        $HouseAWBData = DB::table('ups_details')
            ->selectRaw('ups_details.*,c1.company_name as consigneeName,c2.company_name as shipperName,c3.company_name as billingParty')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party')
            ->where('ups_details.master_ups_id', $id)->get();
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.ups.viewcourierdetailforwarehousemaster', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId]);
    }

    public function assignstatusbywarehousecouriermaster(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $oldStatus = $model->ups_scan_status;
        $model->update($input);

        if (!empty($model) && !empty($model->ups_scan_status)) {
            $newStatus = $model->ups_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'ups';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                if (!empty($oldStatus))
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                else
                    $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }
        return 'true';
    }

    public function assignstatusbywarehousecourier(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $oldStatus = $model->ups_scan_status;
        $model->update($input);

        if (!empty($input['shipment_notes_for_return'])) {
            $inputNotes['flag_note'] = 'R';
            $inputNotes['ups_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes_for_return'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        if (!empty($model) && !empty($model->ups_scan_status)) {
            $newStatus = $model->ups_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'ups';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                if (!empty($oldStatus))
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                else
                    $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
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
        Session::flash('flash_message', 'Status has been updated successfully');
        if ($input['flag'] == 'fromwarehouseflow')
            return redirect('warehouseups/courierupswarehouseflow/' . $input['masterId'] . '/' . $input['id']);
        else
            return redirect('warehouseups/viewcourierdetailforwarehouse/' . $input['id']);
        //return 'true';
    }

    public function import()
    {
        $checkPermission = User::checkPermission(['upload_courier_import'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $model = new Ups;

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->pluck('name', 'id')->toArray();

        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');

        return view('warehouse-role.ups.import', ['model' => $model, 'warehouses' => $warehouses, 'deliveryBoys' => $deliveryBoys]);
    }

    public function importdatawarehouse(Request $request)
    {
        /*if(empty($_FILES['import_file_warehouse_scan']['name']))
            {
                Session::flash('flash_message_error', 'Please choose file');
                return redirect('warehouseups/importupswarehouse');
            }else{
                if($request->hasFile('import_file_warehouse_scan')){
                    $handle = fopen($_FILES['import_file_warehouse_scan']['tmp_name'], "r");
                    $linecount = 0;
                        while(!feof($handle)){
                          $line = fgets($handle);
                          if($line != "")
                          $linecount++;
                        }

                    
                    $handle = fopen($_FILES['import_file_warehouse_scan']['tmp_name'], "r");
                    while (($line = fgets($handle)) !== false) {
                        $Awb = substr($line,14,18);
                        $dataUps = DB::table('ups_details')->where('awb_number', $Awb)->first();
                        if(!empty($dataUps))
                            $oldStatus = $dataUps->ups_scan_status;
                        else
                            $oldStatus = "-";
                        DB::table('ups_details')
                                ->where('awb_number', $Awb)
                                ->update(['ups_scan_status' => '4']);    

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
                return redirect('warehouseups');
            }*/
        $storage = $request->get('storage');
        if ($request->get('s3file')) {
            $file = $request->get('s3file');
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

                        if ($line != "")
                            $linecount++;
                    }


                    if ($storage == 1) {
                        $handle = fopen($_FILES['import_file_warehouse_scan']['tmp_name'], "r");
                    } else {
                        $handle = fopen($inputfile, "r");
                    }

                    while (($line = fgets($handle)) !== false) {

                        $Awb = substr($line, 14, 18);
                        $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $Awb)->first();
                        if (!empty($dataUps))
                            $oldStatus = $dataUps->ups_scan_status;
                        else
                            $oldStatus = "";
                        DB::table('ups_details')
                            ->where('awb_number', $Awb)->where('courier_operation_type', 1)
                            ->update(['ups_scan_status' => '4', 'warehouse' => $_POST['warehouse'], 'inprogress_scan_status' => 3, 'delivery_boy' => $_POST['delivery_boy'], 'delivery_boy_assigned_on' => date('Y-m-d'), 'delivery_boy_assigned_by' => auth()->user()->id]);

                        $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $Awb)->first();
                        if (!empty($dataUps)) {
                            $newStatus = $dataUps->ups_scan_status;
                            if ($oldStatus != $newStatus) {
                                /* if (empty($oldStatus))
                                    $oldStatus = '1'; */
                                $modelActivities = new Activities;
                                $modelActivities->type = 'ups';
                                $modelActivities->related_id = $dataUps->id;
                                $modelActivities->user_id   = auth()->user()->id;
                                if (!empty($oldStatus))
                                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                                else
                                    $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
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
                    return redirect(url('warehouseups/importupswarehouse'));
                }
                $this->importDataAgentSub($theArray);
                // $header  = Excel::load($inputfile)->get()->first()->keys()->toArray();
                // if (count($header) != 1 || $header[0] != 'Tracking') {
                //     Session::flash('flash_message_error', 'Wrong file format !');
                //     return redirect(url('warehouseups/importupswarehouse'));
                // }
                // Excel::load($inputfile, function ($reader) {
                //     foreach ($reader->toArray() as $key => $row) {
                //         $awbNumber = $row['Tracking'];

                //         $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
                //         if (!empty($dataUps))
                //             $oldStatus = $dataUps->ups_scan_status;
                //         else
                //             $oldStatus = "";
                //         DB::table('ups_details')
                //             ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                //             ->update(['ups_scan_status' => '4', 'warehouse' => $_POST['warehouse'], 'inprogress_scan_status' => 3, 'delivery_boy' => $_POST['delivery_boy'], 'delivery_boy_assigned_on' => date('Y-m-d'), 'delivery_boy_assigned_by' => auth()->user()->id]);
                //         $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
                //         if (!empty($dataUps)) {
                //             $newStatus = $dataUps->ups_scan_status;
                //             if ($oldStatus != $newStatus) {
                //                 /* if (empty($oldStatus))
                //                     $oldStatus = '1'; */
                //                 $modelActivities = new Activities;
                //                 $modelActivities->type = 'ups';
                //                 $modelActivities->related_id = $dataUps->id;
                //                 $modelActivities->user_id   = auth()->user()->id;
                //                 if (!empty($oldStatus))
                //                     $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                //                 else
                //                     $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
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
                return redirect('warehouseups');
            } else {
            }
        }
    }

    public function importDataWarehouseSub($dData)
    {
        unset($dData[0]);
        $dData = array_values($dData);
        // foreach ($dData as $key => $row) {
        //     $awbNumber = $row[0];
        //     // $awbNumber = $row['Tracking'];

        //     $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
        //     if (!empty($dataUps))
        //         $oldStatus = $dataUps->ups_scan_status;
        //     else
        //         $oldStatus = "1";
        //     DB::table('ups_details')
        //         ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
        //         ->update(['ups_scan_status' => '6', 'inprogress_scan_status' => 4]);
        //     $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
        //     if (!empty($dataUps)) {
        //         $newStatus = $dataUps->ups_scan_status;
        //         if ($oldStatus != $newStatus) {
        //             if (empty($oldStatus))
        //                 $oldStatus = '1';
        //             $modelActivities = new Activities;
        //             $modelActivities->type = 'ups';
        //             $modelActivities->related_id = $dataUps->id;
        //             $modelActivities->user_id   = auth()->user()->id;
        //             $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
        //             $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        //             $modelActivities->save();
        //         }
        //     }
        // }
        foreach ($dData as $key => $row) {
            $awbNumber = $row['Tracking'];

            $dataUps = DB::table('ups_details')->where('courier_operation_type', '1')->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps))
                $oldStatus = $dataUps->ups_scan_status;
            else
                $oldStatus = "";
            DB::table('ups_details')
                ->where('awb_number', $awbNumber)->where('courier_operation_type', 1)
                ->update(['ups_scan_status' => '4', 'warehouse' => $_POST['warehouse'], 'inprogress_scan_status' => 3, 'delivery_boy' => $_POST['delivery_boy'], 'delivery_boy_assigned_on' => date('Y-m-d'), 'delivery_boy_assigned_by' => auth()->user()->id]);
            $dataUps = DB::table('ups_details')->where('courier_operation_type', 1)->where('awb_number', $awbNumber)->first();
            if (!empty($dataUps)) {
                $newStatus = $dataUps->ups_scan_status;
                if ($oldStatus != $newStatus) {
                    /* if (empty($oldStatus))
                                    $oldStatus = '1'; */
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $dataUps->id;
                    $modelActivities->user_id   = auth()->user()->id;
                    if (!empty($oldStatus))
                        $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    else
                        $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
    }

    public function upsstep1shipmentstatus(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));
        $input['shipment_incomplete_date'] = date('Y-m-d', strtotime($input['shipment_incomplete_date']));
        $input['shipment_shortshipped_date'] = date('Y-m-d', strtotime($input['shipment_shortshipped_date']));
        $input['shipment_status_changed_by'] = auth()->user()->id;
        $model->update($input);

        if (!empty($input['shipment_notes'])) {
            $inputNotes['flag_note'] = 'V';
            $inputNotes['ups_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['shipment_status_changed_by']);

        $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'V')->where('ups_id', $input['id'])->orderBy('id', 'desc')->get();

        $ajaxData['status'] = $input['shipment_status'] == '1' ? 'Received' : Config::get('app.shipmentStatus')[$input['shipment_status']];
        $ajaxData['on'] = $input['shipment_status'] == '1' ? date('d-m-Y', strtotime($model->shipment_received_date)) : ($input['shipment_status'] == '2' ? date('d-m-Y', strtotime($model->shipment_incomplete_date)) : date('d-m-Y', strtotime($model->shipment_shortshipped_date)));
        $ajaxData['changedBy'] = $dataUser->name;
        if (count($dataComments) > 0)
            $ajaxData['comments'] = $dataComments;
        else
            $ajaxData['comments'] = '';


        return view('warehouse-role.ups.step1shipmentstatusajax', ['ajaxData' => $ajaxData]);
    }

    public function upsstep2custominspection(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $input['inspection_flag'] = $input['inspection_flag'] == 'true' ? '1' : '0';
        $input['inspection_date'] = date('Y-m-d', strtotime($input['inspection_date']));
        $input['inspection_by'] = auth()->user()->id;
        $model->update($input);

        $modelActivities = new Activities;
        $modelActivities->type = 'ups';
        $modelActivities->related_id = $input['id'];
        $modelActivities->user_id   = auth()->user()->id;
        if ($input['inspection_flag'] == '1')
            $modelActivities->description = "Custom Inspection | <strong>Done</strong> | On " . date('d-m-Y', strtotime($input['inspection_date'])) . " | Custom File Number : " . $model->custom_file_number;
        else
            $modelActivities->description = "Custom Inspection | <strong>Pending</strong>";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        if (!empty($input['shipment_notes'])) {
            $inputNotes['flag_note'] = 'I';
            $inputNotes['ups_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['inspection_by']);

        $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'I')->where('ups_id', $input['id'])->orderBy('id', 'desc')->get();

        $ajaxData['status'] = Config::get('app.inspectionFileWarehouse')[$input['inspection_flag']];
        $ajaxData['on'] = $input['inspection_flag'] == '1' ? date('d-m-Y', strtotime($model->inspection_date)) : '-';
        $ajaxData['changedBy'] = $dataUser->name;
        if (count($dataComments) > 0)
            $ajaxData['comments'] = $dataComments;
        else
            $ajaxData['comments'] = '';


        return view('warehouse-role.ups.step2custominspection', ['ajaxData' => $ajaxData]);
    }

    public function upsstep3movetononboundedwh(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $input['move_to_nonbounded_wh'] = $input['move_to_nonbounded_wh'] == 'true' ? '1' : '0';
        $input['move_to_nonbounded_wh_on'] = date('Y-m-d');
        $input['move_to_nonbounded_wh_by'] = auth()->user()->id;
        $input['nonbounded_wh_confirmation'] = '0';
        if ($input['move_to_nonbounded_wh'] == '1') {

            $getNonBoundedWH = DB::table('warehouse')
                ->select('id')
                ->where('name', Config::get('app.nonBoundedWHName'))
                ->first();
            if (!empty($getNonBoundedWH)) {
                $nonBoundedId = $getNonBoundedWH->id;
                $input['warehouse'] = $nonBoundedId;
            }

            $input['display_notification_nonbounded_wh'] = 1;
            $input['display_notification_nonbounded_wh_datetime'] = date('Y-m-d H:i:s');
        }
        $model->update($input);

        $modelActivities = new Activities;
        $modelActivities->type = 'ups';
        $modelActivities->related_id = $input['id'];
        $modelActivities->user_id   = auth()->user()->id;
        if ($input['move_to_nonbounded_wh'] == '1')
            $modelActivities->description = "Shipment move to Non Bounded Warehouse | <strong>Yes</strong> | On " . date('d-m-Y', strtotime($input['move_to_nonbounded_wh_on']));
        else
            $modelActivities->description = "Shipment move to Non Bounded Warehouse | <strong>No</strong>";
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['move_to_nonbounded_wh_by']);
        $dataUserConfirmation = $userModel->getUserName($model->nonbounded_wh_confirmation_by);

        $ajaxData['status'] = !empty($input['move_to_nonbounded_wh']) ? 'Assigned' : 'Not Assigned';
        $ajaxData['on'] = $input['move_to_nonbounded_wh'] == '1' ? date('d-m-Y', strtotime($model->move_to_nonbounded_wh_on)) : '-';
        $ajaxData['changedBy'] = $dataUser->name;

        $ajaxDataWHConfirmation['status'] = Config::get('app.NonBoundedWarehouseConfirmation')[$model->nonbounded_wh_confirmation];
        $ajaxDataWHConfirmation['on'] = $model->nonbounded_wh_confirmation == '1' ? date('d-m-Y', strtotime($model->nonbounded_wh_confirmation_on)) : '-';
        $ajaxDataWHConfirmation['changedBy'] = !empty($dataUserConfirmation) ? $dataUserConfirmation->name : '-';

        return view('warehouse-role.ups.step3movetononboundedwh', ['ajaxData' => $ajaxData, 'ajaxDataWHConfirmation' => $ajaxDataWHConfirmation]);
    }

    public function upsstep4invoiceandpayment(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $model->update($input);
    }

    public function upsstep5assigndeliveryboy(Request $request)
    {
        $input = $request->all();
        $model = Ups::find($input['id']);
        $oldStatus = $model->ups_scan_status;
        if ($input['reason'] == '0') {
            $input['delivery_boy_assigned_on'] = date('Y-m-d');
            $input['delivery_boy_assigned_by'] = auth()->user()->id;
            $model->update($input);

            $userModel = new User;
            $dataUser = $userModel->getUserName($input['delivery_boy_assigned_by']);

            $deliveryBoyModel = new DeliveryBoy;
            $dataDeliveryBoy = $deliveryBoyModel->getDeliveryBodData($input['delivery_boy']);

            $modelActivities = new Activities;
            $modelActivities->type = 'ups';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = "Delivery boy assigned - <strong>" . $dataDeliveryBoy->name . "</strong>";
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            /* $modelActivitiesDeliveryBoy = new DeliveryBoyActivities;
            $modelActivitiesDeliveryBoy->delivery_boy_id = $input['delivery_boy'];
            $modelActivitiesDeliveryBoy->ups_id = $input['id'];
            $modelActivitiesDeliveryBoy->file_number   = $model->file_number;
            $modelActivitiesDeliveryBoy->date_time   = gmdate("Y-m-d H:i:s");
            $modelActivitiesDeliveryBoy->description = "Delivery boy assigned";
            $modelActivitiesDeliveryBoy->assigned_by = auth()->user()->id;
            $modelActivitiesDeliveryBoy->save(); */

            $ajaxData['status'] = !empty($dataDeliveryBoy) ? $dataDeliveryBoy->name : '-';
            $ajaxData['on'] = date('d-m-Y', strtotime($input['delivery_boy_assigned_on']));
            if (!empty($dataUser))
                $ajaxData['changedBy'] = $dataUser->name;
            else
                $ajaxData['changedBy'] = '-';

            return view('warehouse-role.ups.upsstep5assigndeliveryboy', ['ajaxData' => $ajaxData]);
        } else {
            if ($input['ups_scan_status'] == '6') {
                $input['warehouse_status'] = '3';
                $input['shipment_delivered_date'] = date('Y-m-d');
            }
            $model->update($input);

            if (!empty($input['shipment_notes'])) {
                $inputNotes['flag_note'] = 'R';
                $inputNotes['ups_id'] = $input['id'];
                $inputNotes['notes'] = $input['shipment_notes'];
                $inputNotes['created_on'] = date('Y-m-d');
                $inputNotes['created_by'] = auth()->user()->id;
                VerificationInspectionNote::create($inputNotes);
            }
            if (!empty($model) && !empty($model->ups_scan_status)) {
                $newStatus = $model->ups_scan_status;
                if ($oldStatus != $newStatus) {
                    if (empty($oldStatus))
                        $oldStatus = '1';
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $model->id;
                    $modelActivities->user_id   = auth()->user()->id;
                    if (!empty($oldStatus))
                        $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes'] . ")";
                    else
                        $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();

                    /* $modelActivitiesDeliveryBoy = new DeliveryBoyActivities;
                    $modelActivitiesDeliveryBoy->delivery_boy_id = $input['delivery_boy'];
                    $modelActivitiesDeliveryBoy->ups_id = $input['id'];
                    $modelActivitiesDeliveryBoy->file_number   = $model->file_number;
                    $modelActivitiesDeliveryBoy->date_time   = gmdate("Y-m-d H:i:s");
                    $modelActivitiesDeliveryBoy->description = $modelActivities->description;
                    $modelActivitiesDeliveryBoy->assigned_by = auth()->user()->id;
                    $modelActivitiesDeliveryBoy->save(); */
                } else {
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $model->id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = "File Comment : " . $input['shipment_notes'];
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();

                    /* $modelActivitiesDeliveryBoy = new DeliveryBoyActivities;
                    $modelActivitiesDeliveryBoy->delivery_boy_id = $input['delivery_boy'];
                    $modelActivitiesDeliveryBoy->ups_id = $input['id'];
                    $modelActivitiesDeliveryBoy->file_number   = $model->file_number;
                    $modelActivitiesDeliveryBoy->date_time   = gmdate("Y-m-d H:i:s");
                    $modelActivitiesDeliveryBoy->description = $modelActivities->description;
                    $modelActivitiesDeliveryBoy->assigned_by = auth()->user()->id;
                    $modelActivitiesDeliveryBoy->save(); */
                }
            }

            $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'R')->where('ups_id', $input['id'])->orderBy('id', 'desc')->get();
            if (count($dataComments) > 0)
                $ajaxData['comments'] = $dataComments;
            else
                $ajaxData['comments'] = '';

            return view('warehouse-role.ups.upsstep5assigndeliveryboyreason', ['ajaxData' => $ajaxData]);
        }
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCourierAddInvoice = User::checkPermission(['add_courier_invoices'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $warehouse = $req['warehouse'];
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
        $orderby = ['', 'ups_details.id', 'ups_details.id', '', 'file_number', 'master_file_number', 'c3.company_name', 'ups_scan_status', '', 'delivery_boy.name', '', 'c2.company_name', 'c1.company_name', 'shipment_number', '', 'awb_number', 'package_type', 'origin', 'weight', ''];

        $total = Ups::selectRaw('count(*) as total');
        //->where('ups_scan_status','4')
        //->where('deleted', '0');
        if (!empty($fileStatus)) {
            $total = $total->where('ups_scan_status', $fileStatus);
        }
        if (!empty($warehouse)) {
            $total = $total->where('warehouse', $warehouse);
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
            ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party')
            ->leftJoin('delivery_boy', 'delivery_boy.id', '=', 'ups_details.delivery_boy');
        //->where('ups_details.deleted', '0');
        //->where('ups_scan_status', '4');
        if (!empty($fileStatus)) {
            $query = $query->where('ups_scan_status', $fileStatus);
        }
        if (!empty($warehouse)) {
            $query = $query->where('warehouse', $warehouse);
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
            ->leftJoin('clients as c3', 'c3.id', '=', 'ups_details.billing_party')
            ->leftJoin('delivery_boy', 'delivery_boy.id', '=', 'ups_details.delivery_boy');
        //->where('ups_details.deleted', '0');
        //->where('ups_scan_status', '4');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('ups_scan_status', $fileStatus);
        }
        if (!empty($warehouse)) {
            $filteredq = $filteredq->where('warehouse', $warehouse);
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
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('delivery_boy.name', 'like', '%' . $search . '%')
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
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('master_file_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c3.company_name', 'like', '%' . $search . '%')
                    ->orWhere('delivery_boy.name', 'like', '%' . $search . '%')
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
            $deliveryBoyData = app('App\DeliveryBoy')->getDeliveryBodData($couriers->delivery_boy);
            //$date = $couriers->courier_operation_type == 1 ? (!empty($couriers->arrival_date) ? date('d-m-Y', strtotime($couriers->arrival_date)) : '-') : (!empty($couriers->tdate) ? date('d-m-Y', strtotime($couriers->tdate)) : '-');
            $date = !empty($couriers->arrival_date) ? date('d-m-Y', strtotime($couriers->arrival_date)) : '-';
            $billingTerm = Ups::getBillingTerm($couriers->id);
            $fileStatus =  isset(Config::get('app.ups_new_scan_status')[!empty($couriers->ups_scan_status) ? $couriers->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($couriers->ups_scan_status) ? $couriers->ups_scan_status : '-'] : '-';
            $invoiceNumbers = Expense::getUpsInvoicesOfFile($couriers->id);
            $warehouseName = app('App\Ups')->getWarehouseData($couriers->id, 'ups_details');

            if ($couriers->package_type == 'LTR')
                $packageType = 'Letter';
            else if ($couriers->package_type == 'DOC')
                $packageType = 'Document';
            else
                $packageType = 'Package';

            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printupsfile", [$couriers->id, $couriers->courier_operation_type]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($couriers->deleted == '0') {
                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                if ($permissionCourierAddInvoice) {
                    $action .= '<li><a href="' . route('createhousefileinvoice', 'ups') . '">Add Invoice</a></li>';
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

            $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $couriers->id . '" value="' . $couriers->id . '" />';

            $data[] = [$couriers->file_close == 1 ? '' : $checkBoxes, $couriers->id, '', $couriers->master_ups_id, $couriers->file_number, !empty($couriers->master_file_number) ? $couriers->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $fileStatus, $warehouseName, !empty($deliveryBoyData) ? $deliveryBoyData->name : '-', $invoiceNumbers, $shipper, $consignee,  !empty($couriers->shipment_number) ? $couriers->shipment_number : '-', $date, $couriers->awb_number, $packageType, $couriers->origin, !empty($couriers->weight) ? $couriers->weight . ' ' . $couriers->unit : '-', $billingTerm, ($couriers->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function assigndeliveryboy($module = null)
    {
        if ($module == 'ups')
            $model = new Ups;
        if ($module == 'aeropost')
            $model = new Aeropost();
        if ($module == 'ccpack')
            $model = new ccpack();

        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('common.assign-delivery-boy', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'module' => $module]);
    }

    public function assigndeliveryboysubmit(Request $request)
    {
        $input = $request->all();
        //$dataInvoice = Invoices::where('id',1166)->first()->toArray();
        $flagModule = $input['module'];
        $selectedIds = explode(',', $input['selectedIds']);
        if ($flagModule == 'ups') {
            $tblName = 'ups_details';
            $col1Name = 'ups_scan_status';
        } else if ($flagModule == 'aeropost') {
            $tblName = 'aeropost';
            $col1Name = 'aeropost_scan_status';
        } else if ($flagModule == 'ccpack') {
            $tblName = 'ccpack';
            $col1Name = 'ccpack_scan_status';
        }

        DB::table($tblName)
            ->whereIn('id', $selectedIds)
            ->update(['delivery_boy' => $input['delivery_boy'], 'delivery_boy_assigned_on' => date('Y-m-d'), 'delivery_boy_assigned_by' => auth()->user()->id, $col1Name => '8']);

        foreach ($selectedIds as $k => $v) {
            $deliveryBoyData = DB::table('delivery_boy')
                ->where('id', $input['delivery_boy'])
                ->first();

            $modelActivities = new Activities;
            $modelActivities->type = $flagModule;
            $modelActivities->related_id = $v;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = "Delivery boy assigned - <strong>" . $deliveryBoyData->name . "</strong>";
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        $pdf = PDF::loadView('common.deliveryboyinvoices', ['selectedIds' => $selectedIds, 'flagModule' => $flagModule]);
        $pdf_file = 'deliveryBoyInvoices_' . time() . '.pdf';
        $pdf_path = 'public/deliveryBoyInvoices/' . $pdf_file;
        $pdf->save($pdf_path);
        return url('/') . '/' . $pdf_path;
    }
}
