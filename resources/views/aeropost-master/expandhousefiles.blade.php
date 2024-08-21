<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="8" style="font-weight: bold;font-size: 14px;color: #22a659;">House Files</td>
</tr>
<tr class="childrw childrwheader child-<?php echo $rowId; ?>">
    <th colspan="1">Sr No.</th>
    <th colspan="1">File Number</th>
    <th colspan="1">AWB Tracking</th>
    <th colspan="1">Billing Party</th>
    <th colspan="1">File Status</th>
    <th colspan="1">Shipper</th>
    <th colspan="1">Consignee</th>
    <th colspan="1">Freight</th>
</tr>

<?php $i = 1; ?>
@foreach ($packageData as $packageData)
<?php $dataBillingParty = app('App\Clients')->getClientData($packageData->billing_party); ?>
<tr class="edit-row childrw child-<?php echo $rowId; ?>" data-editlink="{{ route('viewdetailsaeropost',[$packageData->id]) }}" id="<?php echo $packageData->id; ?>">
    <td colspan="1"><?php echo $i; ?></td>
    <td colspan="1"><?php echo $packageData->file_number; ?></td>
    <td colspan="1"><?php echo $packageData->tracking_no;  ?></td>
    <td colspan="1"><?php echo !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-";  ?></td>
    <td colspan="1"><?php echo isset(Config::get('app.ups_new_scan_status')[!empty($packageData->aeropost_scan_status) ? $packageData->aeropost_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($packageData->aeropost_scan_status) ? $packageData->aeropost_scan_status : '-'] : '-'; ?></td>
    <td colspan="1"><?php echo !empty($packageData->from_location) ? $packageData->from_location : '-'; ?></td>
    <td colspan="1"><?php $data = app('App\Clients')->getClientData($packageData->consignee);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
    <td colspan="1"><?php echo $packageData->total_freight;  ?></td>

</tr>
<?php $i++; ?>
@endforeach