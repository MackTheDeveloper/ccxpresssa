@extends('layouts.custom')

@section('title')
Aeropost Files
@stop

<?php 
    $permissionAeropostEdit = App\User::checkPermission(['update_aeropost'],'',auth()->user()->id); 
    $permissionAeropostDelete = App\User::checkPermission(['delete_aeropost'],'',auth()->user()->id); 
    $permissionAeropostAddInvoice = App\User::checkPermission(['add_aeropost_invoices'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.aeropost')
@stop

@section('content')
<section class="content-header">
    <h1>Aeropost Files Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    @if(Session::has('flash_message_error'))
        <div class="alert alert-danger flash-danger">
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
                <th>File Number</th>
                <th>Date</th>
                <th>From</th>
                <th>Consignee</th>
                <th>Freight</th>
                <th>Flight Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $items)
                <tr data-editlink="{{ route('viewdetailsaeropost',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none;">{{$items->id}}</td>
                    <td>{{$items->file_number}}</td>
                    <td>{{date('d-m-Y',strtotime($items->date))}}</td>
                    <td>{{$items->from_location}}</td>
                    <td><?php $data = app('App\Clients')->getClientData($items->consignee); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td>{{$items->freight}}</td>
                    <td>{{date('d-m-Y h:i A',strtotime($items->flight_date_time))}}</td>
                    <td><?php echo ($items->status == 1) ? 'Active' : 'In active'; ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteaeropost',$items->id);
                        $edit =  route('editaeropost',$items->id);
                        ?>
                        <?php if($permissionAeropostEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionAeropostDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="{{url('files/upload',['aeropost',$items->id])}}" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                            <ul class='dropdown-menu' style='left:auto;'>
                                   <?php if($permissionAeropostAddInvoice) { ?>
                                    <li>
                                        <a href="{{ route('createaeropostinvoice',$items->id) }}">Add Invoice</a>
                                    </li>
                                    <?php } ?>
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
    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
        "date-uk-pre": function ( a ) {
            if (a == null || a == "") {
                return 0;
            }
            var ukDatea = a.split('-');
            return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
        },
    
        "date-uk-asc": function ( a, b ) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },
    
        "date-uk-desc": function ( a, b ) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });
    $('#example').DataTable(
    {
        'stateSave': true,
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            },{ type: 'date-uk', targets: 2 }],
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

