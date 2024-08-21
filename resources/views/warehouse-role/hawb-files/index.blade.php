@extends('layouts.custom')

@section('title')
House AWB Files Listing
@stop

<?php
$permissionCargoHAWBEdit = App\User::checkPermission(['update_cargo_hawb'], '', auth()->user()->id);
$permissionCargoHAWBDelete = App\User::checkPermission(['delete_cargo_hawb'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.warehouse-cargo-files')
@stop

@section('content')
<section class="content-header">
    <h1>House AWB Files Listing</h1>
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
                <div class="col-md-2">
                    <?php echo Form::select('scan', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'hawbscan', 'placeholder' => 'All']); ?>
                </div>

                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" id="fromDate" name="from_date_filter" placeholder=" -- From Date" class="form-control saveStateThis datepicker from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" id="toDate" name="to_date_filter" placeholder=" -- To Date" class="form-control saveStateThis datepicker to-date-filter">
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
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th style="display: none">Master File ID</th>
                        <th>Type</th>
                        <th>File No.</th>
                        <th>Billing Party</th>
                        <th>File Status</th>
                        <th>Opening Date</th>
                        <th>House AWB No.</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
                        <th>Master File</th>
                        <th>Warehouse Status</th>
                        <th>Shipment Received Date</th>
                        <th>Shipment Delivered Date</th>
                        <th>No. Of Days</th>
                        <th>Total Storage Charge</th>
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
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.hawbscan);
        }else{
            DatatableInitiate();
        }

        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fileStatus = $('#hawbscan').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                DatatableInitiate(fromDate, toDate, fileStatus);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', fileStatus = '') {
        var i = 1;
        var table = $('#example').DataTable({
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
                    "targets": [6, 7, 10, 11, 14, 15, -1],
                    "orderable": false
                },
                {
                    targets: [0, 1],
                    className: "hide_column"
                }
            ],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: "{{url('warehouse/hawbfile/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.fileStatus = fileStatus;
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
                }, 2000);
                var houseId = data[0];
                var masterFileId = data[1];
                var thiz = $(this);
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo url("hawbfile/checkoperationfordatatableserversidehawbfiles"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'houseId': houseId,
                        'flag': 'getHouseFileData'
                    },
                    success: function(data) {
                        if (data.deleted == '0') {
                            if (data.file_close == 1) {
                                $(row).addClass('trClosedFile');
                            }
                            
                            if (masterFileId != '' && masterFileId != null) {
                                var editLink = '<?php echo url("cargo/cargowarehouseflow"); ?>';
                                editLink += '/' + masterFileId + '/' + houseId;
                                $(row).attr('data-editlink', editLink);
                                $(row).addClass('edit-row');
                                $(row).attr('id', houseId);
                            }
                        } else {
                            $(row).addClass('trCancelledFile');
                        }
                    },
                });
                $('td', row).eq(13).addClass('alignright');
                $('td', row).eq(14).addClass('alignright');
                i++;
                $("#loading").hide();
            }
        });
    };
</script>
@stop