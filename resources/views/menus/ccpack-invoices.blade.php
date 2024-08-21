<?php
$houseInvoiceAdd = App\User::checkPermission(['add_ccpack_invoices'], '', auth()->user()->id);
$houseInvoices = App\User::checkPermission(['listing_ccpack_invoices'], '', auth()->user()->id);
$masterInvoiceAdd = App\User::checkPermission(['add_ccpack_master_invoices'], '', auth()->user()->id);
$masterInvoices = App\User::checkPermission(['listing_ccpack_master_invoices'], '', auth()->user()->id);
$addPayment = App\User::checkPermission(['add_ccpack_invoice_payments'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();

?>
<?php if ($masterInvoiceAdd) { ?>
    <a class="<?php echo $currentRoute == "createccpackmasterinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createccpackmasterinvoice') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack Master Invoice</span></a>
<?php } ?>
<?php if ($houseInvoiceAdd) { ?>
    <a class="<?php echo $currentRoute == "createccpackinvoices" ? "activeMenu" : ""; ?>" href="{{ route('createccpackinvoices') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack House Invoice</span></a>
<?php } ?>
<?php if ($masterInvoices) { ?>
    <a class="<?php echo $currentRoute == "ccpackmasterinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ccpackmasterinvoices') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack Master Invoices</span></a>
<?php } ?>
<?php if ($houseInvoices) { ?>
    <a class="<?php echo $currentRoute == "ccpackinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ccpackinvoices') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack House Invoices</span></a>
<?php } ?>
<?php if ($addPayment) { ?>
    <a class="<?php echo $currentRoute == "addccpackinvoicepayment" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('addccpackinvoicepayment') }}"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Add Payment</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "invoicepaymentslisting" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('invoicepaymentslisting','ccpack') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Invoice Payment Listing</span></a>