@extends('layouts.custom')
@section('title')
All UPS Files
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <?php 
        $checkPermissionCreateCourier = App\User::checkPermission(['create_ups'],'',auth()->user()->id); 
        $checkPermissionUpdateCourier = App\User::checkPermission(['update_ups'],'',auth()->user()->id); 
        $checkPermissionDeleteCourier = App\User::checkPermission(['delete_ups'],'',auth()->user()->id); 
        $checkPermissionImportCourier = App\User::checkPermission(['import_ups'],'',auth()->user()->id);
        $checkPermissionListingCourier = App\User::checkPermission(['ups_shipment_listing'],'',auth()->user()->id);
        $checkPermissionAddExpenseCourier = App\User::checkPermission(['add_ups_expense'],'',auth()->user()->id);
        ?>
        <?php if($checkPermissionListingCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('ups') }}">UPS Shipments</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionImportCourier) { ?>
        <li class="widemenu submenu">
            <a href="{{ route('importups') }}">Upload File</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu submenu">
            <a href="{{ route('createups') }}">Add Manually</a>
        </li>
        <?php } ?>


        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu">
            <a href="javascript:void(0)">Aeropost Shipments</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionImportCourier) { ?>
        <li class="widemenu submenu">
            <a href="javascript:void(0)">Upload File</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu submenu">
            <a href="javascript:void(0)">Add Manually</a>
        </li>
        <?php } ?>

    
        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu active">
            <a href="{{ route('viewall') }}">View All Shipments</a>
        </li>
        <?php } ?>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.ups-import')
@stop

@section('content')
<section class="content-header">
    <h1>All UPS Files</h1>
</section>

<section class="content">


    @if(Session::has('flash_message_import'))
        <div class="alert alert-success-custom flash-success">
            <span><?php echo Session::get('flash_message_import')['totalUploaded']; ?></span><br/>
            <span><?php echo Session::get('flash_message_import')['totalAdded']; ?></span><br/>
            <span><?php echo Session::get('flash_message_import')['totalUpdated']; ?></span>
        </div>
    @endif
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
                <th>Packages</th>
                <th>Date</th>
                <th>Company</th>
                <th>No Manifeste</th>
                <th>AWE Tracking</th>
                <th>Destination</th>
                <th>Origin</th>
                <th>NBR. PCS</th>
                <th>Weight</th>
                <th>Billing Term</th>
                <th>Action</th>
            </tr>
        </thead>
        <tfoot>
           <tr>
                <th>Packages</th>
                <th>Date</th>
                <th>Company</th>
                <th>No Manifeste</th>
                <th>AWE Tracking</th>
                <th>Destination</th>
                <th>Origin</th>
                <th>NBR. PCS</th>
                <th>Weight</th>
                <th>Billing Term</th>
                <th>Action</th>
           </tr>
       </tfoot>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($upsData as $couriers)
                <?php $dataPackage = App\Ups::checkPakckages($couriers->id); 
                    $cls = '';
                    if($dataPackage > 0)
                        $cls = 'fa fa-plus';
                ?>
                <tr data-editlink="{{ route('editups',$couriers->id) }}" id="<?php echo $couriers->id; ?>" class="edit-row">
                    <td style="display: block;text-align: center;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-upsid="<?php echo $couriers->id; ?>"></td>
                    <td>{{$couriers->tdate}}</td>
                    <td><a href="{{ route('editups',$couriers->id) }}">{{$couriers->company}}</a></td>
                    <td>{{$couriers->no_manifeste}}</td>
                    <td>{{$couriers->awb_number}}</td>
                    <td>{{$couriers->destination}}</td>
                    <td>{{$couriers->origin}}</td>
                    <td>{{$couriers->nbr_pcs}}</td>
                    <td>{{$couriers->weight}}</td>
                    <td><?php echo App\Ups::getBillingTerm($couriers->id); ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteups',$couriers->id);
                        $edit =  route('editups',$couriers->id);
                        ?>
                        <?php if(!$checkPermissionUpdateCourier) { ?>
                        <a href="<?php echo $edit ?>" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if(!$checkPermissionDeleteCourier) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($checkPermissionAddExpenseCourier) { ?>
                                    <li><button id="btnCreateExpense" class="btnModalPopup" value="<?php echo route('createexpense',[$couriers->id,'ups']) ?>">Expense</button></li>
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
        //"ordering": false,
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
        if(title != 'Packages' && title != 'Action')
        {
                if(title == 'Date')
                {
                $(this).html( '<input class="form-control datepicker" type="text" placeholder="-- Search --" />' );    
                }
                else if(title == 'Billing Term')
                {
                $(this).html( '<select class="form-control"><option value="">--Select--</option><option>FC</option><option>FD</option><option>PP</option></select>' );    
                }else{
                $(this).html( '<input class="form-control" type="text" placeholder="-- Search --" />' );
                }

        }else{
            $(this).html( '' );    
        }
    } );
$('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    // Apply the search
    table.columns().every( function () {
        var that = this;
    
        $( 'input,select', this.footer() ).on( 'keyup change', function () {
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

    //$('.expandpackage').click(function(){
    $(document).delegate('.expandpackage','click',function(){
        $('#loading').show();
        setTimeout(function() { $("#loading").hide(); }, 200);
        //$('#loading').show();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var thiz = $(this);
        var parentTR = thiz.closest('tr');
        if(thiz.hasClass('fa-plus'))
        {
            $('.childrw').remove();
            $('.fa-minus').each(function(){
                $(this).removeClass('fa-minus');    
                $(this).addClass('fa-plus');
                //$(this).closest('tr').next('tr').remove();
            })

            thiz.removeClass('fa-plus');
            thiz.addClass('fa-minus');
            var upsId = $(this).data('upsid');
            var rowId = $(this).data('rowid');
            $.ajax({
                url:'ups/expandpackage',
                type:'POST',
                data: {upsId:upsId,rowId:rowId},
                success:function(data) {
                    $(data).insertAfter(parentTR).slideDown();
                },
            });
            //$('#loading').hide();
        }else
        {
            thiz.removeClass('fa-minus');
            thiz.addClass('fa-plus');
            $('.childrw').remove();
            //parentTR.next('tr').remove();
            //$('#loading').hide();

        }
    })

})
</script>
@stop

