@extends('layouts.custom')

@section('title')
Cashier Report
@stop

@section('breadcrumbs')
    @include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Cashier Report</h1>
</section>

<section class="content">
    
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
        	<div class="container-rep">
                
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr.no</th>
                            <th>Name</th>
                            <th>Email Id</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                <tbody>
                	<?php $srNo = 1;?>
                	@foreach($cashierDetail as $cashierDetail)
                      <tr data-editlink="{{ route('cashierReportAllDetail',[$cashierDetail->id]) }}" id="{{$cashierDetail->id}}"; class="edit-row">
                    	<td>
                    		{{$srNo}}
                    	</td>
                    	<td>
                    		{{$cashierDetail->name}}
                    	</td>
                    	<td>
                    		{{$cashierDetail->email}}
                    	</td>
                    	<td>
                    		{{'Cashier'}}
                    	</td>
                    </tr>
                
                    <?php $srNo++;?>
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
        "columnDefs": [ {
            "targets": [],
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

function getDetail(){
	console.log("Hello");
}
</script>
@stop
