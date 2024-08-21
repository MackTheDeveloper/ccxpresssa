<?php

namespace App\Http\Controllers;

use App\AeropostMaster;
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

class WarehouseAeropostMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("warehouse-role.aeropost-master.index");
    }

    public function expandhousefilesforwarehouse(Request $request)
    {
        $masterUpsId = $_POST['masterAeropostId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('aeropost')->where('master_aeropost_id', $masterUpsId)->orderBy('id','desc')->get();
        return view('warehouse-role.aeropost-master.expandhousefiles', ['packageData' => $packageData, 'rowId' => $rowId]);
    }

}
