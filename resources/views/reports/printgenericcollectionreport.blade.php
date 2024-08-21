<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
	<title>Collection Report</title>
</head>

<body>
	<?php

	use Illuminate\Support\Facades\DB;
	use App\Currency;
	?>
	<section class="content" style="font-family: sans-serif;">

		<div class="box box-success" style="width: 100%;margin: 0px auto;">
			<div class="box-body cargo-forms" style="color: #000">
				<div style="float: left;width: 100%; margin: 5px 0 5px 0;">
					<div style="text-align: left;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
						Chatelain Cargo Services
					</div>
					<div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Collection Report</div>
					<div style="text-align:right"><?php echo !empty($fromDate) ? date('d-m-Y', strtotime($fromDate)) : ''; ?> - <?php echo !empty($toDate) ? date('d-m-Y', strtotime($toDate)) : ''; ?></div>

				</div>
				<div style="float: left;width: 100%; margin: 5px 0 5px 0;">
					<div style="width:30%;float:left;display:none">
						<div>Total in HTG: {{number_format($totalOfCurrency['totalInHtg'],2)}}</div>
					</div>
					<?php if (!empty($currencySingle) && $currencySingle->code == 'HTG') { ?>
						<div style="width:30%;float:left;text-align:left">
							<div>HTG : {{number_format($totalOfCurrency[3],2)}}</div>
						</div>
					<?php } ?>
					<?php if (!empty($currencySingle) && $currencySingle->code == 'USD') { ?>
						<div style="width:30%;float:left;text-align:left">
							<div>USD : {{number_format($totalOfCurrency[1],2)}}</div>
						</div>
					<?php } ?>
				</div>
				<table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-size:14px;font-family: Asap, sans-serif;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
					<thead>
						<thead>
							<tr>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Invoice</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File Number</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Receipt No.</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Consignee</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Currency</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Original Amount</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Payable Amount</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Payment Currency</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Paid Amount</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Payment Via</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Payment Description</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Payment Date & Time</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Received By</th>

							</tr>
						</thead>
					<tbody>


						@foreach($paymentReceivedByCashierNew as $paymentDetail)
						{{-- @if(!in_array($expancesDetail->expense_id,$arr)) --}}
						<tr>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$paymentDetail->invoice_number}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$paymentDetail->file_number}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$paymentDetail->receipt_number}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);text-align:right">{{$paymentDetail->consignee_address}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								<?php $dataCurrency = Currency::getData($paymentDetail->invoiceCurrency);
								echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);text-align:right">{{$paymentDetail->originalAmount}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);text-align:right">{{$paymentDetail->amount}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								<?php $currency = $paymentDetail->exchange_currency ?>
								@if($currency != '')
								<?php $dataCurrency = Currency::getData($paymentDetail->exchange_currency);
								echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
								@else
								{{"-"}}
								@endif
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);text-align:right">
								@if($currency != '' && $paymentDetail->exchange_amount != '')
								{{$paymentDetail->exchange_amount}}
								@else
								{{"-"}}
								@endif
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$paymentDetail->payment_via}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								@if($paymentDetail->payment_via_note != '')
								{{$paymentDetail->payment_via_note}}
								@else
								{{"-"}}
								@endif
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								<?php echo date("d-m-Y h:i:s", strtotime($paymentDetail->created_at)); ?>
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$paymentDetail->paymentReceivedBy}}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				<?php  ?>
			</div>
		</div>
	</section>
</body>

</html>