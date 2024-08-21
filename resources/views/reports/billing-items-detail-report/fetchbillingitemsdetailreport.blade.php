@extends('layouts.custom')

@section('title')
Billing Items Detail Report - {{$dataBillingItem->billing_name}}
@stop

@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Billing Items Detail Report - {{$dataBillingItem->billing_name}}</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <div class="col-md-10">
                {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
                {{ csrf_field() }}
                <div class="row" style="margin-bottom:20px">
                    <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter" value="<?php echo date('01-m-Y'); ?>">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter" value="<?php echo date('d-m-Y'); ?>">
                    </div>

                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="submit" id="clsPrint" class="btn btn-success">Print</button>
                </div>
                {{ Form::close() }}
            </div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th>Date</th>
                        <th>Invoice Number</th>
                        <th>Currency</th>
                        <th>Billing Party</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
            </table>
            <div class="row totalAmountSection">
                <div class="col-md-4" style="float: right;text-align: right;margin-top: 10px;">
                    <div class="col-md-2"><b>USD: </b></div>
                    <div style="text-align: left" class="totalUsd col-md-4"></div>
                    <div class="col-md-2"><b>HTG: </b></div>
                    <div style="text-align: left" class="totalHtg col-md-4">
                    </div>
                </div>
            </div>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        DatatableInitiate();
        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var submitButtonName = $(this.submitButton).attr("id");
                var billingItemId = '<?php echo $id; ?>';
                if (submitButtonName == 'clsPrint') {
                    var urlztnn = '<?php echo route("printfetchbillingitemsdetailreport"); ?>';
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'POST',
                        data: {
                            'billingItemId': billingItemId,
                            'fromDate': fromDate,
                            'toDate': toDate,
                            'submitButtonName': submitButtonName
                        },
                        success: function(dataRes) {
                            window.open(dataRes, '_blank');
                        }
                    });
                } else {
                    DatatableInitiate(fromDate, toDate, billingItemId);
                }
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', billingItemId = '<?php echo $id; ?>') {
        var i = 1;
        $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                "targets": [2],
                "orderable": false
            }, {
                targets: [0],
                className: "hide_column"
            }],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container())
                    .on('click', function() {
                        $('.expandpackage').each(function() {
                            if ($(this).hasClass('fa-minus')) {
                                $(this).removeClass('fa-minus');
                                $(this).addClass('fa-plus');
                            }
                        })
                        if ($('.fa-expand-collapse-all').hasClass('fa-minus')) {
                            $('.fa-expand-collapse-all').removeClass('fa-minus');
                            $('.fa-expand-collapse-all').addClass('fa-plus');
                        }
                    });
                $('#example_filter input').bind('keyup', function(e) {
                    if ($('.fa-expand-collapse-all').hasClass('fa-minus')) {
                        $('.fa-expand-collapse-all').removeClass('fa-minus');
                        $('.fa-expand-collapse-all').addClass('fa-plus');
                    }
                });
            },
            "ajax": {
                url: "{{url('reports/listfetchbillingitemsdetailreport')}}", // json datasource
                dataSrc: function(data) {
                    totalUsd = data.totalUsd;
                    totalHtg = data.totalHtg;
                    $('.totalUsd').text(totalUsd);
                    $('.totalHtg').text(totalHtg);
                    return data.data;
                },
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.billingItemId = billingItemId;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                $('td', row).eq(6).addClass('alignright');
                $('td', row).eq(7).addClass('alignright');
                $('td', row).eq(8).addClass('alignright');
            }
        });
    }
</script>
@stop