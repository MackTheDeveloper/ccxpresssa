		<input type="hidden" name="heading" id="_heading" value=<?php echo $heading; ?>>
		<table id="example" class="display" style="width:100%">
			<thead>
				<tr>
					<th>Date</th>
					<th>File Number</th>
					<th>Awb No.</th>
					<th>Billing Party</th>
					<th>Total Amount ($)</th>
					<th>Credits ($)</th>
					<th>Status</th>

				</tr>
			</thead>
			<tbody>

				@foreach($data as $data)
				<tr>
					<td>{{ date('d-m-Y', strtotime($data->date)) }}</td>
					<td>{{ $data->file_no }}</td>
					<td>{{ $data->awb_no }}</td>
					<td><?php $dataUser = app('App\Clients')->getClientData($data->bill_to);
							echo !empty($dataUser->company_name) ? strtoupper($dataUser->company_name) : "-"; ?></td>
					<td>{{ $data->total }}</td>
					<td>{{ $data->credits }}</td>
					<td style="<?php echo ($data->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>"><?php echo !empty($data->payment_status) ? $data->payment_status : '-'; ?></td>
				</tr>
				@endforeach

			</tbody>
		</table>