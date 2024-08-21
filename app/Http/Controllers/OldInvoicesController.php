<?php

namespace App\Http\Controllers;

use App\Invoices;
use App\Clients;
use App\BillingItems;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use Illuminate\Support\Facades\DB;
use App\User;
use App\InvoiceItemDetails;
use App\Activities;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceDetailMail;
use PDF;
use App\Mail\sendCashierInvoiceMail;
use App\Cargo;
use App\Currency;
use Illuminate\Support\Facades\Storage;
use App\Admin;

class OldInvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_old_invoices'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $invoices = DB::table('invoices')->where('deleted', '0')
            ->where('flag_invoice', 'old')
            ->orderBy('id', 'desc')->get();
        return view("old-invoices.index", ['invoices' => $invoices]);
    }


    public function listbydatatableserverside(Request $request)
    {
        $permissionCargoInvoicesEdit = User::checkPermission(['update_old_invoices'], '', auth()->user()->id);
        $permissionCargoInvoicesDelete = User::checkPermission(['delete_old_invoices'], '', auth()->user()->id);
        $permissionCargoInvoicesCopy = User::checkPermission(['copy_old_invoices'], '', auth()->user()->id);

        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['invoices.id', '', 'invoices.date', 'bill_no', 'cargo.file_number', 'invoices.awb_no', 'c1.company_name', 'invoices.consignee_address', 'currency.code', 'total', 'credits', 'users.name', 'payment_status'];

        $total = Invoices::selectRaw('count(*) as total')
            ->where('flag_invoice', 'old');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('date', array($fromDate, $toDate));
        }
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('invoices')
            ->selectRaw('invoices.*')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->leftJoin('cargo', 'cargo.id', '=', 'invoices.cargo_id')
            ->leftJoin('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
            ->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
            ->leftJoin('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
            ->where('flag_invoice', 'old');
        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('date', array($fromDate, $toDate));
        }
        $filteredq = DB::table('invoices')
            ->leftJoin('clients as c1', 'c1.id', '=', 'invoices.bill_to')
            ->leftJoin('users', 'users.id', '=', 'invoices.created_by')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->leftJoin('cargo', 'cargo.id', '=', 'invoices.cargo_id')
            ->leftJoin('ups_details', 'ups_details.id', '=', 'invoices.ups_id')
            ->leftJoin('aeropost', 'aeropost.id', '=', 'invoices.aeropost_id')
            ->leftJoin('ccpack', 'ccpack.id', '=', 'invoices.ccpack_id')
            ->where('flag_invoice', 'old');
        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('date', array($fromDate, $toDate));
        }



        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%')
                    ->orWhere('cargo.file_number', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.file_number', 'like', '%' . $search . '%')
                    ->orWhere('aeropost.file_number', 'like', '%' . $search . '%')
                    ->orWhere('ccpack.file_number', 'like', '%' . $search . '%')
                    ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('invoices.date', 'like', '%' . $search . '%')
                    ->orWhere('bill_no', 'like', '%' . $search . '%')
                    ->orWhere('cargo.file_number', 'like', '%' . $search . '%')
                    ->orWhere('ups_details.file_number', 'like', '%' . $search . '%')
                    ->orWhere('aeropost.file_number', 'like', '%' . $search . '%')
                    ->orWhere('ccpack.file_number', 'like', '%' . $search . '%')
                    ->orWhere('invoices.awb_no', 'like', '%' . $search . '%')
                    ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                    ->orWhere('invoices.consignee_address', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('total', 'like', '%' . $search . '%')
                    ->orWhere('credits', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('users.name', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $dataBillingParty = app('App\Clients')->getClientData($items->bill_to);
            $dataCurrency = Currency::getData($items->currency);
            $dataUser = app('App\User')->getUserName($items->created_by);
            $flagModule = '';
            if (!empty($items->cargo_id)) {
                $fileData = app('App\Cargo')->getCargoData($items->cargo_id);
                $flagModule = 'Cargo';
                $delete =  url('invoices/delete', [$items->id]);
                $edit =  route('editinvoice', $items->id);
            }
            if (!empty($items->hawb_hbl_no)) {
                $fileData = app('App\Common')->getCommonAllModuleData('cargo', $items->hawb_hbl_no);
                $flagModule = 'cargoHouseFile';
                $delete =  route('deletehousefileinvoice', $items->id);
                $edit =  route('edithousefileinvoice', [$items->id, 'cargo']);
            } else if (!empty($items->ups_id)) {
                $fileData = app('App\Ups')->getUpsData($items->ups_id);
                $flagModule = 'Ups';
                $delete =  route('deleteupsinvoice', $items->id);
                $edit =  route('editupsinvoice', $items->id);
            } else if (!empty($items->aeropost_id)) {
                $fileData = app('App\Aeropost')->getAeropostData($items->aeropost_id);
                $flagModule = 'Aeropost';
                $delete =  route('deleteaeropostinvoice', $items->id);
                $edit =  route('editaeropostinvoice', $items->id);
            } else if (!empty($items->ccpack_id)) {
                $fileData = app('App\ccpack')->getccpackdetail($items->ccpack_id);
                $flagModule = 'CCPack';
                $delete =  route('deleteinvoice', $items->id);
                $edit =  route('editccpackinvoice', $items->id);
            }
            if (empty($fileData))
                continue;

            $action = '<div class="dropdown">';



            $action .= '<a title="View & Print"  target="_blank" href="' . route('viewandprintcargoinvoice', $items->id) . '"><i class="fa fa-print"></i></a>&nbsp; &nbsp;';

            if ($items->deleted == '0') {
                if ($permissionCargoInvoicesEdit && $fileData->file_close != 1) {
                    $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
                }

                if ($permissionCargoInvoicesDelete && checkloggedinuserdata() == 'Other') {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }

                $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

                /* if ($permissionCargoInvoicesCopy) {
                    if($flagModule == 'Cargo')
                        $action .= '<li><a href="' . route('copyinvoice', [$items->id, 'fromlisting']) . '">Copy Invoice</a></li>';
                    else if($flagModule == 'Ups')
                        $action .= '<li><a href="' . route('copyupsinvoice', [$items->id, 'fromlisting']) . '">Copy Invoice</a></li>';
                    else if($flagModule == 'Aeropost')
                        $action .= '<li><a href="' . route('copyaeropostinvoice', [$items->id, 'fromlisting']) . '">Copy Invoice</a></li>';
                    else if($flagModule == 'CCPack')
                        $action .= '<li><a href="' . route('copyccpackinvoice', [$items->id, 'fromlisting']) . '">Copy Invoice</a></li>';
                } */

                if ($items->payment_status == 'Pending' || $items->payment_status == 'Partial') {
                    if ($flagModule == 'Cargo') {
                        $action .= '<li><a href="' . route('addinvoicepayment', [$items->cargo_id, $items->id, 0]) . '">Add Payment</a></li>';
                        $action .= '<li><a href="' . route('addinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                    } else if ($flagModule == 'cargoHouseFile') {
                        $action .= '<li><a href="' . route('addinvoicepayment', [$items->hawb_hbl_no, $items->id, 0, '0', 'housefile']) . '">Add Payment</a></li>';
                        $action .= '<li><a href="' . route('addinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                    } else if ($flagModule == 'Ups') {
                        $action .= '<li><a href="' . route('addupsinvoicepayment', [$items->ups_id, $items->id, 0]) . '">Add Payment</a></li>';
                        $action .= '<li><a href="' . route('addupsinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                    } else if ($flagModule == 'Aeropost') {
                        $action .= '<li><a href="' . route('addaeropostinvoicepayment', [$items->aeropost_id, $items->id, 0]) . '">Add Payment</a></li>';
                        $action .= '<li><a href="' . route('addaeropostinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                    } else if ($flagModule == 'CCPack') {
                        $action .= '<li><a href="' . route('addccpackinvoicepayment', [$items->ccpack_id, $items->id, 0]) . '">Add Payment</a></li>';
                        $action .= '<li><a href="' . route('addccpackinvoicepayment', [0, 0, $items->bill_to]) . '">Add Bulk Payment</a></li>';
                    }
                } else {
                    if ($flagModule == 'Cargo') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'cargo']) . '">Payment Receipt</i></a>
                    </li>';
                    } else if ($flagModule == 'cargoHouseFile') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'housefile']) . '">Payment Receipt</i></a>
                    </li>';
                    } else if ($flagModule == 'Ups') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'ups']) . '">Payment Receipt</i></a>
                    </li>';
                    } else if ($flagModule == 'Aeropost') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'aeropost']) . '">Payment Receipt</i></a>
                    </li>';
                    } else if ($flagModule == 'CCPack') {
                        $action .= '<li><a title="Print Receipt"  target="_blank" href="' . route('printreceiptofinvoicepayment', [$items->id, 'invoice', 'ccpack']) . '">Payment Receipt</i></a>
                    </li>';
                    }
                }
                $action .= '</ul>';
            }
            $action .= '</div>';

            $data[] = [$items->id, $flagModule, date('d-m-Y', strtotime($items->date)), $items->bill_no, !empty($fileData) ? $fileData->file_number : '-', $items->awb_no, !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-", $items->consignee_address, !empty($dataCurrency->code) ? $dataCurrency->code : "-", number_format($items->total, 2), number_format($items->credits, 2), !empty($dataUser->name) ? $dataUser->name : "-", $items->payment_status, $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    public function checkuniquebillnoforoldinvoice()
    {
        $value = $_POST['value'];
        $id = $_POST['id'];
        if (!empty($id)) {
            $data = DB::table('invoices')->where('deleted', '0')->where('bill_no', $value)->where('id', '<>', $id)->count();
        } else {
            $data = DB::table('invoices')->where('deleted', '0')->where('bill_no', $value)->count();
        }

        if ($data)
            return 1;
        else
            return 0;
    }
}
