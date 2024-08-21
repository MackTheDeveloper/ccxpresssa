@extends('layouts.custom')
@section('title')
Cashier Detail
@stop
<?php if (auth()->user()->department == 11) { ?>
<?php } else { ?>

	@section('breadcrumbs')
	@include('menus.reports')
	@stop
<?php  } ?>
<?php

use Illuminate\Support\Facades\DB;
use App\Currency;
?>
@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 0px;font-weight: 600;">Cashier Details</h1>
</section>
<section class="content editupscontainer">

	<?php $arr = [];
	$totalPayment = 0; ?>
	<div class="box box-success">
		<div class="box-body">
			@foreach($cashierDetail as $details)
			<div id="div_basicdetails" class="notes box-s" style="margin-bottom:-15px;">Basic Details</div>
			<div class="detail-container">

				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Name : </span>
					<span class="viewblk2">{{$details->name}}</span>
				</div>

				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Email : </span>
					<span class="viewblk2">{{$details->email}}</span>
				</div>
			</div>

			@endforeach

			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;display:none">
				Received Payment Details
			</div>
			<div class="notes" style="padding: 10px;float: left;width: 100%;position: relative;display:none">
				<div style="float: left;" class="col-md-3">
					<b>Total Amount in HTG : </b><span style="margin-left: 2%" class="totalhtg">{{number_format($totalOfCurrency[3],2)}}</span>
				</div>
				<div style="float: left;" class="col-md-3">
					<b>Total Amount in USD : </b><span style="margin-left: 2%" class="totalusd">{{number_format($totalOfCurrency[1],2)}}</span>
				</div>

				<div style="float: left;" class="col-md-2">
					<b>Total : </b><span style="margin-left: 2%" class="finaltotal">
						{{number_format($totalOfCurrency['total'],2)}} HTG
					</span>
				</div>
				<div style="float: left;" class="col-md-2">
					{!! Form::open(['url'=>'invoice/searchByDate']) !!}
					<div class="col-md-3">
						{{Form::text('date','',['class'=>'form-control datepicker','placeholder'=>'Search By Date','style'=>'width:200px;','id'=>'filterreceivedpaymentbydate','autocomplete'=>'off'])}}</div>
					{!! Form::close() !!}

				</div>

				<div style="float: right;text-align: right" class="col-md-2">
					<a class="btn btn-warning showallpaymentdetails" href="javascript:void(0)">Show All</a>
					<a title="Click here to print" target="_blank" href="{{url('reports/printCashierReport/'.$cashierDetail[0]->id.'/')}}" id="pdfLink" style=""><i style="padding-top: 10px;padding-bottom: 10px;" class="fa fa-print btn btn-primary"></i></a>
				</div>
			</div>


			<div id="filterInvoiceInfoDiv" style="display:none">
				<table id="example" class="display nowrap" style="width:100%">
					<thead>
						<tr>
							<th>Invoice Currency</th>
							<th>Invoice Number</th>
							<th>Original Amount</th>
							<th>File Number</th>
							<th>Client</th>
							<th>Paid Amount</th>
							<th>Payment Currency</th>
							<th>Payment Amount</th>
							<th>Payment Via</th>
							<th>Payment Description</th>
							<th>Payment Date & Time</th>
						</tr>
					</thead>
					<tbody>
						<?php $totalPayment = 0;
						$arr = []; ?>
						@foreach($paymentReceivedByCashier as $paymentDetail)
						{{-- @if(!in_array($paymentDetail->invoice_id,$arr)) --}}
						<tr>
							<td>
								<?php $dataCurrency = Currency::getData($paymentDetail->invoiceCurrency);
								echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
							</td>
							<td>{{$paymentDetail->invoice_number}}</td>
							<td>{{$paymentDetail->originalAmount}}</td>
							<td>{{$paymentDetail->file_number}}</td>
							<td><?php $client = DB::table('clients')->where('id', $paymentDetail->client)->get();
									foreach ($client as $client)
										echo $client->company_name;
									?>

							</td>
							<td>{{$paymentDetail->amount}}</td>
							<td>
								<?php $currency = $paymentDetail->exchange_currency ?>
								@if($currency != '')
								<?php $dataCurrency = Currency::getData($paymentDetail->exchange_currency);
								echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
								@else
								{{"-"}}
								@endif
							</td>
							<td>
								@if($currency != '' && $paymentDetail->exchange_amount != '')
								{{$paymentDetail->exchange_amount}}
								@else
								{{"-"}}
								@endif
							</td>
							<td>{{$paymentDetail->payment_via}}</td>
							<td>
								@if($paymentDetail->payment_via_note != '')
								{{$paymentDetail->payment_via_note}}
								@else
								{{"-"}}
								@endif
							</td>
							<td>
								<?php echo date("d-m-Y h:i:s", strtotime($paymentDetail->created_at)); ?>
							</td>
						</tr>
						<?php array_push($arr, $paymentDetail->invoice_id) ?>

						{{-- @endif --}}

						@endforeach
					</tbody>

				</table>
			</div>
			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;">Disbursement Detail
				@foreach($expancesDetail as $expancesDetailCount)
				{{-- @if(!in_array($expancesDetailCount->expense_id,$arr)) --}}
				<?php $totalPayment += $expancesDetailCount->amount; ?>
				<?php array_push($arr, $expancesDetailCount->expense_id) ?>
				{{-- @endif --}}
				@endforeach
			</div>
			<div class="notes" style="padding: 10px;float: left;width: 100%">
				<div style="float: left;" class="col-md-3">
					<b>Total Disbursement : </b><span style="margin-left: 2%" class="totaldisbursement">{{number_format($totalPayment,2)}}</span>
				</div>
				<div style="float: left;" class="col-md-2">
					<b>USD : </b><span style="margin-left: 2%" class="totaldisbursementUsd">{{number_format($totalExpenseOfUSDCount,2)}}</span>
				</div>
				<div style="float: left;" class="col-md-2">
					<b>HTG : </b><span style="margin-left: 2%" class="totaldisbursementHtg">{{number_format($totalExpenseOfHtgCount,2)}}</span>
				</div>
				<div style="float: left;" class="col-md-2">
					{!! Form::open(['url'=>'invoice/searchByDate']) !!}
					<div class="col-md-3">
						{{Form::text('date','',['class'=>'form-control datepicker','placeholder'=>'Search By Date','style'=>'width:200px','id'=>'filterdisbursementbydate','autocomplete'=>'off'])}}</div>
					{!! Form::close() !!}

				</div>

				<div style="float: right;text-align: right" class="col-md-2">
					<a class="btn btn-warning showalldisbursementdetails" href="javascript:void(0)">Show All</a>
					<a title="Click here to print" target="_blank" href="{{url('reports/printCashierDisbursementReport/'.$cashierDetail[0]->id.'/')}}" id="pdfDisbursementLink"><i style="padding-top: 10px;padding-bottom: 10px;" class="fa fa-print btn btn-primary" style=""></i></a>
				</div>
			</div>
			<div class="detail-container">

				<div id="filterExpenceData">
					<table class="display nowrap" style="width:100%" id="expenses">
						<thead>
							<tr>
								<th>Date</th>
								<th>File Number</th>
								<th>Voucher Number</th>
								<th>Currency</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Paid To</th>
								<th>Cash/Bank</th>
							</tr>
						</thead>
						<tbody>
							<?php $arr = [];
							$totalPayment = 0; ?>

							@foreach($expancesDetail as $expancesDetail)
							{{-- @if(!in_array($expancesDetail->expense_id,$arr)) --}}
							<tr>
								<td><?php echo  !empty($expancesDetail->disbursed_datetime) ? date("d-m-Y", strtotime($expancesDetail->disbursed_datetime)) : '-'; ?></td>
								<td>
									<?php
									$file_number = DB::table('cargo')->where('id', $expancesDetail->file_number)->get();
									foreach ($file_number as $file_number) {
										echo $file_number->file_number;
									}
									?>
								</td>
								<td>{{$expancesDetail->voucher_number}}</td>
								<td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($expancesDetail->expense_id);
										echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
								<td>{{$expancesDetail->amount}}</td>
								<td>{{$expancesDetail->description}}</td>
								<td>
									<?php
									$paid_to = DB::table('vendors')->where('id', $expancesDetail->paid_to)->first();
									echo !empty($paid_to) ? $paid_to->company_name : '-';
									?>
								</td>
								<td>
									<?php
									$cashCredit = DB::table('cashcredit')->where('id', $expancesDetail->c_credit_account)->get();
									foreach ($cashCredit as $cashCredit) {
										if ($cashCredit->name != '')
											echo $cashCredit->name;
										else {
											echo ('-');
										}
									}
									?>

								</td>
							</tr>
							<?php array_push($arr, $expancesDetail->expense_id) ?>

							{{-- @endif --}}
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;display:none">
				Received Rent Detail
			</div>
			<div class="notes" style="padding: 10px;float: left;width: 100%;position: relative;display:none">
				<div style="float: left;" class="col-md-3">
					<b>Total Received Rent : </b><span style="margin-left: 2%" class="totallocatrent" id="totalreceivedrent">{{ number_format($totalAcceptedRent,2)}} HTG</span>
				</div>
				<div style="float: left;" class="col-md-3">
					{!! Form::open(['url'=>'invoice/searchByDate']) !!}
					<div class="col-md-3">
						{{Form::text('date','',['class'=>'form-control datepicker','placeholder'=>'Search By Date','style'=>'width:200px','id'=>'filterlocalrentbydate','autocomplete'=>'off'])}}</div>
					{!! Form::close() !!}

				</div>
				<div style="float: right;text-align: right" class="col-md-2">
					<a class="btn btn-warning showalllocalrentaldetail" href="javascript:void(0)">Show All</a>
				</div>
			</div>
			<div class="detail-container" style="display:none">
				<div style="float: right;width: 200px;margin-right: 15%;height: 35px; position: relative;">
					<a title="Click here to print" target="_blank" href="{{url('reports/printLocalRentReport/'.$cashierDetail[0]->id.'/')}}" id="pdfLocalRentLink"><i class="fa fa-print btn btn-primary" style="position: absolute;left: 1%;z-index: 111;top:134%""></i></a>
        	</div>
        	<div id=" filterData">
							<table id="localrental" class="display nowrap" style="width:100%">
								<thead>
									<tr>
										<th>Invoice Number</th>
										<th>Original Amount</th>
										<th>File Number</th>
										<th>Client</th>
										<th>Duration</th>
										<th>Paid Amount</th>
										<th>Payment Currency</th>
										<th>Payment Date & Time</th>

									</tr>
								</thead>

								<tbody>
									@foreach($localInvoicePaymentDetail as $localInvoiceData)
									<tr>
										<td>{{$localInvoiceData->id}}</td>
										<td>{{$localInvoiceData->total}}</td>
										<td>{{$localInvoiceData->file_number}}</td>
										<td>
											<?php $client = DB::table('clients')->where('id', $localInvoiceData->billing_party)->get();
											foreach ($client as $client)
												echo $client->company_name;
											?>
										</td>
										<td>{{$localInvoiceData->duration}}</td>
										<td>{{$localInvoiceData->total}}</td>
										<td>HTG</td>
										<td><?php echo date('d-m-Y h:i:s', strtotime($localInvoiceData->updated_at)); ?></td>
									</tr>
									@endforeach
								</tbody>
							</table>
				</div>
			</div>

		</div>


	</div>
</section>
<script type="text/javascript">
	$('.datepicker').datepicker({
		format: 'dd-mm-yyyy',
		todayHighlight: true,
		autoclose: true
	});
	jQuery.extend(jQuery.fn.dataTableExt.oSort, {
		"date-uk-pre": function(a) {
			if (a == null || a == "") {
				return 0;
			}
			var ukDatea = a.split('-');
			return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
		},

		"date-uk-asc": function(a, b) {
			return ((a < b) ? -1 : ((a > b) ? 1 : 0));
		},

		"date-uk-desc": function(a, b) {
			return ((a < b) ? 1 : ((a > b) ? -1 : 0));
		}
	});
	$('#expenses').DataTable({
		'stateSave': true,
		stateSaveParams: function(settings, data) {
			delete data.order;
		},
		"columnDefs": [{
			"targets": [-1],
			"orderable": false
		}, {
			type: 'date-uk',
			targets: 0
		}],
		"aaSorting": [],
		//"order": [[ 0, "desc" ]],
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

	$('#example,#localrental').DataTable({
		'stateSave': true,
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

	$('#filterreceivedpaymentbydate').focusin(function() {

		$('#pdfLink').hide();
	});
	$('#filterreceivedpaymentbydate').focusout(function() {
		$('#pdfLink').show();
	});
	$('.showallpaymentdetails').click(function() {
		$('#filterreceivedpaymentbydate').val('');
		$('#filterreceivedpaymentbydate').trigger('change');
	})

	$('.showalldisbursementdetails').click(function() {
		$('#filterdisbursementbydate').val('');
		$('#filterdisbursementbydate').trigger('change');
	})

	$('.showalllocalrentaldetail').click(function() {
		$('#filterlocalrentbydate').val('');
		$('#filterlocalrentbydate').trigger('change');
	});


	var link = $('#pdfLink').attr("href");
	var disbursementLink = $('#pdfDisbursementLink').attr("href");
	var localRentLink = $('#pdfLocalRentLink').attr("href");
	$('#filterreceivedpaymentbydate').change(function() {
		$('#loading').show();
		var cashierId = '<?php echo $cashierDetail[0]->id; ?>';
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		var date = $('#filterreceivedpaymentbydate').val();
		if (date == '')
			$('#pdfLink').attr("href", link);
		else
			$('#pdfLink').attr("href", link + '/' + date);
		$.ajax({
			async: false,
			type: 'POST',
			url: "{{url('reports/filterreceivedpaymentbydate')}}",
			data: {
				date: date,
				cashierId: cashierId
			},
			success: function(res) {
				$('#filterInvoiceInfoDiv').html(res);
				$.ajax({
					dataType: "json",
					async: false,
					type: 'POST',
					url: "{{url('reports/gettotalsincurrencies')}}",
					data: {
						date: date,
						cashierId: cashierId
					},
					success: function(res) {
						$('.totalhtg').html(res[3]);
						$('.totalusd').html(res[1]);
						$('.finaltotal').html(res['total']);

						$('#loading').hide();

					}
				})

			}
		})
	})

	$('#filterdisbursementbydate').change(function() {
		$('#loading').show();
		var cashierId = '<?php echo $cashierDetail[0]->id; ?>';
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		var date = $('#filterdisbursementbydate').val();
		if (date == '')
			$('#pdfDisbursementLink').attr("href", disbursementLink);
		else
			$('#pdfDisbursementLink').attr("href", disbursementLink + '/' + date);

		$.ajax({
			//async:false,
			type: 'POST',
			url: "{{url('reports/filterdisbursementbydate')}}",
			data: {
				date: date,
				cashierId: cashierId
			},
			success: function(res) {
				$('#filterExpenceData').html(res);
				$.ajax({
					//async:false,
					dataType: "json",
					type: 'POST',
					url: "{{url('reports/gettotalsofdisbursement')}}",
					data: {
						date: date,
						cashierId: cashierId
					},
					success: function(res) {
						$('#loading').hide();

						$('.totaldisbursement').html(res.totalPayment);
						$('.totaldisbursementUsd').html(res.totalExpenseOfUSDCount);
						$('.totaldisbursementHtg').html(res.totalExpenseOfHtgCount);
					}
				})

			}
		})
	})

	$('#filterlocalrentbydate').change(function() {
		$("#loading").show();
		var cashierId = '<?php echo $cashierDetail[0]->id; ?>';

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		var date = $('#filterlocalrentbydate').val();
		if (date == '')
			$('#pdfLocalRentLink').attr("href", localRentLink);
		else
			$('#pdfLocalRentLink').attr("href", localRentLink + '/' + date);

		$.ajax({

			type: 'POST',
			url: "{{url('reports/filterlocalrentbydate')}}",
			data: {
				date: date,
				cashierId: cashierId
			},
			success: function(res) {
				console.log(res);
				$("#loading").hide();
				$('#filterData').html(res);
				$('#totalreceivedrent').html($('#total').val() + ' ' + 'HTG');

			}
		})
	});
</script>
@endsection