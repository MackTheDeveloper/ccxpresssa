<?php
$permissionCourierImportUpload = App\User::checkPermission(['upload_courier_import'], '', auth()->user()->id);
$permissionUpsMasterListing = App\User::checkPermission(['listing_ups_master'], '', auth()->user()->id);
$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionUpsMasterListing) { ?>
	<a class="<?php echo $currentRoute == "ups-master" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ups-master') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master Files Listing</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "agentups" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('agentups') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS House Files Listing</span></a>
<?php if ($permissionCourierImportUpload) { ?>
	<a class="<?php echo $currentRoute == "importupsagent" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('importupsagent') }}"><i style="color: #fff" class="fa fa-upload fa-2x"></i><span class="menuname">Upload Files</span></a>
<?php } ?>