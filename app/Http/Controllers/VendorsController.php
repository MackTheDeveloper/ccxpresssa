<?php

namespace App\Http\Controllers;

use App\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use App\Admin;
use App\Currency;
use App\ClientContact;
use Response;

class VendorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $checkPermission = User::checkPermission(['listing_vendors'],'',auth()->user()->id);
        // if(!$checkPermission)
        //     return redirect('/home');

        //$vendors = DB::table('vendors')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("vendors.index");
    }

    public function listvendors(Request $request)
    {
        $permissionEdit = User::checkPermission(['update_vendors'], '', auth()->user()->id);
        $permissionDelete = User::checkPermission(['delete_vendors'], '', auth()->user()->id);

        $req = $request->all();
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        $orderby = ['vendors.id', 'company_name', 'currency.code', 'company_phone', 'email', 'vendor_payment_terms.title'];

        $total = Vendors::selectRaw('count(*) as total')
            ->where('vendors.deleted', '0');
        $total = $total->first();
        $totalfiltered = $total->total;

        $query = DB::table('vendors')
            ->selectRaw('vendors.id,vendors.company_name,currency.code,vendors.company_phone,vendors.email,vendor_payment_terms.title as paymentTerm')
            ->leftJoin('vendor_payment_terms', 'vendor_payment_terms.id', '=', 'vendors.payment_term')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->where('vendors.deleted', '0');

        $filteredq = DB::table('vendors')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->leftJoin('vendor_payment_terms', 'vendor_payment_terms.id', '=', 'vendors.payment_term')
            ->where('vendors.deleted', '0');

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('company_phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('vendor_payment_terms.title', 'like', '%' . $search . '%');
            });
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('currency.code', 'like', '%' . $search . '%')
                    ->orWhere('company_phone', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('vendor_payment_terms.title', 'like', '%' . $search . '%');
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $items) {
            $checkVendor = Vendors::checkExistingCurrencyVendor($items->company_name);

            $action = '<div class="dropdown">';

            $delete =  route('deletevendor', $items->id);
            $edit =  route('editvendor', $items->id);

            if ($permissionEdit) {
                $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
            }

            if ($permissionDelete) {
                $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
            }
            $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

            if ($checkVendor == 0) {
                $action .= '<li><a class="copyvendor" data-userid="' . $items->id . '" data-currency="' . $items->code . '" href="javascript:void(0)">Copy Vendor</a></li>';
            }
            $action .= '</ul>';
            $action .= '</div>';

            $data[] = [$items->id, $items->company_name, $items->code, $items->company_phone, $items->email, !empty($items->paymentTerm) ?$items->paymentTerm : 'Not Set', $action];
        }
        $json_data = array(
            "draw"            => intval($_REQUEST['draw']),
            "recordsTotal"    => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data"            => $data
        );
        return Response::json($json_data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_vendors'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Vendors;
        $currency = DB::table('currency')->where('deleted', '0')->where('status', '1')->pluck('code', 'id')->toArray();
        $vendorType = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where(function ($query) {
                $query->where('cashcredit_account_type.name', 'Vendor')
                    ->orWhere('cashcredit_account_type.name', 'Fournisseurs');
            })
            ->where('cashcredit_detail_type.deleted', 0)
            ->pluck('name', 'id');
        $paymentTerms = DB::table('vendor_payment_terms')->select(['id', 'title'])->where('deleted', 0)->where('status', 1)->pluck('title', 'id');
        return view('vendors.form', ['model' => $model, 'vendorType' => $vendorType, 'currency' => $currency, 'paymentTerms' => $paymentTerms]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        session_start();

        $input = $request->all();
        //pre($input);
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $currencyData = Currency::getData($input['currency']);
        $input['company_name'] = $input['company_name'] . ' ' . $currencyData->code . ' (Vendor)';
        $input['currency_code'] = $currencyData->code;
        $model = Vendors::create($input);
        Session::flash('flash_message', 'Record has been created successfully');

        $input['clientContact']['name'] = array_values($input['clientContact']['name']);
        $input['clientContact']['personal_contact'] = array_values($input['clientContact']['personal_contact']);
        $input['clientContact']['cell_number'] = array_values($input['clientContact']['cell_number']);
        $input['clientContact']['direct_line'] = array_values($input['clientContact']['direct_line']);
        $input['clientContact']['work'] = array_values($input['clientContact']['work']);
        $input['clientContact']['email'] = array_values($input['clientContact']['email']);

        $countContacts = count($input['clientContact']['name']);
        if ($countContacts > 0) {
            $contactsData = $input['clientContact'];
            for ($i = 0; $i < $countContacts; $i++) {
                $modelContacts = new ClientContact();
                $modelContacts->vendor_id = $model->id;
                $modelContacts->company_name = $input['company_name'];
                $modelContacts->name = $contactsData['name'][$i];
                $modelContacts->personal_contact = $contactsData['personal_contact'][$i];
                $modelContacts->cell_number = $contactsData['cell_number'][$i];
                $modelContacts->direct_line = $contactsData['direct_line'][$i];
                $modelContacts->work = $contactsData['work'][$i];
                $modelContacts->email = $contactsData['email'][$i];
                $modelContacts->save();
            }
        }

        // Store vendor to QB
        // if(isset($_SESSION['sessionAccessToken'])){
        //     $modelAdmin = new Admin;
        //     $modelAdmin->qbApiCall('vendor',$model);
        // }
        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '1';
            $fData['flagModule'] = 'vendor';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
        return redirect('vendors');
    }


    public function storenewitem(Request $request)
    {
        session_start();
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $currencyData = Currency::getData($input['currency']);
        $input['company_name'] = $input['company_name'] . ' ' . $currencyData->code;
        $input['currency_code'] = $currencyData->code;
        $model = Vendors::create($input);

        $input['clientContact']['name'] = array_values($input['clientContact']['name']);
        $input['clientContact']['personal_contact'] = array_values($input['clientContact']['personal_contact']);
        $input['clientContact']['cell_number'] = array_values($input['clientContact']['cell_number']);
        $input['clientContact']['direct_line'] = array_values($input['clientContact']['direct_line']);
        $input['clientContact']['work'] = array_values($input['clientContact']['work']);
        $input['clientContact']['email'] = array_values($input['clientContact']['email']);

        $countContacts = count($input['clientContact']['name']);
        if ($countContacts > 0) {
            $contactsData = $input['clientContact'];
            for ($i = 0; $i < $countContacts; $i++) {
                $modelContacts = new ClientContact();
                $modelContacts->vendor_id = $model->id;
                $modelContacts->company_name = $input['company_name'];
                $modelContacts->name = $contactsData['name'][$i];
                $modelContacts->personal_contact = $contactsData['personal_contact'][$i];
                $modelContacts->cell_number = $contactsData['cell_number'][$i];
                $modelContacts->direct_line = $contactsData['direct_line'][$i];
                $modelContacts->work = $contactsData['work'][$i];
                $modelContacts->email = $contactsData['email'][$i];
                $modelContacts->save();
            }
        }

        // Store vendor to QB
        // if(isset($_SESSION['sessionAccessToken'])){
        //     $modelAdmin = new Admin;
        //     $modelAdmin->qbApiCall('vendor',$model);
        // }
        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '1';
            $fData['flagModule'] = 'vendor';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }


        return 'true';
    }

    public function getvendordropdowndataaftersubmit()
    {
        $dataCost = DB::table('vendors')
            ->select(DB::raw("id,company_name,currency_code"))
            ->orderBy('id', 'desc')->where('deleted', 0)->where('status', 1)->get();
        $dt = '';
        foreach ($dataCost as $key => $value) {

            $dt .=  '<option value="' . $value->id . '">' . $value->company_name . ' - ' . $value->currency_code . '</option>';
        }
        return $dt;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Vendors  $vendors
     * @return \Illuminate\Http\Response
     */
    public function show(Vendors $vendors)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Vendors  $vendors
     * @return \Illuminate\Http\Response
     */
    public function edit(Vendors $vendors, $id)
    {
        $checkPermission = User::checkPermission(['update_vendors'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');
        $currency = DB::table('currency')->where('deleted', '0')->where('status', '1')->pluck('code', 'id')->toArray();
        $model = DB::table('vendors')->where('id', $id)->first();
        $vendorType = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where(function ($query) {
                $query->where('cashcredit_account_type.name', 'Vendor')
                    ->orWhere('cashcredit_account_type.name', 'Fournisseurs');
            })
            ->where('cashcredit_detail_type.deleted', 0)
            ->pluck('name', 'id');

        $dataContacts  = DB::table('client_contact')->where('vendor_id', $id)->get();
        $dataContacts = json_decode(json_encode($dataContacts));
        $paymentTerms = DB::table('vendor_payment_terms')->select(['id', 'title'])->where('deleted', 0)->where('status', 1)->pluck('title', 'id');
        return view("vendors.form", ['model' => $model, 'vendorType' => $vendorType, 'currency' => $currency, 'dataContacts' => $dataContacts, 'paymentTerms' => $paymentTerms]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vendors  $vendors
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vendors $vendors, $id)
    {
        session_start();

        $model = Vendors::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $currencyData = Currency::getData($input['currency']);
        $input['currency_code'] = $currencyData->code;
        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);

        $input['clientContact']['name'] = array_values($input['clientContact']['name']);
        $input['clientContact']['personal_contact'] = array_values($input['clientContact']['personal_contact']);
        $input['clientContact']['cell_number'] = array_values($input['clientContact']['cell_number']);
        $input['clientContact']['direct_line'] = array_values($input['clientContact']['direct_line']);
        $input['clientContact']['work'] = array_values($input['clientContact']['work']);
        $input['clientContact']['email'] = array_values($input['clientContact']['email']);

        $countContacts = count($input['clientContact']['name']);
        if ($countContacts > 0) {
            // Get copy client data if exist
            $companyName = $model->company_name;
            $findStr = '';
            if (strpos($companyName, 'USD') !== false) {
                $findStr = str_replace('USD', 'HTG', $companyName);
            } else if (strpos($companyName, 'HTG') !== false) {
                $findStr = str_replace('HTG', 'USD', $companyName);
            }

            $anotherCurrencyClient = Vendors::where('company_name', $findStr)->first();

            ClientContact::where('vendor_id', $id)->delete();
            if (!empty($anotherCurrencyClient) && isset($request->copycontacts))
                ClientContact::where('vendor_id', $anotherCurrencyClient->id)->delete();

            $contactsData = $input['clientContact'];
            for ($i = 0; $i < $countContacts; $i++) {
                $modelContacts = new ClientContact();
                $modelContacts->vendor_id = $model->id;
                $modelContacts->company_name = $input['company_name'];
                $modelContacts->name = $contactsData['name'][$i];
                $modelContacts->personal_contact = $contactsData['personal_contact'][$i];
                $modelContacts->cell_number = $contactsData['cell_number'][$i];
                $modelContacts->direct_line = $contactsData['direct_line'][$i];
                $modelContacts->work = $contactsData['work'][$i];
                $modelContacts->email = $contactsData['email'][$i];
                $modelContacts->save();
            }

            if (!empty($anotherCurrencyClient) && isset($request->copycontacts)) {
                $modelContactDetails = DB::table('client_contact')->where('vendor_id', $id)->get();
                foreach ($modelContactDetails as $key => $value) {
                    $contactDetailModel = ClientContact::find($value->id);
                    $contactDetailModel->vendor_id = $anotherCurrencyClient->id;
                    $contactDetailModel->company_name = $anotherCurrencyClient->company_name;
                    $newContactDetailModel = $contactDetailModel->replicate();
                    $newContactDetailModel->push();
                }
            }
        }

        Session::flash('flash_message', 'Record has been updated successfully');

        // Update vendor to QB
        // if(isset($_SESSION['sessionAccessToken'])){
        //     $modelAdmin = new Admin;
        //     $modelAdmin->qbApiCall('updateVendor',$model);
        // } 

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '1';
            $fData['flagModule'] = 'updateVendor';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        return redirect('vendors');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Vendors  $vendors
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vendors $vendor, $id)
    {
        session_start();
        $model = Vendors::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);

        // Delete vendor to QB
        // if(isset($_SESSION['sessionAccessToken'])){
        //     $modelAdmin = new Admin;
        //     $modelAdmin->qbApiCall('deleteVendor',$model);
        // } 
        if (isset($_SESSION['sessionAccessToken'])) {
            //pre('test');
            $fData['id'] = $id;
            $fData['module'] = '1';
            $fData['flagModule'] = 'deleteVendor';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));

            $urlAction = url('call/qb?model=' . $newModel);


            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function checkunique(Request $request)
    {
        $id = $request->get("id");
        //$value = $request->get("value") . ' (Vendor)';
        $value = $request->get("value");
        if (!empty('id')) {
            $dataVendor = DB::table('vendors')->where('company_name', $value)->where('id', '<>', $id)->where('deleted', '0')->count();
            $dataClient = DB::table('clients')->where('deleted','0')->where('client_flag', 'B')->where('company_name',$value)->count();
        } else {
            $dataClient = DB::table('clients')->where('deleted','0')->where('client_flag', 'B')->where('company_name',$value)->count();
            $dataVendor = DB::table('vendors')->where('deleted', '0')->where('company_name', $value)->count();
        }

        if($dataClient || $dataVendor){
        //if ($dataVendor) {
            return 1;
        } else {
            return 0;
        }
    }

    public function copyvendor()
    {
        $id = $_POST['userId'];
        $newCurrency = $_POST['newCurrency'];


        $model = Vendors::find($id);
        $oldCurrencyData = Currency::getData($model->currency);
        $currencyData = Currency::getDataUsingCode($newCurrency);
        $model->currency = $currencyData->id;
        $model->currency_code = $currencyData->code;
        $model->company_name = rtrim(str_replace($oldCurrencyData->code, $currencyData->code, $model->company_name));
        $newModel = $model->replicate();
        $newModel->push();

        $modelContactDetails = DB::table('client_contact')->where('vendor_id', $id)->get();
        foreach ($modelContactDetails as $key => $value) {
            $contactDetailModel = ClientContact::find($value->id);
            $contactDetailModel->vendor_id = $newModel->id;
            $contactDetailModel->company_name = $newModel->company_name;
            $newContactDetailModel = $contactDetailModel->replicate();
            $newContactDetailModel->push();
        }

        Session::flash('flash_message', 'Vendor has been copied successfully');
    }

    public function getvendordata()
    {
        $vendorID = $_POST['vendor'];
        $dataVendor = DB::table('vendors')->where('id', $vendorID)->first();
        return json_encode($dataVendor);
    }
}
