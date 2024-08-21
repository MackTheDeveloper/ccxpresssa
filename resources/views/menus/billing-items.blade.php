<?php 
    $permissionBillingItemAdd = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionBillingItemAdd) { ?>
<a class="<?php echo $currentRoute == "createbillingitem" ? "activeMenu" : ""; ?>" href="{{ route('createbillingitem') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Billing Item</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "billingitems" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('billingitems') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Billing Items</span></a>