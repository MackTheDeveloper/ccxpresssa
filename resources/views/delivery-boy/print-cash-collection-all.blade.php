<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
    <title>CASH COLLECTION DETAIL</title>
</head>
<?php
if ($courierType == 'UPS, Aeropost, CCPack') {
    $flagCourierC = 'All';
} else {
    $flagCourierC = '';
}
?>

<body>
    <section class="content" style="font-family: sans-serif;">

        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="width:50%;float:left">CASH COLLECTION DETAIL - {{$courierType}}</div>
                    <div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
                        <?php echo $model->name; ?>
                        <?php if (!empty($fromDate) && !empty($toDate)) {
                            echo date('d-m-Y', strtotime($fromDate)) . ' To ' . date('d-m-Y', strtotime($toDate));
                        }
                        ?>
                    </div>
                </h3>

                <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px;border-collapse: collapse">
                    <thead>
                        <tr>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File #</th>
                            <th style="width:120px;border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Shipment ID#</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Consignee</th>
                            <th style="width:80px;border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Invoice#</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: right;">Amount</th>
                            <th style="width:110px;border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Package type</th>
                            <th style="width:70px;border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Billing Term</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Payment Status</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Receipt Number</th>
                            <th style="width:80px;border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data)) {
                            foreach ($data as $k => $items) {
                                if ($flagCourierC == 'All') {
                                    $courierType = $items->courierType;
                                }

                                $receiptNumbers = app('App\Common')->getReceiptNumbers($items->invoiceId, 'ups');
                                if ($courierType == 'UPS') {
                                    if ($items->package_type == 'LTR')
                                        $packageType = 'Letter';
                                    else if ($items->package_type == 'DOC')
                                        $packageType = 'Document';
                                    else
                                        $packageType = 'Package';
                                    $billingTerm = app('App\Ups')::getBillingTerm($items->id);
                                    $fileStatus = !empty($items->ups_scan_status) ? Config::get('app.ups_new_scan_status')[$items->ups_scan_status] : '-';
                                } else if ($courierType == 'Aeropost') {
                                    $packageType = '-';
                                    $billingTerm = '-';
                                    $fileStatus = !empty($items->aeropost_scan_status) ? Config::get('app.ups_new_scan_status')[$items->aeropost_scan_status] : '-';
                                } else {
                                    $packageType = '-';
                                    $billingTerm = '-';
                                    $fileStatus = !empty($items->ccpack_scan_status) ? Config::get('app.ups_new_scan_status')[$items->ccpack_scan_status] : '-';
                                }
                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo !empty($items->file_number) ? $items->file_number : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{!empty($items->shipment_number) ? $items->shipment_number : '-'}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{$items->consigneeName}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{!empty($items->invoiceNumbers) ? $items->invoiceNumbers : '-'}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;">{{!empty($items->totalAmount) ? number_format($items->totalAmount,2) : '0.00'}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{$packageType}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{$billingTerm}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{!empty($items->payment_status) ? $items->payment_status : '-'}}</td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo !empty($receiptNumbers) ? $receiptNumbers : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">{{$fileStatus}}</td>
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