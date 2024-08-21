<table id="example1" class="display nowrap" style="width:100%">
    <thead>
        <tr>
            <th>File No.</th>
            <th>Consignee</th>
            <th>Delivery Date</th>
            <th>File Status</th>
            <th>Invoice No.</th>
            <th>Invoice Amount</th>
            <th>Paid Amount</th>
            <th>Payment Status</th>
            <th>Receipt Number</th>
            <th>Payment Notes</th>
            <th>Date</th>
            <th>From</th>
            <th>Freight</th>
            <th>AWB Tracking</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($aeropostFileAssignedToDeliveryBoy as $k => $items) {
            $paymentNotes = app('App\InvoicePayments')->getDataFromInvoice($items->invoiceId);
            $receiptNumbers = app('App\Common')->getReceiptNumbers($items->invoiceId, 'ups');
            $closeCls = '';
            if ($items->file_close == 1) {
                $closeCls = 'trClosedFile';
            }
        ?>
            <tr class="<?php echo $closeCls; ?>">
                <td>{{$items->file_number}}</td>
                <td>{{$items->consigneeName}}</td>
                <td>{{!empty($items->delivery_boy_assigned_on) ? date('d-m-Y',strtotime($items->delivery_boy_assigned_on)) : '-'}}</td>
                <td>{{!empty($items->aeropost_scan_status) ? Config::get('app.ups_new_scan_status')[$items->aeropost_scan_status] : '-'}}</td>
                <td>{{!empty($items->invoiceNumbers) ? $items->invoiceNumbers : '-'}}</td>
                <td class="alignright">{{!empty($items->totalAmount) ? number_format($items->totalAmount,1) : '0.00'}}</td>
                <td class="alignright">{{!empty($items->paidAmount) ? number_format($items->paidAmount,2) : '0.00'}}</td>
                <td style="color:<?php echo $items->payment_status == 'Paid' ? 'green' : 'red';  ?>">{{!empty($items->payment_status) ? $items->payment_status : '-'}}</td>
                <td>{{!empty($receiptNumbers) ? $receiptNumbers : '-'}}</td>
                <td>{{!empty($paymentNotes) ? $paymentNotes->payment_via_note : '-'}}</td>
                <td>{{!empty($items->date) ? date('d-m-Y',strtotime($items->date)) : '-'}}</td>
                <td>{{$items->from_location}}</td>
                <td>{{$items->freight}}</td>
                <td>{{$items->tracking_no}}</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function() {
        $('#example1').DataTable({
            "scrollX": true,
            "aaSorting": [],
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            },

        });
    });
</script>