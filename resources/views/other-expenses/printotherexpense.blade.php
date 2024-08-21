<html>

<head>
    <title>Expense Receipt</title>
</head>

<body>
    <section class="content" style="font-family: sans-serif;">
        <div class="box box-success" style="width: 100%;margin: 0px auto;">
            <div class="box-body cargo-forms" style="color: #000">

                <?php if (!empty($expenseData)) {
                    $kv = 1;
                    foreach ($expenseData as $ko => $vo) {
                        $vo = (object) $vo;
                        $expenseDetailsData = DB::table('other_expenses_details')->where('voucher_number', $vo->voucher_number)->where('expense_id', $vo->id)->where('deleted', 0)->orderBy('id', 'desc')->get();
                ?>

                        <?php if (!empty($expenseDetailsData)) {  ?>


                            <div class="mainblk" style="margin-top: 20px; float: none;  width: 842px; padding-bottom: 20px; margin: 0 auto;">

                                <div class="blockleft" style="margin-bottom: 20px; width: 100%; float: left; padding: 0 10px 0 10px; box-sizing: border-box; border-left: 1px dashed #ccc; border-right: 1px dashed #ccc;font-size:12px">

                                    <div style="float: left;width: 100%; margin: 5px 0 5px 0;">
                                        <div style="text-align: left;float: left; width: 40%; font-size: 18x; font-weight: 600;text-transform: uppercase;">
                                            Chatelain Cargo Services
                                        </div>
                                        <div style="text-align: right;float: left; width: 49%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Other Expense</div>
                                        <div style="width: 10%; float: left;text-align: right; font-size: 18px; margin-top: 9px; color: #f30101;">
                                            <?php echo $vo->voucher_number; ?>
                                        </div>
                                    </div>
                                    <div style="float: left;width: 100%; margin-bottom: 3px;">
                                        <div style="float: left; width: 25%; font-weight: 600;color:#000">Date : </div>
                                        <div style="float: left;  width: 68%;"><?php echo date('d-m-Y', strtotime($vo->exp_date)); ?></div>
                                    </div>

                                    <div style="float: left;width: 100%; margin-bottom: 3px;">
                                        <div style="float: left; width: 25%; font-weight: 600;color:#000">Department : </div>
                                        <div style="float: left;  width: 68%;">
                                            <?php $departmentData = App\CashCreditDetailType::getData($vo->department);
                                            echo !empty($departmentData->name) ? $departmentData->name : '-'; ?></div>
                                    </div>
                                    <div style="float: left;width: 100%; margin-bottom: 3px;">
                                        <div style="float: left;  width: 25%; font-weight: 600;color:#000">Cash/Bank : </div>
                                        <div style="float: left; width: 68%;"><?php $currencyData = App\CashCredit::getCashCreditData($vo->cash_credit_account);
                                                                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></div>
                                    </div>
                                    <div style="float: left;width: 100%; margin-bottom: 3px;">
                                        <div style="float: left;  width: 25%; font-weight: 600;color:#000">Note : </div>
                                        <div style="float: left; width: 68%;"><?php echo $vo->note; ?></div>
                                    </div>
                                    <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left;background-color: fff;line-height: 20px;color: #000;padding: 0;border:solid 1px hsl(0, 0%, 86%);width:100%;border-collapse: collapse;width: 100%;font-size:12px;font-family: Asap, sans-serif;">
                                        <thead>
                                            <tr>
                                                <th width="40px" style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 2px;text-align: left;font-weight:normal;color:#000">Sr No.</th>
                                                <th width="360px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 2px;text-align: left;font-weight:normal;color:#000">Description</th>
                                                <th width="90px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 2px;text-align: left;font-weight:normal;color:#000">Amount
                                                    <?php
                                                    $currencyAData = App\Currency::getData($vo->currency);
                                                    if (empty($currencyAData))
                                                        $code = '';
                                                    else
                                                        $code = $currencyAData->code;
                                                    echo " (" . $code . ")"; ?></th>
                                                <th width="165px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 2px;text-align: left;font-weight:normal;color:#000">Paid to</th>
                                            </tr>
                                        </thead>


                                        <tbody>
                                            <?php if (count($expenseDetailsData)  > 0) {
                                                $ik = 1;
                                                foreach ($expenseDetailsData as $k => $v) {
                                                    $v = (object) $v;
                                            ?>
                                                    <tr>
                                                        <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif; border-collapse: collapse;text-align: left;"><?php echo $ik; ?></td>
                                                        <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:0px 0px 15px 5px;font-family: Asap, sans-serif; border-collapse: collapse;text-align: left;"><?php echo !empty($v->description) ? $v->description : '-'; ?></td>
                                                        <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif; border-collapse: collapse;text-align: right;"><?php echo !empty($v->amount) ? number_format($v->amount, 2) : '0.00'; ?></td>
                                                        <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif; border-collapse: collapse;text-align: left;">
                                                            <?php $dataVendor = app('App\Vendors')->getVendorData($v->paid_to);
                                                            echo !empty($dataVendor->company_name) ? $dataVendor->company_name : "-"; ?></td>
                                                    </tr>
                                                <?php $ik++;
                                                } ?>
                                                <tr>
                                                    <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: left;"></td>
                                                    <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: left;vertical-align:top;">Total</td>
                                                    <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: right;">
                                                        <?php $totalD = App\OtherExpenses::getExpenseTotal($vo->id);
                                                        echo $totalD; ?></td>
                                                    <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: left;"></td>
                                                </tr>
                                            <?php  } else { ?>
                                                <tr>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;" colspan="6">No data found</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <div style="float: left; width: 20%; margin-top: 30px; bottom: 0; left: 20px;border-bottom: 1px solid #ccc; padding-bottom:10px;">
                                        <div style="width: 100%;float: left;margin-top: 20px;">
                                            <div style="padding-bottom: 30px">Signature</div>
                                        </div>
                                    </div>
                                    <div style="float: right; width: 50%; margin-top: 10px; bottom: 0; left: 20px;">
                                        <div style="width: 100%;float: left;margin-bottom: 10px">
                                            <div style="width: 30%;float: left;margin-right: 50px">Request by</div>
                                            <div style="width: 70%;float: left;margin-right: 20px"><b>
                                                    <?php $modelUser = new App\User();
                                                    $dataUser = $modelUser->getUserName($vo->request_by);
                                                    echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                                </b></div>
                                        </div>
                                        <?php if ($expenseData[0]->expense_request == 'Approved' || $expenseData[0]->expense_request == 'Disbursement done') { ?>
                                            <div style="width: 100%;float: left;margin-bottom: 10px">
                                                <div style="width: 30%;float: left;">Approved by</div>
                                                <div style="width: 70%;float: left;"><b>
                                                        <?php $modelUser = new App\User();
                                                        $dataUser = $modelUser->getUserName($vo->approved_by);
                                                        echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                                    </b></div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($expenseData[0]->expense_request == 'Disbursement done') { ?>
                                            <div style="width: 100%;float: left;margin-bottom: 10px">
                                                <div style="width: 30%;float: left;">Disbursed by</div>
                                                <div style="width: 70%;float: left;"><b>
                                                        <?php $modelUser = new App\User();
                                                        $dataUser = $modelUser->getUserName($vo->disbursed_by);
                                                        echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                                    </b></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php  } ?>
                    <?php $kv++;
                    } ?> <?php } ?>



            </div>
        </div>
    </section>
</body>

</html>