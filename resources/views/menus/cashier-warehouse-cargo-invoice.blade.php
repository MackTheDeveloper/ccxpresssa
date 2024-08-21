<?php
$permissionCargoInvoicesListing = App\User::checkPermission(['listing_cargo_invoices'], '', auth()->user()->id);
$permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();

?>
<?php if ($permissionCargoInvoicesAdd) { ?>
    <a style="display: none" class="<?php echo $currentRoute == "createcashierwarehouseinvoicesoffile" ? "activeMenu" : ""; ?>" href="{{ route('createcashierwarehouseinvoicesoffile') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Invoice</span></a>
    <a  class="<?php echo $currentRoute == "createinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createinvoice') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Invoice</span></a>
    <a class="<?php echo $currentRoute == "createhousefileinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createhousefileinvoice','cargo') }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add House File Invoice</span></a>
<?php } ?>
<?php if ($permissionCargoInvoicesListing) { ?>
    <a class="<?php echo $currentRoute == "cashierwarehouseinvoicesoffile" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('cashierwarehouseinvoicesoffile') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Invoice Listing</span></a>
    <a class="<?php echo $currentRoute == "housefileinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('housefileinvoices','cargo') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House File Invoices</span></a>
    <a class="<?php echo $currentRoute == "addinvoicepayment" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('addinvoicepayment',[0,0,0,'yes']) }}"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Add Bulk Payment</span></a>

<?php } ?>