<?php

namespace App\Http\Controllers;

use App\ClientContact;
use App\Clients;
use App\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
class VendorContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_vendor_contacts'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $items = DB::table('client_contact')->where('deleted','0')->whereNotNull('vendor_id')->orderBy('id', 'desc')->get();
        return view("vendor-contact.index",['items'=>$items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($vendorId = null,$flagFromWhere = null)
    {
        $checkPermission = User::checkPermission(['add_vendor_contacts'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new ClientContact;
        $clientBranch = array();
        $clientAddress = array();

        if(!empty($vendorId))
        {
            $modelClient = Vendors::find($vendorId);
            $model->company_name = $modelClient->company_name;
            $model->vendor_id = $vendorId;
        }
        
        
        $dataClient = Vendors::getVendorsAutocomplete();
        return view('vendor-contact.form',['model'=>$model,'dataClient'=>$dataClient,'clientBranch'=>$clientBranch,'clientAddress'=>$clientAddress,'vendorId'=>$vendorId,'flagFromWhere'=>$flagFromWhere]);
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
        if(!empty($input['hidden_vendor_id']))
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
                    $modelContacts->vendor_id = $input['hidden_vendor_id'];
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
        
        return redirect('vendorcontacts');
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
        $checkPermission = User::checkPermission(['update_vendor_contacts'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = clientContact::find($id);
        
        $clientBranch = array();
        $clientAddress = array();

        $dataContacts  = DB::table('client_contact')->where('id',$id)->get();
        $dataContacts = json_decode(json_encode($dataContacts));

        $dataClient = Vendors::getVendorsAutocomplete();
        return view("vendor-contact.form",['model'=>$model,'dataClient'=>$dataClient,'clientBranch'=>$clientBranch,'clientAddress'=>$clientAddress,'flagFromWhere'=>$flagFromWhere,'dataContacts'=>$dataContacts]);
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
        $input['vendor_id'] = $input['hidden_vendor_id'];
        $input['updated_at'] = gmdate("Y-m-d H:i:s");
        
        $input['name'] = $input['clientContact']['name'][0];
        $input['personal_contact'] = $input['clientContact']['personal_contact'][0];
        $input['cell_number'] = $input['clientContact']['cell_number'][0];
        $input['direct_line'] = $input['clientContact']['direct_line'][0];
        $input['work'] = $input['clientContact']['work'][0];
        $input['email'] = $input['clientContact']['email'][0];

        $model->update($input);

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('vendorcontacts');
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
