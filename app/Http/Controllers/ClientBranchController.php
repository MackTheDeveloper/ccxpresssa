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
class ClientBranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = DB::table('client_branch')->where('deleted','0')->orderBy('id', 'desc')->get();
        return view("client-branch.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientId = null,$flagFromWhere = null)
    {
        $model = new ClientBranch;
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
        return view('client-branch.form',['model'=>$model,'country'=>$country,'state'=>$state,'dataClient'=>$dataClient,'clientId'=>$clientId,'flagFromWhere'=>$flagFromWhere]);
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
        $model = ClientBranch::create($input);

        $addressModel = new ClientAddress();
        $addressModel->client_id = $model->client_id;
        $addressModel->client_branch_id = $model->id;
        $addressModel->company_name = $model->company_name;
        $addressModel->address = $model->branch_address;
        $addressModel->zipcode = $model->zipcode;
        $addressModel->city = $model->city;
        $addressModel->state_id = $model->state_id;
        $addressModel->country_id = $model->country_id;
        $addressModel->created_at = gmdate("Y-m-d H:i:s");
        $addressModel->save();

        Session::flash('flash_message', 'Record has been created successfully');
        if(!empty($input['flagFromWhere']) && $input['flagFromWhere'] == 'clientdetail')
            return redirect()->route('viewdetails',[$model->client_id]);
        else
            return redirect('clientbranches');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClientBranch  $clientBranch
     * @return \Illuminate\Http\Response
     */
    public function show(ClientBranch $clientBranch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ClientBranch  $clientBranch
     * @return \Illuminate\Http\Response
     */
    public function edit(ClientBranch $clientBranch,$id,$flagFromWhere = null)
    {
        $model = ClientBranch::find($id);
        $country = DB::table('country')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $country = json_decode($country,1);
        ksort($country);

        $state = DB::table('state')->select(['id','name'])->where('deleted',0)->where('status',1)->pluck('name', 'id');
        $state = json_decode($state,1);
        ksort($state);

        $dataClient = Clients::getClientsAutocomplete();
        return view("client-branch.form",['model'=>$model,'country'=>$country,'state'=>$state,'dataClient'=>$dataClient,'flagFromWhere'=>$flagFromWhere]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ClientBranch  $clientBranch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClientBranch $clientBranch,$id)
    {
        $model = ClientBranch::find($id);
        $input = $request->all();
        $input['updated_at'] = gmdate("Y-m-d H:i:s");
        $input['client_id'] = $input['hidden_client_id'];
        $model->update($input);

        DB::table('clients')->where('client_branch_id',$model->id)->update(['branch_name' => $model->branch_name]);


        Session::flash('flash_message', 'Record has been updated successfully');
        if(!empty($input['flagFromWhere']) && $input['flagFromWhere'] == 'clientdetail')
            return redirect()->route('viewdetails',[$model->client_id]);
        else
            return redirect('clientbranches');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ClientBranch  $clientBranch
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClientBranch $clientBranch,$id)
    {
        $model = ClientBranch::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s")]);
    }

    public function getbranches()
    {
        $client = $_POST['client'];
        $detailTypes = DB::table('client_branch')->select(['id','branch_name'])->where('client_id',$client)->get();
        $dt = '';
        foreach ($detailTypes as $key => $value) {
           $dt .=  '<option value="'.$value->id.'">'.$value->branch_name.'</option>';
        }
        return $dt;
    }

    
}
