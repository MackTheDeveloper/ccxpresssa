<?php 
    $permissionCurrenciesAdd = App\User::checkPermission(['add_currencies'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCurrenciesAdd) { ?>
<a class="<?php echo $currentRoute == "createcurrency" ? "activeMenu" : ""; ?>" href="{{ route('createcurrency') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Currency</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "currency" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('currency') }}"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Currencies</span></a>