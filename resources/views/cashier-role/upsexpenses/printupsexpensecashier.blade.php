<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">
            
            <?php if(!empty($cargoExpenseData)) { 
                    $kv = 1; 
                    foreach($cargoExpenseData as $ko => $vo)
                        {  $vo = (object) $vo; 
                           $cargoData = App\Ups::where('id',$vo->ups_details_id)->first();
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
                                                    <div style="float: left;  width: 38%; border-bottom: 1px solid #ccc; padding-bottom: 4px;">
                                                        <?php echo date('d-m-Y',strtotime($vo->exp_date)); ?></div>
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
                                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 25%; font-weight: 600;">Cash/Bank : </div>                            
                                                    <div style="float: left; width: 50%;border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php $currencyData = App\CashCredit::getCashCreditData($vo->cash_credit_account); echo !empty($currencyData) ? '('.$currencyData->currency_code.')'.' '.$currencyData->name : '-'; ?></div>
                                                </div>
                                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                                    <div style="float: left;  width: 25%; font-weight: 600;">Note : </div>                            
                                                    <div style="float: left; width: 50%;border-bottom: 1px solid #ccc; padding-bottom: 4px;"><?php echo $vo->note; ?></div>
                                                </div>   
                                                
                                       

                                            <h3 style="background: #ccc; padding: 5px; font-weight: normal; display: inline-block; width: 100%; text-align: center; box-sizing: border-box;
                                            margin-bottom: 0;">Cargo Expense</h3>
                                        <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse;width: 100%">
                                        <thead>
                                            <tr>
                                                <th width="80px" style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Sr No.</th>
                                                <th width="320px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Description</th>
                                                <th width="90px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Amount</th>
                                                <th width="145px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Paid to</th>
                                            </tr>
                                        </thead>


                                        <tbody>
                                            <?php if(count($cargoExpenseDetailsData)  > 0) { $ik = 1; foreach($cargoExpenseDetailsData as $k => $v)
                                            { 
                                                $v = (object) $v;
                                                ?>
                                                <tr>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $ik; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->description) ? $v->description : '-'; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->amount) ? $v->amount : '0.00'; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php $dataUser = app('App\Vendors')->getVendorData($v->paid_to); 
                                                        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                                                </tr>
                                                <tr>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;height: 30px"></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                                                </tr>
                                                <?php $ik++; } } else { ?>
                                                    <tr><td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;" colspan="6">No data found</td></tr>
                                                    <?php } ?>
                                        </tbody>
                                    </table>
                                            <div style="float: left; width: 20%; margin-top: 30px; bottom: 0; left: 20px;border-bottom: 1px solid #ccc; padding-bottom:10px;">
                                                    <div style="width: 100%;float: left;margin-top: 20px;">
                                                    <div style="padding-bottom: 30px">Signature</div>
                                                    </div>
                                            </div>
                                            <div style="float: right; width: 50%; margin-top: 0px; bottom: 0; left: 20px;">
                                                <div style="width: 100%;float: left;margin-bottom: 10px">
                                                    <div style="width: 40%;float: left;margin-right: 50px">Request by</div>
                                                    <div style="width: 50%;float: left;border-bottom: 1px solid #ccc;margin-right: 20px"><b><?php $modelUser = new App\User(); 
                                                    $dataUser = $modelUser->getUserName($vo->request_by); echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                                    </b></div>
                                                </div>
                                                <div style="width: 100%;float: left;margin-bottom: 10px">
                                                    <div style="width: 40%;float: left;">Approved by</div>
                                                    <div style="width: 50%;float: left;border-bottom: 1px solid #ccc"><b><?php $modelUser = new App\User(); 
                                                    $dataUser = $modelUser->getUserName($vo->approved_by); echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                                    </b></div>
                                                </div>
                                                <div style="width: 100%;float: left;margin-bottom: 10px">
                                                    <div style="width: 40%;float: left;">Disbursed by</div>
                                                    <div style="width: 50%;float: left;border-bottom: 1px solid #ccc"><b><?php $modelUser = new App\User(); 
                                                    $dataUser = $modelUser->getUserName($vo->disbursed_by); echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                                    </b></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if($kv < count($cargoExpenseData)) { ?><pagebreak></pagebreak><?php } ?>

                            <?php  } ?>
                    <?php $kv++; } ?>  <?php } ?>


             
        </div>
    </div>
</section>


