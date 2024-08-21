<?php

use App\Warehouse;

$warehouse = new Warehouse;
?>
@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('breadcrumbs')
@include('menus.warehouse-aeropost-files')
@stop

@section('content')
@if(Session::has('flash_message'))
<div class="alert alert-success flash-success">
	{{ Session::get('flash_message') }}
</div>
@endif
<section class="content-header" style="float: left;width:100%">
	<h1>
		<div style="float: left"><?php echo 'Aerpost - ' . $items->file_number; ?></div>
		<?php if ($items->file_close == 1) { ?>
			<div style="color:red;float:right">CLOSED</div>
		<?php } else { ?>
			<div style="float: right;margin-right: 15px;color: green;"> File Status : <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($items->aeropost_scan_status) ? $items->aeropost_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($items->aeropost_scan_status) ? $items->aeropost_scan_status : '-'] : '-'; ?>
			</div>
		<?php } ?>
	</h1>
</section>
<section class="content editupscontainer" style="float: left;width:100%">
	<div class="box box-success">
		<div class="box-body">

			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger flash-danger">
				{{ Session::get('flash_message_error') }}
			</div>
			@endif

			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
			<div class="row">
				<div class="col-md-1">
					<a class="btn round orange btn-warning" href="{{route('viewcourieraeropostdetailforwarehousemaster',[$masterId])}}">Back</a>
				</div>
			</div>

			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Master File Details</div>
			<div class="detail-container basicDetaiCls">
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB/BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->tracking_number) ? $model->tracking_number : '-'; ?></span>
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
					<span class="viewblk1">Explications : </span>
					<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Arrival Date : </span>
					<span class="viewblk2"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Weight : </span>
					<span class="viewblk2"><?php echo !empty($model->weight) ? $model->weight : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Volumne : </span>
					<span class="viewblk2"><?php echo !empty($model->volume) ? $model->volume : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Number of Pieces : </span>
					<span class="viewblk2"><?php echo !empty($model->pieces) ? (int) $model->pieces : '-'; ?></span>
				</div>
			</div>

			<?php if (checkNonBoundedWH() == 'Yes' && $items->display_notification_nonbounded_wh == 1) { ?>
				<a style="margin-top: 10px" class="btn btn-success" href="{{route('acceptfilessubmit',[$items->id,'Aeropost','aeropost'])}}">Receive Shipment</a>
			<?php } else {
			?>
				<?php $i = 1; ?>
				<div id="div_basicdetails-<?php echo $i; ?>" class="notes box-s" style="margin-top:12px;margin-bottom:0px;">
					<div style="float:left">House AWB Details</div>
					<div style="float: left;width: 20%;margin-left: 3%;">
						<span>File Number : </span>
						<span><?php echo !empty(trim($items->file_number)) ? $items->file_number : "-"; ?></span>
					</div>
					<div style="float: right;margin-top: -20px;width: 100%;text-align: right;margin-right: 1%;">
						<span style="width:100%" data-id="<?php echo $i; ?>" class="fa fa-minus fa-expand-collapse fa-expand-collapse-<?php echo $i; ?>"></span>
					</div>
				</div>
				<div class="detail-container detail-container-hawb detail-container-hawb-<?php echo $i; ?>">
					<div class="row-<?php echo $i; ?>">
						<div class="col-md-12" style="padding:0px">

							<div class="col-md-12 basicDetaiCls" style="padding: 0px;margin-bottom: 15px;">
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">No. Dossier/ File No. : </span>
									<span class="viewblk2"><?php echo !empty($items->file_number) ? $items->file_number : '-' ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Tracking No : </span>
									<span class="viewblk2"><?php echo $items->tracking_no; ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Shipper : </span>
									<span class="viewblk2"><?php echo $items->from_location; ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Consignataire / Consignee : </span>
									<span class="viewblk2">
										<?php $data = app('App\Clients')->getClientData($items->consignee);
										echo !empty($data->company_name) ? $data->company_name : '-'; ?>
									</span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Shipper Phone No : </span>
									<span class="viewblk2"><?php echo $items->from_phone; ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Consignee Phone No : </span>
									<span class="viewblk2"><?php echo $items->consignee_phone; ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Shipper Address : </span>
									<span class="viewblk2"><?php echo !empty($items->from_address) ? $items->from_address : '-'; ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Consignee Address : </span>
									<span class="viewblk2"><?php echo !empty($items->consignee_address) ? $items->consignee_address : '-'; ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Weight : </span>
									<span class="viewblk2"><?php echo !empty($items->real_weight) ? $items->real_weight : '-'; ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Total Weight : </span>
									<span class="viewblk2"><?php echo !empty($items->shipment_real_weight) ? $items->shipment_real_weight : '-'; ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Total Pieces : </span>
									<span class="viewblk2"><?php echo !empty($items->total_pieces) ? (int) $items->total_pieces : '-' ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Arrival Date : </span>
									<span class="viewblk2"><?php echo date('d-m-Y', strtotime($items->date)); ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Freight : </span>
									<span class="viewblk2"><?php echo !empty($items->freight) ? $items->freight : '-' ?></span>
								</div>
								<div style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Total Freight : </span>
									<span class="viewblk2"><?php echo !empty($items->total_freight) ? '$' . $items->total_freight : '-' ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Warehouse : </span>
									<span class="viewblk2">
										<?php $warehouseData = $warehouse->getData($items->warehouse);
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
									<span class="viewblk2"><?php echo !empty($items->description) ? $items->description : '-' ?></span>
								</div>
								<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
									<span class="viewblk1">Master File Number : </span>
									<span class="viewblk2"><?php echo !empty($items->master_file_number) ? $items->master_file_number : '-' ?></span>
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


								<div class="pipe pipe-<?php echo $items->move_to_nonbounded_wh == '1' ? 'done' : 'pending'; ?>"></div>
								<div class="step-3 steps step-<?php echo $items->move_to_nonbounded_wh == '1' ? 'done' : 'pending'; ?>">
									<div class="inner-round inner-round-<?php echo $items->move_to_nonbounded_wh == '1' ? 'done' : 'pending'; ?>">
									</div>
								</div>

								<div class="pipe pipe-<?php echo !empty($items->delivery_boy)  ? 'done' : 'pending'; ?>"></div>
								<div class="step-4 steps step-<?php echo !empty($items->delivery_boy)  ? 'done' : 'pending'; ?>">
									<div class="inner-round inner-round-<?php echo !empty($items->delivery_boy)  ? 'done' : 'pending'; ?>">
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
									<div style="padding-left: 20%;">Move to Nonbounded Warehouse</div>
									<div style="padding-left: 40%;"><?php echo $items->move_to_nonbounded_wh == 1 ? date('d-m-Y', strtotime($items->move_to_nonbounded_wh_on)) : '' ?></div>
								</div>
								<div style="float: left;text-align: left;width: 25%;">
									<div style="padding-left: 40%;">Assign to delivery boy</div>
									<div style="padding-left: 52%;"><?php echo !empty($items->delivery_boy) ? date('d-m-Y', strtotime($items->delivery_boy_assigned_on)) : '' ?></div>
								</div>
							</div>

							<div class="col-md-12" style="padding: 0px 0px;margin-bottom: 15px;">
								<div id="div_step1" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo $items->shipment_status != 1 ? 'background:#ccc;border: none;' : ''; ?>">1. Shipment Status</div>

								<div class="detail-container">
									<?php $actionUrl = url('warehouse/aeropost/aeropoststep1shipmentstatus'); ?>
									{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'aeropoststep1shipmentstatus','autocomplete'=>'off')) }}
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
											<button type="button" class="btn btn-success btn-aeropoststep1shipmentstatus btn-aeropoststep1shipmentstatus-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
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

										$dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'V')->where('aeropost_id', $items->id)->orderBy('id', 'desc')->get();
										if (count($dataComments) > 0)
											$ajaxData['comments'] = $dataComments;
										else
											$ajaxData['comments'] = '';

										?>

										<div class="aeropoststep1shipmentstatus-ajax-container aeropoststep1shipmentstatus-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
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
														<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">By</span><span><?php
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

								<div id="div_step2" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo empty($items->inspection_flag) ? 'background:#ccc;border: none;' : ''; ?>">2. Custom Inspection</div>

								<div class="detail-container">
									<?php $actionUrl = url('warehouse/aeropost/aeropoststep2custominspection'); ?>
									{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'aeropoststep2custominspection','autocomplete'=>'off')) }}
									{{ csrf_field() }}
									<div style="margin-top:0px;float:left;width:100%">
										<div style="width:15%;float:left;margin-right:10px;">
											<span class="custominspection-span" style="background: #e8e8e8;padding: 0px;width: 100%;float: left;">
												<?php echo Form::checkbox('inspection_flag', null, $items->inspection_flag == '1' ? 'checked' : '', array('id' => 'inspection_flag-' . $items->id, 'class' => 'inspection_flag inspection_flag-' . $items->id, 'data-id' => $items->id)); ?>
												<label for="inspection_flag-<?php echo $items->id; ?>">Custom Inspection</label>
											</span>
										</div>

										<div style="float: left;font-size: 20px;color: green;margin-right: 5px;<?php echo !empty($items->inspection_flag) ? 'display:block' : 'display:none' ?>"><i data-id="<?php echo $items->id; ?>" class="fa fa-calendar fa-calendar-custominspection"></i></div>

										<div style="width:10%;float:left;margin-right:10px;<?php echo $items->inspection_flag == 1 ? 'display:block' : 'display:none'; ?>" class="inspection_date_div-<?php echo $items->id; ?>">
											<?php echo Form::text('inspection_date', !empty($items->inspection_date) ? date('d-m-Y', strtotime($items->inspection_date)) :  date('d-m-Y'), ['class' => 'form-control datepicker inspection_date inspection_date-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Date']); ?>
										</div>

										<div style="width:15%;float:left;margin-right:10px;">
											<?php echo Form::select('inspection_file_status', Config::get('app.customInspectionFileStatus'), $items->inspection_file_status, ['class' => 'form-control selectpicker inspection_file_status inspection_file_status-' . $items->id, 'data-live-search' => 'true', 'data-container' => 'body']); ?>
										</div>

										<div style="width:15%;float:left;margin-right: 19px;">
											<?php echo Form::text('custom_file_number', $items->custom_file_number, ['class' => 'form-control custom_file_number custom_file_number-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Custom File Number', 'title' => 'Custom File Number']); ?>
										</div>

										<div style="width:30%;float:left;">
											<?php echo Form::text('shipment_notes_inspection', '', ['class' => 'form-control shipment_notes_inspection shipment_notes_inspection-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Comment']); ?>
										</div>

										<div style="width:5%;float:left;">
											<button type="button" class="btn btn-success btn-aeropoststep2custominspection btn-aeropoststep2custominspection-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
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


										$dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'I')->where('aeropost_id', $items->id)->orderBy('id', 'desc')->get();
										if (count($dataComments) > 0)
											$ajaxData['comments'] = $dataComments;
										else
											$ajaxData['comments'] = '';
										?>

										<div class="aeropoststep2custominspection-ajax-container aeropoststep2custominspection-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
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
														<div style="width:15%;float:left;padding-left: 15px;padding-bottom: 5px;padding-top: 5px;"><span style="margin-right:15px;color:#000">By</span><span><?php
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

								<div id="div_step3" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo $items->move_to_nonbounded_wh != '1' ? 'background:#ccc;border: none;' : ''; ?>">3. Move to Nonbounded Warehouse</div>

								<div class="detail-container">
									<div style="float:left;width:100%;margin-bottom:10px;">
										<div style="float: left;margin-right: 10px;"><span style="color:#000">Custom Inspection Status:</span> {{!empty($items->inspection_file_status || $items->inspection_file_status == '0') ? Config::get('app.customInspectionFileStatus')[$items->inspection_file_status] : '-'}}</div>
									</div>

									<?php $actionUrl = url('warehouse/aeropost/aeropoststep3movetononboundedwh'); ?>
									{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'aeropoststep3movetononboundedwh','autocomplete'=>'off')) }}
									{{ csrf_field() }}
									<div style="margin-top:0px;float:left;width:100%">
										<div style="width:22%;float:left;margin-right:10px;">
											<span class="custominspection-span" style="background: #e8e8e8;padding: 0px;width: 100%;float: left;">
												<?php echo Form::checkbox('move_to_nonbounded_wh', null, $items->move_to_nonbounded_wh == '1' ? 'checked' : '', array('id' => 'move_to_nonbounded_wh-' . $items->id, 'class' => 'move_to_nonbounded_wh move_to_nonbounded_wh-' . $items->id, 'data-id' => $items->id)); ?>
												<label for="move_to_nonbounded_wh-<?php echo $items->id; ?>">Move to Nonbounded Warehouse</label>
											</span>
										</div>

										<div style="width:5%;float:left;">
											<button type="button" class="btn btn-success btn-aeropoststep3movetononboundedwh btn-aeropoststep3movetononboundedwh-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
										</div>

										<?php
										$ajaxData = array();
										if (!empty($items->move_to_nonbounded_wh)) {
											$ajaxData['status'] = 'Assigned';
											$ajaxData['on'] = date('d-m-Y', strtotime($items->move_to_nonbounded_wh_on));
											$dataUser = app('App\User')->getUserName($items->move_to_nonbounded_wh_by);
											if (!empty($dataUser))
												$ajaxData['changedBy'] = $dataUser->name;
											else
												$ajaxData['changedBy'] = '-';
										} else {
											$ajaxData['status'] = 'Not Assigned';
											$ajaxData['on'] = '-';
											$ajaxData['changedBy'] = '-';
										}

										if (!empty($items->nonbounded_wh_confirmation)) {
											$ajaxDataWHConfirmation['status'] = Config::get('app.NonBoundedWarehouseConfirmation')[$items->nonbounded_wh_confirmation];
											$ajaxDataWHConfirmation['on'] = date('d-m-Y', strtotime($items->nonbounded_wh_confirmation_on));
											$dataUser = app('App\User')->getUserName($items->nonbounded_wh_confirmation_by);
											if (!empty($dataUser))
												$ajaxDataWHConfirmation['changedBy'] = $dataUser->name;
											else
												$ajaxDataWHConfirmation['changedBy'] = '-';
										} else {
											$ajaxDataWHConfirmation['status'] = 'Pending';
											$ajaxDataWHConfirmation['on'] = '-';
											$ajaxDataWHConfirmation['changedBy'] = '-';
										}
										?>

										<div class="aeropoststep3movetononboundedwh-ajax-container aeropoststep3movetononboundedwh-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
											<div style="float:left;width:100%;margin-bottom:10px;">
												<div style="float: left;margin-right: 10px;"><span style="color:#000">{{$ajaxData['status']}}</span></div>
												<?php if (!empty($items->move_to_nonbounded_wh)) { ?>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
												<?php } ?>

												<div style="float: left;margin-right: 10px;margin-left: 20px;"><span style="color:#000">Nonbounded Warehouse Confirmation : </span>{{$ajaxDataWHConfirmation['status']}}</div>
												<?php if (!empty($items->nonbounded_wh_confirmation)) { ?>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">On : </span> {{$ajaxDataWHConfirmation['on']}}</div>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">By : </span> {{$ajaxDataWHConfirmation['changedBy']}}</div>
												<?php } ?>
											</div>

										</div>
									</div>
									{{ Form::close() }}
								</div>

								<?php //if (checkNonBoundedWH() == 'Yes') { 
								?>
								<?php
								//$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkInvoiceIsGeneratedOrNot($items->id, 'aeropost');
								$checkInvoiceIsGeneratedOrNot = app('App\Invoices')->checkAllInvoiceIsGeneratedOrNot($items->id, 'aeropost');
								$checkIfAnyInvoicePending = app('App\Invoices')->checkIfAnyInvoicePending($items->id, 'aeropost');
								?>
								<div id="div_step4" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo (count($checkInvoiceIsGeneratedOrNot) <= 0 || $checkIfAnyInvoicePending != 0) ? 'background:#ccc;border: none;' : ''; ?>">4. Invoice & Payment</div>

								<div class="detail-container">
									<?php if (empty($items->billing_party)) { ?>
										<div style="width: 100%;color:red"><b>Note: </b>Billing party has not assigned to this file. please assign and generate an invoice.</div>
									<?php } ?>
									<?php $actionUrl = url('warehouse/aeropost/aeropoststep4invoiceandpayment'); ?>
									{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'aeropoststep4invoiceandpayment','autocomplete'=>'off')) }}
									{{ csrf_field() }}
									<div style="margin-top:0px;float:left;width:100%">

										<div style="width:50%;margin-bottom:10px;margin-top:10px;float:left">
											<?php
											$getTotalDaysOfCharge = app('App\Invoices')->getTotalDaysOfCharge($items->id, 'aeropost');
											$getTotalChargableDays = app('App\Invoices')->getTotalChargableDays($items->id, 'aeropost');
											$getTotalBilledDays = app('App\Invoices')->getTotalBilledDays($items->id, 'aeropost');
											?>
											<span style="color:#000">Total Days : <?php echo $getTotalDaysOfCharge; ?></span> |
											<span style="color:#000">Total Chargeable Days : <?php echo $getTotalChargableDays; ?></span> |
											<span style="color:#000">Billed Days : <?php echo $getTotalBilledDays; ?></span> |
											<span style="color:#000">Remaining Days : <?php echo $getTotalChargableDays - $getTotalBilledDays; ?></span>
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
											<button type="button" class="btn btn-success btn-aeropoststep4invoiceandpayment btn-aeropoststep4invoiceandpayment-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" style="border-radius: 0px;height: 34px;">Save</button>
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
																<td style="">{{$v->total}}</td>
																<td style="<?php echo ($v->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$v->payment_status}}</td>
																<td>{{$v->payment_status == 'Paid' ? date('d-m-Y',strtotime($v->payment_received_on)) : '-'}}</td>
																<td><a style=" margin-left: 10px;" title="Click here to print" target="_blank" href="{{ route('printinvoice',[$v->id,'aeropost']) }}"><span style="border-radius:0px" class="btn btn-success"><i class="fa fa-print"></i> Print</span></a></td>
															</tr>
														<?php } ?>
													</tbody>
												</table>
											</div>
										<?php } ?>
									</div>
									{{ Form::close() }}
								</div>

								<div id="div_step5" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;<?php echo empty($items->delivery_boy) ? 'background:#ccc;border: none;' : ''; ?>">5. Assign to Delivery Boy</div>

								<div class="detail-container">
									<?php $actionUrl = url('warehouse/aeropost/aeropoststep5assigndeliveryboy'); ?>
									{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form client-side','id'=>'aeropoststep3movetononboundedwh','autocomplete'=>'off')) }}
									{{ csrf_field() }}
									<div style="margin-top:0px;float:left;width:100%">
										<div style="width:15%;float:left;margin-right:10px;">
											<?php echo Form::select('delivery_boy', $deliveryBoys, $items->delivery_boy, ['class' => 'form-control selectpicker delivery_boy delivery_boy-' . $items->id, 'data-id' => $items->id, 'data-live-search' => 'true', 'data-container' => 'body', 'placeholder' => 'Select Delivery Boy']); ?>
										</div>
										<div style="width:5%;float:left;">
											<button type="button" class="btn btn-success btn-aeropoststep5assigndeliveryboy btn-aeropoststep5assigndeliveryboy-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" data-reason='0' style="border-radius: 0px;height: 34px;">Save</button>
										</div>

										<?php
										$ajaxData = array();
										if (!empty($items->delivery_boy)) {
											$dataDeliveryBoy = app('App\DeliveryBoy')->getDeliveryBodData($items->delivery_boy);
											$ajaxData['status'] = !empty($dataDeliveryBoy) ? $dataDeliveryBoy->name : '-';
											$ajaxData['on'] = date('d-m-Y', strtotime($items->delivery_boy_assigned_on));
											$dataUser = app('App\User')->getUserName($items->delivery_boy_assigned_by);
											if (!empty($dataUser))
												$ajaxData['changedBy'] = $dataUser->name;
											else
												$ajaxData['changedBy'] = '-';
										} else {
											$ajaxData['status'] = '-';
											$ajaxData['on'] = '-';
											$ajaxData['changedBy'] = '-';
										}
										?>

										<div class="aeropoststep5assigndeliveryboy-ajax-container aeropoststep5assigndeliveryboy-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
											<div style="float:left;width:100%;margin-bottom:10px;">
												<?php if (!empty($items->delivery_boy)) { ?>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">Assigned To : </span> {{$ajaxData['status']}}</div>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">On :</span> {{$ajaxData['on']}}</div>
													<div style="float: left;margin-right: 10px;"><span style="color:#000">By :</span> {{$ajaxData['changedBy']}}</div>
												<?php } ?>
											</div>
										</div>

										<div style="width:15%;float:left;margin-right:10px;">
											<?php echo Form::select('aeropost_scan_status', Config::get('app.ups_new_scan_status'), $items->aeropost_scan_status, ['class' => 'form-control selectpicker aeropost_scan_status aeropost_scan_status-' . $items->id, 'data-id' => $items->id, 'data-live-search' => 'true', 'data-container' => 'body', 'placeholder' => 'Select File Status']); ?>
										</div>
										<div style="width:30%;float:left;margin-right:10px;<?php echo $items->aeropost_scan_status == 7 ? 'display:block' : 'display:none' ?>" class="reason_for_return_div-<?php echo $items->id; ?>">
											<div style="width:100%;float:left;">
												<?php echo Form::select('reason_for_return', Config::get('app.reasonOfReturn'), $items->reason_for_return, ['class' => 'form-control selectpicker reason_for_return reason_for_return-' . $items->id, 'data-id' => $items->id, 'data-live-search' => 'true', 'data-container' => 'body', 'placeholder' => 'Select Reason']); ?>

											</div>
										</div>
										<div style="width:30%;float:left;margin-left:15px">
											<?php echo Form::text('shipment_notes_for_return', '', ['class' => 'form-control shipment_notes_for_return shipment_notes_for_return-' . $items->id, 'data-id' => $items->id, 'placeholder' => 'Enter Comment Here']); ?>
										</div>
										<div style=" width:5%;float:left;">
											<button type="button" class="btn btn-success btn-aeropoststep5assigndeliveryboy btn-aeropoststep5assigndeliveryboy-<?php echo $items->id; ?>" data-id="<?php echo $items->id; ?>" data-reason='1' style="border-radius: 0px;height: 34px;">Save</button>
										</div>

										<?php
										$ajaxData = array();
										$dataComments = DB::table('verification_inspection_notes')->where('flag_note', 'R')->where('aeropost_id', $items->id)->orderBy('id', 'desc')->get();
										if (count($dataComments) > 0)
											$ajaxData['comments'] = $dataComments;
										else
											$ajaxData['comments'] = '';
										?>

										<div class="aeropoststep5assigndeliveryboy-reason-ajax-container aeropoststep5assigndeliveryboy-reason-ajax-container-<?php echo $items->id; ?>" style="float:left;width:100%;margin-top:15px">
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
								<?php //} 
								?>
							</div>

						</div>
					</div>

				</div>
			<?php } ?>
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

		$('.aeropost_scan_status').change(function() {
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

		$('.btn-aeropoststep1shipmentstatus').click(function(e) {
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
			var urlz = '<?php echo url("warehouse/aeropost/aeropoststep1shipmentstatus"); ?>';
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
					'flagModule': 'aeropost',
					'flagModuleId': '<?php echo $items->id; ?>'
				},
				success: function(data) {
					$('#loading').hide();
					$('.aeropoststep1shipmentstatus-ajax-container-' + tId).html(data);
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

		$('.btn-aeropoststep2custominspection').click(function(e) {
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
			var urlz = '<?php echo url("warehouse/aeropost/aeropoststep2custominspection"); ?>';
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
					$('.aeropoststep2custominspection-ajax-container-' + tId).html(data);
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

		$('.btn-aeropoststep3movetononboundedwh').click(function(e) {
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
			var urlz = '<?php echo url("warehouse/aeropost/aeropoststep3movetononboundedwh"); ?>';
			$.ajax({
				url: urlz,
				type: 'POST',
				data: {
					'id': id,
					'move_to_nonbounded_wh': move_to_nonbounded_wh
				},
				success: function(data) {
					$('#loading').hide();
					$('.aeropoststep3movetononboundedwh-ajax-container-' + tId).html(data);
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

		$('.btn-aeropoststep4invoiceandpayment').click(function(e) {
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
			var urlz = '<?php echo url("warehouse/aeropost/aeropoststep4invoiceandpayment"); ?>';
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
		$('.btn-aeropoststep5assigndeliveryboy').click(function(e) {
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
				var urlz = '<?php echo url("warehouse/aeropost/aeropoststep5assigndeliveryboy"); ?>';
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
						$('.aeropoststep5assigndeliveryboy-ajax-container-' + tId).html(data);
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
				var aeropost_scan_status = $('.aeropost_scan_status-' + tId + " option:selected").val();
				var reason_for_return = $('.reason_for_return-' + tId + " option:selected").val();
				var shipment_notes = $('.shipment_notes_for_return-' + tId).val();
				var urlz = '<?php echo url("warehouse/aeropost/aeropoststep5assigndeliveryboy"); ?>';
				$.ajax({
					url: urlz,
					type: 'POST',
					data: {
						'id': id,
						'aeropost_scan_status': aeropost_scan_status,
						'reason_for_return': reason_for_return,
						'shipment_notes': shipment_notes,
						'reason': reason
					},
					success: function(data) {
						$('#loading').hide();
						$('.aeropoststep5assigndeliveryboy-reason-ajax-container-' + tId).html(data);
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
			var moduleId = '<?php echo $items->id; ?>';
			var flagModule = 'aeropost';
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
					var locationHref = '<?php echo url("warehouseaeropost/viewcourieraeropostdetailforwarehouse"); ?>';
					locationHref += '/' + moduleId;
					window.location.href = locationHref;
				},
			});
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

		$('.aeropost_scan_status_outside').change(function() {
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