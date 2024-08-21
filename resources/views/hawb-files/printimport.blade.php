<!DOCTYPE html>
<html>
<head>
    <title>IMPORTATIONS HOUSE</title>
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
                    <h2 style="margin:0px">IMPORTATIONS HOUSE</h2>
                    <h3 style="margin:0px;margin-top:20px;">CHATELAIN CARGO SERVICES</h3>
                </div>
                <div style="width:20%;float:left;color:#0000;text-align: center;">
                    <h4 style="margin:0px;text-align: right;">No DOSSIER</h4>
                    <h3 style="margin-top:5px;text-align: right;font-weight: normal;">{{$model->file_number}}</h3>
                </div>
            </div>
            
            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                <div style="width: 20%;float: left;">Master File Number :</div>  
                <div style="width: 80%;float: left;border-bottom: 1px dotted #000"><?php $dataConsolidate  = DB::table('cargo')
                                            ->select(DB::raw('group_concat(file_number) as MasterFiles'))
                                             ->whereRaw("find_in_set($model->id,hawb_hbl_no)")
                                            ->first();
                        echo !empty($dataConsolidate->MasterFiles) ? $dataConsolidate->MasterFiles : 'Not Assigned';
                    ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                <div style="width: 20%;float: left;">House AWB No :</div>  
                <div style="width: 80%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->hawb_hbl_no) ? $model->hawb_hbl_no : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                <div style="width: 20%;float: left;">Consignataire - Nom :</div>  
                <div style="width: 80%;float: left;border-bottom: 1px dotted #000"><b><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-'; ?></b></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                <div style="width: 20%;float: left;">Adresse :</div>  
                <div style="width: 80%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                <div style="width: 20%;float: left;">EXPEDITEUR :</div>  
                <div style="width: 80%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></div>
            </div>

            

            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                <div style="width: 50%;float: left;">
                    <div style="width: 40%;float: left;">DATE D'ARRIVEE :</div>  
                    <div style="width: 60%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->arrival_date) ? date('d-m-Y',strtotime($model->arrival_date)) : '-'; ?></div>
                </div>

                <div style="width: 50%;float: left;">
                    <div style="width: 40%;float: left;text-align: center;">Date d'ouverture :</div>  
                    <div style="width: 60%;float: left;border-bottom: 1px dotted #000"><?php echo !empty($model->opening_date) ? date('d-m-Y',strtotime($model->opening_date)) : '-'; ?></div>
                </div>
                
            </div>

            <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">
                    
                        <?php $data = app('App\HawbPackages')::getData($model->id); ?>
                        <div style="width: 50%;float: left;">
                            <div style="width: 40%;float: left;">Weight :</div>  
                            <div style="width: 60%;float: left;border-bottom: 1px dotted #000">
                                 <?php if(!empty($data)) { echo !empty($data->pweight) ? $data->pweight : '-'; ?> 
                                    @if($data->measure_weight == 'k')
                                        {{!empty($data->pweight) ? 'Kg' : ''}}
                                    @else 
                                        {{'Pound'}}
                                    @endif
                                    <?php } else { echo "-"; } ?>
                            </div>
                        </div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">

                        <div style="width: 50%;float: left;">
                            <div style="width: 40%;float: left;">Volume :</div>  
                            <div style="width: 60%;float: left;border-bottom: 1px dotted #000">
                                 <?php if(!empty($data)) { echo !empty($data->pvolume) ? $data->pvolume : '-'; ?> 
                                    @if($data->measure_volume == 'm')
                                        {{!empty($data->pvolume) ? 'Cubic meter' : ''}}
                                    @else
                                        {{'Cubic feet'}}
                                    @endif
                                    <?php } else { echo "-"; } ?>
                            </div>
                        </div>

                </div>

                <div style="width: 100%;margin-bottom: 15px;font-size: 13px;font-weight: normal;">                        

                        <div style="width: 50%;float: left;">
                            <div style="width: 40%;float: left;">Pieces :</div>  
                            <div style="width: 60%;float: left;border-bottom: 1px dotted #000">
                                 <?php echo !empty($data->ppieces) ? $data->ppieces : '-'; ?>
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

            <div style="line-height:10px;width: 100%;text-align: center;font-weight: bold;padding: 5px">OBSERVATIONS</div>
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
            

           

            
                    
                    
             
        </div>
    </div>
</section>
</body>
</html>

