<?php
$permissionAdd = App\User::checkPermission(['add_aeropost_expenses'], '', auth()->user()->id);
$permissionMasterAeropostExpenseAdd = App\User::checkPermission(['add_aeropost_master_expenses'], '', auth()->user()->id);
$permissionListing = App\User::checkPermission(['listing_aeropost_expenses'], '', auth()->user()->id);
$permissionMasterAeropostExpenseListing = App\User::checkPermission(['listing_aeropost_master_expenses'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionMasterAeropostExpenseAdd && checkloggedinuserdata() != 'Cashier') { ?>
    <a class="<?php echo $currentRoute == "createaeropostmasterexpense" ? "activeMenu" : ""; ?>" href="{{ route('createaeropostmasterexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost Master File Expense Request</span></a>
<?php } ?>
<?php if ($permissionAdd && checkloggedinuserdata() != 'Cashier') { ?>
    <a class="<?php echo $currentRoute == "aeropostexpensecreate" ? "activeMenu" : ""; ?>" href="{{ route('aeropostexpensecreate') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost House File Expense Request</span></a>
<?php } ?>
<?php if ($permissionMasterAeropostExpenseListing) { ?>
    <a class="<?php echo $currentRoute == "aeropostmasterexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('aeropostmasterexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost Master File Expense Listing</span></a>
<?php } ?>
<?php if ($permissionListing) { ?>
    <a class="<?php echo $currentRoute == "aerpostexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('aerpostexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost House File Expense Listing</span></a>
<?php } ?>