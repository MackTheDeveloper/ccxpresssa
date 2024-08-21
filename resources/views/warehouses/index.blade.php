@extends('layouts.custom')

@section('title')
Warehouses
@stop

<?php 
    $permissionWarehousesEdit = App\User::checkPermission(['update_warehouses'],'',auth()->user()->id); 
    $permissionWarehousesDelete = App\User::checkPermission(['delete_warehouses'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.warehouses')
@stop

@section('content')
<section class="content-header">
    <h1>Warehouses</h1>
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
                <th>Used For</th>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $items)
                <tr data-editlink="{{ route('editwarehouse',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none;">{{$items->id}}</td>
                    <td>{{Config::get('app.warehouseFor')[$items->warehouse_for]}}</td>
                    <td>{{$items->name}}</td>
                    <td><?php echo ($items->status == 1) ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletewarehouse',$items->id);
                        $edit =  route('editwarehouse',$items->id);
                        ?>
                        <?php if($permissionWarehousesEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionWarehousesDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        
                       
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
    $('#example').DataTable(
    {
        'stateSave': true,
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        },
        
    });

   

} )
</script>
@stop

