<!DOCTYPE html>
<html>

<head>
    <title>Payment Receipt</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">
        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <div style="width: 100%;margin-bottom: 10px;float:left;">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <h2 style="margin:0px;font-size:18px">Chatelain Cargo Services SA</h2>
                        <h3 style="margin:0px;font-weight: normal;line-height:0px;font-size:13px">42, Auto-Route de
                            Aeroport</h3>
                        <h3 style="margin:0px;font-weight: normal;line-height:0px;font-size:13px">Port-au-Prince Haiti
                        </h3>
                        <h3 style="margin:0px;font-weight: normal;line-height:0px;font-size:13px">(509) 22 50 16
                            50/(509) 28 16 81 81</h3>
                    </div>
                </div>
                <?php if ($flag == 'invoice') { ?>

                <div style="width:100%;margin-top:0px;float:left;">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <h2 style="margin:0px;font-weight:normal;font-size:15px;margin-top:5px">Reçu de Caisse</h2>
                    </div>
                    <div style="float:left;width:50%;text-align:left;margin-top:2px;display:none">
                        Date
                    </div>
                    <div style="float:left;width:50%;text-align:right;margin-top:2px;display:none">
                        <?php //echo date('d-m-Y',strtotime($data[0]->paymentDate)); 
                            ?>
                    </div>
                </div>

                <div style="width:100%;margin-top:10px;float:left;">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <h2
                            style="margin:0px;font-weight:normal;font-size:12px;margin-top:5px;border-bottom:1px dashed #ccc">
                            <?php echo !empty($dataClient) ? $dataClient->company_name : '-'; ?>
                        </h2>
                    </div>
                    <div style="width:100%;float:left;color:#0000;text-align: center;display:none">
                        <?php if (count($data) > 0) {
                                foreach ($data as $k => $v) { ?>
                        <div style="float:left;width:40%;text-align:left;margin-top:2px;">Paiement en</div>
                        <div style="float:left;width:20%;text-align:left;margin-top:2px;">
                            <?php echo $v->exchangeCurrencyCode; ?>
                        </div>
                        <div style="float:left;width:35%;text-align:left;margin-top:2px;">
                            <?php echo $v->total_payments_collected . ' ' . $v->exchangeCurrencyCode; ?>
                        </div>
                        <?php }
                            } ?>
                    </div>
                </div>

                <?php if (count($data) > 0) { ?>
                <?php if (!$flagChangeLayout) { ?>
                <div style="width:100%;margin-top:10px;float:left;">
                    <div style="float:left;width:32%;text-align:left;margin-top:2px;">No de recu:</div>
                    <div style="float:left;width:55%;text-align:left;margin-top:2px;">
                        <?php echo $data[0]->receipt_number; ?>
                    </div>
                </div>
                <?php } ?>

                <div style="width:100%;margin-top:10px;float:left;background:#ccc;padding:5px">
                    <div style="float:left;width:35%;text-align:left;">Date</div>
                    <div style="float:left;width:30%;text-align:left;">Invoice No</div>
                    <div style="float:left;width:34%;text-align:right;">Reglement</div>
                </div>
                <div style="width:100%;float:left;font-size:12px">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <?php foreach ($data as $k => $v) { ?>
                        <div style="float:left;width:35%;text-align:left;margin-top:2px;">
                            <?php echo date('d-m-Y', strtotime($v->paymentDate)); ?>
                        </div>
                        <div style="float:left;width:30%;text-align:center;margin-top:2px;">
                            <?php echo $v->bill_no ?>
                        </div>
                        <div style="float:left;width:34%;text-align:right;margin-top:0px;">
                            <?php echo number_format($v->total_payments_collected, 2) . ' ' . $v->exchangeCurrencyCode; ?>
                        </div>
                        <div
                            style="float:left;width:100%;margin-top:0px;margin-bottom:4px;text-align:left;border-bottom:1px dashed #ccc;font-size:12px">
                            <?php echo 'No de Tracking' . ' : ' . $v->awb_no; ?>
                        </div>
                        <?php if (!empty($flagChangeLayout)) { ?>
                        <div
                            style="float:left;width:100%;margin-top:0px;margin-bottom:4px;text-align:left;border-bottom:1px dashed #ccc;font-size:12px">
                            <?php echo 'No de recu:' . ' ' . $v->receipt_number; ?>
                        </div>
                        <?php } ?>
                        <?php if (!empty($v->payment_via)) { ?>
                        <div
                            style="float:left;width:100%;margin-top:0px;margin-bottom:4px;text-align:left;border-bottom:1px dashed #ccc;font-size:12px">
                            <?php echo $v->payment_via . ' : ' . $v->payment_via_note; ?>
                        </div>
                        <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <?php if (!empty($creditedAmount) && $creditedAmount != '0.00') { ?>
                <div style="width:100%;float:left;font-size:12px">
                    <div style="float:left;width:40%;margin-right:10px">
                        <?php echo 'Credited Amount'; ?>
                    </div>
                    <div style="float:left;width:40%;">
                        <?php echo number_format($creditedAmount, 2); ?>
                    </div>
                </div>
                <?php } ?>

                <div style="width:100%;float:left;margin-top:30px">
                    <div style="width:75%;float:right">
                        <?php foreach ($total as $k => $v) { ?>
                        <div style="width:20%;float:left">
                            <?php echo 'Total'; ?>
                        </div>
                        <div style="width:30%;float:left;text-align:center">
                            <?php echo $k; ?>
                        </div>
                        <div style="width:49%;float:left;text-align:right">
                            <?php echo number_format((!empty($v)?$v:0) + (!empty($creditedAmount)?$creditedAmount:0), 2); ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php } else { ?>
                <div style="width:100%;margin-top:0px;float:left;">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <h2 style="margin:0px;font-weight:normal;font-size:15px;margin-top:5px">Reçu de Caisse</h2>
                    </div>
                    <div style="float:left;width:50%;text-align:left;margin-top:2px;display:none">
                        Date
                    </div>
                    <div style="float:left;width:50%;text-align:right;margin-top:2px;display:none">
                        <?php //echo date('d-m-Y',strtotime($data[0]->paymentDate)); 
                            ?>
                    </div>
                </div>

                <div style="width:100%;margin-top:10px;float:left;">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <h2
                            style="margin:0px;font-weight:normal;font-size:12px;margin-top:5px;border-bottom:1px dashed #ccc">
                            <?php echo !empty($dataClient) ? $dataClient->company_name : '-'; ?>
                        </h2>
                    </div>
                    <div style="width:100%;float:left;color:#000;text-align: center;display:none">
                        <?php if (count($data) > 0) {
                                foreach ($data as $k => $v) { ?>
                        <div style="float:left;width:40%;text-align:left;margin-top:2px;">Paiement en</div>
                        <div style="float:left;width:20%;text-align:left;margin-top:2px;">
                            <?php echo $v->exchangeCurrencyCode; ?>
                        </div>
                        <div style="float:left;width:35%;text-align:left;margin-top:2px;">
                            <?php echo $v->total_payments_collected . ' ' . $v->exchangeCurrencyCode; ?>
                        </div>
                        <?php }
                            } ?>
                    </div>
                </div>
                <?php if (count($data) > 0) { ?>
                <div style="width:100%;margin-top:10px;float:left;">
                    <div style="float:left;width:32%;text-align:left;margin-top:2px;">No de recu:</div>
                    <div style="float:left;width:55%;text-align:left;margin-top:2px;">
                        <?php echo $data[0]->receipt_number; ?>
                    </div>
                </div>

                <div style="width:100%;margin-top:10px;float:left;background:#eee;padding:5px">
                    <div style="float:left;width:35%;text-align:left;">Date</div>
                    <div style="float:left;width:30%;text-align:left;">Invoice No</div>
                    <div style="float:left;width:34%;text-align:right;">Reglement</div>
                </div>
                <div style="width:100%;float:left;font-size:12px">
                    <div style="width:100%;float:left;color:#000;text-align: center;">
                        <?php foreach ($data as $k => $v) { ?>
                        <div style="float:left;width:35%;text-align:left;margin-top:2px;">
                            <?php echo date('d-m-Y', strtotime($v->paymentDate)); ?>
                        </div>
                        <div style="float:left;width:30%;text-align:center;margin-top:2px;">
                            <?php echo $v->bill_no ?>
                        </div>
                        <div style="float:left;width:34%;text-align:right;margin-top:0px;">
                            <?php echo number_format($v->total_payments_collected, 2) . ' ' . $v->exchangeCurrencyCode; ?>
                        </div>
                        <div
                            style="float:left;width:100%;margin-top:0px;margin-bottom:4px;text-align:left;border-bottom:1px dashed #ccc;font-size:12px">
                            <?php echo 'No de Tracking' . ' : ' . $v->awb_no; ?>
                        </div>
                        <?php if (!empty($v->payment_via)) { ?>
                        <div
                            style="float:left;width:100%;margin-top:0px;margin-bottom:4px;text-align:left;border-bottom:1px dashed #ccc;font-size:12px">
                            <?php echo $v->payment_via . ' : ' . $v->payment_via_note; ?>
                        </div>
                        <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <?php if (!empty($creditedAmount) && $creditedAmount != '0.00') { ?>
                <div style="width:100%;float:left;font-size:12px">
                    <div style="float:left;width:40%;margin-right:10px">
                        <?php echo 'Credited Amount'; ?>
                    </div>
                    <div style="float:left;width:40%;">
                        <?php echo number_format($creditedAmount, 2); ?>
                    </div>
                </div>
                <?php } ?>

                <div style="width:100%;float:left;margin-top:30px">
                    <div style="width:75%;float:right">
                        <?php foreach ($total as $k => $v) { ?>
                        <div style="width:20%;float:left">
                            <?php echo 'Total'; ?>
                        </div>
                        <div style="width:30%;float:left;text-align:center">
                            <?php echo $k; ?>
                        </div>
                        <div style="width:49%;float:left;text-align:right">
                            <?php echo number_format((!empty($v)?$v:0) + (!empty($creditedAmount)?$creditedAmount:0), 2); ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php } ?>
            </div>
        </div>
    </section>
</body>

</html>

<style>
    @page {
        margin-left: 0.5cm;
        margin-right: 0.5cm;
    }
</style>