@extends('layouts.custom')
@section('title')
<?php echo 'Update Shipment'; ?>
@stop
<?php $url = url('ups/update/' . $model->id); ?>
@section('breadcrumbs')
@include('menus.ups-import')
@stop
<?php

use App\Ups; ?>
@section('content')
<section class="content-header" style="margin-bottom: 1.5%">
    <h1 style="float: left">Update Ups File (Export)</h1>
    <h1 style="float: right;color: green">File Status : <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($model->ups_scan_status) ? $model->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($model->ups_scan_status) ? $model->ups_scan_status : '-'] : '-'; ?></h1>
</section>
<section class="content">
    <div class="box box-success">
        <div class="box-body">

            {{ Form::open(array('url' => $url,'class'=>'form-horizontal','id'=>'exportFileForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <input type="hidden" name="courier_operation_type" value=<?php echo $model->courier_operation_type ?>>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
                        <?php echo Form::label('file_number', 'File Number :', ['class' => 'control-label', 'style' => 'float:left']); ?>
                        <div class="col-md-4">
                            <span class="form-control" style="border: none;font-weight: bold;
                                        box-shadow: none;"><?php echo $model->file_number; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
                        <div class="col-md-4">
                            <?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                        <div class="col-md-12 balance-div" style="display: none;text-align: center;">
                            <span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
                        </div>
                    </div>
                </div>
            </div>

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
                    <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
                        <?php echo Form::label('weight', 'Weight', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('weight', $model->weight, ['class' => 'form-control', 'placeholder' => 'Enter Weight', 'id' => 'fexport_weight']); ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row" style="margin-left: 1%">

                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('freight') ? 'has-error' :'' }}">
                        <?php echo Form::label('freight', 'Freight', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('freight', $model->freight, ['class' => 'form-control', 'placeholder' => 'Enter Freight', 'id' => 'fexport_freight']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('billing_term') ? 'has-error' :'' }}">
                        <?php echo Form::label('billing_term', 'Billing Term', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6 billing_term-md-6">
                            <?php

                            echo Form::radio('billing', '1', $model->fc == 1 ? true : false);
                            echo Form::label('fc', 'F/C');

                            echo Form::radio('billing', '2', $model->fd == 1 ? true : false);
                            echo Form::label('fd', 'F/D');
                            echo Form::radio('billing', '3', $model->pp == 1 ? true : false);
                            echo Form::label('pp', 'P/P');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="warehouse_div" style="display: <?php echo !empty($model->warehouse) ? 'block' : 'none'; ?>">
                    <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
                        <?php echo Form::label('warehouse', 'Warehouse', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::select('warehouse', $warehouses, $model->warehouse, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-left: 1%">
                <div class="col-md-4">
                    <div class="form-group">
                        <?php echo Form::label('ups_scan_status', 'File Status', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::select('ups_scan_status', Config::get('app.ups_new_scan_status'), $model->ups_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'ups_scan_status', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
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
                            <?php echo Form::text('shipper_name', Ups::getConsigneeName($model->shipper_name), ['class' => 'form-control fexport_shipper_name', 'placeholder' => 'Enter Shipper Name']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('shipper_telephone') ? 'has-error' :'' }}">
                        <?php echo Form::label('shipper_telephone', 'Phone Number', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('shipper_telephone', $model->shipper_telephone, ['class' => 'form-control fexport_shipment_number', 'placeholder' => 'Enter Shipment Number']); ?>
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
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                        <div class="required">
                            <?php echo Form::label('consignee_name', 'Receiver Name', ['class' => 'col-md-5 control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('consignee_name', Ups::getConsigneeName($model->consignee_name), ['class' => 'form-control fexport_consignee_name', 'placeholder' => 'Enter Receiver Name', 'id' => 'export_consignee_name']); ?>
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
                    <div class="form-group {{ $errors->has('consignee_city_state') ? 'has-error' :'' }}">
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
                            <?php echo Form::select('package_type', Config::get('app.productType'), $model->package_type, ['class' => 'form-control selectpicker fexport_product_type', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('nbr_pcs') ? 'has-error' :'' }}">
                        <?php echo Form::label('nbr_pcs', 'No of Pcs', ['class' => 'col-md-5 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('nbr_pcs', $model->nbr_pcs, ['class' => 'form-control', 'placeholder' => 'Enter No of Pcs', 'id' => 'fexport_pices']); ?>
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
        </div>
    </div>
</section>
@endsection
<?php
$datas = App\Clients::getClientsAutocomplete();

?>

@section('page_level_js')

<script type="text/javascript">
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

            $('.fexport_shipment_number').each(function() {
                $(this).rules("add", {
                    number: true,
                })
            });

            $('.fexport_consignee_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            /* $('.fexport_consignee_telephone').each(function() {
                $(this).rules("add", {
                    number: true,
                })
            }); */

            $('#fexport_freight').each(function() {
                $(this).rules("add", {
                    number: true,
                })
            });

            $('#fexport_weight').each(function() {
                $(this).rules("add", {
                    number: true,
                })
            });

            $('#fexport_pices').each(function() {
                $(this).rules("add", {
                    number: true,
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
                var flag = 'edit';
                var idz = '<?php echo $model->id; ?>';
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
        $('#ups_scan_status').change(function() {
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