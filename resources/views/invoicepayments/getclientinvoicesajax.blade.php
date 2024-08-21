<?php

use App\Currency;

$i = 1; ?>
@foreach ($dataInvoice as $dinfo)
<tr id="<?php echo $dinfo->id; ?>" data-trid="<?php echo $dinfo->id; ?>">
    <td><input type="checkbox" data-currency="<?php echo $dinfo->currency; ?>" name="singlecheckbox" class="singlecheckbox" id="<?php echo $dinfo->id ?>" value="<?php echo $dinfo->id ?>" /></td>
    <td><?php echo 'Invoice #' . $dinfo->bill_no; ?></td>
    <?php if ($flagModule == 'UPS') { ?><td><?php echo !empty($dinfo->shipment_number) ? $dinfo->shipment_number : '-'; ?></td><?php } ?>
    <?php if ($flagModule == 'Aeropost') { ?><td><?php echo $dinfo->tracking_no; ?></td><?php } ?>
    <td><?php $dataUser = app('App\Clients')->getClientData($dinfo->bill_to);
        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-"; ?></td>
    <td><?php echo  date('d-m-Y', strtotime($dinfo->date)); ?></td>
    <td><?php $dataCurrency = Currency::getData($dinfo->currency);
        echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?></td>
    <td class="alignright"><?php echo number_format($dinfo->total, 2);; ?></td>
    <td class="due-amt alignright" id="due-amt-<?php echo $dinfo->id ?>"><?php echo number_format(($dinfo->total - $dinfo->credits), 2); ?></td>
    <td class="paymentCredit-fill alignright"><input style="text-align:right;width: 200px;float: right;" type="text" id="paymentCredit-<?php echo $dinfo->id ?>" class="form-control paymentCredit" name="paymentCredit[<?php echo $dinfo->id; ?>]" value=""></td>
    <td class="due-amt-fill alignright"><input style="text-align:right;width: 200px;float: right;" type="text" id="due-amt-fill-<?php echo $dinfo->id ?>" class="form-control input-due-amt" name="payment[<?php echo $dinfo->id; ?>]" value=""></td>
    <td class="alignright"><input style="text-align:right;width: 200px;float: right;" type="text" class="form-control exchange_amount" id="exchange_amount-<?php echo $dinfo->id; ?>" name="exchange_amount[<?php echo $dinfo->id; ?>]" value=""></td>
</tr>
<?php if (isset($dinfo->typeFlag) && !empty($dinfo->typeFlag)) { ?>
    <input type="hidden" name="courierorcargo[<?php echo $dinfo->id; ?>]" value="<?php echo $dinfo->typeFlag; ?>">
    <?php if ($dinfo->typeFlag == 'Cargo') { ?>
        <input type="hidden" name="cargoInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'Cargo Housefile') { ?>
        <input type="hidden" name="CargoHousefileInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'UPS') { ?>
        <input type="hidden" name="upsInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'UPS Housefile') { ?>
        <input type="hidden" name="UPSHousefileInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'Aeropost') { ?>
        <input type="hidden" name="aeropostInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'Aeropost Housefile') { ?>
        <input type="hidden" name="AeropostHousefileInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'CCpack') { ?>
        <input type="hidden" name="ccpackInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
    <?php if ($dinfo->typeFlag == 'CCpack Housefile') { ?>
        <input type="hidden" name="CCpackHousefileInvoices[]" value="<?php echo $dinfo->id; ?>">
    <?php } ?>
<?php } ?>
<?php $i++; ?>
@endforeach