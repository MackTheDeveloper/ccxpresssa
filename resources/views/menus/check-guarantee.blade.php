<?php
$permissionAdd = App\User::checkPermission(['add_guarantee_check'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionAdd) { ?>
    <a class="<?php echo $currentRoute == "addCheckGuarantee" ? "activeMenu" : ""; ?>" href="{{ route('addCheckGuarantee') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Guarantee Check</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "check-guarantee" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('check-guarantee') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Guarantee Checks</span></a>