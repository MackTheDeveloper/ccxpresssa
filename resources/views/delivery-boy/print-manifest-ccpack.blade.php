<html>

<head>
    <title>Manifest Detail</title>
</head>

<body>
    <?php $i = 1;
    foreach ($ccpackFileAssignedToDeliveryBoy as $k => $items) {
        $countPending = app('App\Common')->checkIfInvoiceStatusPending($items->id, 'ccpack');
    ?>
        <section class="content" style="font-family: sans-serif;">
            <div class="box box-success" style="width: 100%;margin: 0px auto;">
                <div class="box-body cargo-forms" style="color: #000">

                    <div style="float: left;width: 100%; margin: 5px 0 5px 0;">
                        <div style="text-align: left;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
                            Chatelain Cargo Services
                        </div>
                        <div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Manifest Detail &nbsp;&nbsp;&nbsp; {{$items->file_number}}</div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">File #:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($items->file_number) ? $items->file_number : '-'; ?></div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">File status:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">{{!empty($items->ccpack_scan_status) ? Config::get('app.ups_new_scan_status')[$items->ccpack_scan_status] : '-'}}</div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">Invoice #:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">{{!empty($items->invoiceNumbers) ? $items->invoiceNumbers : '-'}}</div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">Invoice Amount:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">{{!empty($items->totalAmount) ? number_format($items->totalAmount,2) : '0.00'}}</div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">Payment Status:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">{{($countPending > 0) ? 'Pending' : 'Paid'}}</div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">Consignee:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">{{$items->consigneeName}}</div>
                    </div>

                    <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                        <div style="width: 30%;float: left;">Delivery Date:</div>
                        <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">{{!empty($items->delivery_boy_assigned_on) ? date('d-m-Y',strtotime($items->delivery_boy_assigned_on)) : '-'}}</div>
                    </div>
                </div>
            </div>
        </section>
        <?php if (count($ccpackFileAssignedToDeliveryBoy) != $i) { ?>
            <pagebreak></pagebreak>
        <?php } ?>
    <?php $i++;
    } ?>
</body>

</html>