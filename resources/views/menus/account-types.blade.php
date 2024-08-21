<?php 
    $permissionAccountTypesAdd = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionAccountTypesAdd) { ?>
<a class="<?php echo $currentRoute == "createcashcreditaccounttype" ? "activeMenu" : ""; ?>" href="{{ route('createcashcreditaccounttype') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Account Type</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "cashcreditaccounttype" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('cashcreditaccounttype') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Account Types</span></a>