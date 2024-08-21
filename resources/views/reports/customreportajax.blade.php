
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>File Number</th>
                            <th>Custom File Number</th>
                            <th>Invoice Date</th>
                            <th>Payment Status</th>
                            <th>Duties and Taxes</th>
                            <th>Expense</th>
                            <th>Difference</th>
                            <th>Client</th>
                            <th>AWB Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php $i = 1; foreach ($dataImportUpsFiles as $key => $value) {  
                        $customExpense = App\CustomExpenses::getCustomExpenses($value->id);
                        $customInvoice = App\CustomExpenses::getCustomInvoices($value->id);
                        
                        $customData = App\CustomExpenses::getCustomData($value->id);
                        $difference = (!empty($customInvoice->total_invoice) ? $customInvoice->total_invoice : '0.00') - (!empty($customExpense) ? $customExpense : '0.00');
                        $clsBL = '';
                        if($difference < 0)
                            $clsBL = "style=color:red";
                        else
                            $clsBL = "style=color:green";                            
                        ?>
                        <?php if(!empty($customExpense) || !empty($customInvoice)) { ?>
                    <tr>
                        <td><?php echo $value->file_number; ?></td>
                        <td><?php echo !empty($customData) ? $customData->file_number : '-'; ?></td>
                        <td><?php echo !empty($customInvoice->date) ? date('d-m-Y',strtotime($customInvoice->date)) : '-'; ?></td>
                        <td><?php echo !empty($customInvoice->payment_status) ? $customInvoice->payment_status : '-'; ?></td>
                        <td class="alignright"><?php echo !empty($customInvoice->total_invoice) ? $customInvoice->total_invoice : '0.00'; ?></td>
                        <td class="alignright"><?php echo !empty($customExpense) ? $customExpense : '0.00'; ?></td>
                        <td class="alignright" <?php echo $clsBL; ?>><?php echo number_format($difference,2); ?></td>
                        <td><?php $data = app('App\Clients')->getClientData($value->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                        <td><?php echo $value->awb_number; ?></td>
                        <td>
                                <div class='dropdown'>
                                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                        <ul class='dropdown-menu' style='left:auto;'>
                                            <?php if(!empty($customInvoice->payment_status) && ($customInvoice->payment_status == 'Pending' || $customInvoice->payment_status == 'Partial')) { ?>
                                                <li>
                                                        <a target='_blank' href="{{ route('addupsinvoicepayment',[$value->id,$customInvoice->id,0]) }}">Add Payment</a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                </div>
                            </td>
                    </tr>
                <?php } $i++; } ?>
                </tbody>
            </table>

           

<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable({
        'stateSave': true,
        "ordering": false,
        "scrollX": true
    });

    
})
</script>


