@extends('layouts.custom')
@section('title')
Aeropost Master Files Listing
@stop

@section('breadcrumbs')
@include('menus.aeropost')
@stop

@section('content')
<section class="content-header">
    <h1>Aeropost Master Files Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message_import'))
    <div class="alert alert-success-custom flash-success">
        <span><?php echo Session::get('flash_message_import')['totalUploaded']; ?></span><br />
        <span><?php echo Session::get('flash_message_import')['totalAdded']; ?></span><br />
        <span><?php echo Session::get('flash_message_import')['totalUpdated']; ?></span><br /><br />
        <span><a href="{{route('viewlogfiles')}}">View Log Files</a></span>
    </div>
    @endif
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
    <div class="box box-success">
        <div class="box-body">
            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}

            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="from-date-filter-div filterout col-md-2" style="display: block">
                    <input type="text" name="from_date_filter" id="fromDate" placeholder=" -- From Date" class="saveStateThis form-control datepicker from-date-filter">
                </div>
                <div class=" to-date-filter-div filterout col-md-2" style="display: block">
                    <input type="text" name="to_date_filter" id="toDate" placeholder=" -- To Date" class="saveStateThis form-control datepicker to-date-filter">
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
            {{ Form::close() }}

            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="" style="background: #3097D1;width: 20px;height: 20px;float: left;margin-right: 10px;border-radius: 50%;"></div>
                    <div class="" style="float: left;color: #3097D1;">Billing Party not assigned</div>
                </div>
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

            <div class="container-rep courier_container">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th></th>
                            <th>Date</th>
                            <th>File Number</th>
                            <th>AWB Tracking</th>
                            <th>Consignee</th>
                            <th>Shipper</th>
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
        /* var fromDate = $('.from-date-filter').val();
        var toDate = $('.to-date-filter').val();
        DatatableInitiate(fromDate, toDate); */
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.file_type);
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
            var thiz = $(this);
            var parentTR = thiz.closest('tr');
            if (thiz.hasClass('fa-plus')) {
                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var masterAeropostId = $(this).data('masteraeropostid');
                var rowId = $(this).data('rowid');
                $.ajax({
                    url: 'aeropost-master/expandhousefiles',
                    type: 'POST',
                    data: {
                        masterAeropostId: masterAeropostId,
                        rowId: rowId
                    },
                    success: function(data) {
                        $(data).insertAfter(parentTR).slideDown();
                    },
                });
            } else {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.child-' + rowId).remove();
            }
        })

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fileType = $('#file_type').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                DatatableInitiate(fromDate, toDate, fileType);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', fileType = '') {
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
                "targets": [1],
                "orderable": false
            }, {
                targets: [0],
                className: "hide_column"
            }],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
                    /* $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200); */
                    $('.expandpackage').each(function() {
                        if ($(this).hasClass('fa-minus')) {
                            $(this).removeClass('fa-minus');
                            $(this).addClass('fa-plus');
                        }
                    })
                });
                $('#example_filter input').bind('keyup', function(e) {
                    /* $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200); */
                });
            },
            "ajax": {
                url: "{{url('aeropost-master/listingmasteraeropost')}}", // json datasource
                data: function(d) {
                    d.fileType = fileType;
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
                var MasterAeropostId = data[0];
                var thiz = $(this);
                var fcCss = '';
                var assignedCss = '';
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo route("checkoperationsaeropostmaster"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        'MasterAeropostId': MasterAeropostId,
                        'flag': 'checkFileAssgned'
                    },
                    success: function(data) {
                        var assignedCss = '';
                        if (data == 'no') {
                            var assignedCss = 'color:#3097D1;';
                            $(row).attr('style', assignedCss);
                        }
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'MasterAeropostId': MasterAeropostId,
                                'flag': 'getMasterAeropostData'
                            },
                            success: function(data) {
                                if (data.deleted == '0') {
                                    if (data.file_close == 1) {
                                        $(row).addClass('trClosedFile');
                                    }
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            'MasterAeropostId': MasterAeropostId,
                                            'flag': 'checkHawbFiles'
                                        },
                                        success: function(data) {
                                            if (data.allDelivered == 1) {
                                                $(row).addClass('trHouseFileDelivered');
                                            }
                                            if (data.total > 0) {
                                                $('td', row).eq(1).addClass('expandpackage fa fa-plus');
                                                $('td', row).eq(1).attr('style', 'display: block;text-align: center;padding-top: 15px;');
                                                $('td', row).eq(1).attr('data-masteraeropostid', MasterAeropostId);
                                                $('td', row).eq(1).attr('data-rowid', i);
                                            }
                                        }
                                    })
                                } else {
                                    $(row).addClass('trCancelledFile');
                                }
                                <?php if (checkloggedinuserdata() == 'Agent') { ?>
                                    var editLink = '<?php echo url("agent/aeropost-master/view"); ?>';
                                    editLink += '/' + MasterAeropostId;
                                <?php } else { ?>
                                    var editLink = '<?php echo url("aeropost-master/view"); ?>';
                                    editLink += '/' + MasterAeropostId;
                                <?php } ?>
                                $(row).attr('data-editlink', editLink);
                                $(row).addClass('edit-row');
                                $(row).attr('id', MasterAeropostId);
                            },
                        });
                    },
                });
                i++;
            }
        });
    }
</script>
@stop