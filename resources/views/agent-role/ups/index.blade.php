@extends('layouts.custom')
@section('title')
UPS Files
@stop

<?php
$permissionCourierExpensesAdd = App\User::checkPermission(['add_courier_expenses'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.agent-ups-files')
@stop

@section('content')
<section class="content-header">
    <h1>UPS Files Listing</h1>
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
            <div class="col-md-9">
                {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
                {{ csrf_field() }}
                <div class="row" style="margin-bottom:20px">
                    <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                    <div class="col-md-3">
                        <?php echo Form::select('billing_term', ['P/P' => 'P/P', 'F/C' => 'F/C', 'F/D' => 'F/D'], '', ['class' => 'form-control saveStateThis selectpicker', 'placeholder' => 'All (Billing Term)', 'data-live-search' => 'true', 'id' => 'billing_term']); ?>
                    </div>
                    <div class="from-date-filter-div filterout col-md-3">
                        <input type="text" id="fromDate" name="from_date_filter" placeholder=" -- From Date" class="form-control saveStateThis datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-3">
                        <input type="text" id="toDate" name="to_date_filter" placeholder=" -- To Date" class="form-control saveStateThis datepicker to-date-filter">
                    </div>

                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
                {{ Form::close() }}
            </div>
            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="" style="background: #3097D1;width: 20px;height: 20px;float: left;margin-right: 10px;border-radius: 50%;"></div>
                    <div class="" style="float: left;color: #3097D1;">Billing Party not assigned</div>
                </div>
                <div class="col-md-12">
                    <div class="" style="background: #fb7400;width: 20px;height: 20px;float: left;margin-right: 10px;border-radius: 50%;"></div>
                    <div class="" style="float: left;color: #fb7400;">Billing Term F/C Import & No Manual Invoice</div>
                </div>
                <div class="col-md-12">
                    <div class="" style="background: #ff0000;width: 20px;height: 20px;float: left;margin-right: 10px;border-radius: 50%;"></div>
                    <div class="" style="float: left;color: #ff0000;">Billing Term P/P Export & No Manual Invoice</div>
                </div>
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
                    <tr>
                        <th style="display: none">ID</th>
                        <th></th>
                        <th>File Number</th>
                        <th>Master File Number</th>
                        <th>Billing Party</th>
                        <th>File Status</th>
                        <th>Shipper</th>
                        <th>Consignee</th>
                        <th>Shipment Number</th>
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
                        <th>Date</th>
                        <th>AWB Tracking</th>
                        <th>Package Type</th>
                        <th>Origin</th>
                        <th>Weight</th>
                        <th>Billing Term</th>
                        <th>Commission Received</th>
                        <th>Action</th>
                    </tr>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="modalCreateExpense" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title text-center primecolor">Add Expense</h3>
                </div>
                <div class="modal-body" id="modalContentCreateExpense" style="overflow: hidden;">
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
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.billing_term);
        }else{
            DatatableInitiate();
        }
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        // Apply the search

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var billingTerm = $('#billing_term').val();
                DatatableInitiate(fromDate, toDate, billingTerm);
            },
        });


        //$('.expandpackage').click(function(){
        $(document).delegate('.expandpackage', 'click', function() {
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
                $('.childrw').remove();
                $('.fa-minus').each(function() {
                    $(this).removeClass('fa-minus');
                    $(this).addClass('fa-plus');
                    //$(this).closest('tr').next('tr').remove();
                })

                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var upsId = $(this).data('upsid');
                var rowId = $(this).data('rowid');
                $.ajax({
                    url: 'ups/expandpackage',
                    type: 'POST',
                    data: {
                        upsId: upsId,
                        rowId: rowId
                    },
                    success: function(data) {
                        $(data).insertAfter(parentTR).slideDown();
                    },
                });
                //$('#loading').hide();
            } else {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.childrw').remove();
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            }
        })

    })

    function DatatableInitiate(fromDate = '', toDate = '', billingTerm = '') {
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
                "targets": [1, 15, 10],
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
                url: "{{url('agent/ups/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.billingTerm = billingTerm;
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
                var UpsId = data[0];
                var thiz = $(this);
                var fcCss = '';
                var assignedCss = '';
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo route("checkupsoperationfordatatableserverside"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        'UpsId': UpsId,
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
                                'UpsId': UpsId,
                                'flag': 'getUpsDataWithInvoice'
                            },
                            success: function(data) {
                                if (data.fc == '1') {
                                    var fcCss = 'color:#fb7400';
                                    $(row).attr('style', assignedCss + fcCss);
                                }
                                if (data.pp == '1') {
                                    var fcCss = 'color:#ff0000';
                                    $(row).attr('style', assignedCss + fcCss);
                                }
                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        'UpsId': UpsId,
                                        'flag': 'getUpsData'
                                    },
                                    success: function(data) {
                                        if (data.deleted == '0') {
                                            if (data.file_close == 1) {
                                                $(row).addClass('trClosedFile');
                                            }
                                            var editLink = '<?php echo url("courier/viewcourierdetailforagent"); ?>';
                                            editLink += '/' + UpsId;
                                            $(row).attr('data-editlink', editLink);
                                            $(row).addClass('edit-row');
                                            $(row).attr('id', UpsId);

                                            $.ajax({
                                                url: url,
                                                type: 'POST',
                                                data: {
                                                    'UpsId': UpsId,
                                                    'flag': 'checkPakckages'
                                                },
                                                success: function(data) {
                                                    if (data > 0) {
                                                        $('td', row).eq(1).addClass('expandpackage fa fa-plus');
                                                        $('td', row).eq(1).attr('style', 'display: block;text-align: center;padding-top: 15px;');
                                                        $('td', row).eq(1).attr('data-upsid', UpsId);
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
                    },
                });

            }

        });
    }
</script>
@stop