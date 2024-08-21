                    <style type="text/css">
                        .ui-front {
                           z-index: 9999;
                        }
                    </style>

                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w2','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
                  <div class="col-md-12 hawbbimport">
                            <div class="col-md-6" style="display: none;"> 
                                    <div class="form-group {{ $errors->has('cargo_id') ? 'has-error' :'' }}">
                                        <?php echo Form::label('cargo_id', 'AWB / BL No.',['class'=>'control-label']); ?>
                                        <div class="col-md-8">
                                            <?php echo Form::select('cargo_id', $dataImportAwbNos,$model->cargo_id,['class'=>'form-control selectpicker fcargo_id invfieldtbl invfieldtblbillto','placeholder' => 'Select ...','data-live-search' => 'true']); ?>
                                            <span class="text-danger">
                                                <strong id="cargo_id-error"></strong>
                                            </span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="awb_bl_no" class="awb_bl_no" id="awb_bl_no" value="">
                            </div>
                        
                            <div class="col-md-8">
                                <div class="form-group {{ $errors->has('hawb_hbl_no') ? 'has-error' :'' }}">
                                    
                                    <?php echo Form::label('hawb_hbl_no', 'House AWB No.',['class'=>'control-label']); ?>
                                    
                                    <div class="col-md-4">
                                    <?php echo Form::text('hawb_hbl_no',$model->hawb_hbl_no,['class'=>'form-control fhawb_hbl_no','placeholder' => 'Enter House AWB No.']); ?>
                                     <span class="text-danger">
                                                <strong id="hawb_hbl_no-error1"></strong>
                                            </span>
                                    </div>
                                </div>
                        </div>
                    </div>
                        <input type="hidden" name="cargo_operation_type" value="1">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
                                <?php echo Form::label('consignee_name', 'Consignee Name :',['class'=>' control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::text('consignee_name',$model->consignee_name,['class'=>'form-control consignee_name_import','placeholder' => 'Enter Consignee Name']); ?>
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
                                <?php echo Form::label('consignee_address', 'Consignee Address :',['class'=>' control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::textarea('consignee_address',$model->consignee_address,['class'=>'form-control consignee_address','placeholder' => 'Enter Consignee Address','rows'=>2]); ?>
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
                                <?php echo Form::label('shipper_name', 'Shipper Name:',['class'=>'control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::text('shipper_name',$model->shipper_name,['class'=>'form-control shipper_name','placeholder' => 'Enter Shipper Name']); ?>
                                <span class="text-danger">
                                    <strong id="shipper_name_error_import_awb"></strong>
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
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('arrival_date') ? 'has-error' :'' }}">
                                <?php echo Form::label('arrival_date', 'Arrival Date :',['class'=>'control-label']); ?>
                                <div class="col-md-4">
                                <?php echo Form::text('arrival_date',date('d-m-Y'),['class'=>'form-control datepicker','placeholder' => 'Enter Date','id'=>'datepicker']); ?>
                                <span class="text-danger">
                                    <strong id="arrival_date-error1"></strong>
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
                                 <?php echo Form::label('', '',['class'=>'control-label']); ?>
                                <div class="col-md-8 consolidate_flag-md-6">
                                <?php 
                                if(!empty($model->id)) { 
                                echo Form::radio('flag_package_container', '1',$model->flag_package_container == '1' ? 'checked' : '',['class'=>'flagconsolpackagecontainer']); 
                                echo Form::label('', 'Package');
                                echo Form::radio('flag_package_container', '2',$model->flag_package_container == '2' ? 'checked' : '',['class'=>'flagconsolpackagecontainer']); 
                                echo Form::label('', 'Container');
                                }else
                                {
                                    echo Form::radio('flag_package_container', '1',true,['class'=>'flagconsolpackagecontainer']); 
                                    echo Form::label('', 'Package');
                                    echo Form::radio('flag_package_container', '2','',['class'=>'flagconsolpackagecontainer']); 
                                    echo Form::label('', 'Container');
                                }
                                ?>                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 packagediv">
                        <div class="row" style="margin-left: 0.1%;">
                            <div class="col-md-4 col-sm-4 form-group {{ $errors->has('pweight') ? 'has-error' :'' }}">
                                <?php echo Form::label('pweight', 'Weight :',['class'=>'control-label']); ?>
                                <div class="col-md-6 col-sm-6">
                                    <?php echo Form::text('modalCargoPackage[pweight]','0.00',['class'=>'form-control','placeholder' => 'Enter Weight','onkeypress'=>'return isNumber(event)']); ?>
                                     @if ($errors->has('pweight'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('pweight') }}</strong>
                                            </span>
                                     @endif
                                 </div>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <?php echo Form::select('measure_weight',Config::get('app.measureMass'),'',['class'=>'form-control']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 packagediv">
                        <div class="row" style="margin-left: 0.1%">
                            <div class="col-md-4 col-sm-4 form-group {{ $errors->has('pvolume') ? 'has-error' :'' }}">
                                <?php echo Form::label('pvolume', 'Volume :',['class'=>'control-label']); ?>
                                 <div class="col-md-6 col-sm-6">
                                <?php echo Form::text('modalCargoPackage[pvolume]','0.00',['class'=>'form-control','placeholder' => 'Enter Volume','onkeypress'=>'return isNumber(event)']); ?>
                                @if ($errors->has('pvolume'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('pvolume') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <?php echo Form::select('measure_volume',Config::get('app.measureDimension'),'',['class'=>'form-control']); ?>
                            
                            </div>
                        </div>
                    </div>
                         <div class="col-md-12 col-sm-12 packagediv">
                            <div class="row" style="margin-left: 0.1%">
                                <div class="col-md-4 col-sm-4 form-group {{ $errors->has('ppieces') ? 'has-error' :'' }}">
                                    <?php echo Form::label('ppieces', 'Pieces :',['class'=>'control-label']); ?>
                                    <div class="col-md-6 col-sm-6">
                                        <?php echo Form::text('modalCargoPackage[ppieces]','0',['class'=>'form-control','placeholder' => 'Enter Pieces','onkeypress'=>'return isNumber(event)']); ?>
                                        @if ($errors->has('ppieces'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('ppieces') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                  

                    <div class="col-md-12 containerdiv" style="display: none">
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('no_of_container') ? 'has-error' :'' }}">
                                <?php echo Form::label('no_of_container', 'No. of container :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('no_of_container',$model->no_of_container,['class'=>'form-control','placeholder' => 'Enter no. of container']); ?>
                                @if ($errors->has('no_of_container'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('no_of_container') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 sec-containersubcontainer">
                        <?php if(!empty($model->id)) 
                        { 
                            $dataContainerDetails = App\CargoContainers::where('cargo_id',$model->id)->get(); 
                            $plusminuscontainer = 0;
                            if(empty($countcontainerdetail))
                            { ?>
                                    <div id="addcontainer-0">
                                        <div class="col-md-12">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('container_number') ? 'has-error' :'' }}">
                                                
                                                <div class="col-md-12">
                                                <?php echo Form::text('modalCargoContainer[container_number][]',$modelCargoContainer->container_number,['class'=>'form-control','placeholder' => 'Enter Container Number']); ?>
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
                                foreach($dataContainerDetails as $k => $v)
                                { ?>
                                    <div id="addcontainer-<?php echo $plusminuscontainer; ?>">
                                        <div class="col-md-12">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group {{ $errors->has('container_number') ? 'has-error' :'' }}">
                                                
                                                <div class="col-md-12">
                                                <?php echo Form::text('modalCargoContainer[container_number][]',$v->container_number,['class'=>'form-control','placeholder' => 'Enter Container Number']); ?>
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
                                             <?php if($plusminuscontainer != 0) { ?>
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
                                        <?php echo Form::text('modalCargoContainer[container_number][]',$modelCargoContainer->container_number,['class'=>'form-control','placeholder' => 'Enter Container Number']); ?>
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
                                <div class="col-md-12">
                                <?php echo Form::textarea('information',$model->information,['class'=>'form-control','placeholder' => 'Enter Information','rows'=>4,'style'=>'border: 1px solid #ccd0d2;']); ?>
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
        $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
        $(document).ready(function() {
             $( ".consignee_name_import" ).autocomplete({
                select: function (event, ui) {
                         event.preventDefault();
                        //$(".consignee_name_import").val(ui.item.label);
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
                                asyns:false,
                                type:'POST',
                                data:{'clientId':clientId},
                                success:function(data) {
                                            $('.consignee_address').val(data.company_address);
                                        }
                            });
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $(".consignee_name_import").val(ui.item.label);
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


         $( ".shipper_name" ).autocomplete({
                select: function (event, ui) {
                        event.preventDefault();
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $(".shipper_name").val(ui.item.label);
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
                   source: <?php echo $datas; ?>,
                   minLength:1,  
         });


        <?php 
        if(!empty($model->id)) { 
        if($model->flag_package_container == 1)
        { ?>
                $('.containerdiv').hide();
                $('.packagediv').show();
        <?php }else { ?>
                $('.containerdiv').show();
                $('.packagediv').hide();
            <?php } } ?> 

        $('.flagconsolpackagecontainer').click(function(){
            if($(this).val() == 1)
            {
                $('.containerdiv').hide();
                $('.packagediv').show();
            }else
            {
                $('.containerdiv').show();
                $('.packagediv').hide();
            }
        }) 

        var countcontainerdetail = 0;
        $(document).on("click",".addcontainer",function (e) {
            countcontainerdetail = countcontainerdetail+1;
            if(countcontainerdetail == 0) { countcontainerdetail = 1; }
            e.preventDefault();
            var str  =  '<div id="addcontainer-'+countcontainerdetail+'"><div class="col-md-12"></div><div class="col-md-6"><div class="form-group "><div class="col-md-12"><input class="form-control" placeholder="Enter Container Number" name="modalCargoContainer[container_number][]" type="text">                                                        </div></div></div><div class="col-md-2"><a style="margin-right: 10px;" href="javascript:void(0)" class="btn btn-success btn-xs addcontainer">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removecontainer" id="'+countcontainerdetail+'">-</a></div></div>';
            $('.sec-containersubcontainer').append(str);
        });
        $(document).on("click",".removecontainer",function (e) {
            e.preventDefault();
            var id = $(this).attr('id');
            $("#addcontainer-"+id).remove();
        });

$('#w2btn').click(function(event){
    event.preventDefault();
    console.log('Hello');});

        $('#w2').on('click', '#w2btn', function(e){

            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            e.preventDefault();
            $('#loading').show();
            var createExpenseForm = $("#w2");
            var formData = createExpenseForm.serialize();
            $( '#consignee_name_error_hawb' ).html( "" );
            $( '#arrival_date-error1' ).html( "" );
            //$( '#cargo_id-error' ).html( "" );
            $( '#file_name-error1' ).html( "" );
            $( '#hawb_hbl_no-error1' ).html( "" );
            $('#shipper_name_error_import_awb').html('');
            $.ajax({
                url:'<?php echo route("storehawb") ?>',
                type:'POST',
                data:formData,
                success:function(data) {
                    console.log(data);
                    if(data.errors) {
                        if(data.errors.consignee_name){
                            $( '#consignee_name_error_hawb' ).html( data.errors.consignee_name[0] );
                            }
                            if(data.errors.arrival_date){
                            $( '#arrival_date-error1' ).html( data.errors.arrival_date[0] );
                            }
                            /*if(data.errors.cargo_id){
                            $( '#cargo_id-error' ).html( data.errors.cargo_id[0] );
                            }*/
                            if(data.errors.file_name){
                            $( '#file_name-error1' ).html( data.errors.file_name[0] );
                            }
                            if(data.errors.hawb_hbl_no){
                            $( '#hawb_hbl_no-error1' ).html( data.errors.hawb_hbl_no[0] );
                            } 
                            if(data.errors.shipper_name){
                                $('#shipper_name_error_import_awb').html(data.errors.shipper_name);
                            }
                         $("#modalAddNewItemsImport").animate({ scrollTop: 0 }, "slow");
                        $('#loading').hide();
                        }
                    if(data.success) {
                        $('#loading').hide();
                        Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'Import shipment has been added successfully.'
                            });
                        $("#modalAddNewItemsImport").modal("hide");
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                        $("#w2")[0].reset();
                        
                        var allfile = JSON.parse(data.dataImportHawbAll); 
                        console.log($('#hawb_hbl_no'));
                         $('#hawb_hbl_no').inputpicker({

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




                        //$('#w1 input#consignee_name').val('');
                        //window.location = '<?php //echo route("cargoimports") ?>';
                        }
                    },
                });
        });

        $('.hawbbimport #cargo_id').change(function(){
            
            $('.hawbbimport .awb_bl_no').val($(".hawbbimport #cargo_id option:selected").html());
        });
    })
    </script>

