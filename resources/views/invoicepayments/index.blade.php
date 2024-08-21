@extends('layouts.custom')

@section('title')
Invoice Payment Listing
@stop

@section('breadcrumbs')
<?php if ($flag == 'cargo') { ?>
    @include('menus.cargo-invoice')
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_cargo_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_cargo_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<?php if ($flag == 'ups') { ?>
    @include('menus.ups-invoice')
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_courier_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_courier_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<?php if ($flag == 'aeropost') { ?>
    @include('menus.aeropost-invoice')
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_aeropost_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_aeropost_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>
<?php if ($flag == 'ccpack') { ?>
    @include('menus.ccpack-invoices')
    <?php $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_ccpack_invoice_payments'], '', auth()->user()->id);
    $permissionModifyPayment = App\User::checkPermission(['modify_ccpack_invoice_payments'], '', auth()->user()->id); ?>
<?php } ?>

@stop

@section('content')
<section class="content-header">
    <h1>Invoice Payment Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success invoicecontainer">
        <div class="box-body">
            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" id="fromDate" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker saveStateThis from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" id="toDate" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker saveStateThis to-date-filter">
                </div>
                <select id="filterby" class="form-control col-md-2" style="display: none">
                    <option value="Invoices">Invoices</option>
                    <option value="Billing Party">Billing Party</option>
                </select>
                <div class="col-md-2">
                    <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                </div>
            </div>

            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="trCancelledFileDiv1"></div>
                    <div class="trCancelledFileDiv2">Cancelled</div>
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
                        <th>Collected By</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
<style>
    #filterby {
        float: left;
        width: 228px;
        margin: 0px;
        height: 35px;
        z-index: 111;
        background: #ececec;
        top: 0px;
    }

    .hide_column {
        display: none;
    }
</style>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate);
        }else{
            DatatableInitiate();
        }
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $(document).delegate(".to-date-filter,#filterby", "change", function() {
            $('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var filterBy = $('#filterby').val();
            var fromDate = $('.from-date-filter').val();
            var toDate = $('.to-date-filter').val();
            $('#loading').hide();
            DatatableInitiate(fromDate, toDate)
            /* var urlz = '<?php //echo route("invoicepaymentoutsidefiltering"); 
                            ?>';
            $.ajax({
                url: urlz,
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'flag': '<?php //echo "$flag" 
                                ?>',
                    'filterBy': filterBy
                },
                success: function(data) {
                    $('#loading').hide();
                    $('.invoicecontainer').html(data);
                }
            }); */
        })

        $(document).delegate(".allrecores", "click", function() {
            $('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.from-date-filter').val('');
            $('.to-date-filter').val('');
            var filterBy = $('#filterby').val();
            var fromDate = $('.from-date-filter').val();
            var toDate = $('.to-date-filter').val();
            DatatableInitiate(fromDate, toDate)
            /* var urlz = '<?php //echo route("invoicepaymentoutsidefiltering"); 
                            ?>';
            $.ajax({
                url: urlz,
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'flag': '<?php //echo "$flag" 
                                ?>',
                    'filterBy': filterBy
                },
                success: function(data) {
                    $('#loading').hide();
                    $('.invoicecontainer').html(data);
                }
            }); */
        })
    })

    function DatatableInitiate(fromDate = '', toDate = '') {
        var i = 1;
        var table = $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
                $('.saveStateThis').each(function() {
                    data[$(this).attr('id')] = $(this).val();
                });
                localStorage.setItem( 'DataTables_'+window.location.pathname, JSON.stringify(data) );
            },
            "columnDefs": [{
                    "targets": [-1],
                    "orderable": false
                },
                {
                    targets: [0],
                    className: "hide_column"
                }
            ],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: "{{url('invoicepayment/listbydatatableserverside')}}", // json datasource
                "data": {
                    "flag": "<?php echo $flag; ?>",
                    "fromDate": fromDate,
                    "toDate": toDate
                },
                // type: "post",  // method  , by default get
                error: function() { // error handling
                    /* $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none"); */

                }
            },
            "createdRow": function(row, data, index) {
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 2000);
                var receiptNumber = data[0];
                var thiz = $(this);
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var url = '<?php echo url("invoicepayment/checkoperations"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        'receiptNumber': receiptNumber,
                        'flag': 'checkReceipt'
                    },
                    success: function(dataR) {
                        if (dataR == '0') {
                            var editLink = '<?php echo url("invoicepayment/edit"); ?>';
                            editLink += '/' + receiptNumber + '/<?php echo $flag; ?>';
                            $(row).attr('data-editlink', editLink);
                            <?php if ($permissionModifyPayment) { ?>
                                $(row).addClass('edit-row');
                            <?php } ?>
                            $(row).attr('id', receiptNumber);
                        } else {
                            $(row).addClass('trCancelledFile');
                        }
                    }
                });
                $('td', row).eq(6).addClass('alignright');
                i++;
                //$("#loading").hide();
            }
        });
    };
</script>
@stop