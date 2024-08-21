@extends('layouts.custom')
@section('title')
Local File Detail
@stop

@section('breadcrumbs')
@include('menus.cargo-files')
@stop

@section('content')
<section class="content-header">
	<h1>Local File Detail</h1>
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
	<div class="box box-success cargocontainer">
		<div class="box-body">

			<div class="" style="float: left;width: 100%;margin-bottom: 10px;">
				<div style="float:left;margin-right: 5px;">
					<a class="btn round orange btn-warning" href="{{route('editcargo',[$localCargoFileData->id,$localCargoFileData->cargo_operation_type])}}">Edit</a>
				</div>
				<div class="" style="float:left;">
					<button class="btn btn-primary" id="upload-file-btn" value="{{url('files/upload',['cargo',$localCargoFileData->id])}}"><i class="fa fa-upload" aria-hidden="true" style="margin-right: 5px"></i>Upload Files</button>
				</div>
			</div>

			<?php $checkRental = $localCargoFileData->rental; ?>

			<div class="row" style="display: none">
				<div class="col-md-3" style="float: right">
					<div class="row">
						<div class="col-md-6">
							<button class="btn btn-primary" id="upload-file-btn" value="{{url('files/upload',['cargo',$localCargoFileData->id])}}"><i class="fa fa-upload" aria-hidden="true" style="margin-right: 5px"></i>Upload Files</button>
						</div>
						<div class="col-md-6">
							<div class='dropdown' style="margin-bottom: 4%;float: right">
								<button class='btn  btn-md dropdown-toggle' type='button' data-toggle='dropdown' style="background-color: #B6A146;color: white;">Change Status
									<span class="caret"></span></button>
								<ul class='dropdown-menu' style='left:auto;'>
									<li>
										@if($localCargoFileData->rental_paid_status == 'up' || $localCargoFileData->rental_paid_status == '')
										<a href="{{ url('changeCargoLocal/paid/'.$localCargoFileData->id) }}">Mark as Paid</a>
										@else
										<a href="{{ url('changeCargoLocal/unpaid/'.$localCargoFileData->id) }}">Mark as Pending</a>
										@endif
									</li>

								</ul>
							</div>
						</div>

					</div>

				</div>
			</div>
			<div id="div_basicdetails" class="notes box-s">Basic Details</div>
			<div class="detail-container" style="margin-top: 0%">
				<div class="row" style="margin-bottom: 1%">
					<div class="col-md-3">
						<b>File Number :</b>
					</div>
					<div class="col-md-3">
						{{$localCargoFileData->file_number}}
					</div>
					<div class="col-md-3">
						<b>Opening Date :</b>
					</div>
					<div class="col-md-3">
						<?php echo $checkRental == '1' ? date('d-m-Y', strtotime($localCargoFileData->opening_date)) : '-' ?>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<b>AWB/BL No. :</b>
					</div>
					<div class="col-md-3">
						<?php echo !empty($localCargoFileData->awb_bl_no) ? $localCargoFileData->awb_bl_no : '-'; ?>
					</div>
					<div class="col-md-3">
						<b>Status :</b>
					</div>
					<div class="col-md-3">

						@if($checkRental == '1')
						<span style="color: <?php echo $localCargoFileData->rental_paid_status == 'p' ? 'green' : 'red'; ?>"><?php echo $localCargoFileData->rental_paid_status == 'p' ? 'Paid' : 'Pending'; ?></span>
						@else
						<span>-</span>
						@endif
					</div>
				</div>
			</div>

			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%">Agent and Client detail</div>
			<div class="detail-container" style="margin-top: 0%">
				<div class="row">
					<div class="col-md-3">
						<b>Agent Name :</b>
					</div>
					<div class="col-md-3">
						<?php $data = app('App\User')->getUserName($localCargoFileData->agent_id);
						echo !empty($data->name) ? $data->name : '-'; ?>
					</div>
					<div class="col-md-3">
						<b>Cosnsignee/Client :</b>
					</div>
					<div class="col-md-3">
						<?php echo !empty($localCargoFileData->consignee_name) ? App\Ups::getConsigneeName($localCargoFileData->consignee_name) : '-'; ?>
					</div>
				</div>
			</div>

			@if($localCargoFileData->rental == 1)

			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%">Rental Detail</div>
			<div class="detail-container" style="margin-top: 0%">
				<div class="row" style="margin-bottom: 1%">
					<div class="col-md-3">
						<b>Contract Beginning From :</b>
					</div>
					<div class="col-md-3">
						<?php echo date('d-m-Y', strtotime($localCargoFileData->opening_date)) ?>
					</div>
					<div class="col-md-3">
						<b>Contract Ending At :</b>
					</div>
					<div class="col-md-3">
						<?php echo date('d-m-Y', strtotime($localCargoFileData->rental_ending_date)) ?>
					</div>
				</div>
				<div class="row" style="margin-bottom: 1%">

					<div class="col-md-3">
						<b>Monthly Rent :</b>
					</div>
					<div class="col-md-3">
						{{number_format($localCargoFileData->rental_cost,2)}}
					</div>
					<div class="col-md-3"><b>Contract For :</b></div>
					<div class="col-md-3">{{ $localCargoFileData->contract_months }} Months</div>
					<div class="col-md-3" style="display: none">
						<b>Rental Status :</b>
					</div>
					<div class="col-md-3" style="display: none">
						<span style="color: <?php echo $localCargoFileData->rental_paid_status == 'p' ? 'green' : 'red'; ?>"><?php echo $localCargoFileData->rental_paid_status == 'p' ? 'Paid' : 'Pending'; ?>
						</span>
					</div>
				</div>
				<div style="display: none" class="row" style="margin-bottom: 1%">
					<div class="col-md-3"><b>Total Amount :</b></div>
					<div class="col-md-3">{{ number_format($getInvoiceData->total,2) }}</div>
					<div class="col-md-3"><b>Paid Amount :</b></div>
					<div class="col-md-3">{{ number_format($getInvoiceData->credits,2) }}</div>
				</div>
				<div style="display: none" class="row" style="margin-bottom: 1%">

					<div class="col-md-3"><b>Pending Amount :</b></div>
					<div class="col-md-3">{{ number_format($getInvoiceData->total - $getInvoiceData->credits,2) }}</div>
					<div class="col-md-3"><b>Contract For :</b></div>
					<div class="col-md-3">{{ $localCargoFileData->contract_months }} Months</div>
				</div>
			</div>
			@endif

			@if($localCargoFileData->rental == '1')
			<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%">Monthly Payment Details</div>
			<div class="detail-container" style="margin-top: 0%">
				<table id="example" class="display nowrap" style="width:100%;float: left;">
					<thead>
						<tr>
							<th>File Number</th>
							<th>Date</th>
							<th>Duration</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>

						@foreach($localFileData as $cargoLocal)

						<tr>
							<td>{{$cargoLocal->file_number}}</td>
							<td><?php echo date('d-m-Y', strtotime($cargoLocal->date)) ?></td>
							<td>{{$cargoLocal->duration}}</td>
							<td>{{number_format($cargoLocal->total,2)}}</td>
						</tr>
						@endforeach

					</tbody>
				</table>
			</div>
			@endif

			<div id="div_expenses" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;"><span style="margin-top: 3px;
    float: left">Expenses/Cost</span>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<span style="display:none;margin-top: 3px;float: left;margin-left: 15px;"><b>Total:</b></span><span style="display:none;margin-top: 3px;float: left">&nbsp;<?php $totalExpense = App\Expense::getTotalExpenseOfCargo($id);
																																																																																		echo !empty($totalExpense) ? $totalExpense : '0.00'; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>USD:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalExpenseOfUSD) ? number_format($totalExpenseOfUSD, 2) : '0.00'; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>HTG:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalExpenseOfHtg) ? number_format($totalExpenseOfHtg, 2) : '0.00'; ?></span>
				<?php if ($model->file_close != 1) { ?>
					<div class="box-span">
						<a style="width: auto;float: left;color: #fff;margin-right: 10px;padding-top: 3px;height: 30px;    background-color: #3f7b30;border-color: #3f7b30;" class="btn btn-primary" href="{{ route('createexpenseusingawl',['cargo',$id,'flagFromView']) }}">Add Expense</a>
						<div style="float: left">
							<a title="Click here to print" target="_blank" href="{{ route('getprintallexpense',[$model->id]) }}"><i style="background-color: #3f7b30;border-color: #3f7b30;" class="fa fa-print btn btn-primary"></i></a>
						</div>
					</div>
				<?php } ?>
			</div>

			<div class="detail-container">
				<table class="table simpletable display nowrap" id="example1" style="width:100%">
					<thead>
						<tr>
							<th style="display: none">ID</th>
							<th>
								<div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
							</th>
							<th>Date</th>
							<th>Voucher No.</th>
							<th>File No.</th>
							<th>AWB / BL No.</th>
							<th>Currency</th>
							<th>Total Amount</th>
							<th>Expediteur / Shipper</th>
							<th>Consignataire / Consignee</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($dataExpense)) {
							$i = 1;
							foreach ($dataExpense as $k => $items) {
								$dataClientUsingModuleId = App\Common::getClientDataUsingModuleId('cargo', $items->cargo_id);
								$items = (object) $items;
								$dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
								$cls = '';
								if ($dataExpenses > 0)
									$cls = 'fa fa-plus';

								$dataCargo = App\Cargo::getCargoData($items->cargo_id);
								if (empty($dataCargo))
									continue;

								if ($items->request_by_role == 12 || $items->request_by_role == 10)
									$edit =  route('editagentexpensesbyadmin', [$items->expense_id, 'flagFromView']);
								else
									$edit =  route('editexpensevoucher', [$items->expense_id, 'flagFromView']);
						?>
								<tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row">
									<td style="display: none">{{$items->expense_id}}</td>
									<td style="display: block;text-align: center;padding-top: 8px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
									<td>{{date('d-m-Y', strtotime($items->exp_date))}}</td>
									<td>{{$items->voucher_number}}</td>
									<td><?php $dataCargo = App\Cargo::getCargoData($items->cargo_id);
											echo $dataCargo->file_number;  ?></td>
									<td>{{$items->bl_awb}}</td>
									<td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
											echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
									<td style="text-align:right"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
									<td>{{$dataClientUsingModuleId['shipperName']}}</td>
									<td>{{$dataClientUsingModuleId['consigneeName']}}</td>
									<td>{{$items->expense_request}}</td>
									<td>
										<div class='dropdown'>
											<?php
											$delete =  route('deleteexpensevoucher', $items->expense_id);
											?>

											<a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;

											<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>


										</div>

									</td>

								</tr>
							<?php $i++;
							}
						} else { ?>
							<tr>
								<td>No data found.</td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>

			<div id="div_invoice" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;"><span style="margin-top: 3px;
    float: left">Invoices</span>
				&nbsp;&nbsp;&nbsp;&nbsp;<span style="display:none;margin-top: 3px;float: left;margin-left: 15px;"><b>Total:</b></span><span style="display:none;margin-top: 3px;float: left">&nbsp;<?php $totalRevenue = App\Expense::getTotalRevenueOfCargo($id);
																																																																																														echo $totalRevenue; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>USD:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalInvoiceOfUSD) ? number_format($totalInvoiceOfUSD, 2) : '0.00'; ?></span>
				<span style="margin-top: 3px;float: left;margin-left: 35px;"><b>HTG:</b></span><span style="margin-top: 3px;float: left">&nbsp;<?php echo !empty($totalInvoiceOfHTG) ? number_format($totalInvoiceOfHTG, 2) : '0.00'; ?></span>
				<?php if ($model->file_close != 1) { ?>
					<div class="box-span">
						<a class="btn btn-primary" style="width: auto;float: left;color: #fff;margin-right: 10px;padding-top: 3px;height: 30px;background-color: #3f7b30;border-color: #3f7b30;" href="{{ route('createinvoice',[$id,'0','flagFromView']) }}">Add Invoice</a>
					</div>
				<?php } ?>
			</div>
			<div class="detail-container">
				<table class="table simpletable display nowrap" id="example2" style="width:100%">
					<thead>
						<tr>
							<th style="display: none">ID</th>
							<th>Date</th>
							<th>Invoice No.</th>
							<th>File No.</th>
							<th>AWB / BL No.</th>
							<th>Billing Party</th>
							<th>Type</th>
							<th>Currency</th>
							<th>Total Amount</th>
							<th>Paid Amount</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($invoices as $items)
						<?php $edit =  route('editinvoice', [$items->id, 'flagFromView']); ?>
						<tr data-editlink="{{ $edit }}" id="<?php echo $items->id; ?>" class="edit-row">
							<td style="display: none">{{$items->id}}</td>
							<td>{{date('d-m-Y', strtotime($items->date))}}</td>
							<td>{{$items->bill_no}}</td>
							<td>{{$items->file_no}}</td>
							<td>{{$items->awb_no}}</td>
							<td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to);
									echo !empty($dataUser->company_name) ? $dataUser->company_name : "-"; ?></td>
							<td>{{$items->type_flag}}</td>
							<td><?php $dataCurrency = app('App\Currency')::getData($items->currency);
									echo !empty($dataCurrency->code) ? $dataCurrency->code : "-" ?></td>
							<td class="alignright">{{number_format($items->total,2)}}</td>
							<td class="alignright">{{number_format($items->credits,2)}}</td>
							<td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
							<td>
								<div class='dropdown'>
									<?php
									$delete =  url('invoices/delete', $items->id);
									$edit =  route('editinvoice', [$items->id, 'flagFromView']);
									?>

									<a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;

									<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>


								</div>

							</td>
						</tr>
						@endforeach

					</tbody>
				</table>
			</div>

			<div id="div_activities" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Related Files</div>
			<div class="detail-container">
				<table id="filesTable" class="simpletable display nowrap" style="width:100%;float: left;">
					<thead>
						<th style="display: none;">Id</th>
						<th></th>
						<th>File Type</th>
						<th>File Name</th>
						<th>Actions</th>
					</thead>
					<tbody>
						<?php $i = 1; ?>
						@if(count($filesInfo)>0)

						@foreach($filesInfo as $files)
						<tr>

							<td style="display: none;">{{$i}}</td>
							<td></td>
							<td><?php echo !empty($files->file_type) ? $fileTypes[$files->file_type] : '-' ?></td>
							<td>{{$files->file_name}}</td>
							<td>
								<div class='dropdown'>
									<?php
									/* $delete =  route('deletefiles', ['cargo', $localCargoFileData->id, $files[1]]);
									$download =  route('downloadfiles', ['cargo', $localCargoFileData->id, $files[1]]); */
									$delete =  route('deletefiles', ['cargo', $localCargoFileData->id, serialize($files->file_name)]);
									$download =  route('downloadfiles', ['cargo', $localCargoFileData->id, serialize($files->file_name)]);
									?>
									<a href="javascript:void(0)" title="Download" data-value={{$download}} id="download"><i class="fa fa-download" aria-hidden="true"></i></a>&nbsp; &nbsp;
									<a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
								</div>
							</td>
						</tr>
						<?php $i++; ?>
						@endforeach
						@endif
					</tbody>
				</table>
			</div>

			<div id="div_activities" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Activities</div>
			<div class="detail-container " style="height: auto;max-height: 400px;overflow: auto;    width: 100%;">
				<div style="float: left;width: 100%;margin-bottom: 10px;font-weight: bold;text-align: center">
					<div class="labeldata20">Performed By</div>
					<div class="resultdata60">Activities</div>
					<div class="resultdata20">Date/Time</div>
				</div>
				<?php if (!empty($activityData)) { ?>
					<div>
						@foreach ($activityData as $activityData)
						<div class="labeldata20"><?php $userData = app('App\User')->getUserName($activityData->user_id);
																			echo empty($userData) ? 'Automatic' : $userData->name; ?></div>
						<div class="resultdata60"><?php echo $activityData->description; ?></div>
						<div class="resultdata20"><?php echo date('d-m-Y h:i:s', strtotime($activityData->updated_on)); ?></div>
						@endforeach
					</div>
				<?php } else { ?>
					<h4 style="float: left;width: 100%;font-size: 15px;">No Activity Found.</h4>
				<?php } ?>
			</div>
		</div>
		<iframe src="" id="frame" style="display: none;"></iframe>
</section>

@endsection
@section('page_level_js')
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			"columnDefs": [{
				type: 'date-uk',
				targets: [2]
			}],
		});
		$('#example2').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			"columnDefs": [{
				type: 'date-uk',
				targets: [1]
			}],
		});
		var table = $('#example').DataTable({
			'stateSave': true,
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			"columnDefs": [{
				"targets": [1, -1],
				"orderable": false
			}],
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

		$(document).delegate(".changeStatus", "click", function() {
			$('#loading').fadeIn();
			var Id = $(this).data("value");
			var status = $(this).attr('id');
			$.ajax({
				type: "GET",
				url: "{{url('local/changeStatus')}}",
				data: {
					Id: Id,
					status: status
				},
				success: function(res) {
					//console.log(res);
					location.reload();
				}
			});
		});

		$('#filesTable').DataTable({
			'stateSave': true,
			"order": [
				[1, "desc"]
			],
		});

		$(document).delegate('#download', 'click', function() {
			$('#loading').show();
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var urlz = $(this).data('value');
			$.ajax({
				url: urlz,
				type: 'GET',
				data: {},
				success: function(data) {
					$('#frame').attr('src', urlz);
					//window.open(urlz,'_blank' );
					$('#loading').hide();
					//alert(data);
				}
			});
		});
	});
</script>
@stop