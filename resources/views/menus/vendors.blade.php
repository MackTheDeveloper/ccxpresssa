<?php 
    $permissionVendorsAdd = App\User::checkPermission(['add_vendors'],'',auth()->user()->id); 
    $permissionClientsContactsAdd = App\User::checkPermission(['add_vendor_contacts'],'',auth()->user()->id); 
    $permissionClientsContactsListing = App\User::checkPermission(['listing_vendor_contacts'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionVendorsAdd) { ?>
<a class="<?php echo $currentRoute == "createvendor" ? "activeMenu" : ""; ?>" href="{{ route('createvendor') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Vendor</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "vendors" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('vendors') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Vendors</span></a>

<?php if($permissionClientsContactsAdd) { ?>
<a class="<?php echo $currentRoute == "createvendorcontact" ? "activeMenu" : ""; ?>" href="{{ route('createvendorcontact') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Contact</span></a>
<?php } ?>
<?php if($permissionClientsContactsListing) { ?>
<a class="<?php echo $currentRoute == "vendorcontacts" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('vendorcontacts') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Contacts</span></a>
<?php } ?>