@extends('layouts.custom')

@section('title')
Consolidation Report
@stop

@section('breadcrumbs')
    @include('menus.reports')
@stop

@section('content')
	<section class="content-header">
	    <h1>Consolidation Report</h1>
	</section>

	<section class="content">
    
    	<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    	<div class="box box-success">
        	<div class="box-body">
        		<div class="row" style="margin-bottom: 1%;float: right">
					
					<div class="col-md-4">

						
							<div class="col-md-4">
								<button id="ExpToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true" style="margin-right: 3%"></i></span>Export To Excel</button>
							</div>
							
						

					</div>
					
				</div>
        		<div class="container-rep">
        			<table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th>Courier Type</th>
                       	<th>File Number</th>
                       	<th>File Type</th>
                        <th>Invoice Number</th>
                        <th>AWB Tracking</th>
                    	<th>Freight</th>
                    	<th>Commission</th>
                    </tr>
                </thead>
                <tbody>
                	@foreach($combineData as $data)
	                	<tr>
	                		<td style="display: none">{{$data->id}}</td>
	                		<td><?php echo $data->file_type == 'A' ? 'Aeropost' : 'Ups';?></td>
	                		<td>{{$data->file_number}}</td>
	                		<td>
	                			<?php echo !empty($data->opType) ? ($data->opType == 1 ? 'Import' : 'Export') : '-'?>
	                		</td>
	                		<td>
	                			<?php echo !empty($data->bill_no) ? $data->bill_no : '-';?>
	                		</td>
	                		<td>
	                			<?php echo !empty($data->awb_no) ? $data->awb_no : '-';?>
	                		</td>
	                		<td>
	                			<?php echo !empty($data->Freight) ? $data->Freight : '-';?>
	                		</td>
	                		<td>
	                			<?php echo !empty($data->commission) ? $data->commission : '-';?>
	                		</td>
	                	</tr>
                	@endforeach
                </tbody>
        		</div>
        	</div>
    	</div>
        <iframe src="" id="frame" style="display: none;"></iframe>
	</section>

@endsection

@section('page_level_js')
	<script type="text/javascript">
		var table = $('#example').DataTable({
		'stateSave': true,
      	  "columnDefs": [{
        	  "orderable": false,
        	}],
        	"scrollX": true,
        	"order": [[ 0, "desc" ]],
    	});

    	$('#ExpToExcel').click(function(){
            $('#loading').show();
    		$.ajaxSetup({
    			headers: {
                	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            	}
    		});
    		var rExcel =" <?php echo url('reports/tmpReportByInvoice');?>"
    		$.ajax({
    			url : rExcel,
    			type : 'POST',
    			data : '',
    			success : function(data){
                    //console.log(data);
    				$('#frame').attr('src',rExcel);
                    //console.log(data);
                	//window.open(rExcel,'_blank' );
                    $('#loading').hide();
    			}
    		});
    	});
    </script>
@stop