<?php 
$checkPermissionCreateCargoImport = App\User::checkPermission(['import_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoImport = App\User::checkPermission(['update_import'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoImport = App\User::checkPermission(['delete_import'],'',auth()->user()->id);
        $checkPermissionImportIndexCargo = App\User::checkPermission(['import_cargo_index'],'',auth()->user()->id);     
        $checkPermissionAddImportExpenseCargo = App\User::checkPermission(['add_cargo_import_expense'],'',auth()->user()->id);


        $checkPermissionCreateCargoExport = App\User::checkPermission(['export_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoExport = App\User::checkPermission(['update_export'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoExport = App\User::checkPermission(['delete_export'],'',auth()->user()->id);
        $checkPermissionExportIndexCargo = App\User::checkPermission(['export_cargo_index'],'',auth()->user()->id);  
        $checkPermissionAddExportExpenseCargo = App\User::checkPermission(['add_cargo_export_expense'],'',auth()->user()->id);


        $checkPermissionCreateCargoLocale = App\User::checkPermission(['locale_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoLocale = App\User::checkPermission(['update_locale'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoLocale = App\User::checkPermission(['delete_locale'],'',auth()->user()->id);
        $checkPermissionLocaleIndexCargo = App\User::checkPermission(['locale_cargo_index'],'',auth()->user()->id);  
        $checkPermissionAddLocaleExpenseCargo = App\User::checkPermission(['add_cargo_locale_expense'],'',auth()->user()->id);



        ?>
        <?php if($checkPermissionImportIndexCargo) { ?>
        <li class="widemenu">
            <a href="{{ route('cargoimports') }}">Import Shipment</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionImportIndexCargo) { ?>
        <li class="widemenu <?php echo ($id == 1 && $flag == 'listing') ? 'active' : ''; ?>">
            <a href="{{ route('cargoimports') }}">Import Shipment Listing</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionCreateCargoImport) { ?>
        <li class="widemenu <?php echo ($id == 1 && $flag == 'add') ? 'active' : ''; ?>">
            <a href="{{ route('cargoimport','1') }}">Add Manually</a>
        </li>
        <?php } ?>

        <li class="widemenu">
            <a href="javascript:void(0)">------------------------------------</a>
        </li>

        <?php if($checkPermissionExportIndexCargo) { ?>
        <li class="widemenu">
            <a href="{{ route('cargoexports') }}">Export Shipment</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionExportIndexCargo) { ?>
        <li class="widemenu <?php echo ($id == 2 && $flag == 'listing') ? 'active' : ''; ?>">
            <a href="{{ route('cargoexports') }}">Export Shipment Listing</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionCreateCargoExport) { ?>
        <li class="widemenu <?php echo ($id == 2 && $flag == 'add') ? 'active' : ''; ?>">
            <a href="{{ route('cargoexport','2') }}">Add Manually</a>
        </li>
        <?php } ?>

        <li class="widemenu">
            <a href="javascript:void(0)">------------------------------------</a>
        </li>

        <?php if($checkPermissionLocaleIndexCargo) { ?>
        <li class="widemenu">
            <a href="{{ route('cargolocales') }}">Locale Shipment</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionLocaleIndexCargo) { ?>
        <li class="widemenu <?php echo ($id == 3 && $flag == 'listing') ? 'active' : ''; ?>">
            <a href="{{ route('cargolocales') }}">Locale Shipment Listing</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionCreateCargoExport) { ?>
        <li class="widemenu <?php echo ($id == 3 && $flag == 'add') ? 'active' : ''; ?>">
            <a href="{{ route('cargolocale','3') }}">Add Manually</a>
        </li>
        <?php } 
?>