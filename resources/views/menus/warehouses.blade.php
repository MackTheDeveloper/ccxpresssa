<?php 
    $permissionWarehouseAdd = App\User::checkPermission(['add_warehouses'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionWarehouseAdd) { ?>
<a class="<?php echo $currentRoute == "createwarehouse" ? "activeMenu" : ""; ?>" href="{{ route('createwarehouse') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Warehouse</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "warehouses" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouses') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Warehouses</span></a>