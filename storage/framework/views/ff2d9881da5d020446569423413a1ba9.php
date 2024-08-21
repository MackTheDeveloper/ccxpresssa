<?php $__env->startSection('title'); ?>
Combine Report
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php

use App\Ups; ?>
<?php $__env->startSection('content'); ?>
<section>
	<div class="row">
		<div class="col-md-12 content-header" style="margin-left: 1%">
			<h1 id="headingDiv">Combine Report (Cargo & Courier)</h1>
		</div>
	</div>
</section>
<section class="content editupscontainer">
	<div class="box box-success">
		<div class="box-body">
			<?php $actionUrl = url('reports/combinereports/submit') ?>
			<?php echo e(Form::open(array('url' => $actionUrl,'class'=>'form-horizontal','id'=>'combineReportsForm','autocomplete'=>'off'))); ?>

			<?php echo e(csrf_field()); ?>

			<div class="row">
				<div class="col-md-12" style="margin-bottom: 10px">
					<div class="col-md-2">
						<?php echo Form::select('file_type', ['cargo' => 'Cargo', 'courier' => 'Courier'], 'cargo', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'file_type']); ?>
					</div>
					<div id="Cargo">
						<div class="col-md-2">
							<?php echo Form::select('cargo_operation_type', [0 => 'All Files (I, E & L)', 1 => 'Import', 2 => 'Export', 3 => 'Local'], 0, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'operation_type']); ?>

						</div>
					</div>

					<div id="Courier" style="display: none;">
						<div class="col-md-2">
							<?php //echo Form::select('courier_type', [0 => 'All Couriers (Ups , Aeropost , CCpack )', 1 => 'Ups', 2 => 'Aeropost', 3 => 'CCpack'], 0, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'courier_type']); 
							echo Form::select('courier_type', [1 => 'Ups', 4 => 'UPS Master', 2 => 'Aeropost', 5 => 'Aeropost Master', 3 => 'CCpack', 6 => 'CCPack Master'], 0, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'courier_type']);
							?>

						</div>
						<div class="col-md-2" id="courier_operation_type">
							<?php echo Form::select('courier_operation_type', [0 => 'All Files (I, E)', 1 => 'Import', 2 => 'Export'], 0, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'courier_operation_type_dp']); ?>

						</div>
					</div>

					<div class="col-md-2">
						<?php echo Form::select('billing_party', $billingParty, null, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'billing_party', 'placeholder' => 'Select Billing Party...']); ?>
					</div>

					<div class="from-date-filter-div filterout col-md-2">
						<input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
					</div>
					<div class="to-date-filter-div filterout col-md-2">
						<input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
					</div>
				</div>
				<div class="col-md-12">
					<div class="col-md-2" style="float: right;text-align:right">
						<button type="submit" class="btn btn-success">Submit</button>
						<button type="submit" id="clsPrint" class="btn btn-success">Print</button>
					</div>
				</div>
			</div>

			<?php echo e(Form::close()); ?>

			<div style="float: right;width: 200px;margin-right: 15%;height: 35px; position: relative;">


				<a title="Click here to print" target="_blank" href="javascript:void(0)" id="printcombinereport"><i class="fa fa-print btn btn-primary" style="position: absolute;left: 1%;z-index: 111;top:134%;display:none"></i></a>
			</div>
			<div id="filteredData">
				<table id="example" class="display" style="width:100%">
					<thead>
						<tr>
							<th style="display: none">ID</th>
							<th style="display: none">Invoice ID</th>
							<th>Date</th>
							<th>File Number</th>
							<th>Awb No.</th>
							<th>Billing Party</th>
							<th>Total Amount ($)</th>
							<th>Credits ($)</th>
							<th>Status</th>
						</tr>
					</thead>

				</table>
			</div>
		</div>
	</div>
</section>
<style>
	.hide_column {
		display: none;
	}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
	$(document).ready(function() {
		$('.datepicker').datepicker({
			format: 'dd-mm-yyyy',
			todayHighlight: true,
			autoclose: true
		});
		DatatableInitiate();
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$('#file_type').on('change', function() {
			var file_type = $(this).val();

			if (file_type == 'courier') {
				$('#Courier').show();
				$('#Cargo').hide();
			} else {
				$('#Courier').hide();
				$('#Cargo').show();
			}
		});

		$('#reset').click(function() {
			location.reload();
		});

		$('#courier_type').on('change', function() {
			var courierType = $(this).val();
			if (courierType == 2) {

				$('#courier_operation_type').css('display', 'none');

			} else {

				$('#courier_operation_type').css('display', 'block');
			}
		});

		/* $('#combineReportsForm').on('submit', function(e) {
			$('#loading').show();
			e.preventDefault();
			var form_data = $(this).serialize();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$.ajax({
				url: '<?php //echo url("reports/filtercombinereport/f") 
							?>',
				type: 'POST',
				data: form_data,
				success: function(data) {
					$('#filteredData').html(data);

					$('#headingDiv').html($('#heading').val());
					$('#loading').hide();
				}
			});
		}); */

		$('#combineReportsForm').validate({
			submitHandler: function(form) {
				var fromDate = $('.from-date-filter').val();
				if (fromDate == '')
					fromDate = 0;
				var toDate = $('.to-date-filter').val();
				if (toDate == '')
					toDate = 0;


				var moduleType = $('#file_type').val();
				if (moduleType == 'cargo') {
					var operationType = $('#operation_type').val();
					var courierType = '';
				} else {
					var operationType = $('#courier_operation_type_dp').val();
					var courierType = $('#courier_type').val();
				}
				var billingParty = $('#billing_party').val();



				var submitButtonName = $(this.submitButton).attr("id");
				if (submitButtonName == 'clsPrint') {
					var urlztnn = '<?php echo url("reports/filtercombinereport/p"); ?>';
					$.ajax({
						url: urlztnn,
						async: false,
						type: 'POST',
						data: {
							'moduleType': moduleType,
							'courierType': courierType,
							'operationType': operationType,
							'billingParty': billingParty,
							'fromDate': fromDate,
							'toDate': toDate
						},
						success: function(dataRes) {
							window.open(dataRes, '_blank');
						}
					});
				} else {
					DatatableInitiate(moduleType, courierType, operationType, billingParty, fromDate, toDate);
				}
			},
		});


		/* $('#printcombinereport').on('click', function(e) {
			$('#loading').show();
			e.preventDefault();
			var form_data = $('#combineReportsForm').serialize();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$.ajax({
				url: '<?php //echo url("reports/filtercombinereport/p") 
							?>',
				type: 'POST',
				data: form_data,
				success: function(data) {

					window.open(data, '_blank');
					$('#loading').hide();
				}
			});
		}); */
	});

	function DatatableInitiate(moduleType = 'cargo', courierType = '0', operationType = '', billingParty = '', fromDate = '', toDate = '') {
		var i = 1;
		var table = $('#example').DataTable({
			"bDestroy": true,
			"processing": true,
			"serverSide": true,
			'stateSave': true,
			stateSaveParams: function(settings, data) {
				delete data.order;
			},
			"columnDefs": [{
				targets: [0, 1],
				className: "hide_column"
			}],
			"scrollX": true,
			"order": [
				[1, "desc"]
			],
			"ajax": {
				url: "<?php echo e(url('reports/listcombinereport')); ?>", // json datasource
				data: function(d) {
					d.moduleType = moduleType;
					d.courierType = courierType;
					d.operationType = operationType;
					d.billingParty = billingParty;
					d.fromDate = fromDate;
					d.toDate = toDate;
				},
				error: function() { // error handling
					$(".example-error").html("");
					$("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#example_processing").css("display", "none");

				}
			},
			"createdRow": function(row, data, index) {
				if (moduleType == 'courier') {
					if (courierType == '1') {
						var UpsId = data[0];
						var editLink = '<?php echo url("ups/viewdetails"); ?>';
						editLink += '/' + UpsId;
						$(row).attr('id', UpsId);
					} else if (courierType == '4') {
						var UpsMasterId = data[0];
						var editLink = '<?php echo url("ups-master/view"); ?>';
						editLink += '/' + UpsMasterId;
						$(row).attr('id', UpsMasterId);
					} else if (courierType == '2') {
						var AeropostId = data[0];
						var editLink = '<?php echo url("aeropost/viewdetailsaeropost"); ?>';
						editLink += '/' + AeropostId;
						$(row).attr('id', AeropostId);
					} else if (courierType == '5') {
						var AeropostMasterId = data[0];
						var editLink = '<?php echo url("aeropost-master/view"); ?>';
						editLink += '/' + AeropostMasterId;
						$(row).attr('id', AeropostMasterId);
					} else if (courierType == '6') {
						var CcpackMasterId = data[0];
						var editLink = '<?php echo url("ccpack-master/view"); ?>';
						editLink += '/' + CcpackMasterId;
						$(row).attr('id', CcpackMasterId);
					} else if (courierType == '3') {
						var CCPackId = data[0];
						var editLink = '<?php echo url("ccpack/viewdetailsccpack"); ?>';
						editLink += '/' + CCPackId;
						$(row).attr('id', CCPackId);
					}
					$(row).attr('data-editlink', editLink);
					$(row).addClass('edit-row');
				} else {
					var cargoId = data[0];
					var url = '<?php echo url("cargo/checkoperationfordatatableserverside"); ?>';
					$.ajax({
						url: url,
						type: 'POST',
						dataType: 'json',
						data: {
							'cargoid': cargoId,
							'flag': 'getCargoData'
						},
						success: function(data) {
							if (data.cargo_operation_type == 3) {
								var editLink = '<?php echo url("viewcargolocalfiledetailforcashier"); ?>';
								editLink += '/' + cargoId;
							} else {
								var editLink = '<?php echo url("cargo/viewcargo"); ?>';
								editLink += '/' + cargoId + '/' + data.cargo_operation_type;
							}
							$(row).attr('data-editlink', editLink);
							$(row).addClass('edit-row');
							$(row).attr('id', cargoId);
						}
					});
				}
				var invoiceId = data[1];
				$('#loading').show();
				setTimeout(function() {
					$("#loading").hide();
				}, 2000);
				var thiz = $(this);

				var url = '<?php echo url("invoices/checkoperationfordatatableserverside"); ?>';
				$.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: {
						'invoiceId': invoiceId,
						'flag': 'getInvoiceData'
					},
					success: function(dataR) {
						if (dataR.payment_status == 'Paid')
							var style = 'color:green';
						else
							var style = 'color:red';
						$('td', row).eq(8).attr('style', style);
					}
				});

				$('td', row).eq(6).addClass('alignright');
				$('td', row).eq(7).addClass('alignright');
				i++;
			}
		});
	};
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/reports/combinereport.blade.php ENDPATH**/ ?>