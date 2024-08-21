<?php 
    $permissionCurrenciesAdd = App\User::checkPermission(['add_currencies'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCurrenciesAdd) { ?>
<a class="<?php echo $currentRoute == "createcurrency" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createcurrency')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Currency</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "currency" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('currency')); ?>"><i style="color: #fff" class="fa fa-money fa-2x"></i><span class="menuname">Currencies</span></a><?php /**PATH /var/www/html/php/cargo/resources/views/menus/currency.blade.php ENDPATH**/ ?>