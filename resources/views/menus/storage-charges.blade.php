<?php 
    $permissionStorageChargeAdd = App\User::checkPermission(['add_storage_charges'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionStorageChargeAdd) { ?>
<a class="<?php echo $currentRoute == "createstoragecharge" ? "activeMenu" : ""; ?>" href="{{ route('createstoragecharge') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Storage Charge</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "storagecharges" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('storagecharges') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Storage Charges</span></a>