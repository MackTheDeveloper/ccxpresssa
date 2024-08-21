
<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="14" style="font-weight: bold;font-size: 14px;color: #22a659;">Expense Detail - <?php echo $dataCargo->file_number;  ?></td>
</tr>
            <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
                <th></th>
                <th colspan="1">Sr No.</th>
                <th colspan="2">Cost A/C</th>
                <th colspan="2">Description</th>
                <th>Amount</th>
                <th colspan="1">Paid To</th>
                <th colspan="6">Action</th>
                
            </tr>

            <?php $i = 1; ?>
            @foreach ($packageData as $packageData)
                <tr class="childrw child-<?php echo $rowId; ?>">
                    <td></td>
                    <td colspan="1"><?php echo $i; ?></td>
                    <td colspan="2"><?php $costData = app('App\Costs')->getCostData($packageData->expense_type); 
                        echo !empty($costData->cost_name) ? $costData->cost_name : "-";
                    ?></td>
                    <td colspan="2">{{$packageData->description}}</td>
                    <td class="alignright">{{number_format($packageData->amount,2)}}</td>
                    <td colspan="1"><?php $dataUser = app('App\Vendors')->getVendorData($packageData->paid_to); 
                        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?>
                    </td>
                    <td colspan="6">
                        <div class='dropdown'>
                            <?php 
                            $delete =  route('deleteexpense',$packageData->id);
                            ?>
                            <a style="font-size: 14px;" class="delete-record-expense" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        </div>
                    </td>
                    
                </tr>
                <?php $i++; ?>
            @endforeach
            
    







