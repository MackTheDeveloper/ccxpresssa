@extends('layouts.custom')

@section('title')
Aeropost Master File Expense Listing
@stop

@section('breadcrumbs')
@include('menus.aeropost-expense')
@stop

@section('content')
<section class="content-header">
    <h1>Aeropost Master File Expense Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success cargocontainer">
        <div class="box-body">
            <div class="row" style="margin-bottom:20px">
                {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'filterExpenses','autocomplete'=>'off')) }}
                {{ csrf_field() }}
                <div class="col-md-2">
                    <label>Select Status</label>
                    <select id="expenseStatus" class="form-control saveStateThis">
                        <option selected="" value="">All</option>
                        <option value="Requested">Requested</option>
                        <option value="on Hold">on Hold</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Approved">Approved</option>
                        <option value="Disbursement done">Disbursement done</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Select Type</label>
                    <select id="expenseType" class="form-control saveStateThis">
                        <option selected="" value="">All</option>
                        <option value="1">Cash</option>
                        <option value="2">Credit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="width:100%">&nbsp;</label>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
                {{ Form::close() }}

                <div style="float: right;margin-right: 10px;height: 35px;z-index: 111;top: 18px;">
                    <a style="float:left;margin-right: 10px;" title="Click here to print" target="_blank" href="{{ route('printallaeropostmasterexpense',['all']) }}"><i style="padding:10px" class="fa fa-print btn btn-primary"></i></a>
                    <?php $actionUrl = route('approveallselectedexpense'); ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off','style'=>'float:left;width:auto')) }}
                    {{ csrf_field() }}
                    <input type="hidden" name="ids" class="ids" value="">
                    <input type="hidden" name="flag" value="aeropostMasterExpense">
                    <button style="" type="submit" class="btn btn-success">Approve Expenses</button>
                    {{ Form::close() }}
                </div>
            </div>
            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="trCancelledFileDiv1"></div>
                    <div class="trCancelledFileDiv2">Cancelled</div>
                </div>
            </div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th style="display: none">ID</th>
                        <th>
                            <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                        </th>
                        <th>Date</th>
                        <th>Voucher No.</th>
                        <th>Type</th>
                        <th>File No.</th>
                        <th>AWB / BL No.</th>
                        <th>Cash/Bank</th>
                        <th>Description</th>
                        <th>Consignataire / Consignee</th>
                        <th>Shipper</th>
                        <th>Currency</th>
                        <th>Total Amount</th>
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
                        <th>Status</th>
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
            DatatableInitiate(DataTableState.expenseStatus, DataTableState.expenseType);
        }else{
            DatatableInitiate();
        }

        $(document).delegate('.fa-expand-collapse-all', 'click', function() {
            $('#loading').show();
            if ($(this).hasClass('fa-plus')) {
                $(this).removeClass('fa-plus');
                $(this).addClass('fa-minus');
            } else {
                $(this).removeClass('fa-minus');
                $(this).addClass('fa-plus');
            }
            $('.expandpackage').trigger('click');
        });

        //$('.expandpackage').click(function(){
        $(document).delegate('.expandpackage', 'click', function() {
            var rowId = $(this).data('rowid');
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 200);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var thiz = $(this);
            var parentTR = thiz.closest('tr');
            if (thiz.hasClass('fa-plus')) {
                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var expenseId = $(this).data('expenseid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("expandexpenses"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        expenseId: expenseId,
                        rowId: rowId,
                        'flagW': 'AeropostMaster'
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

        $('#createExpenseForm').on('submit', function(event) {
            if ($('.singlecheckbox:checked').length < 1) {
                alert("Please select the expenses.");
                return false;
            } else {
                if (confirm("Are you sure, you want to approve all the expenses?")) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    })

    $('#filterExpenses').validate({
        submitHandler: function(form) {
            var expenseStatus = $('#expenseStatus').val();
            var expenseType = $('#expenseType').val();
            DatatableInitiate(expenseStatus, expenseType);
        },
    });

    function DatatableInitiate(status = '', expenseType = '') {
        var i = 1;
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
                localStorage.setItem( 'DataTables_'+window.location.pathname, JSON.stringify(data) );
            },
            "columnDefs": [{
                "targets": [0, 2, 13, -1],
                "orderable": false
            }, {
                targets: [1],
                className: "hide_column"
            }],
            "order": [
                [1, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container())
                    .on('click', function() {
                        $('.expandpackage').each(function() {
                            if ($(this).hasClass('fa-minus')) {
                                $(this).removeClass('fa-minus');
                                $(this).addClass('fa-plus');
                            }
                        })
                        if ($('.fa-expand-collapse-all').hasClass('fa-minus')) {
                            $('.fa-expand-collapse-all').removeClass('fa-minus');
                            $('.fa-expand-collapse-all').addClass('fa-plus');
                        }
                    });
                $('#example_filter input').bind('keyup', function(e) {
                    if ($('.fa-expand-collapse-all').hasClass('fa-minus')) {
                        $('.fa-expand-collapse-all').removeClass('fa-minus');
                        $('.fa-expand-collapse-all').addClass('fa-plus');
                    }
                });
            },
            "ajax": {
                url: "{{url('aeropost-master/expense/listbydatatableserverside')}}", // json datasource
                data: function(d) {
                    d.status = status;
                    d.expenseType = expenseType;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var cls = '';
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 1000);
                var expenseId = data[1];
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var url = '<?php echo route("checkcargoexpenseoperationfordatatableserverside"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        'expenseId': expenseId,
                        'flag': 'checkExpense'
                    },
                    success: function(data) {
                        if (data > 0)
                            cls = 'fa fa-plus';

                        $('td', row).eq(2).addClass('expandpackage ' + cls);
                        $('td', row).eq(2).attr('style', 'display: block;text-align: center;padding-top: 13px;');
                        $('td', row).eq(2).attr('data-expenseid', expenseId);
                        $('td', row).eq(2).attr('data-rowid', i);

                        $('td', row).eq(12).attr('style', 'text-align: right;');
                        $('td', row).eq(0).attr('style', 'text-align: center;');

                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'expenseId': expenseId,
                                'flag': 'getExpenseData'
                            },
                            success: function(data) {
                                if (data.deleted == '0') {
                                    <?php if (checkloggedinuserdata() == 'Cashier') { ?>
                                        var editLink = '<?php echo url("aeropost-master/expense/viewaeropostmasterexpenseforcashier"); ?>';
                                        editLink += '/' + expenseId + '/' + data.aeropost_master_id + '/fromNotification';
                                    <?php } else { ?>
                                        var editLink = '<?php echo url("aeropost-master/expense/edit"); ?>';
                                        editLink += '/' + expenseId;
                                    <?php } ?>
                                    $(row).attr('data-editlink', editLink);
                                    $(row).addClass('edit-row');
                                    $(row).attr('id', expenseId);
                                } else {
                                    $(row).addClass('trCancelledFile');
                                }
                            }
                        })
                        i++;
                    }
                })

            }
        });

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
    }
</script>
@stop