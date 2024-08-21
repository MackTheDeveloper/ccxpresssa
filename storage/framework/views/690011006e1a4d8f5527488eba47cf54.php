<?php
$permissionCourierImportAdd = App\User::checkPermission(['add_courier_import'], '', auth()->user()->id);
$permissionUpsMasterAdd = App\User::checkPermission(['add_ups_master'], '', auth()->user()->id);
$permissionUpsMasterListing = App\User::checkPermission(['listing_ups_master'], '', auth()->user()->id);
$permissionCourierImportUpload = App\User::checkPermission(['upload_courier_import'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionUpsMasterAdd) { ?>
	<a class="<?php echo $currentRoute == "createupsmaster" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createupsmaster')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS Master File</span></a>
<?php } ?>
<?php if ($permissionCourierImportAdd) { ?>
	<a class="<?php echo $currentRoute == "createups" ? "activeMenu" : ""; ?>" href="<?php echo e(route('createups')); ?>"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS House File</span></a>
<?php } ?>
<?php if ($permissionUpsMasterListing) { ?>
	<a class="<?php echo $currentRoute == "ups-master" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('ups-master')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS Master Files Listing</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "ups" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('ups')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">UPS House Files Listing</span></a>
<?php if ($permissionCourierImportUpload) { ?>
	<a class="<?php echo $currentRoute == "importups" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="<?php echo e(route('importups')); ?>"><i style="color: #fff" class="fa fa-upload fa-2x"></i><span class="menuname">Upload UPS Files</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "viewall" ? "activeMenu" : ""; ?>" style="margin-left: 10px;display: none" href="<?php echo e(route('viewall')); ?>"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">View All Files</span></a><?php /**PATH /var/www/html/php/cargo/resources/views/menus/ups-import.blade.php ENDPATH**/ ?>