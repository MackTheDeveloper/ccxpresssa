<?php $permissionForExport = App\User::checkPermission(['export_house_files_ups_master'], '', auth()->user()->id); ?>
<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="8">
        <table class="display nowrap" style="width:100%">
            <tbody>

                <tr class="childrw">
                    <td colspan="13" style="font-weight: bold;font-size: 14px;color: #22a659;">House Files <?php if ($permissionForExport) { ?><button data-masterupsid="<?php echo $masterUpsId; ?>" style="margin-left:30px" class="btn btn-success clsExportToExcel"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i></span> Export</button><?php } ?></td>
                </tr>
                <tr class="childrw">
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>File Number</th>
                    <th>AWB Tracking</th>
                    <th>Shipment Number</th>
                    <th>Billing Party</th>
                    <th>File Status</th>
                    <th>Shipper</th>
                    <th>Consignee</th>
                    <th>Package Type</th>
                    <th>Weight</th>
                    <th>Invoice Numbers</th>
                    <th>Origin</th>
                </tr>

                <?php $i = 1; ?>
                @foreach ($packageData as $packageData)
                <?php
                $invoiceAmounts = app('App\Expense')::getUpsInvoicesOfFileInExpand($packageData->id, 'forExpandedFiles', '');
                $dataBillingParty = app('App\Clients')->getClientData($packageData->billing_party);
                if ($packageData->package_type == 'LTR')
                    $packageType = 'Letter';
                else if ($packageData->package_type == 'DOC')
                    $packageType = 'Document';
                else
                    $packageType = 'Package';
                ?>
                <tr class="edit-row childrw" data-editlink="{{ route('viewdetailsups',[$packageData->id]) }}" id="<?php echo $packageData->id; ?>" data-masterid="{{$packageData->master_ups_id}}">
                    <td><input type="checkbox" name="singlecheckbox" class="singlecheckbox singlecheckbox-{{$packageData->master_ups_id}}" id="{{$packageData->id}}" value="{{$packageData->id}}" /></td>
                    <td><?php echo $packageData->file_number; ?></td>
                    <td><?php echo $packageData->awb_number;  ?></td>
                    <td><?php echo !empty($packageData->shipment_number) ? $packageData->shipment_number : '-';  ?></td>
                    <td><?php echo !empty($dataBillingParty->company_name) ? $dataBillingParty->company_name : "-";  ?></td>
                    <td><?php echo  isset(Config::get('app.ups_new_scan_status')[!empty($packageData->ups_scan_status) ? $packageData->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($packageData->ups_scan_status) ? $packageData->ups_scan_status : '-'] : '-';  ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($packageData->shipper_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($packageData->consignee_name);
                        echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php echo $packageType;  ?></td>
                    <td><?php echo !empty($packageData->weight) ? $packageData->weight . ' ' . $packageData->unit : '-'; ?></td>
                    <td><?php echo $invoiceAmounts; ?></td>
                    <td><?php echo $packageData->origin; ?></td>

                </tr>
                <?php $i++; ?>
                @endforeach
            </tbody>
        </table>
    </td>
</tr>