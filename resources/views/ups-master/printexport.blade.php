<!DOCTYPE html>
<html>

<head>
    <title>Export File</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;line-height: 15px">
        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">

                <div style="width: 100%;margin-bottom: 25px;">
                    <div style="width:20%;float:left">
                        <img src="{{ public_path('images/logo_files.png') }}" alt="text" height="70px" widht='70px' class="css-class" />
                    </div>
                    <div style="width:60%;float:left;color:#0000;text-align: center;">
                        <h2 style="margin:0px">EXPORTATIONS</h2>
                        <h3 style="margin-top:20px;font-weight: normal;">CHATELAIN CARGO SERVICES</h3>
                    </div>
                    <div style="width:20%;float:left;color:#0000;text-align: center;">
                        <h4 style="margin:0px;text-align: right;">No DOSSIER</h4>
                        <h3 style="margin-top:5px;text-align: right;font-weight: normal;">{{$model->file_number}}</h3>
                    </div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 80%;float: left;">
                        <div style="width: 20%;float: left;">Nom/Expéditeur :</div>
                        <div style="width: 79%;float: left;border-bottom: 1px dotted #000">
                            <b><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></b>
                        </div>
                    </div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 9%;float: left;">Adresse :</div>
                    <div style="width: 91%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px"><?php echo !empty($model->shipper_address) ? $model->shipper_address : '-'; ?></div>
                    <div style="width: 100%;float: left;border-bottom: 1px dotted #000">&nbsp;</div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 13%;float: left;">Destinataire :</div>
                    <div style="width: 87%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 19%;float: left;">DATE D'ARRIVEE :</div>
                    <div style="width: 50%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-'; ?></div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 19%;float: left;">Tracking Number :</div>
                    <div style="width: 50%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px"><?php echo !empty($model->tracking_number) ? $model->tracking_number : '-'; ?></div>
                </div>

                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 90%;margin-bottom: 0px;float: left;">
                        <div style="width: 33%;float: left;">
                            Weight : <?php echo !empty($model->weight) ? $model->weight : '-'; ?>
                            @if($model->measure_weight == 'k')
                            {{!empty($model->weight) ? 'Kg' : ''}}
                            @else
                            {{'Pound'}}
                            @endif
                        </div>
                        <div style="width: 33%;float: left;">
                            Volume : <?php echo !empty($model->volume) ? $model->volume : '-'; ?>
                            @if($model->measure_volume == 'm')
                            {{!empty($model->volume) ? 'Cubic meter' : ''}}
                            @else
                            {{'Cubic feet'}}
                            @endif
                        </div>
                        <div style="width: 33%;float: left;">
                            Pieces : <?php echo !empty($model->pieces) ? (int) $model->pieces : '-'; ?>
                        </div>
                    </div>
                </div>

                <div style="width: 100%;margin-bottom: 0px;font-size: 13px;font-weight: normal;">
                    <table class="table table-bordered" style="border:2px solid #000;font-family: sans-serif;font-weight: normal;font-size: 11px;color:#000;" width="100%" cellpadding="0px" cellspacing="0px">
                        <thead>
                            <tr>
                                <th style="border-bottom:2px solid #000;border-right:1px solid #211e1e;vertical-align:top;text-align: center;width: 12%;font-weight: bold;" rowspan="2">DATE</th>
                                <th style="border-bottom:2px solid #000;border-right:1px solid #211e1e;vertical-align:top;text-align: center;width: 45%" rowspan="2">DESCRIPTION</th>
                                <th style="border-right:1px solid #211e1e;vertical-align:top;text-align: center;">DEPENSES</th>
                                <th style="border-bottom:1px solid #000;border-right:1px solid #211e1e;;text-align: center;padding: 1px" colspan="2">A FACTURER</th>
                                <th style="text-align: center;vertical-align:top;">CREDIT</th>
                            </tr>
                            <tr>
                                <th style="text-align: center;border-right:1px solid #211e1e;border-bottom:2px solid #000;">HTG/USD</th>
                                <th style="text-align: center;border-right:1px solid #211e1e;border-bottom:2px solid #000;">HTG</th>
                                <th style="text-align: center;border-right:1px solid #211e1e;border-bottom:2px solid #000;padding: 1px">USD</th>
                                <th style="text-align: center;border-bottom:2px solid #000;">HTG/USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-bottom: 1px solid #211e1e;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-right:1px solid #211e1e;padding: 5px">&nbsp;</td>
                                <td style="border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="border-right:1px solid #211e1e;">&nbsp;</td>
                                <td style="">&nbsp;</td>
                            </tr>

                        </tbody>
                    </table>
                </div>


                <div style="border: 2px solid #000;padding: 0px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 0px;">
                        <div style="line-height:10px;width: 100%;border-bottom: 2px solid #000;text-align: center;font-weight: bold;padding: 5px">EXPLICATIONS / INFORMATIONS</div>

                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;"><?php echo !empty($model->information) ? $model->information : '-'; ?></div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
                        <div style="padding:5px;width: 100%;">&nbsp;</div>
                    </div>
                </div>


                <div style="border: 2px solid #ccc;padding: 0px;margin-bottom: 15px;display: none">
                    <div style="width: 100%;margin-bottom: 0px;">
                        <div style="width: 100%;border-bottom: 2px solid #ccc;text-align: center;font-weight: bold;line-height:20px;padding: 5px">Nature du service</div>
                        <div style="padding:7px;width: 100%;border-bottom: 1px solid #ccc;">&nbsp;</div>
                        <div style="padding:7px;width: 100%;border-bottom: 1px solid #ccc;">&nbsp;</div>
                        <div style="padding:7px;width: 100%;border-bottom: 1px solid #ccc;">&nbsp;</div>
                        <div style="padding:7px;width: 100%;border-bottom: 1px solid #ccc;">&nbsp;</div>
                        <div style="padding:7px;width: 100%;">&nbsp;</div>
                    </div>
                </div>


            </div>
        </div>
    </section>
</body>

</html>