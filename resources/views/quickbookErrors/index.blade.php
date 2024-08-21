@extends('layouts.custom')

@section('title')
QuickBook Error Logs
@stop

@section('content')
<section class="content-header">
	<h1>QuickBook Error Logs</h1>
</section>
<section class="content">
	<div class="box box-success">
		<div class="box-body">
			<table id="example" class="display nowrap" style="width:100%">
				<thead>
					<tr>
						<th>Name</th>
						<th>Module</th>
						<th>operation</th>
						<th>Error Message</th>
					</tr>
				</thead>
				<tbody>
					@foreach($error as $key => $v)
					<tr>
						<td>{{$v['unique_id']}}</td>
						<td>{{$v['module']}}</td>
						<td>{{$v['operation']}}</td>
						<td>{{$v['error_message']}}</td>
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
		$('#example').DataTable({
			"scrollX": true,
		});
	});
</script>
@stop