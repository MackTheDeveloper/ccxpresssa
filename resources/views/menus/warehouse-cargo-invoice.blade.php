<?php
$permissionCargoInvoicesListing = App\User::checkPermission(['listing_cargo_invoices'], '', auth()->user()->id);
$permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();

if (empty($flagModule))
    $flagModule = '';
?>
<?php if ($permissionCargoInvoicesAdd) { ?>
    <a class="<?php echo $currentRoute == "createhousefileinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createhousefileinvoice',$flagModule) }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add House File Invoice</span></a>
<?php } ?>
<?php if ($permissionCargoInvoicesListing) { ?>
    <a class="<?php echo $currentRoute == "housefileinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('housefileinvoices',$flagModule) }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House File Invoices</span></a>
<?php } ?>