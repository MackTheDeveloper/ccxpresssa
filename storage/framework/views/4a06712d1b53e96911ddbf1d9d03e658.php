<?php
$permissionCourierInvoicesAdd = App\User::checkPermission(['add_aeropost_invoices'], '', auth()->user()->id);
$permissionAeropostInvoices = App\User::checkPermission(['listing_aeropost_invoices'], '', auth()->user()->id);
$permissionCourierInvoicePaymentsAdd = App\User::checkPermission(['add_aeropost_invoice_payments'], '', auth()->user()->id);
$permissionMasterAeropostInvoicesAdd = App\User::checkPermission(['add_aeropost_master_invoices'], '', auth()->user()->id);
$permissionMasterAeropostInvoices = App\User::checkPermission(['listing_aeropost_master_invoices'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();

?>
<?php if ($permissionMasterAeropostInvoicesAdd) { ?>
    <a class="<?php echo $currentRoute == "createaeropostmasterinvoice" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createaeropostmasterinvoice')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost Master Invoice</span></a>
<?php } ?>
<?php if ($permissionCourierInvoicesAdd) { ?>
    <a class="<?php echo $currentRoute == "createaeropostinvoice" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createaeropostinvoice')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost House Invoice</span></a>
<?php } ?>
<?php if ($permissionMasterAeropostInvoices) { ?>
    <a class="<?php echo $currentRoute == "aeropostmasterinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('aeropostmasterinvoices')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost Master Invoices</span></a>
<?php } ?>
<?php if ($permissionAeropostInvoices) { ?>
    <a class="<?php echo $currentRoute == "aeropostinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('aeropostinvoices')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost House Invoices</span></a>
<?php } ?>
<?php if ($permissionCourierInvoicePaymentsAdd) { ?>
    <a class="<?php echo $currentRoute == "addaeropostinvoicepayment" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('addaeropostinvoicepayment')); ?>"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Add Payment</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "invoicepaymentslisting" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('invoicepaymentslisting','aeropost')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Invoice Payment Listing</span></a><?php /**PATH /var/www/html/php/cargo/resources/views/menus/aeropost-invoice.blade.php ENDPATH**/ ?>