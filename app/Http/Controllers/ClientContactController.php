<?php

namespace App\Http\Controllers;

use App\ClientContact;
use App\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
class ClientContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_client_contacts'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('client_contact')->where('deleted','0')->whereNotNull('client_id')->orderBy('id', 'desc')->get();
        return view("client-contact.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientId = null,$flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_client_contacts'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new ClientContact;
        $clientBranch = array();
        $clientAddress = array();

        if(!empty($clientId))
        {
            $modelClient = clients::find($clientId);
            $model->company_name = $modelClient->company_name;
            $model->client_id = $clientId;
        }
        
        
        $dataClient = Clients::getBillingPartyAutocomplete();
        return view('client-contact.form',['model'=>$model,'dataClient'=>$dataClient,'clientBranch'=>$clientBranch,'clientAddress'=>$clientAddress,'clientId'=>$clientId,'flagFromWhere'=>$flagFromWhere]);
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
        if(!empty($input['hidden_client_id']))
        {
        $input['clientContact']['name'] = array_values($input['clientContact']['name']);
        $input['clientContact']['personal_contact'] = array_values($input['clientContact']['personal_contact']);
        $input['clientContact']['cell_number'] = array_values($input['clientContact']['cell_number']);
        $input['clientContact']['direct_line'] = array_values($input['clientContact']['direct_line']);
        $input['clientContact']['work'] = array_values($input['clientContact']['work']);
        $input['clientContact']['email'] = array_values($input['clientContact']['email']);

        $countContacts = count($input['clientContact']['name']);
            if($countContacts > 0)
            {
                $contactsData = $input['clientContact'];
                for($i = 0; $i < $countContacts; $i++)
                {
                    $modelContacts = new ClientContact();
                    $modelContacts->client_id = $input['hidden_client_id'];
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
        }

        Session::flash('flash_message', 'Record has been created successfully');
        
        if(!empty($input['flagFromWhere']) && $input['flagFromWhere'] == 'clientdetail')
            return redirect()->route('viewdetails',[$input['hidden_client_id']]);
        else
            return redirect('clientcontacts');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     */
    public function show(ClientContact $clientContact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientContact $clientContact,$id,$flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['update_client_contacts'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = clientContact::find($id);
        
        $clientBranch = DB::table('client_branch')->select(['id','branch_name'])->where('deleted',0)->where('client_id',$model->client_id)->where('status',1)->pluck('branch_name', 'id');
        $clientBranch = json_decode($clientBranch,1);
        ksort($clientBranch);

        $clientAddress = array();

        $addArr = DB::table('client_address')->select(['client_address.id','client_address.address','client_address.city','country.name as countryName','state.name as stateName'])
        ->join('country', 'country.id', '=', 'client_address.country_id')
        ->join('state', 'state.id', '=', 'client_address.state_id')
        ->where('client_address.client_branch_id',$model->client_branch_id)
        ->where('client_address.deleted',0)
        ->get();

        $dataContacts  = DB::table('client_contact')->where('id',$id)->get();
        $dataContacts = json_decode(json_encode($dataContacts));

         foreach ($addArr as $key => $value) {
                $stateTemp = "";
                if (!empty($value->stateName)) {
                    $stateTemp = ", " . $value->stateName;
                }
                $clientAddress[$value->id] = $value->address . ' , ' . $value->city . $stateTemp . ", " . $value->countryName;
        }

        $dataClient = Clients::getBillingPartyAutocomplete();
        return view("client-contact.form",['model'=>$model,'dataClient'=>$dataClient,'clientBranch'=>$clientBranch,'clientAddress'=>$clientAddress,'flagFromWhere'=>$flagFromWhere,'dataContacts'=>$dataContacts]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientContact $clientContact,$id)
    {
        $model = clientContact::find($id);
        $input = $request->all();
        $input['client_id'] = $input['hidden_client_id'];
        $input['updated_at'] = gmdate("Y-m-d H:i:s");
        
        $input['name'] = $input['clientContact']['name'][0];
        $input['personal_contact'] = $input['clientContact']['personal_contact'][0];
        $input['cell_number'] = $input['clientContact']['cell_number'][0];
        $input['direct_line'] = $input['clientContact']['direct_line'][0];
        $input['work'] = $input['clientContact']['work'][0];
        $input['email'] = $input['clientContact']['email'][0];

        $model->update($input);

        Session::flash('flash_message', 'Record has been updated successfully');
        if(!empty($input['flagFromWhere']) && $input['flagFromWhere'] == 'clientdetail')
            return redirect()->route('viewdetails',[$model->client_id]);
        else
            return redirect('clientcontacts');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientContact  $clientContact
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientContact $clientContact,$id)
    {
        $model = clientContact::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }
}
