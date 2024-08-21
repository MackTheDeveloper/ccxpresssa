<?php 
    $permissionCountriesAdd = App\User::checkPermission(['add_countries'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<a class="<?php echo $currentRoute == "createcountry" ? "activeMenu" : ""; ?>" href="{{ route('createcountry') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Country</span></a>
<a class="<?php echo $currentRoute == "countries" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('countries') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Countries</span></a>