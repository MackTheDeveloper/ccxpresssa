<?php
$permissionCourierExpensesListing = App\User::checkPermission(['listing_courier_expenses'], '', auth()->user()->id);
$permissionMasterUpsExpenseListing = App\User::checkPermission(['listing_ups_master_expenses'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionMasterUpsExpenseListing) { ?>
    <a class="<?php echo $currentRoute == "upsmasterexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upsmasterexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master File Expense Listing</span></a>
<?php } ?>
<?php if ($permissionCourierExpensesListing) { ?>
    <a class="<?php echo $currentRoute == "upsexpenses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upsexpenses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS File Expense Listing</span></a>
<?php } ?>