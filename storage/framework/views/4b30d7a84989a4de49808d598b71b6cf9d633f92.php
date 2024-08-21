<?php $__env->startSection('title'); ?>
Client Ledger Report
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1>Client Ledger Report</h1>
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
                            <th>Client</th>
                            <th>Initial Credit</th>
                            <th>Available Balance</th>
                            <th>Due to pay</th>
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
        DatatableInitiate();
    })

    function DatatableInitiate() {
        $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                    "targets": [],
                    "orderable": false
                },
                {
                    targets: [0],
                    className: "hide_column"
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "<?php echo e(url('reports/listclientcreditall')); ?>", // json datasource
                "data": {},
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var itemId = data[0];
                var name = data[1];
                var editLink = '<?php echo url("reports/getclientcreditdataonclick"); ?>';
                editLink += '/' + itemId + '/' + name;

                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $(row).attr('id', itemId);
            }
        });
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>