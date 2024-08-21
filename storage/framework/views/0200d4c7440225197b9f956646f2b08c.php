<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
    <title>Non Billed Files Report</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">

        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="width:50%;float:left">Non Billed Files : <?php echo e($cargoType); ?></div>
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
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Date</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">File Number</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">AWB Number.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Consignee</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Shipper</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dataNonBilledFiles)) {
                            foreach ($dataNonBilledFiles as $k => $v) {
                                if ($cargoType == 'Cargo') {
                                    $awbNo = $v->awb_bl_no;
                                } else {
                                    if ($v->cargo_operation_type == '1')
                                        $awbNo = $v->hawb_hbl_no;
                                    else
                                        $awbNo = $v->export_hawb_hbl_no;
                                }

                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo date('d-m-Y', strtotime($v->opening_date)) ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->file_number; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($awbNo) ? $awbNo : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($v->consigneeCompany) ? $v->consigneeCompany : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
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

</html><?php /**PATH /var/www/html/php/cargo/resources/views/reports/printnonbilledfilesReports.blade.php ENDPATH**/ ?>