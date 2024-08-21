<html>
<head>
    <title>All House File Expenses</title>
</head>
<body>
<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">
            <h3 style="background: #ccc;padding:5px;font-weight:normal;">All House File Expenses</h3>
            
            <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                <thead>
                    <tr>
                        <th width="100px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Date</th>
                        <th width="100px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Voucher No.</th>
                        <th width="195px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File No.</th>
                        <th width="145px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Consignataire</th>
                        <th width="120px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Total Amount</th>
                    </tr>
                </thead>
            <tbody>
                <?php if(!empty($cargoExpenseData)) { 
                    foreach($cargoExpenseData as $k => $v)
                        {
                            $v = (object) $v;
                            $cargoData = App\HawbFiles::where('id',$v->house_file_id)->first();
                        ?>
                <tr>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo date('d/m/Y',strtotime($v->exp_date)); ?></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;"><?php echo $v->voucher_number; ?></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                    <?php echo !empty($cargoData->file_number) ? $cargoData->file_number : "-"; ?>
                    </td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: left;">
                        <?php echo !empty($v->consignee) ? $v->consignee : "-"; ?>
                    </td>                                            
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;">
                        <?php echo App\Expense::getExpenseTotal($v->expense_id);  ?>
                    </td>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;height: 30px"></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                </tr>
                <?php } } ?>
            </tbody>
            </table>

         </div>
    </div>
</section>
</body>
</html>

