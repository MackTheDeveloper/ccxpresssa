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
                <div style="width: 20%;float: left;margin-left: 10px">
                    <div style="width: 35%;float: left;text-align: center;">Date :</div>  
                    <div style="width: 65%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->opening_date) ? date('d-m-Y',strtotime($model->opening_date)) : '-'; ?></div>
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
                <div style="width: 100%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></div>
            </div>


            <div style="width: 100%;margin-bottom: 10px;margin-top: 30px;font-size: 13px;font-weight: normal;">
                <div style="width: 50%;float: left;">
                    <div style="width: 30%;float: left;">Poids des colis  :</div>  
                   <?php $data = app('App\CargoPackages')::getData($model->id); ?>
                        <div style="width: 70%;float: left;border-bottom: 1px dotted #000">
                        <?php if(!empty($data)) { echo !empty($data->pweight) ? $data->pweight : '-'; ?> 
                            @if($data->measure_weight == 'k')
                                {{!empty($data->pweight) ? 'Kg' : ''}}
                            @else 
                                {{!empty($data->pweight) ? 'Pound' : ''}}
                            @endif
                            <?php } else { echo "-"; } ?>
                        </div>
                </div>
                <div style="width: 50%;float: left;margin-left: 10px">
                    <div style="width: 30%;float: left;text-align: center;">Nbre de Colis :</div>  
                    <div style="width: 70%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->no_of_pieces) ? $model->no_of_pieces : '-'; ?></div>
                </div>
            </div>

            <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                <div style="width: 16%;float: left;">Nature des colis :</div>  
                <div style="width: 84%;float: left;border-bottom: 1px dotted #000;margin-bottom: 5px">&nbsp;</div>
            </div>

            

            <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                <div style="width: 30%;float: left;">
                    <div style="width: 32%;float: left;">AWB/BL :</div>  
                    <div style="width: 67%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></div>
                </div>
                <div style="width: 70%;float: left;margin-left: 10px">
                    <div style="width: 18%;float: left;">HAWB / HBL :</div>  
                    <div style="width: 82%;float: left;border-bottom: 1px dotted #000">
                         <?php $dataConsolidate  = DB::table('hawb_files')
                                            ->select(DB::raw('group_concat(export_hawb_hbl_no) as Hawbnumbers'))
                                            ->whereIn('id',explode(',',$model->hawb_hbl_no))
                                            ->first();
                        echo !empty($dataConsolidate->Hawbnumbers) ? $dataConsolidate->Hawbnumbers : '-' ;
                    ?>
                    </div>
                </div>
            </div>

            <div style="width: 100%;margin-bottom: 20px;font-size: 13px;font-weight: normal;">
                <div style="width: 7%;float: left;">#CTN :</div>  
                <div style="width: 93%;float: left;border-bottom: 1px dotted #000">&nbsp;</div>
            </div>

             <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 10px;font-size: 13px;font-weight: normal;display: none;">
            <div style="width: 35%;margin-bottom: 0px;float: left;">
                <div style="width: 60%;float: left;">Consolidation :</div>  
                <div style="width: 40%;float: left;"><?php echo (!empty($model->consolidate_flag) && $model->consolidate_flag == 1) ? 'Yes' : 'No'; ?></div>
            </div>

            <?php if((!empty($model->consolidate_flag) && $model->consolidate_flag == 1)) { ?>
            <div style="width: 65%;margin-bottom: 0px;">
                <div style="width: 30%;float: left;">House AWB Files :</div>  
                <div style="width: 70%;float: left;">
                    <?php $dataConsolidate  = DB::table('hawb_files')
                                            ->select(DB::raw('group_concat(export_hawb_hbl_no) as Hawbnumbers'))
                                            ->whereIn('id',explode(',',$model->hawb_hbl_no))
                                            ->first();
                        echo $dataConsolidate->Hawbnumbers;
                    ?>
                </div>
            </div>
            <?php } ?>
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

            <div style="padding:0px;margin-bottom: 10px;">
                <div style="width: 100%;margin-bottom: 0px;">
                    <div style="line-height:35px;width: 100%;text-align: center;font-weight: bold">OBSERVATIONS</div>
                </div>
                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 12%;float: left;">EXPEDIE LE :</div>  
                    <div style="width: 88%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->sent_on) ? date('d-m-Y',strtotime($model->sent_on)) : '-'; ?></div>
                </div>
                <div style="width: 100%;margin-bottom: 10px;font-size: 13px;font-weight: normal;">
                    <div style="width: 6%;float: left;">PAR :</div>  
                    <div style="width: 94%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->sent_by) ? $model->sent_by : '-'; ?></div>
                </div>
                
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

