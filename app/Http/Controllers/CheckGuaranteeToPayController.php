<?php

namespace App\Http\Controllers;

use App\Cargo;
use App\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
use App\CheckGuaranteeToPay;
use App\InvoiceItemDetails;
use App\Invoices;

class CheckGuaranteeToPayController extends Controller
{
    public function index()
    {
        return view("check-guarantee-to-pay.index");
    }

    public function listbydatatableserverside(Request $request)
    {
        $permissionEdit = User::checkPermission(['update_guarantee_check'], '', auth()->user()->id);
        $permissionDelete = User::checkPermission(['delete_guarantee_check'], '', auth()->user()->id);

        $req = $request->all();
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $containerReturn = $req['containerReturn'];
        $containerDelivered = $req['containerDelivered'];
        $checkReturn = $req['checkReturn'];
        $billedStatus = $req['billedStatus'];
        $checkType = $req['checkType'];
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['check_guarantee_to_pay.id', 'check_guarantee_to_pay.master_cargo_id', '', 'check_guarantee_to_pay.file_number', 'check_guarantee_to_pay.date', 'check_guarantee_to_pay.check_type', 'check_guarantee_to_pay.invoice_number', 'check_guarantee_to_pay.check_number', 'check_guarantee_to_pay.detention_days_allowed', 'check_guarantee_to_pay.delivered_date', 'check_guarantee_to_pay.return_date', 'check_guarantee_to_pay.check_return', 'check_guarantee_to_pay.amount', 'check_guarantee_to_pay.amount_balance', '', '', '', ''];

        $total = CheckGuaranteeToPay::selectRaw('count(*) as total')
            ->where('check_guarantee_to_pay.deleted', '0');
        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('check_guarantee_to_pay.date', array($fromDate, $toDate));
        }
        if (!empty($checkReturn) || $checkReturn == '0') {
            $total = $total->where('check_guarantee_to_pay.check_return', $checkReturn);
        }
        if (!empty($billedStatus) || $billedStatus == '0') {
            $total = $total->where('check_guarantee_to_pay.billed', $billedStatus);
        }
        if (!empty($containerReturn) || $containerReturn == '0') {
            if ($containerReturn == '1')
                $total = $total->whereNotNull('check_guarantee_to_pay.return_date');
            else
                $total = $total->whereNull('check_guarantee_to_pay.return_date');
        }
        if (!empty($containerDelivered) || $containerDelivered == '0') {
            if ($containerDelivered == '1')
                $total = $total->whereNotNull('check_guarantee_to_pay.delivered_date');
            else
                $total = $total->whereNull('check_guarantee_to_pay.delivered_date');
        }
        if (!empty($checkType)) {
            $total = $total->where('check_guarantee_to_pay.check_type', $checkType);
        }

        $query = DB::table('check_guarantee_to_pay')
            ->selectRaw(DB::raw('check_guarantee_to_pay.id,check_guarantee_to_pay.master_cargo_id as moduleId,check_guarantee_to_pay.file_number,check_guarantee_to_pay.date,check_guarantee_to_pay.check_number,check_guarantee_to_pay.check_type, check_guarantee_to_pay.invoice_number,check_guarantee_to_pay.detention_days_allowed,check_guarantee_to_pay.delivered_date,check_guarantee_to_pay.return_date,check_guarantee_to_pay.check_return,check_guarantee_to_pay.total_cost_container,check_guarantee_to_pay.total_cost_chassis,check_guarantee_to_pay.amount,check_guarantee_to_pay.amount_balance,check_guarantee_to_pay.approved'))
            ->where('check_guarantee_to_pay.deleted', '0');

        if (!empty($fromDate) && !empty($toDate)) {
            $query = $query->whereBetween('check_guarantee_to_pay.date', array($fromDate, $toDate));
        }
        if (!empty($checkReturn) || $checkReturn == '0') {
            $query = $query->where('check_guarantee_to_pay.check_return', $checkReturn);
        }
        if (!empty($billedStatus) || $billedStatus == '0') {
            $query = $query->where('check_guarantee_to_pay.billed', $billedStatus);
        }
        if (!empty($containerReturn) || $containerReturn == '0') {
            if ($containerReturn == '1')
                $query = $query->whereNotNull('check_guarantee_to_pay.return_date');
            else
                $query = $query->whereNull('check_guarantee_to_pay.return_date');
        }
        if (!empty($containerDelivered) || $containerDelivered == '0') {
            if ($containerDelivered == '1')
                $query = $query->whereNotNull('check_guarantee_to_pay.delivered_date');
            else
                $query = $query->whereNull('check_guarantee_to_pay.delivered_date');
        }
        if (!empty($checkType)) {
            $query = $query->where('check_guarantee_to_pay.check_type', $checkType);
        }

        $filteredq = DB::table('check_guarantee_to_pay')
            ->where('check_guarantee_to_pay.deleted', '0');

        if (!empty($fromDate) && !empty($toDate)) {
            $filteredq = $filteredq->whereBetween('check_guarantee_to_pay.date', array($fromDate, $toDate));
        }
        if (!empty($checkReturn) || $checkReturn == '0') {
            $filteredq = $filteredq->where('check_guarantee_to_pay.check_return', $checkReturn);
        }
        if (!empty($billedStatus) || $billedStatus == '0') {
            $filteredq = $filteredq->where('check_guarantee_to_pay.billed', $billedStatus);
        }
        if (!empty($containerReturn) || $containerReturn == '0') {
            if ($containerReturn == '1')
                $filteredq = $filteredq->whereNotNull('check_guarantee_to_pay.return_date');
            else
                $filteredq = $filteredq->whereNull('check_guarantee_to_pay.return_date');
        }
        if (!empty($containerDelivered) || $containerDelivered == '0') {
            if ($containerDelivered == '1')
                $filteredq = $filteredq->whereNotNull('check_guarantee_to_pay.delivered_date');
            else
                $filteredq = $filteredq->whereNull('check_guarantee_to_pay.delivered_date');
        }
        if (!empty($checkType)) {
            $filteredq = $filteredq->where('check_guarantee_to_pay.check_type', $checkType);
        }

        $total = $total->first();
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('check_guarantee_to_pay.file_number', 'like', '%' . $search . '%')
                    ->orWhere('check_guarantee_to_pay.check_number', 'like', '%' . $search . '%')
                    ->orWhere('check_guarantee_to_pay.amount', 'like', '%' . $search . '%')
                    ->orWhere('check_guarantee_to_pay.invoice_number', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('check_guarantee_to_pay.file_number', 'like', '%' . $search . '%')
                    ->orWhere('check_guarantee_to_pay.check_number', 'like', '%' . $search . '%')
                    ->orWhere('check_guarantee_to_pay.amount', 'like', '%' . $search . '%')
                    ->orWhere('check_guarantee_to_pay.invoice_number', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $otherOption1 = '';

            $moduleId = $value->moduleId;
            $dataV = DB::table('cargo')->where('id', $value->moduleId)->first();
            $otherOption1 = $dataV->cargo_operation_type;

            $totalCost = $value->total_cost_container + $value->total_cost_chassis;

            $billedAmount = CheckGuaranteeToPay::getBilledAmountOfFile($moduleId);

            $action = '<div class="dropdown">';
            $edit = route('editCheckGuarantee', [$value->id]);
            $delete =  route('destroyCheckGuarantee', [$value->id]);
            //$action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
            if ($permissionEdit) {
                $action .= '<button style="border:none;background: none;color:#3097D1" id="editNewCheck" value="' . $edit . '" type="button" class="editNewCheck" data-module="Cost Item"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
            }
            if ($permissionDelete) {
                $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
            }
            $action .= '</div>';

            if ($value->approved == 0) {
                $checkBoxes = '<input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="' . $value->id . '" value="' . $value->id . '" />';
            } else {
                $checkBoxes = '';
            }


            $data[] = [$checkBoxes, $value->id, $moduleId, $otherOption1, $value->file_number, !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '-', $value->check_type == 1 ? 'DECSA' : 'Veconinter', $value->invoice_number, $value->check_number, $value->detention_days_allowed, !empty($value->delivered_date) ? date('d-m-Y', strtotime($value->delivered_date)) : '-', !empty($value->return_date) ? date('d-m-Y', strtotime($value->return_date)) : '-', $value->check_return == 1 ? 'Yes' : 'No', $value->amount, $value->amount_balance, $totalCost, $billedAmount, $billedAmount - $totalCost, $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function add()
    {
        $model = new CheckGuaranteeToPay;
        $fileNumber = DB::table('cargo')->whereNull('file_close')->whereNotNull('billing_party')->where('deleted', 0)->get()->pluck('file_number', 'id');
        return view('check-guarantee-to-pay.add', ['fileNumber' => $fileNumber]);
    }

    public function storecheck(Request $request)
    {
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $input['date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;
        $inserted = CheckGuaranteeToPay::create($input);

        // Generate invoice for the DECSA
       /*  $modelCargo = Cargo::find($inserted->master_cargo_id);
        if ($inserted->check_type == 1)
            $dataBillingParty = DB::table('clients')->where('company_name', 'DECSA/ Chèque de garantie USD')->first();
        else
            $dataBillingParty = DB::table('clients')->where('company_name', 'Veconinter USD')->first();

        $dataConsignee = DB::table('clients')->where('id', $modelCargo->consignee_name)->first();
        $dataShipper = DB::table('clients')->where('id', $modelCargo->shipper_name)->first();
        if (!empty($dataBillingParty)) {
            $invoiceInput['type_flag'] = $modelCargo->cargo_operation_type == '1' ? 'IMPORT' : 'EXPORT';;
            $invoiceInput['cargo_id'] = $modelCargo->id;
            $invoiceInput['bill_to'] = $dataBillingParty->id;
            $invoiceInput['currency'] = $dataBillingParty->currency;
            $invoiceInput['date'] = $inserted->date;
            $invoiceInput['email'] = $dataBillingParty->email;
            $invoiceInput['telephone'] = $dataBillingParty->phone_number;
            $invoiceInput['consignee_address'] = $dataConsignee->company_name;
            $invoiceInput['shipper'] = $dataShipper->company_name;
            $invoiceInput['file_no'] = $modelCargo->file_number;
            $invoiceInput['awb_no'] = $modelCargo->awb_bl_no;
            //$invoiceInput['total'] = $input['rental_cost'] * $input['contract_months'];
            $invoiceInput['total'] = $inserted->amount;
            $invoiceInput['sub_total'] = $invoiceInput['total'];
            $invoiceInput['balance_of'] = $invoiceInput['total'];
            $invoiceInput['payment_status'] = 'Pending';

            $model = new Invoices();
            $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->where('deleted', '0')->whereNull('flag_invoice')->first();
            if (empty($getLastInvoice)) {
                $model->bill_no = 'CA-5001';
            } else {
                $ab = 'CA-';
                $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                $model->bill_no = $ab;
            }

            $invoiceInput['bill_no'] = $model->bill_no;
            $invoiceInput['created_by'] = auth()->user()->id;
            $invoiceInput['created_at'] = date('Y-m-d h:i:s');
            $dataInvoices = Invoices::create($invoiceInput);

            $dataBillingItems = DB::table('billing_items')->select('id', 'item_code', 'description')->where('item_code', '1037/ Garantie (DECSA)')->first();

            $dataInvoiceItems['invoice_id'] = $dataInvoices->id;
            $dataInvoiceItems['fees_name'] = !empty($dataBillingItems) ? $dataBillingItems->id : '';
            $dataInvoiceItems['item_code'] = !empty($dataBillingItems) ? $dataBillingItems->item_code : '';
            $dataInvoiceItems['fees_name_desc'] = !empty($dataBillingItems) ? $dataBillingItems->description : '';
            $dataInvoiceItems['quantity'] = 1.00;
            $dataInvoiceItems['unit_price'] = $inserted->amount;
            $dataInvoiceItems['total_of_items'] = $dataInvoiceItems['quantity'] * $dataInvoiceItems['unit_price'];
            InvoiceItemDetails::create($dataInvoiceItems);
        } */

        if (checkloggedinuserdata() == 'Agent') {
            return redirect('cargo/createagentexpenses/cargo/0/0/' . $inserted->id);
        } else {
            Session::flash('flash_message', 'Record has been created successfully');
            return redirect('check-guarantee');
        }
        // return redirect('check-guarantee');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = DB::table('check_guarantee_to_pay')->where('id', $id)->first();
        //$balanceOnCheck = CheckGuaranteeToPay::getBilledAmountOfFile($model->master_cargo_id);
        //$model->amount_balance = $balanceOnCheck - ($model->total_cost_container + $model->total_cost_chassis);
        return view('check-guarantee-to-pay.edit', ['model' => $model]);
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $input['delivered_date'] = !empty($input['delivered_date']) ? date('Y-m-d', strtotime($input['delivered_date'])) : null;
        $input['return_date'] = !empty($input['return_date']) ? date('Y-m-d', strtotime($input['return_date'])) : null;
        $model = CheckGuaranteeToPay::find($input['id']);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $model->update($input);
        Session::flash('flash_message_check', 'Record has been updated successfully');
        return redirect('check-guarantee');
    }

    public function getBillingAmount(Request $request)
    {
        return CheckGuaranteeToPay::getBilledAmountOfFile($_POST['master_cargo_id']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($moduleFlag = '', $moduleId = '', $id = '')
    {
        if ($moduleFlag == 'masterCargo')
            $dataC = DB::table('cargo')->where('id', $moduleId)->first();

        if (!empty($id))
            $model = DB::table('check_guarantee_to_pay')->where('id', $id)->first();
        else
            $model = new CheckGuaranteeToPay;

        return view('check-guarantee-to-pay.form', ['model' => $model, 'moduleFlag' => $moduleFlag, 'moduleId' => $moduleId, 'dataC' => $dataC]);
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
        if ($input['actionName'] == 'store') {
            $input['created_at'] = gmdate("Y-m-d H:i:s");
            $input['date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;
            CheckGuaranteeToPay::create($input);
            Session::flash('flash_message_check', 'Record has been created successfully');
        } else {
            $model = CheckGuaranteeToPay::find($input['id']);
            $input['date'] = !empty($input['date']) ? date('Y-m-d', strtotime($input['date'])) : null;
            $request['updated_at'] = gmdate("Y-m-d H:i:s");
            $model->update($input);
            Session::flash('flash_message_check', 'Record has been updated successfully');
        }
        return 'true';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Country  $country
     * @return \Illuminate\Http\Response
     */
    public function destroy(CheckGuaranteeToPay $checkGuaranteeToPay, $id)
    {
        $model = CheckGuaranteeToPay::where('id', $id)->update(['deleted' => '1', 'deleted_at' => gmdate("Y-m-d H:i:s")]);
    }

    public function approve()
    {
        $ids = explode(',', $_POST['ids']);
        DB::table('check_guarantee_to_pay')->whereIn('id', $ids)->update([
            'approved' => 1,
        ]);
        Session::flash('flash_message', 'Record has been change to Approved.');
        return redirect()->route('check-guarantee');
    }
}
