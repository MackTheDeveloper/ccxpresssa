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
                    <div style="width:50%;float:left">Non Billed Files : {{$courierType}}</div>
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
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Master File Number</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Billing Party</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">File Status</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">AWB Number.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Consignee</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Shipper</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Package Type</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Origin</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Weight</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Billing Term</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dataNonBilledFiles)) {
                            foreach ($dataNonBilledFiles as $k => $v) {
                                if ($courierType == 'UPS') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->awb_number;
                                    $billingTerm = app('App\Ups')::getBillingTerm($v->upsId);
                                    if ($v->package_type == 'LTR')
                                        $packageType = 'Letter';
                                    else if ($v->package_type == 'DOC')
                                        $packageType = 'Document';
                                    else
                                        $packageType = 'Package';
                                    $origin = $v->origin;
                                    $weight = $v->weight;
                                    $fileStatus = !empty($v->ups_scan_status) ? Config::get('app.ups_new_scan_status')[$v->ups_scan_status] : '-';
                                } else if ($courierType == 'upsMaster') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->tracking_number;
                                    $billingTerm = '';
                                    $packageType = '';
                                    $origin = '';
                                    $weight = $v->weight;
                                    $fileStatus = '';
                                } else if ($courierType == 'Aeropost') {
                                    $date = !empty($v->date) ? date('d-m-Y', strtotime($v->date)) : '-';
                                    $awbNo = $v->tracking_no;
                                    $billingTerm = '';
                                    $packageType = '';
                                    $origin = '';
                                    $weight = $v->real_weight;
                                    $fileStatus = !empty($v->aeropost_scan_status) ? Config::get('app.ups_new_scan_status')[$v->aeropost_scan_status] : '-';
                                } else if ($courierType == 'aeropostMaster') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->tracking_number;
                                    $billingTerm = '';
                                    $packageType = '';
                                    $origin = '';
                                    $weight = $v->weight;
                                    $fileStatus = '';
                                } else if ($courierType == 'ccpackMaster') {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->tracking_number;
                                    $billingTerm = '';
                                    $packageType = '';
                                    $origin = '';
                                    $weight = $v->weight;
                                    $fileStatus = '';
                                } else {
                                    $date = !empty($v->arrival_date) ? date('d-m-Y', strtotime($v->arrival_date)) : '-';
                                    $awbNo = $v->awb_number;
                                    $billingTerm = '';
                                    $packageType = '';
                                    $origin = '';
                                    $weight = $v->weight;
                                    $fileStatus = !empty($v->ccpack_scan_status) ? Config::get('app.ups_new_scan_status')[$v->ccpack_scan_status] : '-';
                                }

                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $date; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->file_number; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->master_file_number) ? $v->master_file_number : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->billingParty) ? $v->billingParty : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $fileStatus; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($awbNo) ? $awbNo : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($v->consigneeCompany) ? $v->consigneeCompany : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($v->shipperCompany) ? $v->shipperCompany : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($packageType) ? $packageType : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($origin) ? $origin : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($weight) ? $weight : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo !empty($billingTerm) ? $billingTerm : '-'; ?></td>
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