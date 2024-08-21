<input type="hidden" name="total" value=<?php echo $totalAcceptedRent;?> id = "total">
			<table id="filterLocalInfo" class="display nowrap" style="width:100%">
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
@foreach($localInvoicePaymentFilterDetail as $localInvoiceData)
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
<script type="text/javascript">

	$('#filterLocalInfo').DataTable(
    {
		'stateSave': true,
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            }],
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        },
        
    });

</script>