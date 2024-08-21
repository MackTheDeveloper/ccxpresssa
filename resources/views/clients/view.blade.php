@extends('layouts.custom')
@section('title')
Basic Detail
@stop
@section('sidebar')
<aside class="main-sidebar">
	<ul class="sidemenu nav navbar-nav side-nav">
		<?php
		$checkPermissionAddCargo = App\User::checkPermission(['add_cargo'], '', auth()->user()->id);
		$checkPermissionUpdateCargo = App\User::checkPermission(['update_cargo'], '', auth()->user()->id);
		$checkPermissionDeleteCargo = App\User::checkPermission(['delete_cargo'], '', auth()->user()->id);
		$checkPermissionIndexCargo = App\User::checkPermission(['listing_cargo'], '', auth()->user()->id);
		$checkPermissionExpenseCargo = App\User::checkPermission(['add_expense_cargo'], '', auth()->user()->id);
		?>

		<li class="widemenu">
			<a href="{{ route('clients') }}">Client Listing</a>
		</li>
		<li class="widemenu">
			<a href="#div_basicdetails">Basic Details</a>
		</li>
		<li class="widemenu">
			<a href="#div_contacts">Contacts</a>
		</li>
		<li class="widemenu">
			<a href="#div_branches">Branches</a>
		</li>
		<li class="widemenu">
			<a href="#div_addresses">Addresses</a>
		</li>


	</ul>
</aside>
@stop

@section('breadcrumbs')
@include('menus.client-management')
@stop

@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 0px;font-weight: 600;">View <?php echo $flag == 'B' ? 'Billing Party' : 'Client' ?> Detail</h1>
</section>
<section class="content editupscontainer">
	<div class="box box-success">
		<div class="box-body">

			@if(Session::has('flash_message_error'))
			<div class="alert alert-danger flash-danger">
				{{ Session::get('flash_message_error') }}
			</div>
			@endif

			<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
			<div class="edit-btn">
				<a class="btn round orange btn-warning" href="{{route('editclient',[$model->id,$flag])}}">Edit</a>
			</div>
			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Basic Details</div>
			<div class="detail-container">

				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Client Type : </span>
					<span class="viewblk2"><?php $dataCategory = App\CashCreditDetailType::getData($model->category);
																	echo !empty($dataCategory->name) ? $dataCategory->name : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1"><?php echo $flag == 'B' ? 'Billing Party' : 'Client' ?> : </span>
					<span class="viewblk2"><?php echo !empty($model->company_name) ? $model->company_name : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Payment Term : </span>
					<span class="viewblk2"><?php $dataPaymentTerms = App\PaymentTerms::getData($model->payment_term);
																	echo !empty($dataPaymentTerms->title) ? $dataPaymentTerms->title : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Tax Number : </span>
					<span class="viewblk2"><?php echo !empty($model->tax_number) ? $model->tax_number : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Telephone : </span>
					<span class="viewblk2"><?php echo !empty($model->phone_number) ? $model->phone_number : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Website : </span>
					<span class="viewblk2"><?php echo !empty($model->website) ? $model->website : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Address : </span>
					<span class="viewblk2"><?php echo !empty($model->company_address) ? $model->company_address : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Country : </span>
					<span class="viewblk2"><?php $dataCountry = App\Country::getData($model->country);
																	echo !empty($dataCountry->name) ? $dataCountry->name : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">TCA Applicable : </span>
					<span class="viewblk2"><?php echo $model->flag_prod_tax_type == 1 ? 'Yes' . ' (' . $model->flag_prod_tax_amount . '%)' : 'No'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Status : </span>
					<span class="viewblk2"><?php echo $model->status == 1 ? 'Active' : 'Inactive'; ?></span>
				</div>
			</div>



			<div id="div_contacts" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;"><span style="margin-top: 3px;
    float: left">Contacts</span>
				<div class="box-span">
					<a style="width: auto;float: left;color: #fff;margin-right: 10px;padding-top: 3px;height: 30px;    background-color: #3f7b30;border-color: #3f7b30;" class="btn btn-primary" href="{{ route('createclientcontact',[$model->id,'clientdetail']) }}">Add Contact</a>
				</div>
			</div>
			<div class="detail-container">
				<table class="table simpletable nowrap" id="example" style="width:100%">
					<thead>
						<tr>
							<th style="display: none">ID</th>
							<th>Name</th>
							<th>Position</th>
							<th>Cell Number</th>
							<th>Direct line</th>
							<th>Work</th>
							<th>Email</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!empty($clientContact)) {
							$i = 1;
							foreach ($clientContact as $k => $items) {
						?>
								<tr>
									<td style="display: none">{{$items->id}}</td>
									<td>{{!empty($items->name) ? $items->name : '-'}}</td>
									<td>{{!empty($items->personal_contact) ? $items->personal_contact : '-'}}</td>
									<td>{{!empty($items->cell_number) ? $items->cell_number : '-'}}</td>
									<td>{{!empty($items->direct_line) ? $items->direct_line : '-'}}</td>
									<td>{{!empty($items->work) ? $items->work : '-'}}</td>
									<td>{{!empty($items->email) ? $items->email : '-'}}</td>
									<td>
										<div class='dropdown'>
											<?php
											$delete =  route('deleteclientcontact', $items->id);
											$edit =  route('editclientcontact', [$items->id, 'clientdetail']);
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
																			echo $userData->name; ?></div>
						<div class="resultdata60"><?php echo $activityData->description; ?></div>
						<div class="resultdata20"><?php echo date('d-m-Y h:i:s', strtotime($activityData->updated_on)); ?></div>
						@endforeach
					</div>
				<?php } else { ?>
					<h4 style="float: left;width: 100%;font-size: 15px;">No Activity Found.</h4>
				<?php } ?>

			</div>







		</div>
	</div>
</section>

<div id="modalCreateExpense" class="createexpenseinview modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h3 class="modal-title text-center primecolor">Add Expense</h3>
			</div>
			<div class="modal-body" id="modalContentCreateExpense" style="overflow: hidden;">
			</div>
		</div>
	</div>
</div>
@endsection
@section('page_level_js')
<script type="text/javascript">
	$(document).ready(function() {
		$('#example').DataTable({
			"order": [
				[0, "desc"]
			],
			"scrollX": true,
			drawCallback: function() {
				$('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
					$('#loading').show();
					setTimeout(function() {
						$("#loading").hide();
					}, 200);
				});
				$('#example_filter input').bind('keyup', function(e) {
					$('#loading').show();
					setTimeout(function() {
						$("#loading").hide();
					}, 200);
				});
			},

		});
	});
</script>
@stop
<style type="text/css">
	.main-sidebar {
		position: fixed !important;
	}
</style>