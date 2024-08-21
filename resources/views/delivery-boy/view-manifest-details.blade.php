@extends('layouts.custom')
@section('title')
Manifest Details
@stop

@section('breadcrumbs')
@include('menus.delivery-boy')
@stop

@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 0px;font-weight: 600;">Manifest Details - <?php echo $model->name; ?><a style="line-height:15px;float: right;" class="btn round orange btn-warning" href="{{route('cashcollectiondetailsdeliveryboy',[$model->id])}}">Cash Collection</a><a style="line-height:15px;float: right;margin-right: 10px;" class="btn round orange btn-warning" href="{{route('viewdetailsdeliveryboy',[$model->id])}}">View Details</a></h1>
</section>
<section class="content editupscontainer">
	<div class="box box-success">
		<div class="box-body">
			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger flash-danger">
				{{ Session::get('flash_message_error') }}
			</div>
			@endif
			@if(Session::has('flash_message'))
			<div class="alert alert-success flash-success">
				{{ Session::get('flash_message') }}
			</div>
			@endif

			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
			{{ Form::open(array('url' => '','class'=>'form-horizontal form-w0','id'=>'form-w0','autocomplete'=>'off')) }}
			{{ csrf_field() }}
			<div class="row" style="margin-bottom:20px">
				<div style="float: left;width: auto;padding-left: 15px;margin-right: 15px;">
					<div class="" style="float: left;width: auto;margin-right: 10px;">
						<label style="margin-top: 5px;">Courier Type</label>
					</div>
					<div class="" style="float: left;width: 130px;margin-right: 10px;">
						<?php echo Form::select('courier_type', ['UPS' => 'UPS', 'Aeropost' => 'Aeropost', 'CCPack' => 'CCPack'], 'UPS', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'courier_type']); ?>
					</div>
					<div class="billingTermDiv" style="float: left;width: 100px;">
						<?php echo Form::select('billing_term', ['P/P' => 'P/P', 'F/C' => 'F/C'], '', ['class' => 'form-control selectpicker', 'placeholder' => 'All', 'data-live-search' => 'true', 'id' => 'billing_term']); ?>
					</div>
				</div>
				<div style="float: left;width: auto;margin-right: 15px;">
					<div class="" style="float: left;width: auto;margin-right: 10px;">
						<label style="margin-top: 5px;">File Status</label>
					</div>
					<div class="" style="float: left;width: 150px;">
						<?php echo Form::select('file_status', Config::get('app.ups_new_scan_status'), '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'All', 'id' => 'file_status']); ?>
					</div>
				</div>
				<div style="float: left;width: auto;">
					<div class="" style="float: left;width: auto;margin-right: 10px;">
						<label style="margin-top: 5px;">Date</label>
					</div>
					<div class="" style="float: left;width: auto;margin-right: 10px;">
						<div class=" from-date-filter-div filterout" style="float: left;width: 100px;margin-right: 10px;">
							<input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter" value="<?php echo date('d-m-Y'); ?>">
						</div>
						<div class="to-date-filter-div filterout" style="float: left;width: 100px;">
							<input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter" value="<?php echo date('d-m-Y'); ?>">
						</div>
					</div>
					<div class="" style="float: left;">
						<button type="submit" id="clsSubmit" class="btn btn-success">Submit</button>
					</div>
					<div class="" style="float: left;margin-left:10px">
						<button type="submit" id="clsPrint" class="btn btn-success">Print</button>
					</div>
					<div class="" style="float: left;margin-left:10px">
						<button type="submit" id="clsPrintAll" class="btn btn-warning">Print All</button>
					</div>
				</div>
			</div>
			{{ Form::close() }}

			<div class="db-ups-manifest-details-files">
				<?php echo View::make('delivery-boy.db-ups-manifest-details-files', array('upsFileAssignedToDeliveryBoy' => $upsFileAssignedToDeliveryBoy, 'deliveryBoyId' => $model->id))->render(); ?>
			</div>
			<div class="db-aeropost-manifest-details-files">
			</div>
			<div class="db-ccpack-manifest-details-files">
			</div>



		</div>
	</div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
	$(document).ready(function() {
		$('.datepicker').datepicker({
			format: 'dd-mm-yyyy',
			todayHighlight: true,
			autoclose: true
		});
		$('#courier_type').change(function() {
			if ($(this).val() == 'UPS')
				$('.billingTermDiv').show();
			else
				$('.billingTermDiv').hide();
		})
		$('#form-w0').on('submit', function(event) {
			/* $('.from-date-filter').each(function() {
				$(this).rules("add", {
					required: true,
				})
			});
			$('.to-date-filter').each(function() {
				$(this).rules("add", {
					required: true,
				})
			}); */
		});
		$('#form-w0').validate({
			submitHandler: function(form) {
				var fromDate = $('.from-date-filter').val();
				var toDate = $('.to-date-filter').val();
				var fileStatus = $('#file_status').val();
				var courierType = $('#courier_type').val();
				var billingTerm = $('#billing_term').val();

				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});

				var submitButtonName = $(this.submitButton).attr("id");

				var urlztnn = '<?php echo url("deliveryboy/filter-files-manifest-details"); ?>';
				$.ajax({
					url: urlztnn,
					async: false,
					type: 'POST',
					data: {
						'id': '<?php echo $model->id ?>',
						'fromDate': fromDate,
						'toDate': toDate,
						'fileStatus': fileStatus,
						'courierType': courierType,
						'billingTerm': billingTerm,
						'submitButtonName': submitButtonName
					},
					success: function(data) {
						if (submitButtonName == 'clsSubmit') {
							if (courierType == 'UPS') {
								$('.db-ups-manifest-details-files').show().html(data);
								$('.db-aeropost-manifest-details-files').html('');
								$('.db-ccpack-manifest-details-files').html('');
							} else if (courierType == 'Aeropost') {
								$('.db-ups-manifest-details-files').html('');
								$('.db-ccpack-manifest-details-files').html('');
								$('.db-aeropost-manifest-details-files').show().html(data);
							} else if (courierType == 'CCPack') {
								$('.db-ups-manifest-details-files').html('');
								$('.db-aeropost-manifest-details-files').html('');
								$('.db-ccpack-manifest-details-files').show().html(data);
							}
						} else {
							window.open(data, '_blank');
						}
						$('#loading').hide();
					}
				});
			},
		});
	});
</script>
@stop