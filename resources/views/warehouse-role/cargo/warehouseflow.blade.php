@extends('layouts.custom')
@section('title')
Basic Detail
@stop
@section('breadcrumbs')
@include('menus.warehouse-cargo-files')
@stop
@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;font-weight: 600;"><?php echo 'Cargo - ' . $model->file_number; ?>
		<?php if ($model->file_close == 1) { ?>
			<div style="color:red;float:right">CLOSED</div>
		<?php } ?></h1>
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

			<div class="row">
				<div class="col-md-1">
					<a class="btn round orange btn-warning" href="{{route('viewcargodetailforwarehouse',[$masterId])}}">Back</a>
				</div>
			</div>
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

			<?php if ($model->file_close != 1) { ?>
				<?php
				$actionUrl = url('cargo/assigncargohousefilestatusbywarehouseuser');
				?>
				{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'createforms','autocomplete'=>'off')) }}
				{{ csrf_field() }}
				<input type="hidden" name="id" value="<?php echo $houseId; ?>">
				<input type="hidden" name="masterId" value="<?php echo $masterId; ?>">
				<div class="col-md-12 detail-container" style="margin-bottom: 10px">
					<div class="col-md-3 row">
						<div class="form-group {{ $errors->has('hawb_scan_status') ? 'has-error' :'' }}">
							<div class="col-md-12">
								<?php echo Form::label('hawb_scan_status', 'File Status', ['class' => 'control-label']); ?>
							</div>
							<div class="col-md-12">
								<?php echo Form::select('hawb_scan_status', Config::get('app.ups_new_scan_status'), $modelCargoHouse->hawb_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'hawb_scan_status', 'data-container' => 'body', 'placeholder' => 'Select ...']); ?>
							</div>
						</div>
					</div>
					<div class="col-md-3 reason_for_return_div" style="<?php echo $modelCargoHouse->hawb_scan_status == 7 ? 'display:block' : 'display:none' ?>">
						<div class="col-md-12">
							<?php echo Form::label('reason_for_return', 'Reason', ['class' => 'control-label']); ?>
						</div>
						<div class="col-md-12">
							<?php echo Form::select('reason_for_return', Config::get('app.reasonOfReturn'), $modelCargoHouse->reason_for_return, ['class' => 'form-control selectpicker reason_for_return', 'data-live-search' => 'true', 'data-container' => 'body', 'placeholder' => 'Select Reason']); ?>
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
							<button type="submit" class="btn btn-success assignCargoMasterFileStatus" style="width: 50%">Save</button>
						</div>
					</div>
				</div>
				{{ Form::close() }}
			<?php } ?>

			<?php if (count($HouseAWBData) != 0) { ?>
				<?php $i = 1;
				foreach ($HouseAWBData as $k => $items) { ?>
					<div id="div_basicdetails-<?php echo $i; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:0px;background-color: #365b7b;border-color: #365b7b;">
						<div style="float:left">House AWB Details</div>
						<div style="float: left;width: 20%;margin-left: 3%;">
							<span>File Number : </span>
							<span><?php echo !empty(trim($items->file_number)) ? $items->file_number : "-"; ?></span>
						</div>
						<div style="float: right;margin-top: -2%;width: 100%;text-align: right;margin-right: 1%;">
							<span style="width:100%" data-id="<?php echo $i; ?>" class="fa fa-plus fa-expand-collapse fa-expand-collapse-<?php echo $i; ?>"></span>
						</div>
					</div>

					<div class="detail-container detail-container-hawb detail-container-hawb-<?php echo $items->id; ?> detail-container-hawb-<?php echo $i; ?>" style="display:block">
						<div class="row-<?php echo $i; ?>">
							<div class="col-md-12" style="padding:0px">

								<div class="col-md-12" style="padding: 0px;margin-bottom: 15px;">
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">File No. : </span>
										<span class="viewblk2"><?php echo !empty(trim($items->file_number)) ? $items->file_number : "-"; ?></span>
									</div>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">House AWB No. : </span>
										<span class="viewblk2"><?php
																						if ($items->cargo_operation_type == '1')
																							$houseawb = !empty($items->hawb_hbl_no) ? $items->hawb_hbl_no : '-';
																						else
																							$houseawb = !empty($items->export_hawb_hbl_no) ? $items->export_hawb_hbl_no : '-';

																						echo $houseawb;  ?></span>
									</div>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">Consignee : </span>
										<span class="viewblk2"><?php $dataConsignee = App\Ups::getConsigneeName($items->consignee_name);
																						echo !empty($dataConsignee) ? $dataConsignee : '-' ?></span>
									</div>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">Shipper : </span>
										<span class="viewblk2"><?php $dataShipper = App\Ups::getConsigneeName($items->shipper_name);
																						echo !empty($dataShipper) ? $dataShipper : '-' ?></span>
									</div>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">Arrival Date : </span>
										<span class="viewblk2"><?php echo !empty($items->arrival_date) ? date('d-m-Y', strtotime($items->arrival_date)) : '-' ?></span>
									</div>
									<?php $modelCargoPackage = DB::table('hawb_packages')->where('hawb_id', $items->id)->first(); ?>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">Nbr of pieces : </span>
										<span class="viewblk2"><?php echo !empty($modelCargoPackage) ? (int) $modelCargoPackage->ppieces : '-'; ?></span>
									</div>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">Weight : </span>
										<span class="viewblk2"><?php echo !empty($modelCargoPackage) ? $modelCargoPackage->pweight : '-'; ?></span>
									</div>
									<div style="float: left;width: 50%; margin-bottom: 10px;">
										<span class="viewblk1">Volume : </span>
										<span class="viewblk2"><?php echo !empty($modelCargoPackage) ? $modelCargoPackage->pvolume : '-'; ?></span>
									</div>
								</div>

								<div class="col-md-12" style="padding: 0px 70px;margin-bottom: 4px;">

									<div class="step-1 steps step-<?php echo $items->shipment_status == 1 ? 'done' : 'pending'; ?>">
										<div class="inner-round inner-round-<?php echo $items->shipment_status == 1 ? 'done' : 'pending'; ?>">
										</div>
									</div>

									<div class="pipe pipe-<?php echo $items->inspection_flag == 1 ? 'done' : 'pending'; ?>"></div>
									<div class="step-2 steps step-<?php echo $items->inspection_flag == 1 ? 'done' : 'pending'; ?>">
										<div class="inner-round inner-round-<?php echo $items->inspection_flag == 1 ? 'done' : 'pending'; ?>">
										</div>
									</div>

									<?php
									//$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkInvoiceIsGeneratedOrNot($items->id, 'housefile');
									$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkAllInvoiceIsGeneratedOrNot($items->id, 'housefile');
									$checkIfAnyInvoicePending = app('App\Invoices')->checkIfAnyInvoicePending($items->id, 'housefile');
									if (count($checkInvoiceIsGeneratedOrNot) != 0 && $checkIfAnyInvoicePending == 0)
										$getLastInvoiceDateOfPayment = app('App\Invoices')->getLastInvoiceDateOfPayment($items->id, 'housefile');
									else
										$getLastInvoiceDateOfPayment = '';
									?>
									<div class="pipe pipe-<?php echo (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) ? 'pending' : 'done'; ?>"></div>
									<div class="step-3 steps step-<?php echo (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) ? 'pending' : 'done'; ?>">
										<div class="inner-round inner-round-<?php echo (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) ? 'pending' : 'done'; ?>">
										</div>
									</div>

									<div class="pipe pipe-<?php echo $items->warehouse_status == 3 ? 'done' : 'pending'; ?>"></div>
									<div class="step-4 steps step-<?php echo $items->warehouse_status == 3 ? 'done' : 'pending'; ?>">
										<div class="inner-round inner-round-<?php echo $items->warehouse_status == 3 ? 'done' : 'pending'; ?>">
										</div>
									</div>
								</div>

								<div class="col-md-12" style="padding: 0px 0px;margin-bottom: 15px;">
									<div style="float: left;text-align: left;width: 25%;">
										<div style="padding-left: 8%;">Shipment Received</div>
										<div style="padding-left: 15%;"><?php echo $items->shipment_status == 1 ?  (!empty($items->shipment_received_date) ? date('d-m-Y', strtotime($items->shipment_received_date)) : '') : '' ?></div>
									</div>
									<div style="float: left;text-align: left;width: 25%;">
										<div style="padding-left: 21%;">Custom Inspection</div>
										<div style="padding-left: 26%;"><?php echo $items->inspection_flag == 1 ? date('d-m-Y', strtotime($items->inspection_date)) : '' ?></div>
									</div>
									<div style="float: left;text-align: left;width: 25%;">
										<div style="padding-left: 42%;">Payment</div>
										<div style="padding-left: 40%;"><?php echo (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) ? '' : date('d-m-Y', strtotime($getLastInvoiceDateOfPayment)); ?></div>
									</div>
									<div style="float: left;text-align: left;width: 25%;">
										<div style="padding-left: 55%;">Release</div>
										<div style="padding-left: 52%;"><?php echo $items->warehouse_status == 3 ? date('d-m-Y', strtotime($items->shipment_delivered_date)) : '' ?></div>
									</div>
								</div>

								<div class="col-md-12" style="padding: 0px 0px;margin-bottom: 15px;">
									<div id="div_step1-<?php echo $items->id; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo $items->shipment_status != 1 ? 'background:#ccc;border: none;' : ''; ?>">1. Shipment Status</div>

									<div class="detail-container">
										<?php $actionUrl = url('warehouse/cargo/step1shipmentstatus'); ?>
										{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'step1shipmentstatus','autocomplete'=>'off')) }}
										{{ csrf_field() }}
										<div style="margin-top:0px;float:left;width:100%">

											<div style="width:47%;float:left;margin-right:10px;">
												<span class="shipement-status-span" style="background: #e8e8e8;padding: 0px;width: 30%;float: left;">
													<?php echo Form::radio('shipment_status', '1', $items->shipment_status == '1' ? 'checked' : '', ['class' => 'radio-shipmentstatus radio-shipmentstatus-' . $items->id, 'id' => 'radio-shipmentstatus-completed-' . $items->id, 'data-id' => $items->id]);
													echo Form::label('radio-shipmentstatus-completed-' . $items->id, 'Completed'); ?>
												</span>
												<span class="shipement-status-span" style="background: #e8e8e8;padding: 0px;margin-left:20px;width: 30%;float: left;">
													<?php echo Form::radio('shipment_status', '2', $items->shipment_status == '2' ? 'checked' : '', ['class' => 'radio-shipmentstatus radio-shipmentstatus-' . $items->id, 'id' => 'radio-shipmentstatus-incomplete-' . $items->id, 'data-id' => $items->id]);
													echo Form::label('radio-shipmentstatus-incomplete-' . $items->id, 'Incomplete'); ?>
												</span>
												<span class="shipement-status-span" style="background: #e8e8e8;padding: 0px;margin-left:20px;width: 30%;float: left;">
													<?php echo Form::radio('shipment_status', '3', $items->shipment_status == '3' ? 'checked' : '', ['class' => 'radio-shipmentstatus radio-shipmentstatus-' . $items->id, 'id' => 'radio-shipmentstatus-shortshipped-' . $items->id, 'data-id' => $items->id]);
													echo Form::label('radio-shipmentstatus-shortshipped-' . $items->id, 'Short Shipped'); ?>
												</span>
											</div>


											<div style="float: left;font-size: 20px;color: green;margin-right: 5px;<?php echo !empty($items->shipment_status) ? 'display:block' : 'display:none' ?>"><i data-id="<?php echo $items->id; ?>" class="fa fa-calendar fa-calendar-shipmentstatus"></i></div>
											<div style="width:10%;float:left;margin-right:10px;<?php echo $items->shipment_status == 1 ? 'display:block' : 'display:none'; ?>" class="shipment_received_date_div-<?php echo $items->id; ?>">
												<?php echo Form::text('shipment_received_date', !empty($items->shipment_received_date) ? date('d-m-Y', strtotime($items->shipment_received_date)) :  date('d-m-Y'), ['class' => 'form-control datepicker shipment_received_date shipment_received_date-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Date', 'disabled' => 'disabled']); ?>
											</div>
											<div style="width:10%;float:left;margin-right:10px;<?php echo $items->shipment_status == 2 ? 'display:block' : 'display:none'; ?>" class="shipment_incomplete_date_div-<?php echo $items->id; ?>">
												<?php echo Form::text('shipment_incomplete_date', !empty($items->shipment_incomplete_date) ? date('d-m-Y', strtotime($items->shipment_incomplete_date)) :  date('d-m-Y'), ['class' => 'form-control datepicker shipment_incomplete_date shipment_incomplete_date-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Date']); ?>
											</div>
											<div style="width:10%;float:left;margin-right:10px;<?php echo $items->shipment_status == 3 ? 'display:block' : 'display:none'; ?>" class="shipment_shortshipped_date_div-<?php echo $items->id; ?>">
												<?php echo Form::text('shipment_shortshipped_date', !empty($items->shipment_shortshipped_date) ? date('d-m-Y', strtotime($items->shipment_shortshipped_date)) :  date('d-m-Y'), ['class' => 'form-control datepicker shipment_shortshipped_date shipment_shortshipped_date-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Date']); ?>
											</div>

											<div style="width:34%;float:left;">
												<?php echo Form::text('shipment_notes', '', ['class' => 'form-control shipment_notes shipment_notes-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Comment']); ?>
											</div>

											<div style="width:5%;float:left;">
												<button type="button" class="btn btn-success btn-step1shipmentstatus btn-step1shipmentstatus-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
											</div>

											<?php


											if (!empty($items->shipment_status)) {
												$ajaxData['status'] = $items->shipment_status == '1' ? 'Received' : Config::get('app.shipmentStatus')[$items->shipment_status];
												if ($items->shipment_status == '1')
													$shipmentDate = !empty($items->shipment_received_date) ? date('d-m-Y', strtotime($items->shipment_received_date)) : '-';
												else if ($items->shipment_status == '2')
													$shipmentDate = !empty($items->shipment_incomplete_date) ? date('d-m-Y', strtotime($items->shipment_incomplete_date)) : '-';
												else
													$shipmentDate = !empty($items->shipment_shortshipped_date) ? date('d-m-Y', strtotime($items->shipment_shortshipped_date)) : '-';
												$ajaxData['on'] = $shipmentDate;
											} else {
												$ajaxData['status'] = '';
												$ajaxData['on'] = '';
											}

											$dataUser = app('App\User')->getUserName($items->shipment_status_changed_by);
											if (!empty($dataUser))
												$ajaxData['changedBy'] = $dataUser->name;
											else
												$ajaxData['changedBy'] = '';

											$dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'V')->where('hawb_id', $items->id)->orderBy('id', 'desc')->get();
											if (count($dataComments) > 0)
												$ajaxData['comments'] = $dataComments;
											else
												$ajaxData['comments'] = '';

											?>

											<div class="step1shipmentstatus-ajax-container step1shipmentstatus-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
												<?php if (!empty($ajaxData['status'])) { ?>
													<div style="float:left;width:100%;margin-bottom:10px;">
														<div style="float: left;margin-right: 10px;"><span style="color:#000">Shipment :</span> {{$ajaxData['status']}}</div>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
													</div>
												<?php } ?>

												<div style="float:left;width:100%">
													<h3 style="background: #efefef;border-bottom: 1px solid #000;padding: 10px;width: 100%;float: left;font-size: 15px;margin:0px">Comments</h3>
													<?php if (!empty($ajaxData['comments'])) {
														foreach ($ajaxData['comments'] as $k => $v) { ?>
															<div style="width:70%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;">{{$v->notes}}</div>
															<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">On</span><span>{{!empty($v->created_on) ? date('d-m-Y',strtotime($v->created_on)) : '-'}}</span></div>
															<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">By</span><span>
																	<?php
																	$dataUser = app('App\User')->getUserName($v->created_by);
																	if (!empty($dataUser))
																		echo $dataUser->name;
																	else
																		echo '-';
																	?></span></div>
														<?php }
													} else { ?>
														<div style="padding-left: 15px">No comment</div>
													<?php } ?>
												</div>
											</div>

										</div>

										{{ Form::close() }}
									</div>

									<div id="div_step2-<?php echo $items->id; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo empty($items->rack_location) ? 'background:#ccc;border: none;' : ''; ?>">2. Rack Location</div>

									<div class="detail-container">
										<?php
										$dataAvailableLocations = app('App\StorageRacks')->getAvailableLocations($items->id);
										$actionUrl = url('warehouse/cargo/step2racklocation'); ?>
										{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'step2racklocation','autocomplete'=>'off')) }}
										{{ csrf_field() }}
										<div style="margin-top:0px;float:left;width:100%">
											<div style="width:25%;float:left;margin-right:10px;">
												<?php echo Form::select('rack_location', $dataAvailableLocations, explode(',', $items->rack_location), ['class' => 'form-control selectpicker rack_location rack_location-' . $items->id, 'data-live-search' => 'true', 'multiple' => true, 'data-container' => 'body']); ?>
											</div>
											<div style="width:5%;float:left;">
												<button type="button" class="btn btn-success btn-step2racklocation btn-step2racklocation-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
											</div>
											<div class="step2racklocation-ajax-container step2racklocation-ajax-container-<?php echo $items->id; ?>" style="float:left;width:50%;margin-left: 15px;margin-top:5px;">
												<?php if ($items->warehouse_status == 3)
													echo 'Released';
												else
													echo app('App\StorageRacks')->getData($items->rack_location);
												?>
											</div>
										</div>
										{{ Form::close() }}
									</div>

									<div id="div_step3-<?php echo $items->id; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo empty($items->inspection_flag) ? 'background:#ccc;border: none;' : ''; ?>">3. Custom Inspection</div>

									<div class="detail-container">
										<?php $actionUrl = url('warehouse/cargo/step3custominspection'); ?>
										{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'step3custominspection','autocomplete'=>'off')) }}
										{{ csrf_field() }}
										<div style="margin-top:0px;float:left;width:100%">
											<div style="width:20%;float:left;margin-right:10px;">
												<span class="custominspection-span" style="background: #e8e8e8;padding: 0px;width: 100%;float: left;">
													<?php echo Form::checkbox('inspection_flag', null, $items->inspection_flag == '1' ? 'checked' : '', array('id' => 'inspection_flag-' . $items->id, 'class' => 'inspection_flag inspection_flag-' . $items->id, 'data-id' => $items->id)); ?>
													<label for="inspection_flag-<?php echo $items->id; ?>">Custom Inspection</label>
												</span>
											</div>

											<div style="float: left;font-size: 20px;color: green;margin-right: 5px;<?php echo !empty($items->inspection_flag) ? 'display:block' : 'display:none' ?>"><i data-id="<?php echo $items->id; ?>" class="fa fa-calendar fa-calendar-custominspection"></i></div>

											<div style="width:10%;float:left;margin-right:10px;<?php echo $items->inspection_flag == 1 ? 'display:block' : 'display:none'; ?>" class="inspection_date_div-<?php echo $items->id; ?>">
												<?php echo Form::text('inspection_date', !empty($items->inspection_date) ? date('d-m-Y', strtotime($items->inspection_date)) :  date('d-m-Y'), ['class' => 'form-control datepicker inspection_date inspection_date-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Date']); ?>
											</div>
											<div style="width:20%;float:left;margin-right: 19px;">
												<?php echo Form::text('custom_file_number', $items->custom_file_number, ['class' => 'form-control custom_file_number custom_file_number-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Custom File Number', 'title' => 'Custom File Number']); ?>
											</div>

											<div style="width:40%;float:left;">
												<?php echo Form::text('shipment_notes_inspection', '', ['class' => 'form-control shipment_notes_inspection shipment_notes_inspection-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Comment']); ?>
											</div>

											<div style="width:5%;float:left;">
												<button type="button" class="btn btn-success btn-step3custominspection btn-step3custominspection-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
											</div>

											<?php
											$ajaxData = array();
											if (!empty($items->inspection_flag)) {
												$ajaxData['status'] = Config::get('app.inspectionFileWarehouse')[$items->inspection_flag];
												$ajaxData['on'] = date('d-m-Y', strtotime($items->inspection_date));
												$dataUser = app('App\User')->getUserName($items->inspection_by);
												if (!empty($dataUser))
													$ajaxData['changedBy'] = $dataUser->name;
												else
													$ajaxData['changedBy'] = '-';
											} else {
												$ajaxData['status'] = 'Pending';
												$ajaxData['on'] = '-';
												$ajaxData['changedBy'] = '-';
											}


											$dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'I')->where('hawb_id', $items->id)->orderBy('id', 'desc')->get();
											if (count($dataComments) > 0)
												$ajaxData['comments'] = $dataComments;
											else
												$ajaxData['comments'] = '';
											?>

											<div class="step3custominspection-ajax-container step3custominspection-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
												<div style="float:left;width:100%;margin-bottom:10px;">
													<div style="float: left;margin-right: 10px;"><span style="color:#000">Custom Inspection :</span> {{$ajaxData['status']}}</div>
													<?php if (!empty($items->inspection_flag)) { ?>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
													<?php } ?>
												</div>
												<div style="float:left;width:100%">
													<h3 style="background: #efefef;border-bottom: 1px solid #000;padding: 10px;width: 100%;float: left;font-size: 15px;margin:0px">Comments</h3>
													<?php if (!empty($ajaxData['comments'])) {
														foreach ($ajaxData['comments'] as $k => $v) { ?>
															<div style="width:70%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;">{{$v->notes}}</div>
															<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">On</span><span>{{!empty($v->created_on) ? date('d-m-Y',strtotime($v->created_on)) : '-'}}</span></div>
															<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">By</span><span>
																	<?php
																	$dataUser = app('App\User')->getUserName($v->created_by);
																	if (!empty($dataUser))
																		echo $dataUser->name;
																	else
																		echo '-';
																	?></span></div>
														<?php }
													} else { ?>
														<div style="padding-left: 15px">No comment</div>
													<?php } ?>
												</div>
											</div>
										</div>
										{{ Form::close() }}
									</div>

									<?php
									//$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkInvoiceIsGeneratedOrNot($items->id, 'housefile');
									$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkAllInvoiceIsGeneratedOrNot($items->id, 'housefile');
									$checkIfAnyInvoicePending = app('App\Invoices')->checkIfAnyInvoicePending($items->id, 'housefile');
									$billingPartyData = app('App\Clients')->getClientData($items->billing_party);
									?>
									<div id="div_step4-<?php echo $items->id; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) ? 'background:#ccc;border: none;' : ''; ?>">4. Invoice & Payment</div>

									<div class="detail-container">
										<?php if (empty($items->billing_party)) { ?>
											<div style="width: 100%;color:red"><b>Note: </b>Billing party has not assigned to this file. please assign and generate an invoice.</div>
										<?php } ?>
										<?php $actionUrl = url('warehouse/cargo/step4invoiceandpayment'); ?>
										{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'step4invoiceandpayment','autocomplete'=>'off')) }}
										{{ csrf_field() }}
										<div style="margin-top:0px;float:left;width:100%">
											<?php /* if ($items->shipment_status != 1) { ?>
												<h4 style="color:red">Shipent has not been received in the warehouse</h4>
											<?php } else {  */ ?>

											<div style="width:50%;margin-bottom:10px;margin-top:10px;float:left">
												<?php
												$getTotalDaysOfCharge = app('App\Invoices')->getTotalDaysOfCharge($items->id, 'housefile');
												$getTotalChargableDays = app('App\Invoices')->getTotalChargableDays($items->id, 'housefile');
												$getTotalBilledDays = app('App\Invoices')->getTotalBilledDays($items->id, 'housefile');
												?>
												<span style="color:#000">Total Days : <?php echo $getTotalDaysOfCharge; ?></span> |
												<span style="color:#000">Total Chargeable Days : <?php echo $getTotalChargableDays; ?></span> |
												<span style="color:#000">Billed Days : <?php echo $getTotalBilledDays; ?></span> |
												<span style="color:#000">Remaining Days : <?php echo ($getTotalChargableDays - $getTotalBilledDays < 0) ? 0 : $getTotalChargableDays - $getTotalBilledDays; ?></span>
											</div>

											<?php if ($getTotalChargableDays - $getTotalBilledDays > 0) { ?>
												<div style="float:left;width:23%">
													<div style="float: left;font-size: 20px;color: green;margin-right: 5px;"><i data-id="<?php echo $items->id; ?>" class="fa fa-calendar fa-calendar-invoiceandpayment"></i></div>

													<div style="width:40%;float:left;margin-right:10px;" class="invoice_date_div-<?php echo $items->id; ?>">
														<?php echo Form::text('invoice_date', date('d-m-Y'), ['class' => 'form-control datepicker invoice_date invoice_date-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Date']); ?>
													</div>

													<?php $cssDisable = '';
													if (empty($items->billing_party)) {
														$cssDisable = 'pointer-events: none;opacity: 0.5;';
													} ?>
													<a style="<?php echo $cssDisable; ?>" title="Click here to generate" target="_blank" href="javascript:void(0)"><span style="border-radius:0px" class="btn btn-success generatehousefileinvoice" data-revise='0' data-id='<?php echo $items->id; ?>'>Generate</a>
												</div>

												<a style="display:none" title="Click here to Revise" target="_blank" href="javascript:void(0)"><span style="border-radius:0px" class="btn btn-success generatehousefileinvoice" data-revise='1' data-id='<?php echo $items->id; ?>'>Revise Invoice</a>
											<?php } ?>

											<div style="width:20%;float:left;margin-right: 19px;">
												<?php echo Form::text('custom_invoice_number', $items->custom_invoice_number, ['class' => 'form-control custom_invoice_number custom_invoice_number-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Custom Invoice Number', 'title' => 'Custom Invoice Number']); ?>
											</div>

											<div style="width:5%;float:left;">
												<button type="button" class="btn btn-success btn-step4invoiceandpayment btn-step4invoiceandpayment-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
											</div>

											<?php if (count($checkInvoiceIsGeneratedOrNot) != 0) { ?>
												<div style="float:left;width:100%">
													<table class="table">
														<thead>
															<tr>
																<th>Invoice Number</th>
																<th>Date</th>
																<th>Amount</th>
																<th>Payment Status</th>
																<th>Payment Date</th>
																<th>Action</th>
															</tr>
														</thead>
														<tbody>
															<?php foreach ($checkInvoiceIsGeneratedOrNot as $k => $v) { ?>
																<tr>
																	<td>{{$v->bill_no}}</td>
																	<td>{{date('d-m-Y',strtotime($v->date))}}</td>
																	<td style="">{{number_format($v->total,2)}}</td>
																	<td style="<?php echo ($v->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$v->payment_status}}</td>
																	<td>{{$v->payment_status == 'Paid' ? date('d-m-Y',strtotime($v->payment_received_on)) : '-'}}</td>
																	<td><a style=" margin-left: 10px;" title="Click here to print" target="_blank" href="{{ route('printinvoice',[$v->id,'housefile']) }}"><span style="border-radius:0px" class="btn btn-success"><i class="fa fa-print"></i> Print</span></a></td>
																</tr>
															<?php } ?>
														</tbody>
													</table>
												</div>
											<?php } ?>
											<?php //} 
											?>




											<?php
											/* 	$ajaxData = array();
											if (!empty($checkInvoiceIsGeneratedOrNot) && $checkInvoiceIsGeneratedOrNot->payment_status == 'Paid') {
												$ajaxData['status'] = 'Paid';
												$ajaxData['on'] = date('d-m-Y', strtotime($checkInvoiceIsGeneratedOrNot->payment_received_on));
												$dataUser = app('App\User')->getUserName($checkInvoiceIsGeneratedOrNot->payment_received_by);
												if (!empty($dataUser))
													$ajaxData['changedBy'] = $dataUser->name;
												else
													$ajaxData['changedBy'] = '-';
											} else {
												if (!empty($checkInvoiceIsGeneratedOrNot))
													$ajaxData['status'] = $checkInvoiceIsGeneratedOrNot->payment_status;
												else
													$ajaxData['status'] = 'Pending';

												$ajaxData['on'] = '-';
												$ajaxData['changedBy'] = '-';
											} */
											?>

											<!-- <div class="step4invoiceandpayment-ajax-container step4invoiceandpayment-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
												<div style="float:left;width:100%;margin-bottom:10px;">
													<div style="float: left;margin-right: 10px;"><span style="color:#000">Payment Status :</span> {{$ajaxData['status']}}</div>
													<?php //if (!empty($checkInvoiceIsGeneratedOrNot) && $checkInvoiceIsGeneratedOrNot->payment_status == 'Paid') { 
													?>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
													<?php //} 
													?>
												</div>
											</div> -->
										</div>
										{{ Form::close() }}
									</div>

									<div id="div_step5-<?php echo $items->id; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo $items->warehouse_status != '3' ? 'background:#ccc;border: none;' : ''; ?>">5. Shipment Release</div>

									<div class="detail-container">
										<?php
										$varDisabledReleaseButton = '';
										//if (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) {
										if ((empty($billingPartyData) || $billingPartyData->cash_credit == 'Cash') && $checkIfAnyInvoicePending != 0) {
											$varDisabledReleaseButton = 'disabled'; ?>
											<div style="width: 100%;color:red;margin-bottom:10px"><b>Note: </b>Please check the invoices. It's is still pending to pay or not generated</div>
										<?php } ?>
										<?php $actionUrl = url('warehouse/cargo/step5shipmentrelease'); ?>
										{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'step5shipmentrelease','autocomplete'=>'off')) }}
										{{ csrf_field() }}
										<div style="margin-top:0px;float:left;width:100%">
											<div style="float:left;width:30%;margin-right: 19px;">
												<div style="float:left;margin-right:15px">
													<span style="margin-top: 5px;float: left;">Release By Customer:</span>
												</div>
												<div style="float:left;margin-right:10px">
													<?php echo Form::text('release_by_customer', $items->release_by_customer, ['class' => 'form-control release_by_customer release_by_customer-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Customer Name', 'title' => 'Customer Name']); ?>
												</div>
											</div>


											<div style="width:63%;float:left;">
												<div style="float:left;margin-right:15px">
													<span style="margin-top: 5px;float: left;">Release By CSS</span>
												</div>

												<div style="float:left;margin-right:15px;width: 40%;">
													<?php echo Form::text('release_by_css_agent', $items->release_by_css_agent, ['class' => 'form-control release_by_css_agent release_by_css_agent-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Agent Name', 'title' => 'Agent Name']); ?>
												</div>

												<div style="float:left;margin-right:15px;width: 40%;">
													<?php echo Form::text('release_by_css_driver', $items->release_by_css_driver, ['class' => 'form-control release_by_css_driver release_by_css_driver-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Driver Name', 'title' => 'Driver Name']); ?>
												</div>
											</div>

											<div style="width:5%;float:left;">
												<button type="button" <?php echo $varDisabledReleaseButton; ?> class="btn btn-success btn-step5shipmentrelease btn-step5shipmentrelease-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
											</div>

											<?php
											if ($items->warehouse_status == '3') {
												$ajaxData['status'] = 'Done';
												$ajaxData['on'] = date('d-m-Y', strtotime($items->shipment_delivered_date));
												$dataUser = app('App\User')->getUserName($items->release_by);
												if (!empty($dataUser))
													$ajaxData['changedBy'] = $dataUser->name;
												else
													$ajaxData['changedBy'] = '-';
											} else {
												$ajaxData['status'] = 'Pending';
											}
											?>

											<div class="step5shipmentrelease-ajax-container step5shipmentrelease-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
												<?php if ($items->warehouse_status == '3') { ?>
													<div style="float: left;width: 100%;margin-bottom: 10px;">
														<a style="" title="Release Receipt" href="javascript:void(0)"><span style="border-radius:0px" class="btn btn-success generatereleasereceipt" data-id='<?php echo $items->id; ?>'>Release Receipt</a>
													</div>
												<?php } ?>
												<div style="float:left;width:100%;margin-bottom:10px;">
													<div style="float: left;margin-right: 10px;"><span style="color:#000">Shipment Release :</span> {{$ajaxData['status']}}</div>
													<?php if ($items->warehouse_status == '3') { ?>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
														<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
													<?php } ?>
												</div>
											</div>
										</div>
										{{ Form::close() }}
									</div>


								</div>
							</div>
						</div>
					</div>
				<?php $i++;
				} ?>

			<?php  } ?>

		</div>



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
			$('#loading').show();
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

		$('#hawb_scan_status').change(function() {
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