<?php if ($flag == 'cargo') { ?>
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_cargo_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_cargo_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<?php if ($flag == 'ups') { ?>
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_courier_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_courier_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<?php if ($flag == 'aeropost') { ?>
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_aeropost_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_aeropost_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<?php if ($flag == 'ccpack') { ?>
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_ccpack_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_ccpack_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<div class="box-body">
    <div class="out-filter-secion col-md-8">
        <div class="from-date-filter-div filterout col-md-2">
            <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y', strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
        </div>
        <div class="to-date-filter-div filterout col-md-2">
            <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y', strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
        </div>
        <select id="filterby" class="form-control col-md-2" style="display: none">
            <option <?php echo $filterBy == 'Invoices' ? 'selected' : '' ?> value="Invoices">Invoices</option>
            <option <?php echo $filterBy == 'Billing Party' ? 'selected' : '' ?> value="Billing Party">Billing Party</option>
        </select>
        <div class="col-md-2">
            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
        </div>
    </div>
    <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Receipt Number</th>
                <th>Payment Date</th>
                <th>Invoice Number</th>
                <th>Billing Party</th>
                <th>Payment Currency</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($data as $items)
            <tr data-editlink="{{ route('editinvoicepayment',[$items->receipt_number,$flag]) }}" id="<?php echo $items->receipt_number; ?>" class="edit-row">
                <td style="display: none">{{$items->receipt_number}}</td>
                <td>{{$items->receipt_number}}</td>
                <td>{{date('d-m-Y',strtotime($items->created_at))}}</td>
                <td>{{$items->invoice_number}}</td>
                <td>{{$items->company_name}}</td>
                <td>{{$items->paymentCurrencyCode}}</td>
                <td style="text-align:right">{{number_format($items->exchange_amount,2)}}</td>
                <td>
                    <div class='dropdown'>
                        <a style="margin-right:10px" title="Print Receipt" target="_blank" href="{{route('printsinglereceipt',[$items->receipt_number,'invoice',$flag])}}"><i class="fa fa-print"></i></a>
                        <?php if ($permissionModifyPayment) { ?>
                            <a style="margin-right:10px" class="" href="{{route('editinvoicepayment',[$items->receipt_number,$flag])}}" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        <?php } ?>
                        <?php if ($permissionInvoicePaymentDelete) { ?>
                            <a class="delete-record" href="{{route('deleteinvoicepayment',[$items->invoice_id,$items->receipt_number])}}" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            @endforeach


        </tbody>

    </table>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "date-uk-pre": function(a) {
                if (a == null || a == "") {
                    return 0;
                }
                var ukDatea = a.split('-');
                return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
            },

            "date-uk-asc": function(a, b) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },

            "date-uk-desc": function(a, b) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        });
        $('#example').DataTable({
            'stateSave': true,
            "columnDefs": [{
                "targets": [-1],
                "orderable": false
            }, {
                type: 'date-uk',
                targets: 2
            }],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
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





    })
</script>