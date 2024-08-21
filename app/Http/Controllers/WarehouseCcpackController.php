<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ccpack;
use App\CcpackMaster;
use App\Activities;
use App\Expense;
use DB;
use Session;
use App\Clients;
use Config;
use Illuminate\Support\Facades\Storage;
use Response;
use App\VerificationInspectionNote;
use App\DeliveryBoy;

class WarehouseCcpackController extends Controller
{
  public function index()
  {
    $checkPermission = User::checkPermission(['listing_ccpack'], '', auth()->user()->id);
    if (!$checkPermission)
      return redirect('/home');

    $ccpackData = DB::table('ccpack')->where('deleted', '0')->orderBy('id', 'DESC')->get();
    $warehouses = DB::table('warehouse')->where('deleted', 0)->where('warehouse_for', 'Courier')->pluck('name', 'id')->toArray();
    return view('warehouse-role.ccpack.index', ['ccpackData' => $ccpackData, 'warehouses' => $warehouses]);
  }

  public function listbydatatableserverside(Request $request)
  {
    $permissionCcpackAddInvoice = User::checkPermission(['add_ccpack_invoices'], '', auth()->user()->id);
    $req = $request->all();
    $fileStatus = $req['fileStatus'];
    $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
    $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
    $warehouse = $req['warehouse'];
    $start = $req['start'];
    $length = $req['length'];
    $search = $req['search']['value'];
    $order = $req['order'][0]['dir'];
    $column = $req['order'][0]['column'];
    $orderby = ['ccpack.id', 'ccpack.id', '', 'ccpack.file_number',  'c3.company_name', 'ccpack_scan_status', '', 'delivery_boy.name', '', 'arrival_date', 'awb_number', 'c1.company_name', 'c2.company_name', 'no_of_pcs', 'weight', 'freight'];

    $total = ccpack::selectRaw('count(*) as total');
    //->where('deleted', '0');
    if (!empty($fileStatus)) {
      $total = $total->where('ccpack_scan_status', $fileStatus);
    }
    if (!empty($warehouse)) {
      $total = $total->where('warehouse', $warehouse);
    }
    if (!empty($fromDate) && !empty($toDate)) {
      $total = $total->whereBetween('arrival_date', array($fromDate, $toDate));
    }
    $total = $total->first();
    $totalfiltered = $total->total;

    $query = DB::table('ccpack')
      ->selectRaw('ccpack.*')
      ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
      ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
      ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party')
      ->leftJoin('delivery_boy', 'delivery_boy.id', '=', 'ccpack.delivery_boy');
    //->where('ccpack.deleted', '0');
    if (!empty($fileStatus)) {
      $query = $query->where('ccpack_scan_status', $fileStatus);
    }
    if (!empty($warehouse)) {
      $query = $query->where('warehouse', $warehouse);
    }
    if (!empty($fromDate) && !empty($toDate)) {
      $query = $query->whereBetween('arrival_date', array($fromDate, $toDate));
    }

    $filteredq = DB::table('ccpack')
      ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
      ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
      ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party')
      ->leftJoin('delivery_boy', 'delivery_boy.id', '=', 'ccpack.delivery_boy');
    //->where('ccpack.deleted', '0');
    if (!empty($fileStatus)) {
      $filteredq = $filteredq->where('ccpack_scan_status', $fileStatus);
    }
    if (!empty($warehouse)) {
      $filteredq = $filteredq->where('warehouse', $warehouse);
    }
    if (!empty($fromDate) && !empty($toDate)) {
      $filteredq = $filteredq->whereBetween('arrival_date', array($fromDate, $toDate));
    }

    if ($search != '') {
      $query->where(function ($query2) use ($search) {
        $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
          ->orWhere(DB::raw("date_format(arrival_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
          ->orWhere('awb_number', 'like', '%' . $search . '%')
          ->orWhere('c1.company_name', 'like', '%' . $search . '%')
          ->orWhere('c2.company_name', 'like', '%' . $search . '%')
          ->orWhere('c3.company_name', 'like', '%' . $search . '%')
          ->orWhere('delivery_boy.name', 'like', '%' . $search . '%')
          ->orWhere('no_of_pcs', 'like', '%' . $search . '%')
          ->orWhere('weight', 'like', '%' . $search . '%')
          ->orWhere('freight', 'like', '%' . $search . '%');
        //->orWhere('ccpack_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
      });
      $filteredq->where(function ($query2) use ($search) {
        $query2->where('ccpack.file_number', 'like', '%' . $search . '%')
          ->orWhere(DB::raw("date_format(arrival_date,'%d-%m-%Y')"), 'like', '%' . $search . '%')
          ->orWhere('awb_number', 'like', '%' . $search . '%')
          ->orWhere('c1.company_name', 'like', '%' . $search . '%')
          ->orWhere('c2.company_name', 'like', '%' . $search . '%')
          ->orWhere('c3.company_name', 'like', '%' . $search . '%')
          ->orWhere('delivery_boy.name', 'like', '%' . $search . '%')
          ->orWhere('no_of_pcs', 'like', '%' . $search . '%')
          ->orWhere('weight', 'like', '%' . $search . '%')
          ->orWhere('freight', 'like', '%' . $search . '%');
        //->orWhere('ccpack_scan_status', array_search($search, Config::get('app.ups_new_scan_status')));
      });
      $filteredq = $filteredq->selectRaw('count(*) as total')->first();
      $totalfiltered = $filteredq->total;
    }
    $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

    $data1 = [];
    foreach ($query as $key => $data) {
      $dataBillingParty = app('App\Clients')->getClientData($data->billing_party);
      $consigneeData = app('App\Clients')->getClientData($data->consignee);
      $consignee = !empty($consigneeData->company_name) ? $consigneeData->company_name : '-';
      $shipperData = app('App\Clients')->getClientData($data->shipper_name);
      $shipper = !empty($shipperData->company_name) ? $shipperData->company_name : '-';
      $deliveryBoyData = app('App\DeliveryBoy')->getDeliveryBodData($data->delivery_boy);
      $fileStatus = isset(Config::get('app.ups_new_scan_status')[!empty($data->ccpack_scan_status) ? $data->ccpack_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($data->ccpack_scan_status) ? $data->ccpack_scan_status : '-'] : '-';
      $invoiceNumbers = Expense::getCcpackInvoicesOfFile($data->id);
      $warehouseName = app('App\Ups')->getWarehouseData($data->id, 'ccpack');

      $action = '<div class="dropup"><a title="Click here to print"  target="_blank" href="' . route("printccpackfile", [$data->id]) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';
      if ($data->deleted == '0') {
      $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

      if ($permissionCcpackAddInvoice) {
        $action .= '<li><a href="' . route('createccpackinvoices', $data->id) . '">Add Invoice</a></li>';
      }
        $action .= '</ul>';
      }
      $action .= '</div>';

      $closedDetail = '';
      if ($data->file_close == 1) {
        $dataUserCloseFile = DB::table('users')->where('id', $data->close_unclose_by)->first();
        $closedDetail .= !empty($data->close_unclose_date) ? date('d-m-Y', strtotime($data->close_unclose_date)) : '-';
        $closedDetail .= ' | ';
        $closedDetail .= !empty($dataUserCloseFile) ? $dataUserCloseFile->name : '-';
      }

      $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $data->id . '" value="' . $data->id . '" />';

      $data1[] = [$data->file_close == 1 ? '' : $checkBoxes, $data->id, $data->master_ccpack_id, $data->file_number, !empty($data->master_file_number) ? $data->master_file_number : 'Not Assigned', !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $fileStatus, $warehouseName, !empty($deliveryBoyData) ? $deliveryBoyData->name : '-', $invoiceNumbers, date('d-m-Y', strtotime($data->arrival_date)), !empty($data->awb_number) ? $data->awb_number : '-', $consignee, $shipper, $data->no_of_pcs, $data->weight . ' ' . 'KGS', $data->freight, ($data->file_close) == 1 ? $closedDetail : $action];
    }
    $json_data = array(
      "draw"            => intval($_REQUEST['draw']),
      "recordsTotal"    => intval($total->total),
      "recordsFiltered" => intval($totalfiltered),
      "data"            => $data1
    );
    return Response::json($json_data);
  }

  public function viewcourierccpackdetailforwarehouse($id)
  {
    $checkPermission = User::checkPermission(['viewdetails_courier_ccpack'], '', auth()->user()->id);
    if (!$checkPermission)
      return redirect('/home');

    $model = ccpack::find($id);
    $deliveryBoys = DB::table('delivery_boy')
      ->select('id', 'name')
      ->where('deleted', 0)->where('status', 1)->get()
      ->pluck('name', 'id');
    return view('warehouse-role.ccpack.viewcourierccpackdetailforwarehouse', ['items' => $model, 'deliveryBoys' => $deliveryBoys]);
  }

  public function courierccpackwarehouseflow($masterId = null, $houseId = null)
  {
    $model = CcpackMaster::find($masterId);
    $modelUpsHouse = ccpack::find($houseId);
    $HouseAWBData = DB::table('ccpack')->where('id', $houseId)->get();
    $deliveryBoys = DB::table('delivery_boy')
      ->select('id', 'name')
      ->where('deleted', 0)->where('status', 1)->get()
      ->pluck('name', 'id');
    return view('warehouse-role.ccpack.warehouseflow', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId, 'masterId' => $masterId, 'items' => $modelUpsHouse]);
  }

  public function viewcourierccpackdetailforwarehousemaster($id, $flag = null, $houseId = null)
  {
    $model = CcpackMaster::find($id);
    $HouseAWBData = DB::table('ccpack')
      ->selectRaw('ccpack.*,c1.company_name as consigneeName,c2.company_name as shipperName,c3.company_name as billingParty')
      ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
      ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
      ->leftJoin('clients as c3', 'c3.id', '=', 'ccpack.billing_party')
      ->where('ccpack.master_ccpack_id', $id)->get();
    $deliveryBoys = DB::table('delivery_boy')
      ->select('id', 'name')
      ->where('deleted', 0)->where('status', 1)->get()
      ->pluck('name', 'id');
    return view('warehouse-role.ccpack.viewcourierccpackdetailforwarehousemaster', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId]);
  }

  /* public function viewcourierccpackdetailforwarehouse($id, $flag = null, $houseId = null)
  {
    $checkPermission = User::checkPermission(['viewdetails_courier_ccpack'], '', auth()->user()->id);
    if (!$checkPermission)
      return redirect('/home');

    $model = CcpackMaster::find($id);
    $HouseAWBData = DB::table('ccpack')->where('master_ccpack_id', $id)->get();
    $deliveryBoys = DB::table('delivery_boy')
      ->select('id', 'name')
      ->where('deleted', 0)->where('status', 1)->get()
      ->pluck('name', 'id');
    return view('warehouse-role.ccpack.viewcourierccpackdetailforwarehouse', ['model' => $model, 'deliveryBoys' => $deliveryBoys, 'HouseAWBData' => $HouseAWBData, 'houseId' => $houseId]);
  } */

  public function assignstatusbywarehousecourierccpackmaster(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
    $oldStatus = $model->ccpack_scan_status;
    $model->update($input);

    if (!empty($model) && !empty($model->ccpack_scan_status)) {
      $newStatus = $model->ccpack_scan_status;
      if ($oldStatus != $newStatus) {
        /* if (empty($oldStatus))
          $oldStatus = '1'; */
        $modelActivities = new Activities;
        $modelActivities->type = 'ccpack';
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

  public function assignstatusbywarehousecourierccpack(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
    $oldStatus = $model->ccpack_scan_status;
    $model->update($input);

    if (!empty($input['shipment_notes_for_return'])) {
      $inputNotes['flag_note'] = 'R';
      $inputNotes['ccpack_id'] = $input['id'];
      $inputNotes['notes'] = $input['shipment_notes_for_return'];
      $inputNotes['created_on'] = date('Y-m-d');
      $inputNotes['created_by'] = auth()->user()->id;
      VerificationInspectionNote::create($inputNotes);
    }

    if (!empty($model) && !empty($model->ccpack_scan_status)) {
      $newStatus = $model->ccpack_scan_status;
      if ($oldStatus != $newStatus) {
        /* if (empty($oldStatus))
          $oldStatus = '1'; */
        $modelActivities = new Activities;
        $modelActivities->type = 'ccpack';
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
        $modelActivities->type = 'ccpack';
        $modelActivities->related_id = $model->id;
        $modelActivities->user_id   = auth()->user()->id;
        $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();
      }
    }
    
    Session::flash('flash_message', 'Status has been updated successfully');
    if ($input['flag'] == 'fromwarehouseflow')
      return redirect('warehouseccpack/courierccpackwarehouseflow/' . $input['masterId'] . '/' . $input['id']);
    else
      return redirect('warehouseccpack/viewcourierccpackdetailforwarehouse/' . $input['id']);
    
    //return 'true';
  }

  public function ccpackstep1shipmentstatus(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
    $input['shipment_received_date'] = date('Y-m-d', strtotime($input['shipment_received_date']));
    $input['shipment_incomplete_date'] = date('Y-m-d', strtotime($input['shipment_incomplete_date']));
    $input['shipment_shortshipped_date'] = date('Y-m-d', strtotime($input['shipment_shortshipped_date']));
    $input['shipment_status_changed_by'] = auth()->user()->id;
    $model->update($input);

    if (!empty($input['shipment_notes'])) {
      $inputNotes['flag_note'] = 'V';
      $inputNotes['ccpack_id'] = $input['id'];
      $inputNotes['notes'] = $input['shipment_notes'];
      $inputNotes['created_on'] = date('Y-m-d');
      $inputNotes['created_by'] = auth()->user()->id;
      VerificationInspectionNote::create($inputNotes);
    }

    $userModel = new User;
    $dataUser = $userModel->getUserName($input['shipment_status_changed_by']);

    $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'V')->where('ccpack_id', $input['id'])->orderBy('id', 'desc')->get();

    $ajaxData['status'] = $input['shipment_status'] == '1' ? 'Received' : Config::get('app.shipmentStatus')[$input['shipment_status']];
    $ajaxData['on'] = $input['shipment_status'] == '1' ? date('d-m-Y', strtotime($model->shipment_received_date)) : ($input['shipment_status'] == '2' ? date('d-m-Y', strtotime($model->shipment_incomplete_date)) : date('d-m-Y', strtotime($model->shipment_shortshipped_date)));
    $ajaxData['changedBy'] = $dataUser->name;
    if (count($dataComments) > 0)
      $ajaxData['comments'] = $dataComments;
    else
      $ajaxData['comments'] = '';


    return view('warehouse-role.ccpack.step1shipmentstatusajax', ['ajaxData' => $ajaxData]);
  }

  public function ccpackstep2custominspection(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
    $input['inspection_flag'] = $input['inspection_flag'] == 'true' ? '1' : '0';
    $input['inspection_date'] = date('Y-m-d', strtotime($input['inspection_date']));
    $input['inspection_by'] = auth()->user()->id;
    $model->update($input);

    $modelActivities = new Activities;
    $modelActivities->type = 'ccpack';
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
      $inputNotes['ccpack_id'] = $input['id'];
      $inputNotes['notes'] = $input['shipment_notes'];
      $inputNotes['created_on'] = date('Y-m-d');
      $inputNotes['created_by'] = auth()->user()->id;
      VerificationInspectionNote::create($inputNotes);
    }

    $userModel = new User;
    $dataUser = $userModel->getUserName($input['inspection_by']);

    $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'I')->where('ccpack_id', $input['id'])->orderBy('id', 'desc')->get();

    $ajaxData['status'] = Config::get('app.inspectionFileWarehouse')[$input['inspection_flag']];
    $ajaxData['on'] = $input['inspection_flag'] == '1' ? date('d-m-Y', strtotime($model->inspection_date)) : '-';
    $ajaxData['changedBy'] = $dataUser->name;
    if (count($dataComments) > 0)
      $ajaxData['comments'] = $dataComments;
    else
      $ajaxData['comments'] = '';


    return view('warehouse-role.ccpack.step2custominspection', ['ajaxData' => $ajaxData]);
  }

  public function ccpackstep3movetononboundedwh(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
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
    $modelActivities->type = 'ccpack';
    $modelActivities->related_id = $input['id'];
    $modelActivities->user_id   = auth()->user()->id;
    if ($input['move_to_nonbounded_wh'] == '1')
      $modelActivities->description = "Shipment move to Non Bounded Warehouse | <strong>Yes</strong> | On " . date('d-m-Y', strtotime($input['move_to_nonbounded_wh_on']));
    else
      $modelActivities->description = "Shipment move to Non Boudned Warehouse | <strong>No</strong>";
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

    return view('warehouse-role.ccpack.step3movetononboundedwh', ['ajaxData' => $ajaxData, 'ajaxDataWHConfirmation' => $ajaxDataWHConfirmation]);
  }

  public function ccpackstep4invoiceandpayment(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
    $model->update($input);
  }

  public function ccpackstep5assigndeliveryboy(Request $request)
  {
    $input = $request->all();
    $model = ccpack::find($input['id']);
    $oldStatus = $model->ccpack_scan_status;
    if ($input['reason'] == '0') {
      $input['delivery_boy_assigned_on'] = date('Y-m-d');
      $input['delivery_boy_assigned_by'] = auth()->user()->id;
      $model->update($input);

      $userModel = new User;
      $dataUser = $userModel->getUserName($input['delivery_boy_assigned_by']);

      $deliveryBoyModel = new DeliveryBoy;
      $dataDeliveryBoy = $deliveryBoyModel->getDeliveryBodData($input['delivery_boy']);

      $modelActivities = new Activities;
      $modelActivities->type = 'ccpack';
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

      return view('warehouse-role.ccpack.ccpackstep5assigndeliveryboy', ['ajaxData' => $ajaxData]);
    } else {
      if ($input['ccpack_scan_status'] == '6') {
        $input['warehouse_status'] = '3';
        $input['shipment_delivered_date'] = date('Y-m-d');
      }
      $model->update($input);

      if (!empty($input['shipment_notes'])) {
        $inputNotes['flag_note'] = 'R';
        $inputNotes['ccpack_id'] = $input['id'];
        $inputNotes['notes'] = $input['shipment_notes'];
        $inputNotes['created_on'] = date('Y-m-d');
        $inputNotes['created_by'] = auth()->user()->id;
        VerificationInspectionNote::create($inputNotes);
      }

      if (!empty($model) && !empty($model->ccpack_scan_status)) {
        $newStatus = $model->ccpack_scan_status;
        if ($oldStatus != $newStatus) {
          /* if (empty($oldStatus))
            $oldStatus = '1'; */
          $modelActivities = new Activities;
          $modelActivities->type = 'ccpack';
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
          $modelActivities->type = 'ccpack';
          $modelActivities->related_id = $model->id;
          $modelActivities->user_id   = auth()->user()->id;
          $modelActivities->description = "File Comment : " . $input['shipment_notes'];
          $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
          $modelActivities->save();
        }
      }

      $dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'R')->where('ccpack_id', $input['id'])->orderBy('id', 'desc')->get();
      if (count($dataComments) > 0)
        $ajaxData['comments'] = $dataComments;
      else
        $ajaxData['comments'] = '';

      return view('warehouse-role.ccpack.ccpackstep5assigndeliveryboyreason', ['ajaxData' => $ajaxData]);
    }
  }
}
