<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDF;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'expense_id';
    public $timestamps = false;
    protected $fillable = [
        'status', 'courier_id', 'ups_details_id', 'cargo_id', 'created_on', 'created_by', 'voucher_number', 'expense_request', 'consignee', 'shipper', 'billing_party', 'bl_awb', 'cont_number', 'amount_billing_account', 'note', 'exp_date', 'currency', 'file_number', 'cash_credit_account', 'request_by', 'approved_by', 'disbursed_by', 'request_by_role', 'admin_managers', 'admin_manager_role', 'display_notification_admin', 'expense_request_status_note', 'display_notification_agent', 'display_notification_cashier', 'cashier_id', 'notification_date_time', 'updated_by', 'disbursed_datetime', 'display_notification_cashier_for_ups', 'house_file_id', 'display_notification_cashier_for_house_file_expense', 'aeropost_id', 'ccpack_id', 'display_notification_cashier_for_aeropost', 'display_notification_cashier_for_ccpack', 'ups_master_id', 'aeropost_master_id', 'display_notification_cashier_for_ups_master', 'display_notification_cashier_for_aeropost_master', 'ccpack_master_id', 'display_notification_cashier_for_ccpack_master', 'qb_sync', 'expense_type','vendor_bill_number','cost_shared', 'identification_flag', 'decsa_id'
    ];

    protected function getPendingExpenses($id)
    {
        $countPending = DB::table('expenses')->where('cargo_id', $id)->where('expense_request', 'Pending')->where('deleted', 0)->count();
        return $countPending;
    }

    protected function getPendingExpensesAll()
    {
        $countPendingAll = DB::table('expenses')->where('expense_request', 'Pending')->where('deleted', 0)->count();
        return $countPendingAll;
    }
    protected function Cargo()
    {
        return $this->hasOne(Cargo::class, 'id', 'cargo_id'); 
    }

    protected function getExpenseRequestsNoti($flag = "")
    {
        if ($flag != 'All') {
            $cargoPending = DB::table('expenses')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->select('cargo.id', 'cargo.file_number', 'cargo.cargo_operation_type', DB::raw('count(expenses.cargo_id) as total'))
                ->where('expenses.expense_request', 'Pending')
                ->where('expenses.deleted', 0)
                ->groupBy('expenses.cargo_id')
                ->get()
                ->toArray();

            $array = (array) $cargoPending;
            return $array;
        } else {
            $countPending = DB::table('expenses')->where('expense_request', 'Pending')->where('deleted', 0)->count();
            return $countPending;
        }
    }

    protected function getExpenseRequestsUserNoti($flag = "")
    {
        if ($flag != 'All') {
            $cargoPending = DB::table('expenses')
                ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->select('cargo.id', 'cargo.file_number', 'expenses.expense_request', 'expenses.expense_id', 'cargo.cargo_operation_type', DB::raw('count(expenses.cargo_id) as total'))
                ->where('expenses.display_notification', 1)
                ->where('expenses.deleted', 0)
                ->where('expense_details.paid_to', auth()->user()->id)
                ->where('expense_details.deleted', '0')
                ->groupBy('expenses.cargo_id')
                ->get()
                ->toArray();

            $array = (array) $cargoPending;
            return $array;
        } else {
            $countPending = DB::table('expenses')
                ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
                ->where('expenses.display_notification', 1)
                ->where('expense_details.paid_to', auth()->user()->id)
                ->where('expenses.deleted', 0)
                ->where('expense_details.deleted', '0')
                ->count();
            return $countPending;
        }
    }

    protected function getExpenseTotal($expenseId)
    {
        $countTotal = DB::table('expense_details')
            ->select(DB::raw('sum(amount) as total'))
            ->where('expense_id', $expenseId)
            ->where('deleted', '0')
            ->first();

        if (empty($countTotal->total)) {
            return '0.00';
        } else {
            return number_format($countTotal->total, 2);
        }
    }

    protected function getExpenseTotalOfSamePettyCash($cashCreditId)
    {
        $totalExpense = DB::table('expenses')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->where('expenses.cash_credit_account', $cashCreditId)
            ->where('expense_details.deleted', '0')
            ->first();
        return $totalExpense->total;
    }

    protected function getTotalExpenseOfCargo($cargoId)
    {
        $totalExpense = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $cargoId)
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        return $totalExpense->total;
    }

    protected function getTotalExpenseOfCargoInUSDORHTG($cargoId, $flagCurrency = '')
    {
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', $flagCurrency)
            ->first();

        $totalExpense = DB::table('expenses')
            ->join('cargo', 'expenses.cargo_id', '=', 'cargo.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            ->select('expense_details.amount as total', 'currency.code as currencyCode')
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.cargo_id', $cargoId)
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get();

        $totalInUsd = 0.00;
        foreach ($totalExpense as $k => $v) {
            if ($v->currencyCode == 'HTG') {
                $totalInUsd += ($v->total / $exchangeRateOfUsdToHTH->exchangeRate);
            } else {
                $totalInUsd += $v->total;
            }
        }
        return $totalInUsd;
    }

    protected function getTotalExpenseOfUps($upsId)
    {
        $totalExpense = DB::table('expenses')
            ->join('ups_details', 'expenses.ups_details_id', '=', 'ups_details.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.ups_details_id', $upsId)
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        return number_format($totalExpense->total, 2);
    }

    protected function getTotalExpenseOfHouseFile($houseId)
    {
        $totalExpense = DB::table('expenses')
            ->join('hawb_files', 'expenses.house_file_id', '=', 'hawb_files.id')
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            //->where('expenses.expense_request','Disbursement done')
            ->where('expenses.deleted', 0)
            ->where('expenses.house_file_id', $houseId)
            ->where('expense_details.deleted', '0')
            //->groupBy('expenses.cargo_id')
            ->get()
            ->first();
        return $totalExpense->total;
    }

    protected function getTotalRevenueOfCargo($cargoId)
    {
        $totalRevenue = DB::table('invoices')
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->select(DB::raw('sum(invoice_item_details.total_of_items) as total'))
            //->select(DB::raw('sum(invoices.total) as total'))
            ->where('invoices.deleted', 0)
            ->where('invoices.cargo_id', $cargoId)
            ->where(function ($query) {
                $query->where('hawb_hbl_no', '==', '')
                    ->orWhereNull('hawb_hbl_no');
            })
            ->get()
            ->first();
        /* $totalRevenue = DB::table('invoices')
        ->select(DB::raw('sum(invoices.credits) as total'))
        ->where('invoices.deleted',0)
        ->where('invoices.payment_status','Paid')
        ->where('invoices.cargo_id',$cargoId)
        ->get()
        ->first();  */
        return (!empty($totalRevenue->total) ? $totalRevenue->total : '0.00');
    }

    protected function getTotalRevenueOfHouseFile($houseId)
    {
        $totalRevenue = DB::table('invoices')
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->select(DB::raw('sum(invoice_item_details.total_of_items) as total'))
            //->select(DB::raw('sum(invoices.total) as total'))
            ->where('invoices.deleted', 0)
            ->where('invoices.hawb_hbl_no', $houseId)
            ->whereNotNull('hawb_hbl_no')
            ->where('hawb_hbl_no', '!=', '')
            ->get()
            ->first();
        /* $totalRevenue = DB::table('invoices')
        ->select(DB::raw('sum(invoices.credits) as total'))
        ->where('invoices.deleted',0)
        ->where('invoices.payment_status','Paid')
        ->where('invoices.cargo_id',$cargoId)
        ->get()
        ->first();  */
        return (!empty($totalRevenue->total) ? $totalRevenue->total : '0.00');
    }

    protected function getPrints($expenseId, $cargoId)
    {
        $cargoExpenseData = DB::table('expenses')->where('expense_id', $expenseId)->get();

        $dataCargo = DB::table('cargo')->where('id', $cargoId)->first();
        $pdf = PDF::loadView('expenses.printcargoexpense', ['cargoExpenseData' => $cargoExpenseData, 'dataCargo' => $dataCargo]);
        $pdf_file = $dataCargo->file_number . '_' . $expenseId . '_expense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
    }

    protected function getAllPrints()
    {
        $cargoExpenseData = DB::table('expenses')->where('deleted', '0')->where('expense_request', 'Approved')->orderBy('expense_id', 'desc')->get();

        $pdf = PDF::loadView('expenses.printallcargoexpense', ['cargoExpenseData' => $cargoExpenseData]);
        $pdf_file = 'printallexpense.pdf';
        $pdf_path = 'public/cargoExpensePdf/' . $pdf_file;
        $pdf->save($pdf_path);
    }

    protected function getInvoicesOfFile($cargoId, $cargo_operation_type = null)
    {
        if ($cargo_operation_type == 3) {
            $invoices = DB::table('invoices')
                ->where('cargo_id', $cargoId)
                ->where('deleted', 0)
                ->whereNotNull('cargo_id')
                ->whereNull('housefile_module')
                ->get()
                ->toArray();
        } else {
            $invoices = DB::table('invoices')
                ->where('cargo_id', $cargoId)
                ->where('deleted', 0)
                ->whereNotNull('cargo_id')
                ->whereNull('housefile_module')
                ->get()
                ->toArray();
        }

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getHouseFileInvoicesOfFile($houseId, $cargo_operation_type = null)
    {
        $invoices = DB::table('invoices')
            ->where('hawb_hbl_no', $houseId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getUpsInvoicesOfFile($upsId)
    {
        $invoices = DB::table('invoices')
            ->where('ups_id', $upsId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getUpsInvoicesOfFileInExpand($upsId, $flag = null, $action = null)
    {
        if ($flag == 'forExpandedFiles') {
            $UpsClientId = '';
            $dataClient = DB::table('clients')->where('company_name', 'UPS USD')->first();
            if (!empty($dataClient))
                $UpsClientId = $dataClient->id;

            $invoices = DB::table('invoices')
                ->join('currency', 'invoices.currency', '=', 'currency.id')
                ->where('ups_id', $upsId)
                ->where('invoices.deleted', 0)
                ->where('invoices.bill_to', '<>', $UpsClientId)
                ->get()
                ->toArray();
        } else {
            $invoices = DB::table('invoices')
                ->join('currency', 'invoices.currency', '=', 'currency.id')
                ->where('ups_id', $upsId)
                ->where('invoices.deleted', 0)
                ->get()
                ->toArray();
        }

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                if ($value->code == 'HTG')
                    $code = 'HTG';
                else
                    $code = 'USD';
                if ($action == 'actionExport') {
                    $invoicesN[] = '#' . $value->bill_no . ' (' . $code . ' ' . $value->total . ')';
                } else {
                    $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . ' (' . $code . ' ' . $value->total . ')' . '</b>' : '<b style="color:red">#' . $value->bill_no . ' (' . $code . ' ' . $value->total . ')' . '</b>';
                }
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getUpsMasterInvoicesOfFile($upsMasterId)
    {
        $invoices = DB::table('invoices')
            ->where('ups_master_id', $upsMasterId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getAeropostInvoicesOfFile($aeropostId)
    {
        $invoices = DB::table('invoices')
            ->where('aeropost_id', $aeropostId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getAeropostMasterInvoicesOfFile($aeropostMasterId)
    {
        $invoices = DB::table('invoices')
            ->where('aeropost_master_id', $aeropostMasterId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getCcpackMasterInvoicesOfFile($ccpackMasterId)
    {
        $invoices = DB::table('invoices')
            ->where('ccpack_master_id', $ccpackMasterId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    protected function getCcpackInvoicesOfFile($ccpackId)
    {
        $invoices = DB::table('invoices')
            ->where('ccpack_id', $ccpackId)
            ->where('deleted', 0)
            ->get()
            ->toArray();

        if (!empty($invoices)) {
            foreach ($invoices as $key => $value) {
                $invoicesN[] = $value->payment_status == 'Paid' ? '<b style="color:green">#' . $value->bill_no . '</b>' : '<b style="color:red">#' . $value->bill_no . '</b>';
            }
            return implode(', ', $invoicesN);
        } else {
            return "-";
        }
    }

    public static function getExpenseData($expenseId)
    {
        $dataExpense = DB::table('expenses')->where('expense_id', $expenseId)->first();
        return $dataExpense;
    }

    public static function getTotlaPendingExpensesForExport($vendorId, $fromDate = '', $toDate = '', $modules, $duration)
    {
        /* $totalNotApprovedExpenses = DB::table('expenses')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->where('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request','!=', 'Disbursement done');
        if (!empty($fromDate) && !empty($toDate)) {
            $totalNotApprovedExpenses = $totalNotApprovedExpenses->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if ($modules == 'Cargo') {
            $totalNotApprovedExpenses = $totalNotApprovedExpenses->whereNotNull('expenses.cargo_id');
        }
        $totalNotApprovedExpenses = $totalNotApprovedExpenses->first();
        return $totalNotApprovedExpenses->total; */

        $totalNotApprovedExpensesHtg = DB::table('expenses')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->whereIn('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->where('currency.code', 'HTG')
            ->where('expenses.deleted', '0')
            ->where('expenses.expense_type', 2);
        if (!empty($fromDate) && !empty($toDate)) {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($duration)) {
            if ($duration == '1')
                $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 15 DAY)'));
            else if ($duration == '2')
                $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'));
            else
                $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 31 DAY)'));
        }
        if ($modules == 'Cargo') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ccpack_master_id');
        }
        $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->first();
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        $totalInHtgToUsd = number_format($totalNotApprovedExpensesHtg->total / $exchangeRateOfUsdToHTH->exchangeRate, 2);

        $totalNotApprovedExpensesUSD = DB::table('expenses')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->whereIn('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->where('currency.code', 'USD')
            ->where('expenses.deleted', '0')
            ->where('expenses.expense_type', 2);
        if (!empty($fromDate) && !empty($toDate)) {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($duration)) {
            if ($duration == '1')
                $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 15 DAY)'));
            else if ($duration == '2')
                $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'));
            else
                $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 31 DAY)'));
        }
        if ($modules == 'Cargo') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')->whereNotNull('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')->whereNotNull('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')->whereNotNull('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')->whereNotNull('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')->whereNotNull('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')->whereNotNull('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')->whereNotNull('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')->whereNotNull('expenses.ccpack_master_id');
        }
        $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->first();

        return $totalNotApprovedExpensesUSD->total + $totalInHtgToUsd;
    }

    public static function getTotlaPendingExpenses($vendorId, $fromDate = '', $toDate = '', $modules, $duration)
    {
        /* $totalNotApprovedExpenses = DB::table('expenses')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->where('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request','!=', 'Disbursement done');
        if (!empty($fromDate) && !empty($toDate)) {
            $totalNotApprovedExpenses = $totalNotApprovedExpenses->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if ($modules == 'Cargo') {
            $totalNotApprovedExpenses = $totalNotApprovedExpenses->whereNotNull('expenses.cargo_id');
        }
        $totalNotApprovedExpenses = $totalNotApprovedExpenses->first();
        return $totalNotApprovedExpenses->total; */

        $totalNotApprovedExpensesHtg = DB::table('expenses')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->where('currency.code', 'HTG')
            ->where('expenses.deleted', '0')
            ->where('expenses.expense_type', 2);
        if (!empty($fromDate) && !empty($toDate)) {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($duration)) {
            if ($duration == '1')
                $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 15 DAY)'));
            else if ($duration == '2')
                $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'));
            else
                $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 31 DAY)'));
        }
        if ($modules == 'Cargo') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->whereNotNull('expenses.ccpack_master_id');
        }
        $totalNotApprovedExpensesHtg = $totalNotApprovedExpensesHtg->first();
        $exchangeRateOfUsdToHTH = DB::table('currency_exchange')
            ->select(['currency_exchange.exchange_value as exchangeRate'])
            ->join('currency', 'currency.id', '=', 'currency_exchange.from_currency')
            ->where('currency.code', 'USD')
            ->first();

        //$totalInHtgToUsd = number_format($totalNotApprovedExpensesHtg->total / $exchangeRateOfUsdToHTH->exchangeRate, 2);
        $totalInHtgToUsd = $totalNotApprovedExpensesHtg->total / $exchangeRateOfUsdToHTH->exchangeRate;

        $totalNotApprovedExpensesUSD = DB::table('expenses')
            ->select(DB::raw('sum(expense_details.amount) as total'))
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->join('vendors', 'vendors.id', '=', 'expense_details.paid_to')
            ->join('currency', 'currency.id', '=', 'vendors.currency')
            ->where('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->where('currency.code', 'USD')
            ->where('expenses.deleted', '0')
            ->where('expenses.expense_type', 2);
        if (!empty($fromDate) && !empty($toDate)) {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($duration)) {
            if ($duration == '1')
                $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 15 DAY)'));
            else if ($duration == '2')
                $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'));
            else
                $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 31 DAY)'));
        }
        if ($modules == 'Cargo') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')->whereNotNull('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')->whereNotNull('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')->whereNotNull('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')->whereNotNull('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')->whereNotNull('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')->whereNotNull('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')->whereNotNull('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')->whereNotNull('expenses.ccpack_master_id');
        }
        $totalNotApprovedExpensesUSD = $totalNotApprovedExpensesUSD->first();

        return $totalNotApprovedExpensesUSD->total + $totalInHtgToUsd;
    }

    public static function getFinalReportData($moduleId, $modules, $vendorId, $fromDate, $toDate, $duration)
    {
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
            ->select(['invoice_item_details.fees_name as biliingItemId', 'invoice_item_details.fees_name_desc as biliingItemDescription', 'invoice_item_details.total_of_items as biliingItemAmount', 'currency.code as currencyCode', 'currency.code as billingCurrencyCode', 'invoices.bill_no as invoiceNumber'])
            ->join('invoice_item_details', 'invoice_item_details.invoice_id', '=', 'invoices.id')
            ->leftJoin('currency', 'currency.id', '=', 'invoices.currency')
            //->where('invoices.cargo_id', $moduleId)
            ->where('invoices.deleted', '0');
        //->whereNull('housefile_module')
        //->get();
        if ($modules == 'Cargo') {
            $getBillingItemData = $getBillingItemData->whereNull('housefile_module')->where('invoices.cargo_id', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'House File') {
            $getBillingItemData = $getBillingItemData->where('housefile_module', 'cargo')->where('invoices.hawb_hbl_no', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'UPS') {
            $getBillingItemData = $getBillingItemData->where('invoices.ups_id', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'upsMaster') {
            $getBillingItemData = $getBillingItemData->where('invoices.ups_master_id', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'Aeropost') {
            $getBillingItemData = $getBillingItemData->where('invoices.aeropost_id', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'aeropostMaster') {
            $getBillingItemData = $getBillingItemData->where('invoices.aeropost_master_id', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'CCPack') {
            $getBillingItemData = $getBillingItemData->where('invoices.ccpack_id', $moduleId)->whereNull('flag_invoice');
        }
        if ($modules == 'ccpackMaster') {
            $getBillingItemData = $getBillingItemData->where('invoices.ccpack_master_id', $moduleId)->whereNull('flag_invoice');
        }
        $getBillingItemData = $getBillingItemData->get();

        $getCostItemData = DB::table('expenses')
            ->select(['expense_details.expense_type as costItemId', 'expense_details.description as costDescription', 'expense_details.amount as costAmount', 'currency.code as currencyCode', 'currency.code as costCurrencyCode', 'expenses.voucher_number as voucherNumber', 'expenses.expense_request as expenseStatus', 'expenses.expense_id as expenseId','expense_details.id as expenseDetailsId', 'expenses.vendor_bill_number as vendorBillNumber'])
            ->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->leftJoin('currency', 'currency.id', '=', 'expenses.currency')
            //->where('expenses.cargo_id', $moduleId)
            ->where('expense_details.paid_to', $vendorId)
            ->where('expenses.deleted', '0')
            ->where('expenses.expense_type', 2)
            ->where('expenses.expense_request', '!=', 'Disbursement done');
        //->get();

        if (!empty($fromDate) && !empty($toDate)) {
            $getCostItemData = $getCostItemData->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($duration)) {
            if ($duration == '1')
                $getCostItemData = $getCostItemData->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 15 DAY)'));
            else if ($duration == '2')
                $getCostItemData = $getCostItemData->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'));
            else
                $getCostItemData = $getCostItemData->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 31 DAY)'));
        }
        if ($modules == 'Cargo') {
            $getCostItemData = $getCostItemData->where('expenses.cargo_id', $moduleId);
        }
        if ($modules == 'House File') {
            $getCostItemData = $getCostItemData->where('expenses.house_file_id', $moduleId);
        }
        if ($modules == 'UPS') {
            $getCostItemData = $getCostItemData->where('expenses.ups_details_id', $moduleId);
        }
        if ($modules == 'upsMaster') {
            $getCostItemData = $getCostItemData->where('expenses.ups_master_id', $moduleId);
        }
        if ($modules == 'Aeropost') {
            $getCostItemData = $getCostItemData->where('expenses.aeropost_id', $moduleId);
        }
        if ($modules == 'aeropostMaster') {
            $getCostItemData = $getCostItemData->where('expenses.aeropost_master_id', $moduleId);
        }
        if ($modules == 'CCPack') {
            $getCostItemData = $getCostItemData->where('expenses.ccpack_id', $moduleId);
        }
        if ($modules == 'ccpackMaster') {
            $getCostItemData = $getCostItemData->where('expenses.ccpack_master_id', $moduleId);
        }
        $getCostItemData = $getCostItemData->get();

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
                    $v['allData'][$k1]->invoiceNumber = isset($v['billingData'][$k1]->invoiceNumber) ? $v['billingData'][$k1]->invoiceNumber : '';
                }
                $finalReportData[$k] = $v;
            } else {
                $v['allData'] = $v['billingData'];
                foreach ($v['billingData'] as $k1 => $v1) {
                    $v['allData'][$k1]->expenseDetailsId = isset($v['costData'][$k1]->expenseDetailsId) ? $v['costData'][$k1]->expenseDetailsId : '';
                    $v['allData'][$k1]->vendorBillNumber = isset($v['costData'][$k1]->vendorBillNumber) ? $v['costData'][$k1]->vendorBillNumber : '';
                    $v['allData'][$k1]->costItemId = isset($v['costData'][$k1]->costItemId) ? $v['costData'][$k1]->costItemId : '';
                    $v['allData'][$k1]->costDescription = isset($v['costData'][$k1]->costDescription) ? $v['costData'][$k1]->costDescription : '';
                    $v['allData'][$k1]->costAmount = isset($v['costData'][$k1]->costAmount) ? $v['costData'][$k1]->costAmount : '';
                    $v['allData'][$k1]->costCurrencyCode = isset($v['costData'][$k1]->costCurrencyCode) ? $v['costData'][$k1]->costCurrencyCode : '';
                    $v['allData'][$k1]->voucherNumber = isset($v['costData'][$k1]->voucherNumber) ? $v['costData'][$k1]->voucherNumber : '';
                    $v['allData'][$k1]->expenseStatus = isset($v['costData'][$k1]->expenseStatus) ? $v['costData'][$k1]->expenseStatus : '';
                    $v['allData'][$k1]->expenseId = isset($v['costData'][$k1]->expenseId) ? $v['costData'][$k1]->expenseId : '';
                }
                $finalReportData[$k] = $v;
            }
            unset($finalReportData[$k]['costData']);
            unset($finalReportData[$k]['billingData']);
        }

        return $finalReportData;
    }

    public static function getaccountpayablereportdata($vendorId, $fromDate = '', $toDate = '', $modules, $duration)
    {
        $fileOfExpensesUnpaid = DB::table('expenses')->where('expenses.expense_type', 2);
        if ($modules == 'Cargo') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.cargo_id as moduleId,cargo.file_number as fileNumber'))->join('cargo', 'cargo.id', '=', 'expenses.cargo_id')->groupBy('expenses.cargo_id');
        }
        if ($modules == 'House File') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.house_file_id as moduleId,hawb_files.file_number as fileNumber'))->join('hawb_files', 'hawb_files.id', '=', 'expenses.house_file_id')->groupBy('expenses.house_file_id');
        }
        if ($modules == 'UPS') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.ups_details_id as moduleId,ups_details.file_number as fileNumber'))->join('ups_details', 'ups_details.id', '=', 'expenses.ups_details_id')->groupBy('expenses.ups_details_id');
        }
        if ($modules == 'upsMaster') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.ups_master_id as moduleId,ups_master.file_number as fileNumber'))->join('ups_master', 'ups_master.id', '=', 'expenses.ups_master_id')->groupBy('expenses.ups_master_id');
        }
        if ($modules == 'Aeropost') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.aeropost_id as moduleId,aeropost.file_number as fileNumber'))->join('aeropost', 'aeropost.id', '=', 'expenses.aeropost_id')->groupBy('expenses.aeropost_id');
        }
        if ($modules == 'aeropostMaster') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.aeropost_master_id as moduleId,aeropost_master.file_number as fileNumber'))->join('aeropost_master', 'aeropost_master.id', '=', 'expenses.aeropost_master_id')->groupBy('expenses.aeropost_master_id');
        }
        if ($modules == 'CCPack') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.ccpack_id as moduleId,ccpack.file_number as fileNumber'))->join('ccpack', 'ccpack.id', '=', 'expenses.ccpack_id')->groupBy('expenses.ccpack_id');
        }
        if ($modules == 'ccpackMaster') {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->select(DB::raw('expenses.ccpack_master_id as moduleId,ccpack_master.file_number as fileNumber'))->join('ccpack_master', 'ccpack_master.id', '=', 'expenses.ccpack_master_id')->groupBy('expenses.ccpack_master_id');
        }
        if (!empty($fromDate) && !empty($toDate)) {
            $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->whereBetween('expenses.exp_date', array($fromDate, $toDate));
        }
        if (!empty($duration)) {
            if ($duration == '1')
                $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 15 DAY)'));
            else if ($duration == '2')
                $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'));
            else
                $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->where('expenses.exp_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 31 DAY)'));
        }

        $fileOfExpensesUnpaid = $fileOfExpensesUnpaid->join('expense_details', 'expense_details.expense_id', '=', 'expenses.expense_id')
            ->where('expense_details.paid_to', $vendorId)
            ->where('expenses.expense_request', '!=', 'Disbursement done')
            ->where('expenses.deleted', '0')
            ->get();

        return $fileOfExpensesUnpaid;
    }

    public static function getNumberOfOldDays($expenseId)
    {
        $expenseData =  DB::table('expenses')->where('expense_id', $expenseId)->first();
        $date1=date_create($expenseData->exp_date);
        $date2=date_create(date('Y-m-d'));
        $diff = date_diff($date1,$date2);
        return $diff->format("%a Days");
    }
}
