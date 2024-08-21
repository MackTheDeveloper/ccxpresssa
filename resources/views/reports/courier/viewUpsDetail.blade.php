
@if(!empty($data))

	<table class="table table-bordered">
		<tr>
			<th>File Number</th>
			<th>{{$basicDetail->file_number}}</th>
			<th><?php echo $basicDetail->courier_operation_type == 1 ? 'Import File' : 'Export File'?></th>
		</tr>
		<tr>
			<td>Name Of Client</td>
			<td><?php $newdata = app('App\Clients')->getClientData($basicDetail->consignee_name); echo !empty($newdata->company_name) ? $newdata->company_name : '-'; ?></td>
		</tr>
		<tr>
			<td style="color: white;">1</td>
			<td style="color: white;">1</td>
		</tr>
		<tr>
			<th>List Of Revenue</th>
			<th>Amount</th>
			<th>List Of expenses</th>
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
					{{number_format($balance,2)}}
				</td>
			</tr>
			
			
		@endforeach
		<tr>
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