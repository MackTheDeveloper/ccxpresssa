<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #636b6f">
            
            <?php if(!empty($cargoExpenseData)) { 
                    $kv = 1; 
                    foreach($cargoExpenseData as $ko => $vo)
                        {  $vo = (object) $vo; 
                           $cargoData = App\Cargo::where('id',$vo->cargo_id)->first();
                            $cargoExpenseDetailsData = DB::table('expense_details')->where('voucher_number',$vo->voucher_number)->where('expense_id',$vo->expense_id)->where('deleted',0)->orderBy('id', 'desc')->get();
                            ?>

                            <?php if(!empty($cargoExpenseDetailsData)) {  ?>
                           

                                    <div class="mainblk" style="margin-top: 20px; float: none;  width: 842px; padding-bottom: 20px; margin: 0 auto;">
                                    
                                        <div class="blockleft" style="margin-bottom: 20px; width: 100%; float: left; padding: 0 20px 0 20px; box-sizing: border-box; border-left: 1px dashed #ccc; border-right: 1px dashed #ccc;">                                            
                                               
                                               <div style="float: left;width: 100%; margin: 20px 0 30px 0;">
                                                    <div style="text-align: left;float: left; width: 50%; font-size: 24px; font-weight: 600;text-transform: uppercase;">
                                                    Chatelain Cargo Services
                                                    </div>
                                                    <div style="width: 50%; float: left;text-align: right; font-size: 18px; margin-top: 9px; color: #f30101;">
                                                    <?php echo $vo->voucher_number; ?>
                                                    </div>
                                                </div>
                                                <div style="float: left;width: 50%; margin-bottom: 10px;">
                                                    <div style="float: left; width: 50%; font-weight: 600;">Date : </div>
                                                    <div style="float: left;  width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php echo $vo->exp_date; ?></div>
                                                </div>                      
                                                <div style="float: left;width: 50%; margin-bottom: 10px;">
                                                    <div style="float: left; width: 50%; font-weight: 600;">File number/DOSSIER : </div>
                                                    <div style="float: left;  width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php echo $cargoData->file_number; ?></div>
                                                </div>
                                                <div style="float: left;width: 50%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 50%; font-weight: 600;">BL or AWB No. : </div>
                                                    <div style="float: left;  width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php echo $vo->bl_awb; ?></div>
                                                </div>                    
                                                <div style="float: left;width: 50%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 50%; font-weight: 600;">Consignee : </div>
                                                    <div style="float: left; width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php echo $vo->consignee; ?></div>
                                                </div>                       
                                                <div style="float: left;width: 50%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 50%; font-weight: 600;">Expediteur/Shipper : </div>
                                                    <div style="float: left; width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php echo $vo->shipper; ?></div>
                                                </div>                         
                                                <div style="float: left;width: 50%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 50%; font-weight: 600;">Type : </div>
                                                    <div style="float: left; width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;">
                                                        <?php echo Config::get('app.cargoOperationType')[$cargoData->cargo_operation_type];  ?></div>
                                                </div>
                                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 25%; font-weight: 600;">Note : </div>                            
                                                    <div style="float: left; width: 68%;"><?php echo $vo->note; ?></div>
                                                </div>   
                                                
                                       

                                            <h3 style="background: #ccc; padding: 5px; font-weight: normal; display: inline-block; width: 100%; text-align: center; box-sizing: border-box;
                                            margin-bottom: 0;">Cargo Expense</h3>
                                        <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #333;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse;width: 100%">
                                        <thead>
                                            <tr>
                                                <th width="50px" style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Sr No.</th>
                                                <th width="100px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Cost A/C</th>
                                                <th width="220px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Description</th>
                                                <th width="70px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Amount</th>
                                                <th width="70px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Cash A/C</th>
                                                <th width="110px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Paid to</th>
                                            </tr>
                                        </thead>


                                        <tbody>
                                            <?php if(count($cargoExpenseDetailsData)  > 0) { $ik = 1; foreach($cargoExpenseDetailsData as $k => $v)
                                            { 
                                                $v = (object) $v;
                                                ?>
                                                <tr>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $ik; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php $costData = app('App\Costs')->getCostData($v->expense_type); 
                                                            echo !empty($costData->cost_name) ? $costData->cost_name : "-";
                                                    ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->description) ? $v->description : '-'; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->amount) ? $v->amount : '0.00'; ?></td>
                                                     <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->cash_credit_account; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php $dataUser = app('App\User')->getUserName($v->paid_to); 
                                                        echo !empty($dataUser->name) ? $dataUser->name : "-";?></td>
                                                </tr>
                                                <?php $ik++; } } else { ?>
                                                    <tr><td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;" colspan="6">No data found</td></tr>
                                                    <?php } ?>
                                        </tbody>
                                    </table>
                                            <div style="float: left; width: 100%; margin-top: 70px; bottom: 0; left: 20px;border-bottom: 1px solid #ccc; padding: 15px 0;;">
                                                    <span><?php $dataClient = app('App\Clients')->getClientData($vo->billing_party); echo !empty($dataClient->name) ? $dataClient->name : "-";
                                                    ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if($kv < count($cargoExpenseData)) { ?><pagebreak></pagebreak><?php } ?>

                            <?php  } ?>
                    <?php $kv++; } ?>  <?php } ?>


             
        </div>
    </div>
</section>


