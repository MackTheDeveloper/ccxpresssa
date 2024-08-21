@extends('layouts.custom')
@section('title')
Basic Detail
@stop
@section('sidebar')
<aside class="main-sidebar">
	<ul class="sidemenu nav navbar-nav side-nav">
		<?php
		$checkPermissionAddCargo = App\User::checkPermission(['add_cargo'],'',auth()->user()->id);
		$checkPermissionUpdateCargo = App\User::checkPermission(['update_cargo'],'',auth()->user()->id);
		$checkPermissionDeleteCargo = App\User::checkPermission(['delete_cargo'],'',auth()->user()->id);
		$checkPermissionIndexCargo = App\User::checkPermission(['listing_cargo'],'',auth()->user()->id);
		$checkPermissionExpenseCargo = App\User::checkPermission(['add_expense_cargo'],'',auth()->user()->id);
		?>
		
		<li class="widemenu">
			<a href="{{ route('cargoall') }}">Shipment Listing</a>
		</li>			
		<li class="widemenu">
			<a href="#div_basicdetails">Basic</a>
		</li>
		<li class="widemenu">
			<a href="#div_expenses">Expense/Cost</a>
		</li>
		
		<li class="widemenu">
			<a href="#div_invoice">Invoice</a>
		</li>
		<li class="widemenu">
			<a href="#div_reports">Report</a>
		</li>
		
		
	</ul>
</aside>
@stop
@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 50px;font-weight: 600;"><?php echo 'Courier'.' ( Import - '.$model->awb_number.' ) '; ?></h1>
</section>
<section class="content editupscontainer">
	<div class="box box-success">
		<div class="box-body">
			
			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger flash-danger">
				{{ Session::get('flash_message_error') }}
			</div>
			@endif
			
			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
			
			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Basic Details</div>
			
			<div class="detail-container">
				
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">File Name : </span>
					<span class="viewblk2"><?php echo !empty($model->file_name) ? $model->file_name : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB / BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->awb_number) ? $model->awb_number : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_name) ? $model->consignee_name : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Expediteur / Shipper : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper_name) ? $model->shipper_name : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Address : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : $model->consignee_address; ?></span>
				</div>
			</div>

			<?php
			$actionUrl = url('courier/assignonconsolidationbyagentcourier');
	        ?>
			{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'createforms','autocomplete'=>'off')) }}
	        {{ csrf_field() }}
	        <input type="hidden" name="id" value="<?php echo $model->id; ?>"> 
	        <div class="col-md-12">
	                <div class="col-md-3">
	                    <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
	                        <div class="col-md-12">
	                        <?php echo Form::label('billing_party', 'Billing Party',['class'=>'control-label']); ?>
	                        </div>
	                        <div class="col-md-12">
	                        <?php echo Form::select('billing_party', $billingParty,$model->billing_party,['class'=>'form-control selectpicker', 'data-live-search' => 'true','placeholder' => 'Select ...']); ?>
	                      	</div>
	                    </div>
	                </div>
	                <div class="col-md-2">
	                     
			                    <div class="form-group {{ $errors->has('cash_credit') ? 'has-error' :'' }}">
			                        <div class="col-md-12">
			                        <?php echo Form::label('cash_credit', 'Cash/Credit',['class'=>'control-label']); ?>
			                        </div>
			                        <div class="col-md-12 consolidate_flag-md-6">
			                        <?php 
			                            echo Form::radio('cash_credit', 'Cash',$model->cash_credit == 'Cash' ? 'checked' : '',['class'=>'cash_credit']); 
			                            echo Form::label('', 'Cash');
			                            echo Form::radio('cash_credit', 'Credit',$model->cash_credit == 'Credit' ? 'checked' : '',['class'=>'cash_credit']); 
			                            echo Form::label('', 'Credit');
			                           ?>  
			                        </div>
			                    </div>
			        </div>
			        <div class="form-group col-md-2 btm-sub">
	                	<div class="col-md-12">
	                		<?php echo Form::label('', '',['class'=>'control-label']); ?>
	                	</div>
	                	<div class="col-md-12">
                            <button type="submit" class="btn btn-success" style="width: 50%">Save</button>
                            <a class="btn btn-danger" href="{{url('home')}}" title="">Cancel</a>
                        </div>
                    </div>
	               
	        </div>

	        {{ Form::close() }}


			


	</div>
		
		
		
		
		
	</div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
		$(document).ready(function() {

		$.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });	

		$('#createforms').on('submit', function (event) {
			event.preventDefault();

			$('#loading').show();
			var form = $("#createforms");
			var formData = form.serialize();
	        var urlz = '<?php echo url("courier/assignonconsolidationbyagentcourier"); ?>';
	        $.ajax({
	        url:urlz,
	        async:false,
	        type:'POST',
	        data:formData,
	        success:function(data) {
	                $('#loading').hide();
	                $('.selectpicker').selectpicker('refresh');
	                Lobibox.notify('info', {
	                size: 'mini',
	                delay: 2000,
	                rounded: true,
	                delayIndicator: false,
	                msg: 'Data has been submitted successfully.'
	                });
	            },
	        });
    	});
		
	})
</script>
@stop
<style type="text/css">
	.navbar-static-top {position: fixed !important;width: 100%}
	.main-sidebar{ position: fixed !important; }
	.client-side .dropdown-toggle { height: 36px;  }
</style>