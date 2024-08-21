<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Costs;
use App\BillingItems;
use App\Clients;
use App\ccpack;
use App\Vendors;
use App\Admin;
use App\Aeropost;
use App\AeropostFreightCommission;
use App\AeropostInvoices;
use App\AeropostInvoiceItemDetails;
use App\Agent;
use App\Warehouse;
use App\Expense;
use App\Cashier;
use App\ExpenseDetails;
use App\HawbFiles;
use App\HawbPackages;
use App\HawbContainers;
use App\Invoices;
use App\InvoiceItemDetails;
use QuickBooksOnline\API\DataService\DataService;
use Config;
use Excel;
use App\User;
use PDF;
use QuickBooksOnline\API\Facades\Invoice;
use Session;
use App\Jobs\quickBook;
use Carbon\Carbon;
use Artisan;
use App\Activities;
use stdClass;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        /*if(auth()->user()->id == 1) 
        {
            //session_start();
            if(!isset($_SESSION)) 
            { 
                session_start(); 
            } 
            if (isset($_SESSION['sessionAccessToken'])) {
                $config = Config::get('app.QB');
                $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                //'baseUrl' => "development"
                'baseUrl' => "https://quickbooks.api.intuit.com"
            ));

                $accessToken = $_SESSION['sessionAccessToken'];
                $accessTokenJson = array('token_type' => 'bearer',
                    'access_token' => $accessToken->getAccessToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
                    'expires_in' => $accessToken->getAccessTokenExpiresAt()
                );
                $dataService->updateOAuth2Token($accessToken);
                $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
                $companyInfo = $dataService->getCompanyInfo();
                //$companyInfo = $dataService->Query("select * from Item where Type='Service'");
                //$companyInfo = $dataService->FindAll("Customer");
                
                pre($companyInfo);
            } 
        }*/
        if (auth()->user()->department == 12) { // Agent
            $cargoFiles = DB::table('cargo')->where('agent_id', auth()->user()->id)->where('deleted', 0)->where('status', 1)
                ->where(function ($q) {
                    $q->orWhereNull('billing_party')
                        ->orWhereNull('warehouse')
                        ->orWhereNull('cash_credit');
                })
                ->get()
                ->toArray();

            foreach ($cargoFiles as $key => $value) {

                if (empty($value->consolidate_flag) && !empty($value->billing_party) && !empty($value->cash_credit)) {
                    unset($cargoFiles[$key]);
                } else {
                    $cargoFiles[$key]->flagModule = 'Cargo';
                    $cargoFiles[$key]->operation_type = ($value->cargo_operation_type == 1 ? 'Import' : ($value->cargo_operation_type == 2 ? 'Export' : 'Locale'));
                }
            }

            $courierFiles = DB::table('ups_details')->where('agent_id', auth()->user()->id)->where('deleted', 0)->where('status', 1)
                ->where(function ($q) {
                    $q->orWhereNull('billing_party')
                        ->orWhereNull('cash_credit');
                })
                ->get()
                ->toArray();

            foreach ($courierFiles as $key => $value) {
                $courierFiles[$key]->flagModule = 'Courier';
                $courierFiles[$key]->opening_date = $value->tdate;
                $courierFiles[$key]->operation_type = 'Import';
                $courierFiles[$key]->awb_bl_no = $value->awb_number;
            }

            $allFiles = array_merge($cargoFiles, $courierFiles);


            return view('homeagent', ['allFiles' => $allFiles]);
        } else if (auth()->user()->department == 14) { // Warehouse
            return view('homewarehouse');
        } else if (auth()->user()->department == 11) { // Cashier
            return view('homecashier');
        } else {
            return view('home');
        }
    }

    public function addnewitem($flag)
    {
        if ($flag == 'cost-items') {
            $model = new Costs;
            $findCode = DB::table('costs')->where('deleted', 0)->where('status', 1)->orderBy('id', 'desc')->first();
            if (empty($findCode))
                $bilCode = '501';
            else
                $bilCode = ((int) $findCode->cost_billing_code + 1);

            $dataBillingItems = DB::table('billing_items')
                ->select('id', DB::raw("item_code,CONCAT(item_code,' - ',billing_name) as fullcost"))
                ->where('deleted', 0)->where('status', 1)->get()
                ->pluck('fullcost', 'id');


            return view('costs.addnewitem', ['model' => $model, 'dataBillingItems' => $dataBillingItems]);
        }
        if ($flag == 'billing-items') {
            $model = new BillingItems;
            $dataCost = DB::table('costs')
                ->select('id', DB::raw("id,CONCAT(code,' - ',cost_name) as fullcost"))
                ->where('deleted', 0)->where('status', 1)->get()
                ->pluck('fullcost', 'id');
            return view('billing-items.addnewitem', ['model' => $model, 'dataCost' => $dataCost]);
        }

        if ($flag == 'client') {
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
            return view('clients.addnewitem', ['model' => $model, 'categories' => $categories, 'paymentTerms' => $paymentTerms, 'country' => $country, 'state' => $state, 'currency' => $currency]);
        }
        if ($flag == 'vendor') {
            $model = new Vendors;
            $currency = DB::table('currency')->where('deleted', '0')->where('status', '1')->pluck('code', 'id')->toArray();
            $vendorType = DB::table('cashcredit_detail_type')
                ->select(['cashcredit_detail_type.name', 'cashcredit_detail_type.id'])
                ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
                ->where(function ($query) {
                    $query->where('cashcredit_account_type.name', 'Vendor')
                        ->orWhere('cashcredit_account_type.name', 'Fournisseurs');
                })
                ->pluck('name', 'id');
            $paymentTerms = DB::table('vendor_payment_terms')->select(['id', 'title'])->where('deleted', 0)->where('status', 1)->pluck('title', 'id');
            return view('vendors.addnewitem', ['model' => $model, 'vendorType' => $vendorType, 'currency' => $currency, 'paymentTerms' => $paymentTerms]);
        }
        if ($flag == 'addimporthawbfile' || $flag == 'addexporthawbfile') {
            $modelHawb = new HawbFiles;
            $modelHawbCargoPackage = new HawbPackages;
            $modelHawbCargoContainer = new HawbContainers;
            $dataImportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '1')->get()->pluck('awb_bl_no', 'id');
            $dataExportAwbNos = DB::table('cargo')->where('deleted', 0)->whereNotNull('awb_bl_no')->where('cargo_operation_type', '2')->get()->pluck('awb_bl_no', 'id');
            $modelHawb->weight = '0.00';
            $modelHawb->hdate = date('d-m-Y');
            if ($flag == 'addimporthawbfile') {
                return view('cargo.importAddNewHawb', ['model' => $modelHawb, 'modelCargoPackage' => $modelHawbCargoPackage, 'modelCargoContainer' => $modelHawbCargoContainer, 'dataImportAwbNos' => $dataImportAwbNos, 'actionUrl' => 'hawbfile/store']);
            } else {

                return view('cargo.exportAddNewHawb', ['model' => $modelHawb, 'modelCargoPackage' => $modelHawbCargoPackage, 'modelCargoContainer' => $modelHawbCargoContainer, 'dataExportAwbNos' => $dataExportAwbNos, 'actionUrl' => 'hawbfile/store']);
            }
        }
        if ($flag == 'addimporthawbfileforccpack') {
            $model = new ccpack;
            $model->arrival_date = date('d-m-Y');
            $warehouses = DB::table('warehouse')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->where('warehouse_for', 'Courier')->orderBy('id', 'desc')->pluck('name', 'id');
            return view('ccpack.importAddNewHawb', ['model' => $model, 'warehouses' => $warehouses]);
        }
    }





    public function checknotificationscount()
    {
        $flagModule = $_POST['flagModule'];
        if ($flagModule == 'admin-manager')
            $notiAll = Admin::getNotificationForAdmin('All');
        if ($flagModule == 'cashier')
            $notiAll = Cashier::getNotificationForCashier('All');
        if ($flagModule == 'agent')
            $notiAll = Agent::getNotificationForAgent('All');
        if ($flagModule == 'warehouse')
            $notiAll = Warehouse::getNotificationForWarehouse('All');

        return $notiAll;
    }

    public function checknotifications()
    {
        $flagModule = $_POST['flagModule'];
        if ($flagModule == 'admin-manager')
            $noti = Admin::getNotificationForAdmin();
        if ($flagModule == 'cashier')
            $noti = Cashier::getNotificationForCashier();
        if ($flagModule == 'agent')
            $noti = Agent::getNotificationForAgent();
        if ($flagModule == 'warehouse')
            $noti = Warehouse::getNotificationForWarehouse();


        foreach ($noti as $key => $row) {
            // replace 0 with the field's index/key
            $notiAll1['notificationDateTime'][$key] = $row->notificationDateTime;
            $notiAll1['notificationStatus'][$key] = $row->notificationStatus;
        }

        if (!empty($noti))
            array_multisort($notiAll1['notificationStatus'], SORT_DESC, $notiAll1['notificationDateTime'], SORT_DESC, $noti);

        return view('checknotifications', ['flagModule' => $flagModule, 'noti' => $noti]);
    }

    public function viewallnotifications()
    {
        if (auth()->user()->department == 13 || auth()->user()->department == 10) {
            $notiAll = Admin::getNotificationForAdmin();
            $flagModule = 'admin-manager';
        }

        if (auth()->user()->department == 11) {
            $notiAll = Cashier::getNotificationForCashier();
            $flagModule = 'cashier';
        }

        if (auth()->user()->department == 12) {
            $notiAll = Agent::getNotificationForAgent();
            $flagModule = 'agent';
        }

        if (auth()->user()->department == 14) {
            $notiAll = Warehouse::getNotificationForWarehouse();
            $flagModule = 'warehouse';
        }

        foreach ($notiAll as $key => $row) {
            // replace 0 with the field's index/key
            $notiAll1['notificationDateTime'][$key] = $row->notificationDateTime;
            $notiAll1['notificationStatus'][$key] = $row->notificationStatus;
        }

        if (!empty($notiAll))
            array_multisort($notiAll1['notificationStatus'], SORT_DESC, $notiAll1['notificationDateTime'], SORT_DESC, $notiAll);

        return view('allnotifications', ['flagModule' => $flagModule, 'notiAll' => $notiAll]);
    }

    public function checkqbresponse()
    {
        return view('checkqbresponse');
    }

    public function callback()
    {
        return view('callback');
    }

    public function apiCall()
    {
        return view('apiCall');
    }

    public function loginwithconnection()
    {
        return view('loginwithconnection');
    }

    public function checkconnectedornot()
    {
        session_start();
        if (User::checkPermission(['show_quickbooks'], '', auth()->user()->id)) {
            if (isset($_SESSION['sessionAccessToken'])) {
                //app('App\Http\Controllers\AdminController')->callbacksync();
                /* $url = url('qb/callbacksync');
                shell_exec('curl ' . $url . ' > /dev/null 2>/dev/null &'); */
                /* $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
                $newModel = base64_encode(serialize($fData));
                $url = url('qb/callbacksync',$newModel);
                shell_exec('curl ' . $url . ' > /dev/null 2>/dev/null &'); */


                /* //$checkCronStatus = DB::table('qb_cron')->where('flag_complete', 0)->count();
                
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];
                $newModel = base64_encode(serialize($fData));
                $emailJob = new quickBook($newModel);
                dispatch($emailJob);
                //dispatchIf($checkCronStatus == 0);
                //Artisan::call('queue:work'); */
                return '1';
            } else {
                return '0';
            }
        } else {
            return '1';
        }
    }


    public function importClients()
    {

        // $headercolumnArr = Excel::load("public/Account & costomers list (1).xls")->get()->toArray();
        $headercolumnArr = Excel::toArray(new stdClass(), "public/Account & costomers list (1).xls");
        // pre($headercolumnArr);
        $accountArr = $headercolumnArr[0];
        $clientArr = $headercolumnArr[1];
        unset($clientArr[0]);
        $clientArr = array_values($clientArr);
        //substr("Hello world",-1,-5);
        // pre($clientArr);
        $i = 0;
        foreach ($clientArr as $key => $value) {
            if ($i != 0) {
                $name = $value[0];
                $currency = $value[1];
                $currencyVal = ($currency == 'USD' ? 1 : 3);
                $name = substr($name, 0, -4);
                $data = DB::table('clients')->where('company_name', $name)->where('currency', $currencyVal)->where('deleted', '0')->first();
                //pre($data);
                if (!$data) {
                    $input['company_name'] = $name;
                    $input['currency'] = $currencyVal;
                    $model = Clients::create($input);
                    //pre($model);
                } else {
                    $model = Clients::find($data->id);
                    //pre($client); 
                    $model->update(['company_name' => $name, 'currency' => $currencyVal]);
                }
            }
            $i++;
        }
    }

    public function changeinfilenumber()
    {
        $data = DB::table('ccpack')->select('id', 'file_number')->get();
        //pre($data,1);
        foreach ($data as $key => $value) {
            $newNumber = substr($value->file_number, 0, 1) . ' ' . substr($value->file_number, 1);
            //pre($newNumber);
            DB::table('ccpack')->where('id', $value->id)->update(['file_number' => $newNumber]);
            //pre("Check");
        }
        //pre($data);
    }

    public function invoicesequences()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);

        /* $last = 'HF-5958';
        $data = DB::table('invoices')->select('*')->where('id','>',2133)->get();
        $i = 1;
        foreach ($data as $key => $value) {
            
            if(!empty($value->cargo_id))
                $ab = 'CA-';
            else if(!empty($value->ups_id))
                $ab = 'UP-';
            else if(!empty($value->aeropost_id))
                $ab = 'AP-';
            else if(!empty($value->ccpack_id))
                $ab = 'CC-';

            $ab .= substr($last,3) + 1;

            Invoices::where('id',$value->id)->update(['bill_no'=>$ab]);

            $Datalast = DB::table('invoices')->where('id',$value->id)->first();
            $last = $Datalast->bill_no;

            $i++;
        } */


        /* $data = DB::table('ups_invoices')->where('deleted','0')->get();
        foreach ($data as $key => $value) {
            $value = (array) $value;
            $oldInvId = $value['id'];
            $value['id'] = null;
            $value['bill_no'] = 'UP-'.$value['bill_no'];
            $model = Invoices::create($value);

            $invData = DB::table('invoices')->where('id',$model->id)->first();

            $pdf = PDF::loadView('upsinvoices.printupsinvoice',['invoice'=> (array) $invData]);
            $pdf_file = 'printUpsInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/upsInvoices/'.$pdf_file;
            $pdf->save($pdf_path);

            // Invoice Items
            $dataInvoiceItems = DB::table('ups_invoice_item_details')->where('invoice_id',$oldInvId)->get();
            foreach ($dataInvoiceItems as $key => $value) {
                $value = (array) $value;
                $value['id'] = null;
                $value['invoice_id'] = $model->id;
                $modelInvoiceItemDetails = InvoiceItemDetails::create($value);
            }

            // Payment
            $dataInvoicePaymentItems = DB::table('invoice_payments')->where('invoice_id',$oldInvId)->where('ups_id',$model->ups_id)->get();
            foreach ($dataInvoicePaymentItems as $key => $value) {
                DB::table('invoice_payments')
                ->where('id', $value->id)
                ->update(['invoice_id' => $model->id]);
            }
        } */

        /* $data = DB::table('aeropost_invoices')->where('deleted','0')->get();
        foreach ($data as $key => $value) {
            $value = (array) $value;
            $oldInvId = $value['id'];
            $value['id'] = null;
            $value['bill_no'] = 'AP-'.$value['bill_no'];
            $model = Invoices::create($value);

            $invData = DB::table('invoices')->where('id',$model->id)->first();

            $pdf = PDF::loadView('aeropost-invoices.printaeropostinvoice',['invoice'=>(array) $invData]);
            $pdf_file = 'printAeropostInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/aeropostInvoices/'.$pdf_file;
            $pdf->save($pdf_path);

            // Invoice Items
            $dataInvoiceItems = DB::table('aeropost_invoice_item_details')->where('invoice_id',$oldInvId)->get();
            foreach ($dataInvoiceItems as $key => $value) {
                $value = (array) $value;
                $value['id'] = null;
                $value['invoice_id'] = $model->id;
                $modelInvoiceItemDetails = InvoiceItemDetails::create($value);
            }

            // Payment
            $dataInvoicePaymentItems = DB::table('invoice_payments')->where('invoice_id',$oldInvId)->where('aeropost_id',$model->aeropost_id)->get();
            foreach ($dataInvoicePaymentItems as $key => $value) {
                DB::table('invoice_payments')
                ->where('id', $value->id)
                ->update(['invoice_id' => $model->id]);
            }
        } */

        /* $data = DB::table('ccpack_invoices')->where('deleted','0')->get();
        foreach ($data as $key => $value) {
            $value = (array) $value;
            $oldInvId = $value['id'];
            $value['id'] = null;
            $value['bill_no'] = 'CC-'.$value['bill_no'];
            $model = Invoices::create($value);

            $invData = DB::table('invoices')->where('id',$model->id)->first();

            $pdf = PDF::loadView('ccpackinvoices.printccpackinvoice',['invoice'=>(array) $invData]);
            $pdf_file = 'printCCpackInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/ccpackInvoices/'.$pdf_file;
            $pdf->save($pdf_path);

            // Invoice Items
            $dataInvoiceItems = DB::table('ccpack_invoice_item_details')->where('invoice_id',$oldInvId)->get();
            foreach ($dataInvoiceItems as $key => $value) {
                $value = (array) $value;
                $value['id'] = null;
                $value['invoice_id'] = $model->id;
                $modelInvoiceItemDetails = InvoiceItemDetails::create($value);
            }

            // Payment
            $dataInvoicePaymentItems = DB::table('invoice_payments')->where('invoice_id',$oldInvId)->where('ccpack_id',$model->ccpack_id)->get();
            foreach ($dataInvoicePaymentItems as $key => $value) {
                DB::table('invoice_payments')
                ->where('id', $value->id)
                ->update(['invoice_id' => $model->id]);
            }
        } */

        /* $data = DB::table('housefile_invoices')->where('deleted','0')->get();
        foreach ($data as $key => $value) {
            $value = (array) $value;
            $oldInvId = $value['id'];
            $value['id'] = null;
            $value['bill_no'] = 'HF-'.$value['bill_no'];
            $model = Invoices::create($value);

            $invData = DB::table('invoices')->where('id',$model->id)->first();

            $pdf = PDF::loadView('housefile-invoices.print',['invoice'=>(array) $invData]);
            $pdf_file = 'printInvoice_'.$model->id.'.pdf';
            $pdf_path = 'public/houseFileInvoices/'.$pdf_file;
            $pdf->save($pdf_path);
            
            // Invoice Items
            $dataInvoiceItems = DB::table('housefile_invoices_item_details')->where('invoice_id',$oldInvId)->get();
            foreach ($dataInvoiceItems as $key => $value) {
                $value = (array) $value;
                $value['id'] = null;
                $value['invoice_id'] = $model->id;
                $modelInvoiceItemDetails = InvoiceItemDetails::create($value);
            }

            // Payment
            $dataInvoicePaymentItems = DB::table('invoice_payments')->where('invoice_id',$oldInvId)->where('cargo_id',$model->cargo_id)->get();
            foreach ($dataInvoicePaymentItems as $key => $value) {
                DB::table('invoice_payments')
                ->where('id', $value->id)
                ->update(['invoice_id' => $model->id]);
            }
        } */
        pre("Exit");
    }

    public function expensenotificationoffile($expenseId = null, $flag = null)
    {
        $singleExpense = DB::table('expenses')->where('expense_id', $expenseId)->first();
        if ($flag == 'cargoExpense') {
            $moduleData = DB::table('cargo')->where('id', $singleExpense->cargo_id)->first();
            $expenseData = DB::table('expenses')->where('cargo_id', $singleExpense->cargo_id)->get();
        } else if ($flag == 'housefileExpense') {
            $moduleData = DB::table('hawb_files')->where('id', $singleExpense->house_file_id)->first();
            $expenseData = DB::table('expenses')->where('house_file_id', $singleExpense->house_file_id)->get();
        } else if ($flag == 'upsExpense') {
            $moduleData = DB::table('ups_details')->where('id', $singleExpense->ups_details_id)->first();
            $expenseData = DB::table('expenses')->where('ups_details_id', $singleExpense->ups_details_id)->get();
        } else if ($flag == 'upsMasterExpense') {
            $moduleData = DB::table('ups_master')->where('id', $singleExpense->ups_master_id)->first();
            $expenseData = DB::table('expenses')->where('ups_master_id', $singleExpense->ups_master_id)->get();
        } else if ($flag == 'aeropostExpense') {
            $moduleData = DB::table('aeropost')->where('id', $singleExpense->aeropost_id)->first();
            $expenseData = DB::table('expenses')->where('aeropost_id', $singleExpense->aeropost_id)->get();
        } else if ($flag == 'aeropostMasterExpense') {
            $moduleData = DB::table('aeropost_master')->where('id', $singleExpense->aeropost_master_id)->first();
            $expenseData = DB::table('expenses')->where('aeropost_master_id', $singleExpense->aeropost_master_id)->get();
        } else if ($flag == 'ccpackExpense') {
            $moduleData = DB::table('ccpack')->where('id', $singleExpense->ccpack_id)->first();
            $expenseData = DB::table('expenses')->where('ccpack_id', $singleExpense->ccpack_id)->get();
        } else if ($flag == 'ccpackMasterExpense') {
            $moduleData = DB::table('ccpack_master')->where('id', $singleExpense->ccpack_master_id)->first();
            $expenseData = DB::table('expenses')->where('ccpack_master_id', $singleExpense->ccpack_master_id)->get();
        }
        return view("common.expensenotificationoffile", ['flag' => $flag, 'expenseData' => $expenseData, 'singleExpense' => $singleExpense, 'expenseId' => $expenseId, 'moduleData' => $moduleData]);
    }

    public function approveallexpense($moduleId = null, $expenseId = null, $flag = null)
    {
        if ($flag == 'cargoExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('cargo_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cargo';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            /* Expense::where('cargo_id',$moduleId)->update(['display_notification_admin'=>0]);        
            DB::table('expenses')->where('cargo_id',$moduleId)->where('expense_request','Requested')->update(['expense_request'=>'Approved','approved_by'=>auth()->user()->id]); */
        } else if ($flag == 'housefileExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('house_file_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_house_file_expense' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'houseFile';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }

            /* Expense::where('house_file_id',$moduleId)->update(['display_notification_admin'=>0]);        
            DB::table('expenses')->where('house_file_id',$moduleId)->where('expense_request','Requested')->update(['expense_request'=>'Approved','approved_by'=>auth()->user()->id]); */
        } else if ($flag == 'upsExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('ups_details_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ups' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        } else if ($flag == 'upsMasterExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('ups_master_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ups_master' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'upsMaster';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        } else if ($flag == 'aeropostMasterExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('aeropost_master_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_aeropost_master' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'aeropostMaster';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        } else if ($flag == 'aeropostExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('aeropost_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_aeropost' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'aeropost';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        } else if ($flag == 'ccpackExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('ccpack_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ccpack' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ccpack';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        } else if ($flag == 'ccpackMasterExpense') {
            $dataExpenses = DB::table('expenses')
                ->select(DB::raw('group_concat(expense_id) as expense_id'))
                ->where('expense_request', 'Requested')->where('ccpack_master_id', $moduleId)->first();
            $ids = explode(',', $dataExpenses->expense_id);
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ccpack_master' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ccpackMaster';
                    $modelActivities->related_id = $moduleId;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
        }
        Session::flash('flash_message', 'Requested expense has been change to Approved.');
        return redirect()->route('expensenotificationoffile', [$expenseId, $flag]);
    }

    public function approveallselectedexpense()
    {
        $flag = $_POST['flag'];
        $ids = explode(',', $_POST['ids']);
        if ($flag == 'cargoExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'cargo';
                    $modelActivities->related_id = $expenseData->cargo_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('expenses');
        } else if ($flag == 'housefileExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_house_file_expense' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'houseFile';
                    $modelActivities->related_id = $expenseData->house_file_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('housefileexpenses');
        } else if ($flag == 'upsExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ups' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ups';
                    $modelActivities->related_id = $expenseData->ups_details_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('upsexpenses');
        } else if ($flag == 'upsMasterExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ups_master' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'upsMaster';
                    $modelActivities->related_id = $expenseData->ups_master_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('upsmasterexpenses');
        } else if ($flag == 'aeropostMasterExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_aeropost_master' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'aeropostMaster';
                    $modelActivities->related_id = $expenseData->aeropost_master_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('upsmasterexpenses');
        } else if ($flag == 'aeropostExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_aeropost' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'aeropost';
                    $modelActivities->related_id = $expenseData->aeropost_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('aerpostexpenses');
        } else if ($flag == 'ccpackExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ccpack' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ccpack';
                    $modelActivities->related_id = $expenseData->ccpack_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('ccpackexpenses');
        } else if ($flag == 'ccpackMasterExpense') {
            DB::table('expenses')->whereIn('expense_id', $ids)->update([
                'expense_request' => 'Approved',
                'approved_by' => auth()->user()->id,
                'display_notification_cashier_for_ccpack_master' => '1',
                //'display_notification_agent' => '1',
                'display_notification_admin' => '0',
                'notification_date_time' => date('Y-m-d H:i:s'),
            ]);

            foreach ($ids as $k => $v) {
                $expenseData = DB::table('expenses')->where('expense_id', $v)->first();
                if (!empty($expenseData)) {
                    // Store expense activity on file level
                    $modelActivities = new Activities;
                    $modelActivities->type = 'ccpackMaster';
                    $modelActivities->related_id = $expenseData->ccpack_master_id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Expense #' . $expenseData->voucher_number . ' has been approved';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
            }
            Session::flash('flash_message', 'Requested expense has been change to Approved.');
            return redirect()->route('upsmasterexpenses');
        }
    }

    public function approveallselectedadministrationexpense()
    {
        $ids = explode(',', $_POST['ids']);
        DB::table('other_expenses')->whereIn('id', $ids)->update([
            'expense_request' => 'Approved',
            'approved_by' => auth()->user()->id,
            'display_notification_cashier' => '1',
            'display_notification_admin' => '0',
            'notification_date_time' => date('Y-m-d H:i:s'),
        ]);
        Session::flash('flash_message', 'Requested expense has been change to Approved.');
        return redirect()->route('otherexpenses');
    }



    public function checkbackground()
    {
        $parts = parse_url($url);
        $parts['path'] = $parts['path'] . '/' . str_replace('model=', '', $parts['query']);
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
        if (!$fp) {
            return false;
        } else {
            $out = "GET " . $parts['path'] . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            if (isset($parts['query']))
                $out .= "Content-Length: " . strlen($parts['query']) . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            if (isset($parts['query']))
                $out .= $parts['query'];
            fwrite($fp, str_replace('model=', '/', $out));
            fclose($fp);
            return true;
        }
    }

    public function checkbackgroundactioncalled()
    {
    }

    public function checkreceipt()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 30000);
        //$data = DB::table('invoice_payments')->where('id','<=',296)->orderBy('id','desc')->get();
        $data = DB::table('invoice_payments')->orderBy('id', 'desc')->get();
        $datas = array();
        $allIDs = array();
        foreach ($data as $k => $v) {
            if (in_array($v->id, $allIDs))
                continue;

            $seconds = -5;
            $date_now = $v->created_at;

            $time = date('Y-m-d H:i:s', strtotime($v->created_at));
            $newTime =  date("Y-m-d H:i:s", (strtotime(date($v->created_at)) + $seconds));

            pre($time, 1);
            pre($newTime, 1);

            $dataN = DB::table('invoice_payments')->select(DB::raw("group_concat(id) as ids,id,SUBSTRING_INDEX(group_concat(id), ',', -1) AS receiptNumber"))->whereBetween('created_at', array($newTime, $time))->orderBy('id', 'desc')->first();
            $allIDs = explode(',', $dataN->ids);

            $exploadedIds = explode(',', $dataN->ids);
            $receiptNumber = '10' . $dataN->receiptNumber;
            DB::table('invoice_payments')->whereIn('id', $exploadedIds)->update(['receipt_number' => $receiptNumber]);

            //pre($dataN,1);

            /*pre($time,1);
            pre($newTime); */
        }
    }

    public function scriptToCreateUpsMasterFile()
    {
        //SELECT group_concat(id),file_number,count(*) as total,arrival_date,no_manifeste,awb_number FROM cargo_live_v1.ups_details group by arrival_date order by id desc;
    }

    public function scriptToRestoreAeropostFile()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300000);
        //UPDATE aeropost SET account = RTRIM(account)

        // ---------- Aeropost Table ------ //
        // Remove all the data from aerpost table
        // Import aeropost table from staging

        /* $dataAeropostFromBackup = DB::table('aeropost_1706')->get();
        foreach($dataAeropostFromBackup as $k => $v)
        {
            $data = Aeropost::insert((array) $v);
        } */
        // End---------- Aeropost Table ------ //

        // ---------- Aeropost Commission Table ------ //
        // Remove all the data from aeropost_freight_commission table
        // Import aeropost_freight_commission table from staging

        /* $dataAeropostCommissionFromBackup = DB::table('aeropost_freight_commission_1706')->get();
        foreach ($dataAeropostCommissionFromBackup as $k1 => $v1) {
            $data = AeropostFreightCommission::insert((array) $v1);
        } */
        // End---------- Aeropost Commission Table ------ //

        // ---------- Aeropost Expenses Table ------ //
        // Remove aeropost data from  expenses table
        // LIVE
        //DELETE FROM cargo_live_v1.expenses where aeropost_id is not null and expense_id > 6491
        // Staging    
        // DELETE FROM `expenses` WHERE `aeropost_id` IS NOT NULL

        /* $dataAeropostExpenseFromBackup = DB::table('expenses_1706')->whereNotNull('aeropost_id')->get();
        foreach ($dataAeropostExpenseFromBackup as $k => $v) {
            $v1 = (array) $v;
            $getLastExpense = DB::table('expenses')->orderBy('expense_id', 'desc')->first();
            if (empty($getLastExpense))
                $v1['voucher_number'] = '1001';
            else
                $v1['voucher_number'] = $getLastExpense->voucher_number + 1;
            $data = Expense::create($v1);

            $dataAeropostExpenseDetailFromBackup = DB::table('expense_details_1706')->where('expense_id', $v->expense_id)->first();
            if(!empty($dataAeropostExpenseDetailFromBackup))
            {
                    $vv1 = (array) $dataAeropostExpenseDetailFromBackup;
                    $vv1['expense_id'] = $data->expense_id;
                    ExpenseDetails::create($vv1);
            }
        }  */
        // End---------- Aeropost Expenses Table ------ //

        // ---------- Aeropost Invoice Table ------ //
        // Remove aeropost data from  invoices table
        // DELETE FROM cargo_live_v1.invoices WHERE `aeropost_id` IS NOT NULL
        //-- Data from  invoice_item_details deleted automatically
        // DELETE FROM cargo_live_v1.invoice_payments WHERE `aeropost_id` IS NOT NULL and id > 5901;


        $dataAeropostInviceFromBackup = DB::table('invoices_1706')->whereNotNull('aeropost_id')->get();
        $i = 1;
        foreach ($dataAeropostInviceFromBackup as $k => $v) {
            $v1 = (array) $v;
            $getLastInvoice = DB::table('invoices')->orderBy('id', 'desc')->whereNull('flag_invoice')->first();
            if (empty($getLastInvoice)) {
                $v1['bill_no'] = 'AP-5001';
            } else {
                $ab = 'AP-';
                $ab .= substr($getLastInvoice->bill_no, 3) + 1;
                $v1['bill_no'] = $ab;
            }
            $data = AeropostInvoices::create($v1);

            $dataAeropostInvoiceDetailFromBackup = DB::table('invoice_item_details_1706')->where('invoice_id', $v->id)->get();
            if (!empty($dataAeropostInvoiceDetailFromBackup)) {
                foreach ($dataAeropostInvoiceDetailFromBackup as $kk1 => $vv1) {
                    $vv2 = (array) $vv1;
                    $vv2['invoice_id'] = $data->id;
                    AeropostInvoiceItemDetails::create($vv2);
                }
            }

            DB::table('invoice_payments')->where('invoice_id', $v->id)->update(['invoice_id' => $data->id]);

            $i++;
        }

        // END---------- Aeropost Invoice Table ------ //



        pre("Exit");


        //ALTER TABLE table_name RENAME TO new_table_name;

        //$dataExpense = DB::table('invoice_payments')->orderBy('id', 'desc')->get();
    }
}
