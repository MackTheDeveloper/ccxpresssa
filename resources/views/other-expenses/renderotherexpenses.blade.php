<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="10" style="font-weight: bold;font-size: 14px;color: #22a659;text-align: left;">Administration Expense Detail</td>
</tr>
<tr class="childrw childrwheader child-<?php echo $rowId; ?>">
    <th></th>
    <th colspan="1">Sr No.</th>
    <th colspan="2">Description</th>
    <th>Amount</th>
    <th colspan="2">Paid To</th>
    <th colspan="3">Action</th>

</tr>

<?php $i = 1; ?>
@foreach ($packageData as $packageData)
<tr class="childrw child-<?php echo $rowId; ?>">
    <td></td>
    <td colspan="1"><?php echo $i; ?></td>
    <td colspan="2">{{$packageData->description}}</td>
    <td class="alignright">{{$packageData->amount}}</td>
    <td colspan="2"><?php $dataVendor = app('App\Vendors')->getVendorData($packageData->paid_to);
                    echo !empty($dataVendor->company_name) ? $dataVendor->company_name : "-"; ?>
    </td>
    <td colspan="3">
        <div class='dropdown'>
            <?php
            $delete =  route('deleteotherexpense', $packageData->id);
            ?>
            <a style="font-size: 14px;" class="delete-record-expense" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
        </div>
    </td>

</tr>
<?php $i++; ?>
@endforeach