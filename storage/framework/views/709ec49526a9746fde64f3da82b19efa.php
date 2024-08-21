<?php $__env->startSection('title'); ?>
Warehouse Report
<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Warehouse Report</h1>
</section>

<section class="content">
    <?php if(Session::has('flash_message')): ?>
    <div class="alert alert-success flash-success">
        <?php echo e(Session::get('flash_message')); ?>

    </div>
    <?php endif; ?>
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <div class="col-md-12">
                <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off'))); ?>

                <?php echo e(csrf_field()); ?>

                <div class="row" style="margin-bottom:20px">
                    <label style="float: left;width: 80px;margin-top: 5px;margin-left: 15px;">Filter By :</label>
                    <div class="filterout col-md-2">
                        <select id="fileType" class="form-control">
                            <option selected="" value="">All</option>
                            <option value="1">Import</option>
                            <option value="2">Export</option>
                        </select>
                    </div>

                    <div class="from-date-filter-div filterout col-md-2" style="display: none">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-2" style="display: none">
                        <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>

                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="submit" id="clsPrint" class="btn btn-success">Print</button>
                </div>
                <?php echo e(Form::close()); ?>

            </div>
            <div style="display:none;float: right;width: 200px;margin: 0px;height: 35px;position: absolute;left: 70%;z-index: 111;top:22px">
                <a title="Click here to print" target="_blank" href="<?php echo e(route('warehousereportpdf')); ?>"><i class="fa fa-print btn btn-primary"></i></a>
            </div>
            <div class="container-rep">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>File No.</th>
                            <th>Consignee/Client</th>
                            <th>Invoice Numbers</th>
                            <th>Agent</th>
                            <th>AWB/BL No.</th>
                            <th>House AWB No.</th>
                            <th>Warehouse</th>
                            <th>Warehouse Status</th>
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
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        DatatableInitiate();

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var fileType = $('#fileType').val();
                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'clsPrint') {
                    $('#loading').show();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    var urlztnn = '<?php echo url("reports/warehousereportpdf"); ?>';
                    $.ajax({
                        url: urlztnn,
                        type: 'POST',
                        data: {
                            'fromDate': fromDate,
                            'toDate': toDate,
                            'fileType': fileType,
                        },
                        success: function(dataRes) {
                            $('#loading').hide();
                            window.open(dataRes, '_blank');
                        }
                    });
                } else {
                    DatatableInitiate(fromDate, toDate, fileType);
                }
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
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                targets: [0],
                className: "hide_column"
            }],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
            "ajax": {
                url: "<?php echo e(url('reports/listwarehousereport')); ?>", // json datasource
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
                var cargoId = data[0];
                var url = '<?php echo url("cargo/checkoperationfordatatableserverside"); ?>';
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'cargoid': cargoId,
                        'flag': 'getCargoData'
                    },
                    success: function(data) {
                        if (data.cargo_operation_type == 3) {
                            var editLink = '<?php echo url("viewcargolocalfiledetailforcashier"); ?>';
                            editLink += '/' + cargoId;
                        } else {
                            var editLink = '<?php echo url("cargo/viewcargo"); ?>';
                            editLink += '/' + cargoId + '/' + data.cargo_operation_type;
                        }
                        /* if (data.file_close == 1) {
                            $(row).addClass('trClosedFile');
                        } */
                        $(row).attr('data-editlink', editLink);
                        $(row).attr('id', cargoId);
                    },
                });
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 2000);
                var thiz = $(this);
                $(row).addClass('edit-row');
                i++;
            }
        });
    };
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/warehousereport.blade.php ENDPATH**/ ?>