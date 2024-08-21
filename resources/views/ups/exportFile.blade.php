{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal','id'=>'exportFileForm','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="courier_operation_type" value="2">



<h4 class="formdeviderh4">Shipment information</h4>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('awb_number') ? 'has-error' :'' }}">
            <div class="col-md-5">
                <?php echo Form::label('awb_number', 'AWB Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('awb_number', $model->awb_number, ['class' => 'form-control fexport_awb_number', 'placeholder' => 'Enter AWB Number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('destination') ? 'has-error' :'' }}">
            <?php echo Form::label('destination', 'Destination', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('destination', $model->destination, ['class' => 'form-control', 'placeholder' => 'Enter Destination']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('origin') ? 'has-error' :'' }}">
            <?php echo Form::label('origin', 'Origin', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('origin', $model->origin, ['class' => 'form-control fexport_origin', 'placeholder' => 'Enter Origin']); ?>
            </div>
        </div>
    </div>
</div>


<div class="row" style="margin-left: 1%">

    <div class="col-md-4">
        <div class="form-group {{ $errors->has('freight') ? 'has-error' :'' }}">
            <?php echo Form::label('freight', 'Freight', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('freight', $model->freight, ['class' => 'form-control fexport_freight', 'placeholder' => 'Enter Freight']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('billing_term') ? 'has-error' :'' }}">
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
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
            <?php echo Form::label('weight', 'Weight', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('weight', $model->weight, ['class' => 'form-control fexport_weight', 'placeholder' => 'Enter Weight']); ?>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('arrival_date') ? 'has-error' :'' }}">
            <?php echo Form::label('arrival_date', 'Transport Date', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('arrival_date', !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '', ['class' => 'form-control datepicker', 'placeholder' => 'Enter Arrival Date']); ?>
            </div>
        </div>
    </div>
</div>



<h4 class="formdeviderh4">Shipper information</h4>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
            <div class="col-md-5 required">
                <?php echo Form::label('shipper_name', 'Shipper Name', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('shipper_name', $model->shipper_name, ['class' => 'form-control fexport_shipper_name', 'placeholder' => 'Enter Shipper Name', 'id' => 'export_shipper_name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('shipper_telephone') ? 'has-error' :'' }}">
            <?php echo Form::label('shipper_telephone', 'Phone Number', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('shipper_telephone', $model->shipper_telephone, ['class' => 'form-control fexport_shipment_number', 'placeholder' => 'Enter Shipment Number', 'id' => 'export_shipper_telephone']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">

    </div>
</div>

<div class="row" style="margin-left: 1%">
    <div class="col-md-8">
        <div class="form-group {{ $errors->has('shipper_address') ? 'has-error' :'' }}">
            <?php echo Form::label('shipper_address', 'Address', ['class' => 'col-md-2 control-label']); ?>
            <div class="col-md-6" style="margin-left: 30px;">
                <?php echo Form::textarea('shipper_address', $model->shipper_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address', 'id' => 'export_shipper_address']); ?>
            </div>
        </div>
    </div>
</div>


<h4 class="formdeviderh4">Receiver information</h4>
<div class="row" style="margin-left: 1%">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
            <div class="required">
                <?php echo Form::label('consignee_name', 'Receiver Name', ['class' => 'col-md-5 control-label']); ?>
            </div>
            <div class="col-md-6">
                <?php echo Form::text('consignee_name', $model->consignee_name, ['class' => 'form-control fexport_consignee_name', 'placeholder' => 'Enter Receiver Name', 'id' => 'export_consignee_name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('consignee_telephone') ? 'has-error' :'' }}">
            <?php echo Form::label('consignee_telephone', 'Phone Number', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('consignee_telephone', $model->consignee_telephone, ['class' => 'form-control fexport_consignee_telephone', 'placeholder' => 'Enter Phone Number', 'id' => 'export_consignee_phone']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('shipper_city') ? 'has-error' :'' }}">
            <?php echo Form::label('consignee_city_state', 'City / State', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('consignee_city_state', $model->consignee_city_state, ['class' => 'form-control', 'placeholder' => 'Enter City/State']); ?>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-left: 1%">
    <div class="col-md-8">
        <div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
            <?php echo Form::label('consignee_address', 'Address', ['class' => 'col-md-2 control-label']); ?>
            <div class="col-md-6" style="margin-left: 30px;">
                <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address', 'id' => 'export_consignee_address']); ?>
            </div>
        </div>
    </div>
</div>

<h4 class="formdeviderh4">Product information</h4>
<div class="row" style="margin-left: 1%">

    <div class="col-md-4">
        <div class="form-group {{ $errors->has('no_manifeste') ? 'has-error' :'' }}">
            <?php echo Form::label('package_type', 'Product Type', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::select('package_type', Config::get('app.productType'), '', ['class' => 'form-control selectpicker fexport_product_type', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('nbr_pcs') ? 'has-error' :'' }}">
            <?php echo Form::label('nbr_pcs', 'No of Pcs', ['class' => 'col-md-5 control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('nbr_pcs', $model->nbr_pcs, ['class' => 'form-control fexport_pices', 'placeholder' => 'Enter No of Pcs']); ?>
            </div>
        </div>
    </div>
</div>



<div class="form-group row btm-sub" style="margin-left: 1%">

    <button type="submit" class="btn btn-success">
        <?php
        if (!$model->id)
            echo "Submit";
        else
            echo "Update";
        ?>
    </button>

    <a class="btn btn-danger" href="{{url('ups')}}" title="">Cancel</a>
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
        $('#exportFileForm').on('submit', function(event) {


            $('.fexport_awb_number').each(function() {
                $(this).rules("add", {
                    required: false,
                })
            });

            $('.fexport_shipper_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

            $('.fexport_consignee_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fexport_shipment_number,.fexport_pices,.fexport_freight,.fexport_weight').each(function() {
                $(this).rules("add", {
                    number: true
                })
            });



        });
        $('#exportFileForm').validate({
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

        $("#export_consignee_name").autocomplete({

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
                        console.log(data);
                        $('#export_consignee_address').val(data.company_address);
                        $('#export_consignee_phone').val(data.phone_number);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                $("#export_consignee_name").val(ui.item.label);
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
        $("#export_shipper_name").autocomplete({

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
                        console.log(data);
                        $('#export_shipper_address').val(data.company_address);
                        $('#export_shipper_telephone').val(data.phone_number);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                $("#export_shipper_name").val(ui.item.label);
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