@extends('layouts.custom')

@section('title')
Profit Report
@stop

@section('breadcrumbs')
    @include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Profit Report</h1>
</section>

<section class="content">
    
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
        	<div class="row" style="float: right;margin-bottom: 1%">
					
					<div class="col-md-4">

						<button id="ExpToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true" style="margin-right: 3%"></i></span>Export To Excel</button>
					</div>
					
				</div>
        	<div class="container-rep">
        		<table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                       	<th>File Number</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
                        <th>AWB Tracking</th>
                    	<th>Billing Term</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($UpsFileData as $couriers)
	                    <tr style="" id="view-detail" class="viewdetail" value="<?php echo url('reports/upsprofitreports/view-detail',['flag'=>'viewUpsDetail','id'=>$couriers->id]) ?>" data-module= '{{"Profit Report Of File : ".$couriers->file_number}}'>
	                        <td style="display: none">{{$couriers->id}}</td>
	                        
	                        <td id="view-detail">{{$couriers->file_number}}</td>
	                        <td><?php $data = app('App\Clients')->getClientData($couriers->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
	                        <td><?php $data = app('App\Clients')->getClientData($couriers->shipper_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
	                        
	                        <td>{{$couriers->awb_number}}</td>
	                        
	                        <td><?php echo App\Ups::getBillingTerm($couriers->id); ?></td>
	                    </tr>
                    @endforeach
        	</div>
        </div>
    </div>
    <iframe src="" id="frame" style="display: none;"></iframe>
</section>
<div id="modalViewUpsDetail" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
             <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <center>
                    <h3 class="modal-title modal-title-block text-center primecolor"></h3>
                </center>
            </div>
            <div class="modal-body" id="modalContentViewUpsDetail" >
        	</div>
    	</div>
	</div>
</div>
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
		$.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var rExcel = "<?php echo url('reports/upsprofitreports/view-detail',['flag'=>'exportToExcel']) ?>"; 
        $.ajax({
        	url: rExcel,
        	type: 'POST',
            data: '',
            success:function(data) {
                $('#frame').attr('src',rExcel);
                $('#loading').hide();
            },
        });
	});

	</script>
@stop