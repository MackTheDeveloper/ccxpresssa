@extends('layouts.custom')
@section('title')
Shipment Listing
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <?php 
        $checkPermissionCreateCargoImport = App\User::checkPermission(['import_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoImport = App\User::checkPermission(['update_import'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoImport = App\User::checkPermission(['delete_import'],'',auth()->user()->id);
        $checkPermissionImportIndexCargo = App\User::checkPermission(['import_cargo_index'],'',auth()->user()->id);     
        $checkPermissionAddImportExpenseCargo = App\User::checkPermission(['add_cargo_import_expense'],'',auth()->user()->id);

        ?>
        <?php if($checkPermissionImportIndexCargo) { ?>
        <li class="widemenu active">
            <a href="{{ route('cargoall') }}">Shipment Listing</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionCreateCargoImport) { ?>
        <li class="widemenu">
            <a href="{{ route('cargoimport','1') }}">Add Manually</a>
        </li>
        <?php } ?>

    </ul>
</aside>
@stop
@section('content')
<section class="content-header" style="float: left;width: 100%">
    <h1 style="float: left;">Shipment Listing</h1>
</section>


<section class="content" style="float: left;">
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
    <div class="box box-success cargocontainer">
        <div class="box-body">
            <select id="cargolisting" class="form-control">
        <option selected="" value="1">Import Shipments</option>
        <option value="2">Export Shipments</option>
        <option value="3">Locale Shipments</option>
        <option value="4">All Shipments</option>
    </select>
        <table id="example" class="display" style="width:100%;margin-top: 10px;float: left;">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Consignee</th>
                <th>Shipper</th>
                <th>AWB/BL No</th>
                <th>Opening date</th>
                <th>Arrival date</th>
                <th>Nature du service</th>
                <th>Action</th>
            </tr>
        </thead>
        <tfoot>
           <tr>
                <th style="display: none">ID</th>
                <th>Consignee</th>
                <th>Shipper</th>
                <th>AWB/BL No</th>
                <th>Opening date</th>
                <th>Arrival date</th>
                <th>Nature du service</th>
                <th>Action</th>
           </tr>
       </tfoot>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($cargos as $cargos)
                <tr data-editlink="{{ route('viewcargo',[$cargos->id,'1']) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                    <td style="display: none">{{$cargos->id}}</td>
                    <td><?php $data = app('App\Clients')->getClientData($cargos->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($cargos->shipper_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td>{{$cargos->awb_bl_no}}</td>
                    <td>{{$cargos->opening_date}}</td>
                    <td>{{$cargos->arrival_date}}</td>
                    <td>{{$cargos->nature_of_goods}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletecargo',[$cargos->id,'1']);
                        $edit =  route('editcargo',[$cargos->id,'1']);
                        ?>
                        <?php if($checkPermissionUpdateCargoImport) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($checkPermissionDeleteCargoImport) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($checkPermissionAddImportExpenseCargo) { ?>
                                    <li><button id="btnCreateExpense" class="btnModalPopup" value="<?php echo route('createexpense',[$cargos->id,'cargo']) ?>">Expense</button></li>
                                    <?php } ?>
                                </ul>
                        </div>
                    </td>
                    
                </tr>
                <?php $i++; ?>
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
    
     var table = $('#example').DataTable({
        'stateSave': true,
        //"ordering": false,
         "order": [[ 0, "desc" ]],
         drawCallback: function(){
              $('.paginate_button', this.api().table().container())          
                 .on('click', function(){
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
                    $('.expandpackage').each(function(){
                        if($(this).hasClass('fa-minus'))
                        {
                        $(this).removeClass('fa-minus');    
                        $(this).addClass('fa-plus');
                        }
                    })
                 });       
           }
    });

    $('#example tfoot th').each( function () {
     var title = $(this).text();
        /*if(title == 'Consignee Name' || title == 'AWE Tracking')
        {
            $(this).html( '<input class="form-control" type="text" placeholder="-- Search --" />' );
        }*/
               if(title == 'Opening date' || title == 'Arrival date')
                {
                $(this).html( '<input class="form-control datepicker" type="text" placeholder="-- Search --" />' );    
                }
                else if(title == 'Action')
                {
                $(this).html( '' );    
                }else{
                $(this).html( '<input class="form-control" type="text" placeholder="-- Search --" />' );
                }

        
    } );
$('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    // Apply the search
    table.columns().every( function () {
        var that = this;
    
        $( 'input', this.footer() ).on( 'keyup change', function () {
            $('#loading').show();
            setTimeout(function() { $("#loading").hide(); }, 200);
            $('.expandpackage').each(function(){
                if($(this).hasClass('fa-minus'))
                {
                $(this).removeClass('fa-minus');    
                $(this).addClass('fa-plus');
                }
            })

            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );
    $('#cargolisting').change(function(){
         var cargoI = $(this).val();
        if(cargoI == 1)
            //window.location = 'cargoimports';
            var urlz = '<?php echo route("cargoimportsajax"); ?>'
        else if(cargoI == 2)
            //window.location = 'cargoexports';
            var urlz = '<?php echo route("cargoexportsajax"); ?>'
        else if(cargoI == 3)
            //window.location = 'cargolocales';
            var urlz = '<?php echo route("cargolocalesajax"); ?>'
        else
            var urlz = '<?php echo route("cargoallajax"); ?>'

        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });

         $.ajax({
                url:urlz,
                type:'POST',
                data:{'cargoI':cargoI},
                success:function(data) {
                        $('.cargocontainer').html(data);
                    }
                });
    })
})
</script>
@stop

