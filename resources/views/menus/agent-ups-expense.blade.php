<?php
$permissionCourierExpensesAdd = App\User::checkPermission(['add_courier_expenses'], '', auth()->user()->id);
$permissionMasterUpsExpenseAdd = App\User::checkPermission(['add_ups_master_expenses'], '', auth()->user()->id);
$permissionCourierExpensesListing = App\User::checkPermission(['listing_courier_expenses'], '', auth()->user()->id);
$permissionMasterUpsExpenseListing = App\User::checkPermission(['listing_ups_master_expenses'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionMasterUpsExpenseAdd) { ?>
    <a class="<?php echo $currentRoute == "createupsmasterexpense" ? "activeMenu" : ""; ?>" href="{{ route('createupsmasterexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS Master File Expense Request</span></a>
<?php } ?>
<?php if ($permissionCourierExpensesAdd) { ?>
    <a class="<?php echo $currentRoute == "createagentupsexpenses" ? "activeMenu" : ""; ?>" href="{{ route('createagentupsexpenses') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS House File Expense Request</span></a>
<?php } ?>
<?php if ($permissionMasterUpsExpenseListing) { ?>
    <a class="<?php echo $currentRoute == "upsmasterexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upsmasterexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master File Expense Listing</span></a>
<?php } ?>
<?php if ($permissionCourierExpensesListing) { ?>
    <a class="<?php echo $currentRoute == "upsexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upsexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS House File Expense Listing</span></a>
<?php } ?>