<?php $__env->startSection('title'); ?>
Aeropost Files
<?php $__env->stopSection(); ?>

<?php
$permissionAeropostEdit = App\User::checkPermission(['update_aeropost'], '', auth()->user()->id);
$permissionAeropostDelete = App\User::checkPermission(['delete_aeropost'], '', auth()->user()->id);
$permissionAeropostAddInvoice = App\User::checkPermission(['add_aeropost_invoices'], '', auth()->user()->id);
?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.aeropost', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Aeropost Files Listing</h1>
</section>

<section class="content">
    <?php if(Session::has('flash_message')): ?>
    <div class="alert alert-success flash-success">
        <?php echo e(Session::get('flash_message')); ?>

    </div>
    <?php endif; ?>
    <?php if(Session::has('flash_message_error')): ?>
    <div class="alert alert-danger flash-danger">
        <?php echo e(Session::get('flash_message')); ?>

    </div>
    <?php endif; ?>
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>

    <div class="box box-success">
        <div class="box-body">

            <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off'))); ?>

            <?php echo e(csrf_field()); ?>

            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="col-md-2">
                    <?php echo Form::select('scan', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control selectpicker saveStateThis', 'data-live-search' => 'true', 'id' => 'aeropostscan', 'placeholder' => 'All']); ?>
                </div>
                <div class="from-date-filter-div filterout col-md-2" style="display: block">
                    <input type="text" name="from_date_filter" id="fromDate" placeholder=" -- From Date" class="form-control datepicker saveStateThis from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2" style="display: block">
                    <input type="text" name="to_date_filter" id="toDate" placeholder=" -- To Date" class="form-control datepicker saveStateThis to-date-filter">
                </div>
                
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
            <?php echo e(Form::close()); ?>


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
                        <th>File Number</th>
                        <th>Master File Number</th>
                        <th>Billing Party</th>
                        <th>File Status</th>
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
                        <th>From</th>
                        <th>Consignee</th>
                        <th>Freight</th>
                        <th>Tracking Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="modalAddCashCreditWarehouseInFile" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title modal-title-block text-center primecolor">Add</h3>
                </div>
                <div class="modal-body" id="modalContentAddCashCreditWarehouseInFile" style="overflow: hidden;">
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
        /* var fromDate = $('.from-date-filter').val();
        var toDate = $('.to-date-filter').val();
        DatatableInitiate(fromDate, toDate); */
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.aeropostscan);
        }else{
            DatatableInitiate();
        }
    })

    $('#createInvoiceForm').validate({
        submitHandler: function(form) {
            var fromDate = $('.from-date-filter').val();
            var toDate = $('.to-date-filter').val();
            var fileStatus = $('#aeropostscan').val();
            DatatableInitiate(fromDate, toDate, fileStatus);
        },
    });

    function DatatableInitiate(fromDate = '', toDate = '', fileStatus = '') {
        $('#example').DataTable({
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
                url: "<?php echo e(url('aeropost/listbydatatableserverside')); ?>", // json datasource
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
                }, 1000);
                var aeropostId = data[0];
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var url = '<?php echo url("aeropost/checkoperationfordatatableserversideaeropost"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'aeropostId': aeropostId,
                        'flag': 'getFileData'
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
                            var editLink = '<?php echo url("aeropost/viewaeropostdetailforagent"); ?>';
                            editLink += '/' + aeropostId;
                        <?php } else { ?>
                            var editLink = '<?php echo url("aeropost/viewdetailsaeropost"); ?>';
                            editLink += '/' + aeropostId;
                        <?php } ?>
                        $(row).attr('data-editlink', editLink);
                        $(row).addClass('edit-row');
                        $(row).attr('id', aeropostId);
                    },
                });
            }

        });
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/aeropost/index.blade.php ENDPATH**/ ?>