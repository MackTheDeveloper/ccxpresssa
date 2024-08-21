<?php
$permissionAdd = App\User::checkPermission(['add_delivery_boy'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionAdd) { ?>
    <a class="<?php echo $currentRoute == "createdeliveryboy" ? "activeMenu" : ""; ?>" href="{{ route('createdeliveryboy') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Delivery Boy</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "deliveryboys" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('deliveryboys') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Delivery Boys</span></a>