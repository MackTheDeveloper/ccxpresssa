                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w2','autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
                                <?php echo Form::label('file_number', 'File Number :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <span class="form-control" style="border-bottom:none;font-weight:bold"><?php echo $model->file_number; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 hawbbexport">


                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('hawb_hbl_no') ? 'has-error' :'' }}">

                                <?php echo Form::label('hawb_hbl_no', 'House AWB No.', ['class' => 'control-label']); ?>

                                <div class="col-md-4">
                                    <?php echo Form::text('export_hawb_hbl_no', $model->export_hawb_hbl_no, ['class' => 'form-control fhawb_hbl_no', 'placeholder' => 'Enter Hawb No']); ?>
                                    <span class="text-danger">
                                        <strong id="export_hawb_hbl_no-error"></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="cargo_operation_type" value="2">
                    <input type="hidden" name="id" value="<?php echo $model->id; ?>">

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('opening_date') ? 'has-error' :'' }}">
                                <?php echo Form::label('opening_date', 'Opening Date :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('opening_date', !empty($model->opening_date) ? date('d-m-Y', strtotime($model->opening_date)) : null, ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
                                    @if ($errors->has('opening_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('opening_date') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('shipper_name', 'Shipper Name:', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::text('shipper_name', App\Ups::getConsigneeName($model->shipper_name), ['class' => 'form-control shipper_name_export', 'placeholder' => 'Enter Shipper Name']); ?>
                                    <span class="text-danger">
                                        <strong id="shipper_name-error"></strong>
                                    </span>
                                    @if ($errors->has('shipper_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipper_name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('shipper_address') ? 'has-error' :'' }}">
                                <?php echo Form::label('shipper_address', 'Shipper Address :', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::textarea('shipper_address', $model->shipper_address, ['class' => 'form-control shipper_address_export', 'placeholder' => 'Enter Shipper Address', 'rows' => 2]); ?>
                                    @if ($errors->has('shipper_address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('shipper_address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_name', 'Consignee Name :', ['class' => ' control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::text('consignee_name', App\Ups::getConsigneeName($model->consignee_name), ['class' => 'form-control consignee_name_export', 'placeholder' => 'Enter Consignee Name']); ?>
                                    <span class="text-danger">
                                        <strong id="consignee_name-error"></strong>
                                    </span>
                                    @if ($errors->has('consignee_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('consignee_name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_address', 'Consignee Address :', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control consignee_address_export', 'placeholder' => 'Enter Consignee Address', 'rows' => 2]); ?>
                                    @if ($errors->has('consignee_address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('consignee_address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo Form::label('agent_id', 'Agent:', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::select('agent_id', $agents, $model->agent_id, ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
                                <?php echo Form::label('weight', 'Weight :', ['class' => 'control-label', 'style' => 'margin-left:4.5%']); ?>
                                <div class="col-md-4">

                                    <?php echo Form::text('weight', $model->weight, ['class' => 'form-control', 'placeholder' => 'Enter Weight', 'onkeypress' => 'return isNumber(event)', 'id' => 'weight']); ?>
                                    @if ($errors->has('weight'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('weight') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <?php
                                    $measure = [];

                                    if ($modelCargoPackage->measure_weight == 'k') {
                                        $measure = Config::get('app.measureMass');
                                    } else {
                                        $measure = ['p' => 'Pound', 'k' => 'Kg'];
                                    }
                                    ?>
                                    <?php echo Form::select('measure_weight', $measure, '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'measure_weight']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('no_of_pieces') ? 'has-error' :'' }}">
                                <?php echo Form::label('no_of_pieces', 'Pieces :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('no_of_pieces', $model->no_of_pieces, ['class' => 'form-control', 'placeholder' => 'Enter Nbr of pieces', 'onkeypress' => 'return isNumber(event)']); ?>
                                    @if ($errors->has('no_of_pieces'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('no_of_pieces') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>


                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">

                                <?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>

                                <div class="col-md-8">
                                    <?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'invfieldtbl invfieldtblbillto form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                                </div>
                                <div class="col-md-12 balance-div" style="display: none;text-align: center;">
                                    <span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">

                                <?php echo Form::label('hawb_scan_status', 'File Status', ['class' => 'control-label']); ?>

                                <div class="col-md-8">
                                    <?php echo Form::select('hawb_scan_status', Config::get('app.ups_new_scan_status'), $model->hawb_scan_status, ['class' => 'form-control selectpicker invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'hawb_scan_status', 'placeholder' => 'Select ...']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="formdeviderh4">Observations</h4>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('sent_on') ? 'has-error' :'' }}">
                                <?php echo Form::label('sent_on', 'Expédié le :', ['class' => 'control-label']); ?>
                                <div class="col-md-6">
                                    <?php echo Form::text('sent_on', !empty($model->sent_on) ? date('d-m-Y', strtotime($model->sent_on)) : '', ['class' => 'form-control datepicker']); ?>
                                    @if ($errors->has('sent_on'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sent_on') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('sent_by') ? 'has-error' :'' }}">
                                <?php echo Form::label('sent_by', 'Transporteur :', ['class' => 'control-label']); ?>
                                <div class="col-md-6">
                                    <?php echo Form::text('sent_by', $model->sent_by, ['class' => 'form-control', 'placeholder' => '']); ?>
                                    @if ($errors->has('sent_by'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('sent_by') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>


                    <h4 class="formdeviderh4">EXPLICATIONS / INFORMATIONS</h4>

                    <div class="form-group {{ $errors->has('information') ? 'has-error' :'' }}">
                        <div class="col-md-9">
                            <?php echo Form::textarea('information', $model->information, ['class' => 'form-control', 'placeholder' => 'Enter Information', 'rows' => 4, 'style' => 'border: 1px solid #ccd0d2;']); ?>
                            @if ($errors->has('information'))
                            <span class="help-block">
                                <strong>{{ $errors->first('information') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>


                    <div class="col-md-12 btm-sub">
                        <button type="submit" id="w2btn" class="btn btn-success">
                            <?php
                            if (!$model->id)
                                echo "Submit";
                            else
                                echo "Update";
                            ?>
                        </button>
                        <a class="btn btn-danger" href="{{url('hawbfiles')}}" title="">Cancel</a>
                    </div>

                    {{ Form::close() }}
                    <?php
                    $datas = App\Clients::getClientsAutocomplete();
                    ?>

                    <script type="text/javascript">
                        $('select').change(function() {
                            if ($(this).val() != "") {
                                $(this).valid();
                            }
                        });

                        function isNumber(evt) {
                            evt = (evt) ? evt : window.event;
                            var charCode = (evt.which) ? evt.which : evt.keyCode;
                            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
                                return false;
                            }
                            return true;
                        }
                        $(document).ready(function() {
                            $(".consignee_name_export").autocomplete({
                                select: function(event, ui) {
                                    event.preventDefault();
                                    //$("#consignee_name").val(ui.item.label);
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
                                            $('.consignee_address_export').val(data.company_address);
                                        }
                                    });
                                },
                                focus: function(event, ui) {
                                    $('#loading').show();
                                    event.preventDefault();
                                    $(".consignee_name_export").val(ui.item.label);
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

                            $(".shipper_name_export").autocomplete({
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
                                            $('.shipper_address_export').val(data.company_address);
                                        }
                                    });
                                },
                                focus: function(event, ui) {
                                    $('#loading').show();
                                    event.preventDefault();
                                    $(".shipper_name_export").val(ui.item.label);
                                    $('#loading').hide();
                                },
                                change: function(event, ui) {
                                    if (ui.item == null || typeof(ui.item) == "undefined") {
                                        /*console.log("dsfdsf");
                                        $('#loading').show();
                                        $('#shipper_name').val("");
                                        $('#loading').hide();*/

                                    }
                                },
                                source: <?php echo $datas; ?>,
                                minLength: 1,
                            });

                            $('.datepicker').datepicker({
                                format: 'dd-mm-yyyy',
                                todayHighlight: true,
                                autoclose: true
                            });
                            $('#w2').on('click', '#w2btn', function(e) {
                                e.preventDefault();
                                $('#loading').show();
                                var createExpenseForm = $("#w2");
                                var formData = createExpenseForm.serialize();
                                $('#shipper_name-error').html("");
                                //$( '#export_cargo_id-error' ).html( "" );
                                $('#export_file_name-error').html("");
                                $('#export_hawb_hbl_no-error').html("");


                                $.ajax({
                                    url: '<?php echo route("updatehawbfile", $model->id) ?>',
                                    type: 'POST',
                                    data: formData,
                                    success: function(data) {
                                        console.log(data.errors);
                                        if (data.errors) {
                                            if (data.errors.shipper_name) {
                                                $('#shipper_name-error').html(data.errors.shipper_name[0]);
                                            }
                                            /*if(data.errors.export_cargo_id){
                                                $( '#export_cargo_id-error' ).html( data.errors.export_cargo_id[0] );
                                            }*/
                                            if (data.errors.export_file_name) {
                                                $('#export_file_name-error').html(data.errors.export_file_name[0]);
                                            }
                                            if (data.errors.export_hawb_hbl_no) {
                                                $('#export_hawb_hbl_no-error').html(data.errors.export_hawb_hbl_no[0]);
                                            }
                                            $("html, body").animate({
                                                scrollTop: 0
                                            }, "slow");
                                            $('#loading').hide();
                                        }
                                        if (data.success) {
                                            $('#loading').hide();
                                            Lobibox.notify('info', {
                                                size: 'mini',
                                                delay: 2000,
                                                rounded: true,
                                                delayIndicator: false,
                                                msg: 'Export shipment has been added successfully.'
                                            });
                                            $("html, body").animate({
                                                scrollTop: 0
                                            }, "slow");
                                            //$("#w2")[0].reset();
                                            window.location = '<?php echo route("hawbfiles") ?>';
                                        }
                                    },
                                });
                            });

                            let weight = 'k';
                            $('#measure_weight').on('change', function() {

                                var weight_val = $('#weight').val();
                                //console.log(weight_val);
                                var symbol = $(this).val();
                                //console.log(symbol);
                                if (symbol == 'p' && weight == 'k') {
                                    $('#weight').val((weight_val * 2.20462).toFixed(2));
                                    weight = 'p';
                                } else if (symbol == 'k' && weight == 'p') {
                                    $('#weight').val((weight_val / 2.20462).toFixed(2));
                                    weight = 'k';
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
                                                $('.cash_credit_account_balance').html(blnc).formatCurrency({
                                                    negativeFormat: '-%s%n',
                                                    roundToDecimalPlace: 2,
                                                    symbol: ''
                                                });
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
                                                $('.cash_credit_account_balance').html(blnc).formatCurrency({
                                                    negativeFormat: '-%s%n',
                                                    roundToDecimalPlace: 2,
                                                    symbol: ''
                                                });
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
                        })
                    </script>