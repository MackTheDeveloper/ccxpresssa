                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w2','autocomplete'=>'off')) }}
                    {{ csrf_field() }}


                    <?php
                    if (!empty($model->id)) {
                        $countaddproddetail = App\CargoProductDetails::where('cargo_id', $model->id)->count();
                        $countconsolidatedetail = App\CargoConsolidateAwbHawb::where('cargo_id', $model->id)->count();
                    } else {
                        $countaddproddetail = 0;
                        $countconsolidatedetail = 0;
                    }
                    ?>
                    <input type="hidden" name="cargo_operation_type" value="2">
                    <div class="col-md-12">
                        <div class="col-md-6 flagconsolvv">
                            <div class="form-group {{ $errors->has('consolidate_flag') ? 'has-error' :'' }}">
                                <?php echo Form::label('consolidate_flag', 'Consolidate :', ['class' => 'control-label']); ?>
                                <div class="col-md-8 consolidate_flag-md-6">
                                    <?php
                                    if (!empty($model->id)) {
                                        echo Form::radio('consolidate_flag', '1', $model->consolidate_flag == '1' ? 'checked' : '', ['class' => 'flagconsol', 'id' => 'export_consolidate_flag']);
                                        echo Form::label('export_consolidate_flag', 'Consolidate');
                                        echo Form::radio('consolidate_flag', '0', $model->consolidate_flag == '0' ? 'checked' : '', ['class' => 'flagconsol', 'id' => 'export_non_consolidate_flag']);
                                        echo Form::label('export_non_consolidate_flag', 'Non consolidate');
                                    } else {
                                        echo Form::radio('consolidate_flag', '1', '', ['class' => 'flagconsol', 'id' => 'export_consolidate_flag']);
                                        echo Form::label('export_consolidate_flag', 'Consolidate');
                                        echo Form::radio('consolidate_flag', '0', true, ['class' => 'flagconsol', 'id' => 'export_non_consolidate_flag']);
                                        echo Form::label('export_non_consolidate_flag', 'Non consolidate');
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('awb_bl_no') ? 'has-error' :'' }}">
                                <?php echo Form::label('awb_bl_no', 'AWB/BL No :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('export_awb_bl_no', $model->awb_bl_no, ['class' => 'form-control', 'placeholder' => 'Enter AWB/BL No']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="divvv">
                        <div class="sec-consolidate col-md-12" style="display: none;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-2">
                                            <?php echo Form::label('awb_bl_no', 'House AWB No:', ['class' => 'control-label', 'style' => 'width:auto']); ?>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <input class="form-control export-inp" name="export_hawb_hbl_no" id="export_hawb_hbl_no" value="<?php echo $model->hawb_hbl_no; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <button data-module='HAWB Export File' style="color: #00a75f;float:left;margin-right: 20%;border: none;float:left" id="addNewItems" value="<?php echo url('items/addnewitem', ['addexporthawbfile']) ?>" type="button" class="addnewitems">Add HAWB File</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <?php echo Form::label('unit_of_file', 'Unit:', ['class' => 'control-label']); ?>
                                <div class="col-md-8 consolidate_flag-md-6">
                                    <?php
                                    echo Form::radio('unit_of_file', 'w', true, ['class' => 'unit_of_file_w', 'id' => 'unit_of_file_w']);
                                    echo Form::label('unit_of_file_w', 'Weight');
                                    echo Form::radio('unit_of_file', 'v', '', ['class' => 'unit_of_file_v', 'id' => 'unit_of_file_v']);
                                    echo Form::label('unit_of_file_v', 'Volume');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
                                <?php echo Form::label('weight', 'Weight :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('weight', $model->weight, ['class' => 'form-control weight', 'placeholder' => 'Enter Weight', 'onkeypress' => 'return isNumber(event)', 'id' => 'weightValExport']); ?>
                                </div>
                                <div class="col-md-2">
                                    <?php echo Form::select('measure_weight', Config::get('app.measureMass'), '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'weight_measure_export']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('no_of_pieces') ? 'has-error' :'' }}">
                                <?php echo Form::label('no_of_pieces', 'Pieces :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('no_of_pieces', $model->no_of_pieces, ['class' => 'form-control no_of_pieces', 'placeholder' => 'Enter Nbr of pieces', 'onkeypress' => 'return isNumber(event)']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('opening_date') ? 'has-error' :'' }}">
                                <?php echo Form::label('opening_date', 'Opening Date :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('opening_date', date('d-m-Y'), ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
                                <div class="required">
                                    <?php echo Form::label('shipper_name', 'Shipper Name:', ['class' => 'control-label']); ?>
                                </div>
                                <div class="col-md-8">
                                    <?php echo Form::text('shipper_name', $model->shipper_name, ['class' => 'form-control shipper_name_export fshipper_name', 'placeholder' => 'Enter Shipper Name']); ?>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_name', 'Consignee Name :', ['class' => ' control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::text('consignee_name', '', ['class' => 'form-control consignee_name_export', 'placeholder' => 'Enter Consignee Name']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_address', 'Consignee Address:', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control consignee_address_export', 'placeholder' => 'Enter Consignee Address', 'rows' => 2]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('agent_id') ? 'has-error' :'' }}">
                                <?php echo Form::label('agent_id', 'Agent :', ['class' => 'control-label']); ?>
                                <div class="col-md-6">
                                    <?php echo Form::select('agent_id', $agents, $model->agent_id, ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-12 warehousedivexport" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">

                                <?php echo Form::label('warehouse', 'Warehouse', ['class' => 'control-label']); ?>

                                <div class="col-md-8">
                                    <?php echo Form::select('warehouse', $warehouses, '', ['class' => 'invfieldtbl invfieldtblbillto form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
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
                                    <?php echo Form::text('sent_on', '', ['class' => 'form-control datepicker']); ?>
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
                                </div>
                            </div>
                        </div>
                    </div>


                    <h4 class="formdeviderh4">EXPLICATIONS / INFORMATIONS</h4>

                    <div class="form-group {{ $errors->has('information') ? 'has-error' :'' }}">
                        <div class="col-md-9">
                            <?php echo Form::textarea('information', $model->information, ['class' => 'form-control', 'placeholder' => 'Enter Information', 'rows' => 4, 'style' => 'border: 1px solid #ccd0d2;']); ?>
                        </div>
                    </div>


                    <div class="col-md-12 btm-sub">
                        <input type="hidden" name="flagBtn" class="flagBtn" id="flagBtn" value="">
                        <button type="submit" id="w2btn" class="btn btn-success">
                            <?php
                            if (!$model->id)
                                echo "Save";
                            else
                                echo "Update";
                            ?>
                        </button>
                        <button type="submit" id="buttonSavePrint" class="btn btn-success btn-prime white btn-flat">Save & Print</button>

                        <?php
                        $dept = auth()->user()->department;

                        if ($dept == '11') // Cashier
                        {
                            $listingCargoUrl = 'cashiercargoall';
                        } else {
                            $listingCargoUrl = 'cargoall';
                        }
                        ?>

                        <a class="btn btn-danger" href="{{url($listingCargoUrl)}}" title="">Cancel</a>
                    </div>

                    {{ Form::close() }}

                    <div id="modalAddNewItemsExport" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">×</button>
                                    <center>
                                        <h3 class="modal-title modal-title-block text-center primecolor"></h3>
                                    </center>
                                </div>
                                <div class="modal-body" id="modalContentAddNewItemsExport">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $datas = App\Clients::getClientsAutocomplete();
                    ?>

                    <script type="text/javascript">
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
                            $('#export_hawb_hbl_no').inputpicker({

                                data: <?php echo  $dataExportHawbAll; ?>,

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

                            $(document).on('click', 'tr.inputpicker-element', function() {
                                $('#loading').show();
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                                var selectedAWB = $('#export_hawb_hbl_no').val();
                                console.log(selectedAWB);
                                var urlzt = '<?php echo url("cargo/gettotalweightpiecesinexport"); ?>';
                                $.ajax({
                                    url: urlzt,
                                    async: false,
                                    dataType: "json",
                                    type: 'POST',
                                    data: {
                                        'selectedAWB': selectedAWB
                                    },
                                    success: function(data) {
                                        if ($('#weight_measure_export').val() === 'p') {
                                            $('#weightValExport').val(data.weight * 2.20462);
                                        } else {
                                            $('#weightValExport').val(data.weight);
                                        }
                                        $('#weightValExport').val(data.weight);
                                        $('.no_of_pieces').val(data.pieces);
                                        $('#loading').hide();
                                    }
                                });

                            });


                            // Consolidate section

                            $('.flagconsolvv .flagconsol').click(function() {
                                if ($(this).val() == 1) {
                                    $('.consignee_name_export').val('Chatelain Cargo Services');
                                    $('.warehousedivexport').show();
                                    $('.divvv .sec-nonconsolidate').hide();
                                    $('.divvv .sec-consolidate').show();
                                } else {
                                    $('.consignee_name_export').val('');
                                    $('.warehousedivexport').hide();
                                    $('.divvv .sec-nonconsolidate').show();
                                    $('.divvv .sec-consolidate').hide();
                                }
                            })

                            $('#w2').on('submit', function(event) {

                                $('.fshipper_name').each(function() {
                                    $(this).rules("add", {
                                        required: true,
                                    })
                                });
                            });

                            $('#w2').validate({
                                rules: {
                                    "export_awb_bl_no": {
                                        checkAwbNumber: true
                                    }
                                },
                                submitHandler: function(form) {
                                    $('#loading').show();
                                    var submitButtonName = $(this.submitButton).attr("id");
                                    if ($(this.submitButton).attr("id") == 'buttonSavePrint')
                                        $('.flagBtn').val('saveprint');
                                    else
                                        $('.flagBtn').val('');
                                    var createExpenseForm = $("#w2");
                                    var formData = createExpenseForm.serialize();

                                    $.ajax({
                                        url: '<?php echo route("storecargo") ?>',
                                        type: 'POST',
                                        data: formData,
                                        success: function(data) {
                                            $('#loading').hide();
                                            Lobibox.notify('info', {
                                                size: 'mini',
                                                delay: 2000,
                                                rounded: true,
                                                delayIndicator: false,
                                                msg: 'Record has been added successfully.'
                                            });
                                            $("html, body").animate({
                                                scrollTop: 0
                                            }, "slow");
                                            $("#w2")[0].reset();
                                            $('.divvv .sec-consolidate').hide();

                                            if (submitButtonName == 'buttonSavePrint') {
                                                window.open(data, '_blank');
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
                                    var urlz = '<?php echo url("cargo/checkuniqueawbnumber"); ?>';
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

                        })
                        let weightExport = 'k';
                        $('#weight_measure_export').on('change', function(event) {

                            console.log($(this).val());
                            if ($(this).val() === 'p' && weightExport == 'k') {
                                $('#weightValExport').val(($('#weightValExport').val() * 2.20462).toFixed(2));
                                weightExport = 'p';
                                console.log($('#weightValExport').val());
                            } else if ($(this).val() === 'k' && weightExport == 'p') {
                                $('#weightValExport').val(($('#weightValExport').val() / 2.20462).toFixed(2));
                                weightExport = 'k';
                                console.log($('#weightValExport').val());
                            } else {
                                $('#weightValExport').val(($('#weightValExport').val()).toFixed(2));
                                console.log($('#weightValExport').val());
                            }
                        });

                        $('#modalAddNewItemsExport').on('hidden.bs.modal', function() {
                            $('#hawb_hbl_no').val(<?php echo $model->hawb_hbl_no; ?>);

                        });
                    </script>