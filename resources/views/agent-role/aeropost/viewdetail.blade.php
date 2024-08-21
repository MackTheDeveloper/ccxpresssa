@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('breadcrumbs')
@include('menus.aeropost')
@stop
<?php

use App\Warehouse;

$warehouse = new Warehouse;
?>
@section('content')
<section class="content-header">
	<h1 style=""><?php echo 'Aeropost - ' . $model->file_number; ?>
		<?php if ($model->file_close == 1) { ?>
			<div style="color:red;float:right">CLOSED</div>
		<?php } ?>
	</h1>
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

			<div class="" style="float: left;width: 100%;margin-bottom: 10px;">
				<div class="" style="float:left;margin-right: 5px;">
					<a style="" title="Click here to print" class="btn btn-primary" target="_blank" href="{{route('printaeropostfile', [$model->id])}}"><i class="fa fa-print"></i></a>
				</div>
				<div class="" style="float:left;">
					<button class="btn btn-primary" id="upload-file-btn" value="{{url('files/upload',['aeropost',$model->id])}}"><i class="fa fa-upload" aria-hidden="true" style="margin-right: 5px"></i>Upload Files</button>
				</div>
			</div>

			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">File Details</div>

			<div class="detail-container basicDetaiCls">
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Tracking No : </span>
					<span class="viewblk2"><?php echo $model->tracking_no; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Shipper : </span>
					<span class="viewblk2"><?php echo $model->from_location; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2">
						<?php $data = app('App\Clients')->getClientData($model->consignee);
						echo !empty($data->company_name) ? $data->company_name : '-'; ?>
					</span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Shipper Phone No : </span>
					<span class="viewblk2"><?php echo $model->from_phone; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignee Phone No : </span>
					<span class="viewblk2"><?php echo $model->consignee_phone; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Shipper Address : </span>
					<span class="viewblk2"><?php echo !empty($model->from_address) ? $model->from_address : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignee Address : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Weight : </span>
					<span class="viewblk2"><?php echo !empty($model->real_weight) ? $model->real_weight : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Total Weight : </span>
					<span class="viewblk2"><?php echo !empty($model->shipment_real_weight) ? $model->shipment_real_weight : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Total Pieces : </span>
					<span class="viewblk2"><?php echo !empty($model->total_pieces) ? (int) $model->total_pieces : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Arrival Date : </span>
					<span class="viewblk2"><?php echo date('d-m-Y', strtotime($model->date)); ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Freight : </span>
					<span class="viewblk2"><?php echo !empty($model->freight) ? $model->freight : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Total Freight : </span>
					<span class="viewblk2"><?php echo !empty($model->total_freight) ? '$' . $model->total_freight : '-' ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Warehouse : </span>
					<span class="viewblk2">
						<?php $warehouseData = $warehouse->getData($model->warehouse);
						if (!empty($warehouseData)) {
							echo $warehouseData->name;
						} else {
							echo '-';
						}
						?>
					</span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Description : </span>
					<span class="viewblk2"><?php echo !empty($model->description) ? $model->description : '-' ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Master File Number : </span>
					<span class="viewblk2"><?php echo !empty($model->master_file_number) ? $model->master_file_number : '-' ?></span>
				</div>
			</div>

			<div id="div_activities" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Related Files</div>
			<div class="detail-container" style="">
				<table id="filesTable" class="simpletable display nowrap" style="width:100%;float: left;">
					<thead>
						<th style="display: none;">Id</th>
						<th>File Type</th>
						<th>File Name</th>
						<th>Actions</th>
					</thead>
					<tbody>
						<?php $i = 1; ?>
						@if(count($filesInfo)>0)

						@foreach($filesInfo as $files)
						<tr>
							<td style="display: none;">{{$i}}</td>
							<td><?php echo !empty($files->file_type) ? $fileTypes[$files->file_type] : '-' ?></td>
							<td>{{$files->file_name}}</td>
							<td>
								<div class='dropdown'>
									<?php
									$delete =  route('deletefiles', ['aeropost', $model->id, serialize($files->file_name)]);
									$download =  route('downloadfiles', ['aeropost', $model->id, serialize($files->file_name)]);
									?>
									<a href="javascript:void(0)" title="Download" data-value={{$download}} id="download"><i class="fa fa-download" aria-hidden="true"></i></a>&nbsp; &nbsp;
									<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
								</div>
							</td>
						</tr>

						@endforeach
						@endif
					</tbody>
				</table>
			</div>

			<div id="div_activities" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Activities</div>
			<div class="detail-container " style="height: auto;max-height: 400px;overflow: auto;width: 100%;">
				<div style="float: left;width: 100%;margin-bottom: 10px;font-weight: bold;text-align: center">
					<div class="labeldata20">Performed By</div>
					<div class="resultdata60">Activities</div>
					<div class="resultdata20">Date/Time</div>
				</div>
				<?php if (!empty($activityData)) { ?>
					<div>
						@foreach ($activityData as $activityData)
						<div class="labeldata20"><?php $userData = app('App\User')->getUserName($activityData->user_id);
																			echo $userData->name; ?></div>
						<div class="resultdata60"><?php echo $activityData->description; ?></div>
						<div class="resultdata20"><?php echo date('d-m-Y h:i:s', strtotime($activityData->updated_on)); ?></div>
						@endforeach
					</div>
				<?php } else { ?>
					<h4 style="float: left;width: 100%;font-size: 15px;">No Activity Found.</h4>
				<?php } ?>
			</div>

			<?php
			$actionUrl = url('aeropost/assignbillingparty');
			?>
			{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'createforms','autocomplete'=>'off')) }}
			{{ csrf_field() }}
			<input type="hidden" name="id" value="<?php echo $model->id; ?>">
			<div class="col-md-12">
				<div class="col-md-3">
					<div class="col-md-12">
						<?php echo Form::label('aeropost_scan_status', 'File Status', ['class' => 'control-label']); ?>
					</div>
					<div class="col-md-12">
						<?php echo Form::select('aeropost_scan_status', Config::get('app.ups_new_scan_status'), $model->aeropost_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'aeropost_scan_status', 'placeholder' => 'Select ...']); ?>
					</div>
				</div>
				<div class="col-md-3 reason_for_return_div" style="<?php echo $model->aeropost_scan_status == 7 ? 'display:block' : 'display:none' ?>">
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
				<div class="col-md-3">
					<div class="col-md-12">
						<?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>
					</div>
					<div class="col-md-12">
						<?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
					</div>
					<div class="col-md-12 balance-div" style="display: none;text-align: center;">
						<span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div class="col-md-3">
					<div class="col-md-12">
						<?php echo Form::label('date', 'Arrival Date', ['class' => 'control-label']); ?>
					</div>
					<div class="col-md-12 consolidate_flag-md-6">
						<?php echo Form::text('date', !empty($model->date) ? date('d-m-Y', strtotime($model->date)) : null, ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
					</div>
				</div>
				<div class="form-group col-md-2 btm-sub" style="float: right">
					<div class="col-md-12">
						<?php echo Form::label('', '', ['class' => 'control-label']); ?>
					</div>
					<div class="col-md-12">
						<button type="submit" class="btn btn-success" style="width: 50%">Save</button>
						<a class="btn btn-danger" href="{{url('aeroposts')}}" title="">Cancel</a>
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
		$('.datepicker').datepicker({
			format: 'dd-mm-yyyy',
			todayHighlight: true,
			autoclose: true
		});
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$('#filesTable').DataTable({
			"order": [
				[0, "desc"]
			],
		});
		$('#createforms').on('submit', function(event) {
			event.preventDefault();

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
			}

			$('#loading').show();
			var form = $("#createforms");
			var formData = form.serialize();
			var urlz = '<?php echo url("aeropost/assignbillingparty"); ?>';
			$.ajax({
				url: urlz,
				async: false,
				type: 'POST',
				data: formData,
				success: function(data) {
					$('#loading').hide();
					$('.selectpicker').selectpicker('refresh');
					Lobibox.notify('info', {
						size: 'mini',
						delay: 2000,
						rounded: true,
						delayIndicator: false,
						msg: 'Billing party has been assigned successfully.'
					});
				},
			});
		});

		<?php if ($model->id) { ?>

			var clientId = $('#billing_party').val();
			if (clientId != '') {
				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});
				var urlzte = '<?php echo url("clients/getclientdata"); ?>';
				$.ajax({
					async: false,
					url: urlzte,
					dataType: "json",
					type: 'POST',
					data: {
						'clientId': clientId
					},
					success: function(balance) {
						$('#loading').hide();
						if (balance.cash_credit == 'Credit') {
							$('.balance-div').show();
							var blnc = balance.available_balance;
							$('.cash_credit_account_balance').html(blnc).formatCurrency({
								negativeFormat: '-%s%n',
								roundToDecimalPlace: 2,
								symbol: ''
							});
						} else {
							$('.balance-div').hide();
						}

					}
				});
			} else {
				$('#loading').hide();
				$('.balance-div').hide();
			}

		<?php } ?>

		$('#billing_party').change(function() {

			$('#loading').show();
			var clientId = $(this).val();
			if (clientId != '') {
				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});
				var urlzte = '<?php echo url("clients/getclientdata"); ?>';
				$.ajax({
					async: false,
					url: urlzte,
					dataType: "json",
					type: 'POST',
					data: {
						'clientId': clientId
					},
					success: function(balance) {
						$('#loading').hide();
						if (balance.cash_credit == 'Credit') {
							$('.balance-div').show();
							var blnc = balance.available_balance;
							$('.cash_credit_account_balance').html(blnc).formatCurrency({
								negativeFormat: '-%s%n',
								roundToDecimalPlace: 2,
								symbol: ''
							});
						} else {
							$('.balance-div').hide();
						}

					}
				});
			} else {
				$('#loading').hide();
				$('.balance-div').hide();
			}

		})

		$('#aeropost_scan_status').change(function() {
			var tVal = $(this).val();
			if (tVal == 7) {
				$('.reason_for_return_div').show();
			} else {
				$('.reason_for_return_div').hide();
			}
		})

	})
</script>
@stop