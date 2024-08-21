@extends('layouts.custom')
@section('title')
Files Listing
@stop

<?php 
$permissionCourierImportEdit = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
$permissionCourierImportDelete = App\User::checkPermission(['delete_courier_import'],'',auth()->user()->id); 
$permissionCourierAddExpense = App\User::checkPermission(['add_courier_expenses'],'',auth()->user()->id); 
$permissionCourierAddInvoice = App\User::checkPermission(['add_courier_invoices'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
@include('menus.ups-import')
@stop

@section('content')
<section class="content-header">
    <h1>Export Files Listing</h1>
</section>

<section class="content">
	<div class="box box-success">
        <div class="box-body">
        	 <div class="container-rep">
        	 	<table id="example" class="display nowrap" style="width:100%">
        	 		<thead>
        	 			<th>AWB Number</th>
        	 			<th>Shipper Ac No</th>
        	 			<th>Shipper Name</th>
        	 			<th>Consignee Name</th>
        	 			<th>Destination Country</th>
        	 			<th>Declared Value</th>
        	 			<th>HS CODE</th>
        	 			<th>Freight</th>
        	 			<th>Billing Type</th>
        	 			<th>Action</th>
        	 		</thead>
        	 		<tbody>
        	 			@foreach($upsExportData as $upsExportData)
        	 			<tr>
	        	 			<td>{{$upsExportData->awb_number}}</td>
	        	 			<td>{{$upsExportData->shipper_account_no}}</td>
	        	 			<td>{{$upsExportData->shipper_name}}</td>
	        	 			<td><?php echo empty($upsExportData->consignee_name) ? '-' : $upsExportData->consignee_name ;?></td>
	        	 			<td><?php echo empty($upsExportData->destination_country) ? '-' : $upsExportData->destination_country ;?></td>
	        	 			<td><?php echo empty($upsExportData->declared_value) ? '0' : $upsExportData->declared_value ;?>
	        	 			<?php echo empty($upsExportData->currency) ? '' : ' '.$upsExportData->currency;?></td>
	        	 			<td><?php echo empty($upsExportData->HS_CODE) ? '-' : $upsExportData->HS_CODE ;?></td>
	        	 			<td><?php echo empty($upsExportData->freight) ? '-' : $upsExportData->freight ;?>
	        	 			

	        	 				<?php echo empty($upsExportData->freight_currency) ? '' : ' '.$upsExportData->freight_currency ;?></td>
	        	 			<td><?php if($upsExportData->fc == 1)
	        	 						{ 
	        	 							echo 'F/C';
	        	 						} 
	        	 					  if($upsExportData->fd == 1)
	        	 					  	{
	        	 					  		echo 'F/D';
	        	 					  	}
	        	 					  if($upsExportData->pp == 1){
	        	 					  		echo 'P/P';
	        	 					    }
	        	 				?>
	        	 			</td>
	        	 			<td>
                                <div class='dropdown'>
	        	 				 <?php 
                                	$delete =  route('deleteupsexport',[$upsExportData->id,'export']);
                                	$edit =  route('editupsexport',[$upsExportData->id,'export']);

                                 ?>
                                 <a href="<?php echo $edit ?>" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
	        	 				 
	        	 				 <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>

	        	 				 <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                        <ul class='dropdown-menu' style='left:auto;'>
                                            <?php if($permissionCourierAddExpense) { ?>
                                                <li>
                                                	<a href="{{ route('createupsexpense',$upsExportData->id) }}">Add File Expense</a>
                                               </li>
                                               <?php } ?>
                                               <?php if($permissionCourierAddInvoice) { ?>
                                                <li>
                                                    <a href="{{ route('createupsinvoice',$upsExportData->id) }}">Add Invoice</a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                </div>
	        	 			</td>
	        	 		</tr>
        	 			@endforeach
        	 		</tbody>
        	 	</table>
        	 </div>
        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
 var table = $('#example').DataTable({
		'stateSave': true,
        "columnDefs": [{
            "targets": [3],
            "orderable": false
        }],
        "scrollX": true,
        "order": [[ 0, "desc" ]],
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
                $('.expandpackage').each(function(){
                    if($(this).hasClass('fa-minus'))
                    {
                        $(this).removeClass('fa-minus');    
                        $(this).addClass('fa-plus');
                    }
                })
        });
        $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
        });
      },
     
  });
 </script>
 @stop