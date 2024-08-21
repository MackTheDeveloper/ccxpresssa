<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
    <title>Warehouse Report Cargo</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">

        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">
                <h3 style="background: #ccc;padding:5px;font-weight:normal;">Warehouse Report Cargo</h3>


                <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
                    <thead>
                        <tr>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">File Number</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">Consignee</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">Agent</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">AWB/BL No.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">House AWB No.</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">Warehouse</th>
                            <th style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;">Warehouse Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($warehouseData)) {
                            foreach ($warehouseData as $k => $cargos) {
                        ?>
                                <tr>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;"><?php echo $cargos->file_number; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;"><?php echo !empty($cargos->consigneeCompany) ? $cargos->consigneeCompany : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                                        <?php echo !empty($cargos->agentName) ? $cargos->agentName : '-'; ?>
                                    </td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;"><?php echo !empty($cargos->awb_bl_no) ? $cargos->awb_bl_no : '-'; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                                        <?php $dataHAWNo = app('App\HawbFiles')->getHawbFilesNumbers($cargos->id);
                                        echo !empty($dataHAWNo) ? $dataHAWNo : "-"; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;">
                                        <?php echo !empty($cargos->warehouseName) ? $cargos->warehouseName : "-"; ?></td>
                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;"><?php echo !empty($cargos->warehouse_status) ? Config::get('app.warehouseStatus')[$cargos->warehouse_status] : '-'; ?></td>

                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </section>
</body>

</html>