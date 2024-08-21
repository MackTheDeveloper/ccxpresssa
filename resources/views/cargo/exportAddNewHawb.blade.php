                    <style type="text/css">
                        .ui-front {
                           z-index: 9999;
                        }
                    </style>

                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w3AWB','autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <div class="col-md-12 hawbbexport">
                            <div class="col-md-6" style="display: none;">
                                    <div class="form-group {{ $errors->has('cargo_id') ? 'has-error' :'' }}">
                                        <?php echo Form::label('cargo_id', 'AWB / BL No.',['class'=>'control-label']); ?>
                                        <div class="col-md-8">
                                            <?php echo Form::select('export_cargo_id', $dataExportAwbNos,$model->cargo_id,['class'=>'form-control selectpicker fcargo_id invfieldtbl invfieldtblbillto','placeholder' => 'Select ...','data-live-search' => 'true','id'=>'export_cargo_id']); ?>
                                            <span class="text-danger">
                                                <strong id="export_cargo_id-error"></strong>
                                            </span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="awb_bl_no" class="awb_bl_no" id="awb_bl_no" value="">
                            </div>
                        
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('export_hawb_hbl_no') ? 'has-error' :'' }}">
                                    
                                    <?php echo Form::label('export_hawb_hbl_no', 'House AWB No.',['class'=>'control-label']); ?>
                                    
                                    <div class="col-md-4">
                                    <?php echo Form::text('export_hawb_hbl_no',$model->export_hawb_hbl_no,['class'=>'form-control fhawb_hbl_no','placeholder' => 'Enter House AWB No.']); ?>
                                    <span class="text-danger">
                                                <strong id="export_hawb_hbl_no_error_awb"></strong>
                                            </span>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <input type="hidden" name="cargo_operation_type" value="2">
                    
                    <div class="col-md-12">
                         <div class="col-md-6">
                                <div class="form-group {{ $errors->has('opening_date') ? 'has-error' :'' }}">
                                    <?php echo Form::label('opening_date', 'Opening Date :',['class'=>'control-label']); ?>
                                    <div class="col-md-4">
                                    <?php echo Form::text('opening_date',date('d-m-Y'),['class'=>'form-control datepicker','placeholder' => 'Enter Date']); ?>
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
                                <?php echo Form::label('shipper_name', 'Shipper Name:',['class'=>'control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::text('shipper_name',$model->shipper_name,['class'=>'form-control shipper_name_export','placeholder' => 'Enter Shipper Name']); ?>
                                <span class="text-danger">
                                    <strong id="shipper_name_error_awb"></strong>
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
                                <?php echo Form::label('shipper_address', 'Shipper Address :',['class'=>'control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::textarea('shipper_address',$model->shipper_address,['class'=>'form-control shipper_address_export','placeholder' => 'Enter Shipper Address','rows'=>2]); ?>
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
                                <?php echo Form::label('consignee_name', 'Consignee Name :',['class'=>' control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::text('consignee_name',$model->consignee_name,['class'=>'form-control consignee_name_export','placeholder' => 'Enter Consignee Name','id'=>'consignee_name_export']); ?>
                                <span class="text-danger">
                                    <strong id="consignee_name_error_hawb"></strong>
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
                                <?php echo Form::label('consignee_address', 'Consignee Address:',['class'=>'control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::textarea('consignee_address',$model->consignee_address,['class'=>'form-control consignee_address_export','placeholder' => 'Enter Consignee Address','rows'=>2]); ?>
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
                            <div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }} row">
                                <?php echo Form::label('weight', 'Weight :',['class'=>'control-label']); ?>
                                <div class="col-md-4">
                                <?php echo Form::text('weight',$model->weight,['class'=>'form-control','placeholder' => 'Enter Weight','onkeypress'=>'return isNumber(event)']); ?>
                                @if ($errors->has('weight'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('weight') }}</strong>
                                            </span>
                                @endif
                                </div>
                                <div class="col-md-4">
                                 <?php echo Form::select('measure_weight',Config::get('app.measureMass'),'',['class'=>'form-control','data-live-search' => 'true']); ?>
                                </div>
                                </div>
                            </div>
                        
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('no_of_pieces') ? 'has-error' :'' }}">
                                <?php echo Form::label('no_of_pieces', 'Pieces :',['class'=>'control-label']); ?>
                                <div class="col-md-4">
                                <?php echo Form::text('no_of_pieces','0',['class'=>'form-control','placeholder' => 'Enter Nbr of pieces','onkeypress'=>'return isNumber(event)']); ?>
                                @if ($errors->has('no_of_pieces'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('no_of_pieces') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>

                        
                    </div>

                 
                    

                    <h4 class="formdeviderh4">Observations</h4>

                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('sent_on') ? 'has-error' :'' }}">
                                <?php echo Form::label('sent_on', 'Expédié le :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('sent_on','',['class'=>'form-control datepicker']); ?>
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
                                <?php echo Form::label('sent_by', 'Transporteur :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('sent_by',$model->sent_by,['class'=>'form-control','placeholder' => '']); ?>
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
                                <?php echo Form::textarea('information',$model->information,['class'=>'form-control','placeholder' => 'Enter Information','rows'=>4,'style'=>'border: 1px solid #ccd0d2;']); ?>
                                @if ($errors->has('information'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('information') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>

              
                    <div class="col-md-12 btm-sub">
                                <button type="submit" id="w3AWBbtn" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
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
          $('select').change(function(){
                    if ($(this).val()!="")
                    {
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
       
        $( "#consignee_name_export" ).autocomplete({
                    select: function (event, ui) {
                        event.preventDefault();
                        //$("#consignee_name").val(ui.item.label);
                        $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                        });
                        var clientId    =    ui.item.value;
                        var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                        $.ajax({
                                url:urlztnn,
                                dataType: "json",
                                async:false,
                                type:'POST',
                                data:{'clientId':clientId},
                                success:function(data) {
                                            $('.consignee_address_export').val(data.company_address);
                                        }
                            });
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $("#consignee_name_export").val(ui.item.label);
                    $('#loading').hide();
                    },
                    change: function (event, ui)
                        {
                            if (ui.item == null || typeof (ui.item) == "undefined")
                            {
                                //console.log("dsfdsf");
                                //$('#loading').show();
                                //$('#consignee_name').val("");
                                //$('#loading').hide();
                                
                            }
                        },
                   source: <?php echo $datas; ?>,
                   minLength:1,  
         });

        $( ".shipper_name_export" ).autocomplete({
                select: function (event, ui) {
                        event.preventDefault();
                        $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                        });
                        var clientId    =    ui.item.value;
                        var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                        $.ajax({
                                url:urlztnn,
                                dataType: "json",
                                async:false,
                                type:'POST',
                                data:{'clientId':clientId},
                                success:function(data) {
                                            $('.shipper_address_export').val(data.company_address);
                                        }
                            });
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $(".shipper_name_export").val(ui.item.label);
                    $('#loading').hide();
                    },
                    change: function (event, ui)
                        {
                            if (ui.item == null || typeof (ui.item) == "undefined")
                            {
                                /*console.log("dsfdsf");
                                $('#loading').show();
                                $('#shipper_name').val("");
                                $('#loading').hide();*/
                                
                            }
                        },
                   source: <?php echo $datas;?>,
                   minLength:1,  
         });

        $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
        $('#w3AWB').on('click', '#w3AWBbtn', function(e){
        e.preventDefault();            
        $('#loading').show();
        var createExpenseForm = $("#w3AWB");
        var formData = createExpenseForm.serialize();
        $( '#shipper_name_error_awb' ).html( "" );
        //$( '#export_cargo_id-error' ).html( "" );
        $('#consignee_name_error_hawb').html("");
        $( '#export_file_name_error_awb' ).html( "" );
        $( '#export_hawb_hbl_no_error_awb' ).html( "" );
        
        
        $.ajax({
            url:'<?php echo route("storehawb") ?>',
            type:'POST',
            data:formData,
            success:function(data) {
                console.log(data.errors);
                if(data.errors) {

                        if(data.errors.consignee_name){
                            $('#consignee_name_error_hawb').html(data.errors.consignee_name[0]);
                        }
                        if(data.errors.shipper_name){
                            $( '#shipper_name_error_awb' ).html( data.errors.shipper_name[0] );
                        }
                        /*if(data.errors.export_cargo_id){
                            $( '#export_cargo_id-error' ).html( data.errors.export_cargo_id[0] );
                        }*/
                        if(data.errors.export_file_name){
                            $( '#export_file_name_error_awb' ).html( data.errors.export_file_name[0] );
                        }
                        if(data.errors.export_hawb_hbl_no){
                            $( '#export_hawb_hbl_no_error_awb' ).html( data.errors.export_hawb_hbl_no[0] );
                        }
                        $("#modalAddNewItemsExport").animate({ scrollTop: 0 }, "slow");
                    $('#loading').hide();
                    }
                if(data.success) {
                    $('#loading').hide();
                    Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'Export shipment has been added successfully.'
                            });
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                    $("#w3AWB")[0].reset();
                    $("#modalAddNewItemsExport").modal("hide");
                    console.log(data.dataExportHawbAll);
                    var allfile = JSON.parse(data.dataExportHawbAll); 
                        console.log($('#export_hawb_hbl_no'));
                         $('#export_hawb_hbl_no,#hawb_hbl_no').inputpicker({

                        data:allfile,

                        fields:[
                            {name:'hawb_hbl_no',text:'House AWB No.'},
                            {name:'consignee',text:'Consignee'},
                            {name:'shipper',text:'Shipper'},
                            ],
                        autoOpen: true,
                        headShow: true,
                        fieldText : 'hawb_hbl_no',
                        fieldValue: 'value',
                        filterOpen: true,
                         multiple: true,
                    });
                    //window.location = '<?php //echo route("cargoexports") ?>';
                    }
                },
            });
        });

         $('.hawbbexport #export_cargo_id').change(function(){
            $('.hawbbexport .awb_bl_no').val($(".hawbbexport #export_cargo_id option:selected").html());
        });
    });
    </script>

