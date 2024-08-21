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

class UpsMasterAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(UpsMaster $upsMaster, $id)
    {
        $checkPermission = User::checkPermission(['assign_billingparty_cashcredit_ups_master'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }
        $model = UpsMaster::find($id);
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'upsMaster')->orderBy('id', 'desc')->get()->toArray();
        $billingParty = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->pluck('company_name', 'id');
        $billingParty = json_decode($billingParty, 1);
        ksort($billingParty);
        $attachedFiles = DB::table('ups_uploaded_files')->where('master_file_id', $id)->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        $fileTypes = Config::get('app.fileTypes');
        return view('agent-role.ups-master.view', ['model' => $model, 'billingParty' => $billingParty, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'activityData' => $activityData]);
    }

    public function assignOperations(Request $request)
    {
        $input = $request->all();
        $model = UpsMaster::find($input['id']);
        $oldArrivalDate = $model->arrival_date;
        $oldBillingParty = $model->billing_party;
        $newBillingParty = $request->billing_party;
        $input['arrival_date'] = !empty($input['arrival_date']) ? date('Y-m-d', strtotime($input['arrival_date'])) : null;
        $model->update($input);

        if ($oldArrivalDate != $input['arrival_date']) {
            $modelActivities = new Activities;
            $modelActivities->type = 'upsMaster';
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
            $modelActivities->type = 'upsMaster';
            $modelActivities->related_id = $model->id;
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = 'Updated Billing Party From <b>' . $oldBillingPartyNameA . '</b> To <b>' . $newBillingPartyNameA . '</b>';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
        }
        return 'true';
    }
}
