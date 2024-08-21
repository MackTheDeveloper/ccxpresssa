<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="9" style="font-weight: bold;font-size: 14px;overflow-x:scroll">
        <div class="detail-container" style="width:150%">
            <!-- <div style="background: #6aa07163;font-weight: bold;float: left;width: 100%;border: 1px solid #ccc;">
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">File No</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Cost Item</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Cost Amount</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Conversion</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Voucher No</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Billing Item</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Billing Amount</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Conversion</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Invoice No</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Exc Rate</div>
                <div style="padding: 8px;width: 9%;float: left;padding-left: 10px;">P/L</div>
            </div> -->

            <div style="background: #6aa07163;font-weight: bold;float: left;width: 100%;border: 1px solid #ccc;">
                <div style="padding: 8px;width: 2%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Vendor Bill No</div>
                <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">File No</div>
                <div style="padding: 8px;width: 13%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Cost Item</div>
                <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Cost Amount</div>
                <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Conversion</div>
                <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Voucher No</div>
                <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">number of days old</div>
                <div style="padding: 8px;width: 13%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Billing Item</div>
                <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Billing Amount</div>
                <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Conversion</div>
                <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Invoice No</div>
                <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Exc Rate</div>
                <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;">P/L</div>
            </div>

            <div style="float: left;width: 100%;border: 1px solid #ccc;">
                <?php
                $invoicesum = 0;
                $expensesum = 0;

                foreach ($fileOfExpensesUnpaid as $kd1 => $vv1) {
                    $finalReportData = App\Expense::getFinalReportData($vv1->moduleId, $modules, $vendorId, $fromDate, $toDate, $duration);

                    $finalReportDataNew = array();
                    $ki = 0;
                    foreach($finalReportData as $fk => $fv)
                    {
                        foreach($fv['allData'] as $fvk => $fvv)
                        {
                            $finalReportDataNew['allData'][$fvv->expenseId][$ki] = $fv['allData'][$fvk];
                            $ki++;
                        }
                    }
                    //pre($finalReportDataNew);
                    //pre($finalReportData,1);
                    $invoicesumCurrentItem = 0;
                    $expensesumCurrentItem = 0;
                    $checkFileNumberArray = array();
                    $checkExpenseIdArray = array();
                    foreach ($finalReportDataNew['allData'] as $k => $v) {
                        //pre($v);
                        //if (isset($v['allData'])) {
                            foreach ($v as $k => $v1) {
                                //pre($v1);
                                if (empty($v1->costItemId)) continue;
                                $invoicesumCurrentItemInd = 0;
                                $expensesumCurrentItemInd = 0;
                ?>
                                <div style="float: left;width: 100%;border-bottom: 1px solid #ccc;">

                                    <div style="padding: 8px;width: 2%;float: left;padding-left: 10px;">
                                    <?php if(!in_array($v1->expenseId, $checkExpenseIdArray)) { ?>
                                    <input type="checkbox" data-expenseanddetailid="<?php echo $v1->expenseId.':'.$v1->expenseDetailsId; ?>" name="singlecheckboxforapdisbursement" class="singlecheckboxforapdisbursement singlecheckboxforapdisbursement-<?php echo $vendorId; ?>" id="<?php echo $v1->expenseId.':'.$v1->expenseDetailsId ?>" value="<?php echo $v1->expenseId ?>" />
                                    <?php } ?>
                                    </div>

                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;"><?php echo !empty($v1->vendorBillNumber) ? $v1->vendorBillNumber : '-'; ?></div>

                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;"><?php echo $vv1->fileNumber; ?></div>

                                    <div style="padding: 8px;width: 13%;float: left;padding-left: 10px;"><?php echo !empty($v1->costDescription) ? $v1->costDescription : '&nbsp;'; ?></div>

                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;text-align:right" class="costAmountD-<?php echo $v1->expenseDetailsId; ?> costAmountVendorD-<?php echo $vendorId; ?> costAmountExpenseD-<?php echo $v1->expenseId; ?>"><?php echo !empty($v1->costAmount) ? $v1->costCurrencyCode . ' ' . $v1->costAmount : '&nbsp;'; ?></div>

                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;text-align:right">
                                        <?php
                                        if ($v1->costCurrencyCode == 'HTG') {
                                            if (!empty($v1->costAmount)) {
                                                echo 'USD' . ' ' . number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2);

                                                $expensesumCurrentItem += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
                                                $expensesum += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
                                                $expensesumCurrentItemInd += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
                                            } else {
                                                echo '&nbsp;';
                                            }
                                        } else {
                                            if (!empty($v1->costAmount)) {
                                                echo 'USD' . ' ' . number_format($v1->costAmount, 2);
                                                $expensesumCurrentItem += str_replace(',', '', number_format($v1->costAmount, 2));
                                                $expensesum += str_replace(',', '', number_format($v1->costAmount, 2));
                                                $expensesumCurrentItemInd += str_replace(',', '', number_format($v1->costAmount, 2));
                                            } else {
                                                echo '&nbsp;';
                                            }
                                        }
                                        ?></div>

                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;text-align:right;background:<?php echo ($v1->expenseStatus == 'Approved') ? '#2ad42a' : '';  ?>"><?php echo $v1->voucherNumber; ?></div>

                                    <div style="padding: 8px;width: 9%;float: left;padding-left: 10px;text-align:right;"><?php echo App\Expense::getNumberOfOldDays($v1->expenseId); ?></div>

                                    <div style="padding: 8px;width: 13%;float: left;padding-left: 10px;"><?php echo !empty($v1->biliingItemDescription) ? $v1->biliingItemDescription : '&nbsp;'; ?></div>

                                    <div style="padding: 8px;width: 7%;float: left;padding-left: 10px;text-align:right">
                                        <?php
                                        echo !empty($v1->biliingItemAmount) ? $v1->billingCurrencyCode . ' ' . number_format($v1->biliingItemAmount, 2) : '&nbsp;';
                                        ?></div>

                                    <div style="padding: 8px;width: 7%;float: left;padding-left: 10px;text-align:right">
                                        <?php
                                        if ($v1->billingCurrencyCode == 'HTG') {
                                            if (!empty($v1->biliingItemAmount)) {
                                                echo 'USD' . ' ' . number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2);

                                                $invoicesumCurrentItem += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
                                                $invoicesum += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
                                                $invoicesumCurrentItemInd += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
                                            } else {
                                                echo '&nbsp;';
                                            }
                                        } else {
                                            if (!empty($v1->biliingItemAmount)) {
                                                echo 'USD' . ' ' . number_format($v1->biliingItemAmount, 2);
                                                $invoicesumCurrentItem += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
                                                $invoicesum += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
                                                $invoicesumCurrentItemInd += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
                                            } else {
                                                echo '&nbsp;';
                                            }
                                        } ?></div>

                                    <div style="padding: 8px;width: 7%;float: left;padding-left: 10px;text-align:right"><?php echo $v1->invoiceNumber; ?></div>

                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;"><?php echo $exchangeRateOfUsdToHTH; ?></div>


                                    <div style="padding: 8px;width: 6%;float: left;padding-left: 10px;text-align:right;color:<?php echo ($invoicesumCurrentItemInd - $expensesumCurrentItemInd) < 0 ? 'red' : 'green'; ?>">
                                        <?php echo number_format($invoicesumCurrentItemInd - $expensesumCurrentItemInd, 2); ?>
                                    </div>
                                </div>

                            <?php $checkFileNumberArray[] = $vv1->fileNumber;
                            $checkExpenseIdArray[] = $v1->expenseId;
                                // pre($expensesum, 1);
                            } ?>
                    <?php //}
                    } ?>
                    <div style="background: #ceb33363;font-weight: bold;float: left;width: 100%;border-bottom: 1px solid #ccc;color:#000">
                        <div style="padding: 8px;width: 2%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Total</div>
                        <div style="padding: 8px;width: 13%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($expensesumCurrentItem,2)}}</div>
                        <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 13%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($invoicesumCurrentItem,2)}}</div>
                        <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                        <div style="text-align:right;font-weight:bold;padding: 8px;width: 6%;float: left;padding-left: 10px;color:<?php echo ($invoicesumCurrentItem - $expensesumCurrentItem) < 0 ? 'red' : 'green'; ?>">{{'USD'.' '.number_format($invoicesumCurrentItem-$expensesumCurrentItem,2)}}</div>
                    </div>
                <?php } ?>
                <div style="background: #000;color:#fff;font-weight: bold;float: left;width: 100%;">
                    <div style="padding: 8px;width: 2%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Total</div>
                    <div style="padding: 8px;width: 13%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($expensesum,2)}}</div>
                    <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 9%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 13%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($invoicesum,2)}}</div>
                    <div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="padding: 8px;width: 6%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
                    <div style="text-align:right;font-weight:bold;padding: 8px;width: 6%;float: left;padding-left: 10px;color:<?php echo ($invoicesum - $expensesum) < 0 ? 'red' : 'green'; ?>">{{'USD'.' '.number_format($invoicesum-$expensesum,2)}}</div>
                </div>
            </div>
        </div>
    </td>
</tr>