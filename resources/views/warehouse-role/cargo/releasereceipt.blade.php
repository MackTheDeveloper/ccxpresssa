<html>

<head>
    <title>Release Receipt</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">
        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">

                <div style="float: left;width: 100%; margin: 5px 0 5px 0;">
                    <div style="text-align: left;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
                        Chatelain Cargo Services
                    </div>
                    <div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Release Receipt &nbsp;&nbsp;&nbsp; {{$model->file_number}}</div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                    <div style="width: 30%;float: left;">Consignee:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Shipper:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Number of pieces:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($modelCargoPackage) ? (int) $modelCargoPackage->ppieces : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Master File:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo $masterFileData->file_number; ?></div>
                </div>

                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of arrival:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipment_received_date) ? date('d-m-Y', strtotime($model->shipment_received_date)) : '-'; ?></div>
                </div>
                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of customs inspection:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->inspection_date) ? date('d-m-Y', strtotime($model->inspection_date)) : '-'; ?></div>
                </div>

                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of payment:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($checkInvoiceIsGeneratedOrNot) ? date('d-m-Y', strtotime($checkInvoiceIsGeneratedOrNot->payment_received_on)) : '-'; ?></div>
                </div>
                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of release:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipment_delivered_date) ? date('d-m-Y', strtotime($model->shipment_delivered_date)) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Customs file #:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->custom_file_number) ? $model->custom_file_number : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Customs invoice number:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->custom_invoice_number) ? $model->custom_invoice_number : '-'; ?></div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 15px;">
                        <div><b>Release By CSS</b></div>
                    </div>
                    <div style="width: 100%;">
                        <div style="width: 49%;float:left">
                            <b>Agent Name :</b> <?php echo !empty($model->release_by_css_agent) ? $model->release_by_css_agent : '-'; ?>
                        </div>
                        <div style="width: 49%;float:left">
                            <b>Driver Name :</b> <?php echo !empty($model->release_by_css_driver) ? $model->release_by_css_driver : '-'; ?>
                        </div>
                    </div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 15px;">
                        <div><b>EXPLICATIONS / INFORMATIONS</b></div>
                    </div>
                    <div>
                        <?php echo !empty($model->information) ? $model->information : '-'; ?>
                    </div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 15px;">
                        <div><b>Nature du service</b></div>
                    </div>
                    <div>
                        <br />
                    </div>
                </div>


                <div style="float: left; width: 20%; margin-top: 30px; bottom: 0; left: 20px;border-bottom: 1px solid #ccc; padding-bottom:10px;">
                    <div style="width: 100%;float: left;margin-top: 20px;">
                        <div style="padding-bottom: 30px">Signature</div>
                    </div>
                </div>


            </div>
        </div>
    </section>


    <pagebreak></pagebreak>

    <section class="content" style="font-family: sans-serif;">
        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">

                <div style="float: left;width: 100%; margin: 5px 0 5px 0;">
                    <div style="text-align: left;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
                        Chatelain Cargo Services
                    </div>
                    <div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Release Receipt &nbsp;&nbsp;&nbsp; {{$model->file_number}}</div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;margin-top: 15px;">
                    <div style="width: 30%;float: left;">Consignee:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Shipper:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Number of pieces:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($modelCargoPackage) ? (int) $modelCargoPackage->ppieces : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Master File:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo $masterFileData->file_number; ?></div>
                </div>

                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of arrival:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipment_received_date) ? date('d-m-Y', strtotime($model->shipment_received_date)) : '-'; ?></div>
                </div>
                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of customs inspection:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->inspection_date) ? date('d-m-Y', strtotime($model->inspection_date)) : '-'; ?></div>
                </div>

                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of payment:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($checkInvoiceIsGeneratedOrNot) ? date('d-m-Y', strtotime($checkInvoiceIsGeneratedOrNot->payment_received_on)) : '-'; ?></div>
                </div>
                <div style="width: 100%;float: left;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Date of release:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipment_delivered_date) ? date('d-m-Y', strtotime($model->shipment_delivered_date)) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Customs file #:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->custom_file_number) ? $model->custom_file_number : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 30%;float: left;">Customs invoice number:</div>
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->custom_invoice_number) ? $model->custom_invoice_number : '-'; ?></div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 15px;">
                        <div><b>Release By CSS</b></div>
                    </div>
                    <div style="width: 100%;">
                        <div style="width: 49%;float:left">
                            <b>Agent Name :</b> <?php echo !empty($model->release_by_css_agent) ? $model->release_by_css_agent : '-'; ?>
                        </div>
                        <div style="width: 49%;float:left">
                            <b>Driver Name :</b> <?php echo !empty($model->release_by_css_driver) ? $model->release_by_css_driver : '-'; ?>
                        </div>
                    </div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 15px;">
                        <div><b>EXPLICATIONS / INFORMATIONS</b></div>
                    </div>
                    <div>
                        <?php echo !empty($model->information) ? $model->information : '-'; ?>
                    </div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 15px;">
                        <div><b>Nature du service</b></div>
                    </div>
                    <div>
                        <br />
                    </div>
                </div>


                <div style="float: left; width: 20%; margin-top: 30px; bottom: 0; left: 20px;border-bottom: 1px solid #ccc; padding-bottom:10px;">
                    <div style="width: 100%;float: left;margin-top: 20px;">
                        <div style="padding-bottom: 30px">Signature</div>
                    </div>
                </div>


            </div>
        </div>
    </section>

</body>

</html>