<html>
<head>
	<title>Disbursement Report</title>
</head>
<body>
	<?php use Illuminate\Support\Facades\DB;
		  use App\Currency;
	?>
	<section class="content" style="font-family: sans-serif;">

    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">
				<div style="float: left;width: 100%; margin: 5px 0 5px 0;">
						<div style="text-align: left;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">
						Chatelain Cargo Services
						</div>
						<div style="text-align: right;float: left; width: 50%; font-size: 18px; font-weight: 600;text-transform: uppercase;">Disbursement Report <?php echo !empty($date) ? date('d-m-Y',strtotime($date)) : ''; ?></div>
						
					</div>
					<table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-size:12px;font-family: Asap, sans-serif;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #000;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse">
							<thead>
				<thead>
					<tr>
						<th width="70px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">File</th>
						<th width="100px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Voucher Number</th>
						<th width="70px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Currency</th>
						<th width="70px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Amount</th>
						<th width="169px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Description</th>
						<th width="100px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Paid To</th>
						<th width="100px"  style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: left;">Cash/Bank</th>
					</tr>
				</thead>
			<tbody>
			<?php $arr = [];$totalPayment = 0;?>

			@foreach($expancesDetail as $expancesDetail)
			{{-- @if(!in_array($expancesDetail->expense_id,$arr)) --}}
				<tr>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
						<?php 
							$file_number = DB::table('cargo')->where('id',$expancesDetail->file_number)->get();
							foreach($file_number as $file_number){
								echo $file_number->file_number;
							}
						?>
					</td>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$expancesDetail->voucher_number}}</td>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);"><?php $dataCurrency = App\Vendors::getDataFromPaidTo($expancesDetail->expense_id); echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);text-align:right">{{$expancesDetail->amount}}</td>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">{{$expancesDetail->description}}</td>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
						<?php 
							$paid_to = DB::table('vendors')->where('id',$expancesDetail->paid_to)->first();
							echo !empty($paid_to) ? $paid_to->company_name : '-';
							?>
					</td>
					<td style="padding:5px;border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%);">
						<?php 
							$cashCredit = DB::table('cashcredit')->where('id',$expancesDetail->c_credit_account)->get();
							foreach($cashCredit as $cashCredit){
								if($cashCredit->name != '')
									echo $cashCredit->name;
								else{
									echo('-');
								}
							}
							?>
						
					</td>
				</tr>
				<?php $totalPayment += $expancesDetail->amount; array_push($arr,$expancesDetail->expense_id)?>
			
			{{-- @endif --}}
			@endforeach
		</tbody>
	</table>
	<?php  ?>
	<div style="background: #f3f3f3;float: right;padding: 5px;">
			<div style="width: 60%;float: left;">Total Expense</div>
			<div style="width: 40%;float: left;text-align: right;"><?php echo number_format($totalPayment,2);  ?></div>
	</div>
</div>
</div>
</section>
</body>
</html>