<?php  $currentRoute = Route::currentRouteName();?>

<a class="<?php echo $currentRoute == "filestatus" ? "activeMenu" : ""; ?>" href="{{ route('filestatus') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add File Progress Status</span></a>

<a class="<?php echo $currentRoute == "filestatusindex" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('filestatusindex') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">File Progress Status Listing</span></a>