@extends('layouts.custom')

@section('title')
CCPack Files Listing
@stop


@section('breadcrumbs')
@include('menus.ccpack')
@stop
<?php
$permissionUpdateCcpack = App\User::checkPermission(['update_ccpack'], '', auth()->user()->id);
$permissionDeleteCcpack = App\User::checkPermission(['delete_ccpack'], '', auth()->user()->id);
$permissionCcpackAddInvoice = App\User::checkPermission(['add_ccpack_invoices'], '', auth()->user()->id);
?>
<?php

use App\Ups; ?>
@section('content')
<section class="content-header">
    <h1>CCPack Files Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="box box-success">
        <div class="box-body">

            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="col-md-2" style="display: none">
                    <?php echo Form::select('file_name', [0 => 'All Files (I, E)', 1 => 'Import', 2 => 'Export'], 0, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'ccpacklisting']); ?>
                </div>
                <div class="col-md-2">
                    <?php echo Form::select('scan', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'ccpackscan', 'placeholder' => 'All']); ?>
                </div>
                <div class="from-date-filter-div filterout col-md-2" style="display: block">
                    <input type="text" name="from_date_filter" placeholder=" -- From Date" id="fromDate" class="form-control saveStateThis datepicker from-date-filter">
                </div>
                <div class=" to-date-filter-div filterout col-md-2" style="display: block">
                    <input type="text" name="to_date_filter" placeholder=" -- To Date" id="toDate" class="form-control saveStateThis datepicker to-date-filter">
                </div>

                <button type="submit" class="btn btn-success">Submit</button>
            </div>
            {{ Form::close() }}

            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="" style="background: #FFCCCA;width: 20px;height: 20px;float: left;margin-right: 10px;border-radius: 50%;"></div>
                    <div class="" style="float: left;padding: 0px 10px 0px 10px;background: #FFCCCA">File Closed</div>
                </div>
                <div class="col-md-12" style="margin-top: 5px">
                    <div class="trCancelledFileDiv1"></div>
                    <div class="trCancelledFileDiv2">Cancelled</div>
                </div>
            </div>
            <div class="ccpack_container">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>

                        <tr>
                            <th style="display: none">ID</th>
                            <th>File Number</th>
                            <th>Master File Number</th>
                            <th>Billing Party</th>
                            <th>File Status</th>
                            <th>Arrival Date</th>
                            <th>Awb Number</th>
                            <th>Consignee Name</th>
                            <th>Shipper Name</th>
                            <th>
                                <div style="float:left;margin-right:10px">Invoice Numbers</div>
                                <div style="float:left;margin-top:5px">
                                    <div style="background: red;margin-right:5px;float: left;height: 10px;width: 10px;border-radius: 50%;"></div>
                                    <div style="float: left;margin-right: 10px;font-size: 12px;padding: 0px;line-height: 10px;">Pending</div>
                                    <div style="background: green;width: 10px;height: 10px;border-radius: 50%;float: left;
                            margin-right: 5px;"></div>
                                    <div style="float: left;font-size: 12px;line-height: 10px;">Paid</div>
                                </div>
                            </th>
                            <th>No. Of Pcs</th>
                            <th>Weight</th>
                            <th>Freight</th>
                            <th>Action</th>
                        </tr>

                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div id="modalAddCashCreditWarehouseInFile" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title modal-title-block text-center primecolor">Add</h3>
                </div>
                <div class="modal-body" id="modalContentAddCashCreditWarehouseInFile" style="overflow: hidden;">
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
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        /* var fromDate = $('.from-date-filter').val();
        var toDate = $('.to-date-filter').val();
        DatatableInitiate(fromDate, toDate); */
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.ccpacklisting, DataTableState.ccpackscan);
        }else{
            DatatableInitiate();
        }

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var ccPackType = $('#ccpacklisting').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var fileStatus = $('#ccpackscan').val();
                DatatableInitiate(fromDate, toDate, ccPackType, fileStatus);
            },
        });
    });

    function DatatableInitiate(fromDate = '', toDate = '', ccPackType = '', fileStatus = '') {
        var table = $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            "displayStart": 0,
            stateSaveParams: function(settings, data) {
                //delete data.order;
                delete data.start;
                $('.saveStateThis').each(function() {
                    data[$(this).attr('id')] = $(this).val();
                });
                localStorage.setItem( 'DataTables_'+window.location.pathname, JSON.stringify(data) );
            },
            "columnDefs": [{
                "targets": [-1],
                "orderable": false
            }, {
                targets: [0],
                className: "hide_column"
            }],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: "{{url('ccpack/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.ccPackType = ccPackType;
                    d.fileStatus = fileStatus;
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
                var ccpackId = data[0];
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo url("ccpack/checkoperationfordatatableserversideccpack"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'ccpackId': ccpackId,
                        'flag': 'getFileData'
                    },
                    success: function(data) {
                        if (data.deleted == '0') {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                        } else {
                            $(row).addClass('trCancelledFile');
                        }
                        <?php if (checkloggedinuserdata() == 'Agent') { ?>
                            var editLink = '<?php echo url("ccpack/viewccpackdetailforagent"); ?>';
                            editLink += '/' + ccpackId;
                        <?php } else { ?>
                            var editLink = '<?php echo url("ccpack/viewdetailsccpack"); ?>';
                            editLink += '/' + ccpackId;
                        <?php } ?>

                        $(row).attr('data-editlink', editLink);
                        $(row).addClass('edit-row');
                        $(row).attr('id', ccpackId);
                    },
                });
                $('td', row).eq(10).attr('style', 'text-align: right;');
            }
        });
    }
</script>
@endsection