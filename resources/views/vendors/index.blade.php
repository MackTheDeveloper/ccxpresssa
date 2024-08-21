@extends('layouts.custom')

@section('title')
Vendors
@stop

<?php
$permissionVendorsEdit = App\User::checkPermission(['update_vendors'], '', auth()->user()->id);
$permissionVendorsDelete = App\User::checkPermission(['delete_vendors'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.vendors')
@stop

@section('content')
<section class="content-header">
    <h1>Vendors</h1>
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
                        <th>Company Name</th>
                        <th>Currency</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Payment Term</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <div id="modalViewDetail" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title text-center primecolor">Client Detail</h3>
                </div>
                <div class="modal-body" id="modalContentViewDetail" style="overflow: hidden;">
                </div>
            </div>

        </div>
    </div>

    <div id="modalViewUserActivities" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title text-center primecolor">User Activities</h3>
                </div>
                <div class="modal-body" id="modalContentViewUserActivities" style="overflow: hidden;">
                </div>
            </div>

        </div>
    </div>


    <div id="resetPassword" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3 class="modal-title text-center primecolor">Reset Password</h3>
                </div>
                <div class="modal-body" style="overflow: hidden;">
                    <div class="col-md-offset-1 col-md-10">
                        <form method="POST" id="resetPasswordForm">
                            {{ csrf_field() }}
                            <input type="hidden" value="" name="userId" id="userId">
                            <div class="form-group has-feedback">
                                <input type="password" name="password" class="form-control" placeholder="Password">
                                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                                <span class="text-danger">
                                    <strong id="password-error"></strong>
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <button type="button" id="resetPasswordFormButton" class="btn btn-primary btn-prime white btn-flat">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

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

        //$('.copyvendor').click(function() {
        $(document).delegate('.copyvendor', 'click', function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var userId = $(this).data('userid');
            var currencyIs = $(this).data('currency');
            if (currencyIs == 'USD')
                var newCurrency = 'HTG';
            else
                var newCurrency = 'USD';
            Lobibox.confirm({
                msg: "Are you sure to copy vendor with currency " + newCurrency + "?",
                callback: function(lobibox, type) {

                    if (type == 'yes') {
                        $.ajax({
                            type: 'post',
                            url: '<?php echo url('vendors/copyvendor'); ?>',
                            data: {
                                'userId': userId,
                                'newCurrency': newCurrency
                            },
                            success: function(response) {
                                window.location.href = '<?php echo route("vendors"); ?>';
                            }
                        });
                    } else {

                    }
                }
            })
        })
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
                url: "{{url('vendors/listvendors')}}", // json datasource
                "data": {},
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                var vendorId = data[0];
                var editLink = '<?php echo url("vendors/edit"); ?>';
                editLink += '/' + vendorId;

                $(row).attr('data-editlink', editLink);
                $(row).addClass('edit-row');
                $(row).attr('id', vendorId);
            }
        });
    }
</script>
@stop