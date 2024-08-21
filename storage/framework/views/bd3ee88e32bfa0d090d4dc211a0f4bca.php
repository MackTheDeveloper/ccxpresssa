<?php $__env->startSection('title'); ?>
Pending Invoices
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Pending Invoices</h1>
</section>

<section class="content">
  
    
    <div class="box box-success">
        <div class="box-body">
            <?php echo e(Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'pendingInvoiceForm','autocomplete'=>'off'))); ?>

            <?php echo e(csrf_field()); ?>

            <div class="row" style="margin-bottom:20px">
                <div class="filterout col-md-12">
                    <div class="col-md-1 row">
                        <label style="margin-top:7px">Filter By</label>
                    </div>
                    
                    <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    
                    <div class="col-md-2">
                        
                        <button id="clsExportToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i></span> Export</button>
                    </div>
                </div>
            </div>
            <?php echo e(Form::close()); ?>


        
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
            autoclose: true,
            onClose: function(data)
            {
                console.log('onchange');
            }
        });
       
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#pendingInvoiceForm').validate({
             rules: {
                "from_date_filter": {
                    required: true
                },
                "to_date_filter": {
                    required: true
                }
            },
            submitHandler: function(form) {
                $('#loading').show();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                
                
                    var urlztnn = '<?php echo route("exportPendingInvoices"); ?>';
                    urlztnn += '/' + fromDate + '/' + toDate;
                    $.ajax({
                        url: urlztnn,
                        async: true,
                        type: 'POST',
                        data: {
                            'fromDate': fromDate,
                            'toDate': toDate,
                        },
                        success: function(dataRes) {
                            window.open(urlztnn, '_blank');
                            $('#loading').hide();
                        }
                    });
                
            },
        });
    })

    
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/pending-invoices.blade.php ENDPATH**/ ?>