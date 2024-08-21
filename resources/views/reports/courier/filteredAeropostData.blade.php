<table id="example" class="display nowrap" style="width:100%">
						<thead>
							<tr>
								<th>File Number</th>
								<th>Date</th>
								<th>Awb Number</th>
								<th>From</th>
								<th>Consignee</th>
								<th>Freight Rev</th>
								<th>Commission</th>
							</tr>
						</thead>
						<tbody>
							@foreach($filteredData as $data)
								<tr>
									<td><?php echo $data->file_number; ?></td>
									<td><?php echo !empty($data->date) ? date('d-m-Y',strtotime($data->date)) : '-'; ?></td>
									<td><?php echo $data->tracking_no; ?></td>
									<td><?php echo $data->from_location; ?></td>
									<td><?php $dataClient = app('App\Clients')->getClientData($data->consignee); echo !empty($dataClient->company_name) ? $dataClient->company_name : '-'; ?></td>
									<td><?php echo $data->freight; ?></td>
									<td><?php echo app('App\AeropostFreightCommission')->getCommission($data->id); ?></td>
								</tr>
							@endforeach
						</tbody>
					</table>

					<script type="text/javascript">
						var table = $('#example').DataTable({
							'stateSave': true,
       						"columnDefs": [ {
            				"targets": [3],
            				"orderable": false
            			}],
        				"scrollX": true,
         				"order": [[ 0, "desc" ]],
         				drawCallback: function(){
			              $('#example_length', this.api().table().container())          
			                 .on('click', function(){
			                    $('#loading').show();
			                    	setTimeout(function() { $("#loading").hide(); }, 200);
			                    // $('.expandpackage').each(function(){
			                    //     if($(this).hasClass('fa-minus'))
			                    //     {
			                    //     $(this).removeClass('fa-minus');    
			                    //     $(this).addClass('fa-plus');
			                    //     }
			                    // })
			                	});
                 				$('#example_filter input').bind('keyup', function(e) {
                    				$('#loading').show();
                    				setTimeout(function() { $("#loading").hide(); }, 200);
                				});
           					}
    					});

					</script>