<?php 
    $permissionAccountSubTypesAdd = App\User::checkPermission(['add_account_sub_types'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionAccountSubTypesAdd) { ?>
<a class="<?php echo $currentRoute == "createcashcreditdetailtype" ? "activeMenu" : ""; ?>" href="{{ route('createcashcreditdetailtype') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Account Sub Type</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "cashcreditdetailtype" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('cashcreditdetailtype') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Account Sub Types</span></a>