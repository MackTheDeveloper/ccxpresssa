<?php  $currentRoute = Route::currentRouteName();?>

<a class="<?php echo $currentRoute == "upscommission" ? "activeMenu" : ""; ?>" href="{{ route('upscommission') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Ups Commission</span></a>

<a class="<?php echo $currentRoute == "upscommissiondetails" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('upscommissiondetails') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Ups Commission Details</span></a>

<?php $checkExist = DB::table('aeropost_commission')->count(); if($checkExist == 0) { ?>
<a class="<?php echo $currentRoute == "addaeropostcommission" ? "activeMenu" : ""; ?>" href="{{ route('addaeropostcommission') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost Commission</span></a>
<?php } ?>

<a class="<?php echo $currentRoute == "aeropostcommission" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('aeropostcommission') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Aeropost Commission</span></a>