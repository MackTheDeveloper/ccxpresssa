{{ Form::open(array('url' => 'check-guarantee/update','class'=>'form-horizontal create-form','id'=>'createformscheck','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="id" value="{{$model->id}}" />
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('delivered_date', 'Delivered Date', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('delivered_date', !empty($model->delivered_date) ? date('d-m-Y', strtotime($model->delivered_date)) : null, ['class' => 'form-control fdelivered_date datepicker', 'placeholder' => 'Enter Delivered Date']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('return_date', 'Return Date', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('return_date', !empty($model->return_date) ? date('d-m-Y', strtotime($model->return_date)) : null, ['class' => 'form-control freturn_date datepicker', 'placeholder' => 'Enter Return Date']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('detention_days_allowed', 'Detention Days', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('detention_days_allowed', $model->detention_days_allowed, ['class' => 'form-control fdetention_days_allowed', 'placeholder' => 'Enter Detention Days']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('invoice_number', ($model->check_type == 1 ? 'DECSA' : 'Veconinter').' Invoice Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('invoice_number', $model->invoice_number, ['class' => 'form-control finvoice_number', 'placeholder' => 'Enter Invoice Number']); ?>
            </div>
        </div>
    </div>

</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('total_cost_container', 'Total charges of container', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('total_cost_container', $model->total_cost_container, ['class' => 'form-control ftotal_cost_container', 'placeholder' => 'Enter Container Charge']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('total_cost_chassis', 'Total charges of chassis', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('total_cost_chassis', $model->total_cost_chassis, ['class' => 'form-control ftotal_cost_chassis', 'placeholder' => 'Enter Chassis Charge']); ?>
            </div>
        </div>
    </div>

</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('amount', 'Balance on check', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('amount', $model->amount, ['class' => 'form-control famount', 'placeholder' => 'Enter Balance', 'style' => 'pointer-events: none;background: #efefef;']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('amount_balance', 'Balance on check', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('amount_balance', $model->amount_balance, ['class' => 'form-control famount_balance', 'placeholder' => 'Enter Balance', 'style' => 'pointer-events: none;background: #efefef;']); ?>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('check_return', 'Check  returned', ['class' => 'col-md-6']); ?>
            <div class="consolidate_flag-md-6 col-md-6">
                <?php
                echo Form::radio('check_return', 1, ($model->check_return == 1) ? 'checked' : '', ['class' => 'flagcheck_return', 'id' => 'check_return_yes']);
                echo Form::label('check_return_yes', 'Yes');
                echo Form::radio('check_return', 0, ($model->check_return == 0 || $model->check_return == '') ? 'checked' : '', ['class' => 'flagcheck_return', 'id' => 'check_return_no']);
                echo Form::label('check_return_no', 'No');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12 check_number_cls" style="display: none">
    <div class="col-md-6">
        <div class="form-group">
            <div class="col-md-6">
                <?php echo Form::label('check_number', ($model->check_type == 1 ? 'DECSA' : 'Veconinter').' Check Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('check_number', $model->check_number, ['class' => 'form-control fcheck_number', 'placeholder' => 'Enter '.($model->check_type == 1 ? 'DECSA' : 'Veconinter').' Check Number']); ?>
            </div>
        </div>
    </div>
    <?php if (checkloggedinuserdata() == 'Cashier') { ?>
        <div class="col-md-6">
            <div class="form-group">
                <div class="col-md-6">
                    <?php echo Form::label('cashier_check_number', 'Check Number', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo Form::text('cashier_check_number', $model->cashier_check_number, ['class' => 'form-control fcashier_check_number', 'placeholder' => 'Enter Check Number']); ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<div class="form-group col-md-12 btm-sub">
    <button type="submit" class="btn btn-success">
        <?php
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



        if ($('input[name="check_return"]:checked').val() == 1) {
            $('.check_number_cls').show();
        } else {
            $('.check_number_cls').hide();
        }

        $('.flagcheck_return').change(function() {
            if ($(this).val() == 1)
                $('.check_number_cls').show();
            else
                $('.check_number_cls').hide();
        })

        totalBalanceOnCheck();

        $('#total_cost_chassis,#total_cost_container').focusout(function() {
            totalBalanceOnCheck();
        })

        //$('.selectpicker').selectpicker();
        $('#createformscheck').on('submit', function(event) {


            /* $('.fdelivered_date').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.freturn_date').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            }); */
            $('.fdetention_days_allowed').each(function() {
                $(this).rules("add", {
                    //required: true,
                    number: true
                })
            });
            $('.ftotal_cost_container').each(function() {
                $(this).rules("add", {
                    //required: true,
                    number: true
                })
            });
            $('.ftotal_cost_chassis').each(function() {
                $(this).rules("add", {
                    //required: true,
                    number: true
                })
            });
            /* $('.finvoice_number').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.famount_balance').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            }); */
        });

        $('#createformscheck').validate();
    });

    function totalBalanceOnCheck() {
        $('#loading').show();
        var total_amount = <?php echo $model->amount; ?>;
        var total_cost_chassis = isNaN(parseFloat($('#total_cost_chassis').val())) ? 0.00 : parseFloat($('#total_cost_chassis').val());
        var total_cost_container = isNaN(parseFloat($('#total_cost_container').val())) ? 0.00 : parseFloat($('#total_cost_container').val());
        var d = total_amount - (total_cost_chassis + total_cost_container);
        $('#amount_balance').val(d.toFixed(2));
        $('#loading').hide();

        /* var total_cost_chassis = isNaN(parseFloat($('#total_cost_chassis').val())) ? 0.00 : parseFloat($('#total_cost_chassis').val());
        var total_cost_container = isNaN(parseFloat($('#total_cost_container').val())) ? 0.00 : parseFloat($('#total_cost_container').val());
        var urlz = '<?php //echo url("check-guarantee/getBillingAmount"); 
                    ?>';
        $.ajax({
            url: urlz,
            type: 'POST',
            data: {
                'master_cargo_id': '<?php //echo $model->master_cargo_id 
                                    ?>'
            },
            success: function(data) {
                var d = data - (total_cost_chassis + total_cost_container);
                $('#amount_balance').val(d.toFixed(2));
                $('#loading').hide();
            },
        }); */
    }
</script>