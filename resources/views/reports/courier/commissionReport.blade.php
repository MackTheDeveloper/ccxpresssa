@extends('layouts.custom')

@section('title')
Courier commission Report
@stop

@section('breadcrumbs')
@include('menus.reports')
@stop


@section('content')
<section>
	<div class="row">
		<div class="col-md-3 content-header" style="margin-left: 1%">
			<h1>Commission Report</h1>
		</div>
	</div>
</section>
<section class="content editupscontainer">
	<div class="box box-success">
		<div class="box-body">
			<div class="row">
				{{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
				{{ csrf_field() }}
				<div class="col-md-2">
					<?php echo Form::select('file_name', ['ups' => 'UPS', 'aeropost' => 'Aero-Post'], 'ups', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'file_type']); ?>
				</div>
				<div class="col-md-2 typeimpexpdiv">
					<?php echo Form::select('typeimpexp', ['' => 'All', '1' => 'Import', '2' => 'Export'], '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'typeimpexp']); ?>
				</div>
				<div class="from-date-filter-div filterout col-md-2">
					<input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
				</div>
				<div class="to-date-filter-div filterout col-md-2">
					<input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
				</div>
				<button type="submit" style="float:left;margin-right:5px" class="btn btn-success submitfilter">Submit</button>
				<button style="margin-right:5px" type="submit" id="clsPrint" class="btn btn-success">Print</button>
				<button style="margin-right:5px" id="clsExportToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true" style="margin-right: 3%"></i></span>Export To Excel</button>
				<button id="clsMail" class="btn btn-primary"><span><i class="fa fa-paper-plane fa-paper-plane" aria-hidden="true" style="margin-right: 3%"></i></span>Send Report</button>

				{{ Form::close() }}
			</div>
			
			<div id="filter_data" style="margin-top: 2%">
				<table id="example" class="display nowrap" style="width:100%;">
					<thead>
						<tr>
							<th>File Number</th>
							<th>Date</th>
							<th>Awb Number</th>
							<th>Billing Term</th>
							<th>Freight Rev</th>
							<th>Commission</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
	<iframe src="" id="frame" style="display: none;"></iframe>
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
		DatatableInitiate();
	})

	$('#file_type').on('change', function() {
		//$('#loading').show();
		var courier_type = $(this).val();
		if (courier_type == 'ups')
			$('.typeimpexpdiv').show();
		else
			$('.typeimpexpdiv').hide();
	});

	$('#createInvoiceForm').on('submit', function(event) {});

	$('#createInvoiceForm').validate({
		submitHandler: function(form) {
			var fromDate = $('.from-date-filter').val();
			if (fromDate == '')
				fromDate = 0;
			var toDate = $('.to-date-filter').val();
			if (toDate == '')
				toDate = 0;
			var file_type = $('#file_type').val();
			if (file_type == '')
				file_type = 0;
			var typeimpexp = $('#typeimpexp').val();
			if (typeimpexp == '')
				typeimpexp = 0;
			var submitButtonName = $(this.submitButton).attr("id");
			if (submitButtonName == 'clsPrint' || submitButtonName == 'clsExportToExcel' || submitButtonName == 'clsMail') {
				var urlztnn = '<?php echo url("reports/printandexportcommissionReport"); ?>';
				urlztnn += '/' + fromDate + '/' + toDate + '/' + file_type + '/' + typeimpexp + '/' + submitButtonName;
				$('#loading').show();
				$.ajax({
					url: urlztnn,
					//async: false,
					type: 'GET',
					/* data: {
						'fromDate': fromDate,
						'toDate': toDate,
						'file_type': file_type,
						'typeimpexp': typeimpexp,
						'submitButtonName': submitButtonName
					}, */
					success: function(dataRes) {
						if (submitButtonName == 'clsPrint')
							window.open(dataRes, '_blank');
						else {
							window.open(urlztnn, '_blank');
						}
						$('#loading').hide();
					}
				});
			} else {
				DatatableInitiate(fromDate, toDate, file_type, typeimpexp);
			}
		},
	});

	function DatatableInitiate(fromDate = '', toDate = '', file_type = 'ups', typeimpexp = '') {
		$('#example').DataTable({
			"bDestroy": true,
			"processing": true,
			"serverSide": true,
			'stateSave': true,
			stateSaveParams: function(settings, data) {
				delete data.order;
			},
			"columnDefs": [{
				"targets": [],
				"orderable": false
			}, ],
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			"ajax": {
				url: "{{url('reports/listgetcommissionReport')}}", // json datasource
				data: function(d) {
					d.file_type = file_type;
					d.typeimpexp = typeimpexp;
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
				$('td', row).eq(4).addClass('alignright');
				$('td', row).eq(5).addClass('alignright');
			}
		});
	}
</script>
@stop