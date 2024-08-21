<html>
<head>
    <title>List of Expenses Not Yet Invoiced</title>
</head>
<body>
<section class="content" style="font-family: sans-serif;">

    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #636b6f">
            <h3 style="background: #ccc;padding:5px;font-weight:normal;">List of Expenses Not Yet Invoiced</h3>
            
            
            <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #333;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                <thead>
                    <tr>
                        <th width="50px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">File Number</th>
                        <th width="500px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Voucher No.</th>                       
                        <th width="100px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Total Amount ($)</th>                                                
                    </tr>
                </thead>
            <tbody>
                <?php if(!empty($expenses_details)) { 
                    foreach($expenses_details as $k => $v)
                        {
                           
                        ?>
                <tr>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->file_number; ?></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $v->voucherNumberList; ?></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;">
                    <?php echo $v->total_expense; ?>
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