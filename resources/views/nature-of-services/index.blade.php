@extends('layouts.custom')

@section('title')
Nature Of Services
@stop

@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <?php 
        $checkPermissionCreateUser = App\User::checkPermission(['create_client'],'',auth()->user()->id); 
        $checkPermissionUpdateUser = App\User::checkPermission(['update_client'],'',auth()->user()->id);
        $checkPermissionDeleteUser = App\User::checkPermission(['delete_client'],'',auth()->user()->id);
        ?>
        
        <li class="widemenu">
            <a href="{{ route('createnatureofservice') }}">Add Nature Of Service</a>
        </li>
        
        
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.nature-of-service')
@stop

@section('content')
<section class="content-header">
    <h1>Nature Of Services</h1>
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
            <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $user)
                <tr data-editlink="{{ route('editnatureofservice',[$user->id]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                    <td>{{$user->name}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletenatureofservice',$user->id);
                        $edit =  route('editnatureofservice',$user->id);
                        ?>
                        
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        
                        
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
        "ordering": false
    });
} )
</script>
@stop

