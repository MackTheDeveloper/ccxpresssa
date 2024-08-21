@extends('layouts.custom')

@section('title')
Check Guarantee to pay
@stop
<?php

use App\Currency;
?>
@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Check Guarantee to pay</h1>
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
                        <?php echo Form::select('moduleType', ['masterCargo' => 'Cargo'], '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'moduleType']); ?>
                    </div>
                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" id="clsSubmit" class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
            {{ Form::close() }}

            <div class="container-rep">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th style="display: none">Opetion 1</th>
                            <th>File Number</th>
                            <th>Check Number</th>
                            <th>Check Date</th>
                            <th>DECSA Invoice Number</th>
                            <th>Check Amount</th>
                            <th>Check Amount Deducted</th>
                            <th>Balance</th>
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
                var moduleType = $('#moduleType').val();
                DatatableInitiate(fromDate, toDate, moduleType);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', moduleType = 'masterCargo') {
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
                url: "{{url('reports/checkguaranteetopayreport/list')}}", // json datasource
                data: function(d) {
                    d.moduleType = moduleType;
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
                var Operation1 = data[1];
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 2000);
                if (moduleType == 'masterCargo') {
                    var editLink = '<?php echo url("cargo/viewcargo"); ?>';
                    editLink += '/' + moduleId + '/' + Operation1;
                }
                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $(row).attr('id', moduleId);
                var thiz = $(this);
                $('td', row).eq(6).addClass('alignright');
                $('td', row).eq(7).addClass('alignright');
                $('td', row).eq(8).addClass('alignright');
                i++;
            }
        });
    }
</script>
@stop