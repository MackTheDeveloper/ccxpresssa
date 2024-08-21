<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Validator;
use Response;
use App\User;
use App\CurrencyExchange;
use App\Admin;
use QuickBooksOnline\API\DataService\DataService;
use Config;
use App\Clients;
use App\Vendors;
use App\Costs;
use App\BillingItems;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $checkPermission = User::checkPermission(['listing_currencies'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $items = DB::table('currency')->where('deleted', '0')->orderBy('id', 'desc')->get();
        return view("currency.index", ['items' => $items]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkPermission = User::checkPermission(['add_currencies'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = new Currency;
        $currencies = DB::table('currency')->where('deleted', '0')->orderBy('id', 'desc')->pluck('name', 'id');;
        return view('currency.form', ['model' => $model, 'currencies' => $currencies]);
    }

    public function getcurrencydd()
    {
        $count = $_POST['countcurrency'];
        $currencies = DB::table('currency')->where('deleted', '0')->orderBy('id', 'desc')->get();
        $str = '<option value="">Select Currency</option>';
        foreach ($currencies as $key => $value) {
            $str .= '<option value="' . $value->id . '">' . $value->name . '</option>';
        }
        return $str;
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
        $input['created_at'] = gmdate("Y-m-d H:i:s");
        $model = Currency::create($input);

        $fromCurrency = $model->id;
        foreach ($input['to_currency'] as $key => $value) {
            $modelExchange = new CurrencyExchange();
            $modelExchange->from_currency = $fromCurrency;
            $modelExchange->to_currency = $value;
            $modelExchange->exchange_value = $input['exchange_value'][$key];
            $modelExchange->save();
        }

        // Add Currencies to QB
        /*if(isset($_SESSION['sessionAccessToken'])){
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('currencies',$model);
        } */


        if (isset($_SESSION['sessionAccessToken'])) {
            $fData['id'] = $model->id;
            $fData['module'] = '12';
            $fData['flagModule'] = 'currencies';
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
        return redirect('currency');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function show(Currency $currency)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function edit(Currency $currency, $id)
    {
        $checkPermission = User::checkPermission(['update_currencies'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $model = DB::table('currency')->where('id', $id)->first();
        $currencies = DB::table('currency')->where('deleted', '0')->where('id', '<>', $id)->orderBy('id', 'desc')->pluck('name', 'id');
        $dataCurrencyExchange  = DB::table('currency_exchange')->where('from_currency', $id)->get();
        return view("currency.form", ['model' => $model, 'currencies' => $currencies, 'dataCurrencyExchange' => $dataCurrencyExchange]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Currency $currency, $id)
    {
        $model = Currency::find($id);
        $request['updated_at'] = gmdate("Y-m-d H:i:s");
        $input = $request->all();
        $model->update($input);

        $fromCurrency = $id;
        CurrencyExchange::where('from_currency', $id)->delete();
        foreach ($input['to_currency'] as $key => $value) {
            $modelExchange = new CurrencyExchange();
            $modelExchange->from_currency = $fromCurrency;
            $modelExchange->to_currency = $value;
            $modelExchange->exchange_value = $input['exchange_value'][$key];
            $modelExchange->save();
        }

        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('currency');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Currency  $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency, $id)
    {
        $model = Currency::where('id', $id)->update(['deleted' => 1, 'deleted_at' => gmdate("Y-m-d H:i:s")]);
    }

    public function syncBillingpartyFromQBToLocal()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $config = Config::get('app.QB');
            $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                'baseUrl' => "development"
                //'baseUrl' => "https://quickbooks.api.intuit.com"
            ));

            $accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $companyInfo = $dataService->getCompanyInfo();

            $totalCustomer = $dataService->Query("select COUNT(*) from Customer");

            $startPosition = 1;
            $maxResult = 100;
            $devided = (int) ($totalCustomer / 100);
            for ($i = 0; $i < $devided; $i++) {
                if ($i > 0) {
                    $startPosition = 100 * $i + 1;
                    $maxResult = 100;
                }
                $customerInfo = $dataService->Query("select Id,CompanyName from Customer STARTPOSITION $startPosition MAXRESULTS $maxResult");
                foreach ($customerInfo as $key => $value) {
                    $qbId = $value->Id;
                    $companyName = $value->CompanyName;

                    $billingpartyData = DB::table('clients')
                        ->where('client_flag', 'B')
                        ->where('company_name', $companyName)
                        ->where('deleted', '0')
                        ->whereNull('quick_book_id')
                        ->first();
                    if (!empty($billingpartyData)) {
                        Clients::where('id', $billingpartyData->id)
                            ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                    }
                }
                if ($i == $devided - 1) {
                    $startPosition = $startPosition + 100;
                    $maxResult = 100;

                    $customerInfo = $dataService->Query("select Id,CompanyName from Customer STARTPOSITION $startPosition MAXRESULTS $maxResult");
                    foreach ($customerInfo as $key => $value) {
                        $qbId = $value->Id;
                        $companyName = $value->CompanyName;

                        $billingpartyData = DB::table('clients')
                            ->where('client_flag', 'B')
                            ->where('company_name', $companyName)
                            ->where('deleted', '0')
                            ->whereNull('quick_book_id')
                            ->first();
                        if (!empty($billingpartyData)) {
                            Clients::where('id', $billingpartyData->id)
                                ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                        }
                    }
                }
            }
        }
    }

    public function syncBillingpartyFromLocalToQB()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $customerData = DB::table('clients')
                ->where('deleted', '0')
                ->where('client_flag', 'B')
                ->whereNull('quick_book_id')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            foreach ($customerData as $k => $v) {
                $fData['id'] = $v->id;
                $fData['module'] = '11';

                $fData['flagModule'] = 'client';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
    }

    public function syncVendorsFromQBToLocal()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $config = Config::get('app.QB');
            $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                'baseUrl' => "development"
                //'baseUrl' => "https://quickbooks.api.intuit.com"
            ));

            $accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $companyInfo = $dataService->getCompanyInfo();

            $totalVendor = $dataService->Query("select COUNT(*) from vendor");

            $startPosition = 1;
            $maxResult = 100;
            $devided = (int) ($totalVendor / 100);
            for ($i = 0; $i < $devided; $i++) {
                if ($i > 0) {
                    $startPosition = 100 * $i + 1;
                    $maxResult = 100;
                }
                $vendorInfo = $dataService->Query("select Id,CompanyName from vendor STARTPOSITION $startPosition MAXRESULTS $maxResult");
                foreach ($vendorInfo as $key => $value) {
                    $qbId = $value->Id;
                    $companyName = $value->CompanyName;

                    $vendorData = DB::table('vendors')
                        ->where('company_name', $companyName)
                        ->where('deleted', '0')
                        ->whereNull('quick_book_id')
                        ->first();
                    if (!empty($vendorData)) {
                        Vendors::where('id', $vendorData->id)
                            ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                    }
                }
                if ($i == $devided - 1) {
                    $startPosition = $startPosition + 100;
                    $maxResult = 100;

                    $vendorInfo = $dataService->Query("select Id,CompanyName from vendor STARTPOSITION $startPosition MAXRESULTS $maxResult");
                    foreach ($vendorInfo as $key => $value) {
                        $qbId = $value->Id;
                        $companyName = $value->CompanyName;

                        $vendorData = DB::table('vendors')
                            ->where('company_name', $companyName)
                            ->where('deleted', '0')
                            ->whereNull('quick_book_id')
                            ->first();
                        if (!empty($vendorData)) {
                            Vendors::where('id', $vendorData->id)
                                ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                        }
                    }
                }
            }
        }
    }

    public function syncVendorsFromLocalToQB()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $vendorData = DB::table('vendors')
                ->where('deleted', '0')
                ->whereNull('quick_book_id')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            foreach ($vendorData as $k => $v) {
                $fData['id'] = $v->id;
                $fData['module'] = '1';

                $fData['flagModule'] = 'vendor';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
    }

    public function syncCostItemsFromQBToLocal()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $config = Config::get('app.QB');
            $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                'baseUrl' => "development"
                //'baseUrl' => "https://quickbooks.api.intuit.com"
            ));

            $accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $companyInfo = $dataService->getCompanyInfo();

            $totalCosts = $dataService->Query("select COUNT(*) from Account where AccountType = 'Expense'");

            $startPosition = 1;
            $maxResult = 100;
            $devided = (int) ($totalCosts / 100);
            for ($i = 0; $i < $devided; $i++) {
                if ($i > 0) {
                    $startPosition = 100 * $i + 1;
                    $maxResult = 100;
                }
                $costInfo = $dataService->Query("select Id,Name,AccountType from Account where AccountType = 'Expense' STARTPOSITION $startPosition MAXRESULTS $maxResult");
                foreach ($costInfo as $key => $value) {
                    $qbId = $value->Id;
                    $name = $value->Name;

                    $costData = DB::table('costs')
                        ->where('cost_name', $name)
                        ->where('deleted', '0')
                        ->whereNull('quick_book_id')
                        ->first();
                    if (!empty($costData)) {
                        Costs::where('id', $costData->id)
                            ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                    }
                }
                if ($i == $devided - 1) {
                    $startPosition = $startPosition + 100;
                    $maxResult = 100;

                    $costInfo = $dataService->Query("select Id,Name,AccountType from Account where AccountType = 'Expense' STARTPOSITION $startPosition MAXRESULTS $maxResult");
                    foreach ($costInfo as $key => $value) {
                        $qbId = $value->Id;
                        $name = $value->Name;

                        $costData = DB::table('costs')
                            ->where('cost_name', $name)
                            ->where('deleted', '0')
                            ->whereNull('quick_book_id')
                            ->first();
                        if (!empty($costData)) {
                            Costs::where('id', $costData->id)
                                ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                        }
                    }
                }
            }
        }
    }

    public function syncCostItemsFromLocalToQB()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $costsData = DB::table('costs')
                ->where('deleted', '0')
                ->whereNull('quick_book_id')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            foreach ($costsData as $k => $v) {
                $fData['id'] = $v->id;
                $fData['module'] = '0';

                $fData['flagModule'] = 'cost';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
    }

    public function syncBillingItemsFromQBToLocal()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $config = Config::get('app.QB');
            $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                'baseUrl' => "development"
                //'baseUrl' => "https://quickbooks.api.intuit.com"
            ));

            $accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $companyInfo = $dataService->getCompanyInfo();

            $totalBillingItems = $dataService->Query("select COUNT(*) from Item where Type = 'Service'");

            $startPosition = 1;
            $maxResult = 100;
            $devided = (int) ($totalBillingItems / 100);
            for ($i = 0; $i < $devided; $i++) {
                if ($i > 0) {
                    $startPosition = 100 * $i + 1;
                    $maxResult = 100;
                }
                $billingItemsInfo = $dataService->Query("select Id,Name,Type from Item where Type = 'Service' STARTPOSITION $startPosition MAXRESULTS $maxResult");
                foreach ($billingItemsInfo as $key => $value) {
                    $qbId = $value->Id;
                    $name = $value->Name;

                    $billingItemsData = DB::table('billing_items')
                        ->where('billing_name', $name)
                        ->where('deleted', '0')
                        ->whereNull('quick_book_id')
                        ->first();
                    if (!empty($billingItemsData)) {
                        BillingItems::where('id', $billingItemsData->id)
                            ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                    }
                }
                if ($i == $devided - 1) {
                    $startPosition = $startPosition + 100;
                    $maxResult = 100;

                    $billingItemsInfo = $dataService->Query("select Id,Name,Type from Item where Type = 'Service' STARTPOSITION $startPosition MAXRESULTS $maxResult");
                    foreach ($billingItemsInfo as $key => $value) {
                        $qbId = $value->Id;
                        $name = $value->Name;

                        $billingItemsData = DB::table('billing_items')
                            ->where('billing_name', $name)
                            ->where('deleted', '0')
                            ->whereNull('quick_book_id')
                            ->first();
                        if (!empty($billingItemsData)) {
                            BillingItems::where('id', $billingItemsData->id)
                                ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                        }
                    }
                }
            }
        }
    }

    public function syncBillingItemsFromLocalToQB()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $billingItemsData = DB::table('billing_items')
                ->where('deleted', '0')
                ->whereNull('quick_book_id')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            foreach ($billingItemsData as $k => $v) {
                $fData['id'] = $v->id;
                $fData['module'] = '3';

                $fData['flagModule'] = 'billing-item';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
    }

    public function syncAccountsFromQBToLocal()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $config = Config::get('app.QB');
            $dataService = DataService::Configure(array(
                'auth_mode' => 'oauth2',
                'ClientID' => $config['client_id'],
                'ClientSecret' =>  $config['client_secret'],
                'RedirectURI' => $config['oauth_redirect_uri'],
                'scope' => $config['oauth_scope'],
                'baseUrl' => "development"
                //'baseUrl' => "https://quickbooks.api.intuit.com"
            ));

            $accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $companyInfo = $dataService->getCompanyInfo();


            $totalAccounts = $dataService->Query("select COUNT(*) from Account where AccountType IN ('Credit Card','Bank')");

            $startPosition = 1;
            $maxResult = 100;
            $devided = (int) ($totalAccounts / 100);
            for ($i = 0; $i < $devided; $i++) {
                if ($i > 0) {
                    $startPosition = 100 * $i + 1;
                    $maxResult = 100;
                }
                $accountsInfo = $dataService->Query("select Id,Name,AccountType from Account where AccountType IN ('Credit Card','Bank') STARTPOSITION $startPosition MAXRESULTS $maxResult");
                foreach ($accountsInfo as $key => $value) {
                    $qbId = $value->Id;
                    $name = $value->Name;

                    $accountsData = DB::table('cashcredit')
                        ->where('name', $name)
                        ->where('deleted', '0')
                        ->whereNull('quick_book_id')
                        ->first();
                    if (!empty($accountsData)) {
                        BillingItems::where('id', $accountsData->id)
                            ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                    }
                }
                if ($i == $devided - 1) {
                    $startPosition = $startPosition + 100;
                    $maxResult = 100;

                    $accountsInfo = $dataService->Query("select Id,Name,AccountType from Account where AccountType IN ('Credit Card','Bank') STARTPOSITION $startPosition MAXRESULTS $maxResult");
                    foreach ($accountsInfo as $key => $value) {
                        $qbId = $value->Id;
                        $name = $value->Name;

                        $accountsData = DB::table('cashcredit')
                            ->where('name', $name)
                            ->where('deleted', '0')
                            ->whereNull('quick_book_id')
                            ->first();
                        if (!empty($accountsData)) {
                            BillingItems::where('id', $accountsData->id)
                                ->update(['quick_book_id' => $qbId, 'qb_sync' => 1]);
                        }
                    }
                }
            }
        }
    }

    public function syncAccountsFromLocalToQB()
    {
        session_start();
        if (isset($_SESSION['sessionAccessToken'])) {
            $billingItemsData = DB::table('cashcredit')
                ->where('deleted', '0')
                ->whereNull('quick_book_id')
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();

            foreach ($billingItemsData as $k => $v) {
                $fData['id'] = $v->id;
                $fData['module'] = '2';

                $fData['flagModule'] = 'account';
                $fData['sessionAccessToken'] = $_SESSION['sessionAccessToken'];

                $newModel = base64_encode(serialize($fData));
                $urlAction = url('call/qb?model=' . $newModel);

                $adminModel = new Admin;
                $adminModel->backgroundPost($urlAction);
            }
        }
    }
}
