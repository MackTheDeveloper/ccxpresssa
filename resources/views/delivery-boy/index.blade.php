@extends('layouts.custom')

@section('title')
Delivery Boys
@stop

<?php
$permissionEdit = App\User::checkPermission(['update_delivery_boy'], '', auth()->user()->id);
$permissionDelete = App\User::checkPermission(['delete_delivery_boy'], '', auth()->user()->id);
?>

@section('breadcrumbs')
@include('menus.delivery-boy')
@stop

@section('content')
<section class="content-header">
    <h1>Delivery Boys</h1>
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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>No. of Assigned Files</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $items)
                    <tr data-editlink="{{ route('viewdetailsdeliveryboy',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                        <td style="display: none;">{{$items->id}}</td>
                        <td>{{$items->name}}</td>
                        <td>{{$items->email}}</td>
                        <td>{{$items->phone_number}}</td>
                        <td class="alignright"><?php echo app('App\DeliveryBoy')->getNoOfAssignedFiles($items->id);  ?></td>
                        <td>
                            <div class='dropdown'>
                                <?php
                                $delete =  route('deletedeliveryboy', $items->id);
                                $edit =  route('editdeliveryboy', $items->id);
                                ?>
                                <?php if ($permissionEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                <?php } ?>
                                <?php if ($permissionDelete) { ?>
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                <?php } ?>
                                <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <li>
                                        <a href="{{ route('viewdetailsdeliveryboy',[$items->id]) }}" title="Edit">View Details</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('cashcollectiondetailsdeliveryboy',[$items->id]) }}" title="Edit">Cash Collection Details</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('manifestdetailsdeliveryboy',[$items->id]) }}" title="Edit">Manifest Details</a>
                                    </li>
                                </ul>
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
    $(document).ready(function() {
        $('#example').DataTable({
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                "targets": [-1],
                "orderable": false
            }, ],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            },

        });;



    })
</script>
@stop