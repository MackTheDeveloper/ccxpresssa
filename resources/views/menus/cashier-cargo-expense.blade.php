<?php 
    $permissionCargoExpensesListing = App\User::checkPermission(['listing_cargo_expenses'],'',auth()->user()->id);$permissionOtherExpenseListing = App\User::checkPermission(['listing_other_expense_items'],'',auth()->user()->id);
    $permissionOtherExpenseCreate = App\User::checkPermission(['add_other_expense_items'],'',auth()->user()->id);

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCargoExpensesListing) { ?>
<a class="<?php echo $currentRoute == "expenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('expenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo File Expense Listing</span></a>
<a class="<?php echo $currentRoute == "housefileexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('housefileexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House File Expense Listing</span></a>
<?php } ?>
