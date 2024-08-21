@extends('layouts.custom')
@section('title')
UPS Master Files Listing
@stop

@section('breadcrumbs')
@include('menus.warehouse-ups-files')
@stop

@section('content')
<section class="content-header">
    <h1>UPS Master Files Listing<button style="float: right;width: 13%;" id="btnAssignDeliveryBoy" class="btn btn-success btnAssignDeliveryBoy" value="{{route('assign-delivery-boy',['ups'])}}">Assign Delivery Boy</button></h1>
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
                <div class="col-md-2">
                    <?php echo Form::select('file_name', [0 => 'All Files (I, E)', 1 => 'Import', 2 => 'Export'], 0, ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'file_type']); ?>
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
    <div id="modalAssignDeliveryBoy" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title modal-title-block text-center primecolor">Assign Couriers to Delivery Boy</h3>
                    <input type="hidden" name="ids" class="ids" value="">
                </div>
                <div class="modal-body" id="modalContentAssignDeliveryBoy" style="overflow: hidden;">
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
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
                var masterUpsId = $(this).data('masterupsid');
                var rowId = $(this).data('rowid');
                $.ajax({
                    url: 'warehouse/ups-master/expandhousefiles',
                    type: 'POST',
                    data: {
                        masterUpsId: masterUpsId,
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

        $(document).delegate('.clsExportToExcel', 'click', function() {
            var masterUpsId = $(this).data('masterupsid');
            var urlztnn = '<?php echo url("ups-master/exporttoexcelhousefiles"); ?>';
            urlztnn += '/' + masterUpsId;
            $.ajax({
                url: urlztnn,
                async: false,
                type: 'POST',
                data: {
                    'masterUpsId': masterUpsId,
                },
                success: function(dataRes) {
                    window.open(urlztnn, '_blank');
                }
            });
        })

        $(document).delegate("#selectAll", "click", function(e) {
            var materId = $(this).parents('tr').next('tr').data('masterid');
            $('#example .singlecheckbox-' + materId).prop('checked', this.checked);
            var checked = [];
            $('.singlecheckbox-' + materId).each(function() {
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

        $('.btnAssignDeliveryBoy').click(function() {
            if ($('.singlecheckbox:checked').length < 1) {
                alert("Please select the Files.");
                return false;
            } else {
                $('#modalAssignDeliveryBoy').modal('show').find('#modalContentAssignDeliveryBoy').load($(this).attr('value'));
            }
        })
    })

    function DatatableInitiate(fromDate = '', toDate = '', fileType = '') {
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
                url: "{{url('ups-master/listingmasterups')}}", // json datasource
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
                var MasterUpsId = data[0];
                var thiz = $(this);
                var fcCss = '';
                var assignedCss = '';
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo route("checkoperationsupsmaster"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        'MasterUpsId': MasterUpsId,
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
                                'MasterUpsId': MasterUpsId,
                                'flag': 'getMasterUpsData'
                            },
                            success: function(data) {
                                if (data.deleted == '0') {
                                    if (data.file_close == 1) {
                                        $(row).addClass('trClosedFile');
                                    }
                                    var editLink = '<?php echo url("warehouseups/viewcourierdetailforwarehousemaster"); ?>';
                                    editLink += '/' + MasterUpsId;
                                    $(row).attr('data-editlink', editLink);
                                    $(row).addClass('edit-row');
                                    $(row).attr('id', MasterUpsId);

                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            'MasterUpsId': MasterUpsId,
                                            'flag': 'checkHawbFiles'
                                        },
                                        success: function(data) {
                                            if (data.allDelivered == 1) {
                                                $(row).addClass('trHouseFileDelivered');
                                            }
                                            if (data.total > 0) {
                                                $('td', row).eq(1).addClass('expandpackage fa fa-plus');
                                                $('td', row).eq(1).attr('style', 'display: block;text-align: center;padding-top: 15px;');
                                                $('td', row).eq(1).attr('data-masterupsid', MasterUpsId);
                                                $('td', row).eq(1).attr('data-rowid', i);
                                            }
                                            i++;
                                        }
                                    })
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
    }
</script>
@stop