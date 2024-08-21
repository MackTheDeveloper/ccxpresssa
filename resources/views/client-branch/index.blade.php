@extends('layouts.custom')

@section('title')
Client Branchs
@stop

@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        
        
        <li class="widemenu">
            <a href="{{ route('createclientbranch') }}">Add Client Branch</a>
        </li>
        
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.client-branch')
@stop


@section('content')
<section class="content-header">
    <h1>Client Branchs</h1>
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
                <th>Company</th>
                <th>Branch Name</th>
                <th>Country</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $items)
                <tr data-editlink="{{ route('editclientbranch',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td>{{$items->company_name}}</td>
                    <td>{{$items->branch_name}}</td>
                    <td><?php $dataCountry = App\Country::getData($items->country_id); echo $dataCountry->name; ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteclientbranch',$items->id);
                        $edit =  route('editclientbranch',$items->id);
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

