                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w1','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
                    <?php
                    if (!empty($model->id)) {
                        $countcontainerdetail = App\HawbContainers::where('hawb_id', $model->id)->count();
                    } else {
                        $countcontainerdetail = 0;
                    }
                    ?>
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

                    <div class="col-md-12 hawbbimport">

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('hawb_hbl_no') ? 'has-error' :'' }}">

                                <?php echo Form::label('hawb_hbl_no', 'House AWB No.', ['class' => 'control-label']); ?>

                                <div class="col-md-4">
                                    <?php echo Form::text('hawb_hbl_no', $model->hawb_hbl_no, ['class' => 'form-control fhawb_hbl_no', 'placeholder' => 'Enter Hawb No']); ?>
                                    <span class="text-danger">
                                        <strong id="hawb_hbl_no-error"></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="cargo_operation_type" value="1">
                    <input type="hidden" name="id" value="<?php echo $model->id; ?>">

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_name', 'Consignee Name :', ['class' => ' control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::text('consignee_name', App\Ups::getConsigneeName($model->consignee_name), ['class' => 'form-control', 'placeholder' => 'Enter Consignee Name']); ?>
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
                                <?php echo Form::label('consignee_address', 'Consignee Address :', ['class' => ' control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control', 'placeholder' => 'Enter Consignee Address', 'rows' => 2]); ?>
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
                            <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('shipper_name', 'Shipper Name:', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::text('shipper_name', App\Ups::getConsigneeName($model->shipper_name), ['class' => 'form-control', 'placeholder' => 'Enter Shipper Name']); ?>
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
                            <div class="form-group">
                                <?php echo Form::label('agent_id', 'Agent:', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::select('agent_id', $agents, $model->agent_id, ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

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
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('arrival_date') ? 'has-error' :'' }}">
                                <?php echo Form::label('arrival_date', 'Arrival Date :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('arrival_date', !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : null, ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
                                    <span class="text-danger">
                                        <strong id="arrival_date-error"></strong>
                                    </span>
                                    @if ($errors->has('arrival_date'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('arrival_date') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('flag_package_container') ? 'has-error' :'' }}">
                                <?php echo Form::label('', '', ['class' => 'control-label']); ?>
                                <div class="col-md-8 consolidate_flag-md-6">
                                    <?php
                                    if (!empty($model->id)) {
                                        echo Form::radio('flag_package_container', '1', $model->flag_package_container == '1' ? 'checked' : '', ['class' => 'flagconsolpackagecontainer']);
                                        echo Form::label('', 'Package');
                                        echo Form::radio('flag_package_container', '2', $model->flag_package_container == '2' ? 'checked' : '', ['class' => 'flagconsolpackagecontainer']);
                                        echo Form::label('', 'Container');
                                    } else {
                                        echo Form::radio('flag_package_container', '1', true, ['class' => 'flagconsolpackagecontainer']);
                                        echo Form::label('', 'Package');
                                        echo Form::radio('flag_package_container', '2', '', ['class' => 'flagconsolpackagecontainer']);
                                        echo Form::label('', 'Container');
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12 packagediv" style="margin-left: 1%;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-3 form-group {{ $errors->has('pweight') ? 'has-error' :'' }}">
                                        <?php echo Form::label('pweight', 'Weight :', ['class' => 'control-label']); ?>
                                    </div>
                                    <div class="col-md-5" style="margin-left: 8%">
                                        <?php echo Form::text('modalCargoPackage[pweight]', $modelCargoPackage->pweight, ['class' => 'form-control pweight', 'placeholder' => 'Enter Weight', 'onkeypress' => 'return isNumber(event)', 'id' => 'weightVal']); ?>
                                        @if ($errors->has('pweight'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('pweight') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <?php
                                        $measure = [];

                                        if ($modelCargoPackage->measure_weight == 'k') {
                                            $measure = Config::get('app.measureMass');
                                        } else {
                                            $measure = ['p' => 'Pound', 'k' => 'Kg'];
                                        }
                                        ?>
                                        <?php echo Form::select('measure_weight', $measure, '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'weight_measure']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-3 form-group {{ $errors->has('pvolume') ? 'has-error' :'' }}">
                                        <?php echo Form::label('pvolume', 'Volume :', ['class' => 'control-label']); ?>
                                    </div>
                                    <div class="col-md-5">
                                        <?php echo Form::text('modalCargoPackage[pvolume]', $modelCargoPackage->pvolume, ['class' => 'form-control pvolume', 'placeholder' => 'Enter Volume', 'onkeypress' => 'return isNumber(event)', 'id' => 'volumeVal']); ?>
                                        @if ($errors->has('pvolume'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('pvolume') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        $secondMeasure = [];

                                        if ($modelCargoPackage->measure_volume == 'm') {
                                            $secondMeasure = Config::get('app.measureDimension');
                                        } else {
                                            $secondMeasure = ['m' => 'Cubic meter', 'f' => 'Cubic feet'];
                                        }
                                        ?>
                                        <?php echo Form::select('measure_volume', $secondMeasure, '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'volume_measure']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-3 form-group {{ $errors->has('ppieces') ? 'has-error' :'' }}">
                                        <?php echo Form::label('ppieces', 'Pieces :', ['class' => 'control-label']); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo Form::text('modalCargoPackage[ppieces]', $modelCargoPackage->ppieces, ['class' => 'form-control ppieces', 'placeholder' => 'Enter Pieces', 'onkeypress' => 'return isNumber(event)']); ?>
                                        @if ($errors->has('ppieces'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('ppieces') }}</strong>
                                        </span>
                                        @endif
                                    </div>
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

                    <div class="col-md-12 containerdiv" style="display: none">
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('no_of_container') ? 'has-error' :'' }}">
                                <?php echo Form::label('no_of_container', 'No. of container :', ['class' => 'control-label']); ?>
                                <div class="col-md-6">
                                    <?php echo Form::text('no_of_container', $model->no_of_container, ['class' => 'form-control', 'placeholder' => 'Enter no. of container']); ?>
                                    @if ($errors->has('no_of_container'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('no_of_container') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 sec-containersubcontainer">
                            <?php if (!empty($model->id)) {
                                $dataContainerDetails = App\HawbContainers::where('hawb_id', $model->id)->get();
                                $plusminuscontainer = 0;
                                if (empty($countcontainerdetail)) {  ?>
                                    <div id="addcontainer-0">
                                        <div class="col-md-12">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('container_number') ? 'has-error' :'' }}">

                                                <div class="col-md-12">
                                                    <?php echo Form::text('modalCargoContainer[container_number][]', $modelCargoContainer->container_number, ['class' => 'form-control', 'placeholder' => 'Enter Container Number']); ?>
                                                    @if ($errors->has('container_number'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('container_number') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <a href="javascript:void(0)" class='btn btn-success btn-xs addcontainer'>+</a>
                                        </div>
                                    </div>
                                    <?php } else {
                                    foreach ($dataContainerDetails as $k => $v) {  ?>
                                        <div id="addcontainer-<?php echo $plusminuscontainer; ?>">
                                            <div class="col-md-12">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group {{ $errors->has('container_number') ? 'has-error' :'' }}">

                                                    <div class="col-md-12">
                                                        <?php echo Form::text('modalCargoContainer[container_number][]', $v->container_number, ['class' => 'form-control', 'placeholder' => 'Enter Container Number']); ?>
                                                        @if ($errors->has('container_number'))
                                                        <span class="help-block">
                                                            <strong>{{ $errors->first('container_number') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <a href="javascript:void(0)" class='btn btn-success btn-xs addcontainer'>+</a>
                                                <?php if ($plusminuscontainer != 0) { ?>
                                                    <td><a style='' href='javascript:void(0)' class='btn btn-danger btn-xs removecontainer' id=<?php echo $plusminuscontainer; ?>>-</a></td>
                                                <?php } ?>
                                            </div>
                                        </div>

                                <?php $plusminuscontainer++;
                                    }
                                }
                            } else { ?>
                                <div id="addcontainer-0">
                                    <div class="col-md-12">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {{ $errors->has('container_number') ? 'has-error' :'' }}">

                                            <div class="col-md-12">
                                                <?php echo Form::text('modalCargoContainer[container_number][]', $modelCargoContainer->container_number, ['class' => 'form-control', 'placeholder' => 'Enter Container Number']); ?>
                                                @if ($errors->has('container_number'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('container_number') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <a href="javascript:void(0)" class='btn btn-success btn-xs addcontainer'>+</a>
                                    </div>
                                </div>
                            <?php } ?>
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
                        <button type="submit" id="w1btn" class="btn btn-success">
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
                    @section('page_level_js')
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
                        $('.datepicker').datepicker({
                            format: 'dd-mm-yyyy',
                            todayHighlight: true,
                            autoclose: true
                        });
                        $(document).ready(function() {
                            $("#consignee_name").autocomplete({
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
                                            $('#consignee_address').val(data.company_address);
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
                                },
                                focus: function(event, ui) {
                                    $('#loading').show();
                                    event.preventDefault();
                                    $("#shipper_name").val(ui.item.label);
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


                            <?php
                            if (!empty($model->id)) {
                                if ($model->flag_package_container == 1) { ?>
                                    $('.containerdiv').hide();
                                    $('.packagediv').show();
                                <?php } else { ?>
                                    $('.containerdiv').show();
                                    $('.packagediv').hide();
                            <?php }
                            } ?>

                            $('.flagconsolpackagecontainer').click(function() {
                                if ($(this).val() == 1) {
                                    $('.containerdiv').hide();
                                    $('.packagediv').show();
                                } else {
                                    $('.containerdiv').show();
                                    $('.packagediv').hide();
                                }
                            })

                            var countcontainerdetail = 0;
                            $(document).on("click", ".addcontainer", function(e) {
                                countcontainerdetail = countcontainerdetail + 1;
                                if (countcontainerdetail == 0) {
                                    countcontainerdetail = 1;
                                }
                                e.preventDefault();
                                var str = '<div id="addcontainer-' + countcontainerdetail + '"><div class="col-md-12"></div><div class="col-md-6"><div class="form-group "><div class="col-md-12"><input class="form-control" placeholder="Enter Container Number" name="modalCargoContainer[container_number][]" type="text">                                                        </div></div></div><div class="col-md-2"><a style="margin-right: 10px;" href="javascript:void(0)" class="btn btn-success btn-xs addcontainer">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removecontainer" id="' + countcontainerdetail + '">-</a></div></div>';
                                $('.sec-containersubcontainer').append(str);
                            });
                            $(document).on("click", ".removecontainer", function(e) {
                                e.preventDefault();
                                var id = $(this).attr('id');
                                $("#addcontainer-" + id).remove();
                            });

                            $('#w1').on('click', '#w1btn', function(e) {
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                                e.preventDefault();
                                $('#loading').show();
                                var createExpenseForm = $("#w1");
                                var formData = createExpenseForm.serialize();
                                $('#consignee_name-error').html("");
                                $('#arrival_date-error').html("");
                                //$( '#cargo_id-error' ).html( "" );
                                $('#file_name-error').html("");
                                $('#hawb_hbl_no-error').html("");

                                $.ajax({
                                    url: '<?php echo route("updatehawbfile", $model->id) ?>',
                                    type: 'POST',
                                    data: formData,
                                    success: function(data) {
                                        console.log(data);
                                        if (data.errors) {
                                            if (data.errors.consignee_name) {
                                                $('#consignee_name-error').html(data.errors.consignee_name[0]);
                                            }
                                            if (data.errors.arrival_date) {
                                                $('#arrival_date-error').html(data.errors.arrival_date[0]);
                                            }
                                            /*if(data.errors.cargo_id){
                                            $( '#cargo_id-error' ).html( data.errors.cargo_id[0] );
                                            }*/
                                            if (data.errors.file_name) {
                                                $('#file_name-error').html(data.errors.file_name[0]);
                                            }
                                            if (data.errors.hawb_hbl_no) {
                                                $('#hawb_hbl_no-error').html(data.errors.hawb_hbl_no[0]);
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
                                                msg: 'Import shipment has been added successfully.'
                                            });
                                            $("html, body").animate({
                                                scrollTop: 0
                                            }, "slow");
                                            //$('#w1 input#consignee_name').val('');
                                            window.location = '<?php echo route("hawbfiles") ?>';
                                        }
                                    },
                                });
                            });




                            $('.hawbbimport #cargo_id').change(function() {
                                $('.hawbbimport .awb_bl_no').val($(".hawbbimport #cargo_id option:selected").html());
                            });


                            let weight = 'k',
                                volume = 'm';
                            $('#weight_measure').on('change', function(event) {
                                console.log($(this).val());
                                if ($(this).val() === 'p') {
                                    var kvl = $('#weightVal').val() * 2.20462;
                                    $('#weightVal').val(kvl.toFixed(2));
                                    weight = 'p';
                                    console.log("hello");
                                } else if ($(this).val() === 'k') {
                                    $('#weightVal').val(($('#weightVal').val() / 2.20462).toFixed(2));
                                    weight = 'k';
                                    console.log($('#weightVal').val());
                                } else {
                                    $('#weightVal').val(($('#weightVal').val().toFixed(4)));
                                    console.log("hello");
                                }
                            });
                            $('#volume_measure').on('change', function() {
                                console.log($(this).val());
                                if ($(this).val() === 'f') {
                                    $('#volumeVal').val(($('#volumeVal').val() * 35.3147).toFixed(2));
                                    volume = 'f';
                                } else if ($(this).val() === 'm') {
                                    $('#volumeVal').val(($('#volumeVal').val() / 35.3147).toFixed(2));
                                    volume = 'm';
                                } else {
                                    $('#volumeVal').val(($('#volumeVal').val().toFixed(2)));
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
                    @stop