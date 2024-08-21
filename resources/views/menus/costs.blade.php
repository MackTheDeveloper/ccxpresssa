<?php 
    $permissionCostsAdd = App\User::checkPermission(['add_costs'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionCostsAdd) { ?>
<a class="<?php echo $currentRoute == "createcost" ? "activeMenu" : ""; ?>" href="{{ route('createcost') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add File Cost</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "costs" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('costs') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">File Costs</span></a>