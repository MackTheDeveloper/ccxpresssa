<?php

use App\Warehouse;

$warehouse = new Warehouse;
?>
@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('breadcrumbs')
@include('menus.warehouse-ups-files')
@stop

@section('content')
@if(Session::has('flash_message'))
<div class="alert alert-success flash-success">
	{{ Session::get('flash_message') }}
</div>
@endif
<section class="content-header">
	<h1>
		<div style="float: left"><?php echo 'UPS - ' . $model->file_number; ?></div>
		<?php if ($model->file_close == 1) { ?>
			<div style="color:red;float:right">CLOSED</div>
		<?php }  ?>
	</h1>
</section>
<section class="content editupscontainer" style="float: left">
	<div class="box box-success">
		<div class="box-body">

			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger flash-danger">
				{{ Session::get('flash_message_error') }}
			</div>
			@endif

			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>

			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Master File Details</div>
			<div class="detail-container">
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB/BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->tracking_number) ? $model->tracking_number : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Expediteur / Shipper : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Address : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : $model->consignee_address; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Explications : </span>
					<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Arrival Date : </span>
					<span class="viewblk2"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Weight : </span>
					<span class="viewblk2"><?php echo !empty($model->weight) ? $model->weight : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Volumne : </span>
					<span class="viewblk2"><?php echo !empty($model->volume) ? $model->volume : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Number of Pieces : </span>
					<span class="viewblk2"><?php echo !empty($model->pieces) ? (int) $model->pieces : '-'; ?></span>
				</div>
			</div>

			<h4 style="background: #efeaeacc;padding: 10px;">House Files Listing</h4>
			<table id="housefile" class="display nowrap" style="width:100%">
				<thead>
					<tr>
						<th>File No.</th>
						<th>Billing Party</th>
						<th>File Status</th>
						<th>Arrival Date</th>
						<th>House AWB No.</th>
						<th>Consignee</th>
						<th>Shipper</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($HouseAWBData as $items)
					<tr data-editlink="{{ route('courierupswarehouseflow',[$model->id,$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
						<td>{{$items->file_number}}</td>
						<td>{{$items->billingParty}}</td>
						<td>{{isset(Config::get('app.ups_new_scan_status')[!empty($items->ups_scan_status) ? $items->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($items->ups_scan_status) ? $items->ups_scan_status : '-'] : '-'}}</td>
						<td>{{date('d-m-Y', strtotime($items->arrival_date))}}</td>
						<td>{{$items->awb_number}}</td>
						<td>{{$items->consigneeName}}</td>
						<td>{{$items->shipperName}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
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

		$('.ups_scan_status').change(function() {
			var tId = $(this).data('id');
			var tVal = $(this).val();
			if (tVal == 7) {
				$('.reason_for_return_div-' + tId).show();
			} else {
				$('.reason_for_return_div-' + tId).hide();
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

		$('.btn-upsstep1shipmentstatus').click(function(e) {
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

			if ($('.shipment_notes-' + tId).val() == '') {
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
			var urlz = '<?php echo url("warehouse/ups/upsstep1shipmentstatus"); ?>';
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
					'flagModule': 'ups',
					//'flagModuleId': '<?php //echo $items->id; 
															?>'
				},
				success: function(data) {
					$('#loading').hide();
					$('.upsstep1shipmentstatus-ajax-container-' + tId).html(data);
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

		$('.btn-upsstep2custominspection').click(function(e) {
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
			var inspection_file_status = $('.inspection_file_status-' + tId + " option:selected").val();

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
			var urlz = '<?php echo url("warehouse/ups/upsstep2custominspection"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'inspection_flag': inspection_flag,
					'inspection_date': inspection_date,
					'custom_file_number': custom_file_number,
					'shipment_notes': shipment_notes,
					'inspection_file_status': inspection_file_status
				},
				success: function(data) {
					$('#loading').hide();
					$('.upsstep2custominspection-ajax-container-' + tId).html(data);
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

		$('.btn-upsstep3movetononboundedwh').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			var id = tId;


			var move_to_nonbounded_wh = $('.move_to_nonbounded_wh-' + tId).prop('checked');

			$('#loading').show();
			var urlz = '<?php echo url("warehouse/ups/upsstep3movetononboundedwh"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'move_to_nonbounded_wh': move_to_nonbounded_wh
				},
				success: function(data) {
					$('#loading').hide();
					$('.upsstep3movetononboundedwh-ajax-container-' + tId).html(data);
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Nonbound information has been updated successfully.'
					});
				},
			});
		})

		$('.btn-upsstep4invoiceandpayment').click(function(e) {
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
			var urlz = '<?php echo url("warehouse/ups/upsstep4invoiceandpayment"); ?>';
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

		//$('.delivery_boy').change(function(e) {
		$('.btn-upsstep5assigndeliveryboy').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			$('#loading').show();
			var id = tId;
			var reason = $(this).data('reason');
			if (reason == 0) {
				if ($('.delivery_boy-' + tId + " option:selected").val() == '') {
					$('#loading').hide();
					Lobibox.notify('error', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Please select delivery boy'
					});
					return false;
				}
				var delivery_boy = $('.delivery_boy-' + tId + " option:selected").val();
				var urlz = '<?php echo url("warehouse/ups/upsstep5assigndeliveryboy"); ?>';
				$.ajax({
					url: urlz,
					type: 'POST',
					data: {
						'id': id,
						'delivery_boy': delivery_boy,
						'reason': reason
					},
					success: function(data) {
						$('#loading').hide();
						$('.upsstep5assigndeliveryboy-ajax-container-' + tId).html(data);
						$('.selectpicker').selectpicker('refresh');
						Lobibox.notify('info', {
							size: 'mini',
							delay: 2000,
							rounded: true,
							delayIndicator: false,
							msg: 'Delivery boy has been assigned successfully.'
						});
					},
				});
			} else {
				if ($('.shipment_notes_for_return-' + tId).val() == '') {
					Lobibox.notify('error', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Please enter the any comment'
					});
					$('#loading').hide();
					return false;
				}
				var ups_scan_status = $('.ups_scan_status-' + tId + " option:selected").val();
				var reason_for_return = $('.reason_for_return-' + tId + " option:selected").val();
				var shipment_notes = $('.shipment_notes_for_return-' + tId).val();
				var urlz = '<?php echo url("warehouse/ups/upsstep5assigndeliveryboy"); ?>';
				$.ajax({
					url: urlz,
					type: 'POST',
					data: {
						'id': id,
						'ups_scan_status': ups_scan_status,
						'reason_for_return': reason_for_return,
						'shipment_notes': shipment_notes,
						'reason': reason
					},
					success: function(data) {
						$('#loading').hide();
						$('.upsstep5assigndeliveryboy-reason-ajax-container-' + tId).html(data);
						$('.selectpicker').selectpicker('refresh');
						Lobibox.notify('info', {
							size: 'mini',
							delay: 2000,
							rounded: true,
							delayIndicator: false,
							msg: 'File status has been updated successfully.'
						});
					},
				});
			}
		})

		$('.btn-assignstatusbywarehousecouriermaster').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var tId = $(this).data('id');
			$('#loading').show();
			var id = tId;
			var ups_scan_status = $('.ups_scan_status-main-' + tId + " option:selected").val();
			var urlz = '<?php echo url("warehouseups/assignstatusbywarehousecouriermaster"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'ups_scan_status': ups_scan_status,
				},
				success: function(data) {
					$('#loading').hide();
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Status has been updated successfully.'
					});
				},
			});

		})

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$('.generatehousefileinvoice').click(function(e) {
			e.preventDefault();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var id = $(this).data('id');
			var revise = $(this).data('revise');
			//var moduleId = '<?php //echo $items->id; 
												?>';
			var moduleId = $(this).data('id');;
			var flagModule = 'ups';
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
					var locationHref = '<?php echo url("warehouseups/viewcourierdetailforwarehouse"); ?>';
					locationHref += '/' + moduleId;
					window.location.href = locationHref;
				},
			});
		})
	})
</script>
@stop