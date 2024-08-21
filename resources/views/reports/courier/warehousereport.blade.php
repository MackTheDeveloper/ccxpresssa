@extends('layouts.custom')

@section('title')
Warehouse Report
@stop

@section('sidebar')

@stop

@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Warehouse Report</h1>
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
                            <option value="Aeropost">Aeropost</option>
                            <option value="CCPack">CCPack</option>
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
                        <?php echo Form::select('warehouse', $warehouses, '', ['class' => 'form-control selectpicker fvendor_type', 'data-live-search' => 'true', 'id' => 'warehouse', 'placeholder' => 'All']); ?>
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
                        <th>File No.</th>
                        <th>AWB/BL No.</th>
                        <th>Consignee/Client</th>
                        <th>Shipper</th>
                        <th>Warehouse</th>
                        <th>Warehouse Status</th>
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
                var fileType = $('#fileType').val();
                var warehouse = $('#warehouse').val();
                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'clsPrint') {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    var urlztnn = '<?php echo url("reports/warehousereportcourierpdf"); ?>';
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'POST',
                        data: {
                            'courierType': courierType,
                            'fileType': fileType,
                            'warehouse': warehouse
                        },
                        success: function(dataRes) {
                            window.open(dataRes, '_blank');
                        }
                    });
                } else {
                    DatatableInitiate(courierType, fileType, warehouse);
                }
            },
        });
    })

    function DatatableInitiate(courierType = 'UPS', fileType = '', warehouse = '') {
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
                url: "{{url('reports/listwarehousereportcourier')}}", // json datasource
                data: function(d) {
                    d.courierType = courierType;
                    d.fileType = fileType;
                    d.warehouse = warehouse;
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
                } else if (courierType == 'Aeropost') {
                    var AeropostId = data[0];
                    var editLink = '<?php echo url("aeropost/viewdetailsaeropost"); ?>';
                    editLink += '/' + AeropostId;
                    $(row).attr('id', AeropostId);
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