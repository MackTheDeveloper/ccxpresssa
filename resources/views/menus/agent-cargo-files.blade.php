<?php
$permissionCargoHAWBListing = App\User::checkPermission(['listing_cargo_hawb'], '', auth()->user()->id); 
$currentRoute = Route::currentRouteName();
?>
<a class="<?php echo $currentRoute == "agentcargoall" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('agentcargoall') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Cargo Files Listing</span></a>
<?php if ($permissionCargoHAWBListing) { ?>
    <a class="<?php echo $currentRoute == "hawbfiles" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('hawbfiles') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">House AWB Files Listing</span></a>
<?php } ?>