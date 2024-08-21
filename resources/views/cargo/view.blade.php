@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('breadcrumbs')
@include('menus.cargo-files')
@stop

@section('content')
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
@if(Session::has('flash_message_check'))
<div class="alert alert-success flash-success">
	{{ Session::get('flash_message_check') }}
</div>
@endif
@if(Session::has('flash_message_fm'))
<div class="alert alert-success flash-alert">
	{{ Session::get('flash_message_fm') }}
</div>
@endif

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



			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
			<div class="" style="float: left;width: 100%;margin-bottom: 10px;">
				<?php if ($model->file_close != 1 && $model->deleted != 1) { ?>
					<div style="float:left;margin-right: 5px;">
						<a class="btn round orange btn-warning" href="{{route('editcargo',[$model->id,$model->cargo_operation_type])}}">Edit</a>
					</div>
				<?php } ?>
				<div class="" style="float:left;">
					<button class="btn btn-primary" id="upload-file-btn" value="{{url('files/upload',['cargo',$model->id])}}"><i class="fa fa-upload" aria-hidden="true" style="margin-right: 5px"></i>Upload Files</button>
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
						<span class="viewblk1">AWB / BL No. : </span>
						<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Consignataire / Consignee : </span>
						<span class="viewblk2">
							<?php $data = app('App\Clients')->getClientData($model->consignee_name);
							echo !empty($data->company_name) ? $data->company_name : '-'; ?>
						</span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Expediteur / Shipper : </span>
						<span class="viewblk2">
							<?php $data = app('App\Clients')->getClientData($model->shipper_name);
							echo !empty($data->company_name) ? $data->company_name : '-'; ?>
						</span>
					</div>
					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Address : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : $model->consignee_address; ?></span>
					</div>

					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Opening Date : </span>
						<span class="viewblk2"><?php echo date('d-m-Y', strtotime($model->opening_date)); ?></span>
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

					<?php if ($model->consolidate_flag == 1) { ?>
						<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
							<span class="viewblk1">Warehouse Status : </span>
							<span class="viewblk2" style="font-weight: bold;color: green;"><?php echo !empty($model->warehouse_status) ? Config::get('app.warehouseStatus')[$model->warehouse_status] : '-'; ?></span>
						</div>

						<?php if ($model->warehouse_status == 1) { ?>
							<div style="float: left;width: 50%; margin-bottom: 10px;">
								<span class="viewblk1">Shipment Received Date : </span>
								<span class="viewblk2" style="font-weight: bold;color: green;"><?php echo !empty($model->shipment_received_date) ? date('d-m-Y', strtotime($model->shipment_received_date)) : '-'; ?></span>
							</div>
					<?php }
					} ?>

					<?php if (!empty($model->warehouse)) { ?>
						<div style="float: left;width: 50%; margin-bottom: 10px;">
							<span class="viewblk1">Warehouse : </span>
							<span class="viewblk2"><?php $dataWarehouse = app('App\Warehouse')->getData($model->warehouse);
																			echo !empty($dataWarehouse->name) ? $dataWarehouse->name : "-"; ?></span>
						</div>
					<?php }  ?>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Arrival Date : </span>
						<span class="viewblk2"><?php echo !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '-'; ?></span>
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
						<span class="viewblk1">AWB / BL No. : </span>
						<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Consignataire / Consignee : </span>
						<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-' ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Expediteur / Shipper : </span>
						<span class="viewblk2"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></span>
					</div>

					<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Opening Date : </span>
						<span class="viewblk2"><?php echo date('d-m-Y', strtotime($model->opening_date)); ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">Address : </span>
						<?php echo !empty($model->shipper_address) ? $model->shipper_address : '-' ?>
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



					<?php if ($model->consolidate_flag == 1) { ?>
						<div style="float: left;width: 50%; margin-bottom: 10px;">
							<span class="viewblk1">Warehouse Status : </span>
							<span class="viewblk2" style="font-weight: bold;color: green;"><?php echo !empty($model->warehouse_status) ? Config::get('app.warehouseStatus')[$model->warehouse_status] : '-'; ?></span>
						</div>

						<?php if ($model->warehouse_status == 1) { ?>
							<div style="float: left;width: 50%; margin-bottom: 10px;">
								<span class="viewblk1">Shipment Received Date : </span>
								<span class="viewblk2" style="font-weight: bold;color: green;"><?php echo !empty($model->shipment_received_date) ? date('d-m-Y', strtotime($model->shipment_received_date)) : '-'; ?></span>
							</div>
					<?php }
					} ?>

					<?php if (!empty($model->warehouse)) { ?>
						<div style="float: left;width: 50%; margin-bottom: 10px;">
							<span class="viewblk1">Warehouse : </span>
							<span class="viewblk2"><?php $dataWarehouse = app('App\Warehouse')->getData($model->warehouse);
																			echo !empty($dataWarehouse->name) ? $dataWarehouse->name : "-"; ?></span>
						</div>
					<?php }  ?>


				<?php } else { ?>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">No. Dossier/ File No. : </span>
						<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
					</div>
					<div style="float: left;width: 50%; margin-bottom: 10px;">
						<span class="viewblk1">AWB / BL No. : </span>
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



			<?php if (($id == 1 || $id == 2) && count($HouseAWBData) != 0) { ?>
				<div id="div_houseawbdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">House File Details</div>
				<div class="detail-container">
					<table class="table simpletable display nowrap" id="example3" style="width:100%">
						<thead>
							<tr>
								<th>House AWB No.</th>
								<th>File Number</th>
								<th>Consignee</th>
								<th>Shipper</th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1;
							foreach ($HouseAWBData as $k => $items) { ?>
								<tr data-editlink="{{ route('viewhawbfile',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
									<td><?php echo $items->cargo_operation_type == '1' ? $items->hawb_hbl_no : $items->export_hawb_hbl_no;  ?></td>
									<td><?php echo $items->file_number; ?></td>
									<td><?php echo !empty($items->consignee_name) ? App\Ups::getConsigneeName($items->consignee_name) : '-' ?></td>
									<td><?php echo !empty($items->shipper_name) ? App\Ups::getConsigneeName($items->shipper_name) : '-' ?></td>
								</tr>
							<?php $i++;
							} ?>
						</tbody>
					</table>
				</div>
			<?php } ?>

			@if(Session::has('flash_message'))
			<div class="alert alert-success flash-success" style="clear: both;">
				{{ Session::get('flash_message') }}
			</div>
			@endif
			<div id="div_expenses" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;"><span style="margin-top: 3px;
    float: left">Expenses/Cost</span>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<span style="display:none;margin-top: 3px;float: left;margin-left: 15px;"><b>Total:</b></span><span style="display:none;margin-top: 3px;float: left">&nbsp;<?php $totalExpense = App\Expense::getTotalExpenseOfCargo($rid);
																																																																																		echo !empty($totalExpense) ? $totalExpense : '0.00'; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>USD:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalExpenseOfUSD) ? number_format($totalExpenseOfUSD, 2) : '0.00'; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>HTG:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalExpenseOfHtg) ? number_format($totalExpenseOfHtg, 2) : '0.00'; ?></span>
				<?php if ($model->file_close != 1 && $model->deleted != 1) { ?>
					<div class="box-span">
						<a style="width: auto;float: left;color: #fff;margin-right: 10px;padding-top: 3px;height: 30px;    background-color: #3f7b30;border-color: #3f7b30;" class="btn btn-primary" href="{{ route('createexpenseusingawl',['cargo',$rid,'flagFromView']) }}">Add Expense</a>
						<div style="float: left">
							<a title="Click here to print" target="_blank" href="{{ route('getprintallexpense',[$model->id]) }}"><i style="background-color: #3f7b30;border-color: #3f7b30;" class="fa fa-print btn btn-primary"></i></a>
						</div>
					</div>
				<?php } ?>
			</div>


			<div class="detail-container">
				<table class="table simpletable display nowrap" id="example1" style="width:100%">
					<thead>
						<tr>
							<th style="display: none">ID</th>
							<th>
								<div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
							</th>
							<th>Date</th>
							<th>Voucher No.</th>
							<th>File No.</th>
							<th>AWB / BL No.</th>
							<th>Currency</th>
							<th>Total Amount</th>
							<th>Expediteur / Shipper</th>
							<th>Consignataire / Consignee</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($dataExpense)) {
							$i = 1;
							foreach ($dataExpense as $k => $items) {
								$dataClientUsingModuleId = App\Common::getClientDataUsingModuleId('cargo', $items->cargo_id);
								$items = (object) $items;
								$dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
								$cls = '';
								if ($dataExpenses > 0)
									$cls = 'fa fa-plus';

								$dataCargo = App\Cargo::getCargoData($items->cargo_id);
								if (empty($dataCargo))
									continue;

								if ($items->request_by_role == 12 || $items->request_by_role == 10)
									$edit =  route('editagentexpensesbyadmin', [$items->expense_id, 'flagFromView']);
								else
									$edit =  route('editexpensevoucher', [$items->expense_id, 'flagFromView']);
						?>
								<tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row">
									<td style="display: none">{{$items->expense_id}}</td>
									<td style="display: block;text-align: center;padding-top: 8px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
									<td>{{date('d-m-Y', strtotime($items->exp_date))}}</td>
									<td>{{$items->voucher_number}}</td>
									<td><?php $dataCargo = App\Cargo::getCargoData($items->cargo_id);
											echo $dataCargo->file_number;  ?></td>
									<td>{{$items->bl_awb}}</td>
									<td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
											echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
									<td style="text-align:right"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
									<td>{{$dataClientUsingModuleId['shipperName']}}</td>
									<td>{{$dataClientUsingModuleId['consigneeName']}}</td>
									<td>{{$items->expense_request}}</td>
									<td>
										<div class='dropdown'>
											<?php
											$delete =  route('deleteexpensevoucher', $items->expense_id);
											?>

											<a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;

											<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>


										</div>

									</td>

								</tr>
							<?php $i++;
							}
						} else { ?>
							<tr>
								<td>No data found.</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>

			<div id="div_invoice" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;"><span style="margin-top: 3px;
    float: left">Invoices</span>
				&nbsp;&nbsp;&nbsp;&nbsp;<span style="display:none;margin-top: 3px;float: left;margin-left: 15px;"><b>Total:</b></span><span style="display:none;margin-top: 3px;float: left">&nbsp;<?php $totalRevenue = App\Expense::getTotalRevenueOfCargo($rid);
																																																																																														echo $totalRevenue; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>USD:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalInvoiceOfUSD) ? number_format($totalInvoiceOfUSD, 2) : '0.00'; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>HTG:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalInvoiceOfHTG) ? number_format($totalInvoiceOfHTG, 2) : '0.00'; ?></span>
				<?php if ($model->file_close != 1 && $model->deleted != 1) { ?>
					<div class="box-span">
						<a class="btn btn-primary" style="width: auto;float: left;color: #fff;margin-right: 10px;padding-top: 3px;height: 30px;background-color: #3f7b30;border-color: #3f7b30;" href="{{ route('createinvoice',[$rid,'0','flagFromView']) }}">Add Invoice</a>
					</div>
				<?php } ?>
			</div>
			<div class="detail-container">
				<table class="table simpletable display nowrap" id="example" style="width:100%">
					<thead>
						<tr>
							<th style="display: none">ID</th>
							<th>Date</th>
							<th>Invoice No.</th>
							<th>File No.</th>
							<th>AWB / BL No.</th>
							<th>Billing Party</th>
							<th>Type</th>
							<th>Currency</th>
							<th>Total Amount</th>
							<th>Paid Amount</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($invoices as $items)
						<?php $edit =  route('editinvoice', [$items->id, 'flagFromView']); ?>
						<tr data-editlink="{{ $edit }}" id="<?php echo $items->id; ?>" class="edit-row">
							<td style="display: none">{{$items->id}}</td>
							<td>{{date('d-m-Y', strtotime($items->date))}}</td>
							<td>{{$items->bill_no}}</td>
							<td>{{$items->file_no}}</td>
							<td>{{$items->awb_no}}</td>
							<td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to);
									echo !empty($dataUser->company_name) ? $dataUser->company_name : "-"; ?></td>
							<td>{{$items->type_flag}}</td>
							<td><?php $dataCurrency = app('App\Currency')::getData($items->currency);
									echo !empty($dataCurrency->code) ? $dataCurrency->code : "-" ?></td>
							<td class="alignright">{{number_format($items->total,2)}}</td>
							<td class="alignright">{{number_format($items->credits,2)}}</td>
							<td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
							<td>
								<div class='dropdown'>
									<?php
									$delete =  url('invoices/delete', $items->id);
									$edit =  route('editinvoice', [$items->id, 'flagFromView']);
									?>

									<a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;

									<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>


								</div>

							</td>
						</tr>
						@endforeach

					</tbody>
				</table>
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
									$delete =  route('deletefiles', ['cargo', $model->id, serialize($files->file_name)]);
									$download =  route('downloadfiles', ['cargo', $model->id, serialize($files->file_name)]);
									?>
									<a href="javascript:void(0)" title="Download" data-value={{$download}} id="download"><i class="fa fa-download" aria-hidden="true"></i></a>&nbsp; &nbsp;
									<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
								</div>
							</td>
						</tr>
						<?php $i++; ?>
						@endforeach
						@endif
					</tbody>
				</table>
			</div>

			<div style="display: none" id="div_invoice" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;"><span style="margin-top: 3px;
		float: left">Check Guarantee to pay</span>
				<div class="box-span">
					<button style="border: 0px;background-color: #3f7b30;border-color: #3f7b30;border-radius: 4px;" id="addNewCheck" value="{{ route('createCheckGuarantee',['masterCargo',$rid]) }}" type="button" class="addNewCheck" data-module='Cost Item'>Add Check Guarantee to pay</button>
					<a class="btn btn-primary" style="display:none;width: auto;float: left;color: #fff;margin-right: 10px;padding-top: 3px;height: 30px;background-color: #3f7b30;border-color: #3f7b30;" href="{{ route('createCheckGuarantee',['masterCargo',$rid]) }}">Add Check Guarantee to pay</a>
				</div>
			</div>
			<div class="detail-container" style="display: none">
				<table class="table checkGaurenteeTable display nowrap" id="checkGaurenteeTable" style="width:100%">
					<thead>
						<tr>
							<th>Check Number</th>
							<th>Bank</th>
							<th>Amount</th>
							<th>Paid Status</th>
							<th>Date</th>
							<th>Detention Days</th>
							<th>DECSA Invoice Number</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($checkData)) { ?>
							@foreach ($checkData as $check)
							<?php $returnAction =  route('createCheckGuarantee', ['masterCargo', $rid, $check->id]); ?>
							<tr>
								<td>{{$check->check_number}}</td>
								<td></td>
								<td>{{$check->amount}}</td>
								<td>{{$check->paid == 1 ? 'Paid' : 'Unpaid'}}</td>
								<td>{{date('d-m-Y', strtotime($check->date))}}</td>
								<td>{{$check->detention_days_allowed}}</td>
								<td>{{$check->invoice_number}}</td>
								<td><button style="color: #fff;border: 0px;background-color: #3f7b30;border-color: #3f7b30;border-radius: 4px;" id="addNewCheck" value="{{ $returnAction }}" type="button" class="addNewCheck" data-module='Cost Item'>Return</button></td>
							</tr>
							@endforeach
						<?php } ?>
					</tbody>
				</table>
			</div>


			<div id="div_reports" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">P/L Report</div>
			<div class="detail-container" style="height: auto;max-height: 500px;overflow: auto;display:block">
				<div style="background: #6aa07163;font-weight: bold;float: left;width: 100%;border: 1px solid #ccc;">
					<div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Exc Rate</div>
					<div style="padding: 8px;width: 20%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Billing Item</div>
					<div style="padding: 8px;width: 11%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Revenue Amount</div>
					<div style="padding: 8px;width: 10%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Conversion</div>
					<div style="padding: 8px;width: 20%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Cost Item</div>
					<div style="padding: 8px;width: 11%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Expense Amount</div>
					<div style="padding: 8px;width: 10%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Conversion</div>
					<div style="padding: 8px;width: 11%;float: left;padding-left: 10px;">P/L</div>
				</div>

				<div style="float: left;width: 100%;border: 1px solid #ccc;">
					<?php
					$invoicesum = 0;
					$expensesum = 0;
					foreach ($finalReportData as $k => $v) {
						$invoicesumCurrentItem = 0;
						$expensesumCurrentItem = 0;
						if (isset($v['allData'])) {
							foreach ($v['allData'] as $k => $v1) {
					?>
								<div style="float: left;width: 100%;border-bottom: 1px solid #ccc;">
									<div style="padding: 8px;width: 7%;float: left;padding-left: 10px;"><?php echo $exchangeRateOfUsdToHTH; ?></div>

									<div style="padding: 8px;width: 20%;float: left;padding-left: 10px;"><?php echo !empty($v1->biliingItemDescription) ? $v1->biliingItemDescription : '&nbsp;'; ?></div>
									<div style="padding: 8px;width: 11%;float: left;padding-left: 10px;text-align:right">
										<?php
										echo !empty($v1->biliingItemAmount) ? $v1->billingCurrencyCode . ' ' . number_format($v1->biliingItemAmount, 2) : '&nbsp;';
										?></div>
									<div style="padding: 8px;width: 10%;float: left;padding-left: 10px;text-align:right">
										<?php
										if ($v1->billingCurrencyCode == 'HTG') {
											if (!empty($v1->biliingItemAmount)) {
												echo 'USD' . ' ' . number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2);

												$invoicesumCurrentItem += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
												$invoicesum += str_replace(',', '', number_format($v1->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
											} else {
												echo '&nbsp;';
											}
										} else {
											if (!empty($v1->biliingItemAmount)) {
												echo 'USD' . ' ' . number_format($v1->biliingItemAmount, 2);
												$invoicesumCurrentItem += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
												$invoicesum += str_replace(',', '', number_format($v1->biliingItemAmount, 2));
											} else {
												echo '&nbsp;';
											}
										} ?></div>

									<div style="padding: 8px;width: 20%;float: left;padding-left: 10px;"><?php echo !empty($v1->costDescription) ? $v1->costDescription : '&nbsp;'; ?></div>
									<div style="padding: 8px;width: 11%;float: left;padding-left: 10px;text-align:right"><?php echo !empty($v1->costAmount) ? $v1->costCurrencyCode . ' ' . $v1->costAmount : '&nbsp;'; ?></div>
									<div style="padding: 8px;width: 10%;float: left;padding-left: 10px;text-align:right">
										<?php
										if ($v1->costCurrencyCode == 'HTG') {
											if (!empty($v1->costAmount)) {
												echo 'USD' . ' ' . number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2);

												$expensesumCurrentItem += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
												$expensesum += str_replace(',', '', number_format($v1->costAmount / $exchangeRateOfUsdToHTH, 2));
											} else {
												echo '&nbsp;';
											}
										} else {
											if (!empty($v1->costAmount)) {
												echo 'USD' . ' ' . number_format($v1->costAmount, 2);
												$expensesumCurrentItem += str_replace(',', '', number_format($v1->costAmount, 2));
												$expensesum += str_replace(',', '', number_format($v1->costAmount, 2));
											} else {
												echo '&nbsp;';
											}
										}
										?></div>

									<div style="padding: 8px;width: 11%;float: left;padding-left: 10px;text-align:right">&nbsp;</div>
								</div>

							<?php } ?>
							<div style="background: #ceb33363;font-weight: bold;float: left;width: 100%;border-bottom: 1px solid #ccc;">
								<div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
								<div style="padding: 8px;width: 20%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
								<div style="padding: 8px;width: 11%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
								<div style="padding: 8px;width: 10%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($invoicesumCurrentItem,2)}}</div>
								<div style="padding: 8px;width: 20%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
								<div style="padding: 8px;width: 11%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
								<div style="padding: 8px;width: 10%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($expensesumCurrentItem,2)}}</div>
								<div style="text-align:right;font-weight:bold;padding: 8px;width: 11%;float: left;padding-left: 10px;color:<?php echo ($invoicesumCurrentItem - $expensesumCurrentItem) < 0 ? 'red' : 'green'; ?>">{{'USD'.' '.number_format($invoicesumCurrentItem-$expensesumCurrentItem,2)}}</div>
							</div>
					<?php }
					} ?>
					<div style="background: #000;color:#fff;font-weight: bold;float: left;width: 100%;">
						<div style="padding: 8px;width: 7%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">Total</div>
						<div style="padding: 8px;width: 20%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
						<div style="padding: 8px;width: 11%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
						<div style="padding: 8px;width: 10%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($invoicesum,2)}}</div>
						<div style="padding: 8px;width: 20%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
						<div style="padding: 8px;width: 11%;float: left;border-right: 1px solid #a59999;padding-left: 10px;">&nbsp;</div>
						<div style="padding: 8px;width: 10%;float: left;border-right: 1px solid #a59999;padding-left: 10px;text-align:right">{{'USD'.' '.number_format($expensesum,2)}}</div>
						<div style="text-align:right;font-weight:bold;padding: 8px;width: 11%;float: left;padding-left: 10px;color:<?php echo ($invoicesum - $expensesum) < 0 ? 'red' : 'green'; ?>">{{'USD'.' '.number_format($invoicesum-$expensesum,2)}}</div>
					</div>
				</div>
			</div>
			<div class="detail-container" style="display: none">
				<?php
				/* pre($getCostItemData,1);
					pre($getBillingItemData,1); */

				if (count($getCostItemData) > count($getBillingItemData) || count($getCostItemData) == count($getBillingItemData)) { ?>
					<table class="table table-bordered">

						<tr style="background: #6aa07163;">
							<th>Exc Rate</th>
							<th>Billing Item</th>
							<th>Revenue Amount</th>
							<th>Conversion couts HTG en USD</th>
							<th>Cost Item</th>
							<th>Expense Amount</th>
							<th>Conversion couts HTG en USD</th>
							<th>P/L</th>
						</tr>

						<?php
						$invoicesum = 0;
						$expensesum = 0;
						$excludeBillingItems = $getBillingItemData;
						foreach ($getCostItemData as $k => $v) { ?>

							<?php $getRelatedBillingId = DB::table('billing_items')->where('code', $v->costItemId)->first();
							$biliingItemDescription = '';
							$biliingItemAmount = '';
							$biliingCurrencyCode = '';
							foreach ($getBillingItemData as $k1 => $v1) {
								if (!empty($getRelatedBillingId) && $v1->biliingItemId == $getRelatedBillingId->id) {
									$biliingItemDescription = $v1->biliingItemDescription;
									$biliingItemAmount = $v1->biliingItemAmount;
									$biliingCurrencyCode = $v1->currencyCode;
									unset($excludeBillingItems[$k1]);
									break;
								}
							}
							?>
							<tr>
								<td style="text-align:right"><?php echo $exchangeRateOfUsdToHTH; ?></td>
								<td><?php echo $biliingItemDescription; ?></td>
								<td style="text-align:right;">
									<?php
									echo !empty($biliingItemAmount) ? $biliingCurrencyCode . ' ' . number_format($biliingItemAmount, 2) : '';
									?>
								</td>
								<td style="text-align:right;">
									<?php
									if ($biliingCurrencyCode == 'HTG') {
										if (!empty($biliingItemAmount)) {
											echo 'USD' . ' ' . number_format($biliingItemAmount / $exchangeRateOfUsdToHTH, 2);

											$invoicesum += str_replace(',', '', number_format($biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
										}
									} else {
										if (!empty($biliingItemAmount)) {
											echo 'USD' . ' ' . number_format($biliingItemAmount, 2);
											$invoicesum += str_replace(',', '', number_format($biliingItemAmount, 2));
										}
									}
									?>
								</td>
								<td><?php echo $v->costDescription; ?></td>
								<td style="text-align:right;"><?php echo !empty($v->costAmount) ? $v->currencyCode . ' ' . $v->costAmount : ''; ?></td>
								<td style="text-align:right;">
									<?php
									if ($v->currencyCode == 'HTG') {
										if (!empty($v->costAmount)) {
											echo 'USD' . ' ' . number_format($v->costAmount / $exchangeRateOfUsdToHTH, 2);

											$expensesum += str_replace(',', '', number_format($v->costAmount / $exchangeRateOfUsdToHTH, 2));
										}
									} else {
										if (!empty($v->costAmount)) {
											echo 'USD' . ' ' . number_format($v->costAmount, 2);
											$expensesum += str_replace(',', '', number_format($v->costAmount, 2));
										}
									}
									?>
								</td>
								<td></td>

							</tr>

						<?php } ?>
						<?php
						//pre($excludeBillingItems);
						foreach ($excludeBillingItems as $keyE => $valueE) {
						?>
							<tr>
								<td style="text-align:right"><?php echo $exchangeRateOfUsdToHTH; ?></td>
								<td><?php echo $valueE->biliingItemDescription; ?></td>
								<td style="text-align:right;">
									<?php
									echo !empty($valueE->biliingItemAmount) ? $valueE->currencyCode . ' ' . number_format($valueE->biliingItemAmount, 2) : '';
									?>
								</td>
								<td style="text-align:right;">
									<?php
									if ($valueE->currencyCode == 'HTG') {
										if (!empty($valueE->biliingItemAmount)) {
											echo 'USD' . ' ' . number_format($valueE->biliingItemAmount / $exchangeRateOfUsdToHTH, 2);

											$invoicesum += str_replace(',', '', number_format($valueE->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
										}
									} else {
										if (!empty($valueE->biliingItemAmount)) {
											echo 'USD' . ' ' . number_format($valueE->biliingItemAmount, 2);
											$invoicesum += str_replace(',', '', number_format($valueE->biliingItemAmount, 2));
										}
									}
									?>
								</td>
								<td><?php echo ''; ?></td>
								<td><?php echo ''; ?></td>
								<td style="text-align:right;"></td>
								<td style="text-align:right;"></td>

							</tr>
						<?php }
						?>
						<tr style="background: #ccc;font-weight: bold;">
							<td style="border: none;">Total</td>
							<td></td>
							<td></td>
							<td style="text-align:right;">{{'USD'.' '.number_format($invoicesum,2)}}</td>
							<td></td>
							<td></td>
							<td style="text-align:right;">{{'USD'.' '.number_format($expensesum,2)}}</td>
							<td style="text-align:right;color:<?php echo ($invoicesum - $expensesum) < 0 ? 'red' : 'green'; ?>">{{'USD'.' '.number_format($invoicesum-$expensesum,2)}}</td>

						</tr>

					</table>
				<?php } else { ?>
					<table class="table table-bordered">

						<tr style="background: #6aa07163;">
							<th>Exc Rate</th>
							<th>Billing Item</th>
							<th>Revenue Amount</th>
							<th>Conversion couts HTG en USD</th>
							<th>Cost Item</th>
							<th>Expense Amount</th>
							<th>Conversion couts HTG en USD</th>
							<th>P/L</th>
						</tr>

						<?php
						$invoicesum = 0;
						$expensesum = 0;
						$excludeCostItems = $getCostItemData;
						foreach ($getBillingItemData as $k => $v) { ?>

							<?php $getRelatedCostId = DB::table('costs')->where('cost_billing_code', $v->biliingItemId)->first();
							$costItemDescription = '';
							$costItemAmount = '';
							$costCurrencyCode = '';
							foreach ($getCostItemData as $k1 => $v1) {
								if (!empty($getRelatedCostId) && $v1->costItemId == $getRelatedCostId->id) {
									$costItemDescription = $v1->costDescription;
									$costItemAmount = $v1->costAmount;
									$costCurrencyCode = $v1->currencyCode;
									unset($excludeCostItems[$k1]);
									break;
								}
							}
							?>
							<tr>
								<td style="text-align:right"><?php echo $exchangeRateOfUsdToHTH; ?></td>
								<td><?php echo $v->biliingItemDescription; ?></td>
								<td style="text-align:right;"><?php
																							echo !empty($v->biliingItemAmount) ? $v->currencyCode . ' ' . number_format($v->biliingItemAmount, 2) : '';
																							?>
								</td>
								<td style="text-align:right;">
									<?php
									if ($v->currencyCode == 'HTG') {
										if (!empty($v->biliingItemAmount)) {
											echo 'USD' . ' ' . number_format($v->biliingItemAmount / $exchangeRateOfUsdToHTH, 2);

											$invoicesum += str_replace(',', '', number_format($v->biliingItemAmount / $exchangeRateOfUsdToHTH, 2));
										}
									} else {
										if (!empty($v->biliingItemAmount)) {
											echo 'USD' . ' ' . number_format($v->biliingItemAmount, 2);
											$invoicesum += str_replace(',', '', number_format($v->biliingItemAmount, 2));
										}
									}
									?>
								</td>
								<td><?php echo $costItemDescription; ?></td>
								<td style="text-align:right;"><?php echo !empty($costItemAmount) ? $costCurrencyCode . ' ' . $costItemAmount : ''; ?></td>
								<td style="text-align:right;">
									<?php
									if ($costCurrencyCode == 'HTG') {
										if (!empty($costItemAmount)) {
											echo 'USD' . ' ' . number_format($costItemAmount / $exchangeRateOfUsdToHTH, 2);

											$expensesum += str_replace(',', '', number_format($costItemAmount / $exchangeRateOfUsdToHTH, 2));
										}
									} else {
										if (!empty($costItemAmount)) {
											echo 'USD' . ' ' . number_format($costItemAmount, 2);
											$expensesum += str_replace(',', '', number_format($costItemAmount, 2));
										}
									}
									?>
								</td>
								<td></td>
							</tr>
						<?php } ?>
						<?php
						//pre($excludeBillingItems);
						foreach ($excludeCostItems as $keyE => $valueE) {
						?>
							<tr>
								<td style="text-align:right"><?php echo $exchangeRateOfUsdToHTH; ?></td>
								<td><?php echo ''; ?></td>
								<td style="text-align:right;"><?php echo ''; ?></td>
								<td></td>
								<td><?php echo $valueE->costDescription; ?></td>
								<td style="text-align:right;"><?php echo !empty($valueE->costAmount) ? $valueE->currencyCode . ' ' . $valueE->costAmount : ''; ?></td>
								<td style="text-align:right;">
									<?php
									if ($valueE->currencyCode == 'HTG') {
										if (!empty($valueE->costAmount)) {
											echo 'USD' . ' ' . number_format($valueE->costAmount / $exchangeRateOfUsdToHTH, 2);
											$expensesum += str_replace(',', '', number_format($valueE->costAmount / $exchangeRateOfUsdToHTH, 2));
										}
									} else {
										if (!empty($valueE->costAmount)) {
											echo 'USD' . ' ' . number_format($valueE->costAmount, 2);
											$expensesum += str_replace(',', '', number_format($valueE->costAmount, 2));
										}
									}
									?>
								</td>
								<td></td>

							</tr>
						<?php }
						?>
						<tr style="background: #ccc;font-weight: bold;">
							<td style="border: none;">Total</td>
							<td></td>
							<td></td>
							<td style="text-align:right;">{{'USD'.' '.number_format($invoicesum,2)}}</td>
							<td></td>
							<td></td>
							<td style="text-align:right;">{{'USD'.' '.number_format($expensesum,2)}}</td>
							<td style="text-align:right;color:<?php echo ($invoicesum - $expensesum) < 0 ? 'red' : 'green'; ?>">{{'USD'.' '.number_format($invoicesum-$expensesum,2)}}</td>

						</tr>

					</table>
				<?php }
				?>
				<table class="table simpletable" id="example" style="display:none">
					<thead>
						<tr>
							<th style="width: 250px;">Revenue ($)</th>
							<th style="width: 250px;">Cost ($)</th>
							<th style="width: 250px;">Margin (Profit/Loss) ($)</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="revenuetd"><?php $totalRevenue = App\Expense::getTotalRevenueOfCargo($rid);
																		echo number_format($totalRevenue, 2); ?></td>
							<td class="expensetd"><?php $totalExpense = App\Expense::getTotalExpenseOfCargoInUSDORHTG($rid, 'USD');
																		echo !empty($totalExpense) ? number_format($totalExpense, 2) : '0.00'; ?></td>
							<td class="margintd"><?php $margin = $totalRevenue - $totalExpense;
																		if ($margin < 0) { ?>
									<span style="color: red"><?php echo number_format($margin, 2); ?></span>
								<?php } else { ?>
									<span><?php echo number_format($margin, 2); ?></span>
								<?php } ?>
							</td>
							<td><button style="" id="view-detail" class="viewdetail btn btn-success" value="<?php echo url('cargo/viewdifferencereport', [$model->id]) ?>" data-module='{{"Balance Report : ".$model->file_number}}'>View Detail</button></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div id="div_activities" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Activities</div>
			<div class="detail-container" style="">

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
						<div class="resultdata60" style="word-break: break-all;"><?php echo $activityData->description; ?></div>
						<div class="resultdata20"><?php echo date('d-m-Y h:i:s', strtotime($activityData->updated_on)); ?></div>
						@endforeach
					</div>
				<?php } else { ?>
					<h4 style="float: left;width: 100%;font-size: 15px;">No Activity Found.</h4>
				<?php } ?>

			</div>

		</div>




	</div>
	</div>
	</div>
	</div>
	<iframe src="" id="frame" style="display: none;"></iframe>
</section>
<div id="modalAddCheck" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3 class="modal-title modal-title-block text-center primecolor">Add Check Guarantee to pay</h3>
			</div>
			<div class="modal-body" id="modalContentAddCheck" style="overflow: hidden;">
			</div>
		</div>
	</div>
</div>
<style type="text/css">
	.main-sidebar {
		position: fixed !important;
	}
</style>
@endsection
@section('page_level_js')
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').DataTable({
			"order": [
				[0, "desc"]
			],
			"columnDefs": [{
				"targets": [1, -1],
				"orderable": false
			}, {
				type: 'date-uk',
				targets: [2]
			}],
			"scrollX": true,
			drawCallback: function() {
				$('.paginate_button', this.api().table().container())
					.on('click', function() {
						$('#loading').show();
						setTimeout(function() {
							$("#loading").hide();
						}, 200);
						$('.expandpackage').each(function() {
							if ($(this).hasClass('fa-minus')) {
								$(this).removeClass('fa-minus');
								$(this).addClass('fa-plus');
							}
						})
						if ($('.fa-expand-collapse-all').hasClass('fa-minus')) {
							$('.fa-expand-collapse-all').removeClass('fa-minus');
							$('.fa-expand-collapse-all').addClass('fa-plus');
						}
					});
				$('#example_filter input').bind('keyup', function(e) {
					$('#loading').show();
					if ($('.fa-expand-collapse-all').hasClass('fa-minus')) {
						$('.fa-expand-collapse-all').removeClass('fa-minus');
						$('.fa-expand-collapse-all').addClass('fa-plus');
					}
					setTimeout(function() {
						$("#loading").hide();
					}, 200);
				});
			}
		});
		$('#example').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			"columnDefs": [{
				type: 'date-uk',
				targets: 1
			}]
		});
		$('#example3').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
		});
		$('#checkGaurenteeTable').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
		});


		$('#filesTable').DataTable({
			"order": [
				[0, "desc"]
			],
		});


		$(document).delegate('.fa-expand-collapse-all', 'click', function() {
			$('#loading').show();
			if ($(this).hasClass('fa-plus')) {
				$(this).removeClass('fa-plus');
				$(this).addClass('fa-minus');
			} else {
				$(this).removeClass('fa-minus');
				$(this).addClass('fa-plus');
			}
			$('.expandpackage').trigger('click');
		});

		//$('.expandpackage').click(function(){
		$(document).delegate('.expandpackage', 'click', function() {
			var rowId = $(this).data('rowid');
			$('#loading').show();
			setTimeout(function() {
				$("#loading").hide();
			}, 200);
			//$('#loading').show();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var thiz = $(this);
			var parentTR = thiz.closest('tr');
			if (thiz.hasClass('fa-plus')) {
				/*$('.childrw').remove();
				$('.fa-minus').each(function(){
				$(this).removeClass('fa-minus');
				$(this).addClass('fa-plus');
				})*/
				thiz.removeClass('fa-plus');
				thiz.addClass('fa-minus');
				var expenseId = $(this).data('expenseid');
				var rowId = $(this).data('rowid');
				var urlzte = '<?php echo route("expandexpenses"); ?>';
				$.ajax({
					url: urlzte,
					type: 'POST',
					data: {
						expenseId: expenseId,
						rowId: rowId
					},
					success: function(data) {
						$(data).insertAfter(parentTR).slideDown();
					},
				});
				//$('#loading').hide();
			} else {
				thiz.removeClass('fa-minus');
				thiz.addClass('fa-plus');
				$('.child-' + rowId).remove();
				//parentTR.next('tr').remove();
				//$('#loading').hide();
			}
		})

		$(document).delegate('#download', 'click', function() {
			$('#loading').show();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var urlz = $(this).data('value');
			$.ajax({
				url: urlz,
				type: 'GET',
				data: {},
				success: function(data) {
					$('#frame').attr('src', urlz);
					//window.open(urlz,'_blank' );
					$('#loading').hide();
					//alert(data);
				}
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
	});
</script>
@stop