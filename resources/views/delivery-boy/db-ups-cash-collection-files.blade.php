<table id="example" class="display nowrap" style="width:100%">
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
            <th>Shipper</th>
            <th>Shipment No.</th>
            <th>AWB Tracking</th>
            <th>Package Type</th>
            <th>Billing Term</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($upsFileAssignedToDeliveryBoy as $k => $items) {
            if ($items->package_type == 'LTR')
                $packageType = 'Letter';
            else if ($items->package_type == 'DOC')
                $packageType = 'Document';
            else
                $packageType = 'Package';
            $billingTerm = app('App\Ups')::getBillingTerm($items->id);
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
                <td>{{!empty($items->ups_scan_status) ? Config::get('app.ups_new_scan_status')[$items->ups_scan_status] : '-'}}</td>
                <td>{{!empty($items->invoiceNumbers) ? $items->invoiceNumbers : '-'}}</td>
                <td class="alignright">{{!empty($items->totalAmount) ? number_format($items->totalAmount,2) : '0.00'}}</td>
                <td class="alignright">{{!empty($items->paidAmount) ? number_format($items->paidAmount,2) : '0.00'}}</td>
                <td style="color:<?php echo $items->payment_status == 'Paid' ? 'green' : 'red';  ?>">{{!empty($items->payment_status) ? $items->payment_status : '-'}}</td>
                <td>{{!empty($receiptNumbers) ? $receiptNumbers : '-'}}</td>
                <td>{{!empty($paymentNotes) ? $paymentNotes->payment_via_note : '-'}}</td>
                <td>{{$items->shipperName}}</td>
                <td>{{!empty($items->shipment_number) ? $items->shipment_number : '-'}}</td>
                <td>{{$items->awb_number}}</td>
                <td>{{$packageType}}</td>
                <td>{{$billingTerm}}</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function() {
        $('#example').DataTable({
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