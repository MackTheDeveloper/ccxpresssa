<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
    <title>Due Invoices</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">

        <div style="width:100%">
            <div style="width:60%;float:left">
                <div style="width: 100%"><b>CHATELAIN CARGO SERVICES S.A</b></div>
                <div style="width: 100%">42 Route de l'Aéroport</div>
                <div style="width: 100%">Port-au-Prince, Haiti</div>
                <div style="width: 100%">Tel: +509 2812 0582</div>
                <div style="width: 100%">Email: info@chatelaincargo.com</div>
            </div>
            <div style="width:40%;float:right;text-align:right">
                <img style="width:200px;height:120px" src="<?php echo public_path() . '/images/chatelain_logo_1.jpeg' ?>">
            </div>
        </div>

        <div style="width:100%;margin-top:20px">
            <div style="width:30%;float:left">
                <div style="width: 100%"><b>To</b></div>
                <div style="width: 100%"><?php echo $clientData->company_name; ?></div>
            </div>
        </div>

        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%;display:none">
                    <div style="width:50%;float:left">Due Invoices</div>
                    <div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
                        <?php echo $clientData->company_name; ?>
                    </div>
                </h3>

                <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                    <thead>
                        <tr>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Date</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Description</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Invoice No.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Amount</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Open Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $allTotalDue = '0.00';
                        if (!empty($allDue)) {
                            foreach ($allDue as $k => $items) {
                                $allTotalDue += $items->totalDue;
                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo date('d-m-Y', strtotime($items->date)) ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">{{'#'.$items->fileNumber.', '.$items->trackingNumber}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">{{'#'.$items->bill_no}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;">{{number_format($items->totalAmount,2)}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;">{{number_format($items->totalDue,2)}}</td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
                <div style="width:40%;float:right;margin-top:10px;">
                    <div style="width: 40%;float:left;"><b>Amount Due: </b></div>
                    <?php $currencyData = App\Currency::getData($clientData->currency); ?>
                    <div style="width: 59%;float:left;text-align: right;">{{$currencyData->code.' '.number_format($allTotalDue,2)}}</div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>