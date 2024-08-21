<?php

namespace App\Http\Controllers;

use App\Activities;
use App\Admin;
use App\ClientContact;
use App\Clients;
use App\Currency;
use App\Mail\RegistrationMail;
use App\User;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Response;
use Session;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($flag = null)
    {
        $checkPermission = User::checkPermission(['listing_clients'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        return view("clients.index", ['flag' => $flag]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($flag = null)
    {
        $checkPermission = User::checkPermission(['add_clients'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = new Clients;

        //$categories = DB::table('cashcredit_detail_type')->select(['id','name'])->where('deleted',0)->where('status',1)->where('account_type_id',5)->pluck('name', 'id');
        $categories = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where(function ($query) {
                $query->where('cashcredit_account_type.name', 'Client')
                    ->orWhere('cashcredit_account_type.name', 'Clients cash')
                    ->orWhere('cashcredit_account_type.name', 'Client à crédits');
            })
            ->where('cashcredit_detail_type.deleted', 0)
            ->pluck('name', 'id');
        $categories = json_decode($categories, 1);
        ksort($categories);
        $currency = DB::table('currency')->where('deleted', '0')->where('status', '1')->pluck('code', 'id')->toArray();
        $paymentTerms = DB::table('payment_terms')->select(['id', 'title'])->where('deleted', 0)->where('status', 1)->pluck('title', 'id');
        $paymentTerms = json_decode($paymentTerms, 1);
        ksort($paymentTerms);

        $country = DB::table('country')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $country = json_decode($country, 1);
        ksort($country);

        $state = array();

        $model->credit_limit = '0.00';
        return view('clients.form', ['model' => $model, 'categories' => $categories, 'paymentTerms' => $paymentTerms, 'country' => $country, 'state' => $state, 'currency' => $currency, 'flag' => $flag]);
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

        //$currencyStr = implode(",",$currencyArr);
        //pre($input);
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $input['available_balance'] = $input['credit_limit'];
        //$input['client_flag'] = 'B';

        $currencyData = Currency::getData($input['currency']);
        $input['company_name'] = $input['company_name'] . ' ' . $currencyData->code;
        $model = Clients::create($input);
        Activities::log('create', 'client', $model);
        $model['flag'] = 'client-registration';
        //app('App\Component\MailComponent')->sendMail($data['email'],$user);
        //Mail::to($request['email'])->send(new RegistrationMail($model));

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
                $modelContacts->client_id = $model->id;
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

        // Store deposite activities
        $modelActivities = new Activities;
        $modelActivities->type = 'cashCreditClient';
        $modelActivities->related_id = $model->id;
        $modelActivities->user_id = auth()->user()->id;
        $modelActivities->description = number_format($model->credit_limit, 2) . '- Amount deposited.';
        $modelActivities->cash_credit_flag = '2';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        // Add Client to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
        $modelAdmin = new Admin;
        $modelAdmin->qbApiCall('client',$model);
        } */

        if ($model->client_flag == 'B' && isset($_SESSION['sessionAccessToken'])) {

            $fData['id'] = $model->id;
            $fData['module'] = '11';
            $fData['flagModule'] = 'client';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('clients/' . $input['client_flag']);
    }

    public function storenewitem(Request $request)
    {
        session_start();
        $input = $request->all();
        //pre($input);
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $input['available_balance'] = $input['credit_limit'];
        //$input['client_flag'] = 'B';

        $currencyData = Currency::getData($input['currency']);
        $input['company_name'] = $input['company_name'] . ' ' . $currencyData->code;
        $model = Clients::create($input);
        Activities::log('create', 'client', $model);
        $model['flag'] = 'client-registration';
        //app('App\Component\MailComponent')->sendMail($data['email'],$user);
        //Mail::to($request['email'])->send(new RegistrationMail($model));

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
                $modelContacts->client_id = $model->id;
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

        // Store deposite activities
        $modelActivities = new Activities;
        $modelActivities->type = 'cashCreditClient';
        $modelActivities->related_id = $model->id;
        $modelActivities->user_id = auth()->user()->id;
        $modelActivities->description = number_format($model->credit_limit, 2) . '- Amount deposited.';
        $modelActivities->cash_credit_flag = '2';
        $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
        $modelActivities->save();

        // Add Client to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
        $modelAdmin = new Admin;
        $modelAdmin->qbApiCall('client',$model);
        } */

        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '11';
            $fData['flagModule'] = 'client';
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

    public function viewclientdetail(Request $request, $id)
    {
        $model = DB::table('clients')->where('id', $id)->first();
        return view('clients.clientdetail', ['model' => $model]);
    }

    public function changestatus()
    {
        $status = $_POST['status'];
        $changeStatus = ($status == '1') ? '0' : '1';
        $userId = $_POST['userId'];
        $model = Clients::find($userId);
        $data['status'] = $changeStatus;
        $model->fill($data);
        Activities::log('update', 'client', $model);
        $userData = DB::table('clients')->where('id', $userId)->update(['status' => $changeStatus]);
        return true;
    }

    public function resetpassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
        ]);
        $input = $request->all();
        if ($validator->passes()) {
            $encryptedPassword = bcrypt($input['password']);

            $model = Clients::find($input['userId']);
            $data['password'] = $encryptedPassword;
            $model->fill($data);
            Activities::log('update', 'client', $model);

            $dataUser = DB::table('clients')->where('id', $input['userId'])->update(['password' => $encryptedPassword]);
            $user = DB::table('clients')->where('id', $input['userId'])->first();
            $user = (array) $user;
            $user['plain_password'] = $input['password'];
            $user['flag'] = 'resetPassword';
            Mail::to($user['email'])->send(new RegistrationMail($user));
            return Response::json(['success' => '1']);
        }

        return Response::json(['errors' => $validator->errors()]);
    }

    public function viewclientactivities(Request $request, $id)
    {
        $model = DB::table('activities')->where('related_id', $id)->where('type', 'client')->orderBy('updated_on', 'desc')->get();
        return view('clients.clientactivity', ['model' => $model]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function show(Clients $clients, $id, $flag = null)
    {
        $checkPermission = User::checkPermission(['view_details_clients'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = Clients::find($id);

        $clientContact = DB::table('client_contact')
            ->where('client_contact.deleted', 0)
            ->where('client_contact.client_id', $id)
            ->where('client_contact.status', 1)
            ->get();

        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'client')->orderBy('id', 'desc')->get()->toArray();
        return view('clients.view', ['model' => $model, 'activityData' => $activityData, 'flag' => $flag, 'clientContact' => $clientContact]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function edit(Clients $clients, $id, $flag = null)
    {
        $checkPermission = User::checkPermission(['update_clients'], '', auth()->user()->id);
        if (!$checkPermission) {
            return redirect('/home');
        }

        $model = DB::table('clients')->where('id', $id)->first();
        $currencyArr = explode(',', $model->currency);
        $model->currency = $currencyArr;

        //$categories = DB::table('cashcredit_detail_type')->select(['id','name'])->where('deleted',0)->where('status',1)->where('account_type_id',5)->pluck('name', 'id');
        $categories = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
            ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
            ->where(function ($query) {
                $query->where('cashcredit_account_type.name', 'Client')
                    ->orWhere('cashcredit_account_type.name', 'Clients cash')
                    ->orWhere('cashcredit_account_type.name', 'Client à crédits');
            })
            ->where('cashcredit_detail_type.deleted', 0)
            ->pluck('name', 'id');
        $categories = json_decode($categories, 1);
        ksort($categories);
        $currency = DB::table('currency')->where('deleted', '0')->where('status', '1')->pluck('code', 'id')->toArray();
        $paymentTerms = DB::table('payment_terms')->select(['id', 'title'])->where('deleted', 0)->where('status', 1)->pluck('title', 'id');
        $paymentTerms = json_decode($paymentTerms, 1);
        ksort($paymentTerms);

        $country = DB::table('country')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $country = json_decode($country, 1);
        ksort($country);

        $state = DB::table('state')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        $state = json_decode($state, 1);
        ksort($state);

        $dataContacts = DB::table('client_contact')->where('client_id', $id)->get();
        $dataContacts = json_decode(json_encode($dataContacts));

        $activityData = DB::table('activities')->where('related_id', $id)->orderBy('updated_on', 'desc')->get()->toArray();
        return view("clients.form", ['model' => $model, 'activityData' => $activityData, 'categories' => $categories, 'paymentTerms' => $paymentTerms, 'country' => $country, 'state' => $state, 'dataContacts' => $dataContacts, 'currency' => $currency, 'flag' => $flag]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Clients $clients, $id)
    {
        session_start();
        $model = Clients::find($id);
        $available_balance_added = $model->credit_limit_add;
        $request['flag_prod_tax_type'] = !empty($request['flag_prod_tax_type']) ? 1 : 0;
        $model->fill($request->input());
        // Save activity logs
        Activities::log('update', 'client', $model);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        //pre($input);

        $input['flag_prod_tax_type'] = !empty($input['flag_prod_tax_type']) ? 1 : 0;

        if (!empty($input['credit_limit_add'])) {
            $input['available_balance'] = $input['available_balance'] + $input['credit_limit_add'];

            // Store deposite activities
            $modelActivities = new Activities;
            $modelActivities->type = 'cashCreditClient';
            $modelActivities->related_id = $model->id;
            $modelActivities->user_id = auth()->user()->id;
            $modelActivities->description = number_format($input['credit_limit_add'], 2) . '- Amount deposited.';
            $modelActivities->cash_credit_flag = '2';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            $input['credit_limit_add'] = $available_balance_added + $input['credit_limit_add'];
        }

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

            $anotherCurrencyClient = Clients::where('company_name', $findStr)->first();


            ClientContact::where('client_id', $id)->delete();
            if (!empty($anotherCurrencyClient) && isset($request->copycontacts))
                ClientContact::where('client_id', $anotherCurrencyClient->id)->delete();

            $contactsData = $input['clientContact'];
            for ($i = 0; $i < $countContacts; $i++) {
                $modelContacts = new ClientContact();
                $modelContacts->client_id = $model->id;
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
                Clients::where('id', $anotherCurrencyClient->id)->update(['flag_prod_tax_type' => $input['flag_prod_tax_type']]);
                $modelContactDetails = DB::table('client_contact')->where('client_id', $id)->get();
                foreach ($modelContactDetails as $key => $value) {
                    $contactDetailModel = ClientContact::find($value->id);
                    $contactDetailModel->client_id = $anotherCurrencyClient->id;
                    $contactDetailModel->company_name = $anotherCurrencyClient->company_name;
                    $newContactDetailModel = $contactDetailModel->replicate();
                    $newContactDetailModel->push();
                }
            }
        }

        // Update billing item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
        $modelAdmin = new Admin;
        $modelAdmin->qbApiCall('updateClient',$model);
        }*/

        if ($model->client_flag == 'B' && isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '11';
            $fData['flagModule'] = 'updateClient';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('clients/' . $input['client_flag']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Clients  $clients
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clients $clients, $id)
    {
        session_start();
        $record = DB::table('clients')->where('id', $id)->first();
        $model = Clients::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);
        $dataClient = DB::table('clients')->where('id', $id)->first();
        User::where('id', $dataClient->user_id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);

        // Delete billing item to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
        $modelAdmin = new Admin;
        $modelAdmin->qbApiCall('deleteClient',$record);
        }*/

        if ($record->client_flag == 'B' && isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $id;
            $fData['module'] = '11';
            $fData['flagModule'] = 'deleteClient';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function getclientdata()
    {
        $id = $_POST['clientId'];
        $dataClient = DB::table('clients')->where('id', $id)->first();
        return json_encode($dataClient);
    }

    public function getstatesdata()
    {
        $id = $_POST['id'];
        $data = DB::table('state')->where('country_id', $id)->get();
        $dt = '';
        foreach ($data as $key => $value) {
            $dt .= '<option value="' . $value->id . '">' . $value->name . '</option>';
        }
        return $dt;
    }

    public function getclientdropdowndataaftersubmit()
    {
        $allUsers = DB::table('clients')->select(['id', 'company_name'])->where('client_flag', 'B')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->get();
        $dt = '<option selected="selected" value="">Select ...</option>';
        foreach ($allUsers as $key => $value) {
            $dt .= '<option value="' . $value->id . '">' . $value->company_name . '</option>';
        }
        return $dt;
    }

    public function checkuniquecompany()
    {
        $value = $_POST['value'];
        $id = $_POST['id'];
        $flag = $_POST['flag'];
        if (!empty($id)) {
            if ($flag == 'B')
                $dataClient = DB::table('clients')->where('deleted', '0')->where('client_flag', $flag)->where('company_name', $value)->where('id', '<>', $id)->count();
            else
                $dataClient = DB::table('clients')->where('deleted', '0')
                    ->where(function ($query) use ($flag) {
                        $query->where('client_flag', $flag)
                            ->orWhereNull('client_flag');
                    })
                    ->where('company_name', $value)->where('id', '<>', $id)->count();
            $dataVendor = DB::table('vendors')->where('deleted', '0')->where('company_name', $value)->count();
        } else {
            if ($flag == 'B')
                $dataClient = DB::table('clients')->where('deleted', '0')->where('client_flag', $flag)->where('company_name', $value)->count();
            else
            {
                $dataClient = DB::table('clients')->where('deleted', '0')
                ->where(function ($query) use ($flag) {
                    $query->where('client_flag', $flag)
                        ->orWhereNull('client_flag');
                })
                ->where('company_name', $value)->count();
            }
            $dataVendor = DB::table('vendors')->where('deleted', '0')->where('company_name', $value)->count();
        }

        if ($dataClient || $dataVendor) {
            return 1;
        } else {
            return 0;
        }
    }

    public function copyclient()
    {
        $id = $_POST['userId'];
        $newCurrency = $_POST['newCurrency'];
        $flag = $_POST['flag'];

        $model = Clients::find($id);
        $oldCurrencyData = Currency::getData($model->currency);
        $currencyData = Currency::getDataUsingCode($newCurrency);
        $model->currency = $currencyData->id;
        $model->company_name = rtrim(str_replace($oldCurrencyData->code, '', $model->company_name)) . ' ' . $currencyData->code;
        $newModel = $model->replicate();
        $newModel->push();

        $modelContactDetails = DB::table('client_contact')->where('client_id', $id)->get();
        foreach ($modelContactDetails as $key => $value) {
            $contactDetailModel = ClientContact::find($value->id);
            $contactDetailModel->client_id = $newModel->id;
            $contactDetailModel->company_name = $newModel->company_name;
            $newContactDetailModel = $contactDetailModel->replicate();
            $newContactDetailModel->push();
        }

        Session::flash('flash_message', 'Client has been copied successfully');
    }

    public function listbydatatableserverside(Request $request)
    {
        $flag = $_REQUEST['flag'];
        $permissionClientsEdit = $flag == 'B' ? User::checkPermission(['update_billing_party'], '', auth()->user()->id) : User::checkPermission(['update_clients'], '', auth()->user()->id);
        $permissionClientsDelete = $flag == 'B' ?  User::checkPermission(['delete_billing_party'], '', auth()->user()->id) : User::checkPermission(['delete_clients'], '', auth()->user()->id);
        $permissionClientsResetPassword = $flag == 'B' ? User::checkPermission(['reset_password_billing_party'], '', auth()->user()->id)  : User::checkPermission(['reset_password_clients'], '', auth()->user()->id);

        $req = $request->all();

        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($flag == 'B') {
            $orderby = ['id', 'company_name', 'phone_number', 'email', 'cash_credit', 'available_balance'];
        } else {
            $orderby = ['id', 'company_name', 'phone_number', 'email'];
        }

        if ($flag == 'B') {
            $total = Clients::selectRaw('count(*) as total')->where('deleted', '0')->where('client_flag', 'B')->first();
            $query = Clients::where('deleted', '0')->where('client_flag', 'B');
            $filteredq = Clients::where('deleted', '0')->where('client_flag', 'B');
        } else {
            $total = Clients::selectRaw('count(*) as total')->where('deleted', '0')
                ->where(function ($query) {
                    $query->where('client_flag', '!=', 'B')
                        ->orWhereNull('client_flag');
                })
                ->first();
            $query = Clients::where('deleted', '0')->where(function ($query) {
                $query->where('client_flag', '!=', 'B')
                    ->orWhereNull('client_flag');
            });
            $filteredq = Clients::where('deleted', '0')->where(function ($query) {
                $query->where('client_flag', '!=', 'B')
                    ->orWhereNull('client_flag');
            });
        }
        $totalfiltered = $total->total;

        if ($search != '') {
            $query->where(function ($query2) use ($search) {
                $query2->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            if ($flag == 'B') {
                $query->orWhere(function ($query2) use ($search) {
                    $query2->orWhere('cash_credit', 'like', '%' . $search . '%')
                        ->orWhere('available_balance', 'like', '%' . $search . '%');
                });
            }
            $filteredq->where(function ($query2) use ($search) {
                $query2->where('company_name', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
            if ($flag == 'B') {
                $query->orWhere(function ($query2) use ($search) {
                    $query2->orWhere('cash_credit', 'like', '%' . $search . '%')
                        ->orWhere('available_balance', 'like', '%' . $search . '%');
                });
            }

            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();
        $data = [];
        foreach ($query as $key => $user) {
            $currencyData = Currency::getData($user->currency);
            if ($flag == 'B') {
                $checkClient = Clients::checkExistingCurrencyClient($user->company_name);
            }

            $btnClass = $user->status == '1' ? 'customButtonSuccess' : 'customButtonAlert';
            $btnActive = '<button class="customButtonInGrid ' . $btnClass . '" data-userid="' . $user->id . '" value="' . $user->status . '">' . Config::get('app.userStatus')[$user->status] . '</button>';

            $action = '<div class="dropdown">';
            $delete = route('deleteclient', $user->id);
            $edit = route('editclient', [$user->id, $flag]);
            if ($permissionClientsEdit) {
                $action .= '<a href="' . $edit . '" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;';
            }
            if ($permissionClientsDelete) {
                if ($user->company_name != 'UPS' && $user->company_name != 'UPS USD' && $user->company_name != 'Aeropost' && $user->company_name != 'Aeropost USD' && $user->company_name != 'CHATELAIN CARGO SERVICE S.A USD' && $user->company_name != 'UPS Miami USD' && $user->company_name != 'Aeropost Miami USD' && $user->company_name != 'CHATELAIN CARGO SERVICES INC USD') {
                    $action .= '<a class="delete-record" href="' . $delete . '" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
                }
            }

            $action .= '<button class="fa fa-cogs btn  btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="margin-left: 10px"></button><ul class="dropdown-menu" style="left:auto;">';

            if ($permissionClientsResetPassword) {
                $action .= '<li><a class="clsResetPsw" href="#" data-toggle="modal" data-userid=' . $user->id . ' data-target="#resetPassword">Reset Password</a></li>';
            }

            if ($flag == 'B' && $checkClient == 0) {
                $action .= '<li><a class="copyclient" href="#" data-userid=' . $user->id . ' data-currency=' . $currencyData->code . '>Copy Billing Party</a></li>';
            }

            $action .= '</ul></div>';

            if ($flag != 'B') {
                $data[] = [$user->id, $user->company_name, !empty($user->phone_number) ? $user->phone_number : '-', !empty($user->email) ? $user->email : '-', $btnActive, $action];
            } else {
                $data[] = [$user->id, $user->company_name, !empty($user->phone_number) ? $user->phone_number : '-', !empty($user->email) ? $user->email : '-', $currencyData->code, $user->cash_credit, $user->cash_credit == 'Credit' ? number_format($user->available_balance, 2) : '-', $btnActive, $action];
            }
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }

    public function searchClientsAutocomplete(Request $request){
        return Clients::getClientsAutocompleteNew($request->term);
    }
}
