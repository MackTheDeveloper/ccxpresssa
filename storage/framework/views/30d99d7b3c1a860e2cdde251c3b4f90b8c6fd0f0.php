<?php
$permissionCargoExpensesAdd = App\User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
$permissionCargoOtherExpensesAdd = App\User::checkPermission(['add_other_expenses'], '', auth()->user()->id);
$permissionCargoOtherExpensesListing = App\User::checkPermission(['listing_other_expenses'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionCargoExpensesAdd) { ?>
    <a class="<?php echo $currentRoute == "createexpenseusingawl" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createexpenseusingawl','cargo')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cargo File Expense Request</span></a>

    <a class="<?php echo $currentRoute == "createhousefileexpense" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createhousefileexpense')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add House File Expense Request</span></a>

<?php } ?>
<a class="<?php echo $currentRoute == "expenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('expenses')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo File Expense Listing</span></a>
<a class="<?php echo $currentRoute == "housefileexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('housefileexpenses')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House File Expense Listing</span></a>