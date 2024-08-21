@extends('layouts.custom')

@section('title')
Client Credit Report
@stop

@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Client Credit Report</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">


            <div class="container-rep">
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        DatatableInitiate();
    })

    function DatatableInitiate() {
        $('#example').DataTable({
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                    "targets": [],
                    "orderable": false
                },
            ],
            //"ordering": false,
            "scrollX": true,
            "ajax": {
                url: "{{url('reports/listgetclientcreditdataonclick')}}", // json datasource
                "data": {
                    'clientId': <?php echo $clientId; ?>,
                    'clientName': '<?php echo $clientName; ?>',
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {}
        });
    }
</script>
@stop