@extends('layouts.custom')

@section('title')
Due Invoices
@stop
<?php

use App\Currency;
?>
@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Due Invoices - <?php $dataUser = app('App\Clients')->getClientData($clientId);
                        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-"; ?></h1>
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
            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="row" style="margin-bottom:20px">
                <div class="filterout col-md-12">
                    <div class="col-md-1 row">
                        <label style="margin-top:7px">Filter By</label>
                    </div>
                    <div class="col-md-2 row">
                        <?php echo Form::select('invoicemodules', ['Cargo' => 'Cargo', 'House File' => 'House File', 'UPS' => 'UPS', 'upsMaster' => 'UPS Master', 'Aeropost' => 'Aeropost', 'aeropostMaster' => 'Aeropost Master', 'CCPack' => 'CCPack', 'ccpackMaster' => 'CCPack Master'], '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'invoicemodules']); ?>
                    </div>
                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" id="clsSubmit" class="btn btn-success">Submit</button>
                        <button style="display: none" type="submit" id="clsPrint" class="btn btn-success">Print</button>
                        <button type="submit" id="clsPrintAll" class="btn btn-warning">Print All</button>
                    </div>
                </div>
            </div>
            {{ Form::close() }}

            <div class="container-rep">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th style="display: none">Invoice ID</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Invoice No.</th>
                            <th>Amount</th>
                            <th>Open Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
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
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        DatatableInitiate();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var moduleInvoice = $('#invoicemodules').val();
                var submitButtonName = $(this.submitButton).attr("id");
                var clientId = '<?php echo $clientId; ?>';
                if (submitButtonName == 'clsPrint' || submitButtonName == 'clsPrintAll') {
                    var urlztnn = '<?php echo route("getduefilteredinvoicesofclient"); ?>';
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'POST',
                        data: {
                            'moduleInvoice': moduleInvoice,
                            'clientId': clientId,
                            'fromDate': fromDate,
                            'toDate': toDate,
                            'submitButtonName': submitButtonName
                        },
                        success: function(dataRes) {
                            window.open(dataRes, '_blank');
                        }
                    });
                } else {
                    DatatableInitiate(moduleInvoice, clientId, fromDate, toDate);
                }
            },
        });
    })

    function DatatableInitiate(moduleInvoice = 'Cargo', clientId = '<?php echo $clientId; ?>', fromDate = '', toDate = '') {
        var i = 1;
        var table = $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                targets: [0, 1],
                className: "hide_column"
            }],
            "scrollX": true,
            "order": [
                [1, "desc"]
            ],
            "ajax": {
                url: "{{url('reports/listgetdueinvoicesofclient')}}", // json datasource
                data: function(d) {
                    d.moduleInvoice = moduleInvoice;
                    d.clientId = clientId;
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var moduleId = data[0];
                var invoiceId = data[1];
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 2000);
                var thiz = $(this);
                $('td', row).eq(5).addClass('alignright');
                $('td', row).eq(6).addClass('alignright');
                i++;
            }
        });
    }
</script>
@stop