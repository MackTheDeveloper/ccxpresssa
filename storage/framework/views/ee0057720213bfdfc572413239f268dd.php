<?php $__env->startSection('title'); ?>
Free Domicile Report
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<section>
    <div class="row">
        <div class="col-md-3 content-header" style="margin-left: 1%">
            <h1>Free Domicile Report</h1>
        </div>
    </div>
</section>
<section class="content editupscontainer">
    <div class="box box-success">
        <div class="box-body">
            <div class="row">
                <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off'))); ?>

                <?php echo e(csrf_field()); ?>

                <div class="col-md-2">
                    <div class="from-date-filter-div filterout">
                        <input type="text" name="from_date_filter" value="<?php echo date('d-m-Y', strtotime("-6 day")); ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="to-date-filter-div filterout">
                        <input type="text" name="to_date_filter" value="<?php echo date('d-m-Y'); ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                </div>

                <button type="submit" style="float:left;margin-right:5px" class="btn btn-success submitfilter">Submit</button>
                <button style="margin-right:5px" type="submit" id="clsPrint" class="btn btn-success">Print</button>
                <button style="margin-right:5px" id="clsExportToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true" style=""></i></span>Export To Excel</button>

                <button id="clsMail" class="btn btn-primary" style=""><span><i class="fa fa-paper-plane fa-paper-plane" aria-hidden="true" style=""></i></span>Send Report To Ups</button>

                <?php echo e(Form::close()); ?>

            </div>


            <div id="filter_data" style="margin-top: 2%">
                <table id="example" class="display nowrap" style="width:100%;">
                    <thead>
                        <tr>
                            <th>File Number</th>
                            <th>Date</th>
                            <th>AWB Number</th>
                            <th>Billing Term</th>
                            <th>Shipper</th>
                            <th>Consignee</th>
                            <th>Origin</th>
                            <th>Destination</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        var fromDate = $('.from-date-filter').val();
        var toDate = $('.to-date-filter').val();
        DatatableInitiate(fromDate, toDate);

        $(document).delegate(".from-date-filter", "change", function() {
            $('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var fromDate = $('.from-date-filter').val();
            var urlz = '<?php echo route("settodateinfdreport"); ?>';
            $.ajax({
                url: urlz,
                type: 'POST',
                data: {
                    'fromDate': fromDate
                },
                success: function(data) {
                    $('.to-date-filter').val(data);
                    $('#loading').hide();
                }
            });

        })
    })

    $('#createInvoiceForm').on('submit', function(event) {});

    $('#createInvoiceForm').validate({
        submitHandler: function(form) {
            var fromDate = $('.from-date-filter').val();
            if (fromDate == '')
                fromDate = 0;
            var toDate = $('.to-date-filter').val();
            if (toDate == '')
                toDate = 0;

            var submitButtonName = $(this.submitButton).attr("id");
            if (submitButtonName == 'clsPrint' || submitButtonName == 'clsExportToExcel' || submitButtonName == 'clsMail') {
                var urlztnn = '<?php echo url("reports/printandexportfreedomicilereport"); ?>';
                urlztnn += '/' + fromDate + '/' + toDate + '/' + submitButtonName;
                $('#loading').show();
                $.ajax({
                    url: urlztnn,
                    //async: false,
                    type: 'GET',
                    /* data: {
                    	'fromDate': fromDate,
                    	'toDate': toDate,
                    	'file_type': file_type,
                    	'typeimpexp': typeimpexp,
                    	'submitButtonName': submitButtonName
                    }, */
                    success: function(dataRes) {
                        if (submitButtonName == 'clsPrint')
                            window.open(dataRes, '_blank');
                        else {
                            window.open(urlztnn, '_blank');
                        }
                        $('#loading').hide();
                    }
                });
            } else {
                DatatableInitiate(fromDate, toDate);
            }
        },
    });

    function DatatableInitiate(fromDate = '', toDate = '') {
        $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                "targets": [3],
                "orderable": false
            }, ],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "<?php echo e(url('reports/listfreedomicilereport')); ?>", // json datasource
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {}
        });
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/courier/freeDomicileReport.blade.php ENDPATH**/ ?>