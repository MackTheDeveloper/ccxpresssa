<table id="example" class="display nowrap" style="width:100%">
						<thead>
							<tr>
								<th>File Number</th>
								<th>Date</th>
								<th>Awb Number</th>
								<th>Billing Type</th>
								<th>Nature Of Shipment</th>
								<th>Multiple Package</th>
								<th>Freight Rev</th>
								<th>Commission</th>
								<th>Received</th>
								<th>Weight</th>
							</tr>
						</thead>
						<tbody>
							@foreach($filteredData as $data)
								<tr>
									<td>{{$data->file_number}}</td>
									<td><?php echo $data->courier_operation_type == 1 ? (!empty($data->arrival_date) ? date('d-m-Y',strtotime($data->arrival_date)) : '-') : (!empty($data->tdate) ? date('d-m-Y',strtotime($data->tdate)) : '-') ?></td>
									<td><?php echo !empty($data->awb_number) ? $data->awb_number : '-';?></td>
									<td><?php echo App\Ups::getBillingTerm($data->id); ?></td>
									<td><?php echo App\Ups::getNatureOfShipment($data->id);?></td>
									<td><?php echo $data->nbr_pcs > 1 ? 'Y': 'N';?></td>
									<td><?php echo !empty($data->freight) ? number_format($data->freight,2) : '0.00';?></td>
									<td><?php echo number_format(App\Ups::getCommission($data->id),2); ?></td>
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

					<script type="text/javascript">
					jQuery.extend( jQuery.fn.dataTableExt.oSort, {
						"date-uk-pre": function ( a ) {
								if (a == null || a == "") {
									return 0;
								}
								var ukDatea = a.split('-');
								return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
							},
						
							"date-uk-asc": function ( a, b ) {
								return ((a < b) ? -1 : ((a > b) ? 1 : 0));
							},
						
							"date-uk-desc": function ( a, b ) {
								return ((a < b) ? 1 : ((a > b) ? -1 : 0));
							}
						});
						var table = $('#example').DataTable({
							'stateSave': true,
       						"columnDefs": [ {
            				"targets": [3],
            				"orderable": false
            			},{ type: 'date-uk', targets: 1 }],
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