<?php
$permissionCourierExpenseAdd = App\User::checkPermission(['add_courier_expenses'], '', auth()->user()->id);
$permissionMasterUpsExpenseAdd = App\User::checkPermission(['add_ups_master_expenses'], '', auth()->user()->id);
$permissionMasterUpsExpenseListing = App\User::checkPermission(['listing_ups_master_expenses'], '', auth()->user()->id);
$permissionOtherExpenseAdd = App\User::checkPermission(['add_other_expenses'], '', auth()->user()->id);
$permissionCourierCustomExpenseAdd = App\User::checkPermission(['add_courier_custom_expenses'], '', auth()->user()->id);

$permissionCourierExpensesListing = App\User::checkPermission(['listing_courier_import'], '', auth()->user()->id);
$permissionOtherExpensesListing = App\User::checkPermission(['listing_other_expenses'], '', auth()->user()->id);
$permissionCourierCustomExpensesListing = App\User::checkPermission(['listing_courier_custom_expenses'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionMasterUpsExpenseAdd) { ?>
	<a class="<?php echo $currentRoute == "createupsmasterexpense" ? "activeMenu" : ""; ?>" href="{{ route('createupsmasterexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS Master File Expense Request</span></a>
<?php } ?>
<?php if ($permissionCourierExpenseAdd) { ?>
	<a class="<?php echo $currentRoute == "createupsexpense" ? "activeMenu" : ""; ?>" href="{{ route('createupsexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS House File Expense Request</span></a>
<?php } ?>
<?php if ($permissionCourierCustomExpenseAdd) { ?>
	<a class="<?php echo $currentRoute == "createcustomexpense" ? "activeMenu" : ""; ?>" href="{{ route('createcustomexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Custom Expense</span></a>
<?php } ?>
<?php if ($permissionMasterUpsExpenseListing) { ?>
	<a class="<?php echo $currentRoute == "upsmasterexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upsmasterexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master File Expense Listing</span></a>
<?php } ?>
<?php if ($permissionCourierExpensesListing) { ?>
	<a class="<?php echo $currentRoute == "upsexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upsexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS House File Expense Listing</span></a>
<?php } ?>
<?php if ($permissionCourierCustomExpensesListing) { ?>
	<a class="<?php echo $currentRoute == "customexpneses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('customexpneses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Custom Expenses</span></a>
<?php } ?>