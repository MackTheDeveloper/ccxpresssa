<?php $__env->startSection('title'); ?>
Cashier Report
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
    <?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Cashier Report</h1>
</section>

<section class="content">
    
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
        	<div class="container-rep">
                
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr.no</th>
                            <th>Name</th>
                            <th>Email Id</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                <tbody>
                	<?php $srNo = 1;?>
                	<?php $__currentLoopData = $cashierDetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashierDetail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <tr data-editlink="<?php echo e(route('cashierReportAllDetail',[$cashierDetail->id])); ?>" id="<?php echo e($cashierDetail->id); ?>"; class="edit-row">
                    	<td>
                    		<?php echo e($srNo); ?>

                    	</td>
                    	<td>
                    		<?php echo e($cashierDetail->name); ?>

                    	</td>
                    	<td>
                    		<?php echo e($cashierDetail->email); ?>

                    	</td>
                    	<td>
                    		<?php echo e('Cashier'); ?>

                    	</td>
                    </tr>
                
                    <?php $srNo++;?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            </div>

            
        </div>
    </div>

</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
 var table = $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [ {
            "targets": [],
            "orderable": false
            }],
        "scrollX": true,
         "order": [[ 0, "desc" ]],
         drawCallback: function(){
              $('#example_length', this.api().table().container())          
                 .on('click', function(){
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
                    // $('.expandpackage').each(function(){
                    //     if($(this).hasClass('fa-minus'))
                    //     {
                    //     $(this).removeClass('fa-minus');    
                    //     $(this).addClass('fa-plus');
                    //     }
                    // })
                 });
                 $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
                });
           }
    });

function getDetail(){
	console.log("Hello");
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/cashierReport.blade.php ENDPATH**/ ?>