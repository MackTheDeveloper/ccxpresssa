<?php

use Illuminate\Http\Request;

$permissionListing = App\User::checkPermission(['import_manifestes'], '', auth()->user()->id);
$permissionImport = App\User::checkPermission(['listing_manifestes'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
?>
<?php if ($permissionListing) { ?>
    <a class="<?php echo $currentRoute == "manifests" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('manifests') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Manifests</span></a>
<?php } ?>
<?php if ($permissionImport) { ?>
    <a class="<?php echo $currentRoute == "importmanifests" ? "activeMenu" : ""; ?>" href="{{ route('importmanifests') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Import Manifests</span></a>
<?php } ?>