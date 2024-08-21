@extends('layouts.custom')


@section('title')
Dashboard
@stop

@section('content')
<section class="content-header" style="display: block;position: relative;top: 0px;">
    <h1 style="font-size: 20px !important;font-weight: 600;">Dashboard</h1>
</section>
<section class="content editupscontainer">
    <div class="box box-success">
        <div class="box-body">

            
           
           Warehouse 

        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable({
        "ordering": false
    });

   

} )
</script>
@stop
