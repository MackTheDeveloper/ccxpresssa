<?php 
    $permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicePaymentsAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    $permissionCargoInvoicesListing = App\User::checkPermission(['listing_cargo_invoices'],'',auth()->user()->id); 
    

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCargoInvoicesAdd) { ?>
<a class="<?php echo $currentRoute == "createinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createinvoice') }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cargo Invoice</span></a>
<a class="<?php echo $currentRoute == "createhousefileinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createhousefileinvoice','cargo') }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add House File Invoice</span></a>
<?php } ?>
<?php if($permissionCargoInvoicesListing) { ?>
<a class="<?php echo $currentRoute == "invoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('invoices') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo Invoices</span></a>
<a class="<?php echo $currentRoute == "housefileinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('housefileinvoices','cargo') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House File Invoices</span></a>
<a class="<?php echo $currentRoute == "pendinginvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('pendinginvoices') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Pending Cargo Invoices</span></a>
<?php } ?>
<?php if($permissionCargoInvoicePaymentsAdd) { ?>
<a class="<?php echo $currentRoute == "addinvoicepayment" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('addinvoicepayment',[0,0,0,'yes']) }}"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Add Payment</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "invoicepaymentslisting" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('invoicepaymentslisting','cargo') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Invoice Payment Listing</span></a>
<a class="<?php echo $currentRoute == "importcargoinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px;display: none;" href="{{ route('importcargoinvoices') }}"><i style="color: #fff" class="fa fa-upload fa-2x"></i><span class="menuname">Import Cargo Invoices</span></a>