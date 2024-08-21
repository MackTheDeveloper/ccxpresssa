<!DOCTYPE html>
<html>

<head>
    <title>Import File</title>
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
                        <h2 style="margin:0px">IMPORTATIONS</h2>
                        <h3 style="margin-top:20px;font-weight: normal;">CHATELAIN CARGO SERVICES</h3>
                    </div>
                    <div style="width:20%;float:left;color:#0000;text-align: center;">
                        <h4 style="margin:0px;text-align: right;">No DOSSIER</h4>
                        <h3 style="margin-top:5px;text-align: right;font-weight: normal;">{{$model->file_number}}</h3>
                    </div>
                </div>



                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 20%;float: left;">Consignataire - Nom :</div>
                    <div style="width: 80%;float: left;border-bottom: 1px dotted #000"><b><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-'; ?></b></div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 9%;float: left;">Adresse :</div>
                    <div style="width: 91%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></div>
                    <div style="width: 100%;float: left;border-bottom: 1px dotted #000">&nbsp;</div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 47%;float: left;">
                        <div style="width: 28%;float: left;">EXPEDITEUR :</div>
                        <div style="width: 72%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></div>
                    </div>
                    <div style="width: 6%;float: left;">&nbsp;</div>
                    <div style="width: 47%;float: left;">
                        <div style="width: 25%;float: left;">AWB/BL no :</div>
                        <div style="width: 75%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></div>
                    </div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 47%;float: left;">
                        <div style="width: 40%;float: left;">NOMBRE D'UNITES :</div>
                        <div style="width: 60%;float: left;border-bottom: 1px dotted #000">-</div>
                    </div>
                    <div style="width: 6%;float: left;">&nbsp;</div>
                    <div style="width: 47%;float: left;">
                        <div style="width: 36%;float: left;">Date d'ouverture :</div>
                        <div style="width: 64%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->opening_date) ? date('d-m-Y', strtotime($model->opening_date)) : '-'; ?></div>
                    </div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 50%;float: left;">
                        <div style="width: 33%;float: left;">DATE D'ARRIVEE :</div>
                        <div style="width: 61%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-'; ?></div>
                    </div>
                </div>

                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 34%;float: left;">NATURE OU DESCRIPTION DES COLIS :</div>
                    <div style="width: 66%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px">&nbsp;</div>
                    <div style="width: 100%;float: left;border-bottom: 1px dotted #000">&nbsp;</div>
                </div>



                <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 25%;margin-bottom: 0px;float: left;">
                        <div style="width: 60%;float: left;">Consolidation :</div>
                        <div style="width: 39%;float: left;"><?php echo (!empty($model->consolidate_flag) && $model->consolidate_flag == 1) ? 'Yes' : 'No'; ?></div>
                    </div>

                    <?php if ((!empty($model->consolidate_flag) && $model->consolidate_flag == 1)) { ?>
                        <div style="width: 75%;margin-bottom: 0px;font-size: 14px;font-weight: normal;">
                            <div style="width: 25%;float: left;">House AWB Files :</div>
                            <div style="width: 72%;float: left;">
                                <?php $dataConsolidate  = DB::table('hawb_files')
                                    ->select(DB::raw('group_concat(hawb_hbl_no) as Hawbnumbers'))
                                    ->whereIn('id', explode(',', $model->hawb_hbl_no))
                                    ->first();
                                echo $dataConsolidate->Hawbnumbers;
                                ?>
                            </div>
                        </div>
                    <?php } ?>



                    <div style="width: 10%;margin-bottom: 0px;margin-top: 5px;float: left;clear: both;">
                        <div><b><?php echo $model->flag_package_container == 1 ? 'Package' : 'Container'; ?></b></div>
                    </div>

                    <div style="width: 90%;margin-bottom: 0px;float: left;">
                        <?php if ($model->flag_package_container == 1) { ?>
                            <?php $data = app('App\CargoPackages')::getData($model->id); ?>
                            <div style="width: 33%;float: left;">
                                Weight : <?php if (!empty($data)) { ?> <?php echo !empty($data->pweight) ? $data->pweight : '-'; ?>
                                    @if($data->measure_weight == 'k')
                                    {{!empty($data->pweight) ? 'Kg' : ''}}
                                    @else
                                    {{'Pound'}}
                                    @endif
                                <?php } else {
                                                echo "";
                                            } ?>
                            </div>
                            <div style="width: 33%;float: left;">
                                Volume : <?php if (!empty($data)) { ?><?php echo !empty($data->pvolume) ? $data->pvolume : '-'; ?>
                                @if($data->measure_volume == 'm')
                                {{!empty($data->pvolume) ? 'Cubic meter' : ''}}
                                @else
                                {{'Cubic feet'}}
                                @endif
                            <?php } else {
                                                echo "";
                                            } ?>
                            </div>
                            <div style="width: 33%;float: left;">
                                Pieces : <?php echo !empty($data->ppieces) ? (int) $data->ppieces : '-'; ?>
                            </div>

                        <?php } else { ?>
                            <div style="width: 25%;float: left;margin-left: 5%;">
                                <div style="width: 75%;float: left;">No. of Container :</div>
                                <div style="width: 20%;float: left;"><?php echo (isset($model->no_of_container)) ? $model->no_of_container : '-'; ?></div>
                            </div>
                            <div style="width: 40%;float: left;">
                                <div style="width: 38%;float: left;">Container No :</div>
                                <div style="width: 60%;float: left;"><?php $data = app('App\CargoContainers')::getData($model->id);
                                                                        echo !empty($data) ? $data->containerNumbers : "-"; ?>
                                </div>
                            </div>
                        <?php } ?>
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

                <div style="width: 100%;text-align: center;font-weight: bold;padding: 3px">OBSERVATIONS</div>
                <div style="border: 2px solid #000;padding: 0px;margin-bottom: 15px;">
                    <div style="width: 100%;margin-bottom: 0px;">
                        <div style="line-height:10px;width: 100%;border-bottom: 2px solid #000;text-align: center;font-weight: bold;padding: 5px">EXPLICATIONS / INFORMATIONS</div>

                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;"><?php echo !empty($model->information) ? $model->information : '&nbsp;'; ?></div>
                        <div style="padding:5px;width: 100%;border-bottom: 1px solid #211e1e;">&nbsp;</div>
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
                <br />
            </div>
        </div>





        </div>
        </div>
    </section>
</body>

</html>