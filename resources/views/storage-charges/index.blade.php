@extends('layouts.custom')

@section('title')
Storage Charges
@stop

<?php 
    $permissionStorageChargeEdit = App\User::checkPermission(['update_storage_charges'],'',auth()->user()->id); 
    $permissionStorageChargeDelete = App\User::checkPermission(['delete_storage_charges'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.storage-charges')
@stop

@section('content')
<section class="content-header">
    <h1>Storage Charges</h1>
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
                <th>Grace Period (Days)</th>
                <th>Weight / Volume</th>
                <th>Charge</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $items)
                <tr data-editlink="{{ route('editstoragecharge',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none;">{{$items->id}}</td>
                    <td>{{$items->grace_period}}</td>
                    <td><?php echo $items->measure == 'K' ? 'Kg' : ($items->measure == 'P' ? 'Pound' : ($items->measure == 'M' ? 'Cubit Meter' : 'Cubit Feet')); ?></td>
                    <td>{{$items->charge}}</td>
                    <td><?php echo ($items->status == 1) ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletestoragecharge',$items->id);
                        $edit =  route('editstoragecharge',$items->id);
                        ?>
                        <?php if($permissionStorageChargeEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionStorageChargeDelete) { ?>
                        <a style="display: none" class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
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
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            }],
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
        
    });;

   

} )
</script>
@stop

