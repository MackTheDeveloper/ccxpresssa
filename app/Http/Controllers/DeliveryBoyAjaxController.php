<?php

namespace App\Http\Controllers;

use App\DeliveryBoy;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use Session;
use Response;
use Config;
class DeliveryBoyAjaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_delivery_boy'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $data = DB::table('delivery_boy')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("delivery-boy.index", ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_delivery_boy'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new DeliveryBoy;
        return view('delivery-boy.form', ['model' => $model]);
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
        $model = DeliveryBoy::create($input);
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('deliveryboys');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DeliveryBoy  $deliveryBoy
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryBoy $deliveryBoy, $id)
    {
        $model = DB::table('delivery_boy')->where('id', $id)->first();
        /* $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->groupBy('ups_details.id')
            ->orderBy('ups_details.id')
            ->get(); */
        return view('delivery-boy.view', ['model' => $model, 'id' => $id]);
    }

    
    public function upslistbydatatableserverside(Request $request)
    {
        $req = $request->all();
        $id = $req['id'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['ups_details.id','file_number', 'c1.company_name', 'c2.company_name' , 'delivery_status','shipment_number', 'awb_number', 'delivery_boy_assigned_on', '','','','','','',''];

        $total = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->groupBy('ups_details.id')
            ->orderBy('ups_details.id','desc');
        
        $total = $total->get();
        $totalfiltered = count($total);
        

        $query = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->groupBy('ups_details.id');
        $filteredq = DB::table('ups_details')
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->groupBy('ups_details.id');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('shipment_number', 'like', '%' . $search . '%')
                    ->orWhere('package_type', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('file_number', 'like', '%' . $search . '%')
                    ->orWhere('awb_number', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                    ->orWhere('shipment_number', 'like', '%' . $search . '%')
                    ->orWhere('package_type', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->get();
            $totalfiltered = count($filteredq);
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $couriers) {
            $billingTerm = app('App\Ups')::getBillingTerm($couriers->id);
            $countPending = app('App\Common')->checkIfInvoiceStatusPending($couriers->id, 'ups');
            if ($couriers->delivery_status == 2) {
                $getCommentOfDelivery = app('App\Common')->getCommentOfDelivery($couriers->id, 'ups');
                $deliveryComment = $getCommentOfDelivery->notes . ' - ' . Config::get('app.reasonOfReturn')[$couriers->reason_for_return];
            } else {
                $getCommentOfDelivery = app('App\Common')->getCommentOfDelivery($couriers->id, 'ups');
                $deliveryComment = !empty($getCommentOfDelivery) ? $getCommentOfDelivery->notes : '-';
            }

            if ($couriers->package_type == 'LTR')
                $packageType = 'Letter';
            else if ($couriers->package_type == 'DOC')
                $packageType = 'Document';
            else
                $packageType = 'Package';

            $data[] = [$couriers->id,$couriers->file_number, $couriers->consigneeName, $couriers->shipperName, !empty($couriers->delivery_status) ? Config::get('app.deliveryStatus')[$couriers->delivery_status] : '-', !empty($couriers->shipment_number) ? $couriers->shipment_number : '-', $couriers->awb_number, !empty($couriers->delivery_boy_assigned_on) ? date('d-m-Y', strtotime($couriers->delivery_boy_assigned_on)) : '-', $packageType,$billingTerm, !empty($couriers->invoiceNumbers) ? $couriers->invoiceNumbers : '-', !empty($couriers->totalAmount) ? $couriers->totalAmount : '0.00', !empty($couriers->paidAmount) ? $couriers->paidAmount : '0.00', $countPending > 0 ? 'Pending' : 'Paid', $deliveryComment];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval(count($total)),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function filterfiles()
    {
        $id = $_POST['id'];
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileStatus = $_POST['fileStatus'];
        $courierType = $_POST['courierType'];
        $billingTerm = $_POST['billingTerm'];
        if(!empty($billingTerm))
        {
            if($billingTerm == 'P/P')
                $col = 'pp';
            else if ($billingTerm == 'F/C')
                $col = 'fc';
            else if ($billingTerm == 'F/D')
                $col = 'fd';
        }

        $model = DB::table('delivery_boy')->where('id', $id)->first();
        if ($courierType == 'UPS') {
            $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ups_details.delivery_boy', $id)
                ->where('ups_details.deleted', '0');
            if (!empty($fileStatus))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where('ups_details.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
            if (!empty($billingTerm))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where($col, '1');
            $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->groupBy('ups_details.id')
                ->orderBy('ups_details.id')
                ->get();
            return view('delivery-boy.db-ups-files', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'Aeropost') {
            $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                ->select('aeropost.*', 'c1.company_name as consigneeName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('aeropost.delivery_boy', $id)
                ->where('aeropost.deleted', '0');
            if (!empty($fileStatus))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->where('aeropost.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
            $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->groupBy('aeropost.id')
                ->orderBy('aeropost.id')
                ->get();
            return view('delivery-boy.db-aeropost-files', ['aeropostFileAssignedToDeliveryBoy' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'CCPack') {
            $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                ->select('ccpack.*', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ccpack.delivery_boy', $id)
                ->where('ccpack.deleted', '0');
            if (!empty($fileStatus))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->where('ccpack.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
            $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->groupBy('ccpack.id')
                ->orderBy('ccpack.id')
                ->get();

            return view('delivery-boy.db-ccpack-files', ['ccpackFileAssignedToDeliveryBoy' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model]);
        }
    }

    public function manifestdetailsdeliveryboy(DeliveryBoy $deliveryBoy, $id)
    {
        $model = DB::table('delivery_boy')->where('id', $id)->first();
        $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->groupBy('ups_details.id')
            ->orderBy('ups_details.id')
            ->get();
        return view('delivery-boy.view-manifest-details', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
    }

    public function filterfilesmanifestdetails()
    {
        $id = $_POST['id'];
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileStatus = $_POST['fileStatus'];
        $courierType = $_POST['courierType'];
        $billingTerm = $_POST['billingTerm'];
        if (!empty($billingTerm)) {
            if ($billingTerm == 'P/P')
                $col = 'pp';
            else if ($billingTerm == 'F/C')
                $col = 'fc';
            else if ($billingTerm == 'F/D')
                $col = 'fd';
        }

        $model = DB::table('delivery_boy')->where('id', $id)->first();
        if ($courierType == 'UPS') {
            $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ups_details.delivery_boy', $id)
                ->where('ups_details.deleted', '0');
            if (!empty($fileStatus))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where('ups_details.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
            if (!empty($billingTerm))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where($col, '1');
            $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->groupBy('ups_details.id')
                ->orderBy('ups_details.id')
                ->get();
            return view('delivery-boy.db-ups-manifest-details-files', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'Aeropost') {
            $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                ->select('aeropost.*', 'c1.company_name as consigneeName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('aeropost.delivery_boy', $id)
                ->where('aeropost.deleted', '0');
            if (!empty($fileStatus))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->where('aeropost.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
            $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->groupBy('aeropost.id')
                ->orderBy('aeropost.id')
                ->get();
            return view('delivery-boy.db-aeropost-manifest-details-files', ['aeropostFileAssignedToDeliveryBoy' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'CCPack') {
            $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                ->select('ccpack.*', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ccpack.delivery_boy', $id)
                ->where('ccpack.deleted', '0');
            if (!empty($fileStatus))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->where('ccpack.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
            $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->groupBy('ccpack.id')
                ->orderBy('ccpack.id')
                ->get();

            return view('delivery-boy.db-ccpack-manifest-details-files', ['ccpackFileAssignedToDeliveryBoy' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model]);
        }
    }

    public function cashcollectiondetailsdeliveryboy($id)
    {
        $model = DB::table('delivery_boy')->where('id', $id)->first();
        $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->orderBy('ups_details.id')
            ->get();

        return view('delivery-boy.view-cash-collection', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
    }

    public function filterfilescashcollection()
    {
        $id = $_POST['id'];
        $fromDate = !empty($_POST['fromDate']) ? date('Y-m-d', strtotime($_POST['fromDate'])) : '';
        $toDate = !empty($_POST['toDate']) ? date('Y-m-d', strtotime($_POST['toDate'])) : '';
        $fileStatus = $_POST['fileStatus'];
        $courierType = $_POST['courierType'];
        $billingTerm = $_POST['billingTerm'];
        if (!empty($billingTerm)) {
            if ($billingTerm == 'P/P')
                $col = 'pp';
            else if ($billingTerm == 'F/C')
                $col = 'fc';
            else if ($billingTerm == 'F/D')
                $col = 'fd';
        }

        $model = DB::table('delivery_boy')->where('id', $id)->first();
        if ($courierType == 'UPS') {
            $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName',  'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ups_details.delivery_boy', $id)
                ->where('ups_details.deleted', '0');
            if (!empty($fileStatus))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where('ups_details.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
            if (!empty($billingTerm))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where($col, '1');
            $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->orderBy('ups_details.id')
                ->get();
            return view('delivery-boy.db-ups-cash-collection-files', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'Aeropost') {
            $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                ->select('aeropost.*', 'c1.company_name as consigneeName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                ->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('aeropost.delivery_boy', $id)
                ->where('aeropost.deleted', '0');
            if (!empty($fileStatus))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->where('aeropost.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
            $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->orderBy('aeropost.id')
                ->get();
            return view('delivery-boy.db-aeropost-cash-collection-files', ['aeropostFileAssignedToDeliveryBoy' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'CCPack') {
            $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                ->select('ccpack.*', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                ->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ccpack.delivery_boy', $id)
                ->where('ccpack.deleted', '0');
            if (!empty($fileStatus))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->where('ccpack.delivery_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
            $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->orderBy('ccpack.id')
                ->get();

            return view('delivery-boy.db-ccpack-cash-collection-files', ['ccpackFileAssignedToDeliveryBoy' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DeliveryBoy  $deliveryBoy
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryBoy $deliveryBoy, $id)
    {
        $checkPermission = User::checkPermission(['update_delivery_boy'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('delivery_boy')->where('id', $id)->first();
        return view("delivery-boy.form", ['model' => $model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DeliveryBoy  $deliveryBoy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryBoy $deliveryBoy, $id)
    {
        $model = DeliveryBoy::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('deliveryboys');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DeliveryBoy  $deliveryBoy
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryBoy $deliveryBoy, $id)
    {
        $model = DeliveryBoy::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);
    }
}
