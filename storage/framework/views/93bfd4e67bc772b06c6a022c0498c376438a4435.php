<?php $__env->startSection('title'); ?>
Cargo Files Listing
<?php $__env->stopSection(); ?>

<?php
$permissionCargoEdit = App\User::checkPermission(['update_cargo'], '', auth()->user()->id);
$permissionCargoDelete = App\User::checkPermission(['delete_cargo'], '', auth()->user()->id);
$permissionCargoExpensesAdd = App\User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
$permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);
?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.cargo-files', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Cargo Files Listing</h1>
</section>


<section class="content">
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
    <div id="flash">
    </div>
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success cargocontainer">
        <div class="box-body">
            <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off'))); ?>

            <?php echo e(csrf_field()); ?>


            <div class="row" style="margin-bottom:20px">
                <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                <div class="col-md-2">
                    <?php echo Form::select('scan', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control selectpicker saveStateThis', 'data-live-search' => 'true', 'id' => 'cargomasterscan', 'placeholder' => 'All']); ?>
                </div>
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" name="from_date_filter" id="fromDate" placeholder=" -- From Date" class="saveStateThis form-control datepicker from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" name="to_date_filter" id="toDate" placeholder=" -- To Date" class="saveStateThis form-control datepicker to-date-filter">
                </div>

                <div class="col-md-2">
                    <select id="cargoFileType" class="form-control saveStateThis">
                        <option selected="" value="">All Files (I, E, L)</option>
                        <option value="1">Import Files</option>
                        <option value="2">Export Files</option>
                        <option value="3">Locale Files</option>
                    </select>
                </div>
                <div class="col-md-2 consolidateDiv">
                    <select id="cargoConsolidateType" class="form-control saveStateThis">
                        <option selected="" value="">All Files (Cons. & Non cons.)</option>
                        <option value="1">Consolidate Files</option>
                        <option value="0">Non consolidate Files</option>
                    </select>
                </div>
                <div class="col-md-2 localRentalDiv" style="display: none">
                    <select id="localRentalType" class="form-control saveStateThis">
                        <option selected="" value="">All</option>
                        <option value="1">Rental</option>
                        <option value="0">Non Rental</option>
                    </select>
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
                <div class="col-md-12" style="margin-top: 5px">
                    <div class="trHouseFileDeliveredDiv1"></div>
                    <div class="trHouseFileDeliveredDiv2">Delivered</div>
                </div>
            </div>

            <table id="example" class="display nowrap" style="width:100%;float: left;">
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
    $(document).on('change','#cargoFileType', function() {
        var type = $(this).val();
        if (type == 3) {
            $('select#localRentalType option[value=""]').prop("selected", true)
            $('.localRentalDiv').show();
            $('.consolidateDiv').hide();
        } else {
            $('select#cargoConsolidateType option[value=""]').prop("selected", true)
            $('.consolidateDiv').show();
            $('.localRentalDiv').hide();
        }
    });
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var DataTableState = JSON.parse( localStorage.getItem('DataTables_'+window.location.pathname));
        if(DataTableState){
            console.log('DataTableState',DataTableState)
            changeValue(DataTableState)
            DatatableInitiate(DataTableState.fromDate, DataTableState.toDate, DataTableState.cargoFileType, DataTableState.cargoConsolidateType, DataTableState.localRentalType, DataTableState.cargomasterscan);
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
                var cargoid = $(this).data('cargoid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("expandhawbnumber"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        cargoid: cargoid,
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

        $(document).delegate(".sendmailonlocalfile", "click", function() {
            $('#loading').fadeIn();
            var cargoId = $(this).data("value");
            $.ajax({
                type: "GET",
                url: "<?php echo e(url('invoices/send')); ?>",
                data: {
                    cargoId: cargoId
                },
                success: function(res) {
                    $('#loading').hide();
                    if (res == 'fail') {
                        Lobibox.notify('alert', {
                            size: 'mini',
                            delay: 3000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Something went wrong.'
                        });
                    } else {
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 3000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Invoice email has been sent successfully.'
                        });
                    }
                }
            });
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fileStatus = $('#cargomasterscan').val();
                var cargoFileType = $('#cargoFileType').val();
                var cargoConsolidateType = $('#cargoConsolidateType').val();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var localRentalType = $('#localRentalType').val();
                DatatableInitiate(fromDate, toDate, cargoFileType, cargoConsolidateType, localRentalType, fileStatus);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', cargoFileType = '', cargoConsolidateType = '', localRentalType = '', fileStatus = '') {
        var i = 1;
        var table = $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            "displayStart": 0,
            // 'stateSave': true,
            // stateSaveParams: function(settings, data) {
            //     console.log('data',data)
            //     console.log('settings',settings)
            //     //delete data.order;
            //     delete data.start;
            // },
            "columnDefs": [{
                    "targets": [1, 10, 11],
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
                url: "<?php echo e(url('cargo/listbydatatableserverside')); ?>", // json datasource
                data: function(d) {
                    d.cargoFileType = cargoFileType;
                    d.cargoConsolidateType = cargoConsolidateType;
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.localRentalType = localRentalType;
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
                //var url = "<?php echo e(url('cargo/checkoperationfordatatableserverside')); ?>", // json datasource
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
                                if (data.cargo_operation_type == 3) {
                                    var editLink = '<?php echo url("viewcargolocalfiledetailforcashier"); ?>';
                                    editLink += '/' + cargoId;
                                } else {
                                    var editLink = '<?php echo url("cargo/viewcargo"); ?>';
                                    editLink += '/' + cargoId + '/' + data.cargo_operation_type;
                                }
                                $(row).attr('data-editlink', editLink);
                                $(row).addClass('edit-row');
                                $(row).attr('id', cargoId);
                            },
                        });
                    },
                });
                i++;
            },
            'stateSave': true,
            'stateSaveParams': function(settings, data) {
                delete data.start;
                $('.saveStateThis').each(function() {
                    data[$(this).attr('id')] = $(this).val();
                });
                localStorage.setItem( 'DataTables_'+window.location.pathname, JSON.stringify(data) );
            },
            // 'stateLoadParams': function(settings, data) {
            //     // changeValue(data)
            // },
        });
    };


    // function changeValue(data){
    //     $.each(data, function( index, value ) {
    //         if(typeof value!='object'){
    //             if($('select#'+index).length){
    //                 $('select#'+index+' option[value="'+value+'"]').attr("selected","selected");
    //             }else if($('input#'+index).length){
    //                 $('input#'+index).val(value);
    //             }
    //         }
    //     });
    // }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>