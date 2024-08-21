
                    <?php
                    if($model->id)
                        $actionUrl = url('billingitem/update',$model->id);
                    else
                        $actionUrl = url('billingitem/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('item_code') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                            <?php echo Form::label('item_code', 'Item Code',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('item_code',$model->item_code,['class'=>'form-control fitem_code','placeholder' => 'Enter Item Code']); ?>
                        @if ($errors->has('item_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('item_code') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('billing_name') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                            <?php echo Form::label('billing_name', 'Billing Item Name',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('billing_name',$model->billing_name,['class'=>'form-control fbilling_name','placeholder' => 'Enter Billing Item Name']); ?>
                        @if ($errors->has('billing_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('billing_name') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="form-group {{ $errors->has('description') ? 'has-error' :'' }}">
                        <div class="col-md-4">
                            <?php echo Form::label('description', 'Description',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('description',$model->description,['class'=>'form-control fdescription','placeholder' => 'Enter Description']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('code') ? 'has-error' :'' }}">
                        <?php echo Form::label('code', 'Cost Code',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::select('code', $dataCost,$model->code,['class'=>'form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select ...','data-container'=>'#modalAddNewItems']); ?>
                        @if ($errors->has('code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('code') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('flag_prod_tax_type') ? 'has-error' :'' }}">
                        <?php echo Form::label('flag_prod_tax_type', 'TCA Applicable?',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::checkbox('flag_prod_tax_type','1',$model->flag_prod_tax_type, array('id'=>'flag_prod_tax_type')); ?> Yes
                        @if ($errors->has('flag_prod_tax_type'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('flag_prod_tax_type') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ratediv" style="display: none;pointer-events: none;opacity: 0.5">
                    <?php if($model->flag_prod_tax_amount == '0.00' || empty($model->id)) {  $model->flag_prod_tax_amount = '0.00'; } ?>
                    <div class="form-group {{ $errors->has('flag_prod_tax_amount') ? 'has-error' :'' }}">
                        <?php echo Form::label('flag_prod_tax_amount', 'Percentage',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::text('flag_prod_tax_amount','10.00',['class'=>'form-control','placeholder' => 'Enter Billing Item Name','onkeypress'=>'return isNumber(event)']); ?>
                        @if ($errors->has('flag_prod_tax_amount'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('flag_prod_tax_amount') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                    <?php echo Form::label('status', 'Status',['class'=>'col-md-4']); ?>
                    <div class="consolidate_flag-md-6 col-md-6">
                    <?php 
                       echo Form::radio('status', '1',$model->status == '1' || $model->status == '' ? 'checked' : '',['class'=>'flagconsol']); 
                                echo Form::label('', 'Active');
                                echo Form::radio('status', '0',$model->status == '0' ? 'checked' : '',['class'=>'flagconsol']); 
                                echo Form::label('', 'Inactive');   
                    ?>
                    </div>
                </div>
                </div>
            </div>

            

            <div class="form-group col-md-12 btm-sub">
                            
                                <button type="submit" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
                                            echo "Submit";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                            
                            
            </div>

                    {{ Form::close() }}


        

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
            $('.selectpicker').selectpicker();
            $('#flag_prod_tax_type').click(function(){
                var tVal = $(this).val();
                if($(this).prop('checked') == false)
                    $('.ratediv').hide();
                else
                    $('.ratediv').show();
            })

             $('#createforms').on('submit', function (event) {
                $('.fitem_code').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                });
            $('#createforms').validate({

                rules: {
                    "billing_name": {
                        required: true,
                        checkUniqueBillingName: true
                    },
                    "item_code": {
                        required: true,
                        checkUniqueItemCode: true
                    }
                },

                submitHandler: function (form) {
                $('#loading').show();
                        $.ajaxSetup({
                        headers:{
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                var createExpenseForm = $("#createforms");
                        var formData = createExpenseForm.serialize();
                        var urlz = '<?php echo url("billingitem/storenewitem"); ?>';
                        $.ajax({
                        url:urlz,
                        type:'POST',
                        data:formData,
                        success:function(data) {
                                $('#loading').hide();
                                //$('.selectpicker').selectpicker('refresh');
                                Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'Item has been added successfully.'
                                });

                                var urlzte = '<?php echo url("billingitem/getbillingitemsdropdowndataaftersubmit"); ?>';
                                $.ajax({
                                    async:false,
                                    url:urlzte,
                                    type:'POST',
                                    data:'',
                                    success:function(response) {
                                    $('#loading').hide();
                                    selectedVal = [];
                                    $('select.feesnamefld').each(function(k,v){
                                        idd = $(this).attr('id');
                                        result = idd.split('-');
                                        selectedVal[result[1]] = $('#feesname-'+result[1]+' option:selected').val();
                                    })
                                    
                                    $('select.feesnamefld').html(response);
                                    $('.selectpicker').selectpicker('refresh');

                                    $('select.feesnamefld').each(function(k,v){
                                         idd = $(this).attr('id');
                                         result = idd.split('-');
                                        $('#feesname-'+result[1]).val(selectedVal[result[1]])
                                    })
                                    $('.selectpicker').selectpicker('refresh');
                                    }
                                });    

                                $('#modalAddNewItems').modal('toggle');
                            },
                    });    
                }                   
            });   

            $.validator.addMethod("checkUniqueBillingName", 
                function(value, element) {
                    $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    });
                    var result = false;
                    var urlz = '<?php echo url("billingitem/checkunique"); ?>';
                    var flag = 'billingName';
                    <?php if($model->id) { ?>
                        var id = '<?php echo $model->id; ?>';
                    <?php } else { ?>
                        var id = '';
                    <?php } ?>
                    
                    $.ajax({
                        type:"POST",
                        async: false,
                        url: urlz,
                        data: {value: value,flag:flag,id:id},
                        success: function(data) {
                            result = (data == 0) ? true : false;
                        }
                    });
                    // return true if username is exist in database
                    return result; 
                }, 
                "This Name is already taken! Try another."
            ); 

            $.validator.addMethod("checkUniqueItemCode", 
                function(value, element) {
                    $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    });
                    var result = false;
                    var urlz = '<?php echo url("billingitem/checkunique"); ?>';
                    var flag = 'itemCode';
                    <?php if($model->id) { ?>
                        var id = '<?php echo $model->id; ?>';
                    <?php } else { ?>
                        var id = '';
                    <?php } ?>
                    
                    $.ajax({
                        type:"POST",
                        async: false,
                        url: urlz,
                        data: {value: value,flag:flag,id:id},
                        success: function(data) {
                            result = (data == 0) ? true : false;
                        }
                    });
                    // return true if username is exist in database
                    return result; 
                }, 
                "This Code is already taken! Try another."
            ); 
        });
</script>

