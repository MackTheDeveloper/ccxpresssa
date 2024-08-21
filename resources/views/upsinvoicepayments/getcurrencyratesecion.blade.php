<?php use App\Currency; ?>
<div class="col-md-12">
    <?php foreach ($currencyExchangeData as $key => $value) { 
        $fromData = Currency::getData($value->from_currency);
        $toData = Currency::getData($value->to_currency);
        ?>
        <div style="margin-bottom: 10px;" class="col-md-12"><b style="float:left;margin-top: 5px;"><?php echo $fromData->code ?> To <?php echo $toData->code ?></b><input type="text" id="exchange_value-<?php echo $value->from_currency; ?>" class="form-control" value="<?php echo $value->exchange_value; ?>" style="float: left;width: 40%;margin-left: 20px;"></div>
    <?php } ?>
</div>