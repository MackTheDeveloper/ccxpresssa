<?php

namespace App\Http\Controllers;

use App\DeliveryBoy;
use Illuminate\Http\Request;
use App\User;
use App\Ups;
use App\Aeropost;
use App\ccpack;
use App\VerificationInspectionNote;
use Illuminate\Support\Facades\DB;
use Session;
use PDF;

class DeliveryBoyController extends Controller
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
        $UpsClientId = '';
        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        if (!empty($dataClient))
            $UpsClientId = $dataClient->id;
        $fromDate = date('Y-m-d');
        $toDate = date('Y-m-d');
        $model = DB::table('delivery_boy')->where('id', $id)->first();
        $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->where('ups_details.deleted', '0')
            ->where('ups_details.fd', 0)
            ->where('invoices.bill_to', '<>', $UpsClientId)
            ->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate))
            ->groupBy('ups_details.id')
            ->orderBy('ups_details.id')
            ->get();
        return view('delivery-boy.view', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
    }

    public function filterfiles()
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
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ups_details.delivery_boy', $id)
                ->where('ups_details.deleted', '0')
                ->where('ups_details.fd', 0)
                ->where('invoices.bill_to', '<>', $UpsClientId);
            if (!empty($fileStatus))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where('ups_details.ups_scan_status', $fileStatus);
            if (!empty($fromDate) && !empty($toDate))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
            if (!empty($billingTerm))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where($col, '1');
            $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->groupBy('ups_details.id')
                ->orderBy('ups_details.id')
                ->get();
            return view('delivery-boy.db-ups-files', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
        } else if ($courierType == 'Aeropost') {
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                ->select('aeropost.*', 'c1.company_name as consigneeName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                ->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('aeropost.delivery_boy', $id)
                ->where('aeropost.deleted', '0')
                ->where('invoices.bill_to', '<>', $UpsClientId);
            if (!empty($fileStatus))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->where('aeropost.aeropost_scan_status', $fileStatus);
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
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->where('ccpack.ccpack_scan_status', $fileStatus);
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
        $UpsClientId = '';
        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        if (!empty($dataClient))
            $UpsClientId = $dataClient->id;
        $fromDate = date('Y-m-d');
        $toDate = date('Y-m-d');
        $model = DB::table('delivery_boy')->where('id', $id)->first();
        $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
            //->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('invoices', function ($join) use ($UpsClientId) {
                $join->on('invoices.ups_id', '=', 'ups_details.id')
                    ->where('invoices.bill_to', '<>', $UpsClientId)
                    ->where('invoices.total', '!=', '0.00');
            })
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate))
            ->where('ups_details.deleted', '0')
            //->where('ups_details.fd', 0)
            /* ->where('invoices.bill_to', '<>', $UpsClientId)
            ->where('invoices.total', '!=', '0.00') */
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
        $submitButtonName = $_POST['submitButtonName'];

        if (!empty($billingTerm)) {
            if ($billingTerm == 'P/P')
                $col = 'pp';
            else if ($billingTerm == 'F/C')
                $col = 'fc';
            else if ($billingTerm == 'F/D')
                $col = 'fd';
        }

        $model = DB::table('delivery_boy')->where('id', $id)->first();

        if ($submitButtonName == 'clsPrintAll') {
            // --- UPS --- //
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                ->select(DB::raw("'UPS' as courierType"), 'ups_details.*', 'ups_details.weight as totalWeight', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                //->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                ->leftJoin('invoices', function ($join) use ($UpsClientId) {
                    $join->on('invoices.ups_id', '=', 'ups_details.id')
                        ->where('invoices.bill_to', '<>', $UpsClientId)
                        ->where('invoices.total', '!=', '0.00');
                })
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ups_details.delivery_boy', $id)
                ->where('ups_details.deleted', '0');
                //->where('ups_details.fd', 0);
                /* ->where('invoices.bill_to', '<>', $UpsClientId)
                ->where('invoices.total', '!=', '0.00'); */
            if (!empty($fromDate) && !empty($toDate))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
            $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->groupBy('ups_details.id')
                ->orderBy('ups_details.id')
                ->get()->toArray();

            // --- Aeropost --- //
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->orWhere('company_name', 'Aeropost')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                ->select(DB::raw("'Aeropost' as courierType"), 'aeropost.*', 'aeropost.real_weight as totalWeight', 'c1.company_name as consigneeName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                //->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                ->leftJoin('invoices', function ($join) use ($UpsClientId) {
                    $join->on('invoices.aeropost_id', '=', 'aeropost.id')
                        ->where('invoices.bill_to', '<>', $UpsClientId)
                        ->where('invoices.total', '!=', '0.00');
                })
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('aeropost.delivery_boy', $id)
                ->where('aeropost.deleted', '0');
                /* ->where('invoices.bill_to', '<>', $UpsClientId)
                ->where('invoices.total', '!=', '0.00'); */
            if (!empty($fromDate) && !empty($toDate))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
            $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->groupBy('aeropost.id')
                ->orderBy('aeropost.id')
                ->get()->toArray();

            // --- CCPack --- //
            $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                ->select(DB::raw("'CCPack' as courierType"), 'ccpack.*', 'ccpack.weight as totalWeight', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                //->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                ->leftJoin('invoices', function ($join) {
                    $join->on('invoices.ccpack_id', '=', 'ccpack.id')
                        ->where('invoices.total', '!=', '0.00');
                })
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ccpack.delivery_boy', $id)
                ->where('ccpack.deleted', '0');
                /* ->where('invoices.total', '!=', '0.00'); */
            if (!empty($fromDate) && !empty($toDate))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
            $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->groupBy('ccpack.id')
                ->orderBy('ccpack.id')
                ->get()->toArray();

            $allData = array_merge($upsFileAssignedToDeliveryBoy, $aeropostFileAssignedToDeliveryBoy, $ccpackFileAssignedToDeliveryBoy);
            $pdf = PDF::loadView('delivery-boy.print-manifests-all', ['data' => $allData, 'model' => $model, 'courierType' => 'UPS, Aeropost, CCPack', 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                'format' => 'A4-L',
                'orientation' => 'L'
            ]);
            $pdf_file = time() . '_manifest_ups.pdf';
            $pdf_path = 'public/manifests/' . $pdf_file;
            $pdf->save($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else {
            if ($courierType == 'UPS') {
                $UpsClientId = '';
                $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                if (!empty($dataClient))
                    $UpsClientId = $dataClient->id;
                $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                    ->select('ups_details.*', 'ups_details.weight as totalWeight', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                    //->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                    ->leftJoin('invoices', function ($join) use($UpsClientId) {
                        $join->on('invoices.ups_id', '=', 'ups_details.id')
                        ->where('invoices.bill_to', '<>', $UpsClientId)
                        ->where('invoices.total', '!=', '0.00');
                    })
                    ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                    ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                    ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                    ->where('ups_details.delivery_boy', $id)
                    ->where('ups_details.deleted', '0');
                    //->where('ups_details.fd', 0);
                    //->where('invoices.bill_to', '<>', $UpsClientId)
                    //->where('invoices.total', '!=', '0.00');
                if (!empty($fileStatus))
                    $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where('ups_details.ups_scan_status', $fileStatus);
                if (!empty($fromDate) && !empty($toDate))
                    $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
                if (!empty($billingTerm))
                    $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where($col, '1');
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->groupBy('ups_details.id')
                    ->orderBy('ups_details.id')
                    ->get();

                if ($submitButtonName == 'clsSubmit') {
                    return view('delivery-boy.db-ups-manifest-details-files', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model, 'deliveryBoyId' => $id]);
                } else {
                    $pdf = PDF::loadView('delivery-boy.print-manifests-all', ['data' => $upsFileAssignedToDeliveryBoy, 'model' => $model, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                        'format' => 'A4-L',
                        'orientation' => 'L'
                    ]);
                    $pdf_file = time() . '_manifest_ups.pdf';
                    $pdf_path = 'public/manifests/' . $pdf_file;
                    $pdf->save($pdf_path);
                    return url('/') . '/' . $pdf_path;
                }
            } else if ($courierType == 'Aeropost') {
                $UpsClientId = '';
                $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->orWhere('company_name', 'Aeropost')->first();
                if (!empty($dataClient))
                    $UpsClientId = $dataClient->id;
                $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                    ->select('aeropost.*', 'aeropost.real_weight as totalWeight', 'c1.company_name as consigneeName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                    //->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->leftJoin('invoices', function ($join) use ($UpsClientId) {
                        $join->on('invoices.aeropost_id', '=', 'aeropost.id')
                            ->where('invoices.bill_to', '<>', $UpsClientId)
                            ->where('invoices.total', '!=', '0.00');
                    })
                    ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                    ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                    ->where('aeropost.delivery_boy', $id)
                    ->where('aeropost.deleted', '0');
                    /* ->where('invoices.bill_to', '<>', $UpsClientId)
                    ->where('invoices.total', '!=', '0.00'); */
                if (!empty($fileStatus))
                    $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->where('aeropost.aeropost_scan_status', $fileStatus);
                if (!empty($fromDate) && !empty($toDate))
                    $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->groupBy('aeropost.id')
                    ->orderBy('aeropost.id')
                    ->get();
                if ($submitButtonName == 'clsSubmit') {
                    return view('delivery-boy.db-aeropost-manifest-details-files', ['aeropostFileAssignedToDeliveryBoy' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model, 'deliveryBoyId' => $id]);
                } else {
                    $pdf = PDF::loadView('delivery-boy.print-manifests-all', ['data' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                        'format' => 'A4-L',
                        'orientation' => 'L'
                    ]);
                    $pdf_file = time() . '_manifest_aeropost.pdf';
                    $pdf_path = 'public/manifests/' . $pdf_file;
                    $pdf->save($pdf_path);
                    return url('/') . '/' . $pdf_path;
                }
            } else if ($courierType == 'CCPack') {
                $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                    ->select('ccpack.*', 'ccpack.weight as totalWeight', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', DB::raw('group_concat(invoices.bill_no) as invoiceNumbers'), DB::raw('SUM(invoices.total) as totalAmount'), DB::raw('SUM(invoices.credits) as paidAmount'))
                    //->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->leftJoin('invoices', function ($join) {
                        $join->on('invoices.ccpack_id', '=', 'ccpack.id')
                            ->where('invoices.total', '!=', '0.00');
                    })
                    ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                    ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                    ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                    ->where('ccpack.delivery_boy', $id)
                    ->where('ccpack.deleted', '0');
                    /* ->where('invoices.total', '!=', '0.00'); */
                if (!empty($fileStatus))
                    $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->where('ccpack.ccpack_scan_status', $fileStatus);
                if (!empty($fromDate) && !empty($toDate))
                    $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->groupBy('ccpack.id')
                    ->orderBy('ccpack.id')
                    ->get();
                if ($submitButtonName == 'clsSubmit') {
                    return view('delivery-boy.db-ccpack-manifest-details-files', ['ccpackFileAssignedToDeliveryBoy' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model, 'deliveryBoyId' => $id]);
                } else {
                    $pdf = PDF::loadView('delivery-boy.print-manifests-all', ['data' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                        'format' => 'A4-L',
                        'orientation' => 'L'
                    ]);
                    $pdf_file = time() . '_manifest_ccpack.pdf';
                    $pdf_path = 'public/manifests/' . $pdf_file;
                    $pdf->save($pdf_path);
                    return url('/') . '/' . $pdf_path;
                }
            }
        }
    }

    public function cashcollectiondetailsdeliveryboy($id)
    {
        $UpsClientId = '';
        $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
        if (!empty($dataClient))
            $UpsClientId = $dataClient->id;
        $fromDate = date('Y-m-d');
        $toDate = date('Y-m-d');
        $model = DB::table('delivery_boy')->where('id', $id)->first();
        $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
            ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
            ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
            ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
            ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('ups_details.delivery_boy', $id)
            ->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate))
            ->where('ups_details.deleted', '0')
            ->where('ups_details.fd', 0)
            ->where('invoices.bill_to', '<>', $UpsClientId)
            ->where('invoices.total', '!=', '0.00')
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
        $submitButtonName = $_POST['submitButtonName'];

        if (!empty($billingTerm)) {
            if ($billingTerm == 'P/P')
                $col = 'pp';
            else if ($billingTerm == 'F/C')
                $col = 'fc';
            else if ($billingTerm == 'F/D')
                $col = 'fd';
        }

        $model = DB::table('delivery_boy')->where('id', $id)->first();

        if ($submitButtonName == 'clsPrintAll') {
            // --- UPS --- //
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                ->select(DB::raw("'UPS' as courierType"), 'ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName',  'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ups_details.delivery_boy', $id)
                ->where('ups_details.deleted', '0')
                ->where('ups_details.fd', 0)
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->where('invoices.total', '!=', '0.00');
            if (!empty($fromDate) && !empty($toDate))
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
            $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->orderBy('ups_details.id')
                ->get()->toArray();

            // --- Aeropost --- //
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;
            $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                ->select(DB::raw("'Aeropost' as courierType"), 'aeropost.*', 'c1.company_name as consigneeName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                ->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('aeropost.delivery_boy', $id)
                ->where('aeropost.deleted', '0')
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->where('invoices.total', '!=', '0.00');
            if (!empty($fromDate) && !empty($toDate))
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
            $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->orderBy('aeropost.id')
                ->get()->toArray();

            // --- CCPack --- //
            $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                ->select(DB::raw("'CCPack' as courierType"), 'ccpack.*', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                ->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                ->where('ccpack.delivery_boy', $id)
                ->where('ccpack.deleted', '0')
                ->where('invoices.total', '!=', '0.00');
            if (!empty($fromDate) && !empty($toDate))
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
            $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->orderBy('ccpack.id')
                ->get()->toArray();

            $allData = array_merge($upsFileAssignedToDeliveryBoy, $aeropostFileAssignedToDeliveryBoy, $ccpackFileAssignedToDeliveryBoy);

            $pdf = PDF::loadView('delivery-boy.print-cash-collection-all', ['data' => $allData, 'model' => $model, 'courierType' => 'UPS, Aeropost, CCPack', 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                'format' => 'A4-L',
                'orientation' => 'L'
            ]);
            $pdf_file = time() . '_cash_collections.pdf';
            $pdf_path = 'public/cashCollections/' . $pdf_file;
            $pdf->save($pdf_path);
            return url('/') . '/' . $pdf_path;
        } else {
            if ($courierType == 'UPS') {
                $UpsClientId = '';
                $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
                if (!empty($dataClient))
                    $UpsClientId = $dataClient->id;
                $upsFileAssignedToDeliveryBoy = DB::table('ups_details')
                    ->select('ups_details.*', 'c1.company_name as consigneeName', 'c2.company_name as shipperName',  'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                    ->leftJoin('invoices', 'invoices.ups_id', '=', 'ups_details.id')
                    ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                    ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                    ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                    ->where('ups_details.delivery_boy', $id)
                    ->where('ups_details.deleted', '0')
                    ->where('ups_details.fd', 0)
                    ->where('invoices.bill_to', '<>', $UpsClientId)
                    ->where('invoices.total', '!=', '0.00');
                if (!empty($fileStatus))
                    $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where('ups_details.ups_scan_status', $fileStatus);
                if (!empty($fromDate) && !empty($toDate))
                    $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->whereBetween('ups_details.delivery_boy_assigned_on', array($fromDate, $toDate));
                if (!empty($billingTerm))
                    $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->where($col, '1');
                $upsFileAssignedToDeliveryBoy = $upsFileAssignedToDeliveryBoy->orderBy('ups_details.id')
                    ->get();

                if ($submitButtonName == 'clsSubmit') {
                    return view('delivery-boy.db-ups-cash-collection-files', ['upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'model' => $model]);
                } else {
                    $pdf = PDF::loadView('delivery-boy.print-cash-collection-all', ['data' => $upsFileAssignedToDeliveryBoy, 'model' => $model, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                        'format' => 'A4-L',
                        'orientation' => 'L'
                    ]);
                    $pdf_file = time() . '_cash_collection_ups.pdf';
                    $pdf_path = 'public/cashCollections/' . $pdf_file;
                    $pdf->save($pdf_path);
                    return url('/') . '/' . $pdf_path;
                }
            } else if ($courierType == 'Aeropost') {
                $UpsClientId = '';
                $dataClient = DB::table('clients')->where('company_name', 'Aeropost USD')->first();
                if (!empty($dataClient))
                    $UpsClientId = $dataClient->id;
                $aeropostFileAssignedToDeliveryBoy = DB::table('aeropost')
                    ->select('aeropost.*', 'c1.company_name as consigneeName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                    ->leftJoin('invoices', 'invoices.aeropost_id', '=', 'aeropost.id')
                    ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                    ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                    ->where('aeropost.delivery_boy', $id)
                    ->where('aeropost.deleted', '0')
                    ->where('invoices.bill_to', '<>', $UpsClientId)
                    ->where('invoices.total', '!=', '0.00');
                if (!empty($fileStatus))
                    $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->where('aeropost.aeropost_scan_status', $fileStatus);
                if (!empty($fromDate) && !empty($toDate))
                    $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->whereBetween('aeropost.delivery_boy_assigned_on', array($fromDate, $toDate));
                $aeropostFileAssignedToDeliveryBoy = $aeropostFileAssignedToDeliveryBoy->orderBy('aeropost.id')
                    ->get();

                if ($submitButtonName == 'clsSubmit') {
                    return view('delivery-boy.db-aeropost-cash-collection-files', ['aeropostFileAssignedToDeliveryBoy' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model]);
                } else {
                    $pdf = PDF::loadView('delivery-boy.print-cash-collection-all', ['data' => $aeropostFileAssignedToDeliveryBoy, 'model' => $model, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                        'format' => 'A4-L',
                        'orientation' => 'L'
                    ]);
                    $pdf_file = time() . '_cash_collection_ups.pdf';
                    $pdf_path = 'public/cashCollections/' . $pdf_file;
                    $pdf->save($pdf_path);
                    return url('/') . '/' . $pdf_path;
                }
            } else if ($courierType == 'CCPack') {
                $ccpackFileAssignedToDeliveryBoy = DB::table('ccpack')
                    ->select('ccpack.*', 'c1.company_name as consigneeName',  'c2.company_name as shipperName', 'invoices.id as invoiceId', 'invoices.payment_status', DB::raw('invoices.bill_no as invoiceNumbers'), DB::raw('invoices.total as totalAmount'), DB::raw('invoices.credits as paidAmount'))
                    ->leftJoin('invoices', 'invoices.ccpack_id', '=', 'ccpack.id')
                    ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                    ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                    ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
                    ->where('ccpack.delivery_boy', $id)
                    ->where('ccpack.deleted', '0')
                    ->where('invoices.total', '!=', '0.00');
                if (!empty($fileStatus))
                    $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->where('ccpack.ccpack_scan_status', $fileStatus);
                if (!empty($fromDate) && !empty($toDate))
                    $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->whereBetween('ccpack.delivery_boy_assigned_on', array($fromDate, $toDate));
                $ccpackFileAssignedToDeliveryBoy = $ccpackFileAssignedToDeliveryBoy->orderBy('ccpack.id')
                    ->get();

                if ($submitButtonName == 'clsSubmit') {
                    return view('delivery-boy.db-ccpack-cash-collection-files', ['ccpackFileAssignedToDeliveryBoy' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model]);
                } else {
                    $pdf = PDF::loadView('delivery-boy.print-cash-collection-all', ['data' => $ccpackFileAssignedToDeliveryBoy, 'model' => $model, 'courierType' => $courierType, 'fromDate' => $fromDate, 'toDate' => $toDate], [], [
                        'format' => 'A4-L',
                        'orientation' => 'L'
                    ]);
                    $pdf_file = time() . '_cash_collection_ups.pdf';
                    $pdf_path = 'public/cashCollections/' . $pdf_file;
                    $pdf->save($pdf_path);
                    return url('/') . '/' . $pdf_path;
                }
            }
        }
    }


    public function deliveryboyshipmentnotdelivered(Request $request, $module = null, $deliveryBoyId = null)
    {
        if ($module == 'UPS')
            $model = new Ups;
        if ($module == 'Aeropost')
            $model = new Aeropost();
        if ($module == 'CCPack')
            $model = new ccpack();
        return view('delivery-boy.deliveryboyshipmentnotdelivered', ['model' => $model, 'module' => $module, 'deliveryBoyId' => $deliveryBoyId]);
    }

    public function deliveryboyshipmentdeliveredornot(Request $request)
    {
        $input = $request->all();
        $flagButton = $input['flagButton'];

        $module = $input['module'];
        $ids = explode(',', $input['ids']);
        if ($module == 'UPS') {
            $tblName = 'ups_details';
            $notesModuleId = 'ups_id';
            $columnName = 'ups_scan_status';
        } else if ($module == 'Aeropost') {
            $tblName = 'aeropost';
            $notesModuleId = 'aeropost_id';
            $columnName = 'aeropost_scan_status';
        } else if ($module == 'CCPack') {
            $tblName = 'ccpack';
            $notesModuleId = 'ccpack_id';
            $columnName = 'ccpack_scan_status';
        }

        if ($flagButton == 'delivered') {
            $data = DB::table($tblName)->whereIn('id', $ids)->update([$columnName => '6', 'warehouse_status' => '3', 'shipment_delivered_date' => date('Y-m-d')]);
            foreach ($ids as $k => $v) {
                $inputNotes['flag_note'] = 'R';
                $inputNotes[$notesModuleId] = $v;
                $inputNotes['notes'] = 'Delivered';
                $inputNotes['created_on'] = date('Y-m-d');
                $inputNotes['created_by'] = auth()->user()->id;
                VerificationInspectionNote::create($inputNotes);
            }
        } else {
            $returnReason = $input['reason_for_return'];
            $data = DB::table($tblName)->whereIn('id', $ids)->update(['reason_for_return' => $returnReason, $columnName => '7']);

            if (!empty($input['shipment_notes_for_return'])) {
                foreach ($ids as $k => $v) {
                    $inputNotes['flag_note'] = 'R';
                    $inputNotes[$notesModuleId] = $v;
                    $inputNotes['notes'] = $input['shipment_notes_for_return'];
                    $inputNotes['created_on'] = date('Y-m-d');
                    $inputNotes['created_by'] = auth()->user()->id;
                    VerificationInspectionNote::create($inputNotes);
                }
            }
        }
        Session::flash('flash_message', 'File status has been updated successfully');
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
