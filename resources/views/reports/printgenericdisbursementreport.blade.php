<?php ini_set("pcre.backtrack_limit", "5000000"); ?>
<html>

<head>
	<title>Disbursement Report</title>
</head>

<body>
	<?php

	use Illuminate\Support\Facades\DB;
	use App\Currency;

	$totalPayment = 0;
	foreach ($allExpenseDetails as $expancesDetailCount) {
		$totalPayment += $expancesDetailCount->amount;
	}

	?>
	<section class="content" style="font-family: sans-serif;">

		<div class="box box-success" style="width: 100%;margin: 0px auto;">
			<div class="box-body cargo-forms" style="color: #000">
				<div style="float: left;width: 100%; margin: 5px 0 5px 0;">
					<div style="text-align: left;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
						Chatelain Cargo Services
					</div>
					<div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Disbursement Report</div>
					<div style="text-align:right"><?php echo !empty($fromDate) ? date('d-m-Y', strtotime($fromDate)) : ''; ?> - <?php echo !empty($toDate) ? date('d-m-Y', strtotime($toDate)) : ''; ?></div>

				</div>
				<div style="float: left;width: 100%; margin: 5px 0 5px 0;">
					<div style="width:30%;float:left;display:none">
						<div>Total Disbursement : <?php echo number_format($totalPayment, 2); ?></div>
					</div>
					<?php if (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'USD') { ?>
						<div style="width:50%;float:left;text-align:left">
							<div><?php echo $cashBankSingle->name; ?> (USD) : <?php echo number_format($allTotalExpenseOfUSDCount, 2); ?></div>
						</div>
					<?php } ?>
					<?php if (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'HTG') { ?>
						<div style="width:50%;float:left;text-align:left">
							<div><?php echo $cashBankSingle->name; ?> (HTG) : <?php echo number_format($allTotalExpenseOfHtgCount, 2); ?></div>
						</div>
					<?php } ?>
				</div>
				<table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: wrap; text-align:left; font-variant: normal; font-size:14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
					<thead>
						<thead>
							<tr>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Voucher Number</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Currency</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Amount</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Description</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Disbursed Note</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Paid To</th>
								<th style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Disbursed By</th>
							</tr>
						</thead>
					<tbody>
						<?php $arr = [];
						$totalPayment = 0; ?>

						@foreach($allExpenseDetails as $expancesDetail)
						{{-- @if(!in_array($expancesDetail->expense_id,$arr)) --}}
						<?php $dataCashCredit = App\CashCredit::getCashCreditData($expancesDetail->c_credit_account) ?>
						<tr>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								<?php echo $expancesDetail->file_number; ?>
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$expancesDetail->voucher_number}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								<?php $dataCurrency = App\Currency::getData($dataCashCredit->currency);
								echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?></td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);text-align:right">{{number_format($expancesDetail->amount,2)}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$expancesDetail->description}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$expancesDetail->expense_request_status_note}}</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
								<?php
								$paid_to = DB::table('vendors')->where('id', $expancesDetail->paid_to)->first();
								echo !empty($paid_to) ? $paid_to->company_name : '-';
								?>
							</td>
							<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$expancesDetail->name}}</td>
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