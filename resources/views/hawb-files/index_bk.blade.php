@extends('layouts.custom')

@section('title')
House AWB Files Listing
@stop

<?php 
    $permissionCargoHAWBEdit = App\User::checkPermission(['update_cargo_hawb'],'',auth()->user()->id); 
    $permissionCargoHAWBDelete = App\User::checkPermission(['delete_cargo_hawb'],'',auth()->user()->id);
?>

@section('breadcrumbs')
    @include('menus.cargo-files')
@stop

@section('content')
<section class="content-header">
    <h1>House AWB Files Listing</h1>
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
                <th>Type</th>
                <th>File No.</th>
                <th>House AWB No.</th>
                <th>Consignee</th>
                <th>Shipper</th>
                <th>Master File</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $user)
                <tr data-editlink="{{ route('viewhawbfile',[$user->id]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                    <td style="display: none;">{{$user->id}}</td>
                    <td><?php echo ($user->cargo_operation_type == 1 ? 'Import' : ($user->cargo_operation_type == 2 ? 'Export' : 'Locale')) ?></td>
                    <td><?php echo $user->file_number; ?></td>
                    <td><?php echo $user->cargo_operation_type == 1 ? $user->hawb_hbl_no : $user->export_hawb_hbl_no; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($user->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($user->shipper_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td>
                        <?php $dataConsolidate  = DB::table('cargo')
                                            ->select(DB::raw('group_concat(file_number) as MasterFiles'))
                                             ->whereRaw("find_in_set($user->id,hawb_hbl_no)")
                                            ->first();
                        echo !empty($dataConsolidate->MasterFiles) ? $dataConsolidate->MasterFiles : 'Not Assigned';
                    ?>
                    </td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletehawbfile',$user->id);
                        $edit =  route('edithawbfile',$user->id);
                        ?>
                        <a title="Click here to print"  target="_blank" href="{{ route('printhawbfiles',[$user->id,$user->cargo_operation_type]) }}"><i class="fa fa-print"></i></a>
                        &nbsp; &nbsp;
                        <?php //if($permissionCargoHAWBEdit && $user->created_by == auth()->user()->id) {
                            if($permissionCargoHAWBEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php //if($permissionCargoHAWBDelete && $user->created_by == auth()->user()->id) { 
                            if($permissionCargoHAWBDelete) { ?>
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
        "columnDefs": [{
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
        
    });
} )
</script>
@stop

