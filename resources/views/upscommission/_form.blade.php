@extends('layouts.custom')

@section('title')
Ups Commission
@stop


@section('breadcrumbs')
    @include('menus.ups-commission')
@stop


@section('content')
	<section class="content-header">
        <?php
            if($model->id) { ?>
    	       <h1>Update Ups Commission</h1>
        <?php } 
            else {?>
                <h1>Add Ups Commission</h1>
        <?php }?>
	</section>

	<section class="content">
    	<div class="box box-success">
        	<div class="box-body">
        		<?php
                    if($model->id)
                        $actionUrl = url('upscommission/update',$model->id);
                    else
                        $actionUrl = url('upscommission/store');    
                    ?>
        		{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
        		<div class="row" style="margin-left: 1%">
        			<div class="col-md-5">
        				<div class="row">
	        				<div class="col-md-4">
	        					<?php echo Form::label('file_type', 'File Type',['class'=>'control-label']); ?>
	        				</div>
		        			<div class="col-md-8">
		        				 <?php echo Form::select('file_type',Config::get('app.fileType') ,$model->file_type,['class'=>'filetype form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select Type Of File..','id'=>'filetype']); ?>
		        			</div>
		        		</div>
        			</div>
        		
	        		<div class="col-md-5">
	        			<div class="row" style="margin-left: 20%">
		        			<div class="col-md-4">
		        				<?php echo Form::label('biling_term', 'Billing Term',['class'=>'control-label']); ?>
		        			</div>
		        			<div class="col-md-8 billing_term-md-6">

		                        <?php
		                        if(empty($model->billing_term)){ 
		                        	echo Form::radio('billing_term', '1',true); 
		                       		echo Form::label('fc', 'F/C');
		                        	echo Form::radio('billing_term', '2'); 
		                        	echo Form::label('fd', 'F/D');
		                        	echo Form::radio('billing_term', '3'); 
		                        	echo Form::label('pp', 'P/P');
		                    	} else {
		                    		echo Form::radio('billing_term', '1',$model->billing_term == 1 ? 'checked' : ''); 
		                       		echo Form::label('fc', 'F/C');
		                        	echo Form::radio('billing_term', '2',$model->billing_term == 2 ? 'checked' : ''); 
		                        	echo Form::label('fd', 'F/D');
		                        	echo Form::radio('billing_term', '3',$model->billing_term == 3 ? 'checked' : ''); 
		                        	echo Form::label('pp', 'P/P');
		                    	}
		                        ?>                                    
		                    </div>
	                	</div>
	        		</div>
        		</div>
        		<div class="row" style="margin-left: 1%;margin-top: 2%">
	        		<div class="col-md-5">
	        			<div class="row">
		        			<div class="col-md-4">
		        				<?php echo Form::label('courier_type', 'Courier Type',['class'=>'control-label']); ?>
		        			</div>
		        			<div class="col-md-8">
		        				<?php echo Form::select('courier_type', Config::get('app.productType'),$model->courier_type,['class'=>'form-control selectpicker couriertype','data-live-search' => 'true','placeholder' => 'Select Courier Type ...']); ?>
		        			</div>
	        			</div>
        			</div>
        		
	        		<div class="col-md-5">
	        			<div class="row" style="margin-left: 20%">
		        			<div class="col-md-4">
                                <?php if(!empty($model->id)) { ?>
                                    <?php 
                                    $label = ($model->file_type == 'e' && $model->billing_term == 3) ? 'Commission (%)' : 'Commission ($)';
                                        echo Form::label('commission', $label,['class'=>'control-label','id'=>'commission_label']); ?>
                                <?php } else { ?>
                                <?php echo Form::label('commission', 'Commission ($)',['class'=>'control-label','id'=>'commission_label']); ?>
                                <?php } ?>
		        			</div>
		        			<div class="col-md-8">
		        				<?php echo Form::text('commission',$model->commission,['class'=>'form-control commission','placeholder' => 'Enter Commission In % or amount','id'=>'feight_commission']); ?>
		        			</div>
	        			</div>
	        		</div>
        		</div>
        		<div class="form-group row btm-sub" style="margin-top: 3%">
                    <button type="submit" class="btn btn-success btn-success-form">
     					 <?php
                        	if(!$model->id)
                            	echo "Submit";
                        	else
                            	echo "Update";
                         ?>
                    </button>
                	<a class="btn btn-danger" href="{{url('upscommissiondetails')}}" title="">Cancel</a>
            	</div>

        		{{ Form::close() }}
        	</div>
    	</div>
	</section>

@endsection

@section('page_level_js')
	<script type="text/javascript">
		 $('select').change(function(){
                    if ($(this).val()!="")
                    {
                        $(this).valid();
                    }
                });
		$(document).ready(function() {
             $('#createforms').on('submit', function (event) {
                
                $('.filetype').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.couriertype').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                });
                $('.commission').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                                number: true,
                            })
                });
            });	

            $('#createforms').validate({
				rules: {
                    "commission": {
                        required: true,
                        checkUniqueUpsCommission: true
                    },
                },
            	errorPlacement: function(error, element) {
                        if (element.attr("name") == "file_type" )
                        {
                        var pos = $('.filetype button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if (element.attr("name") == "courier_type" )
                        {
                        var pos = $('.couriertype button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else
                        {
                        error.insertAfter(element);
                        }
                    }

            });

			$.validator.addMethod("checkUniqueUpsCommission", 
                function(value, element) {
                    $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    });
                    var result = false;
                    var urlz = '<?php echo url("upscommission/checkuniqueupscommission"); ?>';
                    <?php if($model->id) { ?>
                        var id = '<?php echo $model->id; ?>';
                    <?php } else { ?>
                        var id = '';
                    <?php } ?>

					var fileType = $('#filetype').val();
					var billingTerm = $("input[name='billing_term']:checked").val();
					var courierType = $("#courier_type").val();
                    
                    $.ajax({
                        type:"POST",
                        async: false,
                        url: urlz,
                        data: {'fileType': fileType,'billingTerm':billingTerm,'courierType':courierType,'id':id},
                        success: function(data) {
                            result = (data == 0) ? true : false;
                        }
                    });
                    // return true if username is exist in database
                    return result; 
                }, 
                "Commission already added for this billing term."
            ); 

            $('input[type=radio][name=billing_term]').change(function() {
                if($('#filetype').val() == 'e'){
                    if($(this).val() == 3){
                        $('#commission_label').html('Commission (%)');
                    } else {
                        $('#commission_label').html('Commission ($)');
                    }

                    if($(this).val() == 2){
                        $('#feight_commission').attr('readonly','readonly');
                    } else {
                        $('#feight_commission').attr('readonly',false);
                    }
                } else {
                     $('#commission_label').html('Commission ($)');
                }
            });
         });
	</script>
@stop