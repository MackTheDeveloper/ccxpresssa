<?php

namespace App\Http\Controllers;

use App\Cargo;
use Illuminate\Http\Request;
use App\User;
use App\CargoProductDetails;
use App\CargoConsolidateAwbHawb;
use App\CargoContainers;
use App\CargoPackages;
use App\VerificationInspectionNote;
use Session;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Illuminate\Support\Facades\Validator;
use Response;
use PDF;
use Config;
use App\Expense;
use App\HawbFiles;

class AgentCargoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function agentcargoimportsajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cargos = DB::table('cargo')->where('status', 1)->where('deleted', '0')->where('cargo_operation_type', '1')->orderBy('id', 'desc')->get();
        return view("agent-role.cargo.importindexajax", ['cargos' => $cargos]);
    }
    public function agentcargoexportsajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cargos = DB::table('cargo')->where('status', 1)->where('deleted', '0')->where('cargo_operation_type', '2')->orderBy('id', 'desc')->get();
        return view("agent-role.cargo.exportindexajax", ['cargos' => $cargos]);
    }
    public function agentcargolocalesajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cargos = DB::table('cargo')->where('status', 1)->where('deleted', '0')->where('cargo_operation_type', '3')->orderBy('id', 'desc')->get();
        return view("agent-role.cargo.localeindexajax", ['cargos' => $cargos]);
    }
    public function agentcargoallajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cargos = DB::table('cargo')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get();
        return view("agent-role.cargo.cargoallajax", ['cargos' => $cargos]);
    }
    public function agentcargoall()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $cargos = DB::table('cargo')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get();
        return view("agent-role.cargo.cargoall", ['cargos' => $cargos]);
    }

    public function viewcargodetailforagent($id)
    {
        $checkPermission = User::checkPermission(['assign_billingparty_cashcredit_warehouse_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = Cargo::find($id);
        Cargo::where('id', $id)->update(['display_notification' => 0]);

        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Cargo')->pluck('name', 'id');
        $warehouses = json_decode($warehouses, 1);
        ksort($warehouses);

        $dataHawbIds = explode(',', $model->hawb_hbl_no);

        $HouseAWBData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->get();
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'cargo')->orderBy('id', 'desc')->get()->toArray();
        $attachedFiles = DB::table('cargo_uploaded_files')->where('file_id', $id)->where('flag_module', 'cargo')->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');
        return view('agent-role.cargo.viewcargodetailforagent', ['model' => $model, 'billingParty' => $billingParty, 'warehouses' => $warehouses, 'HouseAWBData' => $HouseAWBData, 'activityData' => $activityData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes]);
    }

    public function assignonconsolidationbyagent(Request $request)
    {
        $input = $request->all();
        $model = Cargo::find($input['id']);
        $oldStatus = $model->cargo_master_scan_status;
        $oldWarehouse = $model->warehouse;
        $oldArrivalDate = $model->arrival_date;
        $newWarehouse = $request->warehouse;
        $oldBillingParty = $model->billing_party;
        $newBillingParty = $request->billing_party;

        if ($oldWarehouse != $newWarehouse) {
            $input['display_notification_warehouse'] = '1';
            $input['display_notification_admin'] = '1';
        }

        if ($oldBillingParty != $newBillingParty) {
            $oldBillingPartyName = DB::table('clients')->where('id', $oldBillingParty)->first();
            $oldBillingPartyNameA = !empty($oldBillingPartyName) ? $oldBillingPartyName->company_name : 'N/A';
            $newBillingPartyName = DB::table('clients')->where('id', $newBillingParty)->first();
            $newBillingPartyNameA = !empty($newBillingPartyName) ? $newBillingPartyName->company_name : 'N/A';
            $modelActivities = new Activities;
            $modelActivities->type = 'cargo';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Updated Billing Party From <b>' . $oldBillingPartyNameA . '</b> To <b>'. $newBillingPartyNameA .'</b>';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }

        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $dataHawb = explode(',', $model->hawb_hbl_no);
        $data = DB::table('hawb_files')->whereIn('id', $dataHawb)->update(['arrival_date' => $input['arrival_date'], 'shipment_received_date' => $input['arrival_date'], 'shipment_status' => '1', 'shipment_status_changed_by' => auth()->user()->id, 'warehouse_status' => '1']);
        if ($oldArrivalDate != $input['arrival_date']) {
            $modelActivities = new Activities;
            $modelActivities->type = 'cargo';
            $modelActivities->related_id = $input['id'];
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Arrival date has been updated to ' . date('d-m-Y', strtotime($input['arrival_date']));
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        $input['notification_date_time'] = date('Y-m-d H:i:s');
        $input['updated_by'] = auth()->user()->id;
        $model->update($input);
        if (!empty($model)) {
            $newStatus = $model->cargo_master_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'cargo';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;

                if (!empty($oldStatus))
                    $modelActivities->description = "File Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                else
                    $modelActivities->description = " File Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'cargo';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        
        return 'true';
    }

    public function viewhawbdetailforagent($id)
    {
        $checkPermission = User::checkPermission(['assign_billingparty_cashcredit_warehouse_hawb'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = HawbFiles::find($id);


        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);

        $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Cargo')->pluck('name', 'id');
        $warehouses = json_decode($warehouses, 1);
        ksort($warehouses);

        $dataHawbIds = explode(',', $model->hawb_hbl_no);

        $HouseAWBData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->get();
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'houseFile')->orderBy('id', 'desc')->get()->toArray();
        $attachedFiles = DB::table('cargo_uploaded_files')->where('file_id', $id)->where('flag_module', 'houseFile')->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');
        return view('agent-role.hawb-files.viewhawbdetailforagent', ['model' => $model, 'billingParty' => $billingParty, 'warehouses' => $warehouses, 'HouseAWBData' => $HouseAWBData, 'activityData' => $activityData, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes]);
    }

    public function assignonconsolidationbyagenttohawb(Request $request)
    {
        $input = $request->all();
        $model = HawbFiles::find($input['id']);
        $oldStatus = $model->hawb_scan_status;
        $oldArrivalDate = $model->arrival_date;
        $oldBillingParty = $model->billing_party;
        $newBillingParty = $request->billing_party;
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['shipment_received_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $input['shipment_status'] = '1';
        $input['shipment_status_changed_by'] = auth()->user()->id;
        if ($input['hawb_scan_status'] == '6') {
            $input['warehouse_status'] = '3';
            $input['shipment_delivered_date'] = date('Y-m-d');
        }
        $input['updated_by'] = auth()->user()->id;
        $model->update($input);
        $inputNotes['flag_note'] = 'R';
        $inputNotes['hawb_id'] = $input['id'];
        $inputNotes['notes'] = $input['shipment_notes_for_return'];
        $inputNotes['created_on'] = date('Y-m-d');
        $inputNotes['created_by'] = auth()->user()->id;
        VerificationInspectionNote::create($inputNotes);

        if (!empty($model)) {
            $newStatus = $model->hawb_scan_status;
            if ($oldStatus != $newStatus) {
                /* if (empty($oldStatus))
                    $oldStatus = '1'; */
                $modelActivities = new Activities;
                $modelActivities->type = 'houseFile';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;

                if (!empty($oldStatus))
                    $modelActivities->description = "Status has been changed from - <strong>" . Config::get('app.ups_new_scan_status')[$oldStatus] . "</strong> To <strong>" . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";
                else
                    $modelActivities->description = "Status has been updated to " . Config::get('app.ups_new_scan_status')[$newStatus] . "</strong>" . " (" . $input['shipment_notes_for_return'] . ")";

                
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            } else {
                $modelActivities = new Activities;
                $modelActivities->type = 'houseFile';
                $modelActivities->related_id = $model->id;
                $modelActivities->user_id   = auth()->user()->id;
                $modelActivities->description = "File Comment : " . $input['shipment_notes_for_return'];
                $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                $modelActivities->save();
            }
        }

        if ($oldArrivalDate != $input['arrival_date']) {
            $modelActivities = new Activities;
            $modelActivities->type = 'houseFile';
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
            $modelActivities->type = 'houseFile';
            $modelActivities->related_id = $model->id;
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Updated Billing Party From <b>' . $oldBillingPartyNameA . '</b> To <b>' . $newBillingPartyNameA . '</b>';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        return 'true';
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoExpensesAdd = User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
        $permissionCargoEdit = User::checkPermission(['update_cargo'], '', auth()->user()->id);

        $req = $request->all();
        $fileStatus = $req['fileStatus'];
        $cargoFileType = $req['cargoFileType'];
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
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('cargo')
            ->selectRaw('cargo.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
            ->leftJoin('users', 'users.id', '=', 'cargo.agent_id')
            ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
            ->leftJoin('clients as c3', 'c3.id', '=', 'cargo.billing_party');
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

            if ($value->deleted == '0') {
                $edit = route('editcargo', [$value->id, $value->cargo_operation_type]);
                if ($permissionCargoEdit) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';
                if ($permissionCargoExpensesAdd) {
                    if ($value->cargo_operation_type != 3) {
                        $action .= '<li><a href="' . route('createagentexpenses', ['cargo', $value->id, 'flagFromListing']) . '">Add Expense</a></li>';
                    }
                }

                if ($value->consolidate_flag == 1 && ($value->cargo_operation_type == 1 || $value->cargo_operation_type == 2)) {
                    $action .= '<li><button id="btnAddWarehouseInFile" data-module ="Warehouse" class="btnModalPopup" value="' . route('addwarehouseinfile', $value->id) . '">Add Warehouse</button></li>';
                }

                if ($value->cargo_operation_type != 3) {
                    $action .= '<li><button id="btnAddCashCreditInFile" data-module ="Payment Mode" class="btnModalPopup" value="' . route('addcashcreditinfile', $value->id) . '">Add Payment Mode</button></li>';
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

            $data[] = [$value->id, '', $value->file_number, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", isset(Config::get('app.ups_new_scan_status')[!empty($value->cargo_master_scan_status) ? $value->cargo_master_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($value->cargo_master_scan_status) ? $value->cargo_master_scan_status : '-'] : '-', $agent, date('d-m-Y', strtotime($value->opening_date)), !empty($value->awb_bl_no) ? $value->awb_bl_no : '-', $consignee, $shipper, $invoiceNumbers, ($value->file_close) == 1 ? $closedDetail : $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }
}
