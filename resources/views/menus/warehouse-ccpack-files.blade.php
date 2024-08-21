<?php
$permissionCCpackListing = App\User::checkPermission(['listing_ccpack'], '', auth()->user()->id);
$permissionCcpackMasterListing = App\User::checkPermission(['listing_ccpack_master'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionCcpackMasterListing) { ?>
	<a class="<?php echo $currentRoute == "warehouseccpackmaster" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouseccpackmaster') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack Master Files Listing</span></a>
<?php } ?>
<?php
if ($permissionCCpackListing) { ?>
	<a class="<?php echo $currentRoute == "warehouseccpack" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouseccpack') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack Files Listing</span></a>
<?php } ?>