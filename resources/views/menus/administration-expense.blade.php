<?php
$permissionOtherExpenseAdd = App\User::checkPermission(['add_other_expenses'], '', auth()->user()->id);
$permissionOtherExpenseListing = App\User::checkPermission(['listing_other_expenses'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();

?>
<?php //if ($permissionOtherExpenseAdd && (checkloggedinuserdata() == 'Other' || checkloggedinuserdata() == 'Agent' || checkloggedinuserdata() == 'Cashier')) { 
if ($permissionOtherExpenseAdd) { ?>
    <a class="<?php echo $currentRoute == "createotherexpense" ? "activeMenu" : ""; ?>" href="{{ route('createotherexpense') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Administration Expense</span></a>
<?php } ?>
<?php if ($permissionOtherExpenseListing) { ?>
    <a class="<?php echo $currentRoute == "otherexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('otherexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Administration Expense Listing</span></a>
<?php } ?>