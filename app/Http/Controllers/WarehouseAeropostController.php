<?php

namespace App\Http\Controllers;

use App\Aeropost;
use App\AeropostMaster;
use App\Expense;
use App\AeropostFreightCommission;
use App\AeropostInvoiceItemDetails;
use App\AeropostInvoices;
use App\Clients;
use App\Activities;
use App\User;
use Config;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;
use Session;
use Response;
use App\VerificationInspectionNote;
use App\DeliveryBoy;

class WarehouseAeropostController extends Controller
{
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_aeropost'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $items = DB::table('aeropost')->where('deleted', '0')->orderBy('id', 'desc')->get();
        $warehouses = DB::table('warehouse')->where('deleted', 0)->where('warehouse_for', 'Courier')->pluck('name', 'id')->toArray();
        return view("warehouse-role.aeropost.index", ['items' => $items, 'warehouses' => $warehouses]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionAeropostAddInvoice = User::checkPermission(['add_aeropost_invoices'], '', auth()->user()->id);
        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $warehouse = $req['warehouse'];
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['aeropost.id', 'aeropost.id', '', 'aeropost.file_number', 'master_file_number',  'c3.company_name', 'aeropost_scan_status', '', 'delivery_boy.name', '', 'aeropost.date', 'from_location', 'c1.company_name', 'freight', 'aeropost.tracking_no'];



        $total = Aeropost::selectRaw('count(*) as total');
        //->where('deleted', '0');
        if (!empty($fileStatus)) {
            $total = $total->where('aeropost_scan_status', $fileStatus);
        }
        if (!empty($warehouse)) {
            $total = $total->where('warehouse', $warehouse);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('aeropost.date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('aeropost')
            ->selectRaw('aeropost.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
            ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party')
            ->leftJoin('delivery_boy', 'delivery_boy.id', '=', 'aeropost.delivery_boy');
        //->where('aeropost.deleted', '0');
        if (!empty($fileStatus)) {
            $query = $query->where('aeropost_scan_status', $fileStatus);
        }
        if (!empty($warehouse)) {
            $query = $query->where('warehouse', $warehouse);
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('aeropost.date', array($fromDate, $toDate));
        }

        $filteredq = DB::table('aeropost')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
            ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party')
            ->leftJoin('delivery_boy', 'delivery_boy.id', '=', 'aeropost.delivery_boy');
        //->where('aeropost.deleted', '0');
        if (!empty($fileStatus)) {
            $filteredq = $filteredq->where('aeropost_scan_status', $fileStatus);
        }
        if (!empty($warehouse)) {
            $filteredq = $filteredq->where('warehouse', $warehouse);
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
                    ->orWhere('delivery_boy.name', 'like', '%' . $search . '%')
                    ->orWhere('freight', 'like', '%' . $search . '%')
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
                    ->orWhere('delivery_boy.name', 'like', '%' . $search . '%')
                    ->orWhere('freight', 'like', '%' . $search . '%')
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
            $fileStatus = isset(Config::get('app.ups_new_scan_status')[!empty($items->aeropost_scan_status) ? $items->aeropost_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($items->aeropost_scan_status) ? $items->aeropost_scan_status : '-'] : '-';
            $invoiceNumbers = Expense::getAeropostInvoicesOfFile($items->id);

            $deliveryBoyData = app('App\DeliveryBoy')->getDeliveryBodData($items->delivery_boy);
            $warehouseName = app('App\Ups')->getWarehouseData($items->id, 'aeropost');
            $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printaeropostfile", [$items->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';
            if ($items->deleted == '0') {
            $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

            if ($permissionAeropostAddInvoice) {
                $action .= '<li><a href="' . route('createaeropostinvoice', $items->id) . '">Add Invoice</a></li>';
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

            $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $items->id . '" value="' . $items->id . '" />';

            $data[] = [$items->file_close == 1 ? '' : $checkBoxes, $items->id, $items->master_aeropost_id, $items->file_number, !empty($items->master_file_number) ? $items->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $fileStatus, $warehouseName, !empty($deliveryBoyData) ? $deliveryBoyData->name : '-', $invoiceNumbers, date('d-m-Y', strtotime($items->date)), !empty($items->from_location) ? $items->from_location : '-', $consignee, $items->freight, $items->tracking_no, ($items->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function viewcourieraeropostdetailforwarehouse($id)
    {
        $checkPermission = User::checkPermission(['viewdetails_courier_aeropost'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = Aeropost::find($id);
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.aeropost.viewcourieraeropostdetailforwarehouse', ['items' => $model, 'deliveryBoys' => $deliveryBoys]);
    }

    public function courieraeropostwarehouseflow($masterId = null, $houseId = null)
    {
        $model = AeropostMaster::find($masterId);
        $modelUpsHouse = Aeropost::find($houseId);
        $HouseAWBData = DB::table('aeropost')->where('id', $houseId)->get();
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.aeropost.warehouseflow', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId, 'masterId' => $masterId, 'items' => $modelUpsHouse]);
    }

    public function viewcourieraeropostdetailforwarehousemaster($id, $flag = null, $houseId = null)
    {
        $model = AeropostMaster::find($id);
        $HouseAWBData = DB::table('aeropost')
            ->selectRaw('aeropost.*,c1.company_name as consigneeName,c3.company_name as billingParty')
            ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
            ->leftJoin('clients as c3', 'c3.id', '=', 'aeropost.billing_party')
            ->where('aeropost.master_aeropost_id', $id)->get();
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.aeropost.viewcourieraeropostdetailforwarehousemaster', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId]);
    }

    /* public function viewcourieraeropostdetailforwarehouse($id, $flag = null, $houseId = null)
    {
        $checkPermission = User::checkPermission(['viewdetails_courier_aeropost'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = AeropostMaster::find($id);
        $HouseAWBData = DB::table('aeropost')->where('master_aeropost_id', $id)->get();
        $deliveryBoys = DB::table('delivery_boy')
            ->select('id', 'name')
            ->where('deleted', 0)->where('status', 1)->get()
            ->pluck('name', 'id');
        return view('warehouse-role.aeropost.viewcourieraeropostdetailforwarehouse', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId]);
    } */

    public function assignstatusbywarehousecourieraeropostmaster(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $oldStatus = $model->aeropost_scan_status;
        $model->update($input);

        if (!empty($model) && !empty($model->aeropost_scan_status)) {
            $newStatus = $model->aeropost_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'aeropost';
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

    public function assignstatusbywarehousecourieraeropost(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $oldStatus = $model->aeropost_scan_status;
        $model->update($input);

        if (!empty($input['shipment_notes_for_return'])) {
            $inputNotes['flag_note'] = 'R';
            $inputNotes['aeropost_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes_for_return'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        if (!empty($model) && !empty($model->aeropost_scan_status)) {
            $newStatus = $model->aeropost_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'aeropost';
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
                $modelActivities->type = 'aeropost';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }

        }
        Session::flash('flash_message', 'Status has been updated successfully');
        if ($input['flag'] == 'fromwarehouseflow')
            return redirect('warehouseaeropost/courieraeropostwarehouseflow/' . $input['masterId'] . '/' . $input['id']);
        else
            return redirect('warehouseaeropost/viewcourieraeropostdetailforwarehouse/' . $input['id']);
        
        //return 'true';
    }

    public function aeropoststep1shipmentstatus(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));
        $input['shipment_incomplete_date'] = date('Y-m-d', strtotime($input['shipment_incomplete_date']));
        $input['shipment_shortshipped_date'] = date('Y-m-d', strtotime($input['shipment_shortshipped_date']));
        $input['shipment_status_changed_by'] = auth()->user()->id;
        $model->update($input);

        if (!empty($input['shipment_notes'])) {
            $inputNotes['flag_note'] = 'V';
            $inputNotes['aeropost_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['shipment_status_changed_by']);

        $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'V')->where('aeropost_id', $input['id'])->orderBy('id', 'desc')->get();

        $ajaxData['status'] = $input['shipment_status'] == '1' ? 'Received' : Config::get('app.shipmentStatus')[$input['shipment_status']];
        $ajaxData['on'] = $input['shipment_status'] == '1' ? date('d-m-Y', strtotime($model->shipment_received_date)) : ($input['shipment_status'] == '2' ? date('d-m-Y', strtotime($model->shipment_incomplete_date)) : date('d-m-Y', strtotime($model->shipment_shortshipped_date)));
        $ajaxData['changedBy'] = $dataUser->name;
        if (count($dataComments) > 0)
            $ajaxData['comments'] = $dataComments;
        else
            $ajaxData['comments'] = '';


        return view('warehouse-role.aeropost.step1shipmentstatusajax', ['ajaxData' => $ajaxData]);
    }

    public function aeropoststep2custominspection(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $input['inspection_flag'] = $input['inspection_flag'] == 'true' ? '1' : '0';
        $input['inspection_date'] = date('Y-m-d', strtotime($input['inspection_date']));
        $input['inspection_by'] = auth()->user()->id;
        $model->update($input);

        $modelActivities = new Activities;
        $modelActivities->type = 'aeropost';
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
            $inputNotes['aeropost_id'] = $input['id'];
            $inputNotes['notes'] = $input['shipment_notes'];
            $inputNotes['created_on'] = date('Y-m-d');
            $inputNotes['created_by'] = auth()->user()->id;
            VerificationInspectionNote::create($inputNotes);
        }

        $userModel = new User;
        $dataUser = $userModel->getUserName($input['inspection_by']);

        $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'I')->where('aeropost_id', $input['id'])->orderBy('id', 'desc')->get();

        $ajaxData['status'] = Config::get('app.inspectionFileWarehouse')[$input['inspection_flag']];
        $ajaxData['on'] = $input['inspection_flag'] == '1' ? date('d-m-Y', strtotime($model->inspection_date)) : '-';
        $ajaxData['changedBy'] = $dataUser->name;
        if (count($dataComments) > 0)
            $ajaxData['comments'] = $dataComments;
        else
            $ajaxData['comments'] = '';


        return view('warehouse-role.aeropost.step2custominspection', ['ajaxData' => $ajaxData]);
    }

    public function aeropoststep3movetononboundedwh(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
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
        $modelActivities->type = 'aeropost';
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

        return view('warehouse-role.aeropost.step3movetononboundedwh', ['ajaxData' => $ajaxData, 'ajaxDataWHConfirmation' => $ajaxDataWHConfirmation]);
    }

    public function aeropoststep4invoiceandpayment(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $model->update($input);
    }

    public function aeropoststep5assigndeliveryboy(Request $request)
    {
        $input = $request->all();
        $model = Aeropost::find($input['id']);
        $oldStatus = $model->aeropost_scan_status;
        if ($input['reason'] == '0') {
            $input['delivery_boy_assigned_on'] = date('Y-m-d');
            $input['delivery_boy_assigned_by'] = auth()->user()->id;
            $model->update($input);

            $userModel = new User;
            $dataUser = $userModel->getUserName($input['delivery_boy_assigned_by']);

            $deliveryBoyModel = new DeliveryBoy;
            $dataDeliveryBoy = $deliveryBoyModel->getDeliveryBodData($input['delivery_boy']);

            $modelActivities = new Activities;
            $modelActivities->type = 'aeropost';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = "Delivery boy assigned - <strong>" . $dataDeliveryBoy->name . "</strong>";
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            $ajaxData['status'] = !empty($dataDeliveryBoy) ? $dataDeliveryBoy->name : '-';
            $ajaxData['on'] = date('d-m-Y', strtotime($input['delivery_boy_assigned_on']));
            if (!empty($dataUser))
                $ajaxData['changedBy'] = $dataUser->name;
            else
                $ajaxData['changedBy'] = '-';

            return view('warehouse-role.aeropost.aeropoststep5assigndeliveryboy', ['ajaxData' => $ajaxData]);
        } else {
            if ($input['aeropost_scan_status'] == '6') {
                $input['warehouse_status'] = '3';
                $input['shipment_delivered_date'] = date('Y-m-d');
            }
            $model->update($input);

            if (!empty($input['shipment_notes'])) {
                $inputNotes['flag_note'] = 'R';
                $inputNotes['aeropost_id'] = $input['id'];
                $inputNotes['notes'] = $input['shipment_notes'];
                $inputNotes['created_on'] = date('Y-m-d');
                $inputNotes['created_by'] = auth()->user()->id;
                VerificationInspectionNote::create($inputNotes);
            }

            if (!empty($model) && !empty($model->aeropost_scan_status)) {
                $newStatus = $model->aeropost_scan_status;
                if ($oldStatus != $newStatus) {
                    /* if (empty($oldStatus))
                        $oldStatus = '1'; */
                    $modelActivities = new Activities;
                    $modelActivities->type = 'aeropost';
                    $modelActivities->related_id = $model->id;
                    $modelActivities->user_id   = auth()->user()->id;
                    if (!empty($oldStatus))
                        $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes'] . ")";
                    else
                        $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>";
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                } else {
                    $modelActivities = new Activities;
                    $modelActivities->type = 'aeropost';
                    $modelActivities->related_id = $model->id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = "File Comment : " . $input['shipment_notes'];
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }

            $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'R')->where('aeropost_id', $input['id'])->orderBy('id', 'desc')->get();
            if (count($dataComments) > 0)
                $ajaxData['comments'] = $dataComments;
            else
                $ajaxData['comments'] = '';

            return view('warehouse-role.aeropost.aeropoststep5assigndeliveryboyreason', ['ajaxData' => $ajaxData]);
        }
    }
}
