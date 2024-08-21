<?php

use App\Currency;
?>
<table id="example" class="display" style="width:100%">
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Invoice No.</th>
            <th>Amount</th>
            <th>Open Amount</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($allDue as $items)
        <tr>
            <td><?php echo date('d-m-Y', strtotime($items->date)) ?></td>
            <td>{{'#'.$items->fileNumber.', '.$items->trackingNumber}}</td>
            <td>{{'#'.$items->bill_no}}</td>
            <td class="alignright">{{$items->totalAmount}}</td>
            <td class="alignright">{{$items->totalDue}}</td>
            <td>
                <div class='dropdown'>
                    <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                    <ul class='dropdown-menu' style='left:auto;'>
                        <li>
                            <?php if (!empty($items->modeuleCargoId)) { ?>
                                <a target="_blank" href="{{ route('addinvoicepayment',[$items->modeuleCargoId,$items->invoiceId,0]) }}">Add Payment</a>
                            <?php } else if (!empty($items->modeuleUpsId)) { ?>
                                <a target="_blank" href="{{ route('addupsinvoicepayment',[$items->modeuleUpsId,$items->invoiceId,0]) }}">Add Payment</a>
                            <?php } else if (!empty($items->modeuleAeropostId)) { ?>
                                <a target="_blank" href="{{ route('addaeropostinvoicepayment',[$items->modeuleAeropostId,$items->invoiceId,0]) }}">Add Payment</a>
                            <?php } else if (!empty($items->modeuleCcpackId)) { ?>
                                <a target="_blank" href="{{ route('addccpackinvoicepayment',[$items->modeuleCcpackId,$items->invoiceId,0]) }}">Add Payment</a>
                            <?php } ?>
                        </li>

                    </ul>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>



<script type="text/javascript">
    $(document).ready(function() {
        $('#example').DataTable({
            'stateSave': true,
            "ordering": false
        });


    })
</script>