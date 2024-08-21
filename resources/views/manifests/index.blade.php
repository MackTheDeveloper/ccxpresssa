@extends('layouts.custom')

@section('title')
<?php echo 'Manifests'; ?>
@stop

<?php
$permissionImport = App\User::checkPermission(['import_manifestes'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.manifests')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Manifests'; ?></h1>
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
                <div class="col-md-12">
                    <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>

                    <div class="col-md-2">
                        <?php echo Form::select('port', array(), '', ['class' => 'form-control selectpicker', 'placeholder' => 'Select Port', 'data-live-search' => 'true', 'id' => 'port']); ?>
                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('carrier', array(), '', ['class' => 'form-control selectpicker', 'placeholder' => 'Select Carrier', 'data-live-search' => 'true', 'id' => 'carrier']); ?>
                    </div>
                    <div class="col-md-2">
                        <?php echo Form::select('consignee', array(), '', ['class' => 'form-control selectpicker', 'placeholder' => 'Select Consignee', 'data-live-search' => 'true', 'id' => 'consignee']); ?>
                    </div>
                    <div class="col-md-2" style="margin-top: 15px;margin-left: 94px;">
                        <?php echo Form::select('shipper', array(), '', ['class' => 'form-control selectpicker', 'placeholder' => 'Select Shipper', 'data-live-search' => 'true', 'id' => 'shipper']); ?>
                    </div>
                    <div class="col-md-2" style="margin-top: 15px;">
                        <?php echo Form::select('comodity', array(), '', ['class' => 'form-control selectpicker', 'placeholder' => 'Select Comodity', 'data-live-search' => 'true', 'id' => 'comodity']); ?>
                    </div>
                </div>
                <div class="col-md-12" style="text-align: right;margin-top: 15px">
                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="submit" id="clsPrint" class="btn btn-warning">Print</button>
                    <button id="clsExport" class="btn btn-warning"><span><i class="fa fa-file-excel-o" aria-hidden="true" style="margin-right: 3%"></i></span>Export</button>
                </div>



            </div>
            {{ Form::close() }}

            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th>CntrQty</th>
                        <th>Shipper</th>
                        <th>Consignee</th>
                        <th>Port</th>
                        <th>Weight</th>
                        <th>Comodity</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Ship</th>
                        <th>Trip Number</th>
                        <th>Trip Date</th>
                        <th>Carrier</th>
                        <th>Added On</th>
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
        DatatableInitiate();

        var url1 = '<?php echo url("manifests/getmanifestports"); ?>';
        $.ajax({
            url: url1,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">Select Port</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.port + '">' + v.port + '</option>';
                });
                $('#port').html(html);
                $('#port').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        var url2 = '<?php echo url("manifests/getmanifestcarrier"); ?>';
        $.ajax({
            url: url2,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">Select Carrier</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.carrier + '">' + v.carrier + '</option>';
                });
                $('#carrier').html(html);
                $('#carrier').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        var url3 = '<?php echo url("manifests/getmanifestconsignee"); ?>';
        $.ajax({
            url: url3,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">Select Consignee</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.consignee + '">' + v.consignee + '</option>';
                });
                $('#consignee').html(html);
                $('#consignee').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        var url4 = '<?php echo url("manifests/getmanifestshipper"); ?>';
        $.ajax({
            url: url4,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">Select Shipper</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.shipper + '">' + v.shipper + '</option>';
                });
                $('#shipper').html(html);
                $('#shipper').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        var url5 = '<?php echo url("manifests/getmanifestcomodity"); ?>';
        $.ajax({
            url: url5,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">Select Comodity</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.comodity + '">' + v.comodity + '</option>';
                });
                $('#comodity').html(html);
                $('#comodity').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var port = $('#port').val();
                if (port == '')
                    port = 0;
                var carrier = $('#carrier').val();
                if (carrier == '')
                    carrier = 0;
                var consignee = $('#consignee').val();
                if (consignee == '')
                    consignee = 0;
                var shipper = $('#shipper').val();
                if (shipper == '')
                    shipper = 0;
                var comodity = $('#comodity').val();
                if (comodity == '')
                    comodity = 0;
                var fromDate = $('.from-date-filter').val();
                if (fromDate == '')
                    fromDate = 0;
                var toDate = $('.to-date-filter').val();
                if (toDate == '')
                    toDate = 0;
                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'clsPrint' || submitButtonName == 'clsExport') {
                    var urlztnn = '<?php echo url("manifests/printandexport"); ?>';
                    urlztnn += '/' + fromDate + '/' + toDate + '/' + port + '/' + carrier + '/' + consignee + '/' + shipper + '/' + comodity + '/' + submitButtonName;
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'GET',
                        success: function(dataRes) {
                            if (submitButtonName == 'clsPrint')
                                window.open(dataRes, '_blank');
                            else {
                                window.open(urlztnn, '_blank');
                            }
                        }
                    });
                } else {
                    DatatableInitiate(fromDate, toDate, port, carrier, consignee, shipper, comodity);
                }
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', port = '', carrier = '', consignee = '', shipper = '', comodity = '') {
        $('#example').DataTable({
            "pageLength": 100,
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            //'stateSave': true,
            "displayStart": 0,
            stateSaveParams: function(settings, data) {
                delete data.order;
                delete data.start;
            },
            "columnDefs": [{
                targets: [0],
                className: "hide_column"
            }],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "{{url('manifests/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.port = port;
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.carrier = carrier;
                    d.consignee = consignee;
                    d.shipper = shipper;
                    d.comodity = comodity;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {

            }

        });
    }
</script>
@stop