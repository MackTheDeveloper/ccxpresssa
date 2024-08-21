<?php
$permissionListing = App\User::checkPermission(['listing_aeropost'], '', auth()->user()->id);
$permissionAeropostMasterListing = App\User::checkPermission(['listing_aeropost_master'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionAeropostMasterListing) { ?>
	<a class="<?php echo $currentRoute == "warehouseaeropostmaster" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouseaeropostmaster') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost Master Files Listing</span></a>
<?php } ?>
<?php if ($permissionListing) { ?>
	<a class="<?php echo $currentRoute == "warehouseaeroposts" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouseaeroposts') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost Files Listing</span></a>
<?php } ?>