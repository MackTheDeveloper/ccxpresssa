	@extends('layouts.custom')
	@section('title')
	Update UPS Shipment
	@stop
	@section('sidebar')
	<aside class="main-sidebar">
		<ul class="sidemenu nav navbar-nav side-nav">
			<?php
			$checkPermissionCreateCourier = App\User::checkPermission(['create_ups'], '', auth()->user()->id);
			$checkPermissionUpdateCourier = App\User::checkPermission(['update_ups'], '', auth()->user()->id);
			$checkPermissionDeleteCourier = App\User::checkPermission(['delete_ups'], '', auth()->user()->id);
			$checkPermissionImportCourier = App\User::checkPermission(['import_ups'], '', auth()->user()->id);
			$checkPermissionListingCourier = App\User::checkPermission(['ups_shipment_listing'], '', auth()->user()->id);
			$checkPermissionAddExpenseCourier = App\User::checkPermission(['add_ups_expense'], '', auth()->user()->id);
			?>
			<?php if ($checkPermissionListingCourier) { ?>
				<li class="widemenu">
					<a href="{{ route('ups') }}">UPS Shipments</a>
				</li>
			<?php } ?>
			<?php if ($checkPermissionImportCourier) { ?>
				<li class="widemenu submenu">
					<a href="{{ route('importups') }}">Upload File</a>
				</li>
			<?php } ?>
			<?php if ($checkPermissionCreateCourier) { ?>
				<li class="widemenu submenu">
					<a href="{{ route('createups') }}">Add Manually</a>
				</li>
			<?php } ?>



			<?php if ($checkPermissionCreateCourier) { ?>
				<li class="widemenu">
					<a href="javascript:void(0)">Aeropost Shipments</a>
				</li>
			<?php } ?>
			<?php if ($checkPermissionImportCourier) { ?>
				<li class="widemenu submenu">
					<a href="javascript:void(0)">Upload File</a>
				</li>
			<?php } ?>
			<?php if ($checkPermissionCreateCourier) { ?>
				<li class="widemenu submenu">
					<a href="javascript:void(0)">Add Manually</a>
				</li>
			<?php } ?>


			<?php if ($checkPermissionCreateCourier) { ?>
				<li class="widemenu">
					<a href="{{ route('viewall') }}">View All Shipments</a>
				</li>
			<?php } ?>
		</ul>
	</aside>
	@stop
	@section('content')
	<section class="content-header">
		<h1>Update UPS Shipment</h1>
	</section>

	<section class="content editupscontainer">
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
		<div class="box box-success">
			<div class="box-body" style="float: left">
				<div class="col-lg-12" style="margin-top:10px;">
					<form action="<?php echo url('ups/update', $upsData->id) ?>" method="post" name="frmShipment" id="frmShipment">
						{{ csrf_field() }}
						<div class="panel" style="float: left;">
							<div class="panel-heading" style="float: left;width: 100%;">
								<a style="display: none" id="disabled" class="menulink"> Delete <i class="fa fa-times"></i></a>
								<a style="display: none" class="menulink" href="generate-invoice?cid=00000069"> Print <i class="fa fa-print"></i></a>
								<a style="display: none" class="menulink" href="alert?cid=3367061&amp;payment=0"> Mark As Unpaid <i class="fa fa-times"></i></a>
								<a style="display: none" class="menulink" href="alert?cid=3367061"> Send Alert <i class="fa fa-bell"></i></a>
								<a class="btn btn-danger menulinkcancel" href="{{url('ups')}}" title="">Cancel</a>
								<button type="sunmit" class="menulink"> Save <i class="fa fa-floppy-o"></i></button>
								<div class="col-md-3" style="float: right;">
									<select class="form-control" name="ups_status" placeholder="Current Status">
										<option value="">--Select Status--</option>
										<option <?php echo $upsData->ups_status == "Awaiting Approval" ? 'selected' : '' ?> value="Awaiting Approval">Awaiting Approval</option>
										<option <?php echo $upsData->ups_status == "Approved" ? 'selected' : '' ?> value="Approved">Approved</option>
										<option <?php echo $upsData->ups_status == "Cancelled" ? 'selected' : '' ?> value="Cancelled">Cancelled</option>
										<option <?php echo $upsData->ups_status == "Shipment Collected" ? 'selected' : '' ?> value="Shipment Collected">Shipment Collected</option>
										<option <?php echo $upsData->ups_status == "Waiting for scan" ? 'selected' : '' ?> value="Waiting for Scan">Waiting for scan</option>
										<option <?php echo $upsData->ups_status == "Ready For Depart" ? 'selected' : '' ?> value="Ready For Depart">Ready For Depart</option>
										<option <?php echo $upsData->ups_status == "Despatched" ? 'selected' : '' ?> value="Despatched">Despatched</option>
										<option <?php echo $upsData->ups_status == "Arrived" ? 'selected' : '' ?> value="Arrived">Arrived</option>
										<option <?php echo $upsData->ups_status == "Cleared" ? 'selected' : '' ?> value="Cleared">Cleared</option>
										<option <?php echo $upsData->ups_status == "Transit" ? 'selected' : '' ?> value="Transit">Transit</option>
										<option <?php echo $upsData->ups_status == "Out For Destination" ? 'selected' : '' ?> value="Out For Destination">Out For Destination</option>
										<option <?php echo $upsData->ups_status == "Out For Delivery" ? 'selected' : '' ?> value="Out For Delivery">Out For Delivery</option>
										<option <?php echo $upsData->ups_status == "Delivered" ? 'selected' : '' ?> value="Delivered">Delivered</option>
										<option <?php echo $upsData->ups_status == "Returned" ? 'selected' : '' ?> value="Returned">Returned</option>
										<option <?php echo $upsData->ups_status == "Hold" ? 'selected' : '' ?> value="Hold">Hold</option>
									</select>
								</div>


							</div>
							<div class="panel-body" style="float: left">

								<!-- Nav tabs -->
								<ul class="nav nav-tabs nav-line nav-justified">
									<li class="active"><a href="#comments13" data-toggle="tab" aria-expanded="false"><strong>Product Details</strong></a></li>
									<li class=""><a href="#popular12" data-toggle="tab" aria-expanded="false"><strong>Shipment Details</strong></a></li>
									<li class=""><a href="#recent12" data-toggle="tab" aria-expanded="true"><strong>Sender Details</strong></a></li>
									<li class=""><a href="#comments12" data-toggle="tab" aria-expanded="false"><strong>Receiver Details</strong></a></li>
									<li class=""><a href="#comments14" data-toggle="tab" aria-expanded="false"><strong>Delivery Details/ POD</strong></a></li>
								</ul>

								<!-- Tab panes -->
								<div class="tab-content">

									<div class="tab-pane" id="popular12">
										<table class="table">
											<tbody>
												<tr>
													<td>Awb Number<input type="text" name="awb_number" value="{{$upsData->awb_number}}" class="form-control"></td>
													<td>Destination<input type="text" name="destination" value="{{$upsData->destination}}" class="form-control"></td>
													<td>Origin<input type="text" name="origin" value="{{$upsData->origin}}" class="form-control"></td>
													<td>Weight<input type="text" name="weight" value="{{$upsData->weight}}" class="form-control"></td>
													<td>Total freight<input type="text" name="freight" value=<?php echo (empty($upsData->freight)) ? '0.00' : $upsData->freight; ?> class="form-control"></td>
												</tr>
												<tr>
													<td>Transport Date<input type="text" name="tdate" class="form-control datepicker" value="{{$upsData->tdate}}"></td>
													<td>Arrival Date<input type="text" name="arrival_date" class="form-control datepicker" value="{{$upsData->arrival_date}}"></td>
												</tr>
											</tbody>
										</table>
									</div>

									<div class="tab-pane" id="recent12">
										<table class="table">
											<tbody>
												<tr>
													<td>Client name<input class="form-control" type="text" name="shipper_name" value="{{$upsData->shipper_name}}"></td>
													<td>Client phone<input class="form-control" type="text" name="shipper_telephone" value="{{$upsData->shipper_telephone}}"></td>
													<td>Client Address<textarea class="form-control" name="shipper_address">{{$upsData->shipper_address}}</textarea></td>
													<td>From city<input type="text" name="shipper_city" value="{{$upsData->shipper_city}}" class="form-control"></td>
												</tr>
											</tbody>
										</table>
									</div>

									<div class="tab-pane" id="comments12">
										<table class="table">
											<tbody>
												<tr>
													<td>Receiver name<input type="text" name="consignee_name" class="form-control" value="{{$upsData->consignee_name}}"></td>
													<td>Receiver phone<input type="text" name="consignee_telephone" class="form-control" value="{{$upsData->consignee_telephone}}"></td>
													<td>Receiver address<textarea class="form-control" name="consignee_address">{{$upsData->consignee_address}}</textarea></td>
												</tr>
											</tbody>
										</table>
									</div>

									<div class="tab-pane" id="comments14">
									</div>

									<div class="tab-pane active" id="comments13">
										<table class="table table-striped table-hover table-bordered">
											<thead>
												<tr>
													<th>Company</th>
													<th>Destination</th>
													<th>Origin</th>
													<th>No. of Pcs.</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>{{$upsData->company}}</td>
													<td>{{$upsData->destination}}</td>
													<td>{{$upsData->origin}}</td>
													<td>{{$upsData->nbr_pcs}}</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>

		<h1 style="font-size: 20px">Activities</h1>
		<div class="box box-success" style="clear: both;">
			<div class="box-body" style="float: left;width: 100%">
				<div class="detail-container" style="float: left;height: auto;max-height: 400px;overflow: auto;    width: 100%;">
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
			$('.datepicker').datepicker({
				format: 'dd-mm-yyyy',
				todayHighlight: true,
				autoclose: true
			});
			var table = $('#example').DataTable({
				//"ordering": false,
				drawCallback: function() {
					$('.paginate_button', this.api().table().container())
						.on('click', function() {
							$('#loading').show();
							setTimeout(function() {
								$("#loading").hide();
							}, 300);
							$('.expandpackage').each(function() {
								if ($(this).hasClass('fa-minus')) {
									$(this).removeClass('fa-minus');
									$(this).addClass('fa-plus');
								}
							})
						});
				}
			});

			$('#example tfoot th').each(function() {
				var title = $(this).text();
				if (title == 'Consignee Name' || title == 'AWE Tracking') {
					$(this).html('<input class="form-control" type="text" placeholder="-- Search --" />');
				}
			});

			// Apply the search
			table.columns().every(function() {
				var that = this;

				$('input', this.footer()).on('keyup change', function() {
					$('#loading').show();
					setTimeout(function() {
						$("#loading").hide();
					}, 300);
					$('.expandpackage').each(function() {
						if ($(this).hasClass('fa-minus')) {
							$(this).removeClass('fa-minus');
							$(this).addClass('fa-plus');
						}
					})

					if (that.search() !== this.value) {
						that
							.search(this.value)
							.draw();
					}
				});
			});

			//$('.expandpackage').click(function(){
			$(document).delegate('.expandpackage', 'click', function() {
				$('#loading').show();
				setTimeout(function() {
					$("#loading").hide();
				}, 300);
				//$('#loading').show();
				$.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					}
				});
				var thiz = $(this);
				var parentTR = thiz.closest('tr');
				if (thiz.hasClass('fa-plus')) {
					$('.childrw').remove();
					$('.fa-minus').each(function() {
						$(this).removeClass('fa-minus');
						$(this).addClass('fa-plus');
						//$(this).closest('tr').next('tr').remove();
					})

					thiz.removeClass('fa-plus');
					thiz.addClass('fa-minus');
					var upsId = $(this).data('upsid');
					var rowId = $(this).data('rowid');
					$.ajax({
						url: 'ups/expandpackage',
						type: 'POST',
						data: {
							upsId: upsId,
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
					$('.childrw').remove();
					//parentTR.next('tr').remove();
					//$('#loading').hide();

				}
			})

		})
	</script>
	@stop