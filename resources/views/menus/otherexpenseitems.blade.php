<?php 
    $permissionOtherExpenseItemsAdd = App\User::checkPermission(['add_other_expense_items'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionOtherExpenseItemsAdd) { ?>
<a class="<?php echo $currentRoute == "createotherexpenseitem" ? "activeMenu" : ""; ?>" href="{{ route('createotherexpenseitem') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Other Expense Item</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "otherexpenseitems" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('otherexpenseitems') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Other Expense Items</span></a>