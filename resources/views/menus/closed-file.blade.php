<?php
$permissionCloseFile = App\User::checkPermission(['close_file'], '', auth()->user()->id);
$permissionCloseFileListing = App\User::checkPermission(['listing_close_file'], '', auth()->user()->id);


$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionCloseFile) { ?>
    <a class="<?php echo $currentRoute == "closefiles" ? "activeMenu" : ""; ?>" href="{{ route('closefiles') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Close File</span></a>
<?php }
if ($permissionCloseFileListing) { ?>
    <a class="<?php echo $currentRoute == "closefileslisting" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('closefileslisting') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Closed Files Listing</span></a>
<?php } ?>