<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;

class QuickBookController extends Controller
{
	public $error;
	public function index()
	{
		$model = DB::table('quickbook_error_logs')->OrderBy('id', 'DESC')->get();
		//pre($model);
		foreach ($model as $md) {
			if ($md->module == '0') {
				$data = DB::table('costs')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->cost_name, 'cost', $md->id);
				}
			} else if ($md->module == '1') {
				$data = DB::table('vendors')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->company_name, 'vendor', $md->id);
				}
			} else if ($md->module == '2') {
				$data = DB::table('cashcredit')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->name, 'account', $md->id);
				}
			} else if ($md->module == '3') {
				$data = DB::table('billing_items')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->billing_name, 'billing', $md->id);
				}
			} else if ($md->module == '4') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'ups_expense', $md->id);
				}
			} else if ($md->module == '5') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'cargo_expense', $md->id);
				}
			} else if ($md->module == '6') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'cargo_invoice', $md->id);
				}
			} else if ($md->module == '7') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'ups_invoice', $md->id);
				}
			} else if ($md->module == '8') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'ccpack_invoices', $md->id);
				}
			} else if ($md->module == '9') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'aeropost_invoices', $md->id);
				}
			} else if ($md->module == '11') {
				$data = DB::table('clients')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->company_name, 'client', $md->id);
				}
			} else if ($md->module == '12') {
				$data = DB::table('currency')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->code, 'currency', $md->id);
				}
			} else if ($md->module == '13') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'house_invoices', $md->id);
				}
			} else if ($md->module == '14') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'areopost_expense', $md->id);
				}
			} else if ($md->module == '15') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'ccpack_expense', $md->id);
				}
			} else if ($md->module == '16') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'house_expense', $md->id);
				}
			} else if ($md->module == '17') {
				$data = DB::table('cashcredit_detail_type')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->name, 'other_accounts', $md->id);
				}
			} else if ($md->module == '18') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'ups_master_invoices', $md->id);
				}
			} else if ($md->module == '19') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'aeropost_master_invoices', $md->id);
				}
			} else if ($md->module == '20') {
				$data = DB::table('invoices')->where('id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->bill_no, 'ccpack_master_invoices', $md->id);
				}
			} else if ($md->module == '21') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'ups_master_expense', $md->id);
				}
			} else if ($md->module == '22') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'aeropost_master_expense', $md->id);
				}
			} else if ($md->module == '23') {
				$data = DB::table('expenses')->where('expense_id', $md->module_id)->first();
				if (!empty($data)) {
					$this->setParam(Config::get('app.quickbook_modules')[$md->module], $md->operation, $md->error_message, $data->voucher_number, 'ccpack_master_expense', $md->id);
				}
			}
		}


		return view('quickbookErrors.index', ['error' => $this->error]);

		/*foreach ($error as $key => $value) {
    		foreach ($value as $k => $v) {
    			pre($v['module']);
    			
    		}
    	}*/


		//pre($this->error);
	}

	public function setParam($module, $opId, $error_msg, $unique, $prefix,$id)
	{
		if ($opId == 0) {
			$this->error[$id]['operation'] = 'Store';
		} else if ($opId == 1) {
			$this->error[$id]['operation'] = 'Update';
		} else {
			$this->error[$id]['operation'] = 'Delete';
		}

		$this->error[$id]['module'] = $module;
		$this->error[$id]['unique_id'] = $unique;
		$this->error[$id]['error_message'] = $error_msg;
	}
}