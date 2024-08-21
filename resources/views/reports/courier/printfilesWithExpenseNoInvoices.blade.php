<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
    <title>Files with expense but no invoices</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">

        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="width:60%;float:left">Files with expense but no invoices : {{$courierType}}</div>
                    <div style="text-align: right;float: left; width: 40%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
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
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File Number</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">AWB Number.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Consignee</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Shipper</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dataNonBilledFiles)) {
                            foreach ($dataNonBilledFiles as $k => $v) {
                                if ($courierType == 'UPS') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->awb_number;
                                } else if ($courierType == 'upsMaster') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->tracking_number;
                                } else if ($courierType == 'Aeropost') {
                                    $date = !empty($v->date) ? date('d-m-Y', strtotime($v->date)) : '-';
                                    $awbNo = $v->tracking_no;
                                } else if ($courierType == 'aeropostMaster') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->tracking_number;
                                } else if ($courierType == 'ccpackMaster') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->tracking_number;
                                } else {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->awb_number;
                                }
                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo $date; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo $v->file_number; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo !empty($awbNo) ? $awbNo : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                                        <?php echo !empty($v->consigneeCompany) ? $v->consigneeCompany : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                                        <?php echo !empty($v->shipperCompany) ? $v->shipperCompany : '-'; ?></td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </section>
</body>

</html>