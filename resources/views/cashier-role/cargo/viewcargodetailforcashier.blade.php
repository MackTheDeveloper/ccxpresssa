@extends('layouts.custom')
@section('title')
Basic Detail
@stop

@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 50px;font-weight: 600;">
		<span>
		<?php echo ($model->cargo_operation_type == 1 ? 'Import' : ($model->cargo_operation_type == 2 ? 'Export' : 'Locale')).' ( '.$model->file_number.' ) '; ?>
	</span>
		
	<span style="float: right;">Warehouse Status : <?php echo !empty($model->warehouse_status) ? Config::get('app.warehouseStatus')[$model->warehouse_status] : 'Not Assigned'; ?></span>	
	</h1>
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
				<a class="btn round orange btn-warning" href="{{route('createcashierwarehouseinvoicesoffile',[$model->id])}}">Add Invoice</a>
				<?php if($model->warehouse_status == '1') { ?>
				<a class="btn round orange btn-warning" target="_blank" href="{{ route('releasereceiptbycashier',[$model->id,$model->cargo_operation_type]) }}">Release Receipt</a>
				<?php } ?>
			</div>
			<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">Master File Details</div>
			
			<div class="detail-container">
				<?php if($id == 1) { ?>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB / BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Opening Date : </span>
					<span class="viewblk2"><?php echo date('d-m-Y',strtotime($model->opening_date)); ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Expediteur / Shipper : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Address : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : $model->consignee_address; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Explications : </span>
					<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
				</div>
				
				
				<?php } elseif($id == 2){ ?>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB / BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Opening Date : </span>
					<span class="viewblk2"><?php echo date('d-m-Y',strtotime($model->opening_date)); ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Consignataire / Consignee : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Expediteur / Shipper : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper_name) ? App\Ups::getConsigneeName($model->shipper_name) : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Address : </span>
					<span class="viewblk2"><?php echo !empty($model->shipper_address) ? $model->shipper_address : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Explications : </span>
					<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
				</div>
				
				
				<?php } else { ?>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">No. Dossier/ File No. : </span>
					<span class="viewblk2"><?php echo !empty($model->file_number) ? $model->file_number : '-' ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">AWB / BL No. : </span>
					<span class="viewblk2"><?php echo !empty($model->awb_bl_no) ? $model->awb_bl_no : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Opening Date : </span>
					<span class="viewblk2"><?php echo date('d-m-Y',strtotime($model->opening_date)); ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Client : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_name) ? App\Ups::getConsigneeName($model->consignee_name) : '-' ?></span>
				</div>
				
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Address : </span>
					<span class="viewblk2"><?php echo !empty($model->consignee_address) ? $model->consignee_address : '-'; ?></span>
				</div>
				<div style="float: left;width: 50%; margin-bottom: 10px;">
					<span class="viewblk1">Explications : </span>
					<span class="viewblk2"><?php echo !empty($model->information) ? $model->information : '-'; ?></span>
				</div>
				
				
				<?php } ?>
			</div>

			<?php if(count($HouseAWBData) != 0) { ?>
			<div id="div_houseawbdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;">House AWB Details</div>
			<div class="detail-container">
			<table class="table simpletable" id="example1">
					<thead>
						<tr>
							<th>Sr No.</th>
			                <th>House AWB No.</th>
			                <th>Consignee</th>
			                <th>Shipper</th>
			                <th>Verification</th>
			                <th>Custom Inspection</th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 1; foreach($HouseAWBData as $k => $items)
						{ ?>
							<tr>
								<td><?php echo $i; ?></td>
			                    <td><?php echo $items->cargo_operation_type == '1' ? $items->hawb_hbl_no : $items->export_hawb_hbl_no;  ?></td>
			                    <td>{{$items->consignee_name}}</td>
			                    <td>{{$items->shipper_name}}</td>
			                    <td><?php echo Config::get('app.verifyFileWarehouse')[$items->verify_flag]; ?></td>
			                    <td><?php echo Config::get('app.inspectionFileWarehouse')[$items->inspection_flag]; ?></td>
							</tr>
						<?php $i++; } ?>
					</tbody>
				</table>
			</div>
			<?php } ?>


		</div>

		
		
		
		
		
	</div>

</section>

@endsection
@section('page_level_js')
<script type="text/javascript">
	$('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
		$(document).ready(function() {

			$('#createforms').on('submit', function (event) {
				event.preventDefault();

				$('#loading').show();
				var form = $("#createforms");
				var formData = form.serialize();
		        var urlz = '<?php echo url("cargo/assignwarehousestatusbywarehouseuser"); ?>';
		        $.ajax({
		        url:urlz,
		        async:false,
		        type:'POST',
		        data:formData,
		        success:function(data) {
		                $('#loading').hide();
		                $('.selectpicker').selectpicker('refresh');
		                Lobibox.notify('info', {
		                size: 'mini',
		                delay: 2000,
		                rounded: true,
		                delayIndicator: false,
		                msg: 'Status has been changed successfully.'
		                });
		            },
		        });
	    	});
			
			$('#warehouse_status').change(function(){
				var tVal = $(this).val();
				if(tVal == 1)
					$('.shipement-received-date-div').show();
				else
					$('.shipement-received-date-div').hide();
			})

			<?php if($model->warehouse_status == 1) { ?>
				$('.shipement-received-date-div').show();
			<?php } else { ?>
				$('.shipement-received-date-div').hide();
			<?php } ?>


			$('.customButtonInGrid').click(function(){
		        var status = $(this).val();
		        var hawbId = $(this).data('hawbid');
		        var flag = $(this).data('flag');
		        var thiz = $(this);
		        $.ajaxSetup({
		            headers: {
		                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		            }
		        });

		        Lobibox.confirm({
		            msg: "Are you sure to change status?",
		            callback: function (lobibox, type) {

		                         if(type == 'yes')
		                           {
		                                 $.ajax({
		                                    type    : 'post',
		                                    url     : '<?php echo url('cargo/verificationinspection'); ?>',
		                                    data    : {'status':status,'hawbId':hawbId,'flag':flag},
		                                    success : function (response) {
		                                            thiz.val(status);
		                                        }
		                                    });
		                                    if(status == 1)
		                                    {
		                                    thiz.val('0');    
		                                    thiz.text('Pending');
		                                    thiz.removeClass('customButtonSuccess');
		                                    thiz.addClass('customButtonAlert');
		                                    }
		                                    else
		                                    {
		                                    thiz.val('1');    
		                                    thiz.text('Done');    
		                                    thiz.removeClass('customButtonAlert');
		                                    thiz.addClass('customButtonSuccess');
		                                    }
		                                    Lobibox.notify('info', {
		                                        size: 'mini',
		                                        delay: 2000,
		                                        rounded: true,
		                                        delayIndicator: false,
		                                        msg: 'Status has been updated successfully.'
		                                    });
		                           }
		                          else
		                            {}    
		                }
		        })
		     })

		
	})
</script>
@stop
<style type="text/css">
	.navbar-static-top {position: fixed !important;width: 100%}
	.main-sidebar{ position: fixed !important; }
	.client-side .dropdown-toggle { height: 36px;  }
</style>