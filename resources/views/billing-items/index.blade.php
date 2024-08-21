@extends('layouts.custom')

@section('title')
Billing Items
@stop

<?php
$permissionBillingItemEdit = App\User::checkPermission(['update_billing_items'], '', auth()->user()->id);
$permissionBillingItemDelete = App\User::checkPermission(['delete_billing_items'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.billing-items')
@stop

@section('content')
<section class="content-header">
    <h1>Billing Items</h1>
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
                        <th>Billing Item Name</th>
                        <th>Billing Item Code</th>
                        <th>Cost Code</th>
                        <th>TCA Applicable</th>
                        <th>Status</th>
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
            "bDestroy": true,
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
            "ajax": {
                url: "{{url('billingitems/listbillingitems')}}", // json datasource
                "data": {},
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var itemId = data[0];
                var editLink = '<?php echo url("billingitem/edit"); ?>';
                editLink += '/' + itemId;

                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $(row).attr('id', itemId);
            }
        });
    }
</script>
@stop