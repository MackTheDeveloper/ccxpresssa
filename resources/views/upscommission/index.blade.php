@extends('layouts.custom')

@section('title')
UPS Commission
@stop


@section('breadcrumbs')
    @include('menus.ups-commission')
@stop


@section('content')
	<section class="content-header">
    	<h1>UPS Commission</h1>
	</section>

	<section class="content">
		@if(Session::has('flash_message'))
    		<div class="alert alert-success flash-success">
        		{{ Session::get('flash_message') }}
    		</div>
    	@endif
    	<div class="box box-success">
        	<div class="box-body">
        		<table id="example" class="display nowrap" style="width:100%">
                	<thead>
                    	<tr>
	                        <th>File Type</th>
	                        <th>Billing Term</th>
	                        <th>Courier Type</th>
	                        <th>Commission</th>
	                        <th>Action</th>
                    	</tr>
                	</thead>
                	<tbody>
                		@foreach($upsCommissionData as $data)
                		<tr data-editlink="{{ route('editupscommission',[$data->id]) }}" id="<?php echo $data->id; ?>" class="edit-row">
                			<td>
                				<?php 
                					if($data->file_type == 'e'){
                						echo 'Export';
                					} else {
                						echo 'Import';
                					}
                				?>
                			</td>
                			<td>
                				<?php 
                					if($data->billing_term == 1){
                						echo 'F/C';
                					} else if($data->billing_term == 2){
                						echo 'F/D';
                					} else {
                						echo 'P/P';
                					}
                				?>
                			</td>
                			<td>
                				<?php 
                					if($data->courier_type == 'LTR'){
                						echo 'Letter';
                					} else if($data->courier_type == 'DOC'){
                						echo 'Document';
                					} else {
                						echo 'Package';
                					}
                				?>
                			</td>
                			<td><?php echo number_format($data->commission,2);?></td>
                			<?php 
                        		$delete =  route('deleteupscommission',$data->id);
                        		$edit =  route('editupscommission',$data->id);
                        	?>
                			<td>
                				 <div class='dropdown'>
                					<a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                					<a href="<?php echo $delete ?>" title="Delete" style = "margin-left : 10%" class = "delete-record"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
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
@endsection