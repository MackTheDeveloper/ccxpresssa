<?php $__env->startSection('title'); ?>
UPS Files Listing
<?php $__env->stopSection(); ?>

<?php
$permissionCourierImportEdit = App\User::checkPermission(['update_courier_import'], '', auth()->user()->id);
$permissionCourierImportDelete = App\User::checkPermission(['delete_courier_import'], '', auth()->user()->id);
$permissionCourierAddExpense = App\User::checkPermission(['add_courier_expenses'], '', auth()->user()->id);
$permissionCourierAddInvoice = App\User::checkPermission(['add_courier_invoices'], '', auth()->user()->id);
?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.ups-import', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>UPS Files Listing</h1>
</section>

<section class="content">


    <?php if(Session::has('flash_message_import')): ?>
    <div class="alert alert-success-custom flash-success">
        <span><?php echo Session::get('flash_message_import')['totalUploaded']; ?></span><br />
        <span><?php echo Session::get('flash_message_import')['totalAdded']; ?></span><br />
        <span><?php echo Session::get('flash_message_import')['totalUpdated']; ?></span><br /><br />
        <span><a href="<?php echo e(route('viewlogfiles')); ?>">View Log Files</a></span>
    </div>
    <?php endif; ?>
    <?php if(Session::has('flash_message')): ?>
    <div class="alert alert-success flash-success">
        <?php echo e(Session::get('flash_message')); ?>

    </div>
    <?php endif; ?>
    <?php if(Session::has('flash_message_error')): ?>
    <div class="alert alert-danger flash-danger">
        <?php echo e(Session::get('flash_message_error')); ?>

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
                    <?php echo Form::select('file_name', [0 => 'All Files (I, E)', 1 => 'Import', 2 => 'Export'], 0, ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'upslisting']); ?>
                </div>
                <div class="col-md-2">
                    <?php echo Form::select('billing_term', ['P/P' => 'P/P', 'F/C' => 'F/C', 'F/D' => 'F/D'], '', ['class' => 'form-control saveStateThis selectpicker', 'placeholder' => 'All (Billing Term)', 'data-live-search' => 'true', 'id' => 'billing_term']); ?>
                </div>
                <div class="col-md-2">
                    <?php echo Form::select('scan', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control saveStateThis selectpicker', 'data-live-search' => 'true', 'id' => 'upsscan', 'placeholder' => 'All']); ?>
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

            <div class="container-rep courier_container">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
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
                    </thead>



                </table>

            </div>
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
        /* var fromDate = $('.from-date-filter').val();
        var toDate = $('.to-date-filter').val();
        DatatableInitiate(fromDate, toDate); */
        // DatatableInitiate();
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.upslisting, DataTableState.upsscan, DataTableState.billing_term);
        }else{
            DatatableInitiate();
        }
        //$('.date_range').daterangepicker();

        //$('.date_range').change(function(){
        //$('.date_range').on('apply.daterangepicker', function(ev, picker) {     
        /* $('.date_range').on('apply.daterangepicker', function(ev, picker) {
            $('#loading').show();
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: 'ups/filderbydaterange',
                type: 'POST',
                data: {
                    startDate: startDate,
                    endDate: endDate
                },
                success: function(data) {
                    $('.container-rep').html(data);
                    $('#loading').hide();
                },
            });
        })

        $('.date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#loading').show();
            $('.date_range').val('');
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: 'ups/fildergetalldata',
                type: 'POST',
                data: {
                    startDate: startDate,
                    endDate: endDate
                },
                success: function(data) {
                    $('.container-rep').html(data);
                    $('#loading').hide();
                },
            });
        }); */




        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        // Apply the search


        //$('.expandpackage').click(function(){
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
                $('.child-' + rowId).remove();
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            }
        })

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fileType = $('#upslisting').val();
                var billingTerm = $('#billing_term').val();
                var fileStatus = $('#upsscan').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                DatatableInitiate(fromDate, toDate, fileType, fileStatus, billingTerm);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', fileType = '', fileStatus = '', billingTerm = '') {
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
                "targets": [1, 15],
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
                url: "<?php echo e(url('ups/listbydatatableserverside')); ?>", // json datasource
                data: function(d) {
                    d.fileStatus = fileStatus;
                    d.upsType = fileType;
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
                                        var editLink = '<?php echo url("ups/viewdetails"); ?>';
                                        editLink += '/' + UpsId;
                                        $(row).attr('data-editlink', editLink);
                                        $(row).addClass('edit-row');
                                        $(row).attr('id', UpsId);
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/ups/index.blade.php ENDPATH**/ ?>