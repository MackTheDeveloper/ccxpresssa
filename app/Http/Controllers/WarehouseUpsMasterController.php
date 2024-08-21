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

class WarehouseUpsMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("warehouse-role.ups-master.index");
    }

    public function expandhousefilesforwarehouse(Request $request)
    {
        $masterUpsId = $_POST['masterUpsId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('ups_details')->where('master_ups_id', $masterUpsId)->get();
        return view('warehouse-role.ups-master.expandhousefiles', ['packageData' => $packageData, 'rowId' => $rowId, 'masterUpsId' => $masterUpsId]);
    }

}
