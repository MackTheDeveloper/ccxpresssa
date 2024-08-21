<?php echo e(Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off'))); ?>

<?php echo e(csrf_field()); ?>

<input type="hidden" name="courier_operation_type" value="1">
<h4 class="formdeviderh4">Shipment information</h4>
<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('awb_number') ? 'has-error' :''); ?>">
            <div class="col-md-5">
                <?php echo Form::label('awb_number', 'AWB Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('awb_number', $model->awb_number, ['class' => 'form-control fawb_number', 'placeholder' => 'Enter AWB Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('destination') ? 'has-error' :''); ?>">
            <?php echo Form::label('destination', 'Destination', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('destination', 'HT', ['class' => 'form-control', 'placeholder' => 'Enter Destination']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('origin') ? 'has-error' :''); ?>">
            <?php echo Form::label('origin', 'Origin', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('origin', $model->origin, ['class' => 'form-control', 'placeholder' => 'Enter Origin']); ?>
            </div>
        </div>
    </div>
</div>


<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('weight') ? 'has-error' :''); ?>">
            <?php echo Form::label('weight', 'Weight', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('weight', $model->weight, ['class' => 'form-control fweight', 'placeholder' => 'Enter Weight']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('freight') ? 'has-error' :''); ?>">
            <?php echo Form::label('freight', 'Freight', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('freight', $model->freight, ['class' => 'form-control ffrieght', 'placeholder' => 'Enter Freight']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('arrival_date') ? 'has-error' :''); ?>">
            <?php echo Form::label('arrival_date', 'Arrival Date', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('arrival_date', !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '', ['class' => 'form-control datepicker', 'placeholder' => 'Enter Arrival Date']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('billing_term') ? 'has-error' :''); ?>">
            <?php echo Form::label('billing_term', 'Billing Term', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6 billing_term-md-6">
                <?php
                echo Form::radio('billing', '1', true);
                echo Form::label('fc', 'F/C');
                echo Form::radio('billing', '2');
                echo Form::label('fd', 'F/D');
                echo Form::radio('billing', '3');
                echo Form::label('pp', 'P/P');
                ?>
            </div>
        </div>
    </div>
</div>


<h4 class="formdeviderh4">Shipper information</h4>
<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('shipper_name') ? 'has-error' :''); ?>">
            <div class="col-md-5 required">
                <?php echo Form::label('shipper_name', 'Shipper Name', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('shipper_name', $model->shipper_name, ['class' => 'form-control fshipper_name', 'placeholder' => 'Enter Shipper Name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('shipper_telephone') ? 'has-error' :''); ?>">
            <?php echo Form::label('shipper_telephone', 'Phone Number', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('shipper_telephone', $model->shipper_telephone, ['class' => 'form-control fshipper_telephone', 'placeholder' => 'Enter Phone Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('shipper_city') ? 'has-error' :''); ?>">
            <?php echo Form::label('shipper_city', 'City', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('shipper_city', $model->shipper_city, ['class' => 'form-control', 'placeholder' => 'Enter City']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-8">
        <div class="form-group <?php echo e($errors->has('shipper_address') ? 'has-error' :''); ?>">
            <?php echo Form::label('shipper_address', 'Address', ['class' => 'col-md-2 control-label']); ?>
            <div class="col-md-6" style="margin-left: 30px;">
                <?php echo Form::textarea('shipper_address', $model->shipper_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
            </div>
        </div>
    </div>
</div>


<h4 class="formdeviderh4">Receiver information</h4>
<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('consignee_name') ? 'has-error' :''); ?>">
            <?php echo Form::label('consignee_name', 'Receiver Name', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('consignee_name', $model->consignee_name, ['class' => 'form-control fconsignee_name', 'placeholder' => 'Enter Receiver Name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('consignee_telephone') ? 'has-error' :''); ?>">
            <?php echo Form::label('consignee_telephone', 'Phone Number', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('consignee_telephone', $model->consignee_telephone, ['class' => 'form-control fconsignee_telephone', 'placeholder' => 'Enter Phone Number', 'id' => 'consignee_telephone']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-8">
        <div class="form-group <?php echo e($errors->has('consignee_address') ? 'has-error' :''); ?>">
            <?php echo Form::label('consignee_address', 'Address', ['class' => 'col-md-2 control-label']); ?>
            <div class="col-md-6" style="margin-left: 30px;">
                <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
            </div>
        </div>
    </div>
</div>

<h4 class="formdeviderh4" style="display: none;">Product information</h4>
<div class="col-md-12" style="display: none;">
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('company') ? 'has-error' :''); ?>">
            <?php echo Form::label('company', 'Company', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('company', $model->company, ['class' => 'form-control', 'placeholder' => 'Enter Company']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('no_manifeste') ? 'has-error' :''); ?>">
            <?php echo Form::label('no_manifeste', 'No. Manifeste', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('no_manifeste', $model->no_manifeste, ['class' => 'form-control', 'placeholder' => 'Enter No. Manifeste']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('nbr_pcs') ? 'has-error' :''); ?>">
            <?php echo Form::label('nbr_pcs', 'No of Pcs', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('nbr_pcs', $model->nbr_pcs, ['class' => 'form-control fpices', 'placeholder' => 'Enter No of Pcs']); ?>
            </div>
        </div>
    </div>
</div>
<h4 class="formdeviderh4">Product information</h4>
<div class="row" style="margin-left: 1%">

    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('no_manifeste') ? 'has-error' :''); ?>">
            <?php echo Form::label('package_type', 'Product Type', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::select('package_type', Config::get('app.productType'), '', ['class' => 'form-control selectpicker fexport_product_type', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group <?php echo e($errors->has('nbr_pcs') ? 'has-error' :''); ?>">
            <?php echo Form::label('nbr_pcs', 'No of Pcs', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('nbr_pcs', $model->nbr_pcs, ['class' => 'form-control', 'placeholder' => 'Enter No of Pcs']); ?>
            </div>
        </div>
    </div>
</div>



<div class="form-group col-md-12 btm-sub">

    <button type="submit" class="btn btn-success">
        <?php
        if (!$model->id)
            echo "Submit";
        else
            echo "Update";
        ?>
    </button>

    <a class="btn btn-danger" href="<?php echo e(url('ups')); ?>" title="">Cancel</a>
</div>

<?php echo e(Form::close()); ?>

<?php
$datas = App\Clients::getClientsAutocomplete();

?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        $('#createforms').on('submit', function(event) {

            $('.ffile_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

            $('.fshipper_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fconsignee_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            /* $('.fconsignee_telephone').each(function() {
                $(this).rules("add", {
                    number: true
                })
            }); */
            $('.fweight,.ffrieght,.fpices').each(function() {
                $(this).rules("add", {
                    number: true
                })
            });
            $('.fawb_number').each(function() {
                $(this).rules("add", {
                    required: false,
                })
            });

        });
        $('#createforms').validate({
            rules: {
                "awb_number": {
                    required: true,
                    checkAwbNumber: true
                }
            }
        });

        $.validator.addMethod("checkAwbNumber",
            function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("ups/checkuniqueawbnumber"); ?>';
                var flag = '';
                var idz = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        number: value,
                        flag: flag,
                        idz: idz
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Awb Number is already taken! Try another."
        );

        $("#consignee_name").autocomplete({

            select: function(event, ui) {
                event.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var clientId = ui.item.value;
                var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    url: urlztnn,
                    dataType: "json",
                    async: false,
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(data) {
                        $('#consignee_address').val(data.company_address);
                        $('#consignee_telephone').val(data.phone_number);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                $("#consignee_name").val(ui.item.label);
                $('#loading').hide();
            },
            change: function(event, ui) {
                if (ui.item == null || typeof(ui.item) == "undefined") {
                    //console.log("dsfdsf");
                    //$('#loading').show();
                    //$('#consignee_name').val("");
                    //$('#loading').hide();

                }
            },
            source: <?php echo $datas; ?>,
            minLength: 1,
        });
        $("#shipper_name").autocomplete({

            select: function(event, ui) {
                event.preventDefault();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var clientId = ui.item.value;
                var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    url: urlztnn,
                    dataType: "json",
                    async: false,
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(data) {
                        $('#shipper_address').val(data.company_address);
                        $('#shipper_telephone').val(data.phone_number);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                $("#shipper_name").val(ui.item.label);
                $('#loading').hide();
            },
            change: function(event, ui) {
                if (ui.item == null || typeof(ui.item) == "undefined") {
                    //console.log("dsfdsf");
                    //$('#loading').show();
                    //$('#consignee_name').val("");
                    //$('#loading').hide();

                }
            },
            source: <?php echo $datas; ?>,
            minLength: 1,
        });
    });
</script>
<?php $__env->stopSection(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/ups/importFile.blade.php ENDPATH**/ ?>