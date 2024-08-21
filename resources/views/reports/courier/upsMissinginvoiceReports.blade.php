@extends('layouts.custom')

@section('title')
List of Expenses Not Yet Invoiced
@stop

@section('sidebar')

@stop

@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>List of Expenses Not Yet Invoiced</h1>
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
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="filterout col-md-3">
                    <select id="courierType" class="form-control">
                        <option selected="" value="UPS">UPS</option>
                        <option value="upsMaster">UPS Master</option>
                        <option value="Aeropost">Aeropost</option>
                        <option value="aeropostMaster">Aeropost Master</option>
                        <option value="CCPack">CCPack</option>
                        <option value="ccpackMaster">CCPack Master</option>
                    </select>
                </div>
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter" value="<?php echo date('01-m-Y'); ?>">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter" value="<?php echo date('d-m-Y'); ?>">
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
                <button style="display: none" type="submit" id="clsPrint" class="btn btn-success">Print</button>
            </div>
            {{ Form::close() }}
            <div style="display:none;float: right;width: 200px;margin: 0px;height: 35px;position: absolute;left: 70%;z-index: 111;top:22px">
                <a title="Click here to print" target="_blank" href="{{ route('upsMissingInoiceReportPdf') }}"><i class="fa fa-print btn btn-primary"></i></a>
            </div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th style="display: none">Item Generated OR Not</th>
                        <th>Date</th>
                        <th>File Number</th>
                        <th>Voucher No.</th>
                        <th>Cost Item</th>
                        <th>Consignataire / Consignee</th>
                        <th>Shipper</th>
                        <th>Currency</th>
                        <th>Total Amount</th>
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
        DatatableInitiate('UPS', $('.from-date-filter').val(), $('.to-date-filter').val());

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var courierType = $('#courierType').val();
                DatatableInitiate(courierType, fromDate, toDate);
            },
        });
    })

    function DatatableInitiate(courierType = 'UPS', fromDate = '', toDate = '') {
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
                [0, "desc"]
            ],
            "ajax": {
                url: "{{url('reports/listupsMissingInvoiceReport')}}", // json datasource
                data: function(d) {
                    d.courierType = courierType;
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
                if (courierType == 'UPS') {
                    var UpsId = data[0];
                    var editLink = '<?php echo url("ups/viewdetails"); ?>';
                    editLink += '/' + UpsId;
                } else if (courierType == 'upsMaster') {
                    var UpsMasterId = data[0];
                    var editLink = '<?php echo url("ups-master/view"); ?>';
                    editLink += '/' + UpsMasterId;
                    $(row).attr('id', UpsMasterId);
                } else if (courierType == 'Aeropost') {
                    var AeropostId = data[0];
                    var editLink = '<?php echo url("aeropost/viewdetailsaeropost"); ?>';
                    editLink += '/' + AeropostId;
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
                var itemGeneratedOrNot = data[1];
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 2000);
                var thiz = $(this);

                if (itemGeneratedOrNot == "1") {
                    $(row).addClass('billingItemGeneratedForCostItem');
                }

                if (courierType == 'UPS') {
                    var url = '<?php echo route("checkupsoperationfordatatableserverside"); ?>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'UpsId': UpsId,
                            'flag': 'getUpsData'
                        },
                        success: function(data) {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        }
                    });
                    //$(row).attr('id', UpsId);
                } else if (courierType == 'upsMaster') {
                    var url = '<?php echo route("checkoperationsupsmaster"); ?>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'MasterUpsId': UpsMasterId,
                            'flag': 'getMasterUpsData'
                        },
                        success: function(data) {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        }
                    });
                } else if (courierType == 'Aeropost') {
                    var url = '<?php echo route("checkoperationfordatatableserversideaeropost"); ?>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'aeropostId': AeropostId,
                            'flag': 'getFileData'
                        },
                        success: function(data) {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        }
                    });
                    //$(row).attr('id', AeropostId);
                } else if (courierType == 'aeropostMaster') {
                    var url = '<?php echo route("checkoperationsaeropostmaster"); ?>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'MasterAeropostId': AeropostMasterId,
                            'flag': 'getMasterAeropostData'
                        },
                        success: function(data) {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        }
                    });
                } else if (courierType == 'ccpackMaster') {
                    var url = '<?php echo route("checkoperationsccpackmaster"); ?>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'MasterCcpackId': CcpackMasterId,
                            'flag': 'getMasterCcpackData'
                        },
                        success: function(data) {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        }
                    });
                } else {
                    var url = '<?php echo route("checkoperationfordatatableserversideccpack"); ?>';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'ccpackId': CCPackId,
                            'flag': 'getFileData'
                        },
                        success: function(data) {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        }
                    });
                    //$(row).attr('id', CCPackId);
                }

                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $('td', row).eq(9).addClass('alignright');
                i++;
            }
        });
    };
</script>
@stop