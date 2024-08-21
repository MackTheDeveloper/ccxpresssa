@extends('layouts.custom')

@section('title')
<?php echo 'Guarantee Checks'; ?>
@stop

<?php
$permissionApprove = App\User::checkPermission(['approve_guarantee_check'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.check-guarantee')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Guarantee Checks' ?></h1>
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
                    <div class="from-date-filter-div filterout" style="width: 150px;float:left;margin-right:15px">
                        <label>From Date</label>
                        <input type="text" id="fromDate" name="from_date_filter" placeholder=" -- From" class="form-control datepicker saveStateThis from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout" style="width: 150px;float:left">
                        <label>To Date</label>
                        <input type="text" id="toDate" name="to_date_filter" placeholder=" -- To" class="form-control datepicker saveStateThis to-date-filter">
                    </div>
                    <div class="col-md-2" style="width: 170px;float:left">
                        <label>Container Return?</label>
                        <?php echo Form::select('containerReturn', ['' => 'All', '1' => 'Yes', '0' => 'No'], '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'containerReturn']); ?>
                    </div>
                    <div class="col-md-2" style="width: 170px;float:left">
                        <label>Type</label>
                        <?php echo Form::select('checkType', ['' => 'All', '1' => 'DECSA', '2' => 'Veconinter'], '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'checkType']); ?>
                    </div>
                    <div class="col-md-2" style="width: 180px;float:left">
                        <label>Container Delivered?</label>
                        <?php echo Form::select('containerDelivered', ['' => 'All', '1' => 'Yes', '0' => 'No'], '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'containerDelivered']); ?>
                    </div>
                    <div class="col-md-2" style="width: 170px;float:left">
                        <label>Check Return?</label>
                        <?php echo Form::select('checkReturn', ['' => 'All', '1' => 'Yes', '0' => 'No'], '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'checkReturn']); ?>
                    </div>
                    <div class="col-md-2" style="width: 170px;float:left">
                        <label>Billed Status?</label>
                        <?php echo Form::select('billedStatus', ['' => 'All', '1' => 'Yes', '0' => 'No'], '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'billedStatus']); ?>
                    </div>
                    <div class="col-md-1" style="float: right;text-align:right">
                        <label style="width:100%">&nbsp;</label>
                        <button type="submit" id="clsSubmit" class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
            {{ Form::close() }}

            <?php if ($permissionApprove) { ?>
                <?php $actionUrl = route('checkguaranteeapprove'); ?>
                {{ Form::open(array('url' => $actionUrl,'class'=>'','id'=>'checkguaranteeapprove','autocomplete'=>'off')) }}
                {{ csrf_field() }}
                <div class="row" style="margin-bottom:20px">
                    <input type="hidden" name="ids" class="ids" value="">
                    <div class="col-md-12" style="text-align: right">
                        <button style="text-align:right" type="submit" class="btn btn-success">Approve</button>
                    </div>
                </div>
                {{ Form::close() }}
            <?php } ?>

            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th style="display: none">ID</th>
                        <th style="display: none">Opetion 1</th> <!-- Opetion 1 = module ID -->
                        <th style="display: none">Opetion 2</th> <!-- Opetion 2 = module operation type (import/export) -->
                        <th>File Number</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>DESCA/Veconinter Invoice Number</th>
                        <th>Check Number</th>
                        <th>Detention Days</th>
                        <th>Delivered Date</th>
                        <th>Return Date</th>
                        <th>Check Return?</th>
                        <th>Amount</th>
                        <th>Balance</th>
                        <th>Total Cost</th>
                        <th>Billed Amount</th>
                        <th>Difference</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
<div id="modalEditCheck" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title modal-title-block text-center primecolor">Update Check Guarantee</h3>
            </div>
            <div class="modal-body" id="modalContentEditCheck" style="overflow: hidden;">
            </div>
        </div>
    </div>
</div>
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
        // DatatableInitiate();
        var DataTableState = JSON.parse(localStorage.getItem('DataTables_' + window.location.pathname));
        if (DataTableState) {
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.checkReturn, DataTableState.checkType, DataTableState.billedStatus, DataTableState.containerReturn, DataTableState.containerDelivered);
        } else {
            DatatableInitiate();
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var checkReturn = $('#checkReturn').val();
                var checkType = $('#checkType').val();
                var billedStatus = $('#billedStatus').val();
                var containerReturn = $('#containerReturn').val();
                var containerDelivered = $('#containerDelivered').val();
                DatatableInitiate(fromDate, toDate, checkReturn, checkType, billedStatus, containerReturn, containerDelivered);
            },
        });

        $('#checkguaranteeapprove').on('submit', function(event) {
            if ($('.singlecheckbox:checked').length < 1) {
                alert("Please select the record.");
                return false;
            } else {
                if (confirm("Are you sure, you want to approve?")) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', checkReturn = '', checkType = '', billedStatus = '', containerReturn = '', containerDelivered = '') {
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
                localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(data));
            },
            "columnDefs": [{
                    "targets": [0, -1],
                    "orderable": false
                },
                {
                    targets: [1, 2, 3],
                    className: "hide_column"
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "{{url('check-guarantee/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.checkReturn = checkReturn;
                    d.checkType = checkType;
                    d.containerReturn = containerReturn;
                    d.containerDelivered = containerDelivered;
                    d.billedStatus = billedStatus;
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
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 1000);
                var moduleId = data[2];
                var Operation1 = data[3];

                var editLink = '<?php echo url("cargo/viewcargo"); ?>';
                editLink += '/' + moduleId + '/' + Operation1;
                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $(row).attr('id', moduleId);
                var thiz = $(this);
                $('td', row).eq(13).addClass('alignright');
                $('td', row).eq(14).addClass('alignright');
                $('td', row).eq(15).addClass('alignright');
                $('td', row).eq(16).addClass('alignright');
                $('td', row).eq(17).addClass('alignright');
                $('td', row).eq(0).attr('style', 'text-align: center;');
            }

        });
    }

    $(document).delegate("#selectAll", "click", function(e) {
        $('#example .singlecheckbox').prop('checked', this.checked);
        var checked = [];
        $('input[name="singlecheckbox"]').each(function() {
            if ($(this).prop('checked') == true) {
                checked.push($(this).attr('id'))
            }
        });
        $('.ids').val(checked);
        //console.log(checked);
    });

    $(document).on('click', '.singlecheckbox', function() {
        var checkedFlag = 0;
        $('input[name="singlecheckbox"]').each(function() {
            if ($(this).prop('checked') == true) {
                checkedFlag = 1;
            } else {
                checkedFlag = 0;
                return false;
            }
        });
        if (checkedFlag == 0) {
            $('#selectAll').prop('checked', false);
        }
        if (checkedFlag == 1) {
            $('#selectAll').prop('checked', true);
        }

        var checked = [];
        $('input[name="singlecheckbox"]').each(function() {
            if ($(this).prop('checked') == true) {
                checked.push($(this).attr('id'))
            }
        });
        $('.ids').val(checked);
        //console.log(checked);
    });
</script>
@stop