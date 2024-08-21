<?php
$permissionAdd = App\User::checkPermission(['add_ccpack_expenses'], '', auth()->user()->id);
$permissionAddMaster = App\User::checkPermission(['add_ccpack_master_expenses'], '', auth()->user()->id);
$permissionListing = App\User::checkPermission(['listing_ccpack_expenses'], '', auth()->user()->id);
$permissionListingMaster = App\User::checkPermission(['listing_ccpack_master_expenses'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionAddMaster && checkloggedinuserdata() != 'Cashier') { ?>
    <a class="<?php echo $currentRoute == "createccpackmasterexpense" ? "activeMenu" : ""; ?>" href="{{ route('createccpackmasterexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack Master File Expense Request</span></a>
<?php } ?>
<?php if ($permissionAdd && checkloggedinuserdata() != 'Cashier') { ?>
    <a class="<?php echo $currentRoute == "ccpackexpensecreate" ? "activeMenu" : ""; ?>" href="{{ route('ccpackexpensecreate') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack House File Expense Request</span></a>
<?php } ?>
<?php if ($permissionListingMaster) { ?>
    <a class="<?php echo $currentRoute == "ccpackmasterexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ccpackmasterexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack Master File Expense Listing</span></a>
<?php } ?>
<?php if ($permissionListing) { ?>
    <a class="<?php echo $currentRoute == "ccpackexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ccpackexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack House File Expense Listing</span></a>
<?php } ?>