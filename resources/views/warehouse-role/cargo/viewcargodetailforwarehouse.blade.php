@extends('layouts.custom')
@section('title')
Basic Detail
@stop
@section('breadcrumbs')
@include('menus.warehouse-cargo-files')
@stop
@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;font-weight: 600;"><?php echo ($model->cargo_operation_type == 1 ? 'Import' : ($model->cargo_operation_type == 2 ? 'Export' : 'Locale')) . ' ( ' . $model->file_number . ' ) '; ?>
		<?php if ($model->file_close == 1) { ?>
			<div style="color:red;float:right">CLOSED</div>
		<?php } else if ($model->deleted == 1) { ?>
			<div style="color:red;float:right">Cancelled</div>
		<?php } else { ?>
			<div style="float: right;margin-right: 15px;color: green;">File Status : <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($model->cargo_master_scan_status) ? $model->cargo_master_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($model->cargo_master_scan_status) ? $model->cargo_master_scan_status : '-'] : '-';  ?></div>
		<?php } ?>
	</h1>
</section>
<section class="content editupscontainer">
	<div class="box box-success">
		<div class="box-body">

			@if(Session::has('flash_message'))
			<div class="alert alert-success flash-success">
				{{ Session::get('flash_message') }}
			</div>
			@endif
			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger flash-danger">
				{{ Session::get('flash_message_error') }}
			</div>
			@endif

			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>

			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Master File Details</div>

			<div class="detail-container basicDetaiCls">
				<?php if ($id == 1) { ?>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">No. Dossier/ File No. : </span>
						<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">AWB/BL No. : </span>
						<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Consignataire / Consignee : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-' ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Expediteur / Shipper : </span>
						<span class="viewblk2"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></span>
					</div>


					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Address : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : $model->consignee_address; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Opening Date : </span>
						<span class="viewblk2"><?php echo date('d-m-Y', strtotime($model->opening_date)); ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Explications : </span>
						<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Arrival Date : </span>
						<span class="viewblk2"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-' ?></span>
					</div>

					<?php $data = app('App\CargoPackages')::getData($model->id); ?>
					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Weight : </span>
						<span class="viewblk2"><?php echo !empty($data->pweight) ? $data->pweight : '-'; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Volume : </span>
						<span class="viewblk2"><?php echo !empty($data->pvolume) ? $data->pvolume : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Number of Pieces : </span>
						<span class="viewblk2"><?php echo !empty($data->ppieces) ? (int) $data->ppieces : '-'; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;clear:both">
						<span class="viewblk1"><?php echo $model->flag_package_container == 1 ? 'Package' : 'Container'; ?></span>
						<span class="viewblk2"><?php if ($model->flag_package_container == 1) { ?>
								<?php $data = app('App\CargoPackages')::getData($model->id); ?>
								<div style="width: 100%;float: left;">
									Weight : <?php if (!empty($data)) { ?> <?php echo !empty($data->pweight) ? $data->pweight : '-'; ?>
										@if($data->measure_weight == 'k')
										{{!empty($data->pweight) ? 'Kg' : ''}}
										@else
										{{'Pound'}}
										@endif
									<?php } else {
																				echo "";
																			} ?>
								</div>
								<div style="width: 100%;float: left;">
									Volume : <?php if (!empty($data)) { ?><?php echo !empty($data->pvolume) ? $data->pvolume : '-'; ?>
									@if($data->measure_volume == 'm')
									{{!empty($data->pvolume) ? 'Cubic meter' : ''}}
									@else
									{{'Cubic feet'}}
									@endif
								<?php } else {
																				echo "";
																			} ?>
								</div>
								<div style="width: 100%;float: left;">
									Pieces : <?php echo !empty($data->ppieces) ? (int) $data->ppieces : '-'; ?>
								</div>

							<?php } else { ?>
								<div style="width: 100%;float: left;">
									<div style="float: left;">No. of Container:&nbsp</div>
									<div style="float: left;"><?php echo (isset($model->no_of_container)) ? $model->no_of_container : '-'; ?></div>
								</div>
								<div style="width: 100%;float: left;">
									<div style="float: left;">Container No:&nbsp</div>
									<div style="float: left;"><?php $data = app('App\CargoContainers')::getData($model->id);
																						echo !empty($data) ? $data->containerNumbers : "-"; ?>
									</div>
								</div>
							<?php } ?>
						</span>
					</div>


				<?php } elseif ($id == 2) { ?>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">No. Dossier/ File No. : </span>
						<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">AWB/BL No. : </span>
						<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Expediteur / Shipper : </span>
						<span class="viewblk2"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Address : </span>
						<span class="viewblk2"><?php echo !empty($model->shipper_address) ? $model->shipper_address : '-' ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Consignee Address : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : $model->consignee_address; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Opening Date : </span>
						<span class="viewblk2"><?php echo date('d-m-Y', strtotime($model->opening_date)); ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Explications : </span>
						<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Arrival Date : </span>
						<span class="viewblk2"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-' ?></span>
					</div>

					<?php $data = app('App\CargoPackages')::getData($model->id); ?>
					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Weight : </span>
						<span class="viewblk2"><?php echo !empty($data->pweight) ? $data->pweight : '-'; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Volume : </span>
						<span class="viewblk2"><?php echo !empty($data->pvolume) ? $data->pvolume : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Number of Pieces : </span>
						<span class="viewblk2"><?php echo !empty($data->ppieces) ? (int) $data->ppieces : '-'; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Explications : </span>
						<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
					</div>
				<?php } else { ?>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">No. Dossier/ File No. : </span>
						<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">AWB/BL No. : </span>
						<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Opening Date : </span>
						<span class="viewblk2"><?php echo date('d-m-Y', strtotime($model->opening_date)); ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Client : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-' ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Address : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Explications : </span>
						<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Arrival Date : </span>
						<span class="viewblk2"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-' ?></span>
					</div>

				<?php } ?>

			</div>

			<?php if ($model->file_close != 1 && $model->deleted != 1) { ?>
				<?php
				$actionUrl = url('cargo/assigncargomasterfilestatusbyadmin');
				?>
				{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'createforms','autocomplete'=>'off')) }}
				{{ csrf_field() }}
				<input type="hidden" name="id" value="<?php echo $model->id; ?>">
				<div class="col-md-12 row">
					<div class="col-md-3 row">
						<div class="form-group {{ $errors->has('cargo_master_scan_status') ? 'has-error' :'' }}">
							<div class="col-md-12">
								<?php echo Form::label('cargo_master_scan_status', 'File Status', ['class' => 'control-label']); ?>
							</div>
							<div class="col-md-12">
								<?php echo Form::select('cargo_master_scan_status', Config::get('app.ups_new_scan_status'), $model->cargo_master_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'cargo_master_scan_status', 'data-container' => 'body', 'placeholder' => 'Select ...']); ?>
							</div>
						</div>
					</div>
					<div class="col-md-3 reason_for_return_div" style="<?php echo $model->cargo_master_scan_status == 7 ? 'display:block' : 'display:none' ?>">
						<div class="col-md-12">
							<?php echo Form::label('reason_for_return', 'Reason', ['class' => 'control-label']); ?>
						</div>
						<div class="col-md-12">
							<?php echo Form::select('reason_for_return', Config::get('app.reasonOfReturn'), $model->reason_for_return, ['class' => 'form-control selectpicker reason_for_return', 'data-live-search' => 'true', 'data-container' => 'body', 'placeholder' => 'Select Reason']); ?>
						</div>
					</div>
					<div class="col-md-3">
						<div class="col-md-12">
							<?php echo Form::label('shipment_notes_for_return', 'Comment', ['class' => 'control-label']); ?>
						</div>
						<div class="col-md-12">
							<?php echo Form::text('shipment_notes_for_return', '', ['class' => 'form-control shipment_notes_for_return', 'placeholder' => 'Enter Comment Here']); ?>
						</div>
					</div>
					<div class="form-group col-md-2">
						<div class="col-md-12">
							<?php echo Form::label('', '&nbsp;', ['class' => 'control-label']); ?>
						</div>
						<div class="col-md-12">
							<button type="submit" class="btn btn-success" style="width: 50%">Save</button>
						</div>
					</div>

				</div>

				{{ Form::close() }}
			<?php } ?>

			<h4 style="background: #efeaeacc;padding: 10px;">House Files Listing</h4>
			<table id="housefile" class="display nowrap" style="width:100%">
				<thead>
					<tr>
						<th>Type</th>
						<th>File No.</th>
						<th>Billing Party</th>
						<th>File Status</th>
						<th>Opening Date</th>
						<th>House AWB No.</th>
						<th>Consignee</th>
						<th>Shipper</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($HouseAWBData as $items)
					<tr data-editlink="{{ route('cargowarehouseflow',[$model->id,$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
						<td>{{$items->cargo_operation_type == 1 ? 'Import' :  'Export'}}</td>
						<td>{{$items->file_number}}</td>
						<td>{{$items->billingParty}}</td>
						<td>{{isset(Config::get('app.ups_new_scan_status')[!empty($items->hawb_scan_status) ? $items->hawb_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($items->hawb_scan_status) ? $items->hawb_scan_status : '-'] : '-'}}</td>
						<td>{{date('d-m-Y', strtotime($items->opening_date))}}</td>
						<td>{{$items->cargo_operation_type == 1 ? $items->hawb_hbl_no : $items->export_hawb_hbl_no}}</td>
						<td>{{$items->consigneeName}}</td>
						<td>{{$items->shipperName}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>


	<div id="modalAddRackLocation" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h3 class="modal-title modal-title-block text-center primecolor">Add/Change Rack Location</h3>
				</div>
				<div class="modal-body" id="modalContentAddRackLocation" style="overflow: hidden;">
				</div>
			</div>

		</div>
	</div>

	<div id="modalAddVerificationNote" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h3 class="modal-title modal-title-block text-center primecolor">Add Note</h3>
				</div>
				<div class="modal-body" id="modalContentVerificationNote" style="overflow: hidden;">
				</div>
			</div>

		</div>
	</div>

	<div id="modalViewVerificationNote" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h3 class="modal-title modal-title-block text-center primecolor">View Notes</h3>
				</div>
				<div class="modal-body" id="modalContentViewVerificationNote" style="overflow: hidden;">
				</div>
			</div>

		</div>
	</div>
</section>

@endsection
@section('page_level_js')
<script type="text/javascript">
	$('.datepicker').datepicker({
		format: 'dd-mm-yyyy',
		todayHighlight: true,
		autoclose: true
	});
	$(document).ready(function() {

		$('#housefile').DataTable({
			'stateSave': true,
			"scrollX": true,
		});

		$('.fa-calendar-shipmentstatus').click(function() {
			tId = $(this).data('id');
			if ($('.radio-shipmentstatus-' + tId + ':checked').val() == '1')
				$(this).parent('div').next('div').find('.datepicker').trigger('focus');
			else if ($('.radio-shipmentstatus-' + tId + ':checked').val() == '2')
				$(this).parent('div').next('div').next('div').find('.datepicker').trigger('focus');
			else
				$(this).parent('div').next('div').next('div').next('div').find('.datepicker').trigger('focus');
		})
		$('.fa-calendar-custominspection').click(function() {
			tId = $(this).data('id');
			$(this).parent('div').next('div').find('.datepicker').trigger('focus');
		})
		$('.fa-calendar-invoiceandpayment').click(function() {
			tId = $(this).data('id');
			$(this).parent('div').next('div').find('.datepicker').trigger('focus');
		})

		<?php if (empty($houseId)) { ?>
			//$('.detail-container-hawb-1').show();
		<?php } else { ?>
			$('.detail-container-hawb-<?php echo $houseId; ?>').show();
			$('html, body').animate({
				scrollTop: $("#div_step4-<?php echo $houseId; ?>").offset().top
			}, 2000);
		<?php } ?>

		//$('.fa-expand-collapse-1').removeClass('fa-plus');
		//$('.fa-expand-collapse-1').addClass('fa-minus');

		$('.fa-expand-collapse').click(function() {
			var id = $(this).data('id');
			if ($(this).hasClass('fa-minus')) {
				$(this).removeClass('fa-minus');
				$(this).addClass('fa-plus');
				$('.detail-container-hawb-' + id).hide('slow');
			} else {
				$('.fa-expand-collapse').removeClass('fa-minus');
				$('.fa-expand-collapse').addClass('fa-plus');
				$('.detail-container-hawb').hide('slow');
				$(this).removeClass('fa-plus');
				$(this).addClass('fa-minus');
				$('.detail-container-hawb-' + id).show('slow');
			}

		})

		$('.radio-shipmentstatus').change(function() {
			var tId = $(this).data('id');
			var tVal = $(this).val();
			$(this).parent('span').parent('div').next('div').show();
			if (tVal == 1) {
				$('.shipment_received_date_div-' + tId).show();
				$('.shipment_incomplete_date_div-' + tId).hide();
				$('.shipment_shortshipped_date_div-' + tId).hide();
			} else if (tVal == 2) {
				$('.shipment_incomplete_date_div-' + tId).show();
				$('.shipment_received_date_div-' + tId).hide();
				$('.shipment_shortshipped_date_div-' + tId).hide();
			} else {
				$('.shipment_shortshipped_date_div-' + tId).show();
				$('.shipment_incomplete_date_div-' + tId).hide();
				$('.shipment_received_date_div-' + tId).hide();
			}
		})

		$('.inspection_flag').change(function() {
			var tId = $(this).data('id');
			var tVal = $(this).val();
			if ($(this).prop('checked')) {
				$(this).parent('span').parent('div').next('div').show();
				$('.inspection_date_div-' + tId).show();
			} else {
				$(this).parent('span').parent('div').next('div').hide();
				$('.inspection_date_div-' + tId).hide();
			}
		})


		$('.btn-step1shipmentstatus').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			var id = tId;
			var shipment_status = $('.radio-shipmentstatus-' + tId + ':checked').val();
			var shipment_received_date = $('.shipment_received_date-' + tId).val();
			var shipment_incomplete_date = $('.shipment_incomplete_date-' + tId).val();
			var shipment_shortshipped_date = $('.shipment_shortshipped_date-' + tId).val();
			var shipment_notes = $('.shipment_notes-' + tId).val();

			$('#loading').show();
			var urlz = '<?php echo url("warehouse/cargo/step1shipmentstatus"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'shipment_status': shipment_status,
					'shipment_received_date': shipment_received_date,
					'shipment_incomplete_date': shipment_incomplete_date,
					'shipment_shortshipped_date': shipment_shortshipped_date,
					'shipment_notes': shipment_notes,
					'flagModule': 'cargo',
					'flagModuleId': '<?php echo $model->id; ?>'
				},
				success: function(data) {
					$('#loading').hide();
					$('.step1shipmentstatus-ajax-container-' + tId).html(data);
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'File Status has been updated successfully.'
					});
				},
			});

		})

		$('.btn-step2racklocation').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			var id = tId;

			var rack_location = [];
			i = 0;
			$(".rack_location-" + tId + " option:selected").each(function() {
				var $this = $(this);
				if ($this.length) {
					rack_location[i] = $this.val();
					i++;
				}
			});

			$('#loading').show();
			var urlz = '<?php echo url("warehouse/cargo/step2racklocation"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'rack_location': rack_location
				},
				success: function(data) {
					$('#loading').hide();
					$('.step2racklocation-ajax-container-' + tId).html(data);
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Rack locations has been updated successfully.'
					});
				},
			});

		})

		$('.btn-step3custominspection').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			var id = tId;


			var inspection_flag = $('.inspection_flag-' + tId).prop('checked');
			var inspection_date = $('.inspection_date-' + tId).val();
			var custom_file_number = $('.custom_file_number-' + tId).val();
			var shipment_notes = $('.shipment_notes_inspection-' + tId).val();

			if ($('.custom_file_number-' + tId).val() == '') {
				Lobibox.notify('error', {
					size: 'mini',
					delay: 2000,
					rounded: true,
					delayIndicator: false,
					msg: 'Please enter the custom file number'
				});
				return false;
			}
			if ($('.shipment_notes_inspection-' + tId).val() == '') {
				Lobibox.notify('error', {
					size: 'mini',
					delay: 2000,
					rounded: true,
					delayIndicator: false,
					msg: 'Please enter the any comment'
				});
				return false;
			}

			$('#loading').show();
			var urlz = '<?php echo url("warehouse/cargo/step3custominspection"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'inspection_flag': inspection_flag,
					'inspection_date': inspection_date,
					'custom_file_number': custom_file_number,
					'shipment_notes': shipment_notes
				},
				success: function(data) {
					$('#loading').hide();
					$('.step3custominspection-ajax-container-' + tId).html(data);
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Custom inspection status has been updated successfully.'
					});
				},
			});
		})

		$('.btn-step4invoiceandpayment').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			if ($('.custom_invoice_number-' + tId).val() == '') {
				Lobibox.notify('error', {
					size: 'mini',
					delay: 2000,
					rounded: true,
					delayIndicator: false,
					msg: 'Please enter custom invoice number'
				});
				return false;
			}
			var id = tId;
			var custom_invoice_number = $('.custom_invoice_number-' + tId).val();

			$('#loading').show();
			var urlz = '<?php echo url("warehouse/cargo/step4invoiceandpayment"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'custom_invoice_number': custom_invoice_number
				},
				success: function(data) {
					$('#loading').hide();
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Custom invoice number has been updated successfully.'
					});
				},
			});
		})

		$('.btn-step5shipmentrelease').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			var id = tId;
			var release_by_customer = $('.release_by_customer-' + tId).val();
			var release_by_css_agent = $('.release_by_css_agent-' + tId).val();
			var release_by_css_driver = $('.release_by_css_driver-' + tId).val();

			$('#loading').show();
			var urlz = '<?php echo url("warehouse/cargo/step5shipmentrelease"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'release_by_customer': release_by_customer,
					'release_by_css_agent': release_by_css_agent,
					'release_by_css_driver': release_by_css_driver
				},
				success: function(data) {
					$('#loading').hide();
					$('.step5shipmentrelease-ajax-container-' + tId).html(data);
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Data has been updated successfully.'
					});
				},
			});
		})

		$('.customButtonInGrid').click(function() {
			var status = $(this).val();
			var hawbId = $(this).data('hawbid');
			var flag = $(this).data('flag');
			var thiz = $(this);


			Lobibox.confirm({
				msg: "Are you sure to change status?",
				callback: function(lobibox, type) {

					if (type == 'yes') {
						$.ajaxSetup({
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							}
						});
						$.ajax({
							type: 'post',
							url: '<?php echo url('cargo/verificationinspection'); ?>',
							data: {
								'status': status,
								'hawbId': hawbId,
								'flag': flag
							},
							success: function(response) {
								//thiz.val(status);
							}
						});

						var redirectUrl = '<?php echo url("cargo/viewcargodetailforwarehouse/$model->id"); ?>';
						setTimeout(function() {
							window.location.href = redirectUrl;
						}, 100);
						Lobibox.notify('info', {
							size: 'mini',
							delay: 2000,
							rounded: true,
							delayIndicator: false,
							msg: 'Status has been updated successfully.'
						});
					} else {}
				}
			})
		})

		$('.generatehousefileinvoice').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var id = $(this).data('id');
			var revise = $(this).data('revise');
			var moduleId = '<?php echo $model->id; ?>';
			var flagModule = 'cargo';
			var invoiceDate = $('.invoice_date-' + id).val();
			var urlznt = '<?php echo url("generatehousefileinvoice"); ?>';

			$.ajax({
				url: urlznt,
				type: 'POST',
				data: {
					'id': id,
					'moduleId': moduleId,
					'flagModule': flagModule,
					'invoiceDate': invoiceDate,
					'revise': revise
				},
				success: function(data) {
					/* alert(data);
					return false; */
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Invoice has been created successfully.'
					});
					$('#loading').hide();
					window.open(data, '_blank');
					var locationHref = '<?php echo url("cargo/viewcargodetailforwarehouse"); ?>';
					locationHref += '/' + moduleId + '/cargo/' + id;
					window.location.href = locationHref;
				},
			});
		})

		$(document).delegate(".generatereleasereceipt", "click", function() {
			//$('.generatereleasereceipt').click(function(e) {
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var urlznt = '<?php echo url("warehouse/cargo/releasereceipt"); ?>';
			var id = $(this).data('id');
			$.ajax({
				url: urlznt,
				type: 'POST',
				data: {
					'id': id
				},
				success: function(data) {
					/* alert(data);
					return false; */
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Receipt has been released successfully.'
					});
					$('#loading').hide();
					window.open(data, '_blank');
				},
			});
		});

		$('#cargo_master_scan_status').change(function() {
			var tVal = $(this).val();
			if (tVal == 7) {
				$('.reason_for_return_div').show();
			} else {
				$('.reason_for_return_div').hide();
			}
		})

		$('#createforms').on('submit', function(event) {
			if ($('#shipment_notes_for_return').val() == '') {
				Lobibox.notify('error', {
					size: 'mini',
					delay: 2000,
					rounded: true,
					delayIndicator: false,
					msg: 'Please enter the any comment'
				});
				$('#loading').hide();
				return false;
			} else {
				return true;
			}
		});
	})
</script>
@stop