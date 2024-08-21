<?php $__env->startSection('title'); ?>
<?php echo $model->id ? 'Update Aeropost File' : 'Add Aeropost File'; ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.aeropost', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<section class="content-header" style="margin-bottom: 1.5%">
    <h1 style="float: left"><?php echo $model->id ? 'Update Aeropost File' : 'Add Aeropost File'; ?></h1>
    <?php if ($model->id) { ?>
        <h1 style="float: right;color: green">File Status : <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($model->aeropost_scan_status) ? $model->aeropost_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($model->aeropost_scan_status) ? $model->aeropost_scan_status : '-'] : '-'; ?></h1>
    <?php } ?>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $action = url('aeropost/update', $model->id);
            else
                $action = url('aeropost/store');
            ?>
            <?php echo e(Form::open(array('url' => $action ,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off'))); ?>

            <?php echo e(csrf_field()); ?>


            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('date') ? 'has-error' :''); ?>">
                        <?php echo Form::label('date', 'Date', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('date', !empty($model->date) ? date('d-m-Y', strtotime($model->date)) : null, ['class' => 'form-control datepicker', 'placeholder' => 'Enter Arrival Date']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group <?php echo e($errors->has('tracking_no') ? 'has-error' :''); ?>">
                        <div class="col-md-3">
                            <?php echo Form::label('tracking_no', 'Tracking No.', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-9">
                            <?php echo Form::text('tracking_no', $model->tracking_no, ['class' => 'form-control ftracking_no', 'placeholder' => 'Enter Tracking No.']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ($model->id) { ?>
                <h4 class="formdeviderh4">File Status Information</h4>
                <div class="row" style="margin-left: 1%">
                    <div class="col-md-4">
                        <div class="form-group <?php echo e($errors->has('file_number') ? 'has-error' :''); ?>">
                            <div class="col-md-5">
                                <?php echo Form::label('file_number', 'File Number', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-6">
                                <span class="form-control" style="border: none;font-weight: bold;
                                        box-shadow: none;"><?php echo $model->file_number; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group <?php echo e($errors->has('awb_number') ? 'has-error' :''); ?>">
                            <div class="col-md-5">
                                <?php echo Form::label('aeropost_scan_status', 'File Status', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo Form::select('aeropost_scan_status', Config::get('app.ups_new_scan_status'), $model->aeropost_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'aeropost_scan_status', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" id="warehouse_div" style="display: <?php echo !empty($model->warehouse) ? 'block' : 'none'; ?>">
                        <div class="col-md-5">
                            <?php echo Form::label('warehouse', 'Warehouse', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('warehouse', $warehouses, $model->warehouse, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => 'warehouses']); ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <h4 class="formdeviderh4">Origin information</h4>

            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('from_location') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('from_location', 'From', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('from_location', $model->from_location, ['class' => 'form-control ffrom_location', 'placeholder' => 'Enter From']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('from_phone') ? 'has-error' :''); ?>">
                        <div class="col-md-5">
                            <?php echo Form::label('from_phone', 'Phone Number', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('from_phone', $model->from_phone, ['class' => 'form-control ffrom_phone', 'placeholder' => 'Enter Phone Number']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('from_city') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('from_city', 'City', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('from_city', $model->from_city, ['class' => 'form-control ffrom_city', 'placeholder' => 'Enter City']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-8">
                    <div class="form-group <?php echo e($errors->has('from_address') ? 'has-error' :''); ?>">
                        <div class="col-md-2 required">
                            <?php echo Form::label('from_address', 'Address', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6" style="margin-left: 30px;">
                            <?php echo Form::textarea('from_address', $model->from_address, ['class' => 'form-control ffrom_address', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="formdeviderh4">Destination information</h4>

            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('destination_port') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('destination_port', 'Destination Port', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('destination_port', $model->destination_port, ['class' => 'form-control fdestination_port', 'placeholder' => 'Enter Destination Port']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="formdeviderh4">Consignee information</h4>

            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('consignee_autocomplete') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('consignee_autocomplete', 'Consignee', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('consignee_autocomplete', $model->consignee_autocomplete, ['class' => 'form-control fconsignee_autocomplete', 'placeholder' => 'Enter Consignee']); ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" value="<?php echo $model->consignee; ?>" id="consignee" name="consignee">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('consignee_phone') ? 'has-error' :''); ?>">
                        <div class="col-md-5">
                            <?php echo Form::label('consignee_phone', 'Consignee Phone', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('consignee_phone', $model->consignee_phone, ['class' => 'form-control fconsignee_phone', 'placeholder' => 'Enter Consignee Phone']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-8">
                    <div class="form-group <?php echo e($errors->has('consignee_address') ? 'has-error' :''); ?>">
                        <div class="col-md-2">
                            <?php echo Form::label('consignee_address', 'Address', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6" style="margin-left: 30px;">
                            <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control fconsignee_address', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="formdeviderh4">Other information</h4>

            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('total_pieces') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('total_pieces', 'Total Pieces', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('total_pieces', $model->total_pieces, ['class' => 'form-control ftotal_pieces', 'placeholder' => 'Enter Total Pieces']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('shipment_real_weight') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('shipment_real_weight', 'Gross Weight', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('shipment_real_weight', $model->shipment_real_weight, ['class' => 'form-control freal_weight', 'placeholder' => 'Enter Real Weight']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('total_freight') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('total_freight', 'Freight', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('total_freight', $model->total_freight, ['class' => 'form-control ffreight', 'placeholder' => 'Enter Freight']); ?>
                        </div>
                    </div>
                </div>
            </div>





            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group <?php echo e($errors->has('airline') ? 'has-error' :''); ?>">
                        <div class="col-md-5 required">
                            <?php echo Form::label('airline', 'Airline', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('airline', $model->airline, ['class' => 'form-control fairline', 'placeholder' => 'Enter Airline']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group <?php echo e($errors->has('description') ? 'has-error' :''); ?>">
                        <div class="col-md-2">
                            <?php echo Form::label('description', 'Description', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6" style="margin-left: 30px;">
                            <?php echo Form::textarea('description', $model->description, ['class' => 'form-control fdescription', 'rows' => 4, 'placeholder' => 'Enter Description']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($model->id) { ?>
                <div class="col-md-12">
                    <div class="col-md-4">
                        <div class="form-group <?php echo e($errors->has('billing_party') ? 'has-error' :''); ?>">
                            <div class="col-md-5">
                                <?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-7">
                                <?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                            <div class="col-md-12 balance-div" style="display: none;text-align: right;">
                                <span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="form-group col-md-12 btm-sub">
                <button type="submit" class="btn btn-success">
                    <?php
                    if (!$model->id)
                        echo "Submit";
                    else
                        echo "Update";
                    ?>
                </button>
                <a class="btn btn-danger" href="<?php echo e(url('aeroposts')); ?>" title="">Cancel</a>
            </div>

            <?php echo e(Form::close()); ?>

        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });

    $(document).ready(function() {
        $("#flight_date_time").datetimepicker({
            format: "dd-mm-yyyy HH:ii P",
            showMeridian: true,
            autoclose: true,
            todayBtn: true,
            pickerPosition: "bottom-left"
        });
        $("#consignee_autocomplete").autocomplete({
            select: function(event, ui) {
                event.preventDefault();
                $("#consignee_autocomplete").val(ui.item.label);
                $("#consignee").val(ui.item.value);
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
                        $('#consignee_phone').val(data.phone_number);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                //$("#from_location_autocomplete").val(ui.item.label);
                //$("#from_location").val(ui.item.value);
                $('#loading').hide();
            },
            change: function(event, ui) {
                if (ui.item == null || typeof(ui.item) == "undefined") {
                    //console.log("dsfdsf");
                    //$('#loading').show();
                    $('#consignee').val("");
                    //$('#loading').hide();

                }
            },
            source: <?php echo $clientDatas; ?>,
            minLength: 1,
        });

        $('#createforms').on('submit', function(event) {
            $('.ffrom_location,.ffrom_city,.ffrom_address,.fdestination_port,.ftotal_pieces,.freal_weight,.ffreight,.fairline,.fconsignee_autocomplete').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.ftotal_pieces,.freal_weight').each(function() {
                $(this).rules("add", {
                    number: true,
                })
            });
        });
        $('#createforms').validate({
            rules: {
                "tracking_no": {
                    required: false,
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
                var urlz = '<?php echo url("aeropost/checkuniqueawbnumberofaeropost"); ?>';

                var idz = '';
                var flag = '';

                <?php if ($model->id) { ?>
                    var idz = '<?php echo $model->id; ?>';
                    var flag = 'edit';
                <?php } ?>

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

        $('#aeropost_scan_status').change(function() {
            if ($(this).val() == 4) {
                $('#warehouse_div').show();
            } else {
                $('#warehouse_div').hide();
                $('#warehouses').val('');
            }
        });

        <?php if ($model->id) { ?>
            var clientId = $('#billing_party').val();
            if (clientId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzte = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        if (balance.cash_credit == 'Credit') {
                            $('.balance-div').show();
                            var blnc = parseInt(balance.available_balance).toFixed(2);
                            $('.cash_credit_account_balance').html(blnc);
                        } else {
                            $('.balance-div').hide();
                        }

                    }
                });
            } else {
                $('#loading').hide();
                $('.balance-div').hide();
            }

        <?php } ?>
        $('#billing_party').change(function() {
            $('#loading').show();
            var clientId = $(this).val();
            if (clientId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzte = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        if (balance.cash_credit == 'Credit') {
                            $('.balance-div').show();
                            var blnc = parseInt(balance.available_balance).toFixed(2);
                            $('.cash_credit_account_balance').html(blnc);
                        } else {
                            $('.balance-div').hide();
                        }

                    }
                });
            } else {
                $('#loading').hide();
                $('.balance-div').hide();
            }
        })
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/aeropost/_form.blade.php ENDPATH**/ ?>