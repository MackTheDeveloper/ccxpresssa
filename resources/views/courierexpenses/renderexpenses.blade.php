
<tr class="childrw">
    <td colspan="10" style="font-weight: bold;font-size: 14px;color: #22a659;text-align: center;">Expense Detail - <?php echo $dataCargo->file_number;  ?></td>
</tr>
            <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
                <th></th>
                <th colspan="1">Sr No.</th>
                <th colspan="2">Cost A/C</th>
                <th colspan="2">Description</th>
                <th>Amount</th>
                <th>Paid To</th>
                <th>Cash A/C</th>
                <th>Action</th>
                
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
                    <td>{{$packageData->amount}}</td>
                    <td><?php $dataUser = app('App\User')->getUserName($packageData->paid_to); 
                        echo !empty($dataUser->name) ? $dataUser->name : "-";?>
                    </td>
                    <td>{{$packageData->cash_credit_account}}</td>
                    <td>
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
            
    







