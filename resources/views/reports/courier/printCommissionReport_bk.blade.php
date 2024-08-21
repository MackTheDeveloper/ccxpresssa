<!DOCTYPE html>
<html>
<head>
	<title>UPS Commission Report</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<style type="text/css">
		th,td{
			 padding: 8px;
			 text-align: center;
		}
		body{
			background-color: #fff;
		}
	</style>
</head>
<body>
	<h3 style="background: #ccc;padding:5px;font-weight:normal;font-size: 18px;margin-bottom: 25px">UPS Commission Report</h3>

	<table style="width:100%" class="table table-bordered">
		<thead>
			<tr>
				<th>File Number</th>
				<th>Date</th>
				<th>Awb Number</th>
				<th>Billing Type</th>
				<th>Nature Of Shipment</th>
				<th>Multiple Package</th>
				<th>Freight Rev</th>
				<th>Currency</th>
				<th>Commission</th>
				<th>Received</th>
				<th>Weight</th>
			</tr>
		</thead>
		<tbody>
			@foreach($upsData as $data)
				<tr>
					<td>{{$data->file_number}}</td>
					<td><?php echo $data->courier_operation_type == 1 ? (!empty($data->arrival_date) ? date('d-m-Y',strtotime($data->arrival_date)) : '-') : (!empty($data->tdate) ? date('d-m-Y',strtotime($data->tdate)) : '-') ?></td>
					<td><?php echo !empty($data->awb_number) ? $data->awb_number : '-';?></td>
					<td><?php echo App\Ups::getBillingTerm($data->id); ?></td>
					<td><?php echo $data->package_type;?></td>
					<td><?php echo $data->nbr_pcs > 1 ? 'Y': 'N';?></td>
					<td><?php echo !empty($data->freight) ? $data->freight : '0.00';?></td>
					<td><?php echo !empty($data->freight_currency) ? $data->freight_currency : '-' ;?></td>
					<td><?php echo App\Ups::getCommission($data->id); ?></td>
					<td><?php $dataCommission = App\Ups::getCommissionData($data->id);
						if(empty($dataCommission))
							echo '0.00';
						else
								echo number_format((!empty($dataCommission->commission) ? $dataCommission->commission : '0.00')-(is_null($dataCommission->pending_commission) ? $dataCommission->commission : (!empty($dataCommission->pending_commission) ? $dataCommission->pending_commission : '0.00')),2) ;
						 //echo number_format(($dataCommission->commission-$dataCommission->pending_commission),2); ?></td>
					<td>{{$data->weight}}{{' '.$data->unit}}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>

					

					