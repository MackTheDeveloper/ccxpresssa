@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('breadcrumbs')
@include('menus.ups-expense')
@stop

@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 0px;font-weight: 600;">View Detail (<?php echo $model->voucher_number; ?>)</h1>
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
			<div class="edit-btn">
				<?php
				if ($model->request_by_role == 12)
					$editLing = route('editagentupsexpensesbyadmin', [$model->expense_id, 'flagFromExpenseListing']);
				else
					$editLing = route('editupsexpense', [$model->expense_id]);
				?>
				<a class="btn round orange btn-warning" href="{{$editLing}}">Edit</a>
			</div>
			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Basic Details</div>
			<div class="detail-container">
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2">
						<?php $data = App\Ups::getUpsData($model->ups_details_id);
						echo !empty($data->file_number) ? $data->file_number : '-'; ?>
					</span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Voucher No. : </span>
					<span class="viewblk2">
						<span class="viewblk2"><?php echo $model->voucher_number; ?></span>
					</span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB/BL No. : </span>
					<span class="viewblk2">
						<?php $data = App\Ups::getUpsData($model->ups_details_id);
						echo !empty($data->awb_number) ? $data->awb_number : '-'; ?>
					</span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">File Name : </span>
					<span class="viewblk2">
						<?php $data = App\Ups::getUpsData($model->ups_details_id);
						echo !empty($data->file_name) ? $data->file_name : '-'; ?>
					</span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee) ? $model->consignee : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Expediteur / Shipper : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper) ? $model->shipper : '-'; ?></span>
				</div>
			</div>

			<div id="div_activities" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Activities</div>
			<div class="detail-container " style="height: auto;max-height: 400px;overflow: auto;    width: 100%;">
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
		</div>
	</div>
</section>


@endsection
@section('page_level_js')
<script type="text/javascript">
	$(document).ready(function() {
		$('#example').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			drawCallback: function() {
				$('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
					$('#loading').show();
					setTimeout(function() {
						$("#loading").hide();
					}, 200);
				});
				$('#example_filter input').bind('keyup', function(e) {
					$('#loading').show();
					setTimeout(function() {
						$("#loading").hide();
					}, 200);
				});
			},

		});
	});
</script>
@stop
<style type="text/css">
	.main-sidebar {
		position: fixed !important;
	}
</style>