@extends('layouts.custom')

@section('title')
House AWB Files Listing
@stop

<?php
$permissionCargoHAWBEdit = App\User::checkPermission(['update_cargo_hawb'], '', auth()->user()->id);
$permissionCargoHAWBDelete = App\User::checkPermission(['delete_cargo_hawb'], '', auth()->user()->id);
?>


<?php if (checkloggedinuserdata() == 'Agent') { ?>
    @section('breadcrumbs')
    @include('menus.agent-cargo-files')
    @stop
<?php } else { ?>
    @section('breadcrumbs')
    @include('menus.cargo-files')
    @stop
<?php } ?>

@section('breadcrumbs')
@include('menus.cargo-files')
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
                    <input type="text" name="from_date_filter" id="fromDate" placeholder=" -- From Date" class="form-control saveStateThis datepicker from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" name="to_date_filter" id="toDate" placeholder=" -- To Date" class="form-control saveStateThis datepicker to-date-filter">
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
                        <th>Type</th>
                        <th>File No.</th>
                        <th>Billing Party</th>
                        <th>File Status</th>
                        <th>Opening Date</th>
                        <th>House AWB No.</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
                        <th>Master File</th>
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
                    "targets": [5, 6, 9, 10, -1],
                    "orderable": false
                },
                {
                    targets: [0],
                    className: "hide_column"
                }
            ],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: "{{url('hawbfile/listbydatatableserverside')}}", // json datasource
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
                /* setTimeout(function() {
                    $("#loading").hide();
                }, 2000); */
                var houseId = data[0];
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
                        } else {
                            $(row).addClass('trCancelledFile');
                        }
                        <?php if (checkloggedinuserdata() == 'Agent') { ?>
                            var editLink = '<?php echo url("cargo/viewhawbdetailforagent"); ?>';
                            editLink += '/' + houseId;
                        <?php } else { ?>
                            var editLink = '<?php echo url("hawbfile/view"); ?>';
                            editLink += '/' + houseId;
                        <?php } ?>

                        $(row).attr('data-editlink', editLink);
                        $(row).addClass('edit-row');
                        $(row).attr('id', houseId);
                    },
                });
                i++;
                $("#loading").hide();
            }
        });
    };
</script>
@stop