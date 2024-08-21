<!DOCTYPE html>
<html>

<head>
	<title>UPS Commission Report</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<style type="text/css">
		th,
		td {
			padding: 8px;
			text-align: center;
		}

		body {
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
				<th>Billing Term</th>
				<th>Freight Rev</th>
				<th>Commission</th>
			</tr>
		</thead>
		<tbody>
			@foreach($upsData as $data)
			<tr>
				<td>{{$data->fileNumber}}</td>
				<td><?php echo !empty($data->date) ? date('d-m-Y', strtotime($data->date)) : '-'; ?></td>
				<td><?php echo !empty($data->trackingNumber) ? $data->trackingNumber : '-'; ?></td>
				<td><?php echo App\Ups::getBillingTerm($data->id); ?></td>
				<td><?php echo !empty($data->freight) ? $data->freight : '0.00'; ?></td>
				<td><?php echo number_format(App\Ups::getCommission($data->id),2); ?></td>
			</tr>
			@endforeach
		</tbody>
	</table>
</body>

</html>