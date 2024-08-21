
<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="11" style="font-weight: bold;font-size: 14px;color: #22a659;">Expense Detail - <?php echo $dataCargo->file_number;  ?></td>
</tr>
            <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
                <th></th>
                <th colspan="1">Sr No.</th>
                <th colspan="2">Cost A/C</th>
                <th colspan="3">Description</th>
                <th>Amount</th>
                <th colspan="3">Paid To</th>
                
                
            </tr>

            <?php $i = 1; ?>
            @foreach ($packageData as $packageData)
                <tr class="childrw child-<?php echo $rowId; ?>">
                    <td></td>
                    <td colspan="1"><?php echo $i; ?></td>
                    <td colspan="2"><?php $costData = app('App\Costs')->getCostData($packageData->expense_type); 
                        echo !empty($costData->cost_name) ? $costData->cost_name : "-";
                    ?></td>
                    <td colspan="3">{{$packageData->description}}</td>
                    <td class="alignright">{{$packageData->amount}}</td>
                    <td colspan="3"><?php $dataUser = app('App\Vendors')->getVendorData($packageData->paid_to); 
                        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?>
                    </td>
                    
                </tr>
                <?php $i++; ?>
            @endforeach
            
    







