<!DOCTYPE html>
<html>
<head>
	<title>Aeropost Commission Report</title>
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
	<h3 style="background: #ccc;padding:5px;font-weight:normal;font-size: 18px;margin-bottom: 25px">Aeropost Commission Report</h3>

	<table style="width:100%" class="table table-bordered">
		<thead>
			<tr>
				<th>Awb Number</th>
				<th>Date</th>
				<th>From</th>
				<th>Consignee</th>
				<th>Freight Rev</th>
				<th>Commission</th>
			</tr>
		</thead>
		<tbody>
			@foreach($aeroPostData as $data)
				<tr>
					<td><?php echo $data->tracking_no; ?></td>
					<td><?php echo !empty($data->date) ? date('d-m-Y',strtotime($data->date)) : '-'; ?></td>
					<td><?php echo $data->from_location; ?></td>
					<td><?php $dataClient = app('App\Clients')->getClientData($data->consignee); echo !empty($dataClient->company_name) ? $dataClient->company_name : '-'; ?></td>
					<td><?php echo $data->freight; ?></td>
					<td><?php echo app('App\AeropostFreightCommission')->getCommission($data->id); ?></td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>

					

					