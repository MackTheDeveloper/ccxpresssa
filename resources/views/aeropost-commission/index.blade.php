@extends('layouts.custom')

@section('title')
Aeropost Commission
@stop


@section('breadcrumbs')
    @include('menus.ups-commission')
@stop


@section('content')
	<section class="content-header">
    	<h1>Aeropost Commission</h1>
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
	                        <th>Commission (%)</th>
	                        <th>Action</th>
                    	</tr>
                	</thead>
                	<tbody>
                		@foreach($commissionData as $data)
                		<tr data-editlink="{{ route('editaeropostcommission',[$data->id]) }}" id="<?php echo $data->id; ?>" class="edit-row">
                			<td><?php echo $data->commission;?></td>
                			<?php 
                        		$edit =  route('editaeropostcommission',$data->id);
                        	?>
                			<td>
                				 <div class='dropdown'>
                					<a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
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
            "targets": [-1],
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