<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
</head>
<body>
<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">
            <div style="width: 100%;margin-bottom: 25px;float:left;">
                <div style="width:100%;float:left;color:#0000;text-align: center;">
                    <h2 style="margin:0px;font-size:18px">CCXPRESS S.A</h2>
                    <h3 style="margin:0px;font-weight: normal;line-height:0px;font-size:13px">42, Auto-Route de Aeroport</h3>
                    <h3 style="margin:0px;font-weight: normal;line-height:0px;font-size:13px">Port-au-Prince Haiti</h3>
                    <h3 style="margin:0px;font-weight: normal;line-height:0px;font-size:13px">(509) 22 50 16 50/(509) 28 16 81 81</h3>
                </div>
            </div>
            <?php if($flag == 'invoice') { ?>

                <div style="width:100%;margin-top:30px;float:left;">
                        <div style="width:100%;float:left;color:#0000;text-align: center;border-top:1px dashed #ccc">
                            <h2 style="margin:0px;font-weight:normal;font-size:18px;margin-top:5px">Reçu de Caisse</h2>
                        </div>
                        <div style="float:left;width:50%;text-align:left;margin-top:2px;display:none">
                            Date
                        </div>
                        <div style="float:left;width:50%;text-align:right;margin-top:2px;display:none">
                            <?php //echo date('d-m-Y',strtotime($data[0]->paymentDate)); ?>
                        </div>
                    </div>
    
                    <div style="width:100%;margin-top:30px;float:left;">
                        <div style="width:100%;float:left;color:#0000;text-align: center;">
                            <h2 style="margin:0px;font-weight:normal;font-size:18px;margin-top:5px;border-bottom:1px dashed #ccc"><?php echo $dataClient->company_name; ?></h2>
                        </div>
                        <div style="width:100%;float:left;color:#0000;text-align: center;">
                                <?php foreach($data as $k => $v) { ?>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;">Paiement en</div>
                                    <div style="float:left;width:10%;text-align:left;margin-top:2px;"><?php echo $v->exchangeCurrencyCode; ?></div>
                                    <div style="float:left;width:70%;text-align:left;margin-top:2px;"><?php echo $v->total_payments_collected.' '.$v->exchangeCurrencyCode; ?></div>
                                <?php } ?>
                        </div>
                    </div>
    
                    <div style="width:100%;margin-top:30px;float:left;">
                        <div style="float:left;width:20%;text-align:left;margin-top:2px;">No de recu:</div>    
                        <div style="float:left;width:80%;text-align:left;margin-top:2px;"><?php echo '10'.$data[0]->paymentId; ?></div>    
                    </div>
    
                    <div style="width:100%;margin-top:30px;float:left;background:#ccc;padding:5px">
                            <div style="float:left;width:20%;text-align:left;">Date</div>    
                            <div style="float:left;width:20%;text-align:left;">Reglement</div>    
                            <div style="float:left;width:20%;text-align:left;">Invoice No</div>    
                            <div style="float:left;width:20%;text-align:left;">Currency</div>    
                            <div style="float:left;width:20%;text-align:left;">No de Tracking</div>    
                    </div>
                    <div style="width:100%;float:left;">
                        <div style="width:100%;float:left;color:#0000;text-align: center;">
                                <?php foreach($data as $k => $v) { ?>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo date('d-m-Y',strtotime($v->paymentDate)); ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->total_payments_collected; ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->bill_no ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->invoiceCurrencyCode ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->awb_no; ?></div>
                                <?php } ?>
                        </div>
                    </div>

                    <div style="width:100%;float:left;margin-top:30px">
                            <div style="width:50%;float:right">
                            <?php foreach($total as $k => $v) { ?>
                                <div style="width:50%;float:left"><?php echo 'Total'; ?></div>
                                <div style="width:50%;float:left"><?php echo $k.'&nbsp;&nbsp;&nbsp;'.number_format($v,2); ?></div>
                            <?php } ?>
                            </div>
                    </div>
            <?php } else { ?>
                <div style="width:100%;margin-top:30px;float:left;">
                        <div style="width:100%;float:left;color:#0000;text-align: center;border-top:1px dashed #ccc">
                            <h2 style="margin:0px;font-weight:normal;font-size:18px;margin-top:5px">Reçu de Caisse</h2>
                        </div>
                        <div style="float:left;width:50%;text-align:left;margin-top:2px;display:none">
                            Date
                        </div>
                        <div style="float:left;width:50%;text-align:right;margin-top:2px;display:none">
                            <?php //echo date('d-m-Y',strtotime($data[0]->paymentDate)); ?>
                        </div>
                    </div>
    
                    <div style="width:100%;margin-top:30px;float:left;">
                        <div style="width:100%;float:left;color:#0000;text-align: center;">
                            <h2 style="margin:0px;font-weight:normal;font-size:18px;margin-top:5px;border-bottom:1px dashed #ccc"><?php echo $dataClient->company_name; ?></h2>
                        </div>
                        <div style="width:100%;float:left;color:#0000;text-align: center;">
                                <?php foreach($data as $k => $v) { ?>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;">Paiement en</div>
                                    <div style="float:left;width:10%;text-align:left;margin-top:2px;"><?php echo $v->exchangeCurrencyCode; ?></div>
                                    <div style="float:left;width:70%;text-align:left;margin-top:2px;"><?php echo $v->total_payments_collected.' '.$v->exchangeCurrencyCode; ?></div>
                                <?php } ?>
                        </div>
                    </div>
    
                    <div style="width:100%;margin-top:30px;float:left;">
                        <div style="float:left;width:20%;text-align:left;margin-top:2px;">No de recu:</div>    
                        <div style="float:left;width:80%;text-align:left;margin-top:2px;"><?php echo '10'.$data[0]->paymentId; ?></div>    
                    </div>
    
                    <div style="width:100%;margin-top:30px;float:left;background:#ccc;padding:5px">
                        <div style="float:left;width:20%;text-align:left;">Date</div>    
                        <div style="float:left;width:20%;text-align:left;">Reglement</div>    
                        <div style="float:left;width:20%;text-align:left;">Invoice No</div>    
                        <div style="float:left;width:20%;text-align:left;">Currency</div>    
                        <div style="float:left;width:20%;text-align:left;">No de Tracking</div>    
                    </div>
                    <div style="width:100%;float:left;">
                        <div style="width:100%;float:left;color:#0000;text-align: center;">
                                <?php foreach($data as $k => $v) { ?>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo date('d-m-Y',strtotime($v->paymentDate)); ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->total_payments_collected; ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->bill_no ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->invoiceCurrencyCode ?></div>
                                    <div style="float:left;width:20%;text-align:left;margin-top:2px;"><?php echo $v->awb_no; ?></div>
                                <?php } ?>
                        </div>
                    </div>

                    <div style="width:100%;float:left;margin-top:30px">
                        <div style="width:50%;float:right">
                        <?php foreach($total as $k => $v) { ?>
                            <div style="width:50%;float:left"><?php echo 'Total'; ?></div>
                            <div style="width:50%;float:left"><?php echo $k.'&nbsp;&nbsp;&nbsp;'.number_format($v,2); ?></div>
                        <?php } ?>
                        </div>
                    </div>
            <?php } ?>
        </div>
    </div>
</section>
</body>
</html>

