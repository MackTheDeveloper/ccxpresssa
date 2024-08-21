<?php 
    $currentRoute = Route::currentRouteName();
?>
<a class="<?php echo $currentRoute == "createfdcharges" ? "activeMenu" : ""; ?>" href="{{ route('createfdcharges') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Free Domicile Charge</span></a>
<a class="<?php echo $currentRoute == "fdcharges" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('fdcharges') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Free Domicile Charges</span></a>