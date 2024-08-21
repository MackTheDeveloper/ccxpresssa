<?php $__env->startSection('title'); ?>
Account Payable
<?php $__env->stopSection(); ?>
<?php

use App\Currency;

$permissionApprove = App\User::checkPermission(['account_payable_approve_expense'], '', auth()->user()->id);
?>
<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Account Payable</h1>
</section>

<section class="content">
    <?php if(Session::has('flash_message')): ?>
    <div class="alert alert-success flash-success">
        <?php echo e(Session::get('flash_message')); ?>

    </div>
    <?php endif; ?>
    <?php if(Session::has('flash_message_disbursement')): ?>
    <div class="alert alert-success flash-success">
        <?php echo e(Session::get('flash_message_disbursement')); ?>

    </div>
    <?php endif; ?>
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off'))); ?>

            <?php echo e(csrf_field()); ?>

            <div class="row" style="margin-bottom:20px">
                <div class="filterout col-md-12">
                    <div class="col-md-2">
                        <lable><b>Module</b></lable>
                        <?php echo Form::select('modules', ['Cargo' => 'Cargo', 'House File' => 'House File', 'UPS' => 'UPS', 'upsMaster' => 'UPS Master', 'Aeropost' => 'Aeropost', 'aeropostMaster' => 'Aeropost Master', 'CCPack' => 'CCPack', 'ccpackMaster' => 'CCPack Master'], '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'modules']); ?>
                    </div>
                    <div class="col-md-2">
                        <lable><b>Vendor</b></lable>
                        <?php echo Form::select('vendors[]', $vendors, '', ['class' => 'form-control selectpicker fvendors', 'data-live-search' => 'true', 'id' => 'vendors', 'multiple' => true]); ?>
                    </div>
                    <div class="col-md-2">
                        <lable><b>Duration</b></lable>
                        <?php echo Form::select('duration', Config::get('app.durationForAccountPayable'), '', ['class' => 'form-control selectpicker fduration', 'data-live-search' => 'true', 'id' => 'duration']); ?>
                    </div>
                    <div class="from-date-filter-div filterout col-md-2" style="display: none">
                        <lable><b>From Date</b></lable>
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2" style="display: none">
                        <lable><b>To Date</b></lable>
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                        <lable class="col-md-12"><b>&nbsp;</b></lable>
                        <button type="submit" id="clsSubmit" class="btn btn-success">Submit</button>
                        <button id="clsExportToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i></span> Export</button>
                    </div>
                </div>
            </div>
            <?php echo e(Form::close()); ?>


            <?php if ($permissionApprove) { ?>
                <?php $actionUrl = route('approveexpenseinaccountpayablereport'); ?>
                <?php echo e(Form::open(array('url' => $actionUrl,'class'=>'','style' => 'width: auto;float: right;margin-left: 20px;','id'=>'approveexpenseinaccountpayablereport','autocomplete'=>'off'))); ?>

                <?php echo e(csrf_field()); ?>

                <div class="row" style="margin-bottom:20px">
                    <input type="hidden" name="ids" class="ids" value="">
                    <input type="hidden" name="moduleForApproval" class="moduleForApproval" value="">
                    <div class="col-md-12" style="text-align: right">
                        <button style="text-align:right" type="submit" class="btn btn-success">Approve</button>
                    </div>
                </div>
                <?php echo e(Form::close()); ?>

            <?php } ?>
            <div style="float: right"><span>Total Disbursement: </span><span style="margin-right: 15px" class="totalDisbursement">0.00</span><button style="" id="btnApDisbursement" class="btn btn-success btnApDisbursement" value="<?php echo e(route('ap-disbursement')); ?>">Disbursement</button></div>

            <div class="container-rep">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align: center"><input type="checkbox" id="selectAll"></th>
                            <th style="display: none">ID</th>
                            <th style="text-align: center">
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Vendor Name</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div id="modalApDisbursement" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title modal-title-block text-center primecolor">Disbursement</h3>
                    <input type="hidden" name="idsforapdisbursement" class="idsforapdisbursement" value="">
                </div>
                <div class="modal-body" id="modalContentApDisbursement" style="overflow: hidden;">
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        DatatableInitiate();
        $('.moduleForApproval').val($('#modules').val());
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#modules').change(function() {
            $('.moduleForApproval').val($(this).val());
        })

        $('#createInvoiceForm').validate({
            /* rules: {
                "vendors[]": {
                    required: true
                }
            }, */
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                if (fromDate == '')
                    fromDate = 0;
                var toDate = $('.to-date-filter').val();
                if (toDate == '')
                    toDate = 0;
                var modules = $('#modules').val();
                if (modules == '')
                    modules = 0;
                var vendors = $('#vendors').val();
                if (vendors == '')
                    vendors = 0;
                var duration = $('#duration').val();
                if (duration == '')
                    duration = 0;

                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'clsExportToExcel') {
                    $('.moduleForApproval').val($('#modules').val());
                    var urlztnn = '<?php echo url("reports/exportaccountpayablereport"); ?>';
                    urlztnn += '/' + fromDate + '/' + toDate + '/' + modules + '/' + vendors + '/' + duration;
                    $.ajax({
                        url: urlztnn,
                        async: false,
                        type: 'POST',
                        data: {
                            'fromDate': fromDate,
                            'toDate': toDate,
                            'modules': modules,
                            'vendors': vendors,
                            'duration': duration,
                        },
                        success: function(dataRes) {
                            window.open(urlztnn, '_blank');
                        }
                    });
                } else {
                    $('.moduleForApproval').val($('#modules').val());
                    DatatableInitiate(fromDate, toDate, modules, vendors, duration);
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "vendors[]") {
                    var pos = $('.fvendors button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

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

        $(document).delegate('.expandpackage', 'click', function() {
            var rowId = $(this).data('rowid');
            var modules = $('#modules').val();
            var duration = $('#duration').val();
            var fromDate = $('.from-date-filter').val();
            var toDate = $('.to-date-filter').val();
            $('#loading').show();
            /* setTimeout(function() {
                $("#loading").hide();
            }, 200); */
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
                var vendorId = $(this).data('vendorid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("getaccountpayablereportdata"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        vendorId: vendorId,
                        rowId: rowId,
                        modules: modules,
                        fromDate: fromDate,
                        toDate: toDate,
                        duration: duration,
                    },
                    success: function(data) {
                        $(data).insertAfter(parentTR).slideDown();
                        $("#loading").hide();
                    },
                });
            } else {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.child-' + rowId).remove();
                $("#loading").hide();
            }
        })

        $('#approveexpenseinaccountpayablereport').on('submit', function(event) {
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

    function DatatableInitiate(fromDate = '', toDate = '', modules = 'Cargo', vendors = '', duration = '') {
        var i = 1;
        var table = $('#example').DataTable({
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
                targets: [1, 4, 5, 6, 7, 8],
                className: "hide_column"
            }],
            "scrollX": true,
            "order": [
                [1, "desc"]
            ],
            "ajax": {
                url: "<?php echo e(url('reports/listaccountpayablereport')); ?>", // json datasource
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.modules = modules;
                    d.vendors = vendors;
                    d.duration = duration;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var vendorId = data[1];
                $('td', row).eq(2).addClass('expandpackage fa fa-plus');
                $('td', row).eq(2).attr('style', 'display: block;text-align: center;padding-top: 13px;');
                $('td', row).eq(2).attr('data-vendorid', vendorId);
                $('td', row).eq(2).attr('data-rowid', i);
                $('td', row).eq(0).attr('style', 'text-align: center;');
                i++;
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

        // For A/P Disbursement
        $('#example .singlecheckboxforapdisbursement').prop('checked', this.checked);
        var checkedForAp = [];
        $('input[name="singlecheckboxforapdisbursement"]').each(function() {
            if ($(this).prop('checked') == true) {
                checkedForAp.push($(this).attr('id'))
            }
        });
        $('.idsforapdisbursement').val(checkedForAp);
        //-- For A/P Disbursement
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

        // For A/P Disbursement
        var vendorId = $(this).val();
        $('#example .singlecheckboxforapdisbursement-' + vendorId).prop('checked', this.checked);
        var checkedForAp = [];
        $('input[name="singlecheckboxforapdisbursement"]').each(function() {
            if ($(this).prop('checked') == true) {
                checkedForAp.push($(this).attr('id'))
            }
        });
        $('.idsforapdisbursement').val(checkedForAp);

        $('.costAmountVendorD-' + vendorId).each(function() {
            var arraycostAmountD = $(this).text().split(" ");
            var costAmountD = arraycostAmountD[1];
            sumOfAllExpense = sumOfAllExpense + parseInt(costAmountD);
            $('.totalDisbursement').text(sumOfAllExpense).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        });
        if ($(this).prop('checked') !== true) {
            sumOfAllExpense = 0.00;
            $('.totalDisbursement').text(sumOfAllExpense).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        }
        //-- For A/P Disbursement
    });

    // For A/P Disbursement
    var sumOfAllExpense = 0.00;
    $(document).on('click', '.singlecheckboxforapdisbursement', function() {
        var checked = [];

        if ($(this).prop('checked') == true) {
            var id = $(this).val();
            $('.costAmountExpenseD-' + id).each(function() {
                var arraycostAmountD = $(this).text().split(" ");
                var costAmountD = arraycostAmountD[1];
                sumOfAllExpense = sumOfAllExpense + parseInt(costAmountD);
            });
            $('.totalDisbursement').text(sumOfAllExpense).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        } else {
            var id = $(this).val();
            $('.costAmountExpenseD-' + id).each(function() {
                var arraycostAmountD = $(this).text().split(" ");
                var costAmountD = arraycostAmountD[1];
                sumOfAllExpense = sumOfAllExpense - parseInt(costAmountD);
            });
            $('.totalDisbursement').text(sumOfAllExpense).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        }

        $('input[name="singlecheckboxforapdisbursement"]').each(function() {
            if ($(this).prop('checked') == true) {
                checked.push($(this).attr('id'))
            }
        });
        $('.idsforapdisbursement').val(checked);

        /* $('input[name="singlecheckboxforapdisbursement"]').each(function() {
            if ($(this).prop('checked') == true) {
                var id = $(this).val();
                alert(id);
                $('.costAmountExpenseD-' + id).each(function() {
                    var arraycostAmountD = $(this).text().split(" ");
                    var costAmountD = arraycostAmountD[1];
                    sumOfAllExpense = sumOfAllExpense + parseInt(costAmountD);
                    
                });
                
                $('.totalDisbursement').text(sumOfAllExpense).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                checked.push($(this).attr('id'))
            } else {
                
                var id = $(this).val();
                $('.costAmountExpenseD-' + id).each(function() {
                    var arraycostAmountD = $(this).text().split(" ");
                    var costAmountD = arraycostAmountD[1];
                    sumOfAllExpense = sumOfAllExpense - parseInt(costAmountD);
                });
                $('.totalDisbursement').text(sumOfAllExpense).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
            }
            $('.idsforapdisbursement').val(checked);
        }); */
        
    });
    //-- For A/P Disbursement

    // For A/P Disbursement
    $('.btnApDisbursement').click(function() {
        if ($('.singlecheckboxforapdisbursement:checked').length < 1) {
            alert("Please select Expense.");
            return false;
        } else {
            $('#modalApDisbursement').modal('show').find('#modalContentApDisbursement').load($(this).attr('value'));
        }
    })
    //-- For A/P Disbursement
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>