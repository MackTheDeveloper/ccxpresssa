<?php $__env->startSection('title'); ?>
Currencies
<?php $__env->stopSection(); ?>

<?php 
    $permissionCurrenciesEdit = App\User::checkPermission(['update_currencies'],'',auth()->user()->id); 
    $permissionCurrenciesDelete = App\User::checkPermission(['delete_currencies'],'',auth()->user()->id); 
?>

<?php $__env->startSection('breadcrumbs'); ?>
    <?php echo $__env->make('menus.currency', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Currencies</h1>
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
            <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr data-editlink="<?php echo e(route('editcurrency',[$items->id])); ?>" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none;"><?php echo e($items->id); ?></td>
                    <td><?php echo e($items->name); ?></td>
                    <td><?php echo e($items->code); ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletecurrency',$items->id);
                        $edit =  route('editcurrency',$items->id);
                        ?>
                        <?php if($permissionCurrenciesEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCurrenciesDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        
                       
                        </div>
                        
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
        </tbody>
        
    </table>
        </div>
    </div>





</section>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable(
    {
        'stateSave': true,
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        },
        
    });

   

} )
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/currency/index.blade.php ENDPATH**/ ?>