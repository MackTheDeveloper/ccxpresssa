<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #636b6f">
            
            <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="float: left;width: 50%">Release Receipt  - Cargo  : {{$model->file_number}}</div>
            </h3>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Consignee Name :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->consignee_name) ? $model->consignee_name : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Address :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Shipper :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipper_name) ? $model->shipper_name : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Agent :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">
                    <?php 
                    $data = app('App\User')->getUserName($model->agent_id); 
                    echo !empty($data->name) ? $data->name : '-'; ?>
                </div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 50%;float: left;">
                    <div style="width: 40%;float: left;">Opening Date :</div>  
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->opening_date) ? date('d-m-Y',strtotime($model->opening_date)) : '-'; ?></div>
                </div>
                <div style="width: 50%;float: left;margin-left: 10px">
                    <div style="width: 40%;float: left;">Arrival Date :</div>  
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->arrival_date) ? date('d-m-Y',strtotime($model->arrival_date)) : '-'; ?></div>
                </div>
            </div>

            

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 30%;float: left;">Shipment Delivered Date :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipment_delivered_date) ? date('d-m-Y',strtotime($model->shipment_delivered_date)) : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">AWB/BL No :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></div>
            </div>

            
            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Storage Charges :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($storageCharge) ? $storageCharge : '0.00'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">No. Of Packages :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($noOfPackageData) ? $noOfPackageData : '-'; ?></div>
            </div>

            <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                <div style="width: 100%;margin-bottom: 15px;">
                    <div><b><?php echo $model->flag_package_container == 1 ? 'Package' : 'Container'; ?></b></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <?php if($model->flag_package_container == 1) { ?>
                        <?php $data = app('App\CargoPackages')::getData($model->id); ?>
                        <div style="width: 33%;float: left;">
                            Weight : <?php echo !empty($data->pweight) ? $data->pweight : '-'; ?>
                        </div>
                        <div style="width: 33%;float: left;">
                            Volume : <?php echo !empty($data->pvolume) ? $data->pvolume : '-'; ?>
                        </div>
                        <div style="width: 33%;float: left;">
                            Pieces : <?php echo !empty($data->ppieces) ? $data->ppieces : '-'; ?>
                        </div>

                    <?php } else { ?>
                        <div style="width: 50%;float: left;">
                            <div style="width: 40%;float: left;">No. of container :</div>  
                            <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo (isset($model->no_of_container)) ? $model->no_of_container : '-'; ?></div>
                        </div>
                        <div style="width: 50%;float: left;margin-left: 10px">
                            <?php $data = app('App\CargoContainers')::getData($model->id); 
                                echo !empty($data) ? $data->containerNumbers : "-";?>
                        </div>  
                    <?php } ?>
                </div>
            </div>


               <?php if(count($missingPackage) != 0) { ?> 
             <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                <div style="width: 100%;margin-bottom: 15px;">
                    <div><b>Missing Packages</b></div>
                </div>
                <?php foreach ($missingPackage as $key => $value) { ?>
                <div style="width: 100%;margin-bottom: 15px;">
                    <div style="width: 20%;float: left;">House AWB No :</div>  
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">
                        <?php echo $value->cargo_operation_type == 1 ? $value->hawb_hbl_no : $value->export_hawb_hbl_no; ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>


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
                    <br/>
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
        <div class="box-body cargo-forms" style="color: #636b6f">
            
            <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="float: left;width: 50%">Release Receipt  - Cargo  : {{$model->file_number}}</div>
            </h3>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Consignee Name :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->consignee_name) ? $model->consignee_name : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Address :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Shipper :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->shipper_name) ? $model->shipper_name : '-'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Agent :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc">
                    <?php 
                    $data = app('App\User')->getUserName($model->agent_id); 
                    echo !empty($data->name) ? $data->name : '-'; ?>
                </div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 50%;float: left;">
                    <div style="width: 40%;float: left;">Opening Date :</div>  
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->opening_date) ? date('d-m-Y',strtotime($model->opening_date)) : '-'; ?></div>
                </div>
                <div style="width: 50%;float: left;margin-left: 10px">
                    <div style="width: 40%;float: left;">Arrival Date :</div>  
                    <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->arrival_date) ? date('d-m-Y',strtotime($model->arrival_date)) : '-'; ?></div>
                </div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">AWB/BL No :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></div>
            </div>

             <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">Storage Charges :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($storageCharge) ? $storageCharge : '0.00'; ?></div>
            </div>

            <div style="width: 100%;margin-bottom: 15px;">
                <div style="width: 20%;float: left;">No. Of Packages :</div>  
                <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo !empty($noOfPackageData) ? $noOfPackageData : '-'; ?></div>
            </div>

            <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                <div style="width: 100%;margin-bottom: 15px;">
                    <div><b><?php echo $model->flag_package_container == 1 ? 'Package' : 'Container'; ?></b></div>
                </div>

                <div style="width: 100%;margin-bottom: 15px;">
                    <?php if($model->flag_package_container == 1) { ?>
                        <?php $data = app('App\CargoPackages')::getData($model->id); ?>
                        <div style="width: 33%;float: left;">
                            Weight : <?php echo !empty($data->pweight) ? $data->pweight : '-'; ?>
                        </div>
                        <div style="width: 33%;float: left;">
                            Volume : <?php echo !empty($data->pvolume) ? $data->pvolume : '-'; ?>
                        </div>
                        <div style="width: 33%;float: left;">
                            Pieces : <?php echo !empty($data->ppieces) ? $data->ppieces : '-'; ?>
                        </div>

                    <?php } else { ?>
                        <div style="width: 50%;float: left;">
                            <div style="width: 40%;float: left;">No. of container :</div>  
                            <div style="width: 40%;float: left;border-bottom: 1px solid #ccc"><?php echo (isset($model->no_of_container)) ? $model->no_of_container : '-'; ?></div>
                        </div>
                        <div style="width: 50%;float: left;margin-left: 10px">
                            <?php $data = app('App\CargoContainers')::getData($model->id); 
                                echo !empty($data) ? $data->containerNumbers : "-";?>
                        </div>  
                    <?php } ?>
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
                    <br/>
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