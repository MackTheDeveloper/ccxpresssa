
                

                <table id="example" class="table display nowrap" style="width:50%;float: left">
                        <thead style="background: #6195a9;color: #fff;">
                            <tr>
                                <th width="50%" style="background: #8dab81;color: #fff;">CLIENTS</th>
                                <th width="50%;"  colspan="2" style="text-align: center;background: #6195a9;color: #fff;">List of Files Invoiced</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid #ccc;font-weight: bold">
                                <td style="border: 1px solid #ccc"></td>
                                <td style="text-align: center;border: 1px solid #ccc">HTG</td>
                                <td style="text-align: center;border: 1px solid #ccc">USD</td>
                            </tr>
                            <?php $i = 1; foreach ($dataInvoices as $key => $value) { ?>
                            <tr style="border-bottom: 1px solid #ccc">
                                <td style="border: 1px solid #ccc"><?php echo $value->company_name; ?></td>
                                <td style="text-align: left;border: 1px solid #ccc"><span>HTG</span><span style="float: right;"><?php echo $value->code == 'HTG' ? $value->total : '-'; ?></span></td>
                                <td style="text-align: left;border: 1px solid #ccc"><span>$</span><span style="float: right;"><?php echo $value->code == 'USD' ? $value->total : '-'; ?></span></td>
                            </tr>
                            <?php $i++; } ?> 
                            
                            <?php for ($i=$i; $i <= $max; $i++) { ?>
                            <tr style="border-bottom: 1px solid #ccc">
                                <td style="height: 39px;border: 1px solid #ccc"></td>
                                <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>
                                <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>
                            </tr>    
                            <?php } ?>

                            <?php if(!empty($dataInvoices)) { ?>
                            <tr style="border-bottom: 1px solid #ccc;font-weight: bold;">
                                <td style="text-align: left;border: 1px solid #ccc"><b>TOTALS</b></td>
                                <td style="text-align: left;border: 1px solid #ccc"><span>HTG</span><span style="float: right;"><?php echo number_format($totalOfInvoicesHTG,2); ?></span></td>
                                <td style="text-align: left;border: 1px solid #ccc"><span>$</span><span style="float: right;"><?php echo number_format($totalOfInvoicesUSD,2); ?></span></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                </table>
                <table id="example1" class="table display nowrap" style="width:25%;float: left">
                        <thead style="background: #c796aa;color: #fff;">
                            <tr>
                                <th width="30%" colspan="2" style="text-align: center;">List of Expenses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="font-weight: bold">
                                <td style="text-align: center;border: 1px solid #ccc">HTG</td>
                                <td style="text-align: center;border: 1px solid #ccc">USD</td>
                            </tr>
                             <?php $i = 1; foreach ($dataExpenses as $key => $value) { ?>
                            <tr style="border-bottom: 1px solid #ccc">
                                <td style="text-align: left;border: 1px solid #ccc"><span>HTG</span><span style="float: right;"><?php echo $value->code == 'HTG' ? $value->total_expense : '-'; ?></span></td>
                                <td style="text-align: left;border: 1px solid #ccc"><span>$</span><span style="float: right;"><?php echo $value->code == 'USD' ? $value->total_expense : '-'; ?></span></td>
                            </tr>
                            <?php $i++; } ?> 
                            
                            <?php for ($i=$i; $i <= $max; $i++) { ?>
                            <tr style="border-bottom: 1px solid #ccc">
                                <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>
                                <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>                                    
                            </tr>    
                            <?php } ?>

                            <?php if(!empty($dataInvoices)) { ?>
                            <tr style="border-bottom: 1px solid #ccc;font-weight: bold;">
                                <td style="text-align: left;border: 1px solid #ccc"><span>HTG</span><span style="float: right;"><?php echo number_format($totalOfExpenseHTG,2); ?></span></td>
                                <td style="text-align: left;border: 1px solid #ccc"><span>$</span><span style="float: right;"><?php echo number_format($totalOfExpenseUSD,2); ?></span></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                </table>
                <table id="example2" class="table display nowrap" style="width:25%;float: left">
                        <thead style="background: #f5c4a8;color: #fff;">
                            <tr>
                                <th width="30%" colspan="3" style="text-align: center;">Amouts Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="font-weight: bold">
                                <td style="text-align: center;border: 1px solid #ccc">Payment By</td>
                                <td style="text-align: center;border: 1px solid #ccc">HTG</td>
                                <td style="text-align: center;border: 1px solid #ccc">USD</td>
                            </tr>
                                <?php $i = 1; foreach ($dataInvoicesAmountColected as $key => $value) { ?>
                                <tr style="border-bottom: 1px solid #ccc">
                                    <td style="text-align: left;border: 1px solid #ccc"><?php echo $value->payment_via; ?></td>
                                    <td style="text-align: left;border: 1px solid #ccc"><span>HTG</span><span style="float: right;"><?php echo $value->exchangeCurrencyCode == 'HTG' ? $value->total_payments_collected : '-'; ?></span></td>
                                    <td style="text-align: left;border: 1px solid #ccc"><span>$</span><span style="float: right;"><?php echo $value->exchangeCurrencyCode == 'USD' ? $value->total_payments_collected : '-'; ?></span></td>
                                </tr>
                                <?php $i++; } ?> 

                                <?php for ($i=$i; $i <= $max; $i++) { ?>
                                <tr style="border-bottom: 1px solid #ccc">
                                    <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>
                                    <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>
                                    <td style="text-align: left;border: 1px solid #ccc;height: 39px;"></td>
                                </tr>    
                                <?php } ?>

                                <?php if(!empty($dataInvoices)) { ?>
                                <tr style="border-bottom: 1px solid #ccc;font-weight: bold;">
                                    <td style="text-align: left;border: 1px solid #ccc"></td>
                                    <td style="text-align: left;border: 1px solid #ccc"><span>HTG</span><span style="float: right;"><?php echo number_format($totalAmountCollectedOfInvoicesHTG,2); ?></span></td>
                                    <td style="text-align: left;border: 1px solid #ccc"><span>$</span><span style="float: right;"><?php echo number_format($totalAmountCollectedOfInvoicesUSD,2); ?></span></td>
                                </tr>
                                <?php } ?>
                            </tr>
                        </tbody>
                </table>
           