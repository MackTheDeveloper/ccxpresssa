<?php
$permissionCcpackMasterAdd = App\User::checkPermission(['add_ccpack_master'], '', auth()->user()->id);
$permissionCCpackAdd = App\User::checkPermission(['add_ccpack'], '', auth()->user()->id);
$permissionCCpackListing = App\User::checkPermission(['listing_ccpack'], '', auth()->user()->id);
$permissionCcpackMasterListing = App\User::checkPermission(['listing_ccpack_master'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionCcpackMasterAdd) { ?>
	<a class="<?php echo $currentRoute == "createccpackmaster" ? "activeMenu" : ""; ?>" href="{{ route('createccpackmaster') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack Master File</span></a>
<?php } ?>
<?php if ($permissionCCpackAdd && checkloggedinuserdata() != 'Agent') { ?>
	<a class="<?php echo $currentRoute == "createccpack" ? "activeMenu" : ""; ?>" href="{{ route('createccpack') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack House File</span></a>
<?php } ?>
<?php if ($permissionCcpackMasterListing) { ?>
	<a class="<?php echo $currentRoute == "ccpack-master" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ccpack-master') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack Master Files Listing</span></a>
<?php } ?>
<?php if ($permissionCCpackListing) { ?>
	<a class="<?php echo $currentRoute == "ccpack" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('ccpack') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">CCPack House Files Listing</span></a>
<?php } ?>