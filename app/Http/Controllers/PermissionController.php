<?php

namespace App\Http\Controllers;
use App\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Auth;
use Session;
use App\User;
use App\Activities;
class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$permissionFlag)
    {
       /* $checkPermission = User::checkPermission(['create_permissions'],'',auth()->user()->id);
            if(!$checkPermission)
                return redirect('/home');*/

        $modelPermission = new Permissions;
        $departments = DB::table('cashcredit_detail_type')
                ->select(['cashcredit_detail_type.name','cashcredit_detail_type.id'])
                ->join('cashcredit_account_type', 'cashcredit_account_type.id', '=', 'cashcredit_detail_type.account_type_id')
                ->where('cashcredit_account_type.name','User')
                ->pluck('name', 'id');
        
        return view("permissions.index",['permissionFlag'=>$permissionFlag,'departments'=>$departments]);
    }

    public function filteroutside() {
        $model = new Permissions();
        $permission = array();

        //-------------------------- Global Settings ---------------------------------//
        $master['billing_items'] = 'Billing Items';
        $permission['billing_items']['listing_billing_items'] = 'Lisitng';
        $permission['billing_items']['add_billing_items'] = 'Add';
        $permission['billing_items']['update_billing_items'] = 'Update';
        $permission['billing_items']['delete_billing_items'] = 'Delete';

        $master['costs'] = 'Costs';
        $permission['costs']['listing_costs'] = 'Lisitng';
        $permission['costs']['add_costs'] = 'Add';
        $permission['costs']['update_costs'] = 'Update';
        $permission['costs']['delete_costs'] = 'Delete';

        $master['users'] = 'Users';
        $permission['users']['listing_users'] = 'Listing';
        $permission['users']['add_users'] = 'Add';
        $permission['users']['update_users'] = 'Update';
        $permission['users']['delete_users'] = 'Delete';
        $permission['users']['reset_password_users'] = 'Reset Password';

        $master['permissions'] = 'Permissions';
        $permission['permissions']['create_permissions'] = 'Create';

        $master['account_types'] = 'Account Types';
        $permission['account_types']['listing_account_types'] = 'Listing';
        $permission['account_types']['add_account_types'] = 'Add';
        $permission['account_types']['update_account_types'] = 'Update';
        $permission['account_types']['delete_account_types'] = 'Delete';

        $master['account_sub_types'] = 'Account Sub Types';
        $permission['account_sub_types']['listing_account_sub_types'] = 'Listing';
        $permission['account_sub_types']['add_account_sub_types'] = 'Add';
        $permission['account_sub_types']['update_account_sub_types'] = 'Update';
        $permission['account_sub_types']['delete_account_sub_types'] = 'Delete';

        $master['payment_terms'] = 'Payment Terms';
        $permission['payment_terms']['listing_payment_terms'] = 'Listing';
        $permission['payment_terms']['add_payment_terms'] = 'Add';
        $permission['payment_terms']['update_payment_terms'] = 'Update';
        $permission['payment_terms']['delete_payment_terms'] = 'Delete';

        $master['countries'] = 'Countries';
        $permission['countries']['listing_countries'] = 'Listing';
        $permission['countries']['add_countries'] = 'Add';
        $permission['countries']['update_countries'] = 'Update';
        $permission['countries']['delete_countries'] = 'Delete';


        $master['currencies'] = 'Currencies';
        $permission['currencies']['listing_currencies'] = 'Listing';
        $permission['currencies']['add_currencies'] = 'Add';
        $permission['currencies']['update_currencies'] = 'Update';
        $permission['currencies']['delete_currencies'] = 'Delete';

        $master['other_expense_items'] = 'Other Expense Items';
        $permission['other_expense_items']['listing_other_expense_items'] = 'Listing';
        $permission['other_expense_items']['add_other_expense_items'] = 'Add';
        $permission['other_expense_items']['update_other_expense_items'] = 'Update';
        $permission['other_expense_items']['delete_other_expense_items'] = 'Delete';

        $master['warehouses'] = 'Warehouses';
        $permission['warehouses']['listing_warehouses'] = 'Lisitng';
        $permission['warehouses']['add_warehouses'] = 'Add';
        $permission['warehouses']['update_warehouses'] = 'Update';
        $permission['warehouses']['delete_warehouses'] = 'Delete';

        $master['storage_charges'] = 'Storage Charges';
        $permission['storage_charges']['listing_storage_charges'] = 'Listing';
        $permission['storage_charges']['add_storage_charges'] = 'Add';
        $permission['storage_charges']['update_storage_charges'] = 'Update';
        $permission['storage_charges']['delete_storage_charges'] = 'Delete';

        $master['storage_racks'] = 'Storage Racks';
        $permission['storage_racks']['listing_storage_racks'] = 'Listing';
        $permission['storage_racks']['add_storage_racks'] = 'Add';
        $permission['storage_racks']['update_storage_racks'] = 'Update';
        $permission['storage_racks']['delete_storage_racks'] = 'Delete';

        $master['ups_commission'] = 'UPS Commission';
        $permission['ups_commission']['listing_ups_commission'] = 'Listing';
        $permission['ups_commission']['add_ups_commission'] = 'Add';
        $permission['ups_commission']['update_ups_commission'] = 'Update';
        $permission['ups_commission']['delete_ups_commission'] = 'Delete';

        $master['fd_charges'] = 'F/D Charges';
        $permission['fd_charges']['listing_fd_charges'] = 'Listing';
        $permission['fd_charges']['add_fd_charges'] = 'Add';
        $permission['fd_charges']['update_fd_charges'] = 'Update';
        $permission['fd_charges']['delete_fd_charges'] = 'Delete';

        $master['file_progress_status'] = 'File Progress Status';
        $permission['file_progress_status']['listing_file_progress_status'] = 'Listing';
        $permission['file_progress_status']['add_file_progress_status'] = 'Add';
        $permission['file_progress_status']['update_file_progress_status'] = 'Update';
        $permission['file_progress_status']['delete_file_progress_status'] = 'Delete';

        $master['delivery_boy'] = 'Delivery Boy';
        $permission['delivery_boy']['listing_delivery_boy'] = 'Listing';
        $permission['delivery_boy']['add_delivery_boy'] = 'Add';
        $permission['delivery_boy']['update_delivery_boy'] = 'Update';
        $permission['delivery_boy']['delete_delivery_boy'] = 'Delete';

        $master['close_file'] = 'Close File';
        $permission['close_file']['close_file'] = 'Close File';
        $permission['close_file']['listing_close_file'] = 'Listing';

        $master['manifestes'] = 'Manifests';
        $permission['manifestes']['listing_manifestes'] = 'Listing';
        $permission['manifestes']['import_manifestes'] = 'Import';
        

        //-------------------------- Cargo Related ---------------------------------//
        $master['cargo'] = 'Cargo Files';
        $permission['cargo']['listing_cargo'] = 'Lisitng';
        $permission['cargo']['add_cargo'] = 'Add';
        $permission['cargo']['update_cargo'] = 'Update';
        $permission['cargo']['delete_cargo'] = 'Delete';
        $permission['cargo']['assign_billingparty_cashcredit_warehouse_cargo'] = 'Assign Billing Party/Cash-Credit/Warehouse';

        $master['cargo_expenses'] = 'Cargo Expenses';
        $permission['cargo_expenses']['listing_cargo_expenses'] = 'Listing';
        $permission['cargo_expenses']['add_cargo_expenses'] = 'Add';
        $permission['cargo_expenses']['update_cargo_expenses'] = 'Update';
        $permission['cargo_expenses']['delete_cargo_expenses'] = 'Delete';
        $permission['cargo_expenses']['viewdetails_cargo_expenses'] = 'View Details';
        $permission['cargo_expenses']['change_file_expense_status_cargo'] = 'Disbursement';

        $master['cargo_invoices'] = 'Cargo Invoices';
        $permission['cargo_invoices']['listing_cargo_invoices'] = 'Listing';
        $permission['cargo_invoices']['add_cargo_invoices'] = 'Add';
        $permission['cargo_invoices']['update_cargo_invoices'] = 'Update';
        $permission['cargo_invoices']['delete_cargo_invoices'] = 'Delete';
        $permission['cargo_invoices']['copy_cargo_invoices'] = 'Copy Invoice';
        $permission['cargo_invoices']['view_details_cargo_invoices'] = 'View Detail';
        $permission['cargo_invoices']['import_cargo_invoices'] = 'Import Invoices';
        
        $master['cargo_invoice_payments'] = 'Cargo Invoice Payments';
        $permission['cargo_invoice_payments']['add_cargo_invoice_payments'] = 'Add';
        $permission['cargo_invoice_payments']['delete_cargo_invoice_payments'] = 'Delete';
        $permission['cargo_invoice_payments']['modify_cargo_invoice_payments'] = 'Modify';

        $master['guarantee_check'] = 'Guarantee Check';
        $permission['guarantee_check']['listing_guarantee_check'] = 'Listing';
        $permission['guarantee_check']['add_guarantee_check'] = 'Add';
        $permission['guarantee_check']['update_guarantee_check'] = 'Update';
        $permission['guarantee_check']['delete_guarantee_check'] = 'Delete';
        $permission['guarantee_check']['approve_guarantee_check'] = 'Approve';

        //-------------------------- House File Related ---------------------------------//
        $master['cargo_hawb'] = 'Cargo House Files';
        $permission['cargo_hawb']['listing_cargo_hawb'] = 'Lisitng';
        $permission['cargo_hawb']['add_cargo_hawb'] = 'Add';
        $permission['cargo_hawb']['update_cargo_hawb'] = 'Update';
        $permission['cargo_hawb']['delete_cargo_hawb'] = 'Delete';
        $permission['cargo_hawb']['assign_billingparty_cashcredit_warehouse_hawb'] = 'Assign Billing Party/Cash-Credit/Warehouse';

        $master['cargo_house_expenses'] = 'Cargo House Expenses';
        $permission['cargo_house_expenses']['listing_cargo_house_expenses'] = 'Listing';
        $permission['cargo_house_expenses']['add_cargo_house_expenses'] = 'Add';
        $permission['cargo_house_expenses']['update_cargo_house_expenses'] = 'Update';
        $permission['cargo_house_expenses']['delete_cargo_house_expenses'] = 'Delete';
        $permission['cargo_house_expenses']['viewdetails_cargo_house_expenses'] = 'View Details';
        $permission['cargo_house_expenses']['change_file_expense_status_cargo'] = 'Disbursement';

        $master['cargo_house_invoices'] = 'Cargo House Invoices';
        $permission['cargo_house_invoices']['listing_cargo_house_invoices'] = 'Listing';
        $permission['cargo_house_invoices']['add_cargo_house_invoices'] = 'Add';
        $permission['cargo_house_invoices']['update_cargo_house_invoices'] = 'Update';
        $permission['cargo_house_invoices']['delete_cargo_house_invoices'] = 'Delete';
        $permission['cargo_house_invoices']['copy_cargo_house_invoices'] = 'Copy Invoice';
        $permission['cargo_house_invoices']['view_details_cargo_house_invoices'] = 'View Detail';
        $permission['cargo_house_invoices']['import_cargo_house_invoices'] = 'Import Invoices';


        //-------------------------- Courier - UPS Related ---------------------------------//
        $master['ups_master'] = 'UPS Master Files';
        $permission['ups_master']['listing_ups_master'] = 'Listing';
        $permission['ups_master']['add_ups_master'] = 'Create';
        $permission['ups_master']['update_ups_master'] = 'Update';
        $permission['ups_master']['delete_ups_master'] = 'Delete';
        $permission['ups_master']['assign_billingparty_cashcredit_ups_master'] = 'Assign Billing Party/Cash-Credit';
        $permission['ups_master']['viewdetails_ups_master'] = 'View Details';
        $permission['ups_master']['export_house_files_ups_master'] = 'Export House Files';

        $master['ups_master_expenses'] = 'UPS Master Expenses';
        $permission['ups_master_expenses']['listing_ups_master_expenses'] = 'Listing';
        $permission['ups_master_expenses']['add_ups_master_expenses'] = 'Add';
        $permission['ups_master_expenses']['update_ups_master_expenses'] = 'Update';
        $permission['ups_master_expenses']['delete_ups_master_expenses'] = 'Delete';
        $permission['ups_master_expenses']['viewdetails_ups_master_expenses'] = 'View Details';
        $permission['ups_master_expenses']['change_file_expense_status_ups_master'] = 'Disbursement';

        $master['ups_master_invoices'] = 'UPS Master Invoices';
        $permission['ups_master_invoices']['listing_ups_master_invoices'] = 'Listing';
        $permission['ups_master_invoices']['add_ups_master_invoices'] = 'Add';
        $permission['ups_master_invoices']['update_ups_master_invoices'] = 'Update';
        $permission['ups_master_invoices']['delete_ups_master_invoices'] = 'Delete';
        $permission['ups_master_invoices']['copy_ups_master_invoices'] = 'Copy Invoice';
        $permission['ups_master_invoices']['viewdetails_ups_master_invoices'] = 'View Detail';

        /* $master['ups_master_invoice_payments'] = 'UPS Master Invoice Payments';
        $permission['ups_master_invoice_payments']['add_ups_master_invoice_payments'] = 'Add';
        $permission['ups_master_invoice_payments']['delete_ups_master_invoice_payments'] = 'Delete';
        $permission['ups_master_invoice_payments']['modify_ups_master_invoice_payments'] = 'Modify'; */

        $master['courier_import'] = 'UPS Files';
        $permission['courier_import']['listing_courier_import'] = 'Listing';
        $permission['courier_import']['add_courier_import'] = 'Create';
        $permission['courier_import']['update_courier_import'] = 'Update';
        $permission['courier_import']['delete_courier_import'] = 'Delete';
        $permission['courier_import']['upload_courier_import'] = 'Import/Upload';
        $permission['courier_import']['assign_billingparty_cashcredit_courier_import'] = 'Assign Billing Party/Cash-Credit';
        $permission['courier_import']['viewdetails_courier_import'] = 'View Details';

        $master['courier_expenses'] = 'UPS Expenses';
        $permission['courier_expenses']['listing_courier_expenses'] = 'Listing';
        $permission['courier_expenses']['add_courier_expenses'] = 'Add';
        $permission['courier_expenses']['update_courier_expenses'] = 'Update';
        $permission['courier_expenses']['delete_courier_expenses'] = 'Delete';
        $permission['courier_expenses']['viewdetails_ups_courier_expenses'] = 'View Details';
        $permission['courier_expenses']['change_file_expense_status_courier_import'] = 'Disbursement';

        $master['courier_invoices'] = 'UPS Invoices';
        $permission['courier_invoices']['listing_courier_invoices'] = 'Listing';
        $permission['courier_invoices']['add_courier_invoices'] = 'Add';
        $permission['courier_invoices']['update_courier_invoices'] = 'Update';
        $permission['courier_invoices']['delete_courier_invoices'] = 'Delete';
        $permission['courier_invoices']['copy_courier_invoices'] = 'Copy Invoice';
        $permission['courier_invoices']['view_details_ups_invoices'] = 'View Detail';

        $master['courier_invoice_payments'] = 'UPS Invoice Payments';
        $permission['courier_invoice_payments']['add_courier_invoice_payments'] = 'Add';
        $permission['courier_invoice_payments']['delete_courier_invoice_payments'] = 'Delete';
        $permission['courier_invoice_payments']['modify_courier_invoice_payments'] = 'Modify';


        $master['courier_custom_expenses'] = 'UPS Custom Expenses';
        $permission['courier_custom_expenses']['listing_courier_custom_expenses'] = 'Listing';
        $permission['courier_custom_expenses']['add_courier_custom_expenses'] = 'Add';
        $permission['courier_custom_expenses']['update_courier_custom_expenses'] = 'Update';
        $permission['courier_custom_expenses']['delete_courier_custom_expenses'] = 'Delete';


        //-------------------------- Courier - Aeropost Related ---------------------------------//
        $master['aeropost_master'] = 'Aeropost Master Files';
        $permission['aeropost_master']['listing_aeropost_master'] = 'Listing';
        $permission['aeropost_master']['add_aeropost_master'] = 'Create';
        $permission['aeropost_master']['update_aeropost_master'] = 'Update';
        $permission['aeropost_master']['delete_aeropost_master'] = 'Delete';
        $permission['aeropost_master']['assign_billingparty_cashcredit_aeropost_master'] = 'Assign Billing Party/Cash-Credit';
        $permission['aeropost_master']['viewdetails_aeropost_master'] = 'View Details';

        $master['aeropost_master_expenses'] = 'Aeropost Master Expenses';
        $permission['aeropost_master_expenses']['listing_aeropost_master_expenses'] = 'Listing';
        $permission['aeropost_master_expenses']['add_aeropost_master_expenses'] = 'Add';
        $permission['aeropost_master_expenses']['update_aeropost_master_expenses'] = 'Update';
        $permission['aeropost_master_expenses']['delete_aeropost_master_expenses'] = 'Delete';
        $permission['aeropost_master_expenses']['viewdetails_aeropost_master_expenses'] = 'View Details';
        $permission['aeropost_master_expenses']['change_file_expense_status_aeropost_master'] = 'Disbursement';

        $master['aeropost_master_invoices'] = 'Aeropost Master Invoices';
        $permission['aeropost_master_invoices']['listing_aeropost_master_invoices'] = 'Listing';
        $permission['aeropost_master_invoices']['add_aeropost_master_invoices'] = 'Add';
        $permission['aeropost_master_invoices']['update_aeropost_master_invoices'] = 'Update';
        $permission['aeropost_master_invoices']['delete_aeropost_master_invoices'] = 'Delete';
        $permission['aeropost_master_invoices']['copy_aeropost_master_invoices'] = 'Copy Invoice';
        $permission['aeropost_master_invoices']['viewdetails_aeropost_master_invoices'] = 'View Detail';

        /* $master['aeropost_master_invoice_payments'] = 'Aeropost Master Invoice Payments';
        $permission['aeropost_master_invoice_payments']['add_aeropost_master_invoice_payments'] = 'Add';
        $permission['aeropost_master_invoice_payments']['delete_aeropost_master_invoice_payments'] = 'Delete';
        $permission['aeropost_master_invoice_payments']['modify_aeropost_master_invoice_payments'] = 'Modify'; */

        $master['aeropost'] = 'Aeropost Files';
        $permission['aeropost']['listing_aeropost'] = 'Listing';
        $permission['aeropost']['add_aeropost'] = 'Add';
        $permission['aeropost']['update_aeropost'] = 'Update';
        $permission['aeropost']['delete_aeropost'] = 'Delete';
        $permission['aeropost']['upload_aeropost'] = 'Upload';
        $permission['aeropost']['assign_billingparty_cashcredit_aeropost'] = 'Assign Billing Party';
        $permission['aeropost']['viewdetails_courier_aeropost'] = 'View Details';

        $master['aeropost_expenses'] = 'Aeropost Expenses';
        $permission['aeropost_expenses']['listing_aeropost_expenses'] = 'Listing';
        $permission['aeropost_expenses']['add_aeropost_expenses'] = 'Add';
        $permission['aeropost_expenses']['update_aeropost_expenses'] = 'Update';
        $permission['aeropost_expenses']['delete_aeropost_expenses'] = 'Delete';
        $permission['aeropost_expenses']['change_file_expense_status_aeropost'] = 'Disbursement';

        $master['aeropost_invoices'] = 'Aeropost Invoices';
        $permission['aeropost_invoices']['listing_aeropost_invoices'] = 'Listing';
        $permission['aeropost_invoices']['add_aeropost_invoices'] = 'Add';
        $permission['aeropost_invoices']['update_aeropost_invoices'] = 'Update';
        $permission['aeropost_invoices']['delete_aeropost_invoices'] = 'Delete';
        $permission['aeropost_invoices']['copy_aeropost_invoices'] = 'Copy Invoice';
        $permission['aeropost_invoices']['view_details_aeropost_invoices'] = 'View Detail';

        $master['aeropost_invoice_payments'] = 'Aeropost Invoice Payments';
        $permission['aeropost_invoice_payments']['add_aeropost_invoice_payments'] = 'Add';
        $permission['aeropost_invoice_payments']['delete_aeropost_invoice_payments'] = 'Delete';
        $permission['aeropost_invoice_payments']['modify_aeropost_invoice_payments'] = 'Modify';


        //-------------------------- Courier - CCPack Related ---------------------------------//
        $master['ccpack_master'] = 'CCPack Master Files';
        $permission['ccpack_master']['listing_ccpack_master'] = 'Listing';
        $permission['ccpack_master']['add_ccpack_master'] = 'Create';
        $permission['ccpack_master']['update_ccpack_master'] = 'Update';
        $permission['ccpack_master']['delete_ccpack_master'] = 'Delete';
        $permission['ccpack_master']['assign_billingparty_cashcredit_ccpack_master'] = 'Assign Billing Party/Cash-Credit';
        $permission['ccpack_master']['viewdetails_ccpack_master'] = 'View Details';

        $master['ccpack_master_expenses'] = 'CCPack Master Expenses';
        $permission['ccpack_master_expenses']['listing_ccpack_master_expenses'] = 'Listing';
        $permission['ccpack_master_expenses']['add_ccpack_master_expenses'] = 'Add';
        $permission['ccpack_master_expenses']['update_ccpack_master_expenses'] = 'Update';
        $permission['ccpack_master_expenses']['delete_ccpack_master_expenses'] = 'Delete';
        $permission['ccpack_master_expenses']['viewdetails_ccpack_master_expenses'] = 'View Details';
        $permission['ccpack_master_expenses']['change_file_expense_status_ccpack_master'] = 'Disbursement';

        $master['ccpack_master_invoices'] = 'CCPack Master Invoices';
        $permission['ccpack_master_invoices']['listing_ccpack_master_invoices'] = 'Listing';
        $permission['ccpack_master_invoices']['add_ccpack_master_invoices'] = 'Add';
        $permission['ccpack_master_invoices']['update_ccpack_master_invoices'] = 'Update';
        $permission['ccpack_master_invoices']['delete_ccpack_master_invoices'] = 'Delete';
        $permission['ccpack_master_invoices']['copy_ccpack_master_invoices'] = 'Copy Invoice';
        $permission['ccpack_master_invoices']['viewdetails_ccpack_master_invoices'] = 'View Detail';

        /* $master['ccpack_master_invoice_payments'] = 'CCPack Master Invoice Payments';
        $permission['ccpack_master_invoice_payments']['add_ccpack_master_invoice_payments'] = 'Add';
        $permission['ccpack_master_invoice_payments']['delete_ccpack_master_invoice_payments'] = 'Delete';
        $permission['ccpack_master_invoice_payments']['modify_ccpack_master_invoice_payments'] = 'Modify'; */

        $master['ccpack'] = 'Ccpack Files';
        $permission['ccpack']['listing_ccpack'] = 'Listing';
        $permission['ccpack']['add_ccpack'] = 'Add';
        $permission['ccpack']['update_ccpack'] = 'Update';
        $permission['ccpack']['delete_ccpack'] = 'Delete';
        $permission['ccpack']['assign_billingparty_cashcredit_ccpack'] = 'Assign Billing Party';
        $permission['ccpack']['viewdetails_courier_ccpack'] = 'View Details';
        
        $master['ccpack_expenses'] = 'Ccpack Expenses';
        $permission['ccpack_expenses']['listing_ccpack_expenses'] = 'Listing';
        $permission['ccpack_expenses']['add_ccpack_expenses'] = 'Add';
        $permission['ccpack_expenses']['update_ccpack_expenses'] = 'Update';
        $permission['ccpack_expenses']['delete_ccpack_expenses'] = 'Delete';
        $permission['ccpack_expenses']['change_file_expense_status_ccpack'] = 'Disbursement';

        $master['ccpack_invoices'] = 'CcpackInvoices';
        $permission['ccpack_invoices']['listing_ccpack_invoices'] = 'Listing';
        $permission['ccpack_invoices']['add_ccpack_invoices'] = 'Add';
        $permission['ccpack_invoices']['update_ccpack_invoices'] = 'Update';
        $permission['ccpack_invoices']['delete_ccpack_invoices'] = 'Delete';
        $permission['ccpack_invoices']['copy_ccpack_invoices'] = 'Copy Invoice';
        $permission['ccpack_invoices']['view_details_ccpack_invoices'] = 'View Detail';

        $master['ccpack_invoice_payments'] = 'CCpack Invoice Payments';
        $permission['ccpack_invoice_payments']['add_ccpack_invoice_payments'] = 'Add';
        $permission['ccpack_invoice_payments']['delete_ccpack_invoice_payments'] = 'Delete';
        $permission['ccpack_invoice_payments']['modify_ccpack_invoice_payments'] = 'Modify';
        

        //-------------------------- Management Expense/Other Expense Related ------------------------------//
        $master['other_expenses'] = 'Other/Management Expenses';
        $permission['other_expenses']['listing_other_expenses'] = 'Listing';
        $permission['other_expenses']['add_other_expenses'] = 'Add';
        $permission['other_expenses']['update_other_expenses'] = 'Update';
        $permission['other_expenses']['delete_other_expenses'] = 'Delete';

        
        
        //-------------------------- Accounts Related ---------------------------------//
        $master['vendors'] = 'Vendors';
        $permission['vendors']['listing_vendors'] = 'Listing';
        $permission['vendors']['add_vendors'] = 'Add';
        $permission['vendors']['update_vendors'] = 'Update';
        $permission['vendors']['delete_vendors'] = 'Delete';

        $master['billing_party'] = 'Billing Party';
        $permission['billing_party']['listing_billing_party'] = 'Listing';
        $permission['billing_party']['add_billing_party'] = 'Add';
        $permission['billing_party']['update_billing_party'] = 'Update';
        $permission['billing_party']['delete_billing_party'] = 'Delete';
        $permission['billing_party']['reset_password_billing_party'] = 'Reset Password';
        $permission['billing_party']['view_details_billing_party'] = 'View Detail';

        $master['clients'] = 'Clients';
        $permission['clients']['listing_clients'] = 'Listing';
        $permission['clients']['add_clients'] = 'Add';
        $permission['clients']['update_clients'] = 'Update';
        $permission['clients']['delete_clients'] = 'Delete';
        $permission['clients']['reset_password_clients'] = 'Reset Password';
        $permission['clients']['view_details_clients'] = 'View Detail';

        $master['client_contacts'] = 'Client Contacts';
        $permission['client_contacts']['listing_client_contacts'] = 'Listing';
        $permission['client_contacts']['add_client_contacts'] = 'Add';
        $permission['client_contacts']['update_client_contacts'] = 'Update';
        $permission['client_contacts']['delete_client_contacts'] = 'Delete';

        $master['vendor_contacts'] = 'Vendor Contacts';
        $permission['vendor_contacts']['listing_vendor_contacts'] = 'Listing';
        $permission['vendor_contacts']['add_vendor_contacts'] = 'Add';
        $permission['vendor_contacts']['update_vendor_contacts'] = 'Update';
        $permission['vendor_contacts']['delete_vendor_contacts'] = 'Delete';
        
        $master['cash_bank'] = 'Cash/Bank';
        $permission['cash_bank']['listing_cash_bank'] = 'Listing';
        $permission['cash_bank']['add_cash_bank'] = 'Add';
        $permission['cash_bank']['update_cash_bank'] = 'Update';
        $permission['cash_bank']['delete_cash_bank'] = 'Delete';

        $master['deposite_vouchers'] = 'Deposite Vouchers';
        $permission['deposite_vouchers']['listing_deposite_vouchers'] = 'Listing';
        $permission['deposite_vouchers']['add_deposite_vouchers'] = 'Add';
        $permission['deposite_vouchers']['update_deposite_vouchers'] = 'Update';
        $permission['deposite_vouchers']['delete_deposite_vouchers'] = 'Delete';


        $master['old_invoices'] = 'Old Invoices';
        $permission['old_invoices']['listing_old_invoices'] = 'Listing';
        $permission['old_invoices']['add_old_invoices'] = 'Add';
        $permission['old_invoices']['update_old_invoices'] = 'Update';
        $permission['old_invoices']['delete_old_invoices'] = 'Delete';
        $permission['old_invoices']['copy_old_invoices'] = 'Copy Invoice';
        $permission['old_invoices']['view_details_old_invoices'] = 'View Detail';
        
        //-------------------------- Reports ---------------------------------//
        $master['reports'] = 'Cargo Reports';
        $permission['reports']['statement_of_account'] = 'Statement Of Accounts';
        $permission['reports']['cash_credit_reports'] = 'Cash Credit Reports';
        $permission['reports']['client_credit_reports'] = 'Client Credit Reports';
        $permission['reports']['warehouse_reports'] = 'Warehouse Reports';
        $permission['reports']['missing_invoice_reports'] = 'List of Expenses Not Yet Invoiced';
        $permission['reports']['non_billed_files'] = 'Non Billed Files';
        $permission['reports']['files_with_expense_but_no_invoices'] = 'Files with expense but no invoices';
        $permission['reports']['invoice_report'] = 'Invoice Report';
        $permission['reports']['Cashier_report'] = 'Cashier Report';
        $permission['reports']['custom_reports'] = 'Custom Reports';

        $master['courier_reports'] = 'Courier Reports';
        $permission['courier_reports']['courier_missing_invoice_reports'] = 'List of Expenses Not Yet Invoiced';
        $permission['courier_reports']['courier_non_billed_files'] = 'Non Billed Files';
        $permission['courier_reports']['courier_files_with_expense_but_no_invoices'] = 'Files with expense but no invoices';

        $master['account_payable'] = 'Account Payable';
        $permission['account_payable']['account_payable_approve_expense'] = 'Approve Expense';
        
        //-------------------------- File Manager ---------------------------------//
        $master['file_manager'] = 'File Manager';
        $permission['file_manager']['show_file_manager'] = 'File Manager';

        //-------------------------- Quickbook ---------------------------------//
        $master['quickbooks'] = 'QuickBooks';
        $permission['quickbooks']['show_quickbooks'] = 'QuickBooks';
        $permission['quickbooks']['qb_error_logs'] = 'QuickBooks Error Logs';

        

        if(isset($_POST['Permissions'])) {
            if(isset($_POST['Permissions']['flagsubmit']) && !empty($_POST['Permissions']['flagsubmit']))
            {
                
                    $type = $_POST['Permissions']['type_flag'];
                    if ($type == '1') {
                        $related_id = $_POST['Permissions']['user_id'];
                    } else {
                        $related_id = $_POST['Permissions']['user_group_id'];
                    }
                    
                    DB::table('permissions')->where('type_flag',$type)->where('related_id',$related_id)->delete();
                    if (isset($_REQUEST['childmodule'])) {
                        foreach ($_REQUEST['childmodule'] as $key => $val) {
                            foreach ($val as $k => $v) {
                                $model = new Permissions();
                                $model->type_flag = $type;
                                $model->related_id = $related_id;
                                $model->slug =         $k;
                                $model->parent_module = $key;
                                $model->permission_flag = 1;
                                $model->created_on = gmdate("Y-m-d H:i:s");
                                $model->created_by = Auth::user()->id;
                                $model->save();        


                                if($type == 2)
                                {
                                    $queryUsers = DB::table('users')
                                            ->select('*')
                                            ->where('department',$related_id)
                                            ->get();
                                            $deptUsers = array();
                                    foreach ($queryUsers as $keyU => $valueU) {
                                                DB::table('permissions')->where('type_flag',1)->where('related_id',$valueU->id)->where('parent_module',$key)->where('slug',$k)->delete();
                                                //$deptUsers[] = $value['id'];
                                                $model = new Permissions();
                                                $model->type_flag = 1;
                                                $model->related_id = $valueU->id;
                                                $model->slug =         $k;
                                                $model->parent_module = $key;
                                                $model->permission_flag = 1;
                                                $model->created_on = gmdate("Y-m-d H:i:s");
                                                $model->created_by = Auth::user()->id;
                                                $model->save(); 
                                            }                                            
                                }

                                $model->id = $model->related_id;
                                //Activities::log('create','permission',$model);
                            }
                        }
                    }
                    Session::flash('flash_message', 'Permission has been Added Successfully.');
                    return '1';
                    //return redirect('permissions/'.$_POST['Permissions']['type_flag']);
            }
            else
            {
                
                $type = $_POST['Permissions']['type_flag'];
                $userid = $_POST['Permissions']['user_id'];
                $usergroupid = $_POST['Permissions']['user_group_id'];
                if ($type == '1') {
                    $query = DB::table('permissions')
                                ->select('*')
                                ->where('type_flag',$type)
                                ->where('related_id',$userid)
                                ->get();
                    $query = json_decode($query,true);
                } else {
                    $query = DB::table('permissions')
                                ->select('*')
                                ->where('type_flag',$type)
                                ->where('related_id',$usergroupid)
                                ->get();
                    $query = json_decode($query,true);                                
                }
                $moduleArray = array();
                if (count($query) > 0) {
                    foreach ($query as $q) {
                        $moduleArray[$q['parent_module']][$q['slug']] = $q['permission_flag'];
                    }
                }

                
                return view('permissions.filterdata', [
                            'data' => $permission,
                            'master' => $master,
                            'query' => $moduleArray,
                            'type' => $type,
                            'userid' => $userid,
                            'usergroupid' => $usergroupid,
                ]);    
            }
        }
        
    }

    public function getuser()
    {
        $deptId = $_POST['deptId'];
        $users = array();
        if(!empty($deptId))
        {
        $users = DB::table('users')->select('id', 'name')->where('department',$deptId)->where('deleted',0)->get();
        //$result = $users->toArray();
        }
        return json_encode($users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}