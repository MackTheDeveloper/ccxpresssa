<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Config;
use App\Cargo;
use App\Ups;
use App\Aeropost;
use App\User;
use App\Clients;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Data\IPPInvoice;
use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Data\IPPPurchase;
use QuickBooksOnline\API\Data\IPPVendor;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Data\IPPAccount;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Data\IPPItem;
use QuickBooksOnline\API\Facades\Item;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Facades\CompanyCurrency;
use QuickBooksOnline\API\Data\IPPCompanyCurrency;
use App\quickbookErrorLog;

class Admin extends Model
{

    public $config_errors;


    public function __construct()
    {
        $this->config_errors = Config::get('app.quickbook_modules');
    }

    protected function getNotificationForAdmin($flag = "")
    {
        if ($flag != 'All') {

            $userId = auth()->user()->department;
            $expenseNoti = DB::table('expenses')
                //->where('display_notification_admin',1)
                ->whereRaw("FIND_IN_SET($userId,admin_manager_role)")
                ->where('expense_request', 'Requested')
                ->where('deleted', 0);
            /* ->get()
                ->toArray(); */
            if ($flag == 'displayOnBell') {
                $expenseNoti = $expenseNoti->limit(50)->orderBy('notification_date_time', 'desc')->get()->toArray();
            } else {
                $expenseNoti = $expenseNoti->get()->toArray();
            }


            foreach ($expenseNoti as $key => $value) {
                if (!empty($value->ups_details_id)) {
                    $modalCommonC = new Ups;
                    $dataCommonC = $modalCommonC->getUpsData($value->ups_details_id);
                    $expenseNoti[$key]->flagModule = 'UpsExpense';
                } else if (!empty($value->ups_master_id)) {
                    $modalCommonC = new UpsMaster();
                    $dataCommonC = $modalCommonC->getMasterUpsData($value->ups_master_id);
                    $expenseNoti[$key]->flagModule = 'UpsMasterExpense';
                } else if (!empty($value->house_file_id)) {
                    $modalCommonC = new HawbFiles;
                    $dataCommonC = $modalCommonC->getHouseFileData($value->house_file_id);
                    $expenseNoti[$key]->flagModule = 'houseFileExpense';
                } else if (!empty($value->aeropost_id)) {
                    $modalCommonC = new Aeropost;
                    $dataCommonC = $modalCommonC->getAeropostData($value->aeropost_id);
                    $expenseNoti[$key]->flagModule = 'aeropostExpense';
                } else if (!empty($value->aeropost_master_id)) {
                    $modalCommonC = new AeropostMaster();
                    $dataCommonC = $modalCommonC->getMasterAeropostData($value->aeropost_master_id);
                    $expenseNoti[$key]->flagModule = 'AeropostMasterExpense';
                } else if (!empty($value->ccpack_id)) {
                    $modalCommonC = new ccpack;
                    $dataCommonC = $modalCommonC->getccpackdetail($value->ccpack_id);
                    $expenseNoti[$key]->flagModule = 'ccpackExpense';
                } else if (!empty($value->ccpack_master_id)) {
                    $modalCommonC = new CcpackMaster();
                    $dataCommonC = $modalCommonC->getMasterCcpackData($value->ccpack_master_id);
                    $expenseNoti[$key]->flagModule = 'CcpackMasterExpense';
                } else {
                    $modalCommonC = new Cargo;
                    $dataCommonC = $modalCommonC->getCargoData($value->cargo_id);
                    $expenseNoti[$key]->flagModule = 'CargoExpense';
                }

                $modalUser = new User;
                $dataUser = $modalUser->getUserName($value->request_by);
                $dataUserCashier = $modalUser->getUserName($value->updated_by);

                if (empty($dataUser)) {
                    $requestedByName = '';
                } else {
                    $requestedByName = $dataUser->name;
                }


                if (empty($dataUserCashier)) {
                    $cashierName = '';
                } else {
                    $cashierName = $dataUserCashier->name;
                }


                if ($value->expense_request != 'Requested')
                    $expenseNoti[$key]->notificationMessage = '#' . (!empty($dataCommonC->file_number) ? $dataCommonC->file_number : '') . ' (' . $value->bl_awb . ') : Expense status has been changed to ' . $value->expense_request . ' - Changed By ' . $cashierName;
                else
                    $expenseNoti[$key]->notificationMessage = '#' . (!empty($dataCommonC->file_number) ? $dataCommonC->file_number : '') . ' (' . $value->bl_awb . ') : Expense has been requested by ' . $requestedByName;

                $expenseNoti[$key]->notificationStatus = $value->display_notification_admin;
                $expenseNoti[$key]->notificationDateTime = $value->notification_date_time;
                $expenseNoti[$key]->awb_no = $value->bl_awb;
                if (!empty($dataCommonC)) {
                    $Client = new Clients();
                    if (!empty($value->ups_details_id)) {
                        if ($dataCommonC->courier_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->ups_master_id)) {
                        if ($dataCommonC->ups_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->aeropost_master_id)) {
                        $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                        if (!empty($dataShipper))
                            $expenseNoti[$key]->client = $dataShipper->company_name;
                        else
                            $expenseNoti[$key]->client = '-';
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->aeropost_id)) {
                        $expenseNoti[$key]->client = $value->consignee;
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->ccpack_id)) {
                        if ($dataCommonC->ccpack_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else if (!empty($value->ccpack_master_id)) {
                        $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                        if (!empty($dataShipper))
                            $expenseNoti[$key]->client = $dataShipper->company_name;
                        else
                            $expenseNoti[$key]->client = '-';
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    } else {
                        if ($dataCommonC->cargo_operation_type == 1) {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else if ($dataCommonC->cargo_operation_type == 2) {
                            $dataConsignee = $Client->getClientData($dataCommonC->shipper_name);
                            if (!empty($dataConsignee))
                                $expenseNoti[$key]->client = $dataConsignee->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        } else {
                            $dataShipper = $Client->getClientData($dataCommonC->consignee_name);
                            if (!empty($dataShipper))
                                $expenseNoti[$key]->client = $dataShipper->company_name;
                            else
                                $expenseNoti[$key]->client = '-';
                        }
                        $expenseNoti[$key]->file_number = $dataCommonC->file_number;
                    }
                } else {
                    $expenseNoti[$key]->client = '-';
                }
            }
            $expenseNoti = (array) $expenseNoti;

            $expenseAdministrativeNoti = DB::table('other_expenses')
                //->where('display_notification_admin',1)
                ->whereRaw("FIND_IN_SET($userId,admin_manager_role)")
                ->where('expense_request', 'Requested')
                ->where('deleted', 0);
            /* ->get()
                ->toArray(); */

            if ($flag == 'displayOnBell') {
                $expenseAdministrativeNoti = $expenseAdministrativeNoti->limit(50)->orderBy('notification_date_time', 'desc')->get()->toArray();
            } else {
                $expenseAdministrativeNoti = $expenseAdministrativeNoti->get()->toArray();
            }

            foreach ($expenseAdministrativeNoti as $key => $value) {
                $expenseAdministrativeNoti[$key]->flagModule = 'administrationExpense';
                $modalUser = new User;
                $dataUser = $modalUser->getUserName($value->request_by);
                $dataUserCashier = $modalUser->getUserName($value->updated_by);

                if (empty($dataUser)) {
                    $requestedByName = '';
                } else {
                    $requestedByName = $dataUser->name;
                }

                if (empty($dataUserCashier)) {
                    $cashierName = '';
                } else {
                    $cashierName = $dataUserCashier->name;
                }

                if ($value->expense_request != 'Requested')
                    $expenseAdministrativeNoti[$key]->notificationMessage = "Administration Expense status has been changed to " . $value->expense_request . ' - Changed By ' . $cashierName . " (voucher #" . $value->voucher_number . ")";
                else
                    $expenseAdministrativeNoti[$key]->notificationMessage = "Administration Expense has been requested by " . $requestedByName . " (voucher #" . $value->voucher_number . ")";

                $expenseAdministrativeNoti[$key]->notificationStatus = $value->display_notification_admin;
                $expenseAdministrativeNoti[$key]->notificationDateTime = $value->notification_date_time;
            }
            $expenseAdministrativeNoti = (array) $expenseAdministrativeNoti;


            $dataAll = array_merge($expenseNoti, $expenseAdministrativeNoti);
            return $dataAll;
        } else {
            $userId = auth()->user()->department;
            $countExpenseNoti = DB::table('expenses')
                ->where('display_notification_admin', 1)
                ->where('expense_request', 'Requested')
                ->whereRaw("FIND_IN_SET($userId,admin_manager_role)")
                ->where('deleted', 0)
                ->count();

            $countAdministrativeExpenseNoti = DB::table('other_expenses')
                ->where('display_notification_admin', 1)
                ->where('expense_request', 'Requested')
                ->whereRaw("FIND_IN_SET($userId,admin_manager_role)")
                ->where('deleted', 0)
                ->count();

            return $countExpenseNoti + $countAdministrativeExpenseNoti;
        }
    }

    public function qbApiCall($flag = null, $requestedData = array())
    {
        switch ($flag) {
            case 'invoice':
                return $this->storeInvoiceToQB($requestedData);
            case 'updateInvoice':
                return $this->updateInvoiceToQB($requestedData);
            case 'deleteInvoice':
                return $this->deleteInvoiceToQB($requestedData);
            case 'expenses':
                return $this->storeExpenseToQB($requestedData);
            case 'updateExpense':
                return $this->updateExpenseToQB($requestedData);
            case 'deleteExpense':
                return $this->deleteExpenseToQB($requestedData);
            case 'vendor':
                return $this->storeVendorToQB($requestedData);
            case 'updateVendor':
                return $this->updateVendorToQB($requestedData);
            case 'deleteVendor':
                return $this->deleteVendorToQB($requestedData);
            case 'cost':
                return $this->storeCostToQB($requestedData);
            case 'updateCost':
                return $this->updateCostToQB($requestedData);
            case 'deleteCost':
                return $this->deleteCostToQB($requestedData);
            case 'account':
                return $this->storeAccountToQB($requestedData);
            case 'updateAccount':
                return $this->updateAccountToQB($requestedData);
            case 'deleteAccount':
                return $this->deleteAccountToQB($requestedData);
            case 'billing-item':
                return $this->storeBillingItemToQB($requestedData);
            case 'update-billing-item':
                return $this->updateBillingItemToQB($requestedData);
            case 'delete-billing-item':
                return $this->deleteBillingItemToQB($requestedData);
            case 'client':
                return $this->storeClientToQB($requestedData);
            case 'updateClient':
                return $this->updateClientToQB($requestedData);
            case 'deleteClient':
                return $this->deleteClientToQB($requestedData);
            case 'currencies':
                return $this->storeCurrencyToQB($requestedData);
            case 'otherAccount':
                return $this->storeOtherAccountToQB($requestedData);
            case 'updateOtherAccount':
                return $this->updateOtherAccountToQB($requestedData);
            case 'deleteOtherAccount':
                return $this->deleteOtherAccountToQB($requestedData);
            default:
                # code...
                break;
        }
    }

    public function storeInvoiceToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('invoices')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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


        // $accessToken = $_SESSION['sessionAccessToken'];
        $accessToken = $datas->sessionAccessToken;
        $dataService->updateOAuth2Token($accessToken);

        //Get company preferences
        //$companyPreference = $dataService->getCompanyPreferences();

        //Get CustomeField settings
        //$customerField = $companyPreference->SalesFormsPrefs->CustomField;

        //To retrieve PO custom field definition list use //$companyPreference->VendorAndPurchasesPrefs>POCustomField;

        //If size is 2, indicate CustomerField is enabled. The first CustomerField defintion is structure, //the second one is the actual value.
        /* if(sizeof($customerField) == 2){
           echo "CustomerField is enabled.\n";
        } */

        //Get the actual customerField
        //$actualCustomerField = end($customerField)->CustomField;
        //pre($actualCustomerField);

        //Find the name of each field
        /* $CustomFieldInvoices = array();
        $i = 0;
        $userData = new User;
        $dataUserCreated = $userData->getUserName($data->created_by);
        foreach ($actualCustomerField as $field) { */
        /* $name = $field->StringValue;
            $definationId = substr($field->Name, -1);
            if ($name == 'Sales Rep') {
                $CustomFieldInvoices[$i]['DefinitionId'] = $definationId;
                $CustomFieldInvoices[$i]['StringValue'] = $dataUserCreated->name;
                $CustomFieldInvoices[$i]['Type'] = 'StringType';
                $i++;
            }
            $idSalesRep = $definationId;

            if ($name == 'P.O Number') {
                $CustomFieldInvoices[$i]['DefinitionId'] = $definationId;
                $CustomFieldInvoices[$i]['StringValue'] = 'PO123456';
                $CustomFieldInvoices[$i]['Type'] = 'StringType';
                $i++;
            } */
        //echo "Field name is: " . $name . " and the defination ID for the field is: " . $definationId . "\n";
        //}

        $IncomeAccountRef = $dataService->Query("select * from TaxCode");
        $DefaultTaxCodeRefValueNone = 0;
        $DefaultTaxCodeRefValueTCA = 0;
        foreach ($IncomeAccountRef as $k => $v) {
            if ($v->Name == 'None')
                $DefaultTaxCodeRefValueNone = $v->Id;
            else if ($v->Name == 'TCA')
                $DefaultTaxCodeRefValueTCA = $v->Id;
            /* else
                $DefaultTaxCodeRefValueTCA = $v->Id; */
        }
        $DefaultTaxCodeRefValueNoneItems = 'NON';
        $DefaultTaxCodeRefValueTCAItems = 'TAX';

        $invoiceItems = DB::table('invoice_item_details')
            ->select(['invoice_item_details.*', 'billing_items.quick_book_id', 'billing_items.description', 'billing_items.flag_prod_tax_type', 'billing_items.billing_name', 'billing_items.id AS billingItemID'])
            ->join('billing_items', 'billing_items.id', '=', 'invoice_item_details.fees_name')
            ->where('invoice_item_details.invoice_id', $data->id)->get();

        $i = 0;
        $netAmtTaxable = 0;
        foreach ($invoiceItems as $key => $value) {
            $itemData[$i]['Description'] = !empty($value->fees_name_desc) ? $value->fees_name_desc : '';
            $itemData[$i]['Amount'] = $value->total_of_items;
            $itemData[$i]['DetailType'] = "SalesItemLineDetail";
            $itemData[$i]['SalesItemLineDetail']['TaxCodeRef'] = array("value" => $value->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCAItems : $DefaultTaxCodeRefValueNoneItems);
            $itemData[$i]['SalesItemLineDetail']['Qty'] = $value->quantity;
            $itemData[$i]['SalesItemLineDetail']['UnitPrice'] = $value->unit_price;
            $itemData[$i]['SalesItemLineDetail']['ItemRef'] = array(
                "value" => !empty($value->quick_book_id) ? $value->quick_book_id : $this->insertRunTime('billingItem', $value, $accessToken),
            );

            if ($value->flag_prod_tax_type == '1')
                $netAmtTaxable += $value->total_of_items;

            $i++;
        }

        $TaxRateReference = $dataService->Query("select * from TaxRate");
        $DefaultTaxRateValue = 0;
        foreach ($TaxRateReference as $k => $v) {
            if ($v->Name == 'TCA ( Sales)' || $v->Name == 'TCA')
                $DefaultTaxRateValue = $v->Id;
        }

        $taxDatas['TxnTaxCodeRef'] = array(
            "value" => $DefaultTaxCodeRefValueTCA
        );
        $taxDatas['TotalTax'] = $data->tca;
        $taxDatas['TaxLine'] = array(
            "Amount" => $data->tca,
            "DetailType" => "TaxLineDetail",
            "TaxLineDetail" => array(
                "NetAmountTaxable" => $netAmtTaxable,
                "TaxPercent" => '10',
                "TaxRateRef" => array(
                    "value" => $DefaultTaxRateValue
                ),
                "PercentBased" => true
            )
        );


        $clientData = new Clients;
        $dataClients = $clientData->getClientData($data->bill_to);
        $dataCountry = Country::getData($dataClients->country);
        $invoiceToCreate = Invoice::create([
            "TxnDate" => $data->date,
            "TrackingNum" => $data->awb_no,
            "ShipMethodRef" => !empty($data->carrier) ? $data->carrier : '',
            "DocNumber" => $data->bill_no,
            "Deposit" => $data->credits,
            "CustomerMemo" => ['value' => !empty($data->memo) ? $data->memo : '-'],
            "Line" => $itemData,
            //"CustomField" => $CustomFieldInvoices,

            "BillEmail" => [
                "Address" => $dataClients->email
            ],
            "BillAddr" => [
                "Line1" => $dataClients->company_address,
                "Line2" => $dataClients->city,
                "Line3" => $dataClients->state,
                "Line4" => $dataCountry->name
            ],
            "CustomerRef" => [
                "value" => !empty($dataClients->quick_book_id) ? $dataClients->quick_book_id : $this->insertRunTime('client', $dataClients, $accessToken),
                "name" => $dataClients->company_name
            ],
            "ShipAddr" => [
                "Line1" => $dataClients->company_address,
                "Line2" => $dataClients->city,
                "Line3" => $dataClients->state,
                "Line4" => $dataCountry->name
            ],
            "TxnTaxDetail" => $taxDatas
        ]);
        $resultObj = $dataService->Add($invoiceToCreate);
        if (!empty($resultObj)) {
            DB::table('invoices')->where('id', $data->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
            //pre('success');
            // file_put_contents('test.txt',print_r($resultObj->Id,true));
        } else {
            $error = $dataService->getLastError();
            //pre($error);
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 0;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
            file_put_contents('test.txt', print_r($errorsModel, true));
        }
    }

    public function updateInvoiceToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('invoices')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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

        /*
         * Retrieve the accessToken value from session variable
         */
        // $accessToken = $_SESSION['sessionAccessToken'];
        $accessToken = $datas->sessionAccessToken;

        /*
         * Update the OAuth2Token of the dataService object
         */
        $dataService->updateOAuth2Token($accessToken);

        //Get company preferences
        //$companyPreference = $dataService->getCompanyPreferences();

        //Get CustomeField settings
        //$customerField = $companyPreference->SalesFormsPrefs->CustomField;

        //To retrieve PO custom field definition list use //$companyPreference->VendorAndPurchasesPrefs>POCustomField;

        //If size is 2, indicate CustomerField is enabled. The first CustomerField defintion is structure, //the second one is the actual value.
        /* if(sizeof($customerField) == 2){
           echo "CustomerField is enabled.\n";
        } */

        //Get the actual customerField
        //$actualCustomerField = end($customerField)->CustomField;
        //pre($actualCustomerField);

        //Find the name of each field
        /* $CustomFieldInvoices = array();
        $i = 0;
        $userData = new User;
        $dataUserCreated = $userData->getUserName($data->created_by);
        foreach ($actualCustomerField as $field) { */
        /* $name = $field->StringValue;
            $definationId = substr($field->Name, -1);
            if ($name == 'Sales Rep') {
                $CustomFieldInvoices[$i]['DefinitionId'] = $definationId;
                $CustomFieldInvoices[$i]['StringValue'] = $dataUserCreated->name;
                $CustomFieldInvoices[$i]['Type'] = 'StringType';
                $i++;
            }
            $idSalesRep = $definationId;

            if ($name == 'P.O Number') {
                $CustomFieldInvoices[$i]['DefinitionId'] = $definationId;
                $CustomFieldInvoices[$i]['StringValue'] = 'PO123456';
                $CustomFieldInvoices[$i]['Type'] = 'StringType';
                $i++;
            } */
        //echo "Field name is: " . $name . " and the defination ID for the field is: " . $definationId . "\n";
        //}

        $IncomeAccountRef = $dataService->Query("select * from TaxCode");
        $DefaultTaxCodeRefValueNone = 0;
        $DefaultTaxCodeRefValueTCA = 0;
        foreach ($IncomeAccountRef as $k => $v) {
            if ($v->Name == 'None')
                $DefaultTaxCodeRefValueNone = $v->Id;
            else if ($v->Name == 'TCA')
                $DefaultTaxCodeRefValueTCA = $v->Id;
            /* else
                $DefaultTaxCodeRefValueTCA = $v->Id; */
        }
        $DefaultTaxCodeRefValueNoneItems = 'NON';
        $DefaultTaxCodeRefValueTCAItems = 'TAX';

        $invoiceObj = new IPPInvoice();
        $invoiceObj->Id = $data->quick_book_id;
        $invoiceInfo = $dataService->FindById($invoiceObj);
        $invoiceItems = DB::table('invoice_item_details')
            ->select(['invoice_item_details.*', 'billing_items.quick_book_id', 'billing_items.description', 'billing_items.flag_prod_tax_type', 'billing_items.billing_name', 'billing_items.id AS billingItemID'])
            ->join('billing_items', 'billing_items.id', '=', 'invoice_item_details.fees_name')
            ->where('invoice_item_details.invoice_id', $data->id)->get();
        $i = 0;
        $netAmtTaxable = 0;
        foreach ($invoiceItems as $key => $value) {
            $itemData[$i]['Description'] = !empty($value->fees_name_desc) ? $value->fees_name_desc : '';
            $itemData[$i]['Amount'] = $value->total_of_items;
            $itemData[$i]['DetailType'] = "SalesItemLineDetail";
            $itemData[$i]['SalesItemLineDetail']['TaxCodeRef'] = array("value" => $value->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCAItems : $DefaultTaxCodeRefValueNoneItems);
            $itemData[$i]['SalesItemLineDetail']['Qty'] = $value->quantity;
            $itemData[$i]['SalesItemLineDetail']['UnitPrice'] = $value->unit_price;
            $itemData[$i]['SalesItemLineDetail']['ItemRef'] = array(
                "value" => !empty($value->quick_book_id) ? $value->quick_book_id : $this->insertRunTime('billingItem', $value, $accessToken),
            );
            if ($value->flag_prod_tax_type == '1')
                $netAmtTaxable += $value->total_of_items;

            $i++;
        }

        $TaxRateReference = $dataService->Query("select * from TaxRate");
        $DefaultTaxRateValue = 0;
        foreach ($TaxRateReference as $k => $v) {
            if ($v->Name == 'TCA ( Sales)' || $v->Name == 'TCA')
                $DefaultTaxRateValue = $v->Id;
        }

        $taxDatas['TxnTaxCodeRef'] = array(
            "value" => $DefaultTaxCodeRefValueTCA
        );
        $taxDatas['TotalTax'] = $data->tca;
        $taxDatas['TaxLine'] = array(
            "Amount" => $data->tca,
            "DetailType" => "TaxLineDetail",
            "TaxLineDetail" => array(
                "NetAmountTaxable" => $netAmtTaxable,
                "TaxPercent" => '10',
                "TaxRateRef" => array(
                    "value" => $DefaultTaxRateValue
                ),
                "PercentBased" => true
            )
        );

        $clientData = new Clients;
        $dataClients = $clientData->getClientData($data->bill_to);
        $userData = new User;
        $dataUserCreated = $userData->getUserName($data->created_by);
        $dataCountry = Country::getData($dataClients->country);
        $updatedInvoice = Invoice::update($invoiceInfo, [
            "Id" => $data->quick_book_id,
            "SyncToken" => $invoiceInfo->SyncToken,
            "sparse" => true,
            "TxnDate" => $data->date,
            "TrackingNum" => $data->awb_no,
            "ShipMethodRef" => !empty($data->carrier) ? $data->carrier : '',
            "DocNumber" => $data->bill_no,
            "DepositToAccountRef" => "",
            "Deposit" => $data->credits,
            "CustomerMemo" => ['value' => !empty($data->memo) ? $data->memo : '-'],
            "Line" => $itemData,
            //"CustomField" => $CustomFieldInvoices,
            "CustomerRef" => [
                "value" => !empty($dataClients->quick_book_id) ? $dataClients->quick_book_id : $this->insertRunTime('client', $dataClients, $accessToken),
                "name" => $dataClients->company_name
            ],
            "BillEmail" => [
                "Address" => $dataClients->email
            ],
            "BillAddr" => [
                "Line1" => $dataClients->company_address,
                "Line2" => $dataClients->city,
                "Line3" => $dataClients->state,
                "Line4" => $dataCountry->name
            ],
            "ShipAddr" => [
                "Line1" => $dataClients->company_address,
                "Line2" => $dataClients->city,
                "Line3" => $dataClients->state,
                "Line4" => $dataCountry->name
            ],
            "TxnTaxDetail" => $taxDatas
        ]);
        $resultObj = $dataService->Update($updatedInvoice);
        //file_put_contents('test.txt',print_r($resultObj,true));
        if (empty($resultObj)) {
            $error = $dataService->getLastError();
            //pre($error);
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 1;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
            //file_put_contents('test.txt',print_r($error,true));
        } else {
            DB::table('invoices')->where('id', $id)->update(['qb_sync' => 1]);
            //file_put_contents('test.txt',print_r("Success",true));
        }
    }


    public function deleteInvoiceToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('invoices')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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

        //if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            //pre('Test');
            // $accessToken = $_SESSION['sessionAccessToken'];
            $accessToken = $datas->sessionAccessToken;

            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $invoiceObj = new IPPInvoice();
            $invoiceObj->Id = $data->quick_book_id;

            $invoiceInfo = $dataService->FindById($invoiceObj);


            $deleteObj = Invoice::create(array('SyncToken' => $invoiceInfo->SyncToken, 'Id' => $invoiceInfo->Id));
            $delete = $dataService->Delete($deleteObj);

            if (empty($delete)) {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 2;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
                //file_put_contents('test.txt',print_r($error,true));
            } else {
                DB::table('invoices')->where('id', $id)->update(['qb_sync' => 1]);
                //file_put_contents('test.txt',print_r("SuccessDelete",true));
            }
        }
    }



    public function storeExpenseToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('expenses')->where('expense_id', $id)->first();
        // file_put_contents('test.txt',print_r($data,true));
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

        //if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];

            /*file_put_contents('test.txt',print_r((array) $accessToken,1));
            exit;*/
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $purchaseObj = new IPPPurchase();
            $items = DB::table('expense_details')->where('expense_id', $data->expense_id)->get();
            $vendorId = $items[0]->paid_to;
            $vendorDetail = DB::table('vendors')->where('id', $vendorId)->first();
            $i = 0;
            foreach ($items as $key => $value) {
                $itemarr[$i]['Description'] = $value->description;
                $itemarr[$i]['DetailType'] = "AccountBasedExpenseLineDetail";
                $itemarr[$i]['Amount'] = $value->amount;
                $quickBookItemData = DB::table('costs')->where('id', $value->expense_type)->first();
                $itemarr[$i]['AccountBasedExpenseLineDetail']['AccountRef'] = array(
                    "name" => $quickBookItemData->cost_name,
                    "Value" => !empty($quickBookItemData->quick_book_id) ? $quickBookItemData->quick_book_id : $this->insertRunTime('costItem', $quickBookItemData, $accessToken)
                );
                /* if(!empty($quickBookItemData)){
                    $itemarr[$i]['AccountBasedExpenseLineDetail']['AccountRef'] = array(
                      "name" => $quickBookItemData->cost_name,
                      "Value" => !empty($quickBookItemData->quick_book_id) ? $quickBookItemData->quick_book_id : $this->insertRunTime('costItem',$quickBookItemData,$accessToken)
                    );
                } else {
                    $itemarr[$i]['AccountBasedExpenseLineDetail']['AccountRef'] = array(
                        "name"=>"Pump",
                        "Value"=>"11"
                    );    
                } */
                $i++;
            }
            $accountData = DB::table('cashcredit')->where('id', $data->cash_credit_account)->first();
            $reqarr = [
                "EntityRef" =>
                [
                    "name" => $vendorDetail->company_name,
                    "Value" => !empty($vendorDetail->quick_book_id) ? $vendorDetail->quick_book_id  : $this->insertRunTime('vendor', $vendorDetail, $accessToken)

                ],
                "DocNumber" => $data->voucher_number,
                "PrivateNote" => $data->note,
                "TxnDate" => date('Y-m-d', strtotime($data->exp_date)),
                "PaymentType" => "CreditCard",
                "AccountRef" => [
                    "name" => $accountData->name,
                    "Value" => !empty($accountData->quick_book_id) ? $accountData->quick_book_id : $this->insertRunTime('accounts', $accountData, $accessToken)
                ],

                "Line" => $itemarr,
            ];
            //pre($reqarr);
            $expenseInfo = Purchase::create($reqarr);
            $expenseInfoAdd = $dataService->Add($expenseInfo);
            if (!empty($expenseInfoAdd)) {
                DB::table('expenses')->where('expense_id', $data->expense_id)->update(['quick_book_id' => $expenseInfoAdd->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($expenseInfoAdd->Id.'save',true));
                //pre("Success");

            } else {
                $error = $dataService->getLastError();
                //pre($error);
                //file_put_contents('test.txt',print_r($error,true));
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->expense_id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            }
        }
    }


    public function updateExpenseToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('expenses')->where('expense_id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));//pre($data);
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

        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            //$accessToken = $_SESSION['sessionAccessToken'];
            $accessToken = $datas->sessionAccessToken;
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $purchaseObj = new IPPPurchase();
            $purchaseObj->Id = $data->quick_book_id;
            $expenseInfo = $dataService->FindById($purchaseObj);
            //pre($expenseInfo);
            //$itemarr = $expenseInfo->Line;
            $items = DB::table('expense_details')->where('expense_id', $data->expense_id)->get();
            $vendorId = $items[0]->paid_to;
            $vendorDetail = DB::table('vendors')->where('id', $vendorId)->first();
            $accountData = DB::table('cashcredit')->where('id', $data->cash_credit_account)->first();
            $i = 0;
            foreach ($items as $key => $value) {
                $itemarr[$i]['Id'] = $i + 1;
                $itemarr[$i]['DetailType'] = 'AccountBasedExpenseLineDetail';
                $itemarr[$i]['Description'] = $value->description;
                $itemarr[$i]['Amount'] = $value->amount;
                $quickBookItemData = DB::table('costs')->where('id', $value->expense_type)->first();
                $itemarr[$i]['AccountBasedExpenseLineDetail']['AccountRef'] = array(
                    "name" => $quickBookItemData->cost_name,
                    "Value" => !empty($quickBookItemData->quick_book_id) ? $quickBookItemData->quick_book_id : $this->insertRunTime('costItem', $quickBookItemData, $accessToken)
                );
                /* if(!empty($quickBookItemData)){
                    $itemarr[$i]['AccountBasedExpenseLineDetail']['AccountRef'] = array(
                            "name" => $quickBookItemData->cost_name,
                            "Value" => !empty($quickBookItemData->quick_book_id) ? $quickBookItemData->quick_book_id : $this->insertRunTime('costItem',$quickBookItemData,$accessToken)
                    );
                   
                } else {
                    $itemarr[$i]['AccountBasedExpenseLineDetail']['AccountRef'] = array(
                        "name"=>"Pump",
                        "Value"=>"11"
                    ); 
                } */
                $i++;
            }

            $updatedExpense = [
                "Line" => $itemarr,
                "TxnDate" => date('Y-m-d', strtotime($data->exp_date)),
                "PrivateNote" => $data->note,
                "EntityRef" =>
                [
                    "name" => $vendorDetail->company_name,
                    "Value" => !empty($vendorDetail->quick_book_id) ? $vendorDetail->quick_book_id  : $this->insertRunTime('vendor', $vendorDetail, $accessToken)

                ],
                "AccountRef" => [
                    "name" => $accountData->name,
                    "Value" => !empty($accountData->quick_book_id) ? $accountData->quick_book_id : $this->insertRunTime('accounts', $accountData, $accessToken)
                ],

            ];
            $updatedInvoice = Purchase::update($expenseInfo, $updatedExpense);

            $updated = $dataService->Update($updatedInvoice);
            //file_put_contents('test.txt',print_r($updated,true));
            if (empty($updated)) {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->expense_id;
                $error_log['operation'] = 1;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                file_put_contents('test.txt', print_r($errorsModel, true));
            } else {
                DB::table('expenses')->where('expense_id', $id)->update(['qb_sync' => 1]);
            }
        } else {
            //file_put_contents('test.txt',print_r('expired',true));
        }
    }

    public function deleteExpenseToQB($datas)
    {
        $id = $datas->id;
        $config = Config::get('app.QB');
        //file_put_contents('test.txt',print_r($datas,true));
        $dataService = DataService::Configure(array(
            'auth_mode' => 'oauth2',
            'ClientID' => $config['client_id'],
            'ClientSecret' =>  $config['client_secret'],
            'RedirectURI' => $config['oauth_redirect_uri'],
            'scope' => $config['oauth_scope'],
            'baseUrl' => "development"
            //'baseUrl' => "https://quickbooks.api.intuit.com"
        ));

        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            //pre($data);
            //$accessToken = $_SESSION['sessionAccessToken'];
            $accessToken = $datas->sessionAccessToken;
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $purchaseObj = new IPPPurchase();
            $purchaseObj->Id = $datas->qb_id;
            $expenseInfo = $dataService->FindById($purchaseObj);
            $deleteObj = Purchase::create(array('SyncToken' => $expenseInfo->SyncToken, 'Id' => $expenseInfo->Id));
            $delete = $dataService->Delete($deleteObj);
            if (empty($delete)) {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $datas->voucher_number;
                $error_log['operation'] = 2;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            } else {
                DB::table('expenses')->where('expense_id', $id)->update(['qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($delete,true));    
            }
        }
    }


    public function storeVendorToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('vendors')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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

        //if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $dataCurrency = Currency::getData($data->currency);
            $newVendor = [
                'BillAddr' => [
                    'Line1' => $data->street,
                    'City' => $data->city,
                    'Country' => $data->country,
                    'CountrySubDivisionCode' => $data->state,
                    'PostalCode' => $data->zipcode,
                ],
                'Vendor1099' => 'false',
                'CurrencyRef' => $dataCurrency->code,
                'GivenName' => $data->first_name,
                'MiddleName' => $data->middle_name,
                'FamilyName' => $data->last_name,
                'CompanyName' => $data->company_name,
                'DisplayName' => $data->company_name,
                'PrintOnCheckName' => $data->company_name,
                'PrimaryPhone' => [
                    'FreeFormNumber' => $data->company_phone
                ],
                'PrimaryEmailAddr' => [
                    'Address' => $data->email
                ]

            ];
            $addVendor = Vendor::create($newVendor);
            $createdVendor = $dataService->Add($addVendor);
            // file_put_contents('test.txt',print_r($addVendor,true));die;
            if (!empty($createdVendor)) {
                DB::table('vendors')->where('id', $data->id)->update(['quick_book_id' => $createdVendor->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($createdVendor->Id,true));

            } else {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
            }
        }
    }

    public function updateVendorToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('vendors')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        //if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $vendorObj = new IPPVendor();
            $vendorObj->Id = $data->quick_book_id;
            $vendorData = $dataService->FindById($vendorObj);
            $updatedArr = [
                'BillAddr' => [
                    'Line1' => $data->street,
                    'City' => $data->city,
                    'Country' => $data->country,
                    'CountrySubDivisionCode' => $data->state,
                    'PostalCode' => $data->zipcode,
                ],
                'GivenName' => $data->first_name,
                'MiddleName' => $data->middle_name,
                'FamilyName' => $data->last_name,
                'CompanyName' => $data->company_name,
                'DisplayName' => $data->company_name,
                'Vendor1099' => 'false',
                'CurrencyRef' => 'USD',

                'PrintOnCheckName' => $data->company_name,
                'PrimaryPhone' => [
                    'FreeFormNumber' => $data->company_phone
                ],
                'PrimaryEmailAddr' => [
                    'Address' => $data->email
                ]

            ];
            if ($vendorData) {
                $updatedVendor = Vendor::update($vendorData, $updatedArr);
                $updated = $dataService->Update($updatedVendor);
                //file_put_contents('test.txt',print_r($updated,true));
                if (empty($updated)) {
                    $error = $dataService->getLastError();
                    $error_log['module'] = $datas->module;
                    $error_log['module_id'] = $data->id;
                    $error_log['operation'] = 1;
                    $error_log['error_message'] = $error->getIntuitErrorDetail();
                    $errorsModel = quickbookErrorLog::create($error_log);
                    //file_put_contents('test.txt',print_r($errorsModel,true));
                } else {
                    DB::table('vendors')->where('id', $id)->update(['qb_sync' => 1]);
                }
            }
        }
    }


    public function deleteVendorToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('vendors')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $vendorObj = new IPPVendor();
            $vendorObj->Id = $data->quick_book_id;
            $vendorData = $dataService->FindById($vendorObj);
            $updatedArr = [
                'Active' => false,
            ];
            $updatedVendor = Vendor::update($vendorData, $updatedArr);
            $updated = $dataService->Update($updatedVendor);
            //file_put_contents('test.txt',print_r($updated,true));die;
            if (empty($updated)) {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 2;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            } else {
                DB::table('vendors')->where('id', $id)->update(['qb_sync' => 1]);
            }
        }
    }



    public function storeCostToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('costs')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        //if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            // $accessToken = $_SESSION['sessionAccessToken'];
            $accessToken = $datas->sessionAccessToken;

            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

            $IncomeAccountRef = $dataService->Query("select * from Account where Name = 'Sales of Product Income'");
            $ExpenseAccountRef = $dataService->Query("select * from Account where Name = 'Cost of Goods Sold'");
            $AssetAccountRef = $dataService->Query("select * from Account where Name = 'Inventory Asset'");
            $accountArr = [
                'Name' => $data->cost_name,
                'Description' => $data->code,
                'FullyQualifiedName' => $data->cost_name,
                'Classification' => 'Expense',
                'AccountType' => "Expense",
                'AccountSubType' => 'OtherMiscellaneousServiceCost',
                'AcctNum' => substr($data->code, 0, 20)
            ];

            $addAccount = Account::create($accountArr);
            $createdAccount = $dataService->Add($addAccount);
            // file_put_contents('test.txt',print_r($dataService->getLastError(),true));die;
            if (!empty($createdAccount)) {
                DB::table('costs')->where('id', $data->id)->update(['quick_book_id' => $createdAccount->Id, 'qb_sync' => 1]);
                // file_put_contents('test.txt',print_r($createdAccount->Id,true));
            } else {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
            }
        }
    }


    public function updateCostToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('costs')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $accountObj = new IPPAccount();
            $accountObj->Id = $data->quick_book_id;
            $accountData = $dataService->FindById($accountObj);
            $accountArr = [
                'Name' => $data->cost_name,
                'Description' => $data->code,
                'FullyQualifiedName' => $data->cost_name,
                'Classification' => 'Expense',
                'AccountType' => "Expense",
                'AccountSubType' => 'OtherMiscellaneousServiceCost',
                'AcctNum' => substr($data->code, 0, 20)
            ];

            $updatedAccount = Account::update($accountData, $accountArr);
            $updated = $dataService->Update($updatedAccount);
            if (empty($updated)) {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 1;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            } else {
                DB::table('costs')->where('id', $id)->update(['qb_sync' => 1]);
            }
            //file_put_contents('test.txt',print_r($updated,true));
        }
    }

    public function deleteCostToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('costs')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $accountObj = new IPPAccount();
            $accountObj->Id  = $data->quick_book_id;
            $accountData = $dataService->FindById($accountObj);
            $updatedArr = [
                'Active' => false,
            ];
            $updatedItem = Account::update($accountData, $updatedArr);
            $updated = $dataService->Update($updatedItem);
            if (empty($updated)) {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 2;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            } else {
                DB::table('costs')->where('id', $id)->update(['qb_sync' => 1]);
            }
            // file_put_contents('test.txt',print_r($updatedItem,true));

        }
    }

    public function storeAccountToQB($datas)
    { // Cash/Bank Account
        $id = $datas->id;
        $data = DB::table('cashcredit')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

            if ($data->detail_type == 41) { // Petty Cash
                $accountType = 'Bank';
                $accountSubType = 'CashOnHand';
                $Classification = 'Asset';
            } else if ($data->detail_type == 42) { //Current Bank Account
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            } else if ($data->detail_type == 43) { //Credit Card
                $accountType = "Credit Card";
                $accountSubType = 'CreditCard';
                $Classification = 'Liability';
            } else if ($data->detail_type == 44) { //Overdraft
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            } else { // Other
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            }


            $accountArr = [
                'Name' => $data->name,
                'Description' => $data->description,
                'FullyQualifiedName' => $data->name,
                'Classification' => $Classification,
                'AccountType' => $accountType,
                'AccountSubType' => $accountSubType,
                'OpeningBalance' => $data->opening_balance,
                'CurrentBalance' => $data->available_balance,
                'OpeningBalanceDate' => date('Y-m-d'),
                'CurrencyRef' => $data->currency_code
            ];

            $addAccount = Account::create($accountArr);
            $createdAccount = $dataService->Add($addAccount);
            //file_put_contents('test.txt',print_r($dataService->getLastError(),true));
            if (!empty($createdAccount)) {
                DB::table('cashcredit')->where('id', $data->id)->update(['quick_book_id' => $createdAccount->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($createdAccount->Id,true));
            } else {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
            }
        }
    }


    public function updateAccountToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('cashcredit')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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

        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $accountObj = new IPPAccount();
            $accountObj->Id = $data->quick_book_id;
            $accountData = $dataService->FindById($accountObj);

            if ($data->detail_type == 41) { // Petty Cash
                $accountType = 'Bank';
                $accountSubType = 'CashOnHand';
                $Classification = 'Asset';
            } else if ($data->detail_type == 42) { //Current Bank Account
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            } else if ($data->detail_type == 43) { //Credit Card
                $accountType = "Credit Card";
                $accountSubType = 'CreditCard';
                $Classification = 'Liability';
            } else if ($data->detail_type == 44) { //Overdraft
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            } else { // Other
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            }

            $accountArr = [
                'Name' => $data->name,
                'Description' => $data->description,
                'FullyQualifiedName' => $data->name,
                'Classification' => $Classification,
                'AccountType' => $accountType,
                'AccountSubType' => $accountSubType,
                'OpeningBalance' => $data->opening_balance,
                'CurrentBalance' => $data->available_balance,
                'OpeningBalanceDate' => date('Y-m-d'),
                'CurrencyRef' => $data->currency_code
            ];

            $updatedAccount = Account::update($accountData, $accountArr);
            $updated = $dataService->Update($updatedAccount);
            //file_put_contents('test.txt',print_r($updated,true));
            if (empty($updated)) {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 1;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
            } else {
                DB::table('cashcredit')->where('id', $id)->update(['qb_sync' => 1]);
            }
        }
    }

    public function deleteAccountToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('cashcredit')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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


        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $accountObj = new IPPAccount();
            $accountObj->Id  = $data->quick_book_id;
            $accountData = $dataService->FindById($accountObj);
            $updatedArr = [
                'Active' => false,
            ];
            $updatedItem = Account::update($accountData, $updatedArr);
            $updated = $dataService->Update($updatedItem);
            //file_put_contents('test.txt',print_r($updated,true));
            if (empty($updated)) {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 2;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            } else {
                DB::table('cashcredit')->where('id', $id)->update(['qb_sync' => 1]);
            }
        }
    }

    public function storeOtherAccountToQB($datas)
    { // Other Accounts
        $id = $datas->id;
        $data = DB::table('cashcredit_detail_type')->where('id', $id)->first();
        $qbAccountData = DB::table('quickbook_account_types')->where('id', $data->quickbook_account_type_id)->first();
        $qbSubAccountData = DB::table('quickbook_account_sub_types')->where('id', $data->quickbook_account_sub_type_id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

            $accountType = $qbAccountData->slug;
            $accountSubType = $qbSubAccountData->slug;
            $Classification = $qbAccountData->classification;

            $accountArr = [
                'Name' => $data->name,
                'Description' => $data->name,
                'FullyQualifiedName' => $data->name,
                'Classification' => $Classification,
                'AccountType' => $accountType,
                'AccountSubType' => $accountSubType,
                /* 'OpeningBalance' => $data->opening_balance,
                'CurrentBalance' => $data->available_balance,
                'OpeningBalanceDate' => date('Y-m-d'),
                'CurrencyRef' => $data->currency_code */
            ];

            $addAccount = Account::create($accountArr);
            $createdAccount = $dataService->Add($addAccount);
            //file_put_contents('test.txt',print_r($dataService->getLastError(),true));
            if (!empty($createdAccount)) {
                DB::table('cashcredit_detail_type')->where('id', $data->id)->update(['quick_book_id' => $createdAccount->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($createdAccount->Id,true));
            } else {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
            }
        }
    }


    public function updateOtherAccountToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('cashcredit_detail_type')->where('id', $id)->first();
        $qbAccountData = DB::table('quickbook_account_types')->where('id', $data->quickbook_account_type_id)->first();
        $qbSubAccountData = DB::table('quickbook_account_sub_types')->where('id', $data->quickbook_account_sub_type_id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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

        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $accountObj = new IPPAccount();
            $accountObj->Id = $data->quick_book_id;
            $accountData = $dataService->FindById($accountObj);

            $accountType = $qbAccountData->slug;
            $accountSubType = $qbSubAccountData->slug;
            $Classification = $qbAccountData->classification;

            $accountArr = [
                'Name' => $data->name,
                'Description' => $data->name,
                'FullyQualifiedName' => $data->name,
                'Classification' => $Classification,
                'AccountType' => $accountType,
                'AccountSubType' => $accountSubType,
                /* 'OpeningBalance' => $data->opening_balance,
                'CurrentBalance' => $data->available_balance,
                'OpeningBalanceDate' => date('Y-m-d'),
                'CurrencyRef' => $data->currency_code */
            ];

            $updatedAccount = Account::update($accountData, $accountArr);
            $updated = $dataService->Update($updatedAccount);
            //file_put_contents('test.txt',print_r($updated,true));
            if (empty($updated)) {
                $error = $dataService->getLastError();
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 1;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
                //file_put_contents('test.txt',print_r($errorsModel,true));
            } else {
                DB::table('cashcredit_detail_type')->where('id', $id)->update(['qb_sync' => 1]);
            }
        }
    }

    public function deleteOtherAccountToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('cashcredit_detail_type')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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


        // if(isset($_SESSION['sessionAccessToken'])){
        if (isset($datas->sessionAccessToken)) {
            $accessToken = $datas->sessionAccessToken;
            //$accessToken = $_SESSION['sessionAccessToken'];
            $dataService->updateOAuth2Token($accessToken);
            $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
            $accountObj = new IPPAccount();
            $accountObj->Id  = $data->quick_book_id;
            $accountData = $dataService->FindById($accountObj);
            $updatedArr = [
                'Active' => false,
            ];
            $updatedItem = Account::update($accountData, $updatedArr);
            $updated = $dataService->Update($updatedItem);
            //file_put_contents('test.txt',print_r($updated,true));
            if (empty($updated)) {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = $datas->module;
                $error_log['module_id'] = $data->id;
                $error_log['operation'] = 2;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            } else {
                DB::table('cashcredit_detail_type')->where('id', $id)->update(['qb_sync' => 1]);
            }
        }
    }


    public function storeBillingItemToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('billing_items')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        $accessToken = $datas->sessionAccessToken;
        //$accessToken = $_SESSION['sessionAccessToken'];
        $dataService->updateOAuth2Token($accessToken);
        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

        $IncomeAccountRef = $dataService->Query("select * from TaxCode");
        $DefaultTaxCodeRefValueNone = 0;
        $DefaultTaxCodeRefValueTCA = 0;
        foreach ($IncomeAccountRef as $k => $v) {
            if ($v->Name == 'None' || $v->Name == 'NONE')
                $DefaultTaxCodeRefValueNone = $v->Id;
            else if ($v->Name == 'TCA')
                $DefaultTaxCodeRefValueTCA = $v->Id;
            /* else
                $DefaultTaxCodeRefValueTCA = $v->Id; */
        }

        $IncomeAccountRef = $dataService->Query("select * from Account where Name = 'Sales of Product Income'");


        $create = Item::create([
            //"Name" => $data->item_code,
            "Name" => $data->billing_name,
            "Description" => $data->description,
            "Sku" => $data->item_code,
            "Taxable" => $data->flag_prod_tax_type == '1' ? true : false,
            "SalesTaxCodeRef" => array('value' => $data->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCA : $DefaultTaxCodeRefValueNone),
            "Type" => 'Service',
            "IncomeAccountRef" => array(
                "name" => $IncomeAccountRef[0]->Name,
                "Value" => $IncomeAccountRef[0]->Id
            )
        ]);


        $resultObj = $dataService->Add($create);

        if (!empty($resultObj) && isset($resultObj->Id)) {
            DB::table('billing_items')->where('id', $data->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
            //pre("Success");
            //file_put_contents('test.txt',print_r($resultObj->Id,true));
        } else {
            $error = $dataService->getLastError();
            //pre($error);
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 0;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
            file_put_contents('test.txt', print_r($errorsModel, true));
        }
    }

    public function updateBillingItemToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('billing_items')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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

        $accessToken = $datas->sessionAccessToken;
        // $accessToken = $_SESSION['sessionAccessToken'];
        $dataService->updateOAuth2Token($accessToken);
        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();

        $IncomeAccountRef = $dataService->Query("select * from TaxCode");
        $DefaultTaxCodeRefValueNone = 0;
        $DefaultTaxCodeRefValueTCA = 0;
        foreach ($IncomeAccountRef as $k => $v) {
            if ($v->Name == 'None' || $v->Name == 'NONE')
                $DefaultTaxCodeRefValueNone = $v->Id;
            else if ($v->Name == 'TCA')
                $DefaultTaxCodeRefValueTCA = $v->Id;
            /* else
                $DefaultTaxCodeRefValueTCA = $v->Id; */
        }

        $itemObj = new IPPItem();
        $itemObj->Id = $data->quick_book_id;
        $itemInfo = $dataService->FindById($itemObj);



        $updatedItem = Item::update($itemInfo, [
            //"Name" => $data->item_code,
            "Name" => $data->billing_name,
            "Description" => $data->description,
            "Sku" => $data->item_code,
            "Taxable" => $data->flag_prod_tax_type == '1' ? true : false,
            "SalesTaxCodeRef" => array('value' => $data->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCA : $DefaultTaxCodeRefValueNone),
        ]);

        $updated = $dataService->Update($updatedItem);

        if (empty($updated)) {
            $error = $dataService->getLastError();
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 1;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
            // file_put_contents('test.txt',print_r($dataService->getLastError(),true));
        } else {
            DB::table('billing_items')->where('id', $id)->update(['qb_sync' => 1]);
        }
        //file_put_contents('test.txt',print_r($updated,true));
    }

    public function deleteBillingItemToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('billing_items')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        $accessToken = $datas->sessionAccessToken;
        //$accessToken = $_SESSION['sessionAccessToken'];
        $dataService->updateOAuth2Token($accessToken);
        $oauthLoginHelper = $dataService->getOAuth2LoginHelper();
        $itemObj = new IPPItem();
        $itemObj->Id = $data->quick_book_id;
        $itemInfo = $dataService->FindById($itemObj);

        $updatedItem = Item::update($itemInfo, [
            "Active" => false
        ]);

        $updated = $dataService->Update($updatedItem);
        if (empty($updated)) {
            $error = $dataService->getLastError();
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 2;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
        } else {
            DB::table('billing_items')->where('id', $id)->update(['qb_sync' => 1]);
        }
    }

    public function storeClientToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('clients')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        $accessToken = $datas->sessionAccessToken;
        $dataCountry = Country::getData($data->country);
        $dataCurrency = Currency::getData($data->currency);
        $dataService->updateOAuth2Token($accessToken);

        $IncomeAccountRef = $dataService->Query("select * from TaxCode");
        $DefaultTaxCodeRefValueNone = 0;
        $DefaultTaxCodeRefValueTCA = 0;
        foreach ($IncomeAccountRef as $k => $v) {
            if ($v->Name == 'None' || $v->Name == 'NONE')
                $DefaultTaxCodeRefValueNone = $v->Id;
            else if ($v->Name == 'TCA')
                $DefaultTaxCodeRefValueTCA = $v->Id;
            /* else
                $DefaultTaxCodeRefValueTCA = $v->Id; */
        }


        $create = Customer::create([
            'CurrencyRef' => $dataCurrency->code,
            "CompanyName" => $data->company_name,
            "DisplayName" => $data->company_name,
            "GivenName" => $data->first_name,
            "FamilyName" => $data->last_name,
            "MiddleName" => $data->middle_name,
            "PrimaryEmailAddr" => array(
                "Address" => $data->email,
            ),
            //"Taxable" => $data->flag_prod_tax_type == '1' ? true : false,
            "Taxable" => true,
            "DefaultTaxCodeRef" => array("value" => $data->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCA : $DefaultTaxCodeRefValueNone),
            "Active" => true,
            'BillAddr' => [
                'Line1' => $data->company_address,
                'City' => $data->city,
                'Country' => !empty($dataCountry->name) ? $dataCountry->name : '',
                'CountrySubDivisionCode' => $data->state,
                'PostalCode' => $data->zipcode,
            ],

        ]);

        $resultObj = $dataService->Add($create);
        if (!empty($resultObj) && isset($resultObj->Id)) {
            DB::table('clients')->where('id', $data->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
            //pre("success");
            //file_put_contents('test.txt',print_r($resultObj->Id,true));
        } else {
            $error = $dataService->getLastError();
            //pre($error);
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 0;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
            file_put_contents('test.txt', print_r($errorsModel, true));
        }
    }

    public function updateClientToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('clients')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        $accessToken = $datas->sessionAccessToken;
        //$accessToken = $_SESSION['sessionAccessToken'];
        $dataService->updateOAuth2Token($accessToken);

        $IncomeAccountRef = $dataService->Query("select * from TaxCode");
        $DefaultTaxCodeRefValueNone = 0;
        $DefaultTaxCodeRefValueTCA = 0;
        foreach ($IncomeAccountRef as $k => $v) {
            if ($v->Name == 'None' || $v->Name == 'NONE')
                $DefaultTaxCodeRefValueNone = $v->Id;
            else if ($v->Name == 'TCA')
                $DefaultTaxCodeRefValueTCA = $v->Id;
            /* else
                $DefaultTaxCodeRefValueTCA = $v->Id; */
        }

        $itemObj = new IPPCustomer();
        $itemObj->Id = $data->quick_book_id;
        $itemInfo = $dataService->FindById($itemObj);
        $dataCountry = Country::getData($data->country);
        $dataCurrency = Currency::getData($data->currency);
        $updatedItem = Customer::update($itemInfo, [
            "SyncToken" => $itemInfo->SyncToken,
            'CurrencyRef' => $dataCurrency->code,
            "CompanyName" => $data->company_name,
            "DisplayName" => $data->company_name,
            "GivenName" => $data->first_name,
            "FamilyName" => $data->last_name,
            "MiddleName" => $data->middle_name,
            "PrimaryEmailAddr" => array(
                "Address" => $data->email,
            ),
            //"Taxable" => $data->flag_prod_tax_type == '1' ? true : false,
            "Taxable" => true,
            "DefaultTaxCodeRef" => array("value" => $data->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCA : $DefaultTaxCodeRefValueNone),
            "Active" => true,
            'BillAddr' => [
                'Line1' => $data->company_address,
                'City' => $data->city,
                'Country' => !empty($dataCountry->name) ? $dataCountry->name : '',
                'CountrySubDivisionCode' => $data->state,
                'PostalCode' => $data->zipcode,
            ],
        ]);

        $updated = $dataService->Update($updatedItem);
        if (empty($updated)) {
            $error = $dataService->getLastError();
            file_put_contents('test.txt', print_r($error, true));
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 1;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
        } else {
            DB::table('clients')->where('id', $id)->update(['qb_sync' => 1]);
        }
    }

    public function deleteClientToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('clients')->where('id', $id)->first();
        //file_put_contents('test.txt',print_r($data,true));
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
        $accessToken = $datas->sessionAccessToken;
        //$accessToken = $_SESSION['sessionAccessToken'];
        $dataService->updateOAuth2Token($accessToken);
        $itemObj = new IPPCustomer();
        $itemObj->Id = $data->quick_book_id;
        $itemInfo = $dataService->FindById($itemObj);
        $updatedItem = Customer::update($itemInfo, [
            "Active" => false
        ]);
        $updated = $dataService->Update($updatedItem);
        if (empty($updated)) {
            $error = $dataService->getLastError();
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 2;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
        } else {
            DB::table('clients')->where('id', $id)->update(['qb_sync' => 1]);
        }
    }

    public function storeCurrencyToQB($datas)
    {
        $id = $datas->id;
        $data = DB::table('currency')->where('id', $id)->first();
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

        $accessToken = $datas->sessionAccessToken;
        $dataService->updateOAuth2Token($accessToken);
        $create = CompanyCurrency::create([
            "Code" => $data->code,
            "Active" => true,
        ]);

        $resultObj = $dataService->Add($create);

        if (!empty($resultObj) && isset($resultObj->Id)) {
            DB::table('currency')->where('id', $data->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
        } else {
            $error = $dataService->getLastError();
            $error_log['module'] = $datas->module;
            $error_log['module_id'] = $data->id;
            $error_log['operation'] = 0;
            $error_log['error_message'] = $error->getIntuitErrorDetail();
            $errorsModel = quickbookErrorLog::create($error_log);
            //file_put_contents('test.txt',print_r($errorsModel,true));
        }
    }

    public function insertRunTime($flag, $runTimeData, $sessionAccessToken = null)
    {
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
        //$accessToken = $_SESSION['sessionAccessToken'];
        $accessToken = $sessionAccessToken;
        $dataService->updateOAuth2Token($accessToken);
        if ($flag == 'billingItem') {
            $IncomeAccountRef = $dataService->Query("select * from TaxCode");
            $DefaultTaxCodeRefValueNone = 0;
            $DefaultTaxCodeRefValueTCA = 0;
            foreach ($IncomeAccountRef as $k => $v) {
                if ($v->Name == 'None' || $v->Name == 'NONE')
                    $DefaultTaxCodeRefValueNone = $v->Id;
                else if ($v->Name == 'TCA')
                    $DefaultTaxCodeRefValueTCA = $v->Id;
                /* else
                    $DefaultTaxCodeRefValueTCA = $v->Id; */
            }

            $IncomeAccountRef = $dataService->Query("select * from Account where Name = 'Sales of Product Income'");


            $create = Item::create([
                //"Name" => $runTimeData->item_code,
                "Name" => $runTimeData->billing_name,
                "Description" => $runTimeData->description,
                "Sku" => $runTimeData->item_code,
                "Taxable" => $runTimeData->flag_prod_tax_type == '1' ? true : false,
                "SalesTaxCodeRef" => array('value' => $runTimeData->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCA : $DefaultTaxCodeRefValueNone),
                "Type" => 'Service',
                "IncomeAccountRef" => array(
                    "name" => $IncomeAccountRef[0]->Name,
                    "Value" => $IncomeAccountRef[0]->Id
                )
            ]);


            $resultObj = $dataService->Add($create);

            if (!empty($resultObj) && isset($resultObj->Id)) {
                DB::table('billing_items')->where('id', $runTimeData->billingItemID)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
            } else {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = '3';
                $error_log['module_id'] = $runTimeData->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            }
        } else if ($flag == 'costItem') {
            //file_put_contents('test.txt',print_r('test'));
            $IncomeAccountRef = $dataService->Query("select * from Account where Name = 'Sales of Product Income'");
            $ExpenseAccountRef = $dataService->Query("select * from Account where Name = 'Cost of Goods Sold'");
            $AssetAccountRef = $dataService->Query("select * from Account where Name = 'Inventory Asset'");
            $accountArr = [
                'Name' => $runTimeData->cost_name,
                'Description' => $runTimeData->code,
                'FullyQualifiedName' => $runTimeData->cost_name,
                'Classification' => 'Expense',
                'AccountType' => "Expense",
                'AccountSubType' => 'OtherMiscellaneousServiceCost',
                'AcctNum' => substr($runTimeData->code, 0, 20)
            ];

            $addAccount = Account::create($accountArr);
            $resultObj = $dataService->Add($addAccount);
            if (!empty($resultObj)) {
                $newModel = DB::table('costs')->where('id', $runTimeData->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($resultObj->Id),true);
            } else {
                $error = $dataService->getLastError();
                $error_log['module'] = '0';
                $error_log['module_id'] = $runTimeData->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            }
        } else if ($flag == 'client') {
            $IncomeAccountRef = $dataService->Query("select * from TaxCode");
            $DefaultTaxCodeRefValueNone = 0;
            $DefaultTaxCodeRefValueTCA = 0;
            foreach ($IncomeAccountRef as $k => $v) {
                if ($v->Name == 'None' || $v->Name == 'NONE')
                    $DefaultTaxCodeRefValueNone = $v->Id;
                else if ($v->Name == 'TCA')
                    $DefaultTaxCodeRefValueTCA = $v->Id;
                /* else
                    $DefaultTaxCodeRefValueTCA = $v->Id; */
            }

            $dataCountry = Country::getData($runTimeData->country);
            $create = Customer::create([
                "CompanyName" => $runTimeData->company_name,
                "DisplayName" => $runTimeData->company_name,
                "GivenName" => $runTimeData->first_name,
                "FamilyName" => $runTimeData->last_name,
                "MiddleName" => $runTimeData->middle_name,
                "PrimaryEmailAddr" => array(
                    "Address" => $runTimeData->email,
                ),
                //"Taxable" => $runTimeData->flag_prod_tax_type == '1' ? true : false,
                "Taxable" => true,
                "DefaultTaxCodeRef" => array("value" => $runTimeData->flag_prod_tax_type == '1' ? $DefaultTaxCodeRefValueTCA : $DefaultTaxCodeRefValueNone),
                "Active" => true,
                'BillAddr' => [
                    'Line1' => $runTimeData->company_address,
                    'City' => $runTimeData->city,
                    'Country' => !empty($dataCountry->name) ? $dataCountry->name : '',
                    'CountrySubDivisionCode' => $runTimeData->state,
                    'PostalCode' => $runTimeData->zipcode,
                ],

            ]);


            $resultObj = $dataService->Add($create);
            if (!empty($resultObj) && isset($resultObj->Id)) {
                DB::table('clients')->where('id', $runTimeData->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
            } else {
                $error = $dataService->getLastError();
                pre($error);
                $error_log['module'] = '11';
                $error_log['module_id'] = $runTimeData->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            }
        } else if ($flag == 'vendor') {
            $newVendor = [
                'BillAddr' => [
                    'Line1' => $runTimeData->street,
                    'City' => $runTimeData->city,
                    'Country' => $runTimeData->country,
                    'CountrySubDivisionCode' => $runTimeData->state,
                    'PostalCode' => $runTimeData->zipcode,
                ],

                'Vendor1099' => 'false',
                'CurrencyRef' => $runTimeData->currency_code,
                'GivenName' => $runTimeData->first_name,
                'MiddleName' => $runTimeData->middle_name,
                'FamilyName' => $runTimeData->last_name,
                'CompanyName' => $runTimeData->company_name,
                'DisplayName' => $runTimeData->company_name,
                'PrintOnCheckName' => $runTimeData->company_name,
                'PrimaryPhone' => [
                    'FreeFormNumber' => $runTimeData->company_phone
                ],
                'PrimaryEmailAddr' => [
                    'Address' => $runTimeData->email
                ]

            ];
            $addVendor = Vendor::create($newVendor);
            $resultObj = $dataService->Add($addVendor);
            if (!empty($resultObj)) {
                DB::table('vendors')->where('id', $runTimeData->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($resultObj->Id,true));

            } else {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = '1';
                $error_log['module_id'] = $runTimeData->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            }
        } else if ($flag == 'accounts') {
            if ($runTimeData->detail_type == 41) { // Petty Cash
                $accountType = 'Bank';
                $accountSubType = 'CashOnHand';
                $Classification = 'Asset';
            } else if ($runTimeData->detail_type == 42) { //Current Bank Account
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            } else if ($runTimeData->detail_type == 43) { //Credit Card
                $accountType = "Credit Card";
                $accountSubType = 'CreditCard';
                $Classification = 'Liability';
            } else if ($runTimeData->detail_type == 44) { //Overdraft
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            } else { // Other
                $accountType = "Bank";
                $accountSubType = 'Savings';
                $Classification = 'Asset';
            }

            $accountArr = [
                'Name' => $runTimeData->name,
                'Description' => $runTimeData->description,
                'FullyQualifiedName' => $runTimeData->name,
                'Classification' => $Classification,
                'AccountType' => $accountType,
                'AccountSubType' => $accountSubType,
                'OpeningBalance' => $runTimeData->opening_balance,
                'CurrentBalance' => $runTimeData->available_balance,
                'OpeningBalanceDate' => date('Y-m-d'),
                'CurrencyRef' => $runTimeData->currency_code
            ];

            $addAccount = Account::create($accountArr);
            $resultObj = $dataService->Add($addAccount);
            if (!empty($resultObj)) {
                DB::table('cashcredit')->where('id', $runTimeData->id)->update(['quick_book_id' => $resultObj->Id, 'qb_sync' => 1]);
                //file_put_contents('test.txt',print_r($createdAccount->Id,true));
            } else {
                $error = $dataService->getLastError();
                //pre($error);
                $error_log['module'] = '2';
                $error_log['module_id'] = $runTimeData->id;
                $error_log['operation'] = 0;
                $error_log['error_message'] = $error->getIntuitErrorDetail();
                $errorsModel = quickbookErrorLog::create($error_log);
            }
        }

        return $resultObj->Id;
    }


    public function backgroundPost($url)
    {
        //file_put_contents('test.txt',print_r('abc',true));
        $parts = parse_url($url);
        $parts['path'] = $parts['path'] . '/' . str_replace('model=', '', $parts['query']);
        $fp = fsockopen('ssl://' . $parts['host'], isset($parts['port']) ? $parts['port'] : 443, $errno, $errstr, 30);
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
            sleep(5);
            fclose($fp);
            /* while (!feof($fp)) {
                echo fgets($fp, 128);
            } */
            //return true;
        }
    }

    public function backgroundPostForManifest($url)
    {
        //file_put_contents('test.txt',print_r('abc',true));
        $parts = parse_url($url);
        $parts['path'] = $parts['path'] . '/' . str_replace('datas=', '', $parts['query']);
        $fp = fsockopen('ssl://' . $parts['host'], isset($parts['port']) ? $parts['port'] : 443, $errno, $errstr, 30);
        //$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 21, $errno, $errstr, 30);
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
            fwrite($fp, str_replace('datas=', '/', $out));
            fclose($fp);
            return true;
        }
    }

    /* public function backgroundPostForManifest($url)
    {
        //file_put_contents('test.txt',print_r('abc',true));
        $parts = parse_url($url);
        $parts['path'] = $parts['path'] . '/' . str_replace('jobId=', '', $parts['query']);
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 21, $errno, $errstr, 30);
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
            
            fwrite($fp, str_replace('jobId=', '/', $out));
            fclose($fp);
            return true;
        }
    } */
}
