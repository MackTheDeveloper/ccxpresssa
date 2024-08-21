<?php $__env->startSection('title'); ?>
Pending Invoices Report
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Pending Invoices Report</h1>
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
                            <th>Billing Party</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr data-editlink="<?php echo e(route('getdueinvoicesofclient',[$user->id])); ?>" id="<?php echo $user->id; ?>" class="edit-row">
                            <td><?php echo e($user->company_name); ?></td>
                            <td>
                                <div class='dropdown'>
                                    <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                    <ul class='dropdown-menu' style='left:auto;'>
                                        <li>
                                            <a target="_blank" href="<?php echo e(route('invoicepaymentcreateall',[$user->id])); ?>">Receive Payment</a>
                                        </li>

                                    </ul>
                                </div>
                            </td>
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

        $('#createInvoiceForm').on('submit', function(event) {
            $('#loading').show();
            $('.fclients').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
        });
        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var cashCreditId = $('#clients').val();
                var clientName = $('#clients option:selected').html();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlztnn = '<?php echo url("reports/getclientcreditdata"); ?>';
                $.ajax({
                    url: urlztnn,
                    async: false,
                    type: 'POST',
                    data: {
                        'cashCreditId': cashCreditId,
                        'clientName': clientName
                    },
                    success: function(data) {
                        $('.container-rep').html(data);
                        $('#loading').hide();
                    }
                });
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "clients") {
                    var pos = $('.fclients button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
                $('#loading').hide();
            }
        });
    })
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/statementofaccounts.blade.php ENDPATH**/ ?>