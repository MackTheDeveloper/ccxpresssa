@extends('layouts.custom')

@section('title')
Add Aerpost Commission
@stop


@section('breadcrumbs')
    @include('menus.ups-commission')
@stop


@section('content')
	<section class="content-header">
        <?php
            if($model->id) { ?>
    	       <h1>Update Aerpost Commission</h1>
        <?php } 
            else {?>
                <h1>Add Aerpost Commission</h1>
        <?php }?>
	</section>

	<section class="content">
    	<div class="box box-success">
        	<div class="box-body">
        		<?php
                    if($model->id)
                        $actionUrl = url('commission/updateaeropostcommission',$model->id);
                    else
                        $actionUrl = url('commission/storeaeropostcommission');    
                    ?>
        		{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
        		<div class="row" style="">
        			
					<div class="col-md-5">
	        			<div class="row" style="">
		        			<div class="col-md-4">
		        				<?php echo Form::label('commission', 'Commission (%)',['class'=>'control-label','id'=>'commission_label']); ?>
		        			</div>
		        			<div class="col-md-8">
		        				<?php echo Form::text('commission',$model->commission,['class'=>'form-control commission','id'=>'feight_commission']); ?>
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
                	<a class="btn btn-danger" href="{{url('commission/aeropostcommission')}}" title="">Cancel</a>
            	</div>

        		{{ Form::close() }}
        	</div>
    	</div>
	</section>

@endsection

@section('page_level_js')
	<script type="text/javascript">
		$(document).ready(function() {
             $('#createforms').on('submit', function (event) {
                
                
                $('.commission').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                                number: true,
								min: 1,
                            })
                });
            });	

            $('#createforms').validate({
                messages: {
					commission: 
                        {
                        min : "Value must be greater than 0",
                        }
                    }
                });

            
         });
	</script>
@stop