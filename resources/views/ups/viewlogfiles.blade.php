@extends('layouts.custom')
@section('title')
Uploaded File Log
@stop
<?php 
$permissionCourierImportEdit = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
$permissionCourierImportDelete = App\User::checkPermission(['delete_courier_import'],'',auth()->user()->id); 
$permissionCourierAddExpense = App\User::checkPermission(['add_courier_expenses'],'',auth()->user()->id); 
$permissionCourierAddInvoice = App\User::checkPermission(['add_courier_invoices'],'',auth()->user()->id); 
?>
@section('content')
<section class="content-header">
    <h1>Uploaded File Log</h1>
</section>

<section class="content">
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
                <th>Last Operation</th>
                <th>Date</th>
                <th>Company</th>
                <th>No Manifeste</th>
                <th>AWE Tracking</th>
                <th>Destination</th>
                <th>Origin</th>
                <th>NBR. PCS</th>
                <th>Weight</th>
                <th>Billing Term</th>
            </tr>
        </thead>
       
        <tbody>
            <?php $i = 1; ?>
            @foreach ($upsData as $couriers)
                <?php 
                $dataPackage = App\Ups::checkPakckages($couriers->id); 
                    $cls = '';
                    if($dataPackage > 0)
                        $cls = 'fa fa-plus';
                ?>
                <tr data-editlink="{{ route('editups',[$couriers->id,$couriers->courier_operation_type]) }}" id="<?php echo $couriers->id; ?>" class="edit-row">
                    <td>{{ucfirst($couriers->last_action)}}</td>
                    <td>{{date('d-m-Y',strtotime($couriers->tdate))}}</td>
                    <td><a href="{{ route('editups',[$couriers->id,$couriers->courier_operation_type]) }}">{{$couriers->company}}</a></td>
                    <td>{{$couriers->no_manifeste}}</td>
                    <td>{{$couriers->awb_number}}</td>
                    <td>{{$couriers->destination}}</td>
                    <td>{{$couriers->origin}}</td>
                    <td>{{$couriers->nbr_pcs}}</td>
                    <td>{{$couriers->weight}}</td>
                    <td><?php echo App\Ups::getBillingTerm($couriers->id); ?></td>
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

