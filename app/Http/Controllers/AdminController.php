<?php

namespace App\Http\Controllers;

use App\Cargo;
use Illuminate\Http\Request;
use App\User;
use App\CargoProductDetails;
use App\CargoConsolidateAwbHawb;
use App\CargoContainers;
use App\CargoPackages;
use App\Currency;
use App\PaymentTerms;
use App\Clients;
use App\Vendors;
use Session;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Illuminate\Support\Facades\Validator;
use Response;
use PDF;
use App\Admin;
use Excel;
use stdClass;

class AdminController extends Controller
{

    public function callQb($data)
    {
        $datas = unserialize(base64_decode($data, true));
        file_put_contents('test.txt', print_r($datas, true));
        // exit;

        /*session_start();           
         if(isset($_SESSION['sessionAccessToken'])){
            file_put_contents('test.txt',print_r($datas,true));
            $modelAdmin = new Admin;
            $modelAdmin->qbApiCall('expenses',$datas);
        }*/
        $modelAdmin = new Admin;
        $modelAdmin->qbApiCall($datas['flagModule'], (object) $datas);
    }

    public function callbacksyncdemo($accessToken)
    {
        session_start();
        $accessTokens = unserialize(base64_decode($accessToken, true));
        // Create customer
        $customerData = DB::table('clients')
            //->where('deleted', '0')->where('qb_sync', 0)
            ->orderBy('id', 'desc')->limit(1)->get();
        foreach ($customerData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '11';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'client';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateClient';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteClient';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }
    }

    public function callbacksync($accessToken)
    {
        session_start();
        $accessTokens = unserialize(base64_decode($accessToken, true));
        $checkCronStatus = DB::table('qb_cron')
            ->where('flag_complete', 0)
            ->count();
        if ($checkCronStatus > 0)
            return;

        $dbCronId = DB::table('qb_cron')->insertGetId([
            'start_time'      => gmdate('Y-m-d H:i:s'),
            'flag_complete'   => 0,
            //'created_by' => auth()->user()->id,
        ]);

        // Create customer
        $customerData = DB::table('clients')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->where('client_flag', 'B')
            ->get();
        foreach ($customerData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '11';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'client';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateClient';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteClient';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Vendor
        $vendorData = DB::table('vendors')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->get();
        foreach ($vendorData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '1';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'vendor';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateVendor';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteVendor';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Costs
        $costsData = DB::table('costs')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->get();
        foreach ($costsData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '0';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'cost';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateCost';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteCost';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Billing items
        $billingItemsData = DB::table('billing_items')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->get();
        foreach ($billingItemsData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '3';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'billing-item';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'update-billing-item';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'delete-billing-item';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Cash/bank
        $cashCreditData = DB::table('cashcredit')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->get();
        foreach ($cashCreditData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '2';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'account';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateAccount';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteAccount';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Other accounts
        $otherAccountsData = DB::table('cashcredit_detail_type')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->get();
        foreach ($otherAccountsData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '17';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'otherAccount';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateOtherAccount';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteOtherAccount';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Currency
        /* $currencyData = DB::table('currency')
            //->where('deleted', '0')->where('qb_sync', 0)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->get();
        foreach ($currencyData as $k => $v) {
            $fData['id'] = $v->id;
            $fData['module'] = '12';

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'currencies';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        } */

        // Create Expense
        $expensesData = DB::table('expenses')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('expense_id', 'desc')
            //->limit(1)
            ->get();
        foreach ($expensesData as $k => $v) {

            if (!empty($v->cargo_id)) {
                $qbModule = '5';
            } else if (!empty($v->house_file_id)) {
                $qbModule = '16';
            } else if (!empty($v->ups_details_id)) {
                $qbModule = '4';
            } else if (!empty($v->ups_master_id)) {
                $qbModule = '21';
            } else if (!empty($v->aeropost_id)) {
                $qbModule = '14';
            } else if (!empty($v->aeropost_master_id)) {
                $qbModule = '22';
            } else if (!empty($v->ccpack_id)) {
                $qbModule = '15';
            } else if (!empty($v->ccpack_master_id)) {
                $qbModule = '23';
            }

            $fData['id'] = $v->expense_id;
            $fData['module'] = $qbModule;

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'expenses';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateExpense';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteExpense';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        // Create Invoice
        $invoicesData = DB::table('invoices')
            //->where('deleted', '0')->where('qb_sync', 0)
            //->orderBy('id', 'desc')
            //->limit(1)
            ->get();
        foreach ($invoicesData as $k => $v) {

            if (!empty($v->cargo_id)) {
                $qbModule = '6';
            } else if (!empty($v->hawb_hbl_no) && $v->housefile_module == 'cargo') {
                $qbModule = '13';
            } else if (!empty($v->ups_id)) {
                $qbModule = '7';
            } else if (!empty($v->ups_master_id)) {
                $qbModule = '18';
            } else if (!empty($v->aeropost_id)) {
                $qbModule = '9';
            } else if (!empty($v->aeropost_master_id)) {
                $qbModule = '19';
            } else if (!empty($v->ccpack_id)) {
                $qbModule = '8';
            } else if (!empty($v->ccpack_master_id)) {
                $qbModule = '20';
            }

            $fData['id'] = $v->id;
            $fData['module'] = $qbModule;

            if ($v->deleted == '0' && empty($v->quick_book_id))
                $fData['flagModule'] = 'invoice';
            else if ($v->deleted == '0' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'updateInvoice';
            else if ($v->deleted == '1' && !empty($v->quick_book_id) && $v->qb_sync == 0)
                $fData['flagModule'] = 'deleteInvoice';
            else
                continue;

            $fData['sessionAccessToken'] = $accessTokens['sessionAccessToken'];

            // Store expense to QB
            $newModel = base64_encode(serialize($fData));
            //$newTest = unserialize(base64_decode($newModel, true));
            //pre($newTest);
            $urlAction = url('call/qb?model=' . $newModel);

            $adminModel = new Admin;
            $adminModel->backgroundPost($urlAction);
        }

        DB::table('qb_cron')->update([
            'end_time'      => gmdate('Y-m-d H:i:s'),
            'flag_complete'   => 1,
        ], ['id' => $dbCronId]);
    }

    public function importqb()
    {
        return view("importqbdata");
    }

    public function importqbdata(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300);
        $inputfile = $request->file('import');
        //$header  = Excel::load($inputfile)->get()->first()->keys()->toArray();
        //pre($header,1);
        if ($_POST['flag'] == 'customer') {
            $theArray = Excel::toArray(new stdClass(), $inputfile);
            $theArray = $theArray[0];
            $this->importQBDataCustomer($theArray);
            // Excel::load($inputfile, function ($reader) {
            //     $i = 1;
            //     foreach ($reader->toArray() as $key => $row) {
            //         if (!empty($row['Customer'])) {
            //             $input['company_name'] = $row['Customer'];
            //         }
            //         if (!empty($row['Email'])) {
            //             $input['email'] = $row['Email'];
            //         }
            //         if (!empty($row['Billing Address'])) {
            //             $input['company_address'] = $row['Billing Address'];
            //         }
            //         if (!empty($row['Currency'])) {
            //             $currencyModel = new Currency();
            //             $dataCurrency = $currencyModel->getDataUsingCode($row['Currency']);
            //             if (!empty($dataCurrency))
            //                 $input['currency'] = $dataCurrency->id;
            //         }
            //         if (!empty($row['Taxable'])) {
            //             $input['flag_prod_tax_type'] = ($row['Taxable'] == 'Yes') ? 1 : 0;

            //             if (!empty($row['Tax Rate'])) {
            //                 $input['flag_prod_tax_amount'] = '10';
            //             }
            //         }
            //         if (!empty($row['Terms'])) {
            //             $termsModel = new PaymentTerms();
            //             $dataTerms = $termsModel->getDataUsingName($row['Terms']);
            //             if (!empty($dataTerms))
            //                 $input['payment_term'] = $dataTerms->id;
            //         }

            //         $model = Clients::create($input);
            //         /*if($i == 5)
            //             pre("test");*/

            //         $i++;
            //     }
            // });
        }

        if ($_POST['flag'] == 'vendor') {
            $this->importQBDataVendor($theArray);
            // Excel::load($inputfile, function ($reader) {
            //     $i = 1;
            //     foreach ($reader->toArray() as $key => $row) {
            //         if (!empty($row['Vendor'])) {
            //             $input['company_name'] = $row['Vendor'];
            //         }
            //         if (!empty($row['Address'])) {
            //             $input['street'] = $row['Address'];
            //         }
            //         if (!empty($row['Currency'])) {
            //             $currencyModel = new Currency();
            //             $dataCurrency = $currencyModel->getDataUsingCode($row['Currency']);
            //             if (!empty($dataCurrency))
            //                 $input['currency'] = $dataCurrency->id;
            //         }
            //         if (!empty($row['ZIP'])) {
            //             $input['zipcode'] = $row['ZIP'];
            //         }
            //         if (!empty($row['State'])) {
            //             $input['state'] = $row['State'];
            //         }
            //         if (!empty($row['City'])) {
            //             $input['city'] = $row['City'];
            //         }
            //         if (!empty($row['Country'])) {
            //             $input['country'] = $row['Country'];
            //         }


            //         $model = Vendors::create($input);
            //         /*if($i == 5)
            //             pre("test");*/

            //         $i++;
            //     }
            // });
        }
    }

    public function importQBDataCustomer($dData){
        $dData = arrayKeyValueFlip($dData);
        $i = 1;
        foreach ($dData as $key => $row) {
            if (!empty($row['Customer'])) {
                $input['company_name'] = $row['Customer'];
            }
            if (!empty($row['Email'])) {
                $input['email'] = $row['Email'];
            }
            if (!empty($row['Billing Address'])) {
                $input['company_address'] = $row['Billing Address'];
            }
            if (!empty($row['Currency'])) {
                $currencyModel = new Currency();
                $dataCurrency = $currencyModel->getDataUsingCode($row['Currency']);
                if (!empty($dataCurrency))
                    $input['currency'] = $dataCurrency->id;
            }
            if (!empty($row['Taxable'])) {
                $input['flag_prod_tax_type'] = ($row['Taxable'] == 'Yes') ? 1 : 0;

                if (!empty($row['Tax Rate'])) {
                    $input['flag_prod_tax_amount'] = '10';
                }
            }
            if (!empty($row['Terms'])) {
                $termsModel = new PaymentTerms();
                $dataTerms = $termsModel->getDataUsingName($row['Terms']);
                if (!empty($dataTerms))
                    $input['payment_term'] = $dataTerms->id;
            }

            $model = Clients::create($input);
            /*if($i == 5)
                        pre("test");*/

            $i++;
        }
    }
    public function importQBDataVendor($dData)
    {
        $dData = arrayKeyValueFlip($dData);
        $i = 1;
        foreach ($dData as $key => $row) {
            if (!empty($row['Vendor'])) {
                $input['company_name'] = $row['Vendor'];
            }
            if (!empty($row['Address'])) {
                $input['street'] = $row['Address'];
            }
            if (!empty($row['Currency'])) {
                $currencyModel = new Currency();
                $dataCurrency = $currencyModel->getDataUsingCode($row['Currency']);
                if (!empty($dataCurrency))
                    $input['currency'] = $dataCurrency->id;
            }
            if (!empty($row['ZIP'])) {
                $input['zipcode'] = $row['ZIP'];
            }
            if (!empty($row['State'])) {
                $input['state'] = $row['State'];
            }
            if (!empty($row['City'])) {
                $input['city'] = $row['City'];
            }
            if (!empty($row['Country'])) {
                $input['country'] = $row['Country'];
            }


            $model = Vendors::create($input);
            /*if($i == 5)
                        pre("test");*/

            $i++;
        }
    }

    public function closefiles()
    {
        $cargoFiles = DB::table('cargo')->where('deleted', '0')->whereNull('file_close')->pluck('file_number', 'id');
        return view('common.closefiles', ['cargoFiles' => $cargoFiles]);
    }
    public function closefilessubmit(Request $request)
    {
        $input = $request->all();
        $ids = $input['files'];
        $module = $input['module'];
        if ($module == 'Cargo')
            $tblName = 'cargo';
        else if ($module == 'UPS')
            $tblName = 'ups_details';
        else if ($module == 'UPSMaster')
            $tblName = 'ups_master';
        else if ($module == 'Aeropost')
            $tblName = 'aeropost';
        else if ($module == 'AeropostMaster')
            $tblName = 'aeropost_master';
        else if ($module == 'CCPack')
            $tblName = 'ccpack';
        else if ($module == 'CcpackMaster')
            $tblName = 'ccpack_master';
        else if ($module == 'houseFile')
            $tblName = 'hawb_files';
        $data = DB::table($tblName)->whereIn('id', $ids)->update(['file_close' => 1, 'close_unclose_date' => date('Y-m-d'), 'close_unclose_by' => auth()->user()->id]);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect('closefiles');
    }
    public function closefilessubmitsingle($module = null, $id = null)
    {
        if ($module == 'Cargo') {
            $tblName = 'cargo';
            $redirectUrl = 'cargoall';
        } else if ($module == 'UPS') {
            $tblName = 'ups_details';
            $redirectUrl = 'ups';
        } else if ($module == 'UPSMaster') {
            $tblName = 'ups_master';
            $redirectUrl = 'ups-master';
        } else if ($module == 'Aeropost') {
            $tblName = 'aeropost';
            $redirectUrl = 'aeroposts';
        } else if ($module == 'AeropostMaster') {
            $tblName = 'aeropost_master';
            $redirectUrl = 'aeropost-master';
        } else if ($module == 'CCPack') {
            $tblName = 'ccpack';
            $redirectUrl = 'ccpack';
        } else if ($module == 'CcpackMaster') {
            $tblName = 'ccpack_master';
            $redirectUrl = 'ccpack-master';
        } else if ($module == 'houseFile') {
            $tblName = 'hawb_files';
            $redirectUrl = 'hawbfiles';
        }
        $data = DB::table($tblName)->where('id', $id)->update(['file_close' => 1, 'close_unclose_date' => date('Y-m-d'), 'close_unclose_by' => auth()->user()->id]);
        Session::flash('flash_message', 'Record has been updated successfully');
        return redirect($redirectUrl);
    }

    public function reactivatefile()
    {
        $id = $_POST['id'];
        $module = $_POST['flagModule'];
        if ($module == 'Cargo')
            $tblName = 'cargo';
        else if ($module == 'Ups')
            $tblName = 'ups_details';
        else if ($module == 'UpsMaster')
            $tblName = 'ups_master';
        else if ($module == 'Aeropost')
            $tblName = 'aeropost';
        else if ($module == 'AeropostMaster')
            $tblName = 'aeropost_master';
        else if ($module == 'CCPack')
            $tblName = 'ccpack';
        else if ($module == 'CcpackMaster')
            $tblName = 'ccpack_master';
        else if ($module == 'HouseFile')
            $tblName = 'hawb_files';
        $data = DB::table($tblName)->where('id', $id)->update(['file_close' => null, 'close_unclose_date' => date('Y-m-d'), 'close_unclose_by' => auth()->user()->id]);
    }

    public function getfilesforclose()
    {
        $module = $_POST['module'];
        if ($module == 'Cargo')
            $files = DB::table('cargo')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'houseFile')
            $files = DB::table('hawb_files')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'UPS')
            $files = DB::table('ups_details')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'UPSMaster')
            $files = DB::table('ups_master')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'Aeropost')
            $files = DB::table('aeropost')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'AeropostMaster')
            $files = DB::table('aeropost_master')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'CCPack')
            $files = DB::table('ccpack')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();
        else if ($module == 'CcpackMaster')
            $files = DB::table('ccpack_master')->select('id', 'file_number')->whereNull('file_close')->where('deleted', '0')->get();

        return json_encode($files);
    }

    public function closefileslisting()
    {
        $users = DB::table('users')->select(['id', 'name'])->where('deleted', 0)->where('status', 1)->pluck('name', 'id');
        return view("common.closed-file-index", ['users' => $users]);
    }

    public function listbydatatableserverside(Request $request)
    {
        $req = $request->all();

        $flagModule = $req['flagModule'];
        $closedBy = $req['closedBy'];
        /* $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : date('Y-m-01');
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : date('Y-m-d'); */
        $fromDate = !empty($req['fromDate']) ? date('Y-m-d', strtotime($req['fromDate'])) : '';
        $toDate = !empty($req['toDate']) ? date('Y-m-d', strtotime($req['toDate'])) : '';
        $start = $req['start'];
        $length = $req['length'];
        $search = $req['search']['value'];
        $order = $req['order'][0]['dir'];
        $column = $req['order'][0]['column'];
        if ($flagModule == 'Cargo') {
            $tblName = 'cargo';
            $model = 'App\Cargo';
        } else if ($flagModule == 'HouseFile') {
            $tblName = 'hawb_files';
            $model = 'App\HawbFiles';
        } else if ($flagModule == 'Ups') {
            $tblName = 'ups_details';
            $model = 'App\Ups';
        } else if ($flagModule == 'UpsMaster') {
            $tblName = 'ups_master';
            $model = 'App\UpsMaster';
        } else if ($flagModule == 'Aeropost') {
            $tblName = 'aeropost';
            $model = 'App\Aeropost';
        } else if ($flagModule == 'AeropostMaster') {
            $tblName = 'aeropost_master';
            $model = 'App\AeropostMaster';
        } else if ($flagModule == 'CCPack') {
            $tblName = 'ccpack';
            $model = 'App\ccpack';
        } else if ($flagModule == 'CcpackMaster') {
            $tblName = 'ccpack_master';
            $model = 'App\CcpackMaster';
        }

        $orderby = ['moduleFileNumber', 'moduleTracking', 'moduleConsignee', 'moduleShipper', 'closedDate', 'closedBy'];

        $total = $model::selectRaw('count(*) as total')
            ->where('file_close', 1)
            ->where('deleted', '0');

        if (!empty($fromDate) && !empty($toDate)) {
            $total = $total->whereBetween('close_unclose_date', array($fromDate, $toDate));
        }
        if (!empty($closedBy)) {
            $total = $total->where('close_unclose_by', $closedBy);
        }

        $total = $total->first();
        $totalfiltered = $total->total;

        if ($flagModule == 'Cargo') {
            $query = DB::table('cargo')
                ->selectRaw('cargo.id as moduleId,cargo.file_number as moduleFileNumber, cargo.awb_bl_no as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,cargo.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'cargo.close_unclose_by')
                ->where('cargo.deleted', '0')
                ->where('cargo.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('cargo')
                ->leftJoin('clients as c1', 'c1.id', '=', 'cargo.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'cargo.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'cargo.close_unclose_by')
                ->where('cargo.deleted', '0')
                ->where('cargo.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'HouseFile') {
            $query = DB::table('hawb_files')
                ->selectRaw("hawb_files.id as moduleId,hawb_files.file_number as moduleFileNumber,IF(hawb_files.cargo_operation_type = 1, hawb_files.hawb_hbl_no, hawb_files.export_hawb_hbl_no) as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,hawb_files.close_unclose_date as closedDate,users.name as closedBy")
                ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'hawb_files.close_unclose_by')
                ->where('hawb_files.deleted', '0')
                ->where('hawb_files.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('hawb_files')
                ->leftJoin('clients as c1', 'c1.id', '=', 'hawb_files.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'hawb_files.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'hawb_files.close_unclose_by')
                ->where('hawb_files.deleted', '0')
                ->where('hawb_files.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'Ups') {
            $query = DB::table('ups_details')
                ->selectRaw('ups_details.id as moduleId,ups_details.file_number as moduleFileNumber, ups_details.awb_number as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,ups_details.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ups_details.close_unclose_by')
                ->where('ups_details.deleted', '0')
                ->where('ups_details.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('ups_details')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_details.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_details.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ups_details.close_unclose_by')
                ->where('ups_details.deleted', '0')
                ->where('ups_details.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'UpsMaster') {
            $query = DB::table('ups_master')
                ->selectRaw('ups_master.id as moduleId,ups_master.file_number as moduleFileNumber, ups_master.tracking_number as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,ups_master.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ups_master.close_unclose_by')
                ->where('ups_master.deleted', '0')
                ->where('ups_master.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('ups_master')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ups_master.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ups_master.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ups_master.close_unclose_by')
                ->where('ups_master.deleted', '0')
                ->where('ups_master.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'Aeropost') {
            $query = DB::table('aeropost')
                ->selectRaw('aeropost.id as moduleId,aeropost.file_number as moduleFileNumber, aeropost.tracking_no as moduleTracking, c1.company_name as moduleConsignee, aeropost.from_location as moduleShipper,aeropost.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('users', 'users.id', '=', 'aeropost.close_unclose_by')
                ->where('aeropost.deleted', '0')
                ->where('aeropost.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('aeropost')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost.consignee')
                ->leftJoin('users', 'users.id', '=', 'aeropost.close_unclose_by')
                ->where('aeropost.deleted', '0')
                ->where('aeropost.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'AeropostMaster') {
            $query = DB::table('aeropost_master')
                ->selectRaw('aeropost_master.id as moduleId,aeropost_master.file_number as moduleFileNumber, aeropost_master.tracking_number as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,aeropost_master.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'aeropost_master.close_unclose_by')
                ->where('aeropost_master.deleted', '0')
                ->where('aeropost_master.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('aeropost_master')
                ->leftJoin('clients as c1', 'c1.id', '=', 'aeropost_master.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'aeropost_master.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ups_master.close_unclose_by')
                ->where('aeropost_master.deleted', '0')
                ->where('aeropost_master.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'CCPack') {
            $query = DB::table('ccpack')
                ->selectRaw('ccpack.id as moduleId,ccpack.file_number as moduleFileNumber, ccpack.awb_number as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,ccpack.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ccpack.close_unclose_by')
                ->where('ccpack.deleted', '0')
                ->where('ccpack.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('ccpack')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack.consignee')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ccpack.close_unclose_by')
                ->where('ccpack.deleted', '0')
                ->where('ccpack.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        } else if ($flagModule == 'CcpackMaster') {
            $query = DB::table('ccpack_master')
                ->selectRaw('ccpack_master.id as moduleId,ccpack_master.file_number as moduleFileNumber, ccpack_master.tracking_number as moduleTracking, c1.company_name as moduleConsignee, c2.company_name as moduleShipper,ccpack_master.close_unclose_date as closedDate,users.name as closedBy')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ccpack_master.close_unclose_by')
                ->where('ccpack_master.deleted', '0')
                ->where('ccpack_master.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $query = $query->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $query = $query->where('close_unclose_by', $closedBy);
            }
            $filteredq = DB::table('ccpack_master')
                ->leftJoin('clients as c1', 'c1.id', '=', 'ccpack_master.consignee_name')
                ->leftJoin('clients as c2', 'c2.id', '=', 'ccpack_master.shipper_name')
                ->leftJoin('users', 'users.id', '=', 'ups_master.close_unclose_by')
                ->where('ccpack_master.deleted', '0')
                ->where('ccpack_master.file_close', 1);
            if (!empty($fromDate) && !empty($toDate)) {
                $filteredq = $filteredq->whereBetween('close_unclose_date', array($fromDate, $toDate));
            }
            if (!empty($closedBy)) {
                $filteredq = $filteredq->where('close_unclose_by', $closedBy);
            }
        }


        if ($search != '') {
            $query->where(function ($query2) use ($search, $flagModule) {
                if ($flagModule == 'Cargo') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'HouseFile') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'Ups') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'UpsMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'AeropostMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'CCPack') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'CcpackMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                }
            });
            $filteredq->where(function ($query2) use ($search, $flagModule) {
                if ($flagModule == 'Cargo') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_bl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'HouseFile') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('export_hawb_hbl_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'Ups') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'UpsMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'Aeropost') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_no', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('from_location', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'AeropostMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'CCPack') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('awb_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                } else if ($flagModule == 'CcpackMaster') {
                    $query2->where('file_number', 'like', '%' . $search . '%')
                        ->orWhere('tracking_number', 'like', '%' . $search . '%')
                        ->orWhere('c1.company_name', 'like', '%' . $search . '%')
                        ->orWhere('c2.company_name', 'like', '%' . $search . '%')
                        ->orWhere('users.name', 'like', '%' . $search . '%');
                }
            });
            $filteredq = $filteredq->selectRaw('count(*) as total')->first();
            $totalfiltered = $filteredq->total;
        }
        $query = $query->orderBy($orderby[$column], $order)->offset($start)->limit($length)->get();

        $data = [];
        foreach ($query as $key => $value) {
            $reactivateUrl =  route('reactivatefile', [$flagModule, $value->moduleId]);
            $action = '';
            $action .= '<a class="re-activate" data-id="' . $value->moduleId . '"  href="javascript:void(0);" title="Re-Activate">Re-Activate</a>';
            $data[] = [$value->moduleFileNumber, $value->moduleTracking, $value->moduleConsignee, $value->moduleShipper, !empty($value->closedDate) ? date('d-m-Y', strtotime($value->closedDate)) : '-', !empty($value->closedBy) ? $value->closedBy : '-', $action];
        }
        $json_data = array(
            "draw" => intval($_REQUEST['draw']),
            "recordsTotal" => intval($total->total),
            "recordsFiltered" => intval($totalfiltered),
            "data" => $data,
        );
        return Response::json($json_data);
    }
}
