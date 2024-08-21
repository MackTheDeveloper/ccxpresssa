<?php

use App\Currency;

$i = 1; ?>
@foreach ($dataInvoice as $dinfo)
<tr id="<?php echo $dinfo->id; ?>" data-trid="<?php echo $dinfo->id; ?>">
    <td>
        <input type="checkbox" data-currency="<?php echo $dinfo->currency; ?>" name="singlecheckbox" class="singlecheckbox" id="<?php echo $dinfo->id ?>" value="<?php echo $dinfo->id ?>" />
    </td>
    <td>
        <?php echo 'Invoice #' . $dinfo->bill_no; ?>
    </td>
    <td>
        <?php echo !empty($dinfo->shipment_number) ? $dinfo->shipment_number : ''; ?>
    </td>
    <td>
        <?php $dataUser = app('App\Clients')->getClientData($dinfo->bill_to);
        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-"; ?>
    </td>
    <td>
        <?php echo  date('d-m-Y', strtotime($dinfo->date)); ?>
    </td>
    <td><?php $dataCurrency = Currency::getData($dinfo->currency);
        echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
        </td>
    <td class="alignright">
        <?php echo number_format($dinfo->total, 2);; ?>
    </td>
    <td class="due-amt alignright" id="due-amt-<?php echo $dinfo->id ?>">
        <?php echo number_format(($dinfo->total - $dinfo->credits), 2); ?>
    </td>
    <td class="paymentCredit-fill alignright">
        <input style="text-align:right;width: 200px;float: right;" type="text" id="paymentCredit-<?php echo $dinfo->id ?>" class="form-control paymentCredit" name="paymentCredit[<?php echo $dinfo->id; ?>]" value="" disabled>
    </td>
    <td class="due-amt-fill alignright">
        <input style="text-align:right;width: 200px;float: right;" type="text" id="due-amt-fill-<?php echo $dinfo->id ?>" class="form-control input-due-amt" name="payment[<?php echo $dinfo->id; ?>]" value="" disabled>
    </td>
    <td class="alignright">
        <input style="text-align:right;width: 200px;float: right;" type="text" class="form-control exchange_amount" id="exchange_amount-<?php echo $dinfo->id; ?>" name="exchange_amount[<?php echo $dinfo->id; ?>]" value="" disabled>
    </td>
</tr>
<?php $i++; ?>
@endforeach