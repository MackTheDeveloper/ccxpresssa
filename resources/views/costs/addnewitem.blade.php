<?php
if ($model->id)
    $actionUrl = url('costs/update', $model->id);
else
    $actionUrl = url('costs/store');
?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
{{ csrf_field() }}
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('code') ? 'has-error' :'' }}">
            <div class="col-md-4 required">
                <?php echo Form::label('code', 'Cost Code', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('code', $model->code, ['class' => 'form-control fcode', 'placeholder' => 'Enter Code']); ?>
                @if ($errors->has('code'))
                <span class="help-block">
                    <strong>{{ $errors->first('code') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('cost_name') ? 'has-error' :'' }}">
            <div class="col-md-4 required">
                <?php echo Form::label('cost_name', 'Cost Name', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('cost_name', $model->cost_name, ['class' => 'form-control fcost_name', 'placeholder' => 'Enter Cost Name']); ?>
                @if ($errors->has('cost_name'))
                <span class="help-block">
                    <strong>{{ $errors->first('cost_name') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('code') ? 'has-error' :'' }}">
            <?php echo Form::label('cost_billing_code', 'Billing Items Name', ['class' => 'col-md-4 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::select('cost_billing_code', $dataBillingItems, $model->cost_billing_code, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'data-container' => '#modalAddNewItems']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
            <?php echo Form::label('status', 'Status', ['class' => 'col-md-4']); ?>
            <div class="consolidate_flag-md-6 col-md-6">
                <?php
                echo Form::radio('status', '1', ($model->status == '1' || $model->status == '') ? 'checked' : '', ['class' => 'flagconsol']);
                echo Form::label('', 'Active');
                echo Form::radio('status', '0', $model->status == '0' ? 'checked' : '', ['class' => 'flagconsol']);
                echo Form::label('', 'Inactive');
                ?>
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


</div>

{{ Form::close() }}

<script type="text/javascript">
    $(document).ready(function() {
        $('.selectpicker').selectpicker();
        $('#createforms').on('submit', function(event) {

            $('.fcode').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

        });

        $('#createforms').validate({
            rules: {
                'cost_name': {
                    required: true,
                    checkUniqueCost: true
                },
                "code": {
                    required: true,
                    checkUniqueCostCode: true
                }
            },
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var createExpenseForm = $("#createforms");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("costs/storenewitem"); ?>';
                $.ajax({
                    url: urlz,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $('#loading').hide();
                        console.log(data);
                        //$('.selectpicker').selectpicker('refresh');
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Item has been added successfully.'
                        });

                        var urlzte = '<?php echo url("costs/getcostdropdowndataaftersubmit"); ?>';
                        $.ajax({
                            async: false,
                            url: urlzte,
                            type: 'POST',
                            data: '',
                            success: function(response) {
                                $('#loading').hide();
                                selectedVal = [];
                                $('select.fexpense_type').each(function(k, v) {
                                    idd = $(this).attr('id');
                                    result = idd.split('-');
                                    selectedVal[result[1]] = $('#expense_type-' + result[1] + ' option:selected').val();
                                })

                                $('select.fexpense_type').html(response);
                                $('.selectpicker').selectpicker('refresh');

                                $('select.fexpense_type').each(function(k, v) {
                                    idd = $(this).attr('id');
                                    result = idd.split('-');
                                    $('#expense_type-' + result[1]).val(selectedVal[result[1]])
                                })
                                $('.selectpicker').selectpicker('refresh');
                            }
                        });

                        $('#modalAddNewItems').modal('toggle');
                    },
                });
            }
        });


        $.validator.addMethod('checkUniqueCost', function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("costs/checkunique"); ?>';
                var flag = 'costName';
                <?php if ($model->id) { ?>
                    var id = '<?php echo $model->id; ?>';
                <?php } else { ?>
                    var id = '';
                <?php } ?>

                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        value: value,
                        id: id,
                        flag: flag
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Name is already taken! Try another."
        );

        $.validator.addMethod('checkUniqueCostCode', function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("costs/checkunique"); ?>';
                var flag = 'costCode';
                <?php if ($model->id) { ?>
                    var id = '<?php echo $model->id; ?>';
                <?php } else { ?>
                    var id = '';
                <?php } ?>

                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        value: value,
                        id: id,
                        flag: flag
                    },
                    success: function(data) {
                        console.log(data);
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Code is already taken! Try another."
        );
    });
</script>