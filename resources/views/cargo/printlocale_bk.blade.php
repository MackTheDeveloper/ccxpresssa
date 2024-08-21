<!DOCTYPE html>
<html>
<head>
    <title>Locale File</title>
</head>
<body>
<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #636b6f">
            <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                    <div style="float: left;width: 50%">Cargo  : {{$model->file_number}}</div>
            </h3>
            <div style="margin-left: 10%;margin-bottom: 5%">
                <div class="" style="width: 20%;float: left;">
                    {{ Form::image('images/invoice_logo.png', 'alt text', array('class' => 'css-class')) }}
                </div>
                <div class="" style="width: 60%;float: left;text-align: center;">
                        <h3 style="text-align: center;font-weight: bold;font-size: 25px;font-style: italic;margin-top: 0px;">Chatelain Cargo Services S.A</h3>
                        
                            <div style="width: 100%;text-align: center;font-size: 10px">Aeroport International de Port-au-Prince, P.O.Box 1056 Port-au-Prince, Haiti</div>
                            <div style="width: 100%;float: left;text-align: center;font-size: 10px">Tel: (509) 250-1652 a 250-1656, Fax: (509) 250-3898(P-A-P)</div>
                            <div style="width: 100%;text-align: center;font-size: 10px">Fax: (1-305) 436-3793(U.S.A)</div>
                            <div style="width: 100%;float: left;text-align: center;font-size: 10px">Email: pvc@chatelaincargo.com</div>
                        
                    </div>
            </div>
            <div style="border: 1px solid #ccc;margin-bottom: 5%;padding: 2%">
                <div style="width: 100%;background-color: #ccc;padding: 1%"><b>BASIC DETAILS</b></div>
                <div style="margin-left: 1%">
            <div style="width: 50%;margin-bottom: 8px;float: left;margin-top: 2%">
                    <div style="width: 50%;float: left;"><b>Opening Date :</b></div>  
                    <div style="width: 40%;float: left;"><?php echo !empty($model->opening_date) ? date('d-m-Y',strtotime($model->opening_date)) : '-'; ?></div>
            </div>
            <div style="width: 50%;margin-bottom: 8px;">
                <div style="width: 50%;float: left;"><b>Ending Date :</b></div>  
                    <div style="width: 40%;float: left;"><?php echo !empty($model->rental_ending_date) ? date('d-m-Y',strtotime($model->rental_ending_date)) : '-'; ?></div>
            </div>
            <div style="width: 50%;margin-bottom: 8px;float: left">
                <div style="width: 50%;float: left;"><b>Rental :</b></div>  
                <div style="width: 40%;float: left;"><?php echo !empty($model->rental) ? 'Yes' : 'No'; ?></div>
            </div>
            <div style="width: 50%;margin-bottom: 8px;<?php echo !empty($model->rental) ? 'dispaly:block' : 'display:none'; ?>">
                    <div style="width: 50%;float: left;"><b>Rental Cost :</b></div>  
                    <div style="width: 40%;float: left;"><?php echo !empty($model->rental_cost) ? $model->rental_cost : '-'; ?>
                    </div>
            </div>
            <div style="width: 50%;margin-bottom: 8px;float: left">
                <div style="width: 50%;float: left;"><b>Client :</b></div>  
                <div style="width: 40%;float: left;"><?php echo !empty($model->consignee_name) ? $model->consignee_name : '-'; ?>
                </div>
            </div>

            <div style="width: 50%;margin-bottom: 8px;">
                <div style="width: 50%;float: left;"><b>Agent :</b></div>  
                <div style="width: 40%;float: left;">
                    <?php 
                    $data = app('App\User')->getUserName($model->agent_id); 
                    echo !empty($data->name) ? $data->name : '-'; ?>
                </div>
            </div>

            <div style="width: 50%;margin-bottom: 8px;float: left">
                <div style="width: 50%;float: left;"><b>Address :</b></div>  
                <div style="width: 40%;float: left;"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></div>
            </div>
            <div style="width: 50%;margin-bottom: 8px;">
                <div style="width: 50%;float: left;"><b>AWB/BL No :</b></div>  
                <div style="width: 40%;float: left;"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></div>
            </div>
            <div style="width: 50%;margin-bottom: 8px;float: left">
                <div style="width: 50%;float: left;"><b>Paid :</b></div>
                <?php 
                    if($model->rental_paid_status == 'p'){
                        $status = 'Yes';
                    } else {
                        $status = 'No';
                    }
                ?>  
                <div style="width: 40%;float: left;">{{$status}}</div>
            </div>
        </div>
        </div>
            

            <div style="border: 1px solid #ccc;padding: 10px;margin-bottom: 15px;">
                <div style="width: 100%;margin-bottom: 15px;">
                    <div><b>EXPLICATIONS / INFORMATIONS</b></div>
                </div>
                <div>
                    <?php echo $model->information; ?>
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

            
                    
                    
             
        </div>
    </div>
</section>

</body>
</html>
