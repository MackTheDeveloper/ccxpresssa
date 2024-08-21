<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>
<head>
    <title>File Expenses</title>
</head>
<body>
        <section class="content" style="font-family: sans-serif;">

                <div class="box box-success" style="width: 100%;margin: 0px auto;">
                    <div class="box-body cargo-forms" style="color: #000">
                        <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                        <div style="width:50%;float:left">File Expenses</div>
                        <div style="width:48%;float:left;text-align:right"><?php echo $flag != 'all' ? $cargoData->file_number : ''; ?></div>
                        </h3>
                        
                        
                        <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                            <thead>
                                <tr>
                                    <th width="50px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Date</th>
                                    <th width="50px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">File Number</th>
                                    <th width="50px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Voucher No.</th>                       
                                    <th width="110px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Consignee</th>
                                    <th width="110px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Shipper</th>                                 
                                    <th width="50px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Currency</th>                                 
                                    <th width="80px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Total Amount</th>               
                                </tr>
                            </thead>
                        <tbody>
                            <?php if(!empty($cargoExpenseData)) { 
                                foreach($cargoExpenseData as $k => $v)
                                    {
                                       
                                    ?>
                            <tr>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo date('d-m-Y',strtotime($v->exp_date)); ?></td>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->file_number; ?></td>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->voucher_number; ?></td>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->consignee; ?></td>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->shipper; ?></td>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php $dataCurrency = App\Vendors::getDataFromPaidTo($v->expense_id); echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                                        <?php echo App\Expense::getExpenseTotal($v->expense_id);  ?>
                                        </td>
                                                                           
                            </tr>
                            <?php } } ?>
                        </tbody>
                        </table>
            
                     </div>
                </div>
            </section>
</body>
</html>


