<?php
$permissionCourierImportUpload = App\User::checkPermission(['upload_courier_import'], '', auth()->user()->id);
$permissionUpsMasterListing = App\User::checkPermission(['listing_ups_master'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionUpsMasterListing) { ?>
	<a class="<?php echo $currentRoute == "warehouseupsmaster" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouseupsmaster') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master Files Listing</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "warehouseups" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('warehouseups') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS House Files Listing</span></a>
<?php if ($permissionCourierImportUpload) { ?>
	<a class="<?php echo $currentRoute == "importupswarehouse" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('importupswarehouse') }}"><i style="color: #fff" class="fa fa-upload fa-2x"></i><span class="menuname">Upload Files</span></a>
<?php } ?>