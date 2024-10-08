<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
@section('client_css')
@include('layouts.included_css_js')
@show

<body class="skin-green-light">
    <div id="loading">
        <img id="loading-image" src="{{asset('images/loading.gif')}}" class="admin_img" alt="logo">
    </div>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container-fluid m-0">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        <?php

                        $permissionBillingItemListing = App\User::checkPermission(['listing_billing_items'], '', auth()->user()->id);
                        $permissionCostsListing = App\User::checkPermission(['listing_costs'], '', auth()->user()->id);
                        $permissionUsersListing = App\User::checkPermission(['listing_users'], '', auth()->user()->id);
                        $permissionPermissionAdd = App\User::checkPermission(['create_permissions'], '', auth()->user()->id);
                        $permissionAccountTypesListing = App\User::checkPermission(['listing_account_types'], '', auth()->user()->id);
                        $permissionAccountSubTypesListing = App\User::checkPermission(['listing_account_sub_types'], '', auth()->user()->id);
                        $permissionPaymentTermsListing = App\User::checkPermission(['listing_payment_terms'], '', auth()->user()->id);
                        $permissionCountriesListing = App\User::checkPermission(['listing_countries'], '', auth()->user()->id);
                        $permissionCurrenciesListing = App\User::checkPermission(['listing_currencies'], '', auth()->user()->id);
                        $permissionOtherExpenseItemsListing = App\User::checkPermission(['listing_other_expense_items'], '', auth()->user()->id);
                        $permissionWarehousesListing = App\User::checkPermission(['listing_warehouses'], '', auth()->user()->id);
                        $permissionStorageChargesListing = App\User::checkPermission(['listing_storage_charges'], '', auth()->user()->id);
                        $permissionStorageRacksListing = App\User::checkPermission(['listing_storage_racks'], '', auth()->user()->id);
                        $permissionListingUpsCommission = App\User::checkPermission(['listing_ups_commission'], '', auth()->user()->id);
                        $permissionListingFdCharges = App\User::checkPermission(['listing_fd_charges'], '', auth()->user()->id);
                        $permissionListingFileprogressStatus = App\User::checkPermission(['listing_file_progress_status'], '', auth()->user()->id);
                        $permissionListingDeliveryBoy = App\User::checkPermission(['listing_delivery_boy'], '', auth()->user()->id);
                        $permissionFileClose = App\User::checkPermission(['close_file'], '', auth()->user()->id);
                        $permissionManifests = App\User::checkPermission(['listing_manifestes'], '', auth()->user()->id);

                        $permissionCourierImportListing = App\User::checkPermission(['listing_courier_import'], '', auth()->user()->id);
                        $permissionCourierExpensesListing = App\User::checkPermission(['listing_courier_expenses'], '', auth()->user()->id);
                        $permissionAeropostExpensesListing = App\User::checkPermission(['listing_aeropost_expenses'], '', auth()->user()->id);
                        $permissionCcpackExpensesListing = App\User::checkPermission(['listing_ccpack_expenses'], '', auth()->user()->id);
                        $permissionCourierOtherExpensesListing = App\User::checkPermission(['listing_courier_other_expenses'], '', auth()->user()->id);
                        $permissionCourierCustomExpensesListing = App\User::checkPermission(['listing_courier_custom_expenses'], '', auth()->user()->id);
                        $permissionCourierInvoicesListing = App\User::checkPermission(['listing_courier_invoices'], '', auth()->user()->id);
                        $permissionCourierInvoicePaymentsAdd = App\User::checkPermission(['add_courier_invoice_payments'], '', auth()->user()->id);

                        $permissionCourierExportListing = App\User::checkPermission(['listing_courier_export'], '', auth()->user()->id);

                        $permissionGuaranteeCheck = App\User::checkPermission(['listing_guarantee_check'], '', auth()->user()->id);

                        $permissionCargoListing = App\User::checkPermission(['listing_cargo'], '', auth()->user()->id);
                        $permissionCargoHAWBListing = App\User::checkPermission(['listing_cargo_hawb'], '', auth()->user()->id);
                        $permissionCargoExpensesListing = App\User::checkPermission(['listing_cargo_expenses'], '', auth()->user()->id);
                        $permissionCargoOtherExpensesListing = App\User::checkPermission(['listing_cargo_other_expenses'], '', auth()->user()->id);
                        $permissionCargoInvoicesListing = App\User::checkPermission(['listing_cargo_invoices'], '', auth()->user()->id);
                        $permissionCargoInvoicePaymentsAdd = App\User::checkPermission(['add_cargo_invoice_payments'], '', auth()->user()->id);
                        $permissionOtherExpensesListing = App\User::checkPermission(['listing_other_expenses'], '', auth()->user()->id);
                        $permissionOldInvoicesListing = App\User::checkPermission(['listing_old_invoices'], '', auth()->user()->id);
                        $permissionVendorsListing = App\User::checkPermission(['listing_vendors'], '', auth()->user()->id);
                        $permissionClientsListing = App\User::checkPermission(['listing_clients'], '', auth()->user()->id);
                        $permissionCashBankListing = App\User::checkPermission(['listing_cash_bank'], '', auth()->user()->id);

                        $permissionCashCreditReports = App\User::checkPermission(['cash_credit_reports'], '', auth()->user()->id);
                        $permissionClientCreditReports = App\User::checkPermission(['client_credit_reports'], '', auth()->user()->id);
                        $permissionMissingInvoiceReports = App\User::checkPermission(['missing_invoice_reports'], '', auth()->user()->id);
                        $permissionCustomReports = App\User::checkPermission(['custom_reports'], '', auth()->user()->id);
                        $permissionWarehouseReports = App\User::checkPermission(['warehouse_reports'], '', auth()->user()->id);
                        $permissionNonBilledFilesReports = App\User::checkPermission(['non_billed_files'], '', auth()->user()->id);
                        $permissionCourierListofExpensesNotYetInvoiced = App\User::checkPermission(['courier_missing_invoice_reports'], '', auth()->user()->id);
                        $permissionCourierNonBilledFilesReports = App\User::checkPermission(['courier_non_billed_files'], '', auth()->user()->id);
                        $permissionCourierFilesWithExpenseButNoInvoices = App\User::checkPermission(['courier_files_with_expense_but_no_invoices'], '', auth()->user()->id);
                        $permissionInvoiceReport = App\User::checkPermission(['invoice_report'], '', auth()->user()->id);
                        $permissionCashierReport = App\User::checkPermission(['Cashier_report'], '', auth()->user()->id);

                        $permissionAeropostFileListing = App\User::checkPermission(['listing_aeropost'], '', auth()->user()->id);
                        $permissionAeropostInvoicesListing = App\User::checkPermission(['listing_aeropost_invoices'], '', auth()->user()->id);

                        $permissionCCpackAdd = App\User::checkPermission(['add_ccpack'], '', auth()->user()->id);
                        $permissionCCpackListing = App\User::checkPermission(['listing_ccpack'], '', auth()->user()->id);
                        $permissionCCpackInvoiceListing = App\User::checkPermission(['listing_ccpack_invoices'], '', auth()->user()->id);

                        $permissionFileManager = App\User::checkPermission(['show_file_manager'], '', auth()->user()->id);
                        $permissionQbErrorLogs = App\User::checkPermission(['qb_error_logs'], '', auth()->user()->id);
                        $permissionCashBankDepositeVouchersListing = App\User::checkPermission(['listing_deposite_vouchers'], '', auth()->user()->id);
                        ?>


                        <?php if (auth()->user()->department == 12) { // Agent Menu
                        ?>
                            <?php if ($permissionCourierImportListing || $permissionCourierExpensesListing) { ?>
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Courier Management<span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu">
                                        <li class="menu-item dropdown dropdown-submenu">
                                            <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">UPS Files<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                            <ul class="dropdown-menu sub-dpmenu">
                                                <?php if ($permissionCourierImportListing) { ?>
                                                    <li>
                                                        <a href="{{ route('agentups') }}">Manage Files</a>
                                                    </li>
                                                <?php } ?>
                                                <?php if ($permissionCourierInvoicesListing) { ?>
                                                    <li class="menu-item"><a href="{{ route('upsinvoices') }}">Manage Invoices</a></li>
                                                <?php } ?>
                                                <?php if ($permissionCourierExpensesListing) { ?>
                                                    <li>
                                                        <a href="{{ route('upsexpenses') }}">Manage Expenses</a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                        <li class="menu-item dropdown dropdown-submenu">
                                            <a class="aeropost dropdown-toggle" href="javascript:void(0)">Aeropost<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                            <ul class="dropdown-menu sub-dpmenu">
                                                <?php if ($permissionAeropostFileListing) { ?>
                                                    <li><a href="{{ route('aeroposts') }}">Manage Files</a></li>
                                                <?php } ?>
                                                <?php if ($permissionAeropostInvoicesListing) { ?>
                                                    <li><a href="{{ route('aeropostinvoices') }}">Manage Invoices</a></li>
                                                <?php } ?>
                                                <?php if ($permissionAeropostExpensesListing) { ?>
                                                    <li><a href="{{ route('aerpostexpenses') }}">Manage Expenses</a></li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                        <li class="menu-item dropdown dropdown-submenu">
                                            <a class="aeropost dropdown-toggle" href="javascript:void(0)">CCPack<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                            <ul class="dropdown-menu sub-dpmenu">
                                                <?php if ($permissionCCpackListing) { ?>
                                                    <li><a href="{{ route('ccpack') }}">Manage Files</a></li>
                                                <?php } ?>
                                                <?php if ($permissionCCpackInvoiceListing) { ?>
                                                    <li class="menu-item"><a href="{{ route('ccpackinvoices') }}">Manage Invoices</a></li>
                                                <?php } ?>
                                                <?php if ($permissionCcpackExpensesListing) { ?>
                                                    <li><a href="{{ route('ccpackexpenses') }}">Manage Expenses</a></li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoListing || $permissionCargoExpensesListing || $permissionGuaranteeCheck) { ?>
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Cargo Management<span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu">
                                        <?php if ($permissionCargoListing) { ?>
                                            <li>
                                                <a href="{{ route('agentcargoall') }}">Manage Files</a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($permissionCargoInvoicesListing) { ?>
                                            <li>
                                                <a href="{{ route('invoices') }}">Manage Invoices</a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($permissionCargoExpensesListing) { ?>
                                            <li>
                                                <a href="{{ route('expenses') }}">Manage Expenses</a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($permissionGuaranteeCheck) { ?>
                                            <li>
                                                <a href="{{ route('check-guarantee') }}">Guarantee Checks</a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($permissionOtherExpensesListing) { ?>
                                <li class="dropdown">
                                    <a href="{{ route('otherexpenses') }}">Administration</a>
                                </li>
                                <li class="dropdown" style="display: none">
                                    <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Administration<span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu" style="width: 230px;">

                                        <li class="menu-item dropdown dropdown-submenu">
                                            <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">Administration Expenses<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                            <ul class="dropdown-menu sub-dpmenu">
                                                <li class="menu-item"><a href="{{ route('otherexpenses') }}">Administration Expense Listing</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($permissionVendorsListing || $permissionClientsListing || $permissionCashBankListing || $permissionCourierInvoicePaymentsAdd || $permissionCargoInvoicePaymentsAdd) { ?>
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Accounts<span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu">
                                        <?php if ($permissionVendorsListing) { ?>
                                            <li>
                                                <a href="{{ route('vendors') }}">Vendors</a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($permissionClientsListing) { ?>
                                            <li>
                                                <a href="{{ route('clients','B') }}">Billing Parties</a>
                                            </li>
                                        <?php } ?>
                                        <li>
                                            <a href="{{ route('clients','O') }}">Clients</a>
                                        </li>
                                        <?php if ($permissionCashBankListing) { ?>
                                            <li>
                                                <a href="{{ route('cashcredit') }}">Cash/Bank</a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($permissionCourierInvoicePaymentsAdd || $permissionCargoInvoicePaymentsAdd) { ?>
                                            <li>
                                                <a href="{{ route('invoicepaymentcreateall') }}">Add Payment</a>
                                            </li>
                                        <?php } ?>
                                        <?php if ($permissionOldInvoicesListing) { ?>
                                            <li>
                                                <a href="{{ route('oldinvoices') }}">Old Invoices</a>
                                            </li>
                                        <?php } ?>
                                        <li style="display:none">
                                            <a href="{{ route('reportsinvoicesfiles') }}">All Invoices</a>
                                        </li>
                                    </ul>
                                </li>
                            <?php } ?>
                        <?php } else if (auth()->user()->department == 14) { // Warehouse Menu 
                        ?>
                            <?php if ($permissionListingDeliveryBoy) { ?>
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Global Settings<span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu">
                                        <?php if ($permissionListingDeliveryBoy) { ?>
                                            <li>
                                                <a href="{{ route('deliveryboys') }}">Delivery Boy</a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCourierImportListing) { ?>
                                <li class="dropdown">
                                    <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Courier Management<span class="caret"></span></a>
                                    <ul role="menu" class="dropdown-menu">
                                        <li class="menu-item dropdown dropdown-submenu">
                                            <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">UPS Files<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                            <ul class="dropdown-menu sub-dpmenu">
                                                <li class="menu-item dropdown dropdown-submenu">
                                                    <?php if ($permissionCourierImportListing) { ?>
                                                <li>
                                                    <a href="{{ route('warehouseups') }}">Files Listing</a>
                                                </li>
                                            <?php } ?>
                                            <?php if ($permissionCourierInvoicesListing) { ?>
                                                <li>
                                                    <a href="{{ route('housefileinvoices','ups') }}">Manage Invoices</a>
                                                </li>
                                            <?php } ?>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item dropdown dropdown-submenu">
                                    <a class="aeropost dropdown-toggle" href="javascript:void(0)">Aeropost<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                    <ul class="dropdown-menu sub-dpmenu">
                                        <?php if ($permissionAeropostFileListing) { ?>
                                            <li><a href="{{ route('warehouseaeroposts') }}">Files Listing</a></li>
                                        <?php } ?>
                                        <?php if ($permissionAeropostInvoicesListing) { ?>
                                            <li><a href="{{ route('housefileinvoices','aeropost') }}">Manage Invoices</a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                <li class="menu-item dropdown dropdown-submenu">
                                    <a class="aeropost dropdown-toggle" href="javascript:void(0)">Ccpack<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                    <ul class="dropdown-menu sub-dpmenu">
                                        <?php if ($permissionCCpackListing) { ?>
                                            <li><a href="{{ route('warehouseccpack') }}">Files Listing</a></li>
                                        <?php } ?>
                                        <?php if ($permissionCCpackInvoiceListing) { ?>
                                            <li><a href="{{ route('housefileinvoices','ccpack') }}">Manage Invoices</a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                    </ul>
                    </li>
                <?php } ?>
                <?php if ($permissionCargoListing || $permissionCargoInvoicesListing || $permissionGuaranteeCheck) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">Cargo Management<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <?php if ($permissionCargoListing) { ?>
                                <li>
                                    <a href="{{ route('warehousecargoall') }}">Manage Files</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoInvoicesListing) { ?>
                                <li>
                                    <a href="{{ route('housefileinvoices','cargo') }}">Manage Invoices</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionGuaranteeCheck) { ?>
                                <li>
                                    <a href="{{ route('check-guarantee') }}">Guarantee Checks</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            <?php } else if (auth()->user()->department == 11) { // Cashier Menu
            ?>
                <?php if ($permissionCourierImportListing || $permissionCourierExpensesListing) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Courier Management<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <li class="menu-item dropdown dropdown-submenu">
                                <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">UPS Files<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <?php if ($permissionCourierImportListing) { ?>
                                        <li style="display:none">
                                            <a href="{{ route('agentups') }}">Manage Files</a>
                                        </li>
                                    <?php } ?>
                                    <?php if ($permissionCourierImportListing) { ?>
                                        <li class="menu-item"><a href="{{ route('ups') }}">Manage Files</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCourierInvoicesListing) { ?>
                                        <li class="menu-item"><a href="{{ route('upsinvoices') }}">Manage Invoices</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCourierExpensesListing) { ?>
                                        <li>
                                            <a href="{{ route('upsexpenses') }}">Manage Expenses</a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="menu-item dropdown dropdown-submenu">
                                <a class="aeropost dropdown-toggle" href="javascript:void(0)">Aeropost<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <?php if ($permissionAeropostFileListing) { ?>
                                        <li><a href="{{ route('aeroposts') }}">Manage Files</a></li>
                                    <?php } ?>
                                    <?php if ($permissionAeropostInvoicesListing) { ?>
                                        <li><a href="{{ route('aeropostinvoices') }}">Manage Invoices</a></li>
                                    <?php } ?>
                                    <?php if ($permissionAeropostExpensesListing) { ?>
                                        <li><a href="{{ route('aerpostexpenses') }}">Manage Expenses</a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <li class="menu-item dropdown dropdown-submenu">
                                <a class="aeropost dropdown-toggle" href="javascript:void(0)">CCPack<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <?php if ($permissionCCpackListing) { ?>
                                        <li><a href="{{ route('ccpack') }}">Manage Files</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCCpackInvoiceListing) { ?>
                                        <li class="menu-item"><a href="{{ route('ccpackinvoices') }}">Manage Invoices</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCcpackExpensesListing) { ?>
                                        <li><a href="{{ route('ccpackexpenses') }}">Manage Expenses</a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php } ?>
                <?php if ($permissionCargoExpensesListing || $permissionCargoListing || $permissionCargoInvoicesListing || $permissionGuaranteeCheck) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">Cargo Management<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <?php if ($permissionCargoListing) { ?>
                                <li style="display: none">
                                    <a href="{{ route('cashiercargoall') }}">Manage Files</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoListing) { ?>
                                <li>
                                    <a href="{{ route('cargoall') }}">Manage Files</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoExpensesListing) { ?>
                                <li>
                                    <a href="{{ route('expenses') }}">Manage Expenses</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoInvoicesListing) { ?>
                                <li style="display: none">
                                    <a href="{{ route('cashierwarehouseinvoicesoffile') }}">Manage Invoices</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoInvoicesListing) { ?>
                                <li>
                                    <a href="{{ route('invoices') }}">Manage Invoices</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionGuaranteeCheck) { ?>
                                <li>
                                    <a href="{{ route('check-guarantee') }}">Guarantee Checks</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php if ($permissionOtherExpensesListing) { ?>
                    <li class="dropdown">
                        <a href="{{ route('otherexpenses') }}">Administration</a>
                    </li>
                    <li class="dropdown" style="display: none">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Administration<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu" style="width: 230px;">

                            <li class="menu-item dropdown dropdown-submenu">
                                <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">Administration Expenses<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <li class="menu-item"><a href="{{ route('otherexpenses') }}">Administration Expense Listing</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php } ?>
                <?php //if($permissionCashierReport) { 
                ?>
                <li class="dropdown" style="display:none">
                    <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Reports<span class="caret"></span></a>
                    <ul role="menu" class="dropdown-menu">
                        <?php $id = auth()->user()->id; ?>
                        <li style="display:none"><a href="{{ route('cashierReportAllDetail',$id) }}">Report</a></li>
                        <li class="menu-item dropdown dropdown-submenu">
                            <a data-toggle="dropdown" class="ups-reports dropdown-toggle" href="javascript:void(0)">Other Reports<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                            <ul class="dropdown-menu sub-dpmenu">
                                <li>
                                    <a href="{{ route('genericdisbursementreport') }}">Disbursement Report</a>
                                </li>
                                <li>
                                    <a href="{{ route('genericcollectionreport') }}">Collection Report</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <?php //} 
                ?>
                <?php if ($permissionCashBankDepositeVouchersListing) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Accounts<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <li><a href="{{ route('depositcashcredit') }}">Cash/Bank</a></li>
                            <?php if ($permissionCourierInvoicePaymentsAdd || $permissionCargoInvoicePaymentsAdd) { ?>
                                <li>
                                    <a href="{{ route('invoicepaymentcreateall') }}">Add Payment</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionOldInvoicesListing) { ?>
                                <li>
                                    <a href="{{ route('oldinvoices') }}">Old Invoices</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            <?php } else { //Admin Menu 
            ?>

                <?php if ($permissionBillingItemListing || $permissionCostsListing || $permissionUsersListing || $permissionPermissionAdd || $permissionAccountTypesListing || $permissionAccountSubTypesListing || $permissionPaymentTermsListing || $permissionCountriesListing || $permissionCurrenciesListing || $permissionOtherExpenseItemsListing || $permissionWarehousesListing || $permissionStorageChargesListing) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Global Settings<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <?php if ($permissionBillingItemListing) { ?>
                                <li>
                                    <a href="{{ route('billingitems') }}">Billing Items</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCostsListing) { ?>
                                <li>
                                    <a href="{{ route('costs') }}">File Costs</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionUsersListing) { ?>
                                <li class="dropdown">
                                    <a href="{{ route('users') }}">Users Management</a>
                                </li>
                            <?php } ?>

                            <?php if ($permissionPermissionAdd) { ?>
                                <li>
                                    <a href="{{ route('permissions','1') }}">Permissions Management</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionAccountTypesListing) { ?>
                                <li>
                                    <a href="{{ route('cashcreditaccounttype') }}">Account Types</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionAccountSubTypesListing) { ?>
                                <li>
                                    <a href="{{ route('cashcreditdetailtype') }}">Account Sub Types</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionPaymentTermsListing) { ?>
                                <li>
                                    <a href="{{ route('paymentterms') }}">Payment Terms</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCountriesListing) { ?>
                                <li>
                                    <a href="{{ route('countries') }}">Countries</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCurrenciesListing) { ?>
                                <li>
                                    <a href="{{ route('currency') }}">Currencies</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionOtherExpenseItemsListing) { ?>
                                <li>
                                    <a href="{{ route('otherexpenseitems') }}">Other Expense Items</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionWarehousesListing) { ?>
                                <li>
                                    <a href="{{ route('warehouses') }}">Warehouses</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionStorageChargesListing) { ?>
                                <li>
                                    <a href="{{ route('storagecharges') }}">Storage Charges</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionStorageRacksListing) { ?>
                                <li>
                                    <a href="{{ route('storageracks') }}">Storage Racks</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionListingUpsCommission) { ?>
                                <li>
                                    <a href="{{ route('upscommissiondetails') }}">Commission</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionListingFdCharges) { ?>
                                <li>
                                    <a href="{{ route('fdcharges') }}">Free Domicile Charges</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionListingFileprogressStatus) { ?>
                                <li>
                                    <a href="{{ route('filestatusindex') }}">In Progress Status</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionListingDeliveryBoy) { ?>
                                <li>
                                    <a href="{{ route('deliveryboys') }}">Delivery Boy</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionQbErrorLogs) { ?>
                                <li>
                                    <a href="{{ route('qberrorlog') }}">QuickBook Error Logs</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionFileClose) { ?>
                                <li>
                                    <a href="{{ route('closefiles') }}">Close File</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if ($permissionCourierImportListing || $permissionCourierExpensesListing || $permissionCourierInvoicesListing || $permissionAeropostFileListing || $permissionAeropostInvoicesListing || $permissionCCpackListing || $permissionCCpackInvoiceListing) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Courier Management<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">

                            <li class="menu-item dropdown dropdown-submenu">
                                <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">UPS Files<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <?php if ($permissionCourierImportListing) { ?>
                                        <li class="menu-item"><a href="{{ route('ups') }}">Manage Files</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCourierInvoicesListing) { ?>
                                        <li class="menu-item"><a href="{{ route('upsinvoices') }}">Manage Invoices</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCourierExpensesListing) { ?>
                                        <li>
                                            <a href="{{ route('upsexpenses') }}">Manage Expenses</a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>

                            <li class="menu-item dropdown dropdown-submenu">
                                <a class="aeropost dropdown-toggle" href="javascript:void(0)">Aeropost<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <?php if ($permissionAeropostFileListing) { ?>
                                        <li><a href="{{ route('aeroposts') }}">Manage Files</a></li>
                                    <?php } ?>
                                    <?php if ($permissionAeropostInvoicesListing) { ?>
                                        <li><a href="{{ route('aeropostinvoices') }}">Manage Invoices</a></li>
                                    <?php } ?>
                                    <?php if ($permissionAeropostExpensesListing) { ?>
                                        <li><a href="{{ route('aerpostexpenses') }}">Manage Expenses</a></li>
                                    <?php } ?>
                                </ul>
                            </li>

                            <li class="menu-item dropdown dropdown-submenu">
                                <a href="javascript:void(0)" data-toggle="dropdown" class="ccpack dropdown-toggle">CCPack<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <?php if ($permissionCCpackListing) { ?>
                                        <li class="menu-item"><a href="{{ route('ccpack') }}">Manage Files</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCCpackInvoiceListing) { ?>
                                        <li class="menu-item"><a href="{{ route('ccpackinvoices') }}">Manage Invoices</a></li>
                                    <?php } ?>
                                    <?php if ($permissionCcpackExpensesListing) { ?>
                                        <li><a href="{{ route('ccpackexpenses') }}">Manage Expenses</a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if ($permissionCargoListing || $permissionCargoExpensesListing || $permissionCargoInvoicesListing || $permissionGuaranteeCheck) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Cargo Management<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <?php if ($permissionCargoListing) { ?>
                                <li>
                                    <a href="{{ route('cargoall') }}">Manage Files</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoExpensesListing) { ?>
                                <li>
                                    <a href="{{ route('expenses') }}">Manage Expenses</a>
                                    <?php /* if (auth()->user()->department == 10) { ?>
                                        <a href="{{ route('managerexpenses') }}">Manage Expenses</a>
                                    <?php } else { ?>
                                        <a href="{{ route('expenses') }}">Manage Expenses</a>
                                    <?php } */ ?>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCargoInvoicesListing) { ?>
                                <li>
                                    <a href="{{ route('invoices') }}">Manage Invoices</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionGuaranteeCheck) { ?>
                                <li>
                                    <a href="{{ route('check-guarantee') }}">Guarantee Checks</a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if ($permissionOtherExpensesListing) { ?>
                    <li class="dropdown">
                        <a href="{{ route('otherexpenses') }}">Administration</a>
                    </li>
                    <li class="dropdown" style="display: none">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Administration<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu" style="width: 230px;">

                            <li class="menu-item dropdown dropdown-submenu">
                                <a data-toggle="dropdown" class="ups dropdown-toggle" href="javascript:void(0)">Administration Expenses<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                                <ul class="dropdown-menu sub-dpmenu">
                                    <li class="menu-item"><a href="{{ route('otherexpenses') }}">Administration Expense Listing</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if ($permissionVendorsListing || $permissionClientsListing || $permissionCashBankListing || $permissionCourierInvoicePaymentsAdd || $permissionCargoInvoicePaymentsAdd) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Accounts<span class="caret"></span></a>
                        <ul role="menu" class="dropdown-menu">
                            <?php if ($permissionVendorsListing) { ?>
                                <li>
                                    <a href="{{ route('vendors') }}">Vendors</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionClientsListing) { ?>
                                <li>
                                    <a href="{{ route('clients','B') }}">Billing Parties</a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="{{ route('clients','O') }}">Clients</a>
                            </li>
                            <?php if ($permissionCashBankListing) { ?>
                                <li>
                                    <a href="{{ route('cashcredit') }}">Cash/Bank</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionCourierInvoicePaymentsAdd || $permissionCargoInvoicePaymentsAdd) { ?>
                                <li>
                                    <a href="{{ route('invoicepaymentcreateall') }}">Add Payment</a>
                                </li>
                            <?php } ?>
                            <?php if ($permissionOldInvoicesListing) { ?>
                                <li>
                                    <a href="{{ route('oldinvoices') }}">Old Invoices</a>
                                </li>
                            <?php } ?>
                            <li style="display:none">
                                <a href="{{ route('reportsinvoicesfiles') }}">All Invoices</a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>
            <?php } ?>
            <?php //if ($permissionCashCreditReports || $permissionClientCreditReports || $permissionMissingInvoiceReports || $permissionCustomReports) { 
            ?>
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle main-menu-link" href="#">Reports<span class="caret"></span></a>
                <ul role="menu" class="dropdown-menu">

                    <li class="menu-item dropdown dropdown-submenu">
                        <a data-toggle="dropdown" class="ups-reports dropdown-toggle" href="javascript:void(0)">Cargo Reports<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                        <ul class="dropdown-menu sub-dpmenu">
                            <?php //if ($permissionCashCreditReports) { 
                            ?>
                            <li>
                                <a href="{{ route('cashcreditallreport') }}">Cash/Bank Report</a>
                            </li>
                            <?php //} 
                            ?>
                            <?php //if ($permissionClientCreditReports) { 
                            ?>
                            <li>
                                <a href="{{ route('clientcreditallreport') }}">Client Ledger Report</a>
                            </li>
                            <?php //} 
                            ?>
                            <?php //if ($permissionMissingInvoiceReports) { 
                            ?>
                            <li>
                                <a href="{{ route('missingInvoiceReports') }}">List of Expenses Not Yet Invoiced</a>
                            </li>
                            <?php //} 
                            ?>
                            <?php //if ($permissionNonBilledFilesReports) { 
                            ?>
                            <li>
                                <a href="{{ route('nonbilledfiles') }}">Non Billed Files</a>
                            </li>
                            <li>
                                <a href="{{ route('filesWithExpenseNoInvoices','Cargo') }}">Files with expense but no invoices</a>
                            </li>
                            <li style="display:none">

                                <?php //if ($permissionInvoiceReport) { 
                                ?>
                                <a href="{{ route('invoicePaymentReport') }}">Invoice Payment Report</a>
                                <?php //} 
                                ?>
                            </li>
                            <li>
                                <?php //if ($permissionCashierReport) { 
                                ?>
                                <a href="{{ route('cashierReport') }}">Cashier Report</a>
                                <?php //} 
                                ?>
                            </li>
                            <?php //} 
                            ?>
                            <?php //if ($permissionWarehouseReports) { 
                            ?>
                            <li>
                                <a href="{{ route('warehousereport') }}">Warehouse Report</a>
                            </li>
                            <?php //} 
                            ?>
                        </ul>
                    </li>

                    <li class="menu-item dropdown dropdown-submenu">
                        <a data-toggle="dropdown" class="ups-reports dropdown-toggle" href="javascript:void(0)">Courier Reports<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                        <ul class="dropdown-menu sub-dpmenu">
                            <?php //if ($permissionCustomReports) { 
                            ?>
                            <li>
                                <a href="{{ route('customreport') }}">Customs Report</a>
                            </li>
                            <?php //} 
                            ?>
                            <li>
                                <a href="{{ route('commissionReport') }}">Commission Report</a>
                            </li>

                            <li>
                                <a href="{{ route('freeDomicileReport') }}">Free Domicile Report</a>
                            </li>
                            <li style="display: none">
                                <a href="{{ route('upsexpensepayments') }}">Payments/Expenses</a>
                            </li>
                            <li style="display: none">
                                <a href="{{ route('upsprofitreports') }}">Profit Report</a>
                            </li>
                            <li style="display: none">

                                <a href="{{ route('templeteReport') }}">Consolidation Report</a>
                            </li>
                            <li>
                                <a href="{{ route('warehousereportcourier') }}">Warehouse Report</a>
                            </li>
                            <?php //if ($permissionCourierListofExpensesNotYetInvoiced) { 
                            ?>
                            <li>
                                <a href="{{ route('upsMissingInvoiceReport') }}">List of Expenses Not Yet Invoiced</a>
                            </li>
                            <?php //} 
                            ?>
                            <?php //if ($permissionCourierNonBilledFilesReports) { 
                            ?>
                            <li>
                                <a href="{{ route('nonbilledfilescourier') }}">Non Billed Files</a>
                            </li>
                            <?php //} 
                            ?>
                            <?php //if ($permissionCourierFilesWithExpenseButNoInvoices) { 
                            ?>
                            <li>
                                <a href="{{ route('filesWithExpenseNoInvoicesCourier','Courier') }}">Files with expense but no invoices</a>
                            </li>
                            <?php //} 
                            ?>
                        </ul>
                    </li>
                    <li class="menu-item dropdown dropdown-submenu">
                        <a data-toggle="dropdown" class="ups-reports dropdown-toggle" href="javascript:void(0)">Other Reports<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                        <ul class="dropdown-menu sub-dpmenu">
                            <li>
                                <a href="{{ route('genericdisbursementreport') }}">Disbursement Report</a>
                            </li>
                            <li>
                                <a href="{{ route('genericcollectionreport') }}">Collection Report</a>
                            </li>
                            <li style="display: none">
                                <a href="{{ route('checkguaranteetopayreport') }}">Check Guarantee to pay Report</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('combineReport') }}">Combine Report</a>
                    </li>
                    <li>
                        <a href="{{ route('statementofaccounts') }}">Pending Invoices</a>
                    </li>
                    <li>
                        <a href="{{ route('accountpayablereport') }}">Account Payable Report</a>
                    </li>
                    <li>
                        <a href="{{ route('billingitemsdetailreport') }}">Billing Items Detail Report</a>
                    </li>
                    <li>
                        <a href="{{ route('costitemsdetailreport') }}">Cost Items Detail Report</a>
                    </li>
                    <?php if ($permissionFileManager) { ?>
                        <li style="">
                            <a href="{{route('filemanager')}}">File Manager</a>
                        </li>
                    <?php } ?>
                    <li class="menu-item dropdown dropdown-submenu">
                        <a data-toggle="dropdown" class="ups-reports dropdown-toggle" href="javascript:void(0)">Open Invoices<i class="fa fa-caret-down" style="font-size:15px;margin-left: 10%;padding-top: 2%;float: right"></i></a>
                        <ul class="dropdown-menu sub-dpmenu">
                            <li>
                                <a href="{{ route('showStatementOfAccount') }}">Statement Of Accounts</a>
                            </li>
                            <li>
                                <a href="{{ route('showPendingInvoices') }}">Pending Invoices</a>
                            </li>
                        </ul>
                    </li>
                    <li style="">
                        <a href="{{route('showArAging')}}">A/R Aging</a>
                    </li>
                </ul>
            </li>
            <?php //} 
            ?>
            <?php if ($permissionManifests) { ?>
                <li>
                    <a href="{{ route('manifests') }}">Manifests</a>
                </li>
            <?php } ?>


            </ul>



            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">


                <?php if (auth()->user()->department == 13 || auth()->user()->department == 10) { ?>
                    <li class="dropdown">
                        <?php $notiAll = App\Admin::getNotificationForAdmin('All');  ?>
                        <a data-toggle="dropdown1" style="float: left;" class="dropdown-toggle" href="{{route('viewallnotifications')}}"><i class="fa fas fa-bell" style="font-size:19px;color:#ffffff;float: left;"></i><span class="adminnotitotal pendingexpense pendingexpenseall"><?php echo $notiAll; ?></span></a>
                        <?php $noti = App\Admin::getNotificationForAdmin('displayOnBell');

                        foreach ($noti as $key => $row) {
                            // replace 0 with the field's index/key
                            $notiAll1['notificationDateTime'][$key] = $row->notificationDateTime;
                            $notiAll1['notificationStatus'][$key] = $row->notificationStatus;
                        }

                        if (!empty($noti))
                            array_multisort($notiAll1['notificationStatus'], SORT_DESC, $notiAll1['notificationDateTime'], SORT_DESC, $noti);
                        ?>
                        <ul role="menu" class="dropdown-menu adminnotiul notiallul">
                            <?php
                            if (!empty($noti)) {
                                $countNoti = 0;
                                foreach ($noti as $k => $v) {
                                    $countNoti = $countNoti + 1;
                                    if ($countNoti > 5)
                                        break;

                                    $unreadnoti = '';
                                    if ($v->notificationStatus == 1)
                                        $unreadnoti = 'unreadnoti';
                            ?>

                                    <?php if ($v->flagModule == 'CargoExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'cargoExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UpsExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'upsExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UpsMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'upsMasterExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'houseFileExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'housefileExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'aeropostExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'aeropostExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'AeropostMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'aeropostMasterExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'ccpackExpense') {  ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'ccpackExpense'])}}">
                                                <?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'CcpackMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'ccpackMasterExpense'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'administrationExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('editotherexpense',[$v->id])}}">
                                                <?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } ?>
                            <?php }
                            }
                            ?>
                            <li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (auth()->user()->department == 11) { //Cashier 
                ?>
                    <li class="dropdown">
                        <?php $notiAll = App\Cashier::getNotificationForCashier('All');  ?>
                        <a data-toggle="dropdown1" style="float: left;" class="dropdown-toggle" href="{{route('viewallnotifications')}}"><i class="fa fas fa-bell" style="font-size:19px;color:#ffffff;float: left;"></i><span class="cashiernotitotal pendingexpense pendingexpenseall"><?php echo $notiAll; ?></span></a>
                        <?php $noti = App\Cashier::getNotificationForCashier('displayOnBell');
                        foreach ($noti as $key => $row) {
                            // replace 0 with the field's index/key
                            $notiAll1['notificationDateTime'][$key] = $row->notificationDateTime;
                            $notiAll1['notificationStatus'][$key] = $row->notificationStatus;
                        }
                        if (!empty($noti))
                            array_multisort($notiAll1['notificationStatus'], SORT_DESC, $notiAll1['notificationDateTime'], SORT_DESC, $noti);
                        ?>
                        <ul role="menu" class="dropdown-menu cashiernotiul notiallul">

                            <?php
                            if (!empty($noti)) {
                                $countNoti = 0;
                                foreach ($noti as $k => $v) {
                                    $countNoti = $countNoti + 1;
                                    if ($countNoti > 5)
                                        break;

                                    $unreadnoti = '';
                                    if ($v->notificationStatus == 1)
                                        $unreadnoti = 'unreadnoti';
                            ?>
                                    <?php if ($v->flagModule == 'CargoExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleexpensecashier',[$v->expense_id,$v->cargo_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UpsExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleupsexpensecashier',[$v->expense_id,$v->ups_details_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UpsMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewupsmasterexpenseforcashier',[$v->expense_id,$v->ups_master_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'houseFileExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsinglehousefileexpensecashier',[$v->expense_id,$v->house_file_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'aeropostExpense') {  ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleaeropostexpensecashier',[$v->expense_id,$v->aeropost_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'AeropostMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewaeropostmasterexpenseforcashier',[$v->expense_id,$v->aeropost_master_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'ccpackExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleccpackexpensecashier',[$v->expense_id,$v->ccpack_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'CcpackMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewccpackmasterexpenseforcashier',[$v->expense_id,$v->ccpack_master_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } else if ($v->flagModule == 'administrationExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleadministrationexpensecashier',[$v->id,'fromNotification'])}}">
                                                <?php echo $v->notificationMessage;; ?></a></li>
                                    <?php } ?>

                            <?php }
                            }
                            ?>
                            <li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li>
                        </ul>
                    </li>
                <?php } ?>


                <?php if (auth()->user()->department == 12) { //Agent 
                ?>
                    <li class="dropdown">
                        <?php $notiAll = App\Agent::getNotificationForAgent('All');  ?>
                        <a data-toggle="dropdown1" style="float: left;" class="dropdown-toggle" href="{{route('viewallnotifications')}}"><i class="fa fas fa-bell" style="font-size:19px;color:#ffffff;float: left;"></i><span class="agentnotitotal pendingexpense pendingexpenseall"><?php echo $notiAll; ?></span></a>
                        <?php $noti = App\Agent::getNotificationForAgent('displayOnBell');
                        foreach ($noti as $key => $row) {
                            // replace 0 with the field's index/key
                            $notiAll1['notificationDateTime'][$key] = $row->notificationDateTime;
                            $notiAll1['notificationStatus'][$key] = $row->notificationStatus;
                        }

                        if (!empty($noti))
                            array_multisort($notiAll1['notificationStatus'], SORT_DESC, $notiAll1['notificationDateTime'], SORT_DESC, $noti);
                        ?>
                        <ul role="menu" class="dropdown-menu agentnotiul notiallul">

                            <?php
                            if (!empty($noti)) {
                                $countNoti = 0;
                                foreach ($noti as $k => $v) {
                                    $countNoti = $countNoti + 1;
                                    if ($countNoti > 5)
                                        break;

                                    $unreadnoti = '';
                                    if ($v->notificationStatus == 1)
                                        $unreadnoti = 'unreadnoti';
                            ?>

                                    <?php if ($v->flagModule == 'Cargo File Assigned') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargodetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UPS File Assigned') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcourierdetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'CargoExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsingleexpense',[$v->expense_id,$v->cargo_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UpsExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsingleupsexpense',[$v->expense_id,$v->ups_details_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'UpsMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('printsingleupsmasterexpense',[$v->expense_id,$v->ups_master_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'houseFileExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsinglehousefileexpense',[$v->expense_id,$v->house_file_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'aeropostExpense') {  ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('printoneaeropostexpense',[$v->expense_id,$v->aeropost_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'AeropostMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('printsingleaeropostmasterexpense',[$v->expense_id,$v->aeropost_master_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'ccpackExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('printoneccpackexpense',[$v->expense_id,$v->ccpack_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } else if ($v->flagModule == 'CcpackMasterExpense') { ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('printsingleccpackmasterexpense',[$v->expense_id,$v->ccpack_master_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
                                    <?php } ?>
                            <?php }
                            }
                            ?>
                            <li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (auth()->user()->department == 14) { //Warehouse 
                    if (checkNonBoundedWH() == 'Yes') {
                ?>
                        <li class="dropdown">
                            <?php $notiAll = App\Warehouse::getNotificationForWarehouse('All');  ?>
                            <a data-toggle="dropdown1" style="float: left;" class="dropdown-toggle" href="{{route('viewallnotifications')}}"><i class="fa fas fa-bell" style="font-size:19px;color:#ffffff;float: left;"></i><span class="warehousenotitotal pendingexpense pendingexpenseall"><?php echo $notiAll; ?></span></a>
                            <?php $noti = App\Warehouse::getNotificationForWarehouse('displayOnBell');
                            foreach ($noti as $key => $row) {
                                // replace 0 with the field's index/key
                                $notiAll1['notificationDateTime'][$key] = $row->notificationDateTime;
                                $notiAll1['notificationStatus'][$key] = $row->notificationStatus;
                            }

                            if (!empty($noti))
                                array_multisort($notiAll1['notificationStatus'], SORT_DESC, $notiAll1['notificationDateTime'], SORT_DESC, $noti);
                            ?>
                            <ul role="menu" class="dropdown-menu warehousenotiul notiallul">

                                <?php
                                if (!empty($noti)) {
                                    $countNoti = 0;
                                    foreach ($noti as $k => $v) {
                                        $countNoti = $countNoti + 1;
                                        if ($countNoti > 5)
                                            break;

                                        $unreadnoti = '';
                                        if ($v->notificationStatus == 1)
                                            $unreadnoti = 'unreadnoti';
                                ?>
                                        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('acceptfiles',[$v->id,'fromNotification',$v->flagModule])}}"><?php echo $v->notificationMessage; ?></a></li>
                                <?php }
                                }
                                ?>
                                <li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li>
                            </ul>
                        </li>
                <?php }
                } ?>



                <!-- Authentication Links -->
                @if (Auth::guest())
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
                @else
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                Logout
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                    </ul>
                </li>
                @endif
            </ul>
                </div>
            </div>
        </nav>



        <!-- @section('sidebar')
        @show -->

        <?php if (Route::currentRouteName() != 'home' && Route::currentRouteName() != 'homeagent') { ?>
            <div class="breadcrumbs" style="width: 100%;background: #b5b5b5;padding-top: 10px">
                @section('breadcrumbs')
                @show
            </div>
        <?php } ?>

        <?php if (Route::currentRouteName() == 'home') { ?>
            <div class="" style="margin-top: 0px;">
                @yield('content')
            </div>
        <?php } else { ?>
            <div class="content-wrapper" style="margin-left: 0px;">
                @yield('content')
                <div id="modalUploadNewFiles" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" id="model-close">×</button>
                                <h3 class="modal-title modal-title-block text-center primecolor">Add New</h3>
                            </div>
                            <div class="modal-body" id="modalContentUploadNewFiles" style="overflow: hidden;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>
    @section('scripts')
    @yield('page_level_js')
    @show
</body>

</html>

<script type="text/javascript">
    /* window.setInterval(function(){
        $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
        });
        
        var urlNotiCount = '<?php //echo url("checknotificationscount"); 
                            ?>';
        var urlNoti = '<?php //echo url("checknotifications"); 
                        ?>';
        <?php //if(auth()->user()->department == 13 || auth()->user()->department == 10) { 
        ?>
            var flagModule = 'admin-manager';
        <?php //} else if(auth()->user()->department == 11) { 
        ?>
            var flagModule = 'cashier';
        <?php //} else if(auth()->user()->department == 12) {  
        ?>
            var flagModule = 'agent';
        <?php //} else if(auth()->user()->department == 14) { 
        ?>
            var flagModule = 'warehouse';
        <?php //} 
        ?>
                    $.ajax({
                        url:urlNotiCount,
                        async:false,
                        type:'POST',
                        data:{'flagModule':flagModule},
                        success:function(data) {
                                    if(flagModule == 'admin-manager')               
                                        $('.adminnotitotal').text(data);

                                    if(flagModule == 'cashier')               
                                        $('.cashiernotitotal').text(data);

                                    if(flagModule == 'agent')               
                                        $('.agentnotitotal').text(data);

                                    if(flagModule == 'warehouse')               
                                        $('.warehousenotitotal').text(data);
                                }
                        });

                        $.ajax({
                        url:urlNoti,
                        async:false,
                        type:'POST',
                        data:{'flagModule':flagModule},
                        success:function(data) {
                                    if(flagModule == 'admin-manager')               
                                        $('.adminnotiul').html(data);

                                    if(flagModule == 'cashier')               
                                        $('.cashiernotiul').html(data);

                                    if(flagModule == 'agent')               
                                        $('.agentnotiul').html(data);

                                    if(flagModule == 'warehouse')               
                                        $('.warehousenotiul').html(data);
                                }
                    });
        

    }, 5000); */
    $('.main-menu-link').on("click", function(e) {
        $('.sub-dpmenu').hide();
    });
    $('a.ccpack,a.ups,a.aeropost,a.ups-reports').on("click", function(e) {
        $('.sub-dpmenu').hide();
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
    });
</script>
<style>
    .dropdown-submenu .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -1px;
    }
</style>