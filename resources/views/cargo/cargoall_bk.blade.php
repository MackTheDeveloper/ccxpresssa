@extends('layouts.custom')
@section('title')
Cargo Files Listing
@stop

<?php 
        $permissionCargoEdit = App\User::checkPermission(['update_cargo'],'',auth()->user()->id); 
        $permissionCargoDelete = App\User::checkPermission(['delete_cargo'],'',auth()->user()->id); 
        $permissionCargoExpensesAdd = App\User::checkPermission(['add_cargo_expenses'],'',auth()->user()->id); 
        $permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.cargo-files')
@stop


@section('content')
<section class="content-header">
    <h1>Cargo Files Listing</h1>
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
    <div id="flash">
    </div>
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success cargocontainer">
        <div class="box-body">
            <select id="cargolisting" class="form-control">
                    <option selected="" value="4">All Files (I, E, L)</option>
                    <option value="1">Import Files</option>
                    <option value="2">Export Files</option>
                    <option value="3">Locale Files</option>
            </select>

            <select id="cargofiletype" class="form-control">
                    <option selected="" value="">All Files (Cons. & Non cons.)</option>
                    <option value="1">Consolidate Files</option>
                    <option value="0">Non consolidate Files</option>
            </select>

        <div class="out-filter-secion col-md-4">

        <div class="from-date-filter-div filterout col-md-6">
            <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
        </div>
        <div class="to-date-filter-div filterout col-md-6">
            <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
        </div>
        </div>
        <table id="example" class="display nowrap" style="width:100%;float: left;">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th></th>
                <th>File No.</th>
                <th>Agent</th>
                <th>Opening Date</th>
                <th>AWB/BL No</th>
                <th>Consignee/Client</th>
                <th>Invoice Numbers</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php $count = 0;?>
            @foreach ($cargos as $cargos)
            <?php $dataTotal = App\HawbFiles::checkHawbFiles($cargos->id); 
                    $cls = '';
                    if($dataTotal > 0)
                        $cls = 'expandpackage fa fa-plus';

                    $assignedCss = '';
                    $checkFileAssigned = App\Cargo::checkFileAssgned($cargos->id);
                    if($checkFileAssigned == 'no')
                        $assignedCss = 'color:#3097D1';
                    ?>
                    @if($cargos->cargo_operation_type == 3)
                        <tr style="<?php echo $assignedCss; ?>"  data-editlink="{{ route('viewcargolocalfiledetailforcashier',$cargos->id) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                    @else 
                        <tr style="<?php echo $assignedCss; ?>" data-editlink="{{ route('viewcargo',[$cargos->id,$cargos->cargo_operation_type]) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                    @endif
                    <td style="display: none">{{$cargos->id}}</td>
                    <td style="display: block;text-align: center;padding-top: 13px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-cargoid="<?php echo $cargos->id; ?>"></td>
                    <td>{{$cargos->file_number}}</td>
                    <td><?php $data = app('App\User')->getUserName($cargos->agent_id); echo !empty($data->name) ? $data->name : '-'; ?></td>
                    <td><?php echo date('d-m-Y',strtotime($cargos->opening_date)) ?></td>
                    <td><?php echo !empty($cargos->awb_bl_no) ? $cargos->awb_bl_no : '-'; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($cargos->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php echo App\Expense::getInvoicesOfFile($cargos->id,$cargos->cargo_operation_type);  ?></td>
                    <td>
                        <div class='dropup'>
                        <a title="Click here to print"  target="_blank" href="{{ route('printcargofile',[$cargos->id,$cargos->cargo_operation_type]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;
                        <?php 
                        $delete =  route('deletecargo',[$cargos->id,$cargos->cargo_operation_type]);
                        $edit =  route('editcargo',[$cargos->id,$cargos->cargo_operation_type]);
                        ?>
                        <?php if($permissionCargoEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCargoDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="{{url('files/upload',['cargo',$cargos->id])}}" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($permissionCargoExpensesAdd) { 
                                    $countPending = 0;
                                    $countPending = App\Expense::getPendingExpenses($cargos->id);
                                        if($cargos->cargo_operation_type != 3) {
                                        ?>
                                    <li>
                                        <a href="{{ route('createexpenseusingawl',['cargo',$cargos->id,'flagFromListing']) }}">Add Expense</a>
                                    </li>
                                    <?php }
                                        } ?>
                                    <?php if($permissionCargoInvoicesAdd) {  ?>
                                        <?php if($cargos->cargo_operation_type != 3){?>
                                    <li>
                                        <a href="{{ route('createinvoice',$cargos->id) }}">Add Invoice</a>
                                    </li>
                                        <?php }?>
                                    <?php } ?>
                                    <?php if($cargos->consolidate_flag == 1 && ($cargos->cargo_operation_type == 1 || $cargos->cargo_operation_type == 2)) { ?>
                                    <li>
                                        <button id="btnAddWarehouseInFile" data-module ="Warehouse" class="btnModalPopup" value="<?php echo route('addwarehouseinfile',$cargos->id) ?>">Add Warehouse</button>
                                    </li>
                                    <?php } if($cargos->cargo_operation_type == 3) {?>
                                        <li>
                                            <a href="javascript:void(0)"  data-value="{{$cargos->id}}" class="sendmailonlocalfile">
                                            Send Invoice
                                            </a>

                                        </li>
                                            
                                    <?php }?>
                                    <?php if($cargos->cargo_operation_type != 3) {?>
                                    <li>
                                        <button id="btnAddCashCreditInFile" data-module ="Payment Mode" class="btnModalPopup" value="<?php echo route('addcashcreditinfile',$cargos->id) ?>">Add Payment Mode</button>
                                    </li>
                                    <?php }?>
                                    
                                </ul>
                        </div>
                    </td>
                    
                </tr>
                <?php $i++; ?>
                <?php $count++;?>
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
<div id="modalAddCashCreditWarehouseInFile" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title modal-title-block text-center primecolor">Add</h3>
            </div>
            <div class="modal-body" id="modalContentAddCashCreditWarehouseInFile" style="overflow: hidden;">
            </div>
        </div>

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
     var table = $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [ {
            "targets": [1,8],
            "orderable": false
            },{ type: 'date-uk', targets: 4 }],
        "scrollX": true,
         "order": [[ 0, "desc" ]],
         drawCallback: function(){
              $('.fg-button,.sorting,#example_length', this.api().table().container())          
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
                 $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
                });
           }
    });

    
$('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
   
   

    <?php if($flagCargo) { ?>
         var cargoI = '<?php echo $flagCargo; ?>';
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
    <?php } ?>

    $('#cargolisting').change(function(){
        $('#loading').show();
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
                         $('#loading').hide();
                    }
                });

    })

    $('#cargofiletype').change(function(){
        $('#loading').show();
         var flagFileType = $(this).val();
         var urlz = '<?php echo route("filterusingcargofiletype"); ?>'

        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });

         $.ajax({
                url:urlz,
                type:'POST',
                data:{'flagFileType':flagFileType},
                success:function(data) {
                        $('.cargocontainer').html(data);
                         $('#loading').hide();
                    }
                });

    })

    // Outside filtering
    //$('.to-date-filter').change(function(){
    $(document).delegate(".to-date-filter", "change", function(){
        $('#loading').show();
         $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
         var fromDate = $('.from-date-filter').val();
         var toDate = $(this).val();
         var urlz = '<?php echo route("shipmentoutsidefiltering"); ?>';
         $.ajax({
                url:urlz,
                type:'POST',
                data:{'fromDate':fromDate,'toDate':toDate},
                success:function(data) {
                        $('#loading').hide();
                        $('.cargocontainer').html(data);
                    }
                });

    })

     $(document).delegate('.expandpackage','click',function(){
        var rowId = $(this).data('rowid');
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
            /*$('.childrw').remove();
            $('.fa-minus').each(function(){
                $(this).removeClass('fa-minus');    
                $(this).addClass('fa-plus');
            })*/

            thiz.removeClass('fa-plus');
            thiz.addClass('fa-minus');
            var cargoid = $(this).data('cargoid');
            var rowId = $(this).data('rowid');
            var urlzte = '<?php echo route("expandhawbnumber"); ?>';
            $.ajax({
                url:urlzte,
                type:'POST',
                data: {cargoid:cargoid,rowId:rowId},
                success:function(data) {
                    
                    $(data).insertAfter(parentTR).slideDown();
                },
            });
            //$('#loading').hide();
        }else
        {
            thiz.removeClass('fa-minus');
            thiz.addClass('fa-plus');
            $('.child-'+rowId).remove();
            //parentTR.next('tr').remove();
            //$('#loading').hide();

        }
    })
     
     
    $(document).delegate(".sendmailonlocalfile", "click", function(){
        $('#loading').fadeIn();
        var cargoId = $(this).data("value");
        $.ajax({
           type:"GET",
           url:"{{url('invoices/send')}}",
           data:{cargoId:cargoId},
           success:function(res){
            $('#loading').hide();
            if(res == 'fail'){
                Lobibox.notify('alert', {
                size: 'mini',
                delay: 3000,
                rounded: true,
                delayIndicator: false,
                msg: 'Something went wrong.'
                });    
            }else{
                Lobibox.notify('info', {
                size: 'mini',
                delay: 3000,
                rounded: true,
                delayIndicator: false,
                msg: 'Invoice email has been sent successfully.'
                });    
            }
           }
        });
     });
 
             
     
})
</script>
@stop

