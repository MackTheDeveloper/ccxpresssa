<?php 
    $permissionUsersAdd = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionUsersAdd) { ?>
<a class="<?php echo $currentRoute == "register" ? "activeMenu" : ""; ?>" href="{{ route('register') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add User</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "users" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('users') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Users</span></a>