<?php
$permissionCashBankAdd = App\User::checkPermission(['add_cash_bank'], '', auth()->user()->id);
$permissionListingBankAdd = App\User::checkPermission(['listing_cash_bank'], '', auth()->user()->id);
$permissionCashBankDepositeVouchersAdd = App\User::checkPermission(['add_deposite_vouchers'], '', auth()->user()->id);
$permissionCashBankDepositeVouchersListing = App\User::checkPermission(['listing_deposite_vouchers'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionCashBankAdd) { ?>
    <a class="<?php echo $currentRoute == "createcashcredit" ? "activeMenu" : ""; ?>" href="{{ route('createcashcredit') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cash/Bank</span></a>
<?php } ?>
<?php if ($permissionListingBankAdd) { ?>
    <a class="<?php echo $currentRoute == "cashcredit" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('cashcredit') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Manage Cash/Bank</span></a>
<?php } ?>
<?php if ($permissionCashBankDepositeVouchersAdd) { ?>
    <a class="<?php echo $currentRoute == "createdepositcashcredit" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('createdepositcashcredit') }}"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Replenish Account</span></a>
<?php } ?>
<?php if ($permissionCashBankDepositeVouchersListing) { ?>
    <a class="<?php echo $currentRoute == "depositcashcredit" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('depositcashcredit') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Replenish Account Listing</span></a>
<?php } ?>