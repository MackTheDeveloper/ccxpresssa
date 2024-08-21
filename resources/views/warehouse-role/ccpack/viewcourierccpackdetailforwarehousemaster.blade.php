<?php

use App\Warehouse;

$warehouse = new Warehouse;
?>
@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('breadcrumbs')
@include('menus.warehouse-ccpack-files')
@stop

@section('content')
@if(Session::has('flash_message'))
<div class="alert alert-success flash-success">
	{{ Session::get('flash_message') }}
</div>
@endif
<section class="content-header">
	<h1>
		<div style="float: left"><?php echo 'CCPack - ' . $model->file_number; ?></div>
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
					<tr data-editlink="{{ route('courierccpackwarehouseflow',[$model->id,$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
						<td>{{$items->file_number}}</td>
						<td>{{$items->billingParty}}</td>
						<td>{{isset(Config::get('app.ups_new_scan_status')[!empty($items->ccpack_scan_status) ? $items->ccpack_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($items->ccpack_scan_status) ? $items->ccpack_scan_status : '-'] : '-'}}</td>
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
	})
</script>
@stop