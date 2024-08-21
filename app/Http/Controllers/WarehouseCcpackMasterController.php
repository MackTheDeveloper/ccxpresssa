<?php

namespace App\Http\Controllers;

use App\CcpackMaster;
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

class WarehouseCcpackMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("warehouse-role.ccpack-master.index");
    }

    public function expandhousefilesforwarehouse(Request $request)
    {
        $masterUpsId = $_POST['masterCcpackId'];
        $rowId = $_POST['rowId'];

        $packageData = DB::table('ccpack')->where('master_ccpack_id', $masterUpsId)->orderBy('id','desc')->get();
        return view('warehouse-role.ccpack-master.expandhousefiles', ['packageData' => $packageData, 'rowId' => $rowId]);
    }

}
