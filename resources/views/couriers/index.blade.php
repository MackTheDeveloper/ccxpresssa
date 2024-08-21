@extends('layouts.custom')
@section('title')
Couriers
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <?php 
        $checkPermissionCreateCourier = App\User::checkPermission(['create_couriers'],'',auth()->user()->id); 
        $checkPermissionUpdateCourier = App\User::checkPermission(['update_couriers'],'',auth()->user()->id); 
        $checkPermissionDeleteCourier = App\User::checkPermission(['delete_couriers'],'',auth()->user()->id); 
        $checkPermissionImportCourier = App\User::checkPermission(['import_couriers'],'',auth()->user()->id);
        ?>
        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('createcourier') }}">Create Courier</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionImportCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('importcourier') }}">Import Courier</a>
        </li>
        <?php } ?>
    </ul>
</aside>
@stop
@section('content')
<section class="content-header">
    <h1>Couriers</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    @if(Session::has('flash_message_error'))
        <div class="alert alert-danger flash-danger">
            {{ Session::get('flash_message_error') }}
        </div>
    @endif
    
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Consignee Name</th>
                <th>Shipping Detail</th>
                <th>AWE Tracking</th>
                <th>Origin Country Code</th>
                <th>Weight</th>
                <th>Declared Value</th>
                <th>Freight</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($couriers as $couriers)
                <tr>
                    <td>{{$couriers->consignee_name}}</td>
                    <td>{{$couriers->no_manifeste}}</td>
                    <td>{{$couriers->awe_tracking}}</td>
                    <td>{{$couriers->origin_country_code}}</td>
                    <td>{{$couriers->weight}}</td>
                    <td>{{$couriers->declared_value}}</td>
                    <td>{{$couriers->freight}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletecourier',$couriers->id);
                        $edit =  route('editcourier',$couriers->id);
                        ?>
                        <?php if($checkPermissionUpdateCourier) { ?>
                        <a href="<?php echo $edit ?>" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($checkPermissionDeleteCourier) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <li><button id="btnCreateExpense" class="btnModalPopup" value="<?php echo route('createexpense',[$couriers->id,'courier']) ?>">Expense</button></li>
                                </ul>
                        </div>
                        
                    </td>
                    
                </tr>
            @endforeach
            
        </tbody>
        
    </table>
        </div>
    </div>

<div id="modalCreateExpense" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">Add Expense</h3>
            </div>
            <div class="modal-body" id="modalContentCreateExpense" style="overflow: hidden;">
            </div>
        </div>

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
})
</script>
@stop

