<?php
$permissionCourierInvoicesAdd = App\User::checkPermission(['add_courier_invoices'], '', auth()->user()->id);
$permissionCourierInvoicePaymentsAdd = App\User::checkPermission(['add_courier_invoice_payments'], '', auth()->user()->id);
$permissionMasterUpsInvoicesAdd = App\User::checkPermission(['add_ups_master_invoices'], '', auth()->user()->id);
$permissionMasterUpsInvoices = App\User::checkPermission(['listing_ups_master_invoices'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();

?>
<?php if ($permissionMasterUpsInvoicesAdd) { ?>
    <a class="<?php echo $currentRoute == "createupsmasterinvoice" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createupsmasterinvoice')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS Master Invoice</span></a>
<?php } ?>
<?php if ($permissionCourierInvoicesAdd) { ?>
    <a class="<?php echo $currentRoute == "createupsinvoice" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createupsinvoice')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS House Invoice</span></a>
<?php } ?>
<?php if ($permissionMasterUpsInvoices) { ?>
    <a class="<?php echo $currentRoute == "upsmasterinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('upsmasterinvoices')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master Invoices</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "upsinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('upsinvoices')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS House Invoices</span></a>
<?php if ($permissionCourierInvoicePaymentsAdd) { ?>
    <a class="<?php echo $currentRoute == "addupsinvoicepayment" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('addupsinvoicepayment')); ?>"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Add Payment</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "invoicepaymentslisting" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('invoicepaymentslisting','ups')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Invoice Payment Listing</span></a><?php /**PATH /var/www/html/php/cargo/resources/views/menus/ups-invoice.blade.php ENDPATH**/ ?>