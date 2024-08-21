<style type="text/css">
    .ui-front {
        z-index: 9999;
    }
</style>
<?php
$actionUrl = url('ccpack/store');
?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'importFileForm','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="ccpack_operation_type" value=1>
<h4 class="formdeviderh4">Shipment information</h4>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('awb_number') ? 'has-error' :'' }}">
            <div class="col-md-5">
                <?php echo Form::label('awb_number', 'AWB Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('awb_number', $model->awb_number, ['class' => 'form-control ccpack_awb_number', 'placeholder' => 'Enter AWB Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('no_of_pcs') ? 'has-error' :'' }}">
            <?php echo Form::label('no_of_pcs', 'No of Pices', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('no_of_pcs', $model->no_of_pcs, ['class' => 'form-control ccpack_pices', 'placeholder' => 0.00]); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
            <?php echo Form::label('weight', 'Weight', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('weight', $model->weight, ['class' => 'form-control ccpack_weight', 'placeholder' => 'Enter Weight']); ?>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('arrival_date') ? 'has-error' :'' }}">
            <div class="col-md-5">
                <?php echo Form::label('arrival_date', 'Arrival Date', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('arrival_date', '', ['class' => 'form-control datepicker', 'placeholder' => '']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('freight') ? 'has-error' :'' }}">
            <?php echo Form::label('freight', 'Freight', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('freight', $model->freight, ['class' => 'form-control ccpack_freight', 'placeholder' => 'Enter Freight']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('expences') ? 'has-error' :'' }}">
            <?php echo Form::label('expences', 'Other Expences', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('expences', $model->expences, ['class' => 'form-control ccpack_expences', 'placeholder' => 'Enter Other Expence']); ?>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('commission') ? 'has-error' :'' }}">
            <?php echo Form::label('commission', 'Commission', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('commission', $model->commission, ['class' => 'form-control ccpack_commission', 'placeholder' => 'Enter Commission']); ?>
            </div>
        </div>
    </div>
</div>
<h4 class="formdeviderh4">Shipper information</h4>
<div class="row" style="margin-left: 1%">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
            <div class="col-md-5 required">
                <?php echo Form::label('shipper_name', 'Shipper Name', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('shipper_name', $model->shipper_name, ['class' => 'form-control ccpack_shipper_name', 'placeholder' => 'Enter Shipper Name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('shipper_telephone') ? 'has-error' :'' }}">
            <?php echo Form::label('shipper_telephone', 'Phone Number', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('shipper_telephone', $model->shipper_telephone, ['class' => 'form-control ccpack_shipper_telephone', 'placeholder' => 'Enter Phone Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">

    </div>
</div>

<div class="row" style="margin-left: 1%">
    <div class="col-md-12">
        <div class="form-group {{ $errors->has('shipper_address') ? 'has-error' :'' }}">
            <div class="col-md-2">
                <?php echo Form::label('shipper_address', 'Address', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6" style="margin-left:40px">
                <?php echo Form::textarea('shipper_address', $model->shipper_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
            </div>
        </div>
    </div>
</div>

<h4 class="formdeviderh4">Receiver information</h4>
<div class="row" style="margin-left: 1%">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
            <div class="col-md-5 required">
                <?php echo Form::label('consignee_name', 'Receiver Name', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('consignee_name', $model->consignee_name, ['class' => 'form-control ccpack_consignee_name', 'placeholder' => 'Enter Receiver Name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('consignee_telephone') ? 'has-error' :'' }}" style="margin-left: 2%">
            <div class="col-md-6">
                <?php echo Form::label('consignee_telephone', 'Phone Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('consignee_telephone', $model->consignee_telephone, ['class' => 'form-control ccpack_consignee_telephone', 'placeholder' => 'Enter Phone Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">

    </div>
</div>

<div class="row" style="margin-left: 1%">
    <div class="col-md-12">
        <div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
            <div class="col-md-2">
                <?php echo Form::label('consignee_address', 'Address', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6" style="margin-left: 40px;">
                <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control ccpack_consignee_address', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group row btm-sub" style="margin-left: 1%">
    <input type="hidden" name="flagBtn" class="flagBtn" id="flagBtn" value="addCcpackHousefile">
    <button type="submit" id="w2btn" class="btn btn-success">
        <?php
        if (!$model->id)
            echo "Submit";
        else
            echo "Update";
        ?>
    </button>
    <a class="btn btn-danger" href="{{url('ccpack')}}" title="">Cancel</a>
</div>
{{ Form::close() }}

<?php
$datas = App\Clients::getClientsAutocomplete();
?>

<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        $('#importFileForm').on('submit', function(event) {
            $('.ccpack_awb_number').each(function() {
                $(this).rules("add", {
                    required: false,
                })
            });
            $('.ccpack_shipper_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.ccpack_consignee_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.ccpack_consignee_telephone,.ccpack_shipper_telephone,.ccpack_freight,.ccpack_weight,.ccpack_pices,.ccpack_commission,.ccpack_expences').each(function() {
                $(this).rules("add", {
                    number: true
                })
            });
        });
        $('#importFileForm').validate({
            rules: {
                "awb_number": {
                    required: true,
                    checkAwbNumber: true
                }
            },
            submitHandler: function(form) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                //e.preventDefault();
                $('#loading').show();
                var createExpenseForm = $("#importFileForm");
                var formData = createExpenseForm.serialize();
                $.ajax({
                    url: '<?php echo route("storeccpack") ?>',
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        if (data.success) {
                            $('#loading').hide();
                            Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'CCPack house shipment has been added successfully.'
                            });
                            $("#modalAddNewItemsImport").modal("hide");
                            $("html, body").animate({
                                scrollTop: 0
                            }, "slow");
                            $("#importFileForm")[0].reset();

                            var allfile = JSON.parse(data.dataImportHawbAll);
                            $('#hawb_hbl_no').inputpicker({

                                data: allfile,

                                fields: [{
                                        name: 'hawb_hbl_no',
                                        text: 'House AWB No.'
                                    },
                                    {
                                        name: 'consignee',
                                        text: 'Consignee'
                                    },
                                    {
                                        name: 'shipper',
                                        text: 'Shipper'
                                    },
                                ],
                                autoOpen: true,
                                headShow: true,
                                fieldText: 'hawb_hbl_no',
                                fieldValue: 'value',
                                filterOpen: true,
                                multiple: true,
                            });
                        }
                    },
                });
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
                var urlz = '<?php echo url("ccpack/checkuniqueawbnumber"); ?>';
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


        $(".ccpack_consignee_name").autocomplete({
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
                        $('.ccpack_consignee_address').val(data.company_address);
                        $('#consignee_telephone').val(data.phone_number);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                $(".ccpack_consignee_name").val(ui.item.label);
                $('#loading').hide();
            },
            change: function(event, ui) {
                if (ui.item == null || typeof(ui.item) == "undefined") {}
            },
            source: <?php echo $datas; ?>,
            minLength: 1,
        });

        $(".ccpack_shipper_name").autocomplete({
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
                $(".ccpack_shipper_name").val(ui.item.label);
                $('#loading').hide();
            },
            change: function(event, ui) {
                if (ui.item == null || typeof(ui.item) == "undefined") {}
            },
            source: <?php echo $datas; ?>,
            minLength: 1,
        });

        /* $('#importFileForm').on('click', '#w2btn', function(e) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            e.preventDefault();
            $('#loading').show();
            var createExpenseForm = $("#importFileForm");
            var formData = createExpenseForm.serialize();
            $.ajax({
                url: '<?php echo route("storeccpack") ?>',
                type: 'POST',
                data: formData,
                success: function(data) {
                    if (data.success) {
                        $('#loading').hide();
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Import shipment has been added successfully.'
                        });
                        $("#modalAddNewItemsImport").modal("hide");
                        $("html, body").animate({
                            scrollTop: 0
                        }, "slow");
                        $("#importFileForm")[0].reset();

                        var allfile = JSON.parse(data.dataImportHawbAll);
                        $('#hawb_hbl_no').inputpicker({

                            data: allfile,

                            fields: [{
                                    name: 'hawb_hbl_no',
                                    text: 'House AWB No.'
                                },
                                {
                                    name: 'consignee',
                                    text: 'Consignee'
                                },
                                {
                                    name: 'shipper',
                                    text: 'Shipper'
                                },
                            ],
                            autoOpen: true,
                            headShow: true,
                            fieldText: 'hawb_hbl_no',
                            fieldValue: 'value',
                            filterOpen: true,
                            multiple: true,
                        });
                    }
                },
            });
        }); */


    });
</script>