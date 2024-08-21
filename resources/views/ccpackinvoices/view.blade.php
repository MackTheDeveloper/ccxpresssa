@extends('layouts.custom')
@section('title')
View Invoice Detail
@stop

@section('breadcrumbs')
@include('menus.ccpack-invoices')
@stop
<?php

use App\Currency; ?>
@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 0px;font-weight: 600;">View Invoice Detail</h1>
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
				<a class="btn round orange btn-warning" href="{{route('editccpackinvoice',$model->id)}}">Edit</a>
			</div>
			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Basic Details</div>
			<div class="detail-container basicDetaiCls">
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Bill No : </span>
					<span class="viewblk2"><?php echo $model->bill_no ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Date : </span>
					<span class="viewblk2"><?php echo !empty($model->date) ? date('d-m-Y', strtotime($model->date)) : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Billing Party : </span>
					<span class="viewblk2"><?php $dataUser = app('App\Clients')->getClientData($model->bill_to);
																	echo !empty($dataUser->company_name) ? strtoupper($dataUser->company_name) : "-"; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Email : </span>
					<span class="viewblk2"><?php echo !empty($model->email) ? $model->email : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Expediteur / Shipper : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper) ? $model->shipper : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? nl2br($model->consignee_address) : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB / BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->awb_no) ? $model->awb_no : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Memo : </span>
					<span class="viewblk2"><?php echo !empty($model->memo) ? $model->memo : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">File Number : </span>
					<span class="viewblk2"><?php echo !empty($model->file_no) ? $model->file_no : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Weight : </span>
					<span class="viewblk2"><?php echo !empty($model->weight) ? $model->weight : '-'; ?></span>
				</div>
				<div class="newline" style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Payment Status : </span>
					<span class="viewblk2" style="<?php echo ($model->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>"><?php echo !empty($model->payment_status) ? $model->payment_status : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Balance of : </span>
					<span class="viewblk2"><?php echo $model->balance_of; ?></span>
				</div>
			</div>

			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;margin-bottom:-15px;">
				Payment Details
			</div>
			<div class="detail-container">
				<div class="row">
					<div class="col-md-4">
						<b>Total Amount in HTG : </b><span style="margin-left: 2%" class="totalhtg">{{number_format($totalOfCurrency[3],2)}}</span>
					</div>
					<div class="col-md-4">
						<b>Total Amount in USD : </b><span style="margin-left: 2%" class="totalusd">{{number_format($totalOfCurrency[1],2)}}</span>
					</div>
					<div class="col-md-4" style="display: none">
						<b>Total : </b>
						<span style="margin-left: 2%" class="totalusd">
							{{number_format($totalOfCurrency['total'],2)}} HTG
						</span>
					</div>
				</div>


				<?php $count = 1; ?>

				<table id="example" class="display" style="width:100%">
					<thead>
						<tr>
							<th>Amount</th>
							<th>Payment Currency</th>
							<th>Paid Amount</th>
							<th>Payment Via</th>
							<th>Payment Description</th>
							<th>Date & Time</th>
						</tr>
					</thead>
					<tbody>
						@foreach($paymentDetail as $paymentDetail)
						<tr>
							<td>{{number_format($model->total, 2)}}</td>
							<td>
								@if($paymentDetail->exchange_currency != '')
								<?php $dataCurrency = Currency::getData($paymentDetail->exchange_currency);
								echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
								@else
								{{'-'}}
								@endif
							</td>
							<td>
								{{$paymentDetail->exchange_amount}}
							</td>

							<td>
								{{$paymentDetail->payment_via}}
							</td>
							<td>
								@if($paymentDetail->payment_via_note != '')
								{{$paymentDetail->payment_via_note}}
								@else
								{{"-"}}
								@endif
							</td>
							<td>
								<?php echo date('d-m-Y h:i:s', strtotime($paymentDetail->created_at)); ?>

							</td>
						</tr>
						<?php $count++; ?>
						@endforeach
					</tbody>
				</table>
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
	$('#example').DataTable({
		"columnDefs": [{
			"targets": [-1],
			"orderable": false
		}],
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
</script>
@stop
<style type="text/css">
	.main-sidebar {
		position: fixed !important;
	}

	.dataTable {
		font-size: 14px;
	}
</style>