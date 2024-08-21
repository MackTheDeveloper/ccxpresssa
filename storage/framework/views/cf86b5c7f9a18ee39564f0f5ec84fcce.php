<?php $__env->startSection('title'); ?>
<?php echo 'Add UPS File'; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.ups-import', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1><?php echo 'Add UPS File'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            $actionUrl = url('ups/store');
            ?>
            <div class="tab-v1">
                <ul class="nav nav-tabs" style="margin-bottom: 15px;">
                    <li class="active"><a href="#importform" data-toggle="tab">Import</a></li>
                    <li><a href="#exportform" data-toggle="tab">Export</a></li>

                </ul>
                <div class="tab-content">
                    <div id="importform" class="tab-pane fade in active">

                        <?php echo View::make('ups.importFile', array('actionUrl' => $actionUrl, 'model' => $model, 'agents' => $agents, 'warehouses' => $warehouses))->render();
                        ?>
                    </div>
                    <div id="exportform" class="tab-pane fade in">
                        <?php $model->origin = 'HT'; ?>
                        <?php echo View::make('ups.exportFile', array('actionUrl' => $actionUrl, 'model' => $model, 'agents' => $agents, 'warehouses' => $warehouses))->render();
                        ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/ups/_form.blade.php ENDPATH**/ ?>