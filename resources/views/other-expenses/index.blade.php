@extends('layouts.custom')

@section('title')
Administration Expense Listing
@stop

<?php
$permissionOtherExpenseEdit = App\User::checkPermission(['update_other_expenses'], '', auth()->user()->id);
$permissionOtherExpenseDelete = App\User::checkPermission(['delete_other_expenses'], '', auth()->user()->id);
?>


@section('breadcrumbs')
@include('menus.administration-expense')
@stop



@section('content')
<section class="content-header">
    <h1>Administration Expense Listing</h1>
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
            <div class="col-md-10">
                {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
                {{ csrf_field() }}
                <div class="row" style="margin-bottom:20px">
                    <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                    <div class="filterout col-md-2">
                        <select id="expenseStatus" class="form-control">
                            <option selected="" value="">All</option>
                            <option value="Requested">Requested</option>
                            <option value="on Hold">on Hold</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Approved">Approved</option>
                            <option value="Disbursement done">Disbursement done</option>

                        </select>
                    </div>

                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter" value="<?php echo date('01-m-Y'); ?>">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter" value="<?php echo date('d-m-Y'); ?>">
                    </div>

                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="submit" id="clsPrint" class="btn btn-success">Print</button>
                    <button id="clsExportToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true" style="margin-right: 3%"></i></span>Export To Excel</button>
                </div>
                {{ Form::close() }}
            </div>
            <div class="col-md-2" style="padding:0px;float:right">
                <?php if (checkloggedinuserdata() == 'Other') { ?>
                    <div style="float: right;margin-right: 10px;height: 35px;z-index: 111;top: 18px;">
                        <?php $actionUrl = route('approveallselectedadministrationexpense'); ?>
                        {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off','style'=>'float:left;width:auto')) }}
                        {{ csrf_field() }}
                        <input type="hidden" name="ids" class="ids" value="">
                        <input type="hidden" name="flag" value="aeropostExpense">
                        <button style="" type="submit" class="btn btn-success">Approve Expenses</button>
                        {{ Form::close() }}
                    </div>
                <?php } ?>
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
                        <th style="text-align: center"><input type="checkbox" id="selectAll"></th>
                        <th style="display: none">ID</th>
                        <th>
                            <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                        </th>
                        <th>Date</th>
                        <th>Voucher No.</th>
                        <th>Department</th>
                        <th>Cash/Bank</th>
                        <th>Currency</th>
                        <th>Total Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
            <div class="row totalAmountSection">
                <div class="col-md-4" style="float: right;text-align: right;margin-top: 10px;">
                    <div class="col-md-2"><b>USD: </b></div>
                    <div style="text-align: left" class="totalUsd col-md-4"></div>
                    <div class="col-md-2"><b>HTG: </b></div>
                    <div style="text-align: left" class="totalHtg col-md-4">
                    </div>
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
    $('select,input').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        DatatableInitiate();
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
                var urlzte = '<?php echo route("expandotherexpenses"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        expenseId: expenseId,
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

        $('#createInvoiceForm').on('submit', function(event) {
            /* $('.from-date-filter').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.to-date-filter').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            }); */
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                if (fromDate == '')
                    fromDate = 0;
                var toDate = $('.to-date-filter').val();
                if (toDate == '')
                    toDate = 0;
                var expenseStatus = $('#expenseStatus').val();
                if (expenseStatus == '')
                    expenseStatus = 0;
                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'clsPrint' || submitButtonName == 'clsExportToExcel') {
                    /* $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }) */
                    //var urlztnn = '<?php //echo url("otherexpense/printotherexpensebyfilter"); 
                                        ?>';
                    var urlztnn = '<?php echo url("otherexpense/printotherexpensebyfilter"); ?>';
                    urlztnn += '/' + fromDate + '/' + toDate + '/' + expenseStatus + '/' + submitButtonName;
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'GET',
                        success: function(dataRes) {
                            if (submitButtonName == 'clsPrint')
                                window.open(dataRes, '_blank');
                            else {
                                window.open(urlztnn, '_blank');
                            }
                        }
                    });
                } else {
                    DatatableInitiate(fromDate, toDate, expenseStatus);
                }
            },
        });

        /* $('#expenseStatus').change(function() {
            $('#loading').show();
            var status = $(this).val();
            DatatableInitiate(status);
            $('#loading').hide();
        }) */

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
    })

    function DatatableInitiate(fromDate = '', toDate = '', expenseStatus = '') {
        var i = 1;
        $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                "targets": [0, 2, 9],
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
                url: "{{url('otherexpense/expense/listbydatatableserverside')}}", // json datasource
                dataSrc: function(data) {
                    totalUsd = data.totalUsd;
                    totalHtg = data.totalHtg;
                    $('.totalUsd').text(totalUsd);
                    $('.totalHtg').text(totalHtg);
                    return data.data;
                },
                data: function(d) {
                    d.status = expenseStatus;
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

                var url = '<?php echo route("checkoperationfordatatableserversideadministrationexpense"); ?>';
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

                        $('td', row).eq(8).attr('style', 'text-align: right;');
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
                                        var editLink = '<?php echo url("otherexpense/getprintviewsingleadministrationexpensecashier"); ?>';
                                        editLink += '/' + expenseId + '/fromNotification';
                                    <?php } else { ?>
                                        var editLink = '<?php echo url("otherexpense/edit"); ?>';
                                        editLink += '/' + expenseId + '/flagFromListing';
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
    }
</script>
@stop