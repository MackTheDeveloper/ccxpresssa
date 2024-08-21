@extends('layouts.custom')

@section('title')
Free Domicile Charges
@stop

<?php 
    $permissionCountriesEdit = App\User::checkPermission(['update_countries'],'',auth()->user()->id); 
    $permissionCountriesDelete = App\User::checkPermission(['delete_countries'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.fdcharges')
@stop

@section('content')
<section class="content-header">
    <h1>Free Domicile Charges</h1>
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
                <th style="display: none">ID</th>
                <th>Name</th>
                <th>Code</th>
                <th>Charge</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $items)
                <tr data-editlink="{{ route('editfdcharges',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none;">{{$items->id}}</td>
                    <td>{{$items->name}}</td>
                    <td>{{$items->code}}</td>
                    <td>{{$items->charge}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletefdcharges',$items->id);
                        $edit =  route('editfdcharges',$items->id);
                        ?>
                        
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <a class="delete-record" href="<?php echo $delete ?>" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        
                        
                        
                        
                       
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

