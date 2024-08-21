{{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createformscheck','autocomplete'=>'off')) }}
{{ csrf_field() }}
<?php if ($model->id) { ?>
    <input type="hidden" value="update" id="actionName" name="actionName">
    <input type="hidden" value="<?php echo $model->id; ?>" id="id" name="id">
<?php } else { ?>
    <input type="hidden" value="store" id="actionName" name="actionName">
<?php } ?>
<input type="hidden" value="<?php echo $moduleFlag; ?>" id="moduleFlag" name="moduleFlag">
<?php if ($moduleFlag == 'masterCargo') { ?>
    <input type="hidden" value="<?php echo $moduleId; ?>" id="master_cargo_id" name="master_cargo_id">
<?php } ?>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6 required">
                <?php echo Form::label('check_number', 'Check Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('check_number', $model->check_number, ['class' => 'form-control fcheck_number', 'placeholder' => 'Enter Check Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6 required">
                <?php echo Form::label('date', 'Date', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('date', !empty($model->date) ? date('d-m-Y', strtotime($model->date)) : null, ['class' => 'form-control fdate datepicker', 'placeholder' => 'Enter Date']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6 required">
                <?php echo Form::label('amount', 'Amount', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('amount', $model->amount, ['class' => 'form-control famount', 'placeholder' => 'Enter Amount']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('paid', 'Paid', ['class' => 'col-md-6']); ?>
            <div class="consolidate_flag-md-6 col-md-6">
                <?php
                echo Form::radio('paid', '1', ($model->paid == '1') ? 'checked' : '', ['class' => 'flagconsol']);
                echo Form::label('', 'Yes');
                echo Form::radio('paid', '0', ($model->paid == '0' || $model->paid == '') ? 'checked' : '', ['class' => 'flagconsol']);
                echo Form::label('', 'No');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6 required">
                <?php echo Form::label('detention_days_allowed', 'Detention Days Allowed', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('detention_days_allowed', $model->detention_days_allowed, ['class' => 'form-control fdetention_days_allowed', 'placeholder' => 'Enter Detention Days Allowed']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6 required">
                <?php echo Form::label('invoice_number', 'Invoice Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('invoice_number', $model->invoice_number, ['class' => 'form-control finvoice_number', 'placeholder' => 'Enter Invoice Number']); ?>
            </div>
        </div>
    </div>
</div>

<?php if ($model->id) { ?>
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="form-group">
                <div class="col-md-6 required">
                    <?php echo Form::label('detention_days_used', 'Detention Days Used', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo Form::text('detention_days_used', $model->detention_days_used, ['class' => 'form-control fdetention_days_used', 'placeholder' => 'Enter Detention Days Used']); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <div class="col-md-6 required">
                    <?php echo Form::label('amount_deducted', 'Amount Deducted', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo Form::text('amount_deducted', $model->amount_deducted, ['class' => 'form-control famount_deducted', 'placeholder' => 'Enter Amount']); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="form-group">
                <div class="col-md-6 required">
                    <?php echo Form::label('amount_balance', 'Balance', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo Form::text('amount_balance', $model->amount_balance, ['class' => 'form-control famount_balance', 'placeholder' => 'Enter Balance']); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="col-md-12">
            <div class="form-group">
                <div class="col-md-3 required">
                    <?php echo Form::label('notes', 'Amount Deducted', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo Form::textarea('notes', $model->notes, ['class' => 'form-control fnotes', 'placeholder' => 'Enter Notes', 'rows' => '4']); ?>
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
</div>
{{ Form::close() }}

<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        //$('.selectpicker').selectpicker();
        $('#createformscheck').on('submit', function(event) {

            $('.fcheck_number').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fdate').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.famount').each(function() {
                $(this).rules("add", {
                    required: true,
                    number: true
                })
            });
            $('.fdetention_days_allowed').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.finvoice_number').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fdetention_days_used').each(function() {
                $(this).rules("add", {
                    required: <?php echo ($model->id) ? 'true' : 'false' ?>,
                })
            });
            $('.famount_deducted').each(function() {
                $(this).rules("add", {
                    required: <?php echo ($model->id) ? 'true' : 'false' ?>,
                })
            });
            $('.famount_balance').each(function() {
                $(this).rules("add", {
                    required: <?php echo ($model->id) ? 'true' : 'false' ?>,
                })
            });
            $('.fnotes').each(function() {
                $(this).rules("add", {
                    required: <?php echo ($model->id) ? 'true' : 'false' ?>,
                })
            });

        });

        $('#createformscheck').validate({
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var createExpenseForm = $("#createformscheck");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("check-guarantee/store"); ?>';
                $.ajax({
                    url: urlz,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $('#loading').hide();
                        <?php if ($moduleFlag == 'masterCargo') { ?>
                            window.location.href = '<?php echo route("viewcargo", [$moduleId, $dataC->cargo_operation_type]) ?>';
                        <?php } ?>
                        event.preventDefault();
                        return false;
                    },
                });
            }
        });
    });
</script>