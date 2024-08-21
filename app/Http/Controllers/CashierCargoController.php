<?php

namespace App\Http\Controllers;

use App\Cargo;
use Illuminate\Http\Request;
use App\User;
use App\Invoices;
use App\CargoProductDetails;
use App\CargoConsolidateAwbHawb;
use App\CargoContainers;
use App\CargoPackages;
use App\HawbFiles;
use Session;
use Illuminate\Support\Facades\DB;
use App\Activities;
use Illuminate\Support\Facades\Validator;
use Response;
use PDF;
use App\localInvoicePayment;
use Config;
use Illuminate\Support\Facades\Storage;
use QuickBooksOnline\API\Facades\Invoice;

class CashierCargoController extends Controller
{
    public function cashiercargoall()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $dataCashierCargo = DB::table('cargo')
            ->where('deleted', 0)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('cargo_operation_type', '1')
                    ->orWhere('cargo_operation_type', '2');
            })
            ->orderBy('id', 'desc')
            ->get();

        return view("cashier-role.cargo.index", ['dataCashierCargo' => $dataCashierCargo]);
    }

    public function cashiercargoimportsajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $dataCashierCargo = DB::table('cargo')
            //->where('cashier_id',auth()->user()->id)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where('cargo_operation_type', '1')
            ->orderBy('id', 'desc')
            ->get();

        return view("cashier-role.cargo.importindexajax", ['dataCashierCargo' => $dataCashierCargo]);
    }

    public function cashiercargoexportsajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $dataCashierCargo = DB::table('cargo')
            //->where('cashier_id',auth()->user()->id)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where('cargo_operation_type', '2')
            ->orderBy('id', 'desc')
            ->get();

        return view("cashier-role.cargo.exportindexajax", ['dataCashierCargo' => $dataCashierCargo]);
    }

    public function cashiercargoallajax()
    {
        $checkPermission = User::checkPermission(['listing_cargo'], '', auth()->user()->id);
        if (!$checkPermission)
            return redirect('/home');

        $dataCashierCargo = DB::table('cargo')
            //->where('cashier_id',auth()->user()->id)
            ->where('deleted', 0)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('cargo_operation_type', '1')
                    ->orWhere('cargo_operation_type', '2');
            })
            ->orderBy('id', 'desc')
            ->get();

        return view("cashier-role.cargo.cargoallajax", ['dataCashierCargo' => $dataCashierCargo]);
    }

    public function viewcargodetailforcashier($id, $flag = null)
    {
        if ($flag == 'fromNotification')
            Cargo::where('id', $id)->update(['display_notification_cashier' => 0]);

        $model = Cargo::find($id);
        $dataHawbIds = explode(',', $model->hawb_hbl_no);

        $HouseAWBData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->get();
        return view('cashier-role.cargo.viewcargodetailforcashier', ['model' => $model, 'id' => $model->cargo_operation_type, 'HouseAWBData' => $HouseAWBData]);
    }

    public function releasereceiptbycashier($cargoId, $cargoType)
    {

        $model = DB::table('cargo')->where('id', $cargoId)->first();
        $dataStorageTotal = DB::table('invoice_item_details')
            ->select(DB::raw('SUM(invoice_item_details.total_of_items) as storageTotal'))
            ->join('invoices', 'invoices.id', '=', 'invoice_item_details.invoice_id')
            ->whereIn('invoice_item_details.item_code', ['SCD', 'SCW', 'SCM', 'SCC'])
            ->where('invoices.cargo_id', $cargoId)
            ->first();

        $dataHawbIds = explode(',', $model->hawb_hbl_no);

        $missingPackage = DB::table('hawb_files')
            //->select(DB::raw('SUM(invoice_item_details.total_of_items) as storageTotal'))
            ->select('*')
            ->whereIn('id', $dataHawbIds)
            ->where('verify_flag', '0')
            ->get();


        $noOfPackageData = DB::table('hawb_files')->whereIn('id', $dataHawbIds)->count();
        if ($cargoType == 1) {
            $pdf = PDF::loadView('cashier-role.cargo.printimportfilereleasereceipt', ['model' => $model, 'noOfPackageData' => $noOfPackageData, 'storageCharge' => $dataStorageTotal->storageTotal, 'missingPackage' => $missingPackage]);
        } else {
            $pdf = PDF::loadView('cashier-role.cargo.printexportfilereleasereceipt', ['model' => $model, 'noOfPackageData' => $noOfPackageData, 'storageCharge' => $dataStorageTotal->storageTotal, 'missingPackage' => $missingPackage]);
        }

        $pdf_file = $model->file_number . '_release-receipt.pdf';
        $pdf_path = 'public/releaseReceipts/' . $pdf_file;
        $pdf->save($pdf_path);
        return response()->file($pdf_path);
    }
    public function cashierlocalfilelisting()
    {
        $localFileData = DB::table('cargo')->where('rental', '1')->where('cargo_operation_type', '3')->orderBy('id', 'desc')->get();
        return view('cashier-role.cargo.localfile_listing', compact('localFileData'));
    }

    public function changestatusoflocalfile($status, $id)
    {
        $a = [];
        $localData = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $id)->orderBy('id', 'DESC')->first();
        //pre($localData);
        $mainInvoiceData = DB::table('invoices')->where('cargo_id', $id)->orderBy('id', 'DESC')->first();
        $totalPaidAmount = $mainInvoiceData->credits;
        $mainFileData = DB::table('cargo')->where('id', $id)->first();
        //pre($status);
        if ($status == 'paid') {
            if (count($localData) > 0) {
                $localFileDate = $localData->date;
                $input['total'] = $localData->total;
                $input['local_invoice_id'] = $id;
                $input['status'] = 'p';
                $input['mail_send'] = '1';
                $input['created_by'] = auth()->user()->id;
                $input['created_at'] = gmdate('Y-m-d H:i:s');
                $mainFileDate = $mainFileData->rental_ending_date;
            } else {
                $localFileDate = $mainFileData->opening_date;
                $input['total'] = $mainFileData->rental_cost;
                $input['local_invoice_id'] = $id;
                $input['status'] = 'p';
                $input['mail_send'] = '1';
                $input['created_by'] = auth()->user()->id;
                $input['created_at'] = gmdate('Y-m-d H:i:s');
                $mainFileDate = $mainFileData->rental_ending_date;
            }
            $datediff = date_diff(date_create($mainFileDate), date_create($localFileDate));
            $diff = $datediff->format("%m");
            if ($diff != 0) {
                for ($i = 0; $i < $diff; $i++) {
                    $totalPaidAmount = $totalPaidAmount + $input['total'];
                    $input['duration'] = date('d-m-Y', strtotime($localFileDate)) . ' TO ';
                    $localFileDate = date('d-m-Y', strtotime('+1month', strtotime($localFileDate)));
                    $input['date'] = date('Y-m-d', strtotime($localFileDate));
                    $input['duration'] = $input['duration'] . $localFileDate;
                    localInvoicePayment::Create($input);
                    $newLocalData = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $id)->orderBy('id', 'DESC')->first();
                    $modelActivities = new Activities;
                    $modelActivities->type = 'localInvoicePayment';
                    $modelActivities->related_id = $mainFileData->id;
                    $modelActivities->user_id   = auth()->user()->id;
                    $modelActivities->description = 'Invoice for the duration ' . $newLocalData->duration . ' has been generated';
                    $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
                    $modelActivities->save();
                }
                $localDataForCount = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $id)->orderBy('id', 'DESC')->get();
                $totalPaidAmount = $input['total'] * count($localDataForCount);
            } else {
                $localDataForCount = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $id)->orderBy('id', 'DESC')->get();
                $totalPaidAmount = $input['total'] * count($localDataForCount);
            }
            //pre($a);
            DB::table('cargo')->where('id', $id)->update(['rental_paid_status' => 'p', 'updated_by' => auth()->user()->id, 'updated_on' => gmdate('Y-m-d H:i:s')]);
            $data = DB::table('cargo')->where('id', $id)->get();
            DB::table('invoices')->where('cargo_id', $id)->update(['payment_status' => 'Paid', 'updated_at' => gmdate('Y-m-d H:i:s'), 'credits' => $totalPaidAmount]);
            DB::table('local_invoice_payment_detail')->where('local_invoice_id', $id)->update(['status' => 'p', 'updated_by' => auth()->user()->id, 'updated_at' => gmdate('Y-m-d H:i:s')]);
            Session::flash('flash_message', 'Status has been changed successfully.');
            return redirect('viewcargolocalfiledetailforcashier/' . $id);
        } else {
            $localDataForCount = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $id)->orderBy('id', 'DESC')->get();
            $totalPaidAmount = $localData->total * count($localDataForCount);
            DB::table('cargo')->where('id', $id)->update(['rental_paid_status' => 'up', 'updated_by' => auth()->user()->id, 'updated_on' => gmdate('Y-m-d H:i:s')]);
            DB::table('invoices')->where('cargo_id', $id)->update(['payment_status' => 'Pending', 'updated_at' => gmdate('Y-m-d H:i:s'), 'credits' => $totalPaidAmount]);
            Session::flash('flash_message', 'Status has been changed successfully.');
            return redirect('viewcargolocalfiledetailforcashier/' . $id);
        }
    }

    public function getAllDetail($id)
    {
        $localCargoFileData = DB::table('cargo')->where('cargo.id', $id)->where('cargo.cargo_operation_type', '3')->first();
        $model = Cargo::find($id);
        $localFileData = DB::table('cargo')->select(['cargo.*', 'local_invoice_payment_detail.*'])->join('local_invoice_payment_detail', 'cargo.id', '=', 'local_invoice_payment_detail.local_invoice_id')->where('cargo.id', $id)->where('cargo.cargo_operation_type', '3')->get();
        $activityData = DB::table('activities')->where('related_id', $id)->where('type', 'cargo')->orderBy('id', 'DESC')->get();
        //pre($localFileData);
        $getInvoiceData = DB::table('invoices')->where('cargo_id', $id)->first();
        if(empty($getInvoiceData))
            $getInvoiceData = new Invoices();
        //pre($getInvoiceData);

        $dataExpense = DB::table('expenses')
            //->select(DB::raw('count(*) as user_count, voucher_number'))
            ->whereNotNull('cargo_id')
            ->where('deleted', '0')
            //->where('expense_request','Approved')
            ->where('cargo_id', $id)
            ->orderBy('expense_id', 'desc')
            ->get();
        $invoices = DB::table('invoices')
            ->select('invoices.*', 'currency.code as currencyCode')
            ->join('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.deleted', '0')
            ->where('invoices.cargo_id', $id)
            ->whereNull('invoices.housefile_module')
            ->orderBy('invoices.id', 'desc')->get();
        $totalInvoiceOfHTG = 0;
        $totalInvoiceOfUSD = 0;

        foreach ($invoices as $k => $v) {
            if ($v->currencyCode == 'USD')
                $totalInvoiceOfUSD += $v->total;

            if ($v->currencyCode == 'HTG')
                $totalInvoiceOfHTG += $v->total;
        }

        $path = 'Files/Cargo/Local/' . $localCargoFileData->file_number;


        $attachedFiles = DB::table('cargo_uploaded_files')->where('file_id', $id)->where('flag_module', 'cargo')->where('deleted', '0')->orderBy('id', 'DESC')->get()->toArray();
        /* $files = Storage::disk('s3')->files($path);

        if ($files) {
            $fileName = explode('/', $files[0]);
            $afc = count($attachedFiles);

            for ($i = 0; $i < count($files); $i++) {
                if (count($attachedFiles) != $i) {
                    $filesInfo[$i][0] = $attachedFiles[$i]->file_type;
                } else {
                    $filesInfo[$i][0] = '';
                }
                $tempArr = explode('/', $files[$i]);

                $filesInfo[$i][1] = $tempArr[(count($tempArr) - 1)];
            }
        } else {
            $filesInfo = [];
        } */
        $fileTypes = Config::get('app.fileTypes');

        $totalExpenseOfHtg = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $id)
            ->where('currency.code', 'HTG')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        $totalExpenseOfUSD = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->leftJoin('currency', 'currency.id', '=', 'vendors.currency')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $id)
            ->where('currency.code', 'USD')
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();

        /* Report by billing items */
        $getBillingAssociatedData = $getBillingItemData = DB::table('billing_items')
            //->select(DB::raw("CONCAT(billing_items.id,'-',costs.id) as fullcost"))
            ->select('billing_items.id as billingItemId', DB::raw('group_concat(costs.id) as costIds'))
            ->leftJoin('costs', 'costs.cost_billing_code', '=', 'billing_items.id')
            ->groupBy('billing_items.id')
            ->get();
        foreach ($getBillingAssociatedData as $k => $v) {
            $finalGetBillingAssociatedData[$getBillingAssociatedData[$k]->billingItemId] = $v;
        }

        $getBillingItemData = DB::table('invoices')
            ->select(['invoice_item_details.fees_name as biliingItemId', 'invoice_item_details.fees_name_desc as biliingItemDescription', 'invoice_item_details.total_of_items as biliingItemAmount', 'currency.code as currencyCode', 'currency.code as billingCurrencyCode'])
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            ->where('invoices.cargo_id', $id)
            ->where('invoices.deleted', '0')
            ->whereNull('housefile_module')
            ->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->where('expenses.cargo_id', $id)
            ->where('expenses.deleted', '0')
            ->get();


        /* Report by billing items */
        $finalReportData = array();
        foreach ($finalGetBillingAssociatedData as $k => $v) {
            foreach ($getBillingItemData as $k1 => $v1) {
                if ($k == $v1->biliingItemId) {
                    $finalReportData[$k]['billingData'][] = $v1;
                }
            }

            foreach ($getCostItemData as $k1 => $v1) {
                if (in_array($v1->costItemId, explode(',', $v->costIds))) {
                    $finalReportData[$k]['costData'][] = $v1;
                }
            }
        }

        foreach ($finalReportData as $k => $v) {
            $countBillingData = 0;
            $countCostData = 0;
            if (isset($v['billingData']))
                $countBillingData = count($v['billingData']);
            if (isset($v['costData']))
                $countCostData = count($v['costData']);
            $maxCount = max($countBillingData, $countCostData);
            if ($maxCount == $countBillingData)
                $vG = 'billingGreater';
            else
                $vG = 'costGreater';

            if ($vG == 'costGreater') {
                $v['allData'] = $v['costData'];
                foreach ($v['costData'] as $k1 => $v1) {
                    $v['allData'][$k1]->biliingItemId = isset($v['billingData'][$k1]->biliingItemId) ? $v['billingData'][$k1]->biliingItemId : '';
                    $v['allData'][$k1]->biliingItemDescription = isset($v['billingData'][$k1]->biliingItemDescription) ? $v['billingData'][$k1]->biliingItemDescription : '';
                    $v['allData'][$k1]->biliingItemAmount = isset($v['billingData'][$k1]->biliingItemAmount) ? $v['billingData'][$k1]->biliingItemAmount : '';
                    $v['allData'][$k1]->billingCurrencyCode = isset($v['billingData'][$k1]->billingCurrencyCode) ? $v['billingData'][$k1]->billingCurrencyCode : '';
                }
                $finalReportData[$k] = $v;
            } else {
                $v['allData'] = $v['billingData'];
                foreach ($v['billingData'] as $k1 => $v1) {
                    $v['allData'][$k1]->costItemId = isset($v['costData'][$k1]->costItemId) ? $v['costData'][$k1]->costItemId : '';
                    $v['allData'][$k1]->costDescription = isset($v['costData'][$k1]->costDescription) ? $v['costData'][$k1]->costDescription : '';
                    $v['allData'][$k1]->costAmount = isset($v['costData'][$k1]->costAmount) ? $v['costData'][$k1]->costAmount : '';
                    $v['allData'][$k1]->costCurrencyCode = isset($v['costData'][$k1]->costCurrencyCode) ? $v['costData'][$k1]->costCurrencyCode : '';
                }
                $finalReportData[$k] = $v;
            }
            unset($finalReportData[$k]['costData']);
            unset($finalReportData[$k]['billingData']);
        }

        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();
        //return view('cashier-role.cargo.rentallocalfiledetail', compact('localFileData', 'localCargoFileData', 'activityData', 'getInvoiceData', 'filesInfo', 'fileTypes', 'path'));
        //return view('cashier-role.cargo.rentallocalfiledetail', ['localFileData' => $localFileData, 'localCargoFileData' => $localCargoFileData, 'activityData' => $activityData, 'getInvoiceData' => $getInvoiceData]);
        return view('cashier-role.cargo.rentallocalfiledetail', ['model' => $model, 'localFileData' => $localFileData, 'localCargoFileData' => $localCargoFileData, 'activityData' => $activityData, 'getInvoiceData' => $getInvoiceData, 'activityData' => $activityData, 'id' => $id, 'dataExpense' => $dataExpense, 'invoices' => $invoices, 'filesInfo' => $attachedFiles, 'fileTypes' => $fileTypes, 'path' => $path, 'totalExpenseOfHtg' => $totalExpenseOfHtg->total, 'totalExpenseOfUSD' => $totalExpenseOfUSD->total, 'getBillingItemData' => $getBillingItemData, 'getCostItemData' => $getCostItemData, 'exchangeRateOfUsdToHTH' => $exchangeRateOfUsdToHTH->exchangeRate, 'totalInvoiceOfUSD' => $totalInvoiceOfUSD, 'totalInvoiceOfHTG' => $totalInvoiceOfHTG, 'finalReportData' => $finalReportData]);
    }

    public function changestatusoflocalsubfile(Request $request)
    {
        $id = $request->get('Id');
        $status = $request->get('status');
        //pre($status);
        if ($status == 'up') {
            DB::table('local_invoice_payment_detail')->where('id', $id)->update(['status' => 'p', 'updated_by' => auth()->user()->id, 'updated_at' => gmdate('Y-m-d H:i:s')]);
            $data = DB::table('local_invoice_payment_detail')->where('id', $id)->first();
            $cargoId = $data->local_invoice_id;

            $modelActivities = new Activities;
            $modelActivities->type = 'localInvoiceStatus';
            $modelActivities->related_id = $cargoId;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Status of invoice #' . $id . ' has been changed to Paid';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();
            $this->changeStatusOfMainFile($cargoId);

            echo 'Status has been updated successfully.';
        } else {
            DB::table('local_invoice_payment_detail')->where('id', $id)->update(['status' => 'up', 'updated_by' => auth()->user()->id, 'updated_at' => gmdate('Y-m-d H:i:s')]);
            $data = DB::table('local_invoice_payment_detail')->where('id', $id)->first();
            $cargoId = $data->local_invoice_id;

            $modelActivities = new Activities;
            $modelActivities->type = 'localInvoiceStatus';
            $modelActivities->related_id = $cargoId;
            $modelActivities->user_id   = auth()->user()->id;
            $modelActivities->description = 'Status of invoice #' . $id . ' has been changed to Pending';
            $modelActivities->updated_on = gmdate("Y-m-d H:i:s");
            $modelActivities->save();

            $this->changeStatusOfMainFile($cargoId);
        }
    }

    public function changeStatusOfMainFile($cargoId)
    {
        $data = DB::table('local_invoice_payment_detail')->where('local_invoice_id', $cargoId)->get();
        //pre($data);
        $cargoFileData = DB::table('cargo')->where('id', $cargoId)->get();
        $contractEndingDate = $cargoFileData[0]->rental_ending_date;
        $paidAmount = 0;
        $statusFlag = 0;
        foreach ($data as $localdata) {
            if ($localdata->status == 'p') {

                $datediff = strtotime($localdata->date) - strtotime($contractEndingDate);
                $diffSecond = round($datediff / (60 * 60 * 24));
                if ($diffSecond >= 0) {
                    $statusFlag = 1;
                    $cargoId = $localdata->local_invoice_id;
                } else {
                    $statusFlag = 0;
                    $cargoId = $localdata->local_invoice_id;
                }
            } else {
                $statusFlag = 0;
                $cargoId = $localdata->local_invoice_id;
                break;
            }
        }

        foreach ($data as $localnewdata) {
            if ($localnewdata->status == 'p') {
                $paidAmount += $localnewdata->total;
            }
        }
        //pre($paidAmount);
        if ($statusFlag == 1) {
            DB::table('cargo')->where('id', $cargoId)->update(['rental_paid_status' => 'p', 'updated_by' => auth()->user()->id, 'updated_on' => gmdate('Y-m-d H:i:s')]);
            DB::table('invoices')->where('cargo_id', $cargoId)->update(['payment_status' => 'Paid', 'updated_at' => gmdate('Y-m-d H:i:s'), 'credits' => $paidAmount]);
        } else {
            DB::table('cargo')->where('id', $cargoId)->update(['rental_paid_status' => 'up', 'updated_by' => auth()->user()->id, 'updated_on' => gmdate('Y-m-d H:i:s')]);
            DB::table('invoices')->where('cargo_id', $cargoId)->update(['payment_status' => 'Pending', 'updated_at' => gmdate('Y-m-d H:i:s'), 'credits' => $paidAmount]);
        }
    }
}
