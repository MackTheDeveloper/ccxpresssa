@extends('layouts.custom')

@section('title')
Vendor Contacts
@stop

<?php 
    $permissionClientsContactEdit = App\User::checkPermission(['update_vendor_contacts'],'',auth()->user()->id); 
    $permissionClientsContactDelete = App\User::checkPermission(['delete_vendor_contacts'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.vendors')
@stop


@section('content')
<section class="content-header">
    <h1>Vendor Contacts</h1>
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
                <th>Vendor</th>
                <th>Name</th>
                <th>Position</th>
                <th>Cell Number</th>
                <th>Direct line</th>
                <th>Work</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $items)
                <tr data-editlink="{{ route('editvendorcontact',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none;">{{$items->id}}</td>
                    <td><?php $dataClient = app('App\Vendors')->getVendorData($items->vendor_id); echo !empty($dataClient->company_name) ? $dataClient->company_name : '-'; ?></td>
                    <td>{{$items->name}}</td>
                    <td>{{$items->personal_contact}}</td>
                    <td>{{$items->cell_number}}</td>
                    <td>{{$items->direct_line}}</td>
                    <td>{{$items->work}}</td>
                    <td>{{$items->email}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletevendorcontact',$items->id);
                        $edit =  route('editvendorcontact',$items->id);
                        ?>
                        <?php if($permissionClientsContactEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionClientsContactDelete) { ?>
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

