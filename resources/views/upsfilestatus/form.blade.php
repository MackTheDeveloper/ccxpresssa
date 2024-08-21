@extends('layouts.custom')

@section('title')
File Status
@stop


@section('breadcrumbs')
    @include('menus.ups-file-status')
@stop


@section('content')
	<section class="content-header">
		<?php
            if($model->id) { ?>
    	       <h1>Update File Progress Status</h1>
        <?php } 
            else {?>
                <h1>Add File Progress Status</h1>
        <?php }?>
    	
	</section>

	<section class="content">
    	<div class="box box-success">
        	<div class="box-body">
        		<?php
                    if($model->id)
                        $actionUrl = url('filestatus/update',$model->id);
                    else
                        $actionUrl = url('filestatus/store');    
                    ?>
        		{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
        		<div class="row">
        			<div class="col-md-5">
        				<div class="row" style="margin-left: 2%">
        					<div class="col-md-3">
        						<?php echo Form::label('after_or_before', 'Occurrence',['class'=>'control-label']); ?>
        					</div>
        					<div class="col-md-8">
        						<div class="col-md-8 billing_term-md-6">

			                        <?php
			                        if(empty($model->after_or_before)){ 
			                        	echo Form::radio('after_or_before', '1',true); 
			                       		echo Form::label('Before', '');
			                        	echo Form::radio('after_or_before', '2'); 
			                        	echo Form::label('After', '');
			                        	
			                    	} else {
			                    		echo Form::radio('after_or_before', '1',$model->after_or_before == '1' ? 'checked' : ''); 
			                       		echo Form::label('Before', '');
			                        	echo Form::radio('after_or_before', '2',$model->after_or_before == '2' ? 'checked' : ''); 
			                        	echo Form::label('After', '');





			                    		
			                    	}
			                        ?>                                    
		                    	</div>
        					</div>
        				</div>
        			</div>
        			<div class="col-md-5">
	        			<div class="row">
		        			<div class="col-md-3">
		        				<?php echo Form::label('status', 'File Status',['class'=>'control-label']); ?>
		        			</div>
		        			<div class="col-md-8">
		        				<?php echo Form::text('status',$model->status,['class'=>'form-control status','placeholder' => 'Enter File Status']); ?>
		        			</div>
	        			</div>
	        		</div>
        		</div>
        		
	        		<div class="row btm-sub" style="margin-top: 3%">
	                    <button type="submit" class="btn btn-success btn-success-form">
	     					 <?php
	                        	if(!$model->id)
	                            	echo "Submit";
	                        	else
	                            	echo "Update";
	                         ?>
	                    </button>
	                	<a class="btn btn-danger" href="{{url('filestatus/index')}}" title="">Cancel</a>
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
                
                $('.status').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                
            });	

            $('#createforms').validate({});
        });
    </script>
@stop