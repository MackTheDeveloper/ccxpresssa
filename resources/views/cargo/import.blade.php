                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w1','autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <?php
                    if (!empty($model->id)) {
                        $countaddproddetail = App\CargoProductDetails::where('cargo_id', $model->id)->count();
                        $countcontainerdetail = App\CargoContainers::where('cargo_id', $model->id)->count();
                    } else {
                        $countaddproddetail = 0;
                        $countcontainerdetail = 0;
                    }
                    ?>
                    <input type="hidden" name="cargo_operation_type" value="1">

                    <div class="col-md-12">
                        <div class="col-md-6 flagconsolmn">
                            <div class="form-group {{ $errors->has('consolidate_flag') ? 'has-error' :'' }}">
                                <?php echo Form::label('consolidate_flag', 'Consolidate :', ['class' => 'control-label']); ?>
                                <div class="col-md-8 consolidate_flag-md-6">
                                    <?php
                                    if (!empty($model->id)) {
                                        echo Form::radio('consolidate_flag', '1', $model->consolidate_flag == '1' ? 'checked' : '', ['class' => 'flagconsol']);
                                        echo Form::label('consolidate_flag', 'Consolidate');
                                        echo Form::radio('consolidate_flag', '0', $model->consolidate_flag == '0' ? 'checked' : '', ['class' => 'flagconsol', 'id' => 'non_consolidate_flag']);
                                        echo Form::label('non_consolidate_flag', 'Non consolidate');
                                    } else {
                                        echo Form::radio('consolidate_flag', '1', '', ['class' => 'flagconsol']);
                                        echo Form::label('consolidate_flag', 'Consolidate');
                                        echo Form::radio('consolidate_flag', '0', true, ['class' => 'flagconsol', 'id' => 'non_consolidate_flag']);
                                        echo Form::label('non_consolidate_flag', 'Non consolidate');
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('awb_bl_no') ? 'has-error' :'' }}">
                                <?php echo Form::label('awb_bl_no', 'AWB/BL No :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('awb_bl_no', $model->awb_bl_no, ['class' => 'form-control', 'placeholder' => 'Enter AWB/BL No']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="divmn">
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
                                                    <input class="form-control" name="hawb_hbl_no" id="hawb_hbl_no" value="<?php echo $model->hawb_hbl_no; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <button data-module='HAWB Import File' style="color: #00a75f;margin-right: 20%;border: none;float:left" id="addNewItems" value="<?php echo url('items/addnewitem', ['addimporthawbfile']) ?>" type="button" class="addnewitems">Add HAWB File</button>
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
                                    echo Form::radio('unit_of_file', 'w', true, ['class' => 'unit_of_file_w','id'=> 'unit_of_file_w']);
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
                            <div class="form-group {{ $errors->has('flag_package_container') ? 'has-error' :'' }}">
                                <?php echo Form::label('type', 'Type:', ['class' => 'control-label']); ?>
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
                                    <div class="col-md-5">
                                        <?php echo Form::text('modalCargoPackage[pweight]', $modelCargoPackage->pweight, ['class' => 'form-control pweight', 'placeholder' => 'Enter Weight', 'onkeypress' => 'return isNumber(event)', 'id' => 'weightVal']); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo Form::select('measure_weight', Config::get('app.measureMass'), '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'weight_measure']); ?>
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
                                    </div>
                                    <div class="col-md-4">
                                        <?php echo Form::select('measure_volume', Config::get('app.measureDimension'), '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'volume_measure']); ?>
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
                                    </div>
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
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 sec-containersubcontainer">
                            <?php if (!empty($model->id)) {
                                $dataContainerDetails = App\CargoContainers::where('cargo_id', $model->id)->get();
                                $plusminuscontainer = 0;
                                if (empty($countcontainerdetail)) { ?>
                                    <div id="addcontainer-0">
                                        <div class="col-md-12">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('awb_bl_no') ? 'has-error' :'' }}">

                                                <div class="col-md-12">
                                                    <?php echo Form::text('modalCargoContainer[container_number][]', $modelCargoContainer->container_number, ['class' => 'form-control', 'placeholder' => 'Enter Container Number']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <a href="javascript:void(0)" class='btn btn-success btn-xs addcontainer'>+</a>
                                        </div>
                                    </div>
                                    <?php } else {
                                    foreach ($dataContainerDetails as $k => $v) { ?>
                                        <div id="addcontainer-<?php echo $plusminuscontainer; ?>">
                                            <div class="col-md-12">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group {{ $errors->has('awb_bl_no') ? 'has-error' :'' }}">

                                                    <div class="col-md-12">
                                                        <?php echo Form::text('modalCargoContainer[container_number][]', $v->container_number, ['class' => 'form-control', 'placeholder' => 'Enter Container Number']); ?>
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
                                        <div class="form-group {{ $errors->has('awb_bl_no') ? 'has-error' :'' }}">

                                            <div class="col-md-12">
                                                <?php echo Form::text('modalCargoContainer[container_number][]', $modelCargoContainer->container_number, ['class' => 'form-control', 'placeholder' => 'Enter Container Number']); ?>
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

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                                <div class="required">
                                    <?php echo Form::label('consignee_name', 'Consignee Name :', ['class' => ' control-label']); ?>
                                </div>
                                <div class="col-md-8">
                                    <?php echo Form::text('consignee_name', '', ['class' => 'form-control fconsignee_name', 'placeholder' => 'Enter Consignee Name']); ?>
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
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('shipper_name', 'Shipper Name :', ['class' => 'control-label']); ?>
                                <div class="col-md-8">
                                    <?php echo Form::text('shipper_name', $model->shipper_name, ['class' => 'form-control', 'placeholder' => 'Enter Shipper Name']); ?>
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

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('opening_date') ? 'has-error' :'' }}">
                                <?php echo Form::label('opening_date', 'Opening Date :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('opening_date', date('d-m-Y'), ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('arrival_date') ? 'has-error' :'' }}">
                                <?php echo Form::label('arrival_date', 'Arrival Date :', ['class' => 'control-label']); ?>
                                <div class="col-md-4">
                                    <?php echo Form::text('arrival_date', '', ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 warehousediv" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">

                                <?php echo Form::label('warehouse', 'Warehouse', ['class' => 'control-label']); ?>

                                <div class="col-md-8">
                                    <?php echo Form::select('warehouse', $warehouses, '', ['class' => 'invfieldtbl invfieldtblbillto form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
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
                        <button type="submit" id="w1btn" class="btn btn-success">
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

                    <div id="modalAddNewItemsImport" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">×</button>
                                    <center>
                                        <h3 class="modal-title modal-title-block text-center primecolor"></h3>
                                    </center>
                                </div>
                                <div class="modal-body" id="modalContentAddNewItemsImport">
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // $datas = App\Clients::getClientsAutocomplete();
                    ?>

                    @section('page_level_js')
                    <script type="text/javascript">
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

                            $('#hawb_hbl_no').inputpicker({

                                data: <?php echo  $dataImportHawbAll; ?>,

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

                            //$(document).on('click.inputpicker-element',function(){ 
                            $(document).on('click', 'tr.inputpicker-element', function() {
                                var selectedAWB = $('#hawb_hbl_no').val();

                                if ($('.flagconsol:checked').val() == 1) {

                                    $.ajaxSetup({
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        }
                                    });

                                    var urlzt = '<?php echo url("cargo/gettotalweightvolumeandpieces"); ?>';
                                    $.ajax({
                                        url: urlzt,
                                        async: false,
                                        dataType: "json",
                                        type: 'POST',
                                        data: {
                                            'selectedAWB': selectedAWB
                                        },
                                        success: function(data) {
                                            if ($('#weight_measure').val() === 'p') {
                                                $('.pweight').val(data.weight * 2.20462);
                                            } else {
                                                $('.pweight').val(data.weight);
                                            }
                                            if ($('#volume_measure').val() === 'f') {
                                                $('.pvolume').val(data.volume * 35.3147);
                                            } else {
                                                $('.pvolume').val(data.volume);
                                            }

                                            $('.ppieces').val(data.pieces);
                                        }
                                    });
                                }

                            });

                            // $(document).on('#hawb_hbl_no','click',function(e){
                            //$(document).delegate("#hawb_hbl_no", "click", function(e){
                            /*$('#hawb_hbl_no').change(function(input){
                                alert("sd");
                                //alert($(this).val());
                            })*/

                            $('.flagconsolmn .flagconsol').click(function() {
                                if ($(this).val() == 1) {
                                    $('#consignee_name').val('Chatelain Cargo Services');
                                    $('.warehousediv').show();
                                    $('.divmn .sec-nonconsolidate').hide();
                                    $('.divmn .sec-consolidate').show();
                                } else {
                                    $('#consignee_name').val('');
                                    $('.warehousediv').hide();
                                    $('.divmn .sec-nonconsolidate').show();
                                    $('.divmn .sec-consolidate').hide();
                                }
                            })

                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });



                            $("#consignee_name").autocomplete({
                                select: function(event, ui) {
                                    event.preventDefault();
                                    //$("#consignee_name").val(ui.item.label);
                                    $.ajaxSetup({
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        }
                                    });
                                    console.log(ui)
                                    console.log(ui.item.value)
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
                                source: "{{route('cargoautocompletesearchclient')}}",

                                minLength: 3,
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
                                source: "{{route('cargoautocompletesearchclient')}}",
                                minLength: 3,
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
                            if ('<?php echo $countcontainerdetail; ?>' != 0) {
                                countcontainerdetail = <?php echo $countcontainerdetail - 1; ?>;
                            }
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

                            $('#w1').on('submit', function(event) {

                                $('.fconsignee_name').each(function() {
                                    $(this).rules("add", {
                                        required: true,
                                    })
                                });
                            });

                            $('#w1').validate({
                                rules: {
                                    "awb_bl_no": {
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
                                    var createExpenseForm = $("#w1");
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
                                            $("#w1")[0].reset();
                                            $('.divmn .sec-consolidate').hide();

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


                            //Script to convert weight to pound and meter to feet
                            let weight = 'k',
                                volume = 'm';
                            $('#weight_measure').on('change', function(event) {
                                console.log($(this).val());
                                if ($(this).val() === 'p' && weight == 'k') {
                                    $('#weightVal').val(($('#weightVal').val() * 2.20462).toFixed(2));
                                    weight = 'p';
                                    console.log($('#weightVal').val());
                                } else if ($(this).val() === 'k' && weight == 'p') {
                                    $('#weightVal').val(($('#weightVal').val() / 2.20462).toFixed(2));
                                    weight = 'k';
                                    console.log($('#weightVal').val());
                                } else {
                                    $('#weightVal').val($('#weightVal').val());
                                    console.log($('#weightVal').val());
                                }
                            });
                            $('#volume_measure').on('change', function() {
                                console.log($(this).val());
                                if ($(this).val() === 'f' && volume === 'm') {
                                    $('#volumeVal').val(($('#volumeVal').val() * 35.3147).toFixed(2));
                                    volume = 'f';
                                } else if ($(this).val() === 'm' && volume === 'f') {
                                    $('#volumeVal').val(($('#volumeVal').val() / 35.3147).toFixed(2));
                                    volume = 'm';
                                } else {
                                    $('#volumeVal').val(($('#volumeVal').val()).toFixed(2));
                                }

                            });

                            $('#modalAddNewItemsImport').on('hidden.bs.modal', function() {
                                $('#hawb_hbl_no').val(<?php echo $model->hawb_hbl_no; ?>);

                            });

                        })
                    </script>
                    @stop