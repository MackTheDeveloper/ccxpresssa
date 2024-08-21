<?php
$permissionAeropostMasterAdd = App\User::checkPermission(['add_aeropost_master'], '', auth()->user()->id);
$permissionAeropostAdd = App\User::checkPermission(['add_aeropost'], '', auth()->user()->id);
$permissionAeropostImport = App\User::checkPermission(['upload_aeropost'], '', auth()->user()->id);
$permissionListing = App\User::checkPermission(['listing_aeropost'], '', auth()->user()->id);
$permissionAeropostMasterListing = App\User::checkPermission(['listing_aeropost_master'], '', auth()->user()->id);


$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionAeropostMasterAdd) { ?>
	<a class="<?php echo $currentRoute == "createaeropostmaster" ? "activeMenu" : ""; ?>" href="{{ route('createaeropostmaster') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost Master File</span></a>
<?php } ?>
<?php if ($permissionAeropostAdd && checkloggedinuserdata() != 'Agent') { ?>
	<a class="<?php echo $currentRoute == "createaeropost" ? "activeMenu" : ""; ?>" href="{{ route('createaeropost') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost House File</span></a>
<?php } ?>
<?php if ($permissionAeropostMasterListing) { ?>
	<a class="<?php echo $currentRoute == "aeropost-master" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('aeropost-master') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost Master Files Listing</span></a>
<?php } ?>
<?php if ($permissionListing) { ?>
	<a class="<?php echo $currentRoute == "aeroposts" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('aeroposts') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost House Files Listing</span></a>
<?php } ?>
<?php if ($permissionAeropostImport && checkloggedinuserdata() != 'Agent') { ?>
	<a class="<?php echo $currentRoute == "importaeropost" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('importaeropost') }}"><i style="color: #fff" class="fa fa-upload fa-2x"></i><span class="menuname">Upload Aeropost House File</span></a>
<?php } ?>