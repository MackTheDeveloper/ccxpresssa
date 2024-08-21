<?php

namespace App\Http\Controllers;

use App\ClientBranch;
use App\Clients;
use App\ClientAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;

class ClientAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = DB::table('client_address')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("client-address.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientId = null,$flagFromWhere = null)
    {
        $model = new ClientAddress;
        $country = DB::table('country')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $country = json_decode($country,1);
        ksort($country);

        $state = array();

        if(!empty($clientId))
        {
            $modelClient = clients::find($clientId);
            $model->company_name = $modelClient->company_name;
            $model->client_id = $clientId;
        }

        $dataClient = Clients::getClientsAutocomplete();

        $clientBranch = array();
        return view('client-address.form',['model'=>$model,'country'=>$country,'state'=>$state,'dataClient'=>$dataClient,'clientBranch'=>$clientBranch,'clientId'=>$clientId,'flagFromWhere'=>$flagFromWhere]);
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
        $input['client_id'] = $input['hidden_client_id'];
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = ClientAddress::create($input);

        Session::flash('flash_message', 'Record has been created successfully');
        
        if(!empty($input['flagFromWhere']) && $input['flagFromWhere'] == 'clientdetail')
            return redirect()->route('viewdetails',[$model->client_id]);
        else
            return redirect('clientaddresses');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientAddress  $clientAddress
     * @return \Illuminate\Http\Response
     */
    public function show(ClientAddress $clientAddress)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientAddress  $clientAddress
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientAddress $clientAddress,$id,$flagFromWhere = null)
    {
        $model = clientAddress::find($id);
        $country = DB::table('country')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $country = json_decode($country,1);
        ksort($country);

        $state = DB::table('state')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $state = json_decode($state,1);
        ksort($state);

        $clientBranch = DB::table('client_branch')->select(['id','branch_name'])->where('deleted',0)->where('status',1)->pluck('branch_name', 'id');
        $clientBranch = json_decode($clientBranch,1);
        ksort($clientBranch);

        $dataClient = Clients::getClientsAutocomplete();
        return view("client-address.form",['model'=>$model,'country'=>$country,'state'=>$state,'dataClient'=>$dataClient,'clientBranch'=>$clientBranch,'flagFromWhere'=>$flagFromWhere]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientAddress  $clientAddress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientAddress $clientAddress,$id)
    {
        $model = ClientAddress::find($id);
        $input = $request->all();
        $input['updated_at'] = gmdate("Y-m-d H:i:s");
        $input['client_id'] = $input['hidden_client_id'];
        $model->update($input);

        Session::flash('flash_message', 'Record has been updated successfully');
        if(!empty($input['flagFromWhere']) && $input['flagFromWhere'] == 'clientdetail')
            return redirect()->route('viewdetails',[$model->client_id]);
        else
            return redirect('clientaddresses');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientAddress  $clientAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientAddress $clientAddress,$id)
    {
        $model = clientAddress::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }

    public function getaddresses()
    {
        $branch = $_POST['branch'];
        $detailTypes = DB::table('client_address')->select(['client_address.id','client_address.address','client_address.city','country.name as countryName','state.name as stateName'])
        ->join('country', 'country.id', '=', 'client_address.country_id')
        ->join('state', 'state.id', '=', 'client_address.state_id')
        ->where('client_address.client_branch_id',$branch)
        ->where('client_address.deleted',0)
        ->get();
        
        $dt = '';
        foreach ($detailTypes as $key => $value) {

            $stateTemp = "";
                if (!empty($value->stateName)) {
                    $stateTemp = ", " . $value->stateName;
                }
                $fullAddr = $value->address . ' , ' . $value->city . $stateTemp . ", " . $value->countryName;

           $dt .=  '<option value="'.$value->id.'">'.$fullAddr.'</option>';
        }
        return $dt;


    }
}
