<?php $__env->startSection('title'); ?>
Cargo Invoices
<?php $__env->stopSection(); ?>

<?php
$permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'], '', auth()->user()->id);
$permissionCargoInvoicesDelete = App\User::checkPermission(['delete_cargo_invoices'], '', auth()->user()->id);
$permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'], '', auth()->user()->id);
$permissionCargoInvoicesCopy = App\User::checkPermission(['copy_cargo_invoices'], '', auth()->user()->id);
?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.cargo-invoice', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php

use App\Currency;
?>
<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Cargo Invoices</h1>
</section>

<section class="content">
    <?php if(Session::has('flash_message')): ?>
    <div class="alert alert-success flash-success">
        <?php echo e(Session::get('flash_message')); ?>

    </div>
    <?php endif; ?>
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success invoicecontainer">

        <div class="box-body">
            <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off'))); ?>

            <?php echo e(csrf_field()); ?>

            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="col-md-2">
                    <div class="from-date-filter-div filterout">
                        <input type="text" id="fromDate" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker saveStateThis from-date-filter">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="to-date-filter-div filterout">
                        <input type="text" id="toDate" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker saveStateThis to-date-filter">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
            <?php echo e(Form::close()); ?>

            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="trCancelledFileDiv1"></div>
                    <div class="trCancelledFileDiv2">Cancelled</div>
                </div>
            </div>
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th>Date</th>
                        <th>Invoice No.</th>
                        <th>File No.</th>
                        <th>AWB / BL No.</th>
                        <th>Billing Party</th>
                        <th>Consingee</th>
                        <th>Currency</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Created By</th>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate);
        }else{
            DatatableInitiate();
        }
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $('.customButtonInGridinvoicestatus').click(function() {
            var status = $(this).val();
            var invoiceId = $(this).data('invoiceid');
            var cargoId = $(this).data('cargoid');
            var thiz = $(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            Lobibox.confirm({
                msg: "Are you sure to change status of payment?",
                callback: function(lobibox, type) {
                    if (type == 'yes') {
                        var urlz = '<?php echo route("changeinvoicestatus"); ?>';
                        $.ajax({
                            type: 'post',
                            url: urlz,
                            async: false,
                            data: {
                                'status': status,
                                'invoiceId': invoiceId
                            },
                            success: function(response) {
                                thiz.val(status);
                            }
                        });
                        if (status == 'Paid') {
                            thiz.val('Pending');
                            thiz.text('Pending');
                            thiz.removeClass('customButtonSuccess');
                            thiz.addClass('customButtonAlert');
                        } else {
                            thiz.val('Paid');
                            thiz.text('Paid');
                            thiz.removeClass('customButtonAlert');
                            thiz.addClass('customButtonSuccess');
                        }
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Payment Status has been updated successfully.'
                        });
                    } else {}
                }
            })
        })


        $(document).delegate(".sendmailonlocalfile", "click", function() {
            $('#loading').fadeIn();
            var itemId = $(this).data("value");
            console.log(itemId);
            $.ajax({
                type: "GET",
                url: "<?php echo e(url('invoicesmail/send')); ?>",
                data: {
                    itemId: itemId
                },
                success: function(res) {
                    $('#loading').hide();
                    Lobibox.notify('info', {
                        size: 'mini',
                        delay: 3000,
                        rounded: true,
                        delayIndicator: false,
                        msg: 'Invoice email has been sent successfully.'
                    });
                }
            });
        });

        $('[data-toggle="popover"]').popover(function() {

        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                DatatableInitiate(fromDate, toDate);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '') {
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
                    "targets": [-1],
                    "orderable": false
                },
                {
                    targets: [0],
                    className: "hide_column"
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "<?php echo e(url('invoices/listbydatatableserverside')); ?>", // json datasource
                "data": {
                    "fromDate": fromDate,
                    "toDate": toDate
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var style = '';
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 500);
                var invoiceId = data[0];
                var thiz = $(this);
                //console.log(thiz);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                //var url = '<?php echo route("checkoperationfordatatableserverside"); ?>';
                var url = '<?php echo url("invoices/checkoperationfordatatableserverside"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'invoiceId': invoiceId,
                        'flag': 'getInvoiceData'
                    },
                    success: function(dataR) {
                        if (dataR.deleted == '0') {
                            if (dataR.type_flag == 'Local') {
                                var editLink = '<?php echo url("invoices/viewcargoinvoicedetails"); ?>';
                                editLink += '/' + invoiceId;
                            } else {
                                var editLink = '<?php echo url("invoices/viewcargoinvoicedetails"); ?>';
                                editLink += '/' + invoiceId;
                            }
                            $(row).attr('data-editlink', editLink);
                            $(row).addClass('edit-row');
                            $(row).attr('id', invoiceId);
                        } else {
                            $(row).addClass('trCancelledFile');
                        }


                        if (dataR.payment_status == 'Paid')
                            var style = 'color:green';
                        else
                            var style = 'color:red';

                        $('td', row).eq(8).addClass('alignright');
                        $('td', row).eq(9).addClass('alignright');
                        $('td', row).eq(11).attr('style', style);
                    },
                });
            }
        });
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/invoices/index.blade.php ENDPATH**/ ?>