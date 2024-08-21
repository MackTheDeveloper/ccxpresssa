<?php 
    $permissionCargoExpensesAdd = App\User::checkPermission(['add_cargo_expenses'],'',auth()->user()->id); 
    $permissionCargoExpensesListing = App\User::checkPermission(['listing_cargo_expenses'],'',auth()->user()->id); 
    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCargoExpensesAdd) { ?>
<a class="<?php echo $currentRoute == "createmanagerexpenses" ? "activeMenu" : ""; ?>" href="{{ route('createmanagerexpenses',['cargo']) }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cargo File Expense Request</span></a>
<?php } ?>
<?php if($permissionCargoExpensesListing) { ?>
<a class="<?php echo $currentRoute == "managerexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('managerexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo File Expense Listing</span></a>
<?php } ?>
