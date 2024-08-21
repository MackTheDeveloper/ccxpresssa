<?php

use Illuminate\Http\Request;

$permissionClientsAdd = App\User::checkPermission(['add_clients'], '', auth()->user()->id);
$permissionBillingPartyAdd = App\User::checkPermission(['add_billing_party'], '', auth()->user()->id);
$permissionClientsContactsAdd = App\User::checkPermission(['add_client_contacts'], '', auth()->user()->id);
$permissionClientsContactsListing = App\User::checkPermission(['listing_client_contacts'], '', auth()->user()->id);

$currentRoute = Route::currentRouteName();
$param = Route::current()->parameters();

$parameter = '';
if (!empty($param)) {
    if (!empty($param['flag']))
        $parameter = $param['flag'];
    else
        $parameter = '';
}

?>
<?php if ($permissionBillingPartyAdd) { ?>
    <a class="<?php echo ($currentRoute == "createclient" && $parameter == 'B') ? "activeMenu" : ""; ?>" href="{{ route('createclient','B') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Billing Party</span></a>
<?php } ?>
<a class="<?php echo ($currentRoute == "clients" && $parameter == 'B') ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('clients','B') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Billing Parties</span></a>

<?php if ($permissionClientsAdd) { ?>
    <a class="<?php echo ($currentRoute == "createclient" && $parameter == 'O') ? "activeMenu" : ""; ?>" href="{{ route('createclient','O') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Client</span></a>
<?php } ?>

<a class="<?php echo ($currentRoute == "clients" && $parameter == 'O') ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('clients','O') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Clients</span></a>

<?php if ($permissionClientsContactsAdd) { ?>
    <a class="<?php echo $currentRoute == "createclientcontact" ? "activeMenu" : ""; ?>" href="{{ route('createclientcontact') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Contact</span></a>
<?php } ?>
<?php if ($permissionClientsContactsListing) { ?>
    <a class="<?php echo $currentRoute == "clientcontacts" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('clientcontacts') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Contacts</span></a>
<?php } ?>