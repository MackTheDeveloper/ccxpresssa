@extends('layouts.custom')

@section('title')
CCPack Invoices
@stop

@section('breadcrumbs')
@include('menus.ccpack-invoices')
@stop

<?php
$permissionCCpackInvoicesEdit = App\User::checkPermission(['update_ccpack_invoices'], '', auth()->user()->id);
$permissionCCpackInvoicesDelete = App\User::checkPermission(['delete_courier_invoices'], '', auth()->user()->id);
$permissionCCpackInvoicePaymentsAdd = App\User::checkPermission(['add_ccpack_invoice_payments'], '', auth()->user()->id);

?>
<?php

use App\Currency;
?>
@section('content')
<section class="content-header">
    <h1>CCPack Invoices</h1>
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
            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="col-md-2">
                    <div class="from-date-filter-div filterout">
                        <input type="text" id="fromDate" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker saveStateThis from-date-filter">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="to-date-filter-div filterout">
                        <input type="text" id="toDate" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker saveStateThis to-date-filter">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
            {{ Form::close() }}
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
                        <th>Date</th>
                        <th>Invoice No.</th>
                        <th>File No.</th>
                        <th>AWB / BL No.</th>
                        <th>Billing Party</th>
                        <th>Consingee</th>
                        <th>Type</th>
                        <th>Currency</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
<style>
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

        $('.customButtonInGridinvoicestatus').click(function() {
            var status = $(this).val();
            var invoiceId = $(this).data('invoiceid');
            var upsId = $(this).data('upsid');
            var thiz = $(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            Lobibox.confirm({
                msg: "Are you sure to change status of payment?",
                callback: function(lobibox, type) {
                    if (type == 'yes') {
                        var urlz = '<?php echo route("changeinvoicestatus"); ?>';
                        $.ajax({
                            type: 'post',
                            url: urlz,
                            async: false,
                            data: {
                                'status': status,
                                'invoiceId': invoiceId
                            },
                            success: function(response) {
                                thiz.val(status);
                            }
                        });
                        if (status == 'Paid') {
                            thiz.val('Pending');
                            thiz.text('Pending');
                            thiz.removeClass('customButtonSuccess');
                            thiz.addClass('customButtonAlert');
                        } else {
                            thiz.val('Paid');
                            thiz.text('Paid');
                            thiz.removeClass('customButtonAlert');
                            thiz.addClass('customButtonSuccess');
                        }
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Payment Status has been updated successfully.'
                        });
                    } else {}
                }
            })
        })

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                DatatableInitiate(fromDate, toDate);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '') {
        $('#example').DataTable({
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
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "{{url('ccpackinvoice/listbydatatableserverside')}}", // json datasource
                "data": {
                    "fromDate": fromDate,
                    "toDate": toDate
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var style = '';
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 500);
                var invoiceId = data[0];
                var thiz = $(this);
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                //var url = '<?php echo route("checkoperationfordatatableserverside"); ?>';
                var url = '<?php echo url("ccpackinvoice/checkoperationfordatatableserverside"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'invoiceId': invoiceId,
                        'flag': 'getCcpackInvoiceData'
                    },
                    success: function(dataR) {
                        if (dataR.deleted == '0') {
                            var editLink = '<?php echo url("ccpackinvoice/viewccpackinvoicedetails"); ?>';
                            editLink += '/' + invoiceId;
                            $(row).attr('data-editlink', editLink);
                            $(row).addClass('edit-row');
                            $(row).attr('id', invoiceId);

                            if (dataR.payment_status == 'Paid')
                                var style = 'color:green';
                            else
                                var style = 'color:red';
                        } else {
                            $(row).addClass('trCancelledFile');
                        }

                        $('td', row).eq(9).addClass('alignright');
                        $('td', row).eq(10).addClass('alignright');
                        $('td', row).eq(11).attr('style', style);

                    },
                });
            }

        });
    }
</script>
@stop