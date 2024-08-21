<?php if ($flagCargo == 'agentcargo') { ?>
    <tr class="childrw child-<?php echo $rowId; ?>">
        <td colspan="8" style="font-weight: bold;font-size: 14px;color: #22a659;">House AWB Files</td>
    </tr>
    <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
        <th></th>
        <th colspan="1">Sr No.</th>
        <th colspan="1">File Number</th>
        <th colspan="2">House AWB No.</th>
        <th colspan="1">Consignee</th>
        <th colspan="1">Shipper</th>
        <th>Action</th>
    </tr>

    <?php $i = 1; ?>
    @foreach ($packageData as $packageData)
    <tr class="childrw child-<?php echo $rowId; ?>">
        <td></td>
        <td colspan="1"><?php echo $i; ?></td>
        <td colspan="1"><?php echo $packageData->file_number; ?></td>
        <td colspan="2"><?php echo $packageData->cargo_operation_type == '1' ? $packageData->hawb_hbl_no : $packageData->export_hawb_hbl_no;  ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->consignee_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->shipper_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td><a title="Click here to print" target="_blank" href="{{ route('printhawbfiles',[$packageData->id,$packageData->cargo_operation_type]) }}"><i class="fa fa-print"></i></a></td>
    </tr>
    <?php $i++; ?>
    @endforeach

<?php } else if ($flagCargo == 'warehousecargo') { ?>

    <tr class="childrw child-<?php echo $rowId; ?>">
        <td colspan="8" style="font-weight: bold;font-size: 14px;color: #22a659;">House AWB Files</td>
    </tr>
    <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
        <th colspan="1">Sr No.</th>
        <th colspan="1">File Number</th>
        <th colspan="1">House AWB No.</th>
        <th colspan="1">Consignee</th>
        <th colspan="1">Shipper</th>
        <th>Verification</th>
        <th>Custom Inspection</th>
        <th>Action</th>
    </tr>

    <?php $i = 1; ?>
    @foreach ($packageData as $packageData)
    <tr class="childrw child-<?php echo $rowId; ?>">
        <td colspan="1"><?php echo $i; ?></td>
        <td colspan="1"><?php echo $packageData->file_number; ?></td>
        <td colspan="1"><?php echo $packageData->cargo_operation_type == '1' ? $packageData->hawb_hbl_no : $packageData->export_hawb_hbl_no;  ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->consignee_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->shipper_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td><?php $btnClass = $packageData->verify_flag == '1' ? 'customButtonSuccess' : 'customButtonAlert'; ?><button class="customButtonInGrid <?php echo $btnClass; ?>" data-hawbid="{{$packageData->id}}" data-flag="verifyFlag" value="{{$packageData->verify_flag}}"><?php echo Config::get('app.verifyFileWarehouse')[$packageData->verify_flag]; ?></button></td>
        <td><?php $btnClass = $packageData->inspection_flag == '1' ? 'customButtonSuccess' : 'customButtonAlert'; ?><button class="customButtonInGrid <?php echo $btnClass; ?>" data-hawbid="{{$packageData->id}}" data-flag="inspectionFlag" value="{{$packageData->inspection_flag}}"><?php echo Config::get('app.inspectionFileWarehouse')[$packageData->inspection_flag]; ?></button></td>
        <td><a title="Click here to print" target="_blank" href="{{ route('printhawbfiles',[$packageData->id,$packageData->cargo_operation_type]) }}"><i class="fa fa-print"></i></a></td>
    </tr>
    <?php $i++; ?>
    @endforeach
<?php } else if ($flagCargo == 'cashiercargo') { ?>
    <tr class="childrw child-<?php echo $rowId; ?>">
        <td colspan="9" style="font-weight: bold;font-size: 14px;color: #22a659;">House AWB Files</td>
    </tr>
    <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
        <th></th>
        <th colspan="1">Sr No.</th>
        <th colspan="1">File Number</th>
        <th colspan="1">House AWB No.</th>
        <th colspan="1">Consignee</th>
        <th colspan="1">Shipper</th>
        <th>Verification</th>
        <th>Custom Inspection</th>
        <th>Action</th>
    </tr>

    <?php $i = 1; ?>
    @foreach ($packageData as $packageData)
    <tr class="childrw child-<?php echo $rowId; ?>">
        <td></td>
        <td colspan="1"><?php echo $i; ?></td>
        <td colspan="1"><?php echo $packageData->file_number; ?></td>
        <td colspan="1"><?php echo $packageData->cargo_operation_type == '1' ? $packageData->hawb_hbl_no : $packageData->export_hawb_hbl_no;  ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->consignee_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->shipper_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td><?php echo Config::get('app.verifyFileWarehouse')[$packageData->verify_flag]; ?></td>
        <td><?php echo Config::get('app.inspectionFileWarehouse')[$packageData->inspection_flag]; ?></td>
        <td>
            <?php
            $cargoData =  app('App\Cargo')::getCargoData($packageData->cargo_id);
            if ($cargoData->warehouse_status == '3') { ?>
                <a href="{{ route('createcashierwarehouseinvoicesoffile',[$packageData->cargo_id,$packageData->id]) }}">Add Invoice</a>
            <?php } else {
                echo "";
            } ?>
            <a title="Click here to print" target="_blank" href="{{ route('printhawbfiles',[$packageData->id,$packageData->cargo_operation_type]) }}"><i class="fa fa-print"></i></a>
        </td>
    </tr>
    <?php $i++; ?>
    @endforeach

<?php } else { ?>

    <tr class="childrw child-<?php echo $rowId; ?>">
        <td colspan="8" style="font-weight: bold;font-size: 14px;color: #22a659;">House AWB Files</td>
    </tr>
    <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
        <th></th>
        <th colspan="1">Sr No.</th>
        <th colspan="1">File Number</th>
        <th colspan="2">House AWB No.</th>
        <th colspan="1">Consignee</th>
        <th colspan="1">Shipper</th>
        <th>Action</th>
    </tr>

    <?php $i = 1; ?>
    @foreach ($packageData as $packageData)
    <tr class="edit-row childrw child-<?php echo $rowId; ?>" data-editlink="{{ route('edithawbfile',[$packageData->id]) }}" id="<?php echo $packageData->id; ?>">
        <td></td>
        <td colspan="1"><?php echo $i; ?></td>
        <td colspan="1"><?php echo $packageData->file_number; ?></td>
        <td colspan="2"><?php echo $packageData->cargo_operation_type == '1' ? $packageData->hawb_hbl_no : $packageData->export_hawb_hbl_no;  ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->consignee_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->shipper_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
        <td><a title="Click here to print" target="_blank" href="{{ route('printhawbfiles',[$packageData->id,$packageData->cargo_operation_type]) }}"><i class="fa fa-print"></i></a></td>
    </tr>
    <?php $i++; ?>
    @endforeach
<?php } ?>