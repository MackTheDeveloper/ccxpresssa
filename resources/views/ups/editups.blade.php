	@extends('layouts.custom')
	@section('title')
	Update UPS Shipment
	@stop


	@section('breadcrumbs')
	@include('menus.ups-import')
	@stop

	@section('content')
	<section class="content-header" style="margin-bottom: 1.5%">
		<h1 style="float: left">Update UPS File (Import)</h1>
		<h1 style="float: right;color: green">File Status : <?php echo isset(Config::get('app.ups_new_scan_status')[!empty($upsData->ups_scan_status) ? $upsData->ups_scan_status : '-']) ? Config::get('app.ups_new_scan_status')[!empty($upsData->ups_scan_status) ? $upsData->ups_scan_status : '-'] : '-'; ?></h1>
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
			<div class="box-body">
				<?php

				use App\Ups; ?>
				<form action="<?php echo url('ups/update', $upsData->id) ?>" method="post" name="frmShipment" id="createforms" class="form-horizontal  create-form">
					{{ csrf_field() }}
					<input type="hidden" name="courier_operation_type" value=1>
					<div class="panel-heading" style="float: left;width: 100%;">
						<div class="col-md-6">

							<div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
								<div class="col-md-4">
									<?php echo Form::label('file_number', 'File Number :', ['class' => 'control-label', 'style' => 'float:left']); ?>
								</div>
								<div class="col-md-6">
									<span class="form-control" style="border: none;font-weight: bold;
                                    box-shadow: none;"><?php echo $upsData->file_number; ?></span>
								</div>
							</div>

						</div>
						<a class="btn btn-danger menulinkcancel" href="{{url('ups')}}" title="">Cancel</a>
						<button type="submit" class="btn btn-success menulink">Update</button>
						<div class="row">
							<div class="col-md-3" style="float: right;">

								<?php echo Form::select('inprogress_scan_status', array('Before The Delivery' => $status_before_delivery, 'After The Delivery' => $status_after_delivery), $upsData->inprogress_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => 'inprogress_scan_status']); ?>
							</div>
						</div>
						<div class="row" style="margin-top: 1%;display: <?php echo !empty($upsData->other_status) ? 'block' : 'none' ?>;" id="otherDiv">
							<div class="col-md-3" style="float: right;margin-right: 19.5%">
								<?php echo Form::text('other_status', $upsData->other_status, ['class' => 'form-control fagent_id', 'data-live-search' => 'true', 'placeholder' => 'Enter Other Category...', 'id' => 'other_scan_status']); ?>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
								<div class="col-md-4">
									<?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>
								</div>
								<div class="col-md-6">
									<?php echo Form::select('billing_party', $billingParty, $upsData->billing_party, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
								</div>
								<div class="col-md-12 balance-div" style="display: none;text-align: center;">
									<span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
								</div>
							</div>
						</div>
					</div>

					<h4 class="formdeviderh4">Shipment information</h4>
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('awb_number') ? 'has-error' :'' }}">
								<div class="col-md-4 required">
									<?php echo Form::label('awb_number', 'Awb Number', ['class' => 'control-label']); ?>
								</div>
								<div class="col-md-6">
									<?php echo Form::text('awb_number', $upsData->awb_number, ['class' => 'form-control fawb_number', 'placeholder' => 'Enter Awb Number']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('destination') ? 'has-error' :'' }}">
								<?php echo Form::label('destination', 'Destination', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('destination', $upsData->destination, ['class' => 'form-control', 'placeholder' => 'Enter Destination']); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('origin') ? 'has-error' :'' }}">
								<?php echo Form::label('origin', 'Origin', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('origin', $upsData->origin, ['class' => 'form-control', 'placeholder' => 'Enter Origin']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('weight') ? 'has-error' :'' }}">
								<?php echo Form::label('weight', 'Weight', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('weight', $upsData->weight, ['class' => 'form-control', 'placeholder' => 'Enter Weight', 'id' => 'fimport_weight']); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('freight') ? 'has-error' :'' }}">
								<?php echo Form::label('freight', 'Total freight', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('freight', $upsData->freight, ['class' => 'form-control', 'placeholder' => 'Enter Total freight', 'id' => 'fimport_freight']); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('arrival_date') ? 'has-error' :'' }}">
								<?php echo Form::label('arrival_date', 'Arrival Date', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('arrival_date', !empty($upsData->arrival_date) ? date('d-m-Y', strtotime($upsData->arrival_date)) : '', ['class' => 'form-control datepicker', 'placeholder' => 'Enter Arrival Date']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('billing_term') ? 'has-error' :'' }}">
								<?php echo Form::label('billing_term', 'Billing Term', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6 billing_term-md-6">
									<?php
									echo Form::radio('billing', '1', $upsData->fc == '1' ? 'checked' : '');
									echo Form::label('fc', 'F/C');
									echo Form::radio('billing', '2', $upsData->fd == '1' ? 'checked' : '');
									echo Form::label('fd', 'F/D');
									echo Form::radio('billing', '3', $upsData->pp == '1' ? 'checked' : '');
									echo Form::label('pp', 'P/P');
									?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group">
								<?php echo Form::label('ups_scan_status', 'File Status', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::select('ups_scan_status', Config::get('app.ups_new_scan_status'), $upsData->ups_scan_status, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'id' => 'ups_scan_status', 'placeholder' => 'Select ...']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6" id="warehouse_div" style="display: <?php echo !empty($model->warehouse) ? 'block' : 'none'; ?>">
							<div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
								<?php echo Form::label('warehouse', 'Warehouse', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::select('warehouse', $warehouses, $upsData->warehouse, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
								</div>
							</div>
						</div>
					</div>
					<h4 class="formdeviderh4">Shipper information</h4>
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('shipper_name') ? 'has-error' :'' }}">
								<div class="col-md-4 required">
									<?php echo Form::label('shipper_name', 'Shipper name', ['class' => 'control-label']); ?>
								</div>
								<div class="col-md-6">
									<?php echo Form::text('shipper_name', Ups::getConsigneeName($upsData->shipper_name), ['class' => 'form-control fshipper_name', 'placeholder' => 'Enter Shipper name']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('shipper_telephone') ? 'has-error' :'' }}">
								<?php echo Form::label('shipper_telephone', 'Phone Number', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('shipper_telephone', $upsData->shipper_telephone, ['class' => 'form-control fshipper_telephone', 'placeholder' => 'Enter phone number']); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('shipper_address') ? 'has-error' :'' }}">
								<?php echo Form::label('shipper_address', 'Address', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::textarea('shipper_address', $upsData->shipper_address, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('shipper_city') ? 'has-error' :'' }}">
								<?php echo Form::label('shipper_city', 'City', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('shipper_city', $upsData->shipper_city, ['class' => 'form-control', 'placeholder' => 'Enter City']); ?>
								</div>
							</div>
						</div>
					</div>

					<h4 class="formdeviderh4">Receiver Details</h4>
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
								<div class="col-md-4 required">
									<?php echo Form::label('consignee_name', 'Receiver name', ['class' => 'control-label']); ?>
								</div>

								<div class="col-md-6">
									<?php echo Form::text('consignee_name', Ups::getConsigneeName($upsData->consignee_name), ['class' => 'form-control fconsignee_name', 'placeholder' => 'Enter Receiver name']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('consignee_telephone') ? 'has-error' :'' }}">
								<?php echo Form::label('consignee_telephone', 'Phone Number', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('consignee_telephone', $upsData->consignee_telephone, ['class' => 'form-control fconsignee_telephone', 'placeholder' => 'Enter phone number']); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
								<?php echo Form::label('consignee_address', 'Address', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::textarea('consignee_address', $upsData->consignee_address, ['class' => 'form-control fconsignee_address', 'rows' => 4, 'placeholder' => 'Enter Address']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('cash_credit') ? 'has-error' :'' }}">

								<?php echo Form::label('cash_credit', 'Cash/Credit', ['class' => 'col-md-4 control-label']); ?>

								<div class="col-md-6 consolidate_flag-md-6">
									<?php
									echo Form::radio('cash_credit', 'Cash', $upsData->cash_credit == 'Cash' ? 'checked' : '', ['class' => 'cash_credit']);
									echo Form::label('', 'Cash');
									echo Form::radio('cash_credit', 'Credit', $upsData->cash_credit == 'Credit' ? 'checked' : '', ['class' => 'cash_credit']);
									echo Form::label('', 'Credit');
									?>
								</div>
							</div>
						</div>
					</div>
					<?php //if (checkloggedinuserdata() == 'Other') { ?>
						<h4 class="formdeviderh4">Product information</h4>
						<div class="row" style="margin-left: 1%">

							<div class="col-md-4">
								<div class="form-group {{ $errors->has('no_manifeste') ? 'has-error' :'' }}">
									<?php echo Form::label('package_type', 'Product Type', ['class' => 'col-md-5 control-label']); ?>
									<div class="col-md-6">
										<?php echo Form::select('package_type', Config::get('app.productType'), $upsData->package_type, ['class' => 'form-control selectpicker fexport_product_type', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group {{ $errors->has('nbr_pcs') ? 'has-error' :'' }}">
									<?php echo Form::label('nbr_pcs', 'No of Pcs', ['class' => 'col-md-5 control-label']); ?>
									<div class="col-md-6">
										<?php echo Form::text('nbr_pcs', $upsData->nbr_pcs, ['class' => 'form-control', 'placeholder' => 'Enter No of Pcs', 'id' => 'fimport_pices']); ?>
									</div>
								</div>
							</div>
						</div>
					<?php //} ?>



					<h4 class="formdeviderh4">Custom Details</h4>
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
								<?php echo Form::label('file_number', 'Custom File Number', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('Custom[file_number]', $customData->file_number, ['class' => 'form-control', 'placeholder' => 'Enter File Number']); ?>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group {{ $errors->has('custom_date') ? 'has-error' :'' }}">
								<?php echo Form::label('custom_date', 'Date', ['class' => 'col-md-4 control-label']); ?>
								<div class="col-md-6">
									<?php echo Form::text('Custom[custom_date]', date('d-m-Y', strtotime($customData->custom_date)), ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
								</div>
							</div>
						</div>
					</div>




				</form>


			</div>


		</div>


	</section>
	@endsection
	<?php
	$datas = App\Clients::getClientsAutocomplete();

	?>
	@section('page_level_js')
	<script type="text/javascript">
		$('.datepicker').datepicker({
			format: 'dd-mm-yyyy',
			todayHighlight: true,
			autoclose: true
		});
		$(document).ready(function() {
			$(document).keypress(function(e) {
				var key = e.which;
				if (key == 13) {
					$('#createforms').trigger('submit');
				}
			});

			$('#createforms').on('submit', function(event) {


				$('.ffile_name').each(function() {
					$(this).rules("add", {
						required: true,
					})
				});

				$('.fshipper_name').each(function() {
					$(this).rules("add", {
						required: true,
					})
				});
				$('.fconsignee_name').each(function() {
					$(this).rules("add", {
						required: true,
					})
				});
				/* $('.fconsignee_telephone').each(function() {
					$(this).rules("add", {
						number: true,
					})
				});
				$('.fshipper_telephone').each(function() {
					$(this).rules("add", {
						number: true,
					})
				}); */
				$('#fimport_weight').each(function() {
					$(this).rules("add", {
						number: true,
					})
				});
				$('#fimport_freight').each(function() {
					$(this).rules("add", {
						number: true,
					})
				});
				$('#fimport_pices').each(function() {
					$(this).rules("add", {
						number: true,
					})
				});
				$('.fawb_number').each(function() {
					$(this).rules("add", {
						required: false,
					})
				});


			});
			$('#createforms').validate({
				rules: {
					"awb_number": {
						required: true,
						checkAwbNumber: true
					},
					"Custom[file_number]": {
						checkUnique: true
					}
				}
			});

			$.validator.addMethod("checkUnique",
				function(value, element) {
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						}
					});
					var result = false;
					var urlz = '<?php echo url("customs/checkuniquecustomfilenumber"); ?>';

					var upsId = '<?php echo $upsData->id; ?>';
					$.ajax({
						type: "POST",
						async: false,
						url: urlz,
						data: {
							'value': value,
							'upsId': upsId
						},
						success: function(data) {
							result = (data == 0) ? true : false;
						}
					});
					// return true if username is exist in database
					return result;
				},
				"This number is already taken! Try another."
			);

			$.validator.addMethod("checkAwbNumber",
				function(value, element) {
					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						}
					});
					var result = false;
					var urlz = '<?php echo url("ups/checkuniqueawbnumber"); ?>';
					var flag = 'edit';
					var idz = '<?php echo $upsData->id; ?>';
					$.ajax({
						type: "POST",
						async: false,
						url: urlz,
						data: {
							number: value,
							flag: flag,
							idz: idz
						},
						success: function(data) {
							result = (data == 0) ? true : false;
						}
					});
					// return true if username is exist in database
					return result;
				},
				"This Awb Number is already taken! Try another."
			);


			<?php if ($upsData->id) { ?>
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
								var blnc = parseInt(balance.available_balance).toFixed(2);
								$('.cash_credit_account_balance').html(blnc);
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
								var blnc = parseInt(balance.available_balance).toFixed(2);
								$('.cash_credit_account_balance').html(blnc);
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

			$("#consignee_name").autocomplete({

				select: function(event, ui) {
					event.preventDefault();

					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						}
					});
					var clientId = ui.item.value;
					var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
					$.ajax({
						url: urlztnn,
						dataType: "json",
						async: false,
						type: 'POST',
						data: {
							'clientId': clientId
						},
						success: function(data) {
							console.log(data);
							$('#consignee_address').val(data.company_address);
							$('#consignee_telephone').val(data.phone_number);
						}
					});
				},
				focus: function(event, ui) {
					$('#loading').show();
					event.preventDefault();
					$("#consignee_name").val(ui.item.label);
					$('#loading').hide();
				},
				change: function(event, ui) {
					if (ui.item == null || typeof(ui.item) == "undefined") {
						//console.log("dsfdsf");
						//$('#loading').show();
						//$('#consignee_name').val("");
						//$('#loading').hide();

					}
				},
				source: <?php echo $datas; ?>,
				minLength: 1,
			});
			$("#shipper_name").autocomplete({

				select: function(event, ui) {
					event.preventDefault();

					$.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						}
					});
					var clientId = ui.item.value;
					var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
					$.ajax({
						url: urlztnn,
						dataType: "json",
						async: false,
						type: 'POST',
						data: {
							'clientId': clientId
						},
						success: function(data) {
							console.log(data);
							$('#shipper_address').val(data.company_address);
							$('#shipper_telephone').val(data.phone_number);
						}
					});
				},
				focus: function(event, ui) {
					$('#loading').show();
					event.preventDefault();
					$("#shipper_name").val(ui.item.label);
					$('#loading').hide();
				},
				change: function(event, ui) {
					if (ui.item == null || typeof(ui.item) == "undefined") {
						//console.log("dsfdsf");
						//$('#loading').show();
						//$('#consignee_name').val("");
						//$('#loading').hide();

					}
				},
				source: <?php echo $datas; ?>,
				minLength: 1,
			});


			$('#inprogress_scan_status').on('change', function() {
				$('#inprogress_scan_status').children().children().each(function() {
					var name = $(this).html();
					var status = $(this).prop('selected');
					if ((name == 'Other' || name == 'other') && status == true) {
						$('#otherDiv').show();
						return false;
					} else {
						$('#otherDiv').hide();
						$('#other_scan_status').val('');
					}
					//console.log(status);
				});
			});

			$('#ups_scan_status').change(function() {
				if ($(this).val() == 4) {
					$('#warehouse_div').show();
				} else {
					$('#warehouse_div').hide();
					$('#warehouses').val('');
				}
			});
		})
	</script>
	@stop