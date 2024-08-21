<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
    <title>Cost Items Detail</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">
        <div style="width:100%">
            <div style="width:60%;float:left">
                <div style="width: 100%"><b>CHATELAIN CARGO SERVICES S.A</b></div>
                <div style="width: 100%">16312 SW 45th Ter</div>
                <div style="width: 100%">Miami, FL 33185 US</div>
                <div style="width: 100%">pvc@chatelaincargo.com</div>
                <div style="width: 100%">www.chatelaincargo.com</div>
            </div>
            <div style="width:40%;float:right">
                <img src="<?php echo public_path() . '/images/chatelain_logo.jpeg' ?>">
            </div>
        </div>

        <div style="width:100%;margin-top:20px">
            <div style="width:30%;float:left">
                <div style="width: 100%"><b>Name</b></div>
                <div style="width: 100%"><?php echo $dataCostItem->cost_name; ?></div>
            </div>
        </div>

        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="width:50%;float:left">Cost Items Detail</div>
                    <div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
                        <?php if (!empty($fromDate) && !empty($toDate)) {
                            echo date('d-m-Y', strtotime($fromDate)) . ' To ' . date('d-m-Y', strtotime($toDate));
                        }
                        ?>
                    </div>
                </h3>
                
                <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                    <thead>
                        <tr>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Date</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Voucher No.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Currency</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Vendor</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Description</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data)) {
                            foreach ($data as $k => $v) {
                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo date('d-m-Y', strtotime($v->exp_date)); ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo $v->voucher_number; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                                        <?php echo $v->currencyCode; ?>
                                    </td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                                        <?php echo $v->company_name; ?>
                                    </td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                                        <?php echo $v->description; ?>
                                    </td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;">
                                        <?php echo $v->amount; ?>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
                <div style="width:50%;float:right;margin-top:10px">
                    <div style="width: 20%;float:left"><b>USD: </b></div>
                    <div style="width: 30%;float:left;text-align: left">{{number_format($totalInUsd,2)}}</div>
                    <div style="width: 20%;float:left"><b>HTG: </b></div>
                    <div style="width: 30%;float:left;text-align: left">{{number_format($totalInHtg,2)}}</div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>