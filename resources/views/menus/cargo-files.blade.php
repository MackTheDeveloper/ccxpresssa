<?php 
	$permissionCargoAdd = App\User::checkPermission(['add_cargo'],'',auth()->user()->id);
    $permissionCargoHAWBAdd = App\User::checkPermission(['add_cargo_hawb'],'',auth()->user()->id); 
    $permissionCargoHAWBListing = App\User::checkPermission(['listing_cargo_hawb'],'',auth()->user()->id); 
    $permissionWarehousesListing = App\User::checkPermission(['listing_warehouses'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();

    $dept = auth()->user()->department;

    if($dept == '11') // Cashier
    {
    	$listingCargoUrl = 'cashiercargoall';
    }else
    {
    	$listingCargoUrl = 'cargoall';
    }

?>
<?php if($permissionCargoAdd) { ?>
<a class="<?php echo $currentRoute == "cargoimport" ? "activeMenu" : ""; ?>" href="{{ route('cargoimport','1') }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cargo File</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == $listingCargoUrl ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route($listingCargoUrl) }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo Files Listing</span></a>
<?php if($permissionCargoHAWBAdd) { ?>
<a class="<?php echo $currentRoute == "createhawbfile" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('createhawbfile') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add House AWB File</span></a>
<?php } ?>
<?php if($permissionCargoHAWBListing) { ?>
<a class="<?php echo $currentRoute == "hawbfiles" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('hawbfiles') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House AWB Files Listing</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == 'cashierLocalFileListing' ? "activeMenu" : ""; ?>" style="margin-left: 10px;display:none" href="{{ route('cashierLocalFileListing') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Local(rental) File Listing</span></a>
