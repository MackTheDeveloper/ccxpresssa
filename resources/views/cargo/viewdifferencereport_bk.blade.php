
@if(!empty($data))

	<table class="table table-bordered">
		
		<tr style="background: #adeaea;">
			<th>Billing Item</th>
			<th>Revenue Amount</th>
			<th>Cost Item</th>
			<th>Expense Amount</th>
			<th>Balance</th>
		</tr>
		
		<?php 
			$invoicesum = 0;
			$expensesum = 0;
			$balanceTotal = 0;
		?>
		@foreach($data as $nwdata)

			<?php $invoicesum += $nwdata->item_cost;
				$expensesum += $nwdata->expences_item_amount;
			 	$balance = $nwdata->item_cost - $nwdata->expences_item_amount;
			 	$balanceTotal += $balance;
			 ?>
			<tr>
				<td>
					{{$nwdata->revenue}}
				</td>
				<td>
					{{number_format($nwdata->item_cost,2)}}
				</td>
				<td>
					{{$nwdata->costs_name}}
				</td>
				<td>
					{{number_format($nwdata->expences_item_amount,2)}}
				</td>
				<td>
					<span style="{{(number_format($balance,2) < 0) ? 'color:red' : ''}}">{{number_format($balance,2)}}</span>
				</td>
			</tr>
			
			
		@endforeach
		<tr style="background: #ccc;font-weight: bold;">
				<td style="border: none;"></td>
				<td>{{number_format($invoicesum,2)}}</td>
				<td></td>
				<td>{{number_format($expensesum,2)}}</td>
				<td>{{number_format($balanceTotal,2)}}</td>
			</tr>
	
		
	</table>
@else 
	<h4>No Data Found.</h4>
@endif