<?php $__env->startSection('title'); ?>
Cash/Bank Report
<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumbs'); ?>
    <?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Cash/Bank Report</h1>
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
            <div class="container-rep">
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>Cash/Bank Type</th>
                            <th>Name</th>
                            <th>Available Balance</th>
                            <th>as of</th>
                            <th>Currency</th>
                        </tr>
                    </thead>
                <tbody>
                     <?php $__currentLoopData = $cashcredit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr data-editlink="<?php echo e(route('getcashcreditdataonclick',[$user->id,$user->name])); ?>" id="<?php echo $user->id; ?>" class="edit-row">
                                <td style="display: none;"><?php echo e($user->id); ?></td>
                                <td><?php $detailData = App\CashCreditDetailType::getData($user->detail_type); echo $detailData->name; ?></td>
                                <td><?php echo e($user->name); ?></td>
                                <td class="alignright"><?php echo e($user->available_balance); ?></td>
                                <td><?php echo date('d-m-Y',strtotime($user->as_of)) ?></td>
                                <td><?php $currencyData = App\Currency::getData($user->currency); echo $currencyData->code; ?></td>
                            </tr>
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
$(document).ready(function() {
    $('#example').DataTable({
        'stateSave': true,
        "ordering": false
    });

} )
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/cashcreditallreport.blade.php ENDPATH**/ ?>