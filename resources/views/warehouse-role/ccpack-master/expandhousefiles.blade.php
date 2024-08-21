<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="8" style="font-weight: bold;font-size: 14px;color: #22a659;">House Files</td>
</tr>
<tr class="childrw childrwheader child-<?php echo $rowId; ?>">
    <th><input type="checkbox" id="selectAll"></th>
    <th colspan="1">File Number</th>
    <th colspan="1">AWB Tracking</th>
    <th colspan="1">Billing Party</th>
    <th colspan="1">File Status</th>
    <th colspan="1">Shipper</th>
    <th colspan="1">Consignee</th>
    <th colspan="1">Weight</th>
</tr>

<?php $i = 1; ?>
@foreach ($packageData as $packageData)
<?php $dataBillingParty = app('App\Clients')->getClientData($packageData->billing_party); ?>
<tr class="edit-row childrw child-<?php echo $rowId; ?>" data-editlink="{{ route('viewdetailsccpack',[$packageData->id]) }}" id="<?php echo $packageData->id; ?>" data-masterid="{{$packageData->master_ccpack_id}}">
    <td colspan="1"><input type="checkbox" name="singlecheckbox" class="singlecheckbox singlecheckbox-{{$packageData->master_ccpack_id}}" id="{{$packageData->id}}" value="{{$packageData->id}}" /></td>
    <td colspan="1"><?php echo $packageData->file_number; ?></td>
    <td colspan="1"><?php echo $packageData->awb_number;  ?></td>
    <td colspan="1"><?php echo !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-";  ?></td>
    <td colspan="1"><?php echo isset(Config::get('app.ups_new_scan_status')[!empty($packageData->ccpack_scan_status) ? $packageData->ccpack_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($packageData->ccpack_scan_status) ? $packageData->ccpack_scan_status : '-'] : '-';  ?></td>
    <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->shipper_name);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
    <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->consignee);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
    <td colspan="1"><?php echo $packageData->weight;  ?></td>

</tr>
<?php $i++; ?>
@endforeach