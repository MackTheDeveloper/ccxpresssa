
<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="7" style="font-weight: bold;font-size: 14px;color: #22a659;text-align: center;">Expense Detail - <?php echo $dataUps->file_number;  ?></td>
</tr>
            <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
                <th></th>
                <th colspan="1">Sr No.</th>
                <th colspan="2">Description</th>
                <th colspan="2">Amount</th>
                <th>Action</th>
                
            </tr>

            <?php $i = 1; ?>
            @foreach ($packageData as $packageData)
                <tr class="childrw child-<?php echo $rowId; ?>">
                    <td></td>
                    <td colspan="1"><?php echo $i; ?></td>
                    <td colspan="2">{{$packageData->description}}</td>
                    <td class="alignright" colspan="2">{{$packageData->amount}}</td>
                    <td>
                        <div class='dropdown'>
                            <?php 
                            $delete =  route('deletecustomexpnese',$packageData->id);
                            ?>
                            <a style="font-size: 14px;" class="delete-record-expense" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        </div>
                    </td>
                    
                </tr>
                <?php $i++; ?>
            @endforeach
            
    







