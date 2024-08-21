@extends('layouts.custom')
@section('title')
Local File Listing
@stop

@section('breadcrumbs')
@include('menus.cargo-files')
@stop

@section('content')
<section class="content-header">
	<h1>Local(rental) File Listing</h1>
</section>

<section class="content">
	@if(Session::has('flash_message'))
	<div class="alert alert-success flash-success">
		{{ Session::get('flash_message') }}
	</div>
	@endif
	@if(Session::has('flash_message_error'))
	<div class="alert alert-danger flash-danger">
		{{ Session::get('flash_message_error') }}
	</div>
	@endif
	<!-- <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div> -->
	<div class="box box-success cargocontainer" style="margin-top: 1%">
		<div class="box-body">
			<table id="example" class="display nowrap" style="width:100%;float: left;">
				<thead>
					<tr>
						<th>File No.</th>
						<th>Agent</th>
						<th>AWB/BL No.</th>
						<th>Cosnsignee/Client</th>
						<th>Opening Date</th>
						<th>Contract Ending Date</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach($localFileData as $cargoLocal)
					<tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$cargoLocal->id) }}" id="<?php echo $cargoLocal->id; ?>" class="edit-row">
						<td>{{$cargoLocal->file_number}}</td>
						<td><?php $data = app('App\User')->getUserName($cargoLocal->agent_id);
								echo !empty($data->name) ? $data->name : '-'; ?></td>
						<td><?php echo !empty($cargoLocal->awb_bl_no) ? $cargoLocal->awb_bl_no : '-'; ?></td>
						<td><?php echo !empty($cargoLocal->consignee_name) ? $cargoLocal->consignee_name : '-'; ?></td>
						<td><?php echo date('d-m-Y', strtotime($cargoLocal->opening_date)) ?></td>
						<td><?php echo date('d-m-Y', strtotime($cargoLocal->rental_ending_date)) ?></td>
						<td style="color: <?php echo $cargoLocal->rental_paid_status == 'p' ? 'green' : 'red'; ?>"><?php echo $cargoLocal->rental_paid_status == 'p' ? 'Paid' : 'Pending'; ?></td>
						<td>
							<div class='dropdown'>
								<button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
								<ul class='dropdown-menu' style='left:auto;'>
									<li>
										@if($cargoLocal->rental_paid_status == 'up' || $cargoLocal->rental_paid_status == '')
										<a href="{{ url('changeCargoLocal/paid/'.$cargoLocal->id) }}">Mark as Paid</a>
										@else
										<a href="{{ url('changeCargoLocal/unpaid/'.$cargoLocal->id) }}">Mark as Pending</a>
										@endif
									</li>

								</ul>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
	$(document).ready(function() {
		var table = $('#example').DataTable({
			'stateSave': true,
			stateSaveParams: function(settings, data) {
				delete data.order;
			},
			/* "order": [
				[0, "desc"]
			], */
			"scrollX": true,
			"columnDefs": [{
				"targets": [1, -1],
				"orderable": false
			}],
			"aaSorting": [],
			drawCallback: function() {
				$('.fg-button,.sorting,#example_length', this.api().table().container())
					.on('click', function() {
						$('#loading').show();
						setTimeout(function() {
							$("#loading").hide();
						}, 200);
						$('.expandpackage').each(function() {
							if ($(this).hasClass('fa-minus')) {
								$(this).removeClass('fa-minus');
								$(this).addClass('fa-plus');
							}
						})
					});
				$('#example_filter input').bind('keyup', function(e) {
					$('#loading').show();
					setTimeout(function() {
						$("#loading").hide();
					}, 200);
				});
			}
		});
	});
</script>
@stop