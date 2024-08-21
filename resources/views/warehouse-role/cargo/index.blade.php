@extends('layouts.custom')
@section('title')
Cargo Files Listing
@stop

<?php
$permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.warehouse-cargo-files')
@stop



@section('content')
<section class="content-header">
    <h1>Cargo Files Listing</h1>
</section>


<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    @if(Session::has('flash_message_error'))
    <div class="alert alert-danger flash-danger">
        {{ Session::get('flash_message_error') }}
    </div>
    @endif

    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success cargocontainer">
        <div class="box-body">
            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}

            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="col-md-2">
                    <?php echo Form::select('scan', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'cargomasterscan', 'placeholder' => 'All']); ?>
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
                <div class="col-md-12" style="margin-top: 5px">
                    <div class="trHouseFileDeliveredDiv1"></div>
                    <div class="trHouseFileDeliveredDiv2">Delivered</div>
                </div>
            </div>
            <table id="example" class="display nowrap" style="width:100%;">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th></th>
                        <th>File No.</th>
                        <th>Billing Party</th>
                        <th>File Status</th>
                        <th>Agent</th>
                        <th>Opening Date</th>
                        <th>AWB/BL No</th>
                        <th>Consignee/Client</th>
                        <th>Shipper</th>
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
            console.log('DataTableState',DataTableState)
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.cargomasterscan);
        }else{
            DatatableInitiate();
        }
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $(document).delegate('.expandpackage', 'click', function() {
            var rowId = $(this).data('rowid');
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 200);
            //$('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var thiz = $(this);
            var parentTR = thiz.closest('tr');
            if (thiz.hasClass('fa-plus')) {
                /*$('.childrw').remove();
                $('.fa-minus').each(function(){
                    $(this).removeClass('fa-minus');    
                    $(this).addClass('fa-plus');
                })*/

                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var cargoid = $(this).data('cargoid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("expandhawbnumber"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        cargoid: cargoid,
                        rowId: rowId,
                        flagcargo: 'warehousecargo'
                    },
                    success: function(data) {

                        $(data).insertAfter(parentTR).slideDown();
                    },
                });
                //$('#loading').hide();
            } else {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.child-' + rowId).remove();
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            }
        })

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fileStatus = $('#cargomasterscan').val();
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
                //delete data.order;
                $('.saveStateThis').each(function() {
                    data[$(this).attr('id')] = $(this).val();
                });
                localStorage.setItem( 'DataTables_'+window.location.pathname, JSON.stringify(data) );
            },
            "columnDefs": [{
                    "targets": [1, 10],
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
                url: "{{url('warehouse/cargo/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
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
                }, 2000);
                var cargoId = data[0];
                var thiz = $(this);
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo url("cargo/checkoperationfordatatableserverside"); ?>';
                //var url = "{{url('cargo/checkoperationfordatatableserverside')}}", // json datasource
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        'cargoid': cargoId,
                        'flag': 'checkFileAssgned'
                    },
                    success: function(data) {
                        if (data == 'no')
                            $(row).attr('style', 'color: #3097D1');
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'cargoid': cargoId,
                                'flag': 'getCargoData'
                            },
                            success: function(data) {
                                if (data.deleted == '0') {
                                    if (data.file_close == 1) {
                                        $(row).addClass('trClosedFile');
                                    }
                                    var editLink = '<?php echo url("cargo/viewcargodetailforwarehouse"); ?>';
                                    editLink += '/' + cargoId;
                                    $(row).attr('data-editlink', editLink);
                                    $(row).addClass('edit-row');
                                    $(row).attr('id', cargoId);
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            'cargoid': cargoId,
                                            'flag': 'checkHawbFiles'
                                        },
                                        success: function(data) {
                                            if (data.allDelivered == 1) {
                                                $(row).addClass('trHouseFileDelivered');
                                            }
                                            if (data.total > 0) {
                                                $('td', row).eq(1).addClass('expandpackage fa fa-plus');
                                                $('td', row).eq(1).attr('style', 'display: block;text-align: center;padding-top: 15px;');
                                                $('td', row).eq(1).attr('data-cargoid', cargoId);
                                                $('td', row).eq(1).attr('data-rowid', i);
                                            }
                                            i++;
                                        },
                                    });
                                } else {
                                    $(row).addClass('trCancelledFile');
                                }
                            },
                        });
                    },
                });
                i++;
            }

        });
    };
</script>
@stop