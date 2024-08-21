@extends('layouts.custom')

@section('title')
<?php echo 'Guarantee Checks'; ?>
@stop

<?php
$permissionClientsEdit = App\User::checkPermission(['update_clients'], '', auth()->user()->id);
$permissionClientsDelete = App\User::checkPermission(['delete_clients'], '', auth()->user()->id);
$permissionClientsResetPassword = App\User::checkPermission(['reset_password_clients'], '', auth()->user()->id);

?>

@section('breadcrumbs')
@include('menus.client-management')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Guarantee Checks' ?></h1>
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
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th style="display: none">Opetion 1</th> <!-- Opetion 1 = module ID -->
                        <th style="display: none">Opetion 2</th> <!-- Opetion 2 = module operation type (import/export) -->
                        <th>File Number</th>
                        <th>Date</th>
                        <th>DESCA Invoice</th>
                        <th>DESCA Check Number</th>
                        <th>Detention Days</th>
                        <th>Delivered Date</th>
                        <th>Return Date</th>
                        <th>Check Return?</th>
                        <th>Total Cost</th>
                        <th>Billed Amount</th>
                        <th>Difference</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
<style>
    .hide_column {
        display: none;
    }
</style>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        DatatableInitiate();
    })

    function DatatableInitiate() {
        $('#example').DataTable({
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                    "targets": [-1],
                    "orderable": false
                },
                {
                    targets: [0],
                    className: "hide_column"
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            /* drawCallback: function(){
              $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
                });       
                $('#example_filter input').bind('keyup', function(e) {
                        $('#loading').show();
                        setTimeout(function() { $("#loading").hide(); }, 200);
                });
            }, */
            "ajax": {
                url: "{{url('check-guarantee/listbydatatableserverside')}}", // json datasource
                data: {
                    'flag': '<?php echo $flag; ?>'
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                $('#loading').show();
                setTimeout(function() {
                    $("#loading").hide();
                }, 1000);
                var moduleId = data[0];
                var Operation1 = data[1];

                var editLink = '<?php echo url("cargo/viewcargo"); ?>';
                editLink += '/' + moduleId + '/' + Operation1;
                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $(row).attr('id', moduleId);
                var thiz = $(this);
                $('td', row).eq(11).addClass('alignright');
                $('td', row).eq(12).addClass('alignright');
                $('td', row).eq(13).addClass('alignright');
                i++;
            }

        });
    }
</script>
@stop