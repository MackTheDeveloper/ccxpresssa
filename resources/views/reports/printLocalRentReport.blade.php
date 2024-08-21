<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css"> -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<style type="text/css">
		th,td{
			 padding: 8px;
			 text-align: center;
		}
	</style>
</head>
<body>
	<?php use Illuminate\Support\Facades\DB;
		  
	?>
	<section class="content" style="font-family: sans-serif;">

    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">
            <h3 style="background: #ccc;padding:5px;font-weight:normal;">Received Rent Report </h3>
        </div>
        <table class="table table-bordered" style="width:100%">
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
								<?php $client = DB::table('clients')->where('id',$localInvoiceData->billing_party)->get();
									foreach($client as $client)
										echo $client->company_name;
								?>
							</td>
							<td>{{$localInvoiceData->duration}}</td>
							<td>{{$localInvoiceData->total}}</td>
							<td>HTG</td>
							<td><?php echo date('d-m-Y h:i:s',strtotime($localInvoiceData->updated_at));?></td>
						</tr>
					@endforeach
				</tbody>
			</table>
    </div>
</section>
</body>
</html>