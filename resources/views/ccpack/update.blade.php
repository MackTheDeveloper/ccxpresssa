@extends('layouts.custom')
@section('title')
<?php echo 'Update CCpack File'; ?>
@stop

@section('breadcrumbs')
@include('menus.ccpack')
@stop


@section('content')
<section class="content-header" style="margin-bottom: 1.5%">
    <h1 style="float: left">
        <?php echo $model->ccpack_operation_type == 1 ? 'Update CCpack File (Import)' : 'Update CCpack File (Export)'; ?>
        <h1 style="float: right;color: green">File Status :
            <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($model->ccpack_scan_status) ? $model->ccpack_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($model->ccpack_scan_status) ? $model->ccpack_scan_status : '-'] : '-'; ?>
        </h1>
    </h1>
</section>
<?php

use App\Ups; ?>
<section class="content">
    <div class="box box-success">
        <div class="box-body">

            <?php
            $actionUrl = url('ccpack/update', $model->id);

            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'updateForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <input type="hidden" name="ccpack_operation_type" value=<?php echo $model->ccpack_operation_type; ?>>
            <?php if ($model->ccpack_operation_type == 1) { ?>
                <h4 class="formdeviderh4">File Status Information</h4>
                <div class="row" style="margin-left: 1%">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
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
                        <div class="form-group {{ $errors->has('awb_number') ? 'has-error' :'' }}">
                            <div class="col-md-5">
                                <?php echo Form::label('ccpack_scan_status', 'File Status', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo Form::select('ccpack_scan_status', Config::get('app.ups_new_scan_status'), $model->ccpack_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'ccpack_scan_status', 'placeholder' => 'Select ...']); ?>
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

                <div class="row" style="margin-left: 1%">
                    <?php if ($model->id) { ?>
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
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
                    <?php } ?>
                </div>

            <?php } else { ?>
                <div class="row" style="margin-left: 1%">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
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
                        <div class="form-group {{ $errors->has('awb_number') ? 'has-error' :'' }}">
                            <div class="col-md-5">
                                <?php echo Form::label('ccpack_scan_status', 'File Status', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo Form::select('ccpack_scan_status', Config::get('app.ups_new_scan_status'), $model->ccpack_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'ccpack_scan_status', 'placeholder' => 'Select ...']); ?>
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

                <div class="row" style="margin-left: 1%">
                    <?php if ($model->id) { ?>
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
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
                    <?php } ?>
                </div>


            <?php } ?>
            <h4 class="formdeviderh4">Shipment information</h4>
            <div class="row" style="margin-left: 1%">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('awb_number') ? 'has-error' :'' }}">

                        <div class="col-md-5">
                            <?php echo Form::label('awb_number', 'AWB Number', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('awb_number', $model->awb_number, ['class' => 'form-control ccpack_awb_number', 'placeholder' => 'Enter AWB Number', 'id']); ?>
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
                            <?php echo Form::text('arrival_date', !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : null, ['class' => 'form-control datepicker', 'placeholder' => '']); ?>
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
                            <?php echo Form::text('expences', $model->expences, ['class' => 'form-control ccpack_expences', 'placeholder' => 'Enter Other Expences']); ?>
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
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                            <?php echo Form::label('shipper_name', 'Shipper Name', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('shipper_name', Ups::getConsigneeName($model->shipper_name), ['class' => 'form-control ccpack_shipper_name', 'placeholder' => 'Enter Shipper Name']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-8">
                    <div class="form-group {{ $errors->has('shipper_address') ? 'has-error' :'' }}">
                        <?php echo Form::label('shipper_address', 'Address', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-6" style="margin-left: 30px;">
                            <?php echo Form::textarea('shipper_address', $model->shipper_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="formdeviderh4">Receiver information</h4>
            <div class="row" style="margin-left: 1%">


                <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }} col-md-4">
                    <div class="row" style="margin-left: 1%">
                        <div class="col-md-5 required">
                            <?php echo Form::label('consignee_name', 'Receiver Name', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('consignee_name', Ups::getConsigneeName($model->consignee), ['class' => 'form-control ccpack_consignee_name', 'placeholder' => 'Enter Receiver Name']); ?>
                        </div>
                    </div>
                </div>



                <div class="form-group {{ $errors->has('consignee_telephone') ? 'has-error' :'' }} col-md-4" style="margin-left: 2%">
                    <div class="row" style="margin-left: 1%">
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
                <div class="col-md-8">
                    <div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
                        <?php echo Form::label('consignee_address', 'Address', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-6" style="margin-left: 30px;">
                            <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
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
                <a class="btn btn-danger" href="{{url('ccpack')}}" title="">Cancel</a>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

<?php
$datas = App\Clients::getClientsAutocomplete();
?>
@section('page_level_js')
<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        $('#updateForm').on('submit', function(event) {


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
            $('.ccpack_consignee_telephone').each(function() {
                $(this).rules("add", {
                    number: true
                })
            });
            $('.ccpack_consignee_telephone,.ccpack_shipper_telephone,.ccpack_freight,.ccpack_weight,.ccpack_pices,.ccpack_commission,.ccpack_expences').each(function() {
                $(this).rules("add", {
                    number: true
                })
            });




        });
        $('#updateForm').validate({
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

        $('#ccpack_scan_status').change(function() {
            if ($(this).val() == 4) {
                $('#warehouse_div').show();
            } else {
                $('#warehouse_div').hide();
                $('#warehouses').val('');
            }
        });


    });
</script>
@stop