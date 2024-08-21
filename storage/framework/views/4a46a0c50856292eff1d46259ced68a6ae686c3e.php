                    <?php echo e(Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w3','autocomplete'=>'off'))); ?>

                    <?php echo e(csrf_field()); ?>

<?php 
if(!empty($model->id))
{
    $countaddproddetail = App\CargoProductDetails::where('cargo_id',$model->id)->count();
}else{
    $countaddproddetail = 0;
}
?> 


                    <input type="hidden" name="cargo_operation_type" value="3">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group <?php echo e($errors->has('opening_date') ? 'has-error' :''); ?>">
                                <?php echo Form::label('opening_date', 'Opening Date :',['class'=>'control-label']); ?>
                                <div class="col-md-4">
                                <?php echo Form::text('opening_date',date('d-m-Y'),['class'=>'form-control datepicker','placeholder' => 'Enter Date','id'=>'openingDate']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group <?php echo e($errors->has('rental') ? 'has-error' :''); ?>">
                                <?php echo Form::label('rental', 'Rental :',['class'=>'control-label']); ?>
                                <div class="col-md-4">
                                <?php echo Form::radio('rental','1',false,['id'=>'rental']); ?> Rental
                                <?php echo Form::radio('rental','0',true,['id'=>'non-rental']); ?> Non-rental
                               
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-md-12" id="rentDetail" style="display: none;margin-left: 0px !important;padding-left: 0 !important;">
                        <div class="col-md-3" style="display: none;">
                            <div class="form-group">
                                <?php echo Form::label('rental_starting_date', 'From Date :',['class'=>'control-label']); ?>
                                <div class="col-md-4">
                                <?php echo Form::text('rental_starting_date','',['class'=>'form-control datepicker','placeholder' => 'From Date']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="control-label">Contract months:</label>
                                </div>
                                <div class="col-md-6">
                                    <?php echo Form::select('contract_months',Config::get('app.months'),'',['class'=>'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto','data-live-search' => 'true','id'=>'contract_month']); ?>
                                   
                                </div>
                            </div>
                        </div>
                         <div class="col-md-3" style="">
                            <input type="hidden" name="rental_hidden_ending_date" value="" id="hidden_ending_date">
                            <div class="form-group">
                                <?php echo Form::label('rental_ending_date', 'Ending Date :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('rental_ending_date','',['class'=>'form-control datepicker','placeholder' => 'To Date','id'=>'ending_date','readonly'=>'true']); ?>
                                 </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?php echo Form::label('rental_cost', 'Charge :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('rental_cost','',['class'=>'form-control','placeholder' => 'Enter Charge']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3">
                                        
                                <?php echo Form::label('rental_paid_status', 'Status :',['class'=>'control-label']); ?>
                                    </div>
                                <div class="col-md-9" style="padding-top: 2.3%" >
                                    <?php echo Form::radio('rental_paid_status','p',false,['id'=>'paid']); ?> <span style="margin-right: 2.5%">Paid</span>
                                    <?php echo Form::radio('rental_paid_status','up',true,['id'=>'unpaid']); ?> Pending
                               </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group <?php echo e($errors->has('consignee_name') ? 'has-error' :''); ?>">
                                <?php echo Form::label('consignee_name', 'Client :',['class'=>'control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::text('consignee_name','',['class'=>'form-control consignee_name_locale','placeholder' => 'Enter client Name','autocomplete'=>'off','id'=>'consignee_name_locale']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                                <div class="form-group <?php echo e($errors->has('billing_party') ? 'has-error' :''); ?>">
                                    
                                    <?php echo Form::label('billing_party', 'Billing Party:',['class'=>'control-label']); ?>
                                    
                                    <div class="col-md-8">
                                    <?php echo Form::select('billing_party', $billingParty,$model->billing_party,['class'=>'invfieldtbl invfieldtblbillto hbilling_party form-control selectpicker', 'data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                                    </div>
                                     <div class="col-md-12 balance-div" style="display: none;text-align: center;">
                                        <span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group <?php echo e($errors->has('agent_id') ? 'has-error' :''); ?>">
                                <?php echo Form::label('agent_id', 'Agent :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::select('agent_id', $agents,$model->agent_id,['class'=>'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto','data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group <?php echo e($errors->has('consignee_address') ? 'has-error' :''); ?>">
                                <?php echo Form::label('consignee_address', 'Address :',['class'=>'control-label']); ?>
                                <div class="col-md-8">
                                <?php echo Form::textarea('consignee_address',$model->consignee_address,['class'=>'form-control consignee_address_locale','placeholder' => 'Enter Address','rows'=>2]); ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="col-md-6">
                                <div class="form-group <?php echo e($errors->has('awb_bl_no') ? 'has-error' :''); ?>">
                                    <?php echo Form::label('awb_bl_no', 'AWB/BL No :',['class'=>'control-label']); ?>
                                    <div class="col-md-4">
                                    <?php echo Form::text('locale_awb_bl_no',$model->awb_bl_no,['class'=>'form-control','placeholder' => 'Enter AWB/BL No']); ?>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="col-md-12" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group <?php echo e($errors->has('custom_file_number') ? 'has-error' :''); ?>">
                                <?php echo Form::label('custom_file_number', 'Custom File Number :',['class'=>'control-label']); ?>
                                <div class="col-md-6">
                                <?php echo Form::text('custom_file_number',$model->custom_file_number,['class'=>'form-control','placeholder' => 'Enter Custom File Number']); ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    <h4 class="formdeviderh4">EXPLICATIONS / INFORMATIONS</h4>

                    <div class="form-group <?php echo e($errors->has('information') ? 'has-error' :''); ?>">
                                <div class="col-md-9">
                                <?php echo Form::textarea('information',$model->information,['class'=>'form-control','placeholder' => 'Enter Information','rows'=>4,'style'=>'border: 1px solid #ccd0d2;']); ?>
                                </div>
                     
                    </div>

                  
                    <div class="col-md-12 btm-sub">
                        <input type="hidden" name="flagBtn" class="flagBtn" id="flagBtn" value="">
                                <button type="submit" id="w3btn" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
                                            echo "Save";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                                <button type="submit" id="buttonSavePrint" class="btn btn-success btn-prime white btn-flat">Save & Print</button>
                                <?php 
                                    $dept = auth()->user()->department;

                                    if($dept == '11') // Cashier
                                    {
                                        $listingCargoUrl = 'cashiercargoall';
                                    }else
                                    {
                                        $listingCargoUrl = 'cargoall';
                                    }
                                ?>

                                <a class="btn btn-danger" href="<?php echo e(url($listingCargoUrl)); ?>" title="">Cancel</a>
                     </div>   

                    <?php echo e(Form::close()); ?>


<?php 
$datas = App\Clients::getClientsAutocomplete();
?>
    <script type="text/javascript">
    $(document).ready(function() {


        //Get Ending as per selected months
        var months = $('#contract_month').val();
        var d = $('#openingDate').val();
             $.ajax({
                    url:"<?php echo e(url('local/getdate')); ?>",
                    type:'GET',
                    data:{date:d,months:months,flage:'bm'},
                    success:function(data) {
                        console.log(data);
                        $('#ending_date').val(data);
                    }
                });



        $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
        var countaddproddetail = 0;
        if('<?php echo $countaddproddetail; ?>' != 0)
        {
            countaddproddetail  =  <?php echo $countaddproddetail-1;?>;
        }
        $(document).on("click","#w3 .addcargoproductdetail",function (e) {
            countaddproddetail = countaddproddetail+1;
            if(countaddproddetail == 0) { countaddproddetail = 1; }
            e.preventDefault();
        var str  =  "<tr id='tr-"+countaddproddetail+"'><td><input class='form-control datepicker' placeholder = 'Enter Date' name='prodDetail[prod_date][]' type='text'></td><td><textarea class='form-control' placeholder='Enter Description' rows='1' name='prodDetail[prod_description][]' cols='50'></textarea></td><td><input class='form-control' placeholder='Enter Expense' name='prodDetail[pro_expense][]' type='text'></td><td><input class='form-control' placeholder='Enter GDES' name='prodDetail[to_bill_gdes][]' type='text'></td><td><input class='form-control' placeholder='Enter USD' name='prodDetail[to_bill_usd][]' type='text'></td><td><input class='form-control' placeholder='Enter Credit' name='prodDetail[credit_gdes_usd][]' type='text'></td><td><a id='"+countaddproddetail+"' href='javascript:void(0)' class='btn btn-success btn-xs addcargoproductdetail'>+</a></td><td><a style='' href='javascript:void(0)' class='btn btn-danger btn-xs removecargoproductdetail' id='"+countaddproddetail+"'>-</a></td></tr>";
            $('#w3 .tblproductdesc tbody').append(str);
            $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
        });
        $(document).on("click","#w3 .removecargoproductdetail",function (e) {
            e.preventDefault();
            var id = $(this).attr('id');
            $(".tblproductdesc tbody #tr-"+id).remove();
        });


        $('#w3').on('submit', function (event) {
        });
        $('#w3').validate({
            rules: {
                "consignee_name_locale": {
                    required: '#rental:checked'
                },
                "billing_party": {
                    required: '#rental:checked'
                },
                "rental_cost": {
                    required: '#rental:checked'
                },
                "locale_awb_bl_no": {
                    checkAwbNumber: true
                }
            },
            submitHandler : function(form) {
                $('#loading').show();
                var submitButtonName =  $(this.submitButton).attr("id");
                if($(this.submitButton).attr("id") == 'buttonSavePrint')
                    $('.flagBtn').val('saveprint');
                else
                    $('.flagBtn').val('');
                var createExpenseForm = $("#w3");
                var formData = createExpenseForm.serialize();

                $.ajax({
                        url:'<?php echo route("storecargo") ?>',
                        type:'POST',
                        data:formData,
                        success:function(data) {
                                $('#loading').hide();
                                Lobibox.notify('info', {
                                            size: 'mini',
                                            delay: 2000,
                                            rounded: true,
                                            delayIndicator: false,
                                            msg: 'Record has been added successfully.'
                                        });
                                $("html, body").animate({ scrollTop: 0 }, "slow");
                                $("#w3")[0].reset();
                                $('.selectpicker').selectpicker('refresh');
                                $('#rentDetail').css('display','none');

                                if(submitButtonName == 'buttonSavePrint')
                                {
                                   window.open(data, '_blank');
                                }
                            },
                });
            },
             errorPlacement: function(error, element) {
                    if (element.attr("name") == "billing_party" )
                    {
                        var pos = $('.hbilling_party button.dropdown-toggle');
                        error.insertAfter(pos);
                    }
                    else
                    {
                        error.insertAfter(element);
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
                    var urlz = '<?php echo url("cargo/checkuniqueawbnumber"); ?>';
                    var flag = '';
                    var idz = '';
                    $.ajax({
                        type:"POST",
                        async: false,
                        url: urlz,
                        data: {number: value,flag:flag,idz:idz},
                        success: function(data) {
                            result = (data == 0) ? true : false;
                        }
                    });
                    // return true if username is exist in database
                    return result; 
                }, 
                "This Awb Number is already taken! Try another."
            );


        $( ".consignee_name_locale" ).autocomplete({
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
                                            $('.consignee_address_locale').val(data.company_address);
                                        }
                            });
                    },
                    focus: function (event, ui) {
                    $('#loading').show();
                    event.preventDefault();
                    $(".consignee_name_locale").val(ui.item.label);
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



        $('#rental').click(function(){
            $.ajax({
                    url:"<?php echo e(url('local/getdate')); ?>",
                    type:'GET',
                    data:{date:d,months:months,flage:'bm'},
                    success:function(data) {
                        console.log(data);
                        $('#ending_date').val(data);
                    }
                });
            $('#rentDetail').slideDown('slow');
        });
        $('#non-rental').click(function(){
            $("#rentDetail input").each(function() {
                $(this).val("");
            });
            $('#rentDetail').slideUp('slow');
        });

        $('#contract_month').on('change',function(){
             var months = $(this).val();
            // console.log($('#openingDate').val());
             var d = $('#openingDate').val();
             //var date = d.getFullYear()+'-'+d.getMonth()+'-'+d.getDay();
            //console.log(date);
            // var d = new Date(d);
            // //d.getDay();

            // // console.log(d);
            // var newdate = new Date(d.getFullYear(),d.getMonth(),d.getDay());
            // var newdate = new Date(newdate.getMonth() + months);
            // console.log(newdate);
            $.ajax({
                    url:"<?php echo e(url('local/getdate')); ?>",
                    type:'GET',
                    data:{date:d,months:months,flage:'bm'},
                    success:function(data) {
                        console.log(data);
                        $('#hidden_ending_date').val(data);
                        $('#ending_date').val(data);
                    }
                });

        });

        $('#openingDate').on('change',function(){
            var date = $(this).val();
            var months = $('#contract_month').val();
             $.ajax({
                    url:"<?php echo e(url('local/getdate')); ?>",
                    type:'GET',
                    data:{date:date,months:months,flage:'bd'},
                    success:function(data) {
                        console.log(data);
                        $('#hidden_ending_date').val(data);
                        $('#ending_date').val(data);
                    }
                });
         });
        
    });
    </script>

