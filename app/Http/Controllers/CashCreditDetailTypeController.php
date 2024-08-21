<?php
namespace App\Http\Controllers;
use App\CashCreditDetailType;
use App\CashCreditAccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use App\Admin;
class CashCreditDetailTypeController extends Controller
{
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_account_sub_types'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $cashcredit = DB::table('cashcredit_detail_type')
            ->select(['cashcredit_detail_type.*', 'quickbook_account_types.name as QbAccountName', 'quickbook_account_sub_types.name as QbSubAccountName'])
            ->leftJoin('quickbook_account_types', 'quickbook_account_types.id', '=', 'cashcredit_detail_type.quickbook_account_type_id')
            ->leftJoin('quickbook_account_sub_types', 'quickbook_account_sub_types.id', '=', 'cashcredit_detail_type.quickbook_account_sub_type_id')
            ->where('cashcredit_detail_type.deleted', 0)->orderBy('cashcredit_detail_type.id', 'desc')->get();
        return view("cashcreditdetailtype.index",['cashcredit'=>$cashcredit]);
    }

    public function create()
    {
        $checkPermission = User::checkPermission(['add_account_sub_types'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = new CashCreditDetailType;
        $accoutTypes = DB::table('cashcredit_account_type')->where('deleted',0)->where('status',1)->get()->pluck('name','id');
        return view('cashcreditdetailtype.form',['model'=>$model,'accoutTypes'=>$accoutTypes,'subAccounts'=>array()]);
    }

    public function store(Request $request)
    {
        session_start();
        $validater = $this->validate($request, [
            'name' => 'required|string',
            'account_type_id' => 'required',
            
        ]);
        $input = $request->all();
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = CashCreditDetailType::create($input);
        if(isset($_SESSION['sessionAccessToken']))
        {
            $fData['id'] = $model->id;
            $fData['module'] = '17';
            $fData['flagModule'] = 'otherAccount';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model='.$newModel);

            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }  
        Session::flash('flash_message', 'Record has been created successfully');
        return redirect('cashcreditdetailtype');
    }

    public function edit(CashCreditDetailType $CashCreditDetailType,$id)
    {
        $checkPermission = User::checkPermission(['update_account_sub_types'],'',auth()->user()->id);
        if(!$checkPermission)
            return redirect('/home');

        $model = DB::table('cashcredit_detail_type')->where('id',$id)->first();
        $accoutTypes = DB::table('cashcredit_account_type')->where('deleted',0)->where('status',1)->get()->pluck('name','id');
        $subAccounts = DB::table('quickbook_account_sub_types')->select('id', 'name')->where('qb_account_id',$model->quickbook_account_type_id)->pluck('name','id');
        return view("cashcreditdetailtype.form",['model'=>$model,'accoutTypes'=>$accoutTypes,'subAccounts'=>$subAccounts]);
    }

    public function update(Request $request, CashCreditDetailType $CashCreditDetailType,$id)
    {
        session_start();
        $model = CashCreditDetailType::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        // Modify QB Sync Flag
        $input['qb_sync'] = 0;
        $model->update($input);

        if(isset($_SESSION['sessionAccessToken']))
        {
            if(empty($model->quick_book_id))
            {
                $fData['id'] = $model->id;
                $fData['module'] = '17';
                $fData['flagModule'] = 'otherAccount';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                // Store expense to QB
                $newModel = base64_encode(serialize($fData));
                //$newTest = unserialize(base64_decode($newModel, true));
                //pre($newTest);
                $urlAction = url('call/qb?model='.$newModel);

                
                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }else
            {
                $fData['id'] = $model->id;
                $fData['module'] = '17';
                $fData['flagModule'] = 'updateOtherAccount';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                // Store expense to QB
                $newModel = base64_encode(serialize($fData));
                //$newTest = unserialize(base64_decode($newModel, true));
                //pre($newTest);
                $urlAction = url('call/qb?model='.$newModel);

                
                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('cashcreditdetailtype');
    }

    public function destroy(CashCreditDetailType $CashCreditDetailType,$id)
    {
        session_start();
        $model = CashCreditDetailType::where('id',$id)->update(['deleted'=>1,'deleted_at'=>gmdate("Y-m-d H:i:s"), 'qb_sync' => 0]);
        if(isset($_SESSION['sessionAccessToken']))
        {
            $fData['id'] = $id;
            $fData['module'] = '17';
            $fData['flagModule'] = 'deleteOtherAccount';
            $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model='.$newModel);

            
            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function getqbsubaccounts(Request $request)
    {
        $input = $request->all();
        $getQbId = CashCreditAccountType::find($input['id']);
        $subTypes = DB::table('quickbook_account_sub_types')->select('id', 'name')->where('qb_account_id',$getQbId->quickbook_account_type_id)->get()->toArray();
        
        //return json_encode($subTypes);
        $data['subTypes'] = $subTypes;
        $data['quickbook_account_type_id'] = $getQbId->quickbook_account_type_id;
        return json_encode($data);
    }
}
