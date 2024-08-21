<?php
$permissionAdd = App\User::checkPermission(['add_old_invoices'], '', auth()->user()->id);
$permissionListing = App\User::checkPermission(['listing_old_invoices'], '', auth()->user()->id);


$currentRoute = Route::currentRouteName();
?>

<?php if ($permissionListing) { ?>
    <a class="<?php echo $currentRoute == "oldinvoices" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('oldinvoices') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Old Invoices</span></a>
<?php } ?>
<?php if ($permissionAdd) { ?>
    <a class="<?php echo $currentRoute == "createinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createinvoice',[0,'old']) }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cargo Old Invoice</span></a>

    <a style="display: none" class="<?php echo $currentRoute == "createhousefileinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createhousefileinvoice',['cargo','old']) }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Cargo House Old Invoice</span></a>

    <a class="<?php echo $currentRoute == "createupsinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createupsinvoice',[0,'old']) }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add UPS Old Invoice</span></a>

    <a class="<?php echo $currentRoute == "createaeropostinvoice" ? "activeMenu" : ""; ?>" href="{{ route('createaeropostinvoice',[0,'old']) }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Aeropost Old Invoice</span></a>

    <a class="<?php echo $currentRoute == "createccpackinvoices" ? "activeMenu" : ""; ?>" href="{{ route('createccpackinvoices',[0,'old']) }}"><i style="color: #fff;margin-top: 10%" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add CCPack Old Invoice</span></a>
<?php } ?>