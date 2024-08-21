<?php 
/* pre($getBillingItemData,1);
pre($getCostItemData,1);  */
if(count($getCostItemData) > count($getBillingItemData) || count($getCostItemData) == count($getBillingItemData))
{ ?>
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
			$excludeBillingItems = $getBillingItemData;			
			foreach($getCostItemData as $k => $v) { ?>
			
				<?php $getRelatedBillingId = DB::table('billing_items')->where('code',$v->costItemId)->first(); 
				$biliingItemDescription = '';
				$biliingItemAmount = '';
				foreach($getBillingItemData as $k1 => $v1)
				{
					if(!empty($getRelatedBillingId) && $v1->biliingItemId == $getRelatedBillingId->id)
					{ 
						$biliingItemDescription = $v1->biliingItemDescription;
						$biliingItemAmount = $v1->biliingItemAmount;
						unset($excludeBillingItems[$k1]);
					}
				}
				?>
				<?php
				$invoicesum += $biliingItemAmount;
				$expensesum += $v->costAmount;
				$balance = $biliingItemAmount - $v->costAmount;
			 	$balanceTotal += $balance;;
				?>
				<tr>
					<td><?php echo $biliingItemDescription; ?></td>
					<td style="text-align:right;"><?php echo $biliingItemAmount; ?></td>
					<td><?php echo $v->costDescription; ?></td>
					<td style="text-align:right;"><?php echo $v->costAmount; ?></td>
					<td style="text-align:right;color:<?php echo ($biliingItemAmount - $v->costAmount) < 0 ? 'red' : ''; ?>"><?php echo number_format($biliingItemAmount - $v->costAmount,2); ?></td>

				</tr>

		<?php } ?>
		<?php 
		//pre($excludeBillingItems);
		foreach ($excludeBillingItems as $keyE => $valueE) { 
			$balance = $valueE->biliingItemAmount - 0.00;
			$balanceTotal += $balance;
			$invoicesum += $valueE->biliingItemAmount;
			?>
			<tr>
				<td><?php echo $valueE->biliingItemDescription; ?></td>
				<td style="text-align:right;"><?php echo $valueE->biliingItemAmount; ?></td>
				<td><?php echo ''; ?></td>
				<td style="text-align:right;"><?php echo ''; ?></td>
				<td style="text-align:right;"><?php echo $valueE->biliingItemAmount; ?></td>

			</tr>
		<?php }
		?>
		<tr style="background: #ccc;font-weight: bold;">
				<td style="border: none;"></td>
				<td style="text-align:right;">{{number_format($invoicesum,2)}}</td>
				<td></td>
				<td style="text-align:right;">{{number_format($expensesum,2)}}</td>
				<td style="text-align:right;color:<?php echo ($balanceTotal) < 0 ? 'red' : ''; ?>">{{number_format($balanceTotal,2)}}</td>
			</tr>

	</table>
<?php }else
{ ?>
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
			$excludeCostItems = $getCostItemData;			
			foreach($getBillingItemData as $k => $v) { ?>
			
				<?php $getRelatedCostId = DB::table('costs')->where('cost_billing_code',$v->biliingItemId)->first(); 
				$costItemDescription = '';
				$costItemAmount = '';
				foreach($getCostItemData as $k1 => $v1)
				{
					if(!empty($getRelatedCostId) && $v1->costItemId == $getRelatedCostId->id)
					{ 
						$costItemDescription = $v1->costDescription;
						$costItemAmount = $v1->costAmount;
						unset($excludeCostItems[$k1]);
					}
				}
				?>
				<?php
				$invoicesum += $v->biliingItemAmount;
				$expensesum += $costItemAmount;
				$balance = $v->biliingItemAmount - $costItemAmount;
			 	$balanceTotal += $balance;
				?>
				<tr>
					<td><?php echo $v->biliingItemDescription; ?></td>
					<td style="text-align:right;"><?php echo $v->biliingItemAmount; ?></td>
					<td><?php echo $costItemDescription; ?></td>
					<td style="text-align:right;"><?php echo $costItemAmount; ?></td>
					<td style="text-align:right;color:<?php echo ($v->biliingItemAmount - $costItemAmount) < 0 ? 'red' : ''; ?>"><?php echo number_format($v->biliingItemAmount - $costItemAmount,2); ?></td>

				</tr>

		<?php } ?>
		<?php 
		//pre($excludeBillingItems);
		foreach ($excludeCostItems as $keyE => $valueE) { 
			$balance = 0.00 - $valueE->costAmount;
			$balanceTotal += $balance;
			$expensesum += $valueE->costAmount;
			?>
			<tr>
				<td><?php echo ''; ?></td>
				<td style="text-align:right;"><?php echo ''; ?></td>
				<td><?php echo $valueE->costDescription; ?></td>
				<td style="text-align:right;"><?php echo $valueE->costAmount; ?></td>
				<td style="text-align:right;color:red"><?php echo '-'.$valueE->costAmount; ?></td>

			</tr>
		<?php }
		?>
		<tr style="background: #ccc;font-weight: bold;">
				<td style="border: none;"></td>
				<td style="text-align:right;">{{number_format($invoicesum,2)}}</td>
				<td></td>
				<td style="text-align:right;">{{number_format($expensesum,2)}}</td>
				<td style="text-align:right;color:<?php echo ($balanceTotal) < 0 ? 'red' : ''; ?>">{{number_format($balanceTotal,2)}}</td>
			</tr>

	</table>
<?php }
exit;
?>
	