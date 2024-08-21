<?php 
    $permissionCargoListing = App\User::checkPermission(['listing_cargo'],'',auth()->user()->id); 
    $permissionCargoHAWBListing = App\User::checkPermission(['listing_cargo_hawb'],'',auth()->user()->id); 
    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCargoListing) { ?>
<a class="<?php echo $currentRoute == "warehousecargoall" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehousecargoall') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo Files Listing
</span></a>
<?php } ?>
<?php if($permissionCargoListing) { ?>
    <a class="<?php echo $currentRoute == "warehousehawbfiles" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehousehawbfiles') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House AWB Files Listing
    </span></a>
<?php } ?>
