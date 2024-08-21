<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CustomExpenses extends Model
{
    protected $table = 'custom_expenses';
    public $timestamps = false;
    protected $fillable = [
        'ups_details_id', 'cargo_id', 'custom_id','ups_file_number','custom_file_number','exp_date','status','created_on','created_by','updated_on','updated_by','deleted','deleted_on','deleted_by','voucher_number'
    ];

     protected function getExpenseTotal($expenseId)
    {
        $countTotal = DB::table('custom_expense_details')
        ->select(DB::raw('sum(amount) as total'))
        ->where('expense_id',$expenseId)
        ->where('deleted','0')
        ->first();
        
        if(empty($countTotal->total))
            return '0.00';
        else
            return number_format($countTotal->total,2);
    }

    protected function getCustomExpenses($updId)
    {
        $dataInvoices = DB::table('custom_expense_details')
                        ->select(DB::raw('SUM(custom_expense_details.amount) as total_expense'))
                        ->join('custom_expenses', 'custom_expenses.id', '=', 'custom_expense_details.expense_id')
                        ->where('custom_expenses.ups_details_id',$updId)      
                        ->first();
        return $dataInvoices->total_expense;

    }

    protected function getCustomInvoices($updId)
    {
        $dataInvoicesCount = DB::table('invoice_item_details')
                        ->select(DB::raw('SUM(invoice_item_details.total_of_items) as total_invoice,invoices.date,invoices.payment_status,invoices.id'))
                        ->join('invoices', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                        ->where('invoice_item_details.fees_name','26')      
                        ->where('invoices.ups_id',$updId)      
                        ->count();
        if($dataInvoicesCount > 0)
        {
        $dataInvoices = DB::table('invoice_item_details')
                        ->select(DB::raw('SUM(invoice_item_details.total_of_items) as total_invoice,invoices.date,invoices.payment_status,invoices.id'))
                        ->join('invoices', 'invoice_item_details.invoice_id', '=', 'invoices.id')
                        ->where('invoice_item_details.fees_name','26')      
                        ->where('invoices.ups_id',$updId)      
                        ->first();
        }else
        {
            $dataInvoices = array();
        }
        return $dataInvoices;
    }

    protected function getCustomData($updId)
    {
        $dataCustom = DB::table('customs')
                        ->select(DB::raw('file_number'))
                        ->where('ups_details_id',$updId)      
                        ->first();
        return $dataCustom;
    }

    
}
