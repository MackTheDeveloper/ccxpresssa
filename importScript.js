
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
             $( "#consignee_name" ).autocomplete({
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
                                            $('#consignee_address').val(data.company_address);
                                        }
                            });
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $("#consignee_name").val(ui.item.label);
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


         $( "#shipper_name" ).autocomplete({
                select: function (event, ui) {
                        event.preventDefault();
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $("#shipper_name").val(ui.item.label);
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


        $('#w1').on('click', '#w1btn', function(e){
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            e.preventDefault();
            $('#loading').show();
            var createExpenseForm = $("#w1");
            var formData = createExpenseForm.serialize();
            $( '#consignee_name-error' ).html( "" );
            $( '#arrival_date-error' ).html( "" );
            //$( '#cargo_id-error' ).html( "" );
            $( '#file_name-error' ).html( "" );
            $( '#hawb_hbl_no-error' ).html( "" );
            
            $.ajax({
                url:'<?php echo route("storehawb") ?>',
                type:'POST',
                data:formData,
                success:function(data) {
                    console.log(data);
                    if(data.errors) {
                        if(data.errors.consignee_name){
                            $( '#consignee_name-error' ).html( data.errors.consignee_name[0] );
                            }
                            if(data.errors.arrival_date){
                            $( '#arrival_date-error' ).html( data.errors.arrival_date[0] );
                            }
                            /*if(data.errors.cargo_id){
                            $( '#cargo_id-error' ).html( data.errors.cargo_id[0] );
                            }*/
                            if(data.errors.file_name){
                            $( '#file_name-error' ).html( data.errors.file_name[0] );
                            }
                            if(data.errors.hawb_hbl_no){
                            $( '#hawb_hbl_no-error' ).html( data.errors.hawb_hbl_no[0] );
                            }
                         $("html, body").animate({ scrollTop: 0 }, "slow");
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
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                        $("#w1")[0].reset();
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
    