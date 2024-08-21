<?php 
    $permissionStorageRackAdd = App\User::checkPermission(['add_storage_racks'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionStorageRackAdd) { ?>
<a class="<?php echo $currentRoute == "createstoragerack" ? "activeMenu" : ""; ?>" href="{{ route('createstoragerack') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Storage Rack</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "storageracks" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('storageracks') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Storage Racks</span></a>