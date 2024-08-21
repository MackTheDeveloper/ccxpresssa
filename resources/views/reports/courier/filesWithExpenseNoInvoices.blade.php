@extends('layouts.custom')

@section('title')
Files with expense but no invoices
@stop

@section('sidebar')

@stop

@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Files with expense but no invoices</h1>
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

            <div class="col-md-12">
                {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
                {{ csrf_field() }}
                <div class="row" style="margin-bottom:20px">
                    <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                    <div class="filterout col-md-2">
                        <select id="courierType" class="form-control">
                            <option selected="" value="UPS">UPS</option>
                            <option value="upsMaster">UPS Master</option>
                            <option value="Aeropost">Aeropost</option>
                            <option value="aeropostMaster">Aeropost Master</option>
                            <option value="CCPack">CCPack</option>
                            <option value="ccpackMaster">CCPack Master</option>
                        </select>
                    </div>

                    <div class="filterout col-md-2">
                        <select id="fileType" class="form-control">
                            <option selected="" value="">All</option>
                            <option value="1">Import</option>
                            <option value="2">Export</option>
                        </select>
                    </div>

                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>

                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="submit" id="clsPrint" class="btn btn-success">Print</button>
                </div>
                {{ Form::close() }}
            </div>

            <div style="display:none;float: right;width: 200px;margin: 0px;height: 35px;position: absolute;left: 70%;z-index: 111;top:22px">
                <a title="Click here to print" target="_blank" href="{{ route('nonBilledFilesReportsPDF') }}"><i class="fa fa-print btn btn-primary"></i></a>
            </div>

            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th>Opening Date</th>
                        <th>File Number</th>
                        <th>AWB Number</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
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
    $('select,input').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });
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
                var courierType = $('#courierType').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var fileType = $('#fileType').val();
                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'clsPrint') {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    var urlztnn = '<?php echo url("reports/filesWithExpenseNoInvoicesCourierPDF"); ?>';
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'POST',
                        data: {
                            'fromDate': fromDate,
                            'toDate': toDate,
                            'fileType': fileType,
                            'courierType': courierType
                        },
                        success: function(dataRes) {
                            window.open(dataRes, '_blank');
                        }
                    });
                } else {
                    DatatableInitiate(fromDate, toDate, fileType, courierType);
                }
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', fileType = '', courierType = 'UPS') {
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
                targets: [0],
                className: "hide_column"
            }],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: "{{url('reports/listfilesWithExpenseNoInvoicesCourier')}}", // json datasource
                data: function(d) {
                    d.fileType = fileType;
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.courierType = courierType;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                if (courierType == 'UPS') {
                    var UpsId = data[0];
                    var editLink = '<?php echo url("ups/viewdetails"); ?>';
                    editLink += '/' + UpsId;
                    $(row).attr('id', UpsId);
                } else if (courierType == 'upsMaster') {
                    var UpsMasterId = data[0];
                    var editLink = '<?php echo url("ups-master/view"); ?>';
                    editLink += '/' + UpsMasterId;
                    $(row).attr('id', UpsMasterId);
                } else if (courierType == 'Aeropost') {
                    var AeropostId = data[0];
                    var editLink = '<?php echo url("aeropost/viewdetailsaeropost"); ?>';
                    editLink += '/' + AeropostId;
                    $(row).attr('id', AeropostId);
                } else if (courierType == 'aeropostMaster') {
                    var AeropostMasterId = data[0];
                    var editLink = '<?php echo url("aeropost-master/view"); ?>';
                    editLink += '/' + AeropostMasterId;
                    $(row).attr('id', AeropostMasterId);
                } else if (courierType == 'ccpackMaster') {
                    var CcpackMasterId = data[0];
                    var editLink = '<?php echo url("ccpack-master/view"); ?>';
                    editLink += '/' + CcpackMasterId;
                    $(row).attr('id', CcpackMasterId);
                } else {
                    var CCPackId = data[0];
                    var editLink = '<?php echo url("ccpack/viewdetailsccpack"); ?>';
                    editLink += '/' + CCPackId;
                    $(row).attr('id', CCPackId);
                }

                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 2000);
                var thiz = $(this);
                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                i++;
            }

        });
    };
</script>
@stop