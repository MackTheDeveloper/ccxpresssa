<?php $__env->startSection('title'); ?>
Customs Report
<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Customs Report</h1>
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
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                </div>
                <div class="col-md-2 typeimpexpdiv">
                    <?php echo Form::select('paymentstatus', ['' => 'All', 'Paid' => 'Paid', 'Pending' => 'Pending'], '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'paymentstatus']); ?>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
                <div class="col-md-2" style="float:right">
                    <a class="btn round orange btn-warning" href="<?php echo e(route('customexpneses')); ?>">Custom Expense Listing</a>
                </div>
            </div>


            <?php echo e(Form::close()); ?>

            <div class="container-rep">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>File Number</th>
                            <th>Custom File Number</th>
                            <th>Invoice Date</th>
                            <th>Payment Status</th>
                            <th>Duties and Taxes</th>
                            <th>Expense</th>
                            <th>Difference</th>
                            <th>Client</th>
                            <th>AWB Number</th>
                            <th>Action</th>
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
    $('select,input').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        DatatableInitiate();
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                var paymentStatus = $('#paymentstatus').val();

                DatatableInitiate(fromDate, toDate, paymentStatus);
            },
        });
    })

    function DatatableInitiate(fromDate = '', toDate = '', paymentStatus = '') {
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
                url: "<?php echo e(url('reports/listcustomreport')); ?>", // json datasource
                data: function(d) {
                    d.fromDate = fromDate;
                    d.toDate = toDate;
                    d.paymentStatus = paymentStatus;
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
                }, 500);
                if (data[6] < 0)
                    var style = 'color:red';
                else
                    var style = 'color:green';
                $('td', row).eq(4).addClass('alignright');
                $('td', row).eq(5).addClass('alignright');
                $('td', row).eq(6).addClass('alignright');
                $('td', row).eq(6).attr('style', style);
            }
        });
    };
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/customreport.blade.php ENDPATH**/ ?>