<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>
<head>
    <title>Pending Cargo Invoices</title>
</head>
<body>
<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">
            
            <h3 style="background: #ccc;padding:5px;font-weight:normal;">Pending Cargo Invoices</h3>
            <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                <thead>
                    <tr>
                        <th width="50px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Date</th>
                        <th width="70px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Invoice No.</th>
                        <th width="70px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File No.</th>
                        <th width="100px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">AWB / BL No.</th>
                        <th width="150px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Billing Party</th>
                        <th width="70px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Type</th>
                        <th width="100px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Total Amount</th>
                    </tr>
                </thead>
            <tbody>
                <?php if(!empty($pendingInvoiceData)) { 
                    foreach($pendingInvoiceData as $k => $v)
                        {
                            
                        ?>
                <tr>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;"><?php echo date('d/m/Y',strtotime($v->date)); ?></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;"><?php echo $v->bill_no; ?></td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                    <?php echo !empty($v->file_no) ? $v->file_no : "-"; ?>
                    </td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                        <?php echo !empty($v->awb_no) ? $v->awb_no : "-"; ?>
                    </td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                        <?php $dataClient = app('App\Clients')->getClientData($v->bill_to); echo !empty($dataClient->company_name) ? $dataClient->company_name : '-'; ?>
                    </td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                        <?php echo !empty($v->type_flag) ? $v->type_flag : "-"; ?>
                    </td>
                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;">
                        <?php echo !empty($v->balance_of) ? $v->balance_of : "-"; ?>
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

