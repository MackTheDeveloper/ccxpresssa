@extends('layouts.custom')
@section('title')
Cargo Files Listing
@stop

<?php 
        $permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'],'',auth()->user()->id); 
        $permissionCargoEdit = App\User::checkPermission(['update_cargo'],'',auth()->user()->id); 
        $permissionCargoDelete = App\User::checkPermission(['delete_cargo'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.cargo-files')
@stop



@section('content')
<section class="content-header">
    <h1>Cargo File Listing</h1>
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
    <div class="box box-success cargocontainer">
        <div class="box-body">
            <select id="cargolisting" class="form-control">
                    <option selected="" value="4">All Files</option>
                    <option value="1">Import Files</option>
                    <option value="2">Export Files</option>
            </select>

        <div class="out-filter-secion col-md-4">

        <div class="from-date-filter-div filterout col-md-6">
            <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter" value="<?php echo !empty(Session::get('cargoListingFromDate')) ?  Session::get('cargoListingFromDate') : '';?>">
        </div>
        <div class="to-date-filter-div filterout col-md-6">
            <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter" value="<?php echo !empty(Session::get('cargoListingToDate')) ?  Session::get('cargoListingToDate') : '';?>">
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
                <th>Shipper</th>
                <th>Invoice Numbers</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($dataCashierCargo as $cargos)
            <?php $dataTotal = App\HawbFiles::checkHawbFiles($cargos->id); 
                    $cls = '';
                    if($dataTotal > 0)
                        $cls = 'expandpackage fa fa-plus';
                    ?>
                
                     @if($cargos->cargo_operation_type == 3)
                        <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$cargos->id) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                    @else 
                        <tr data-editlink="{{ route('viewcargodetailforcashier',$cargos->id) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                    @endif
                    <td style="display: none">{{$cargos->id}}</td>
                    <td style="display: block;text-align: center;padding-top: 13px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-cargoid="<?php echo $cargos->id; ?>"></td>
                    <td>{{$cargos->file_number}}</td>
                    <td><?php $data = app('App\User')->getUserName($cargos->agent_id); echo !empty($data->name) ? $data->name : '-'; ?></td>
                    <td><?php echo date('d-m-Y',strtotime($cargos->opening_date)) ?></td>
                    <td><?php echo !empty($cargos->awb_bl_no) ? $cargos->awb_bl_no : '-'; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($cargos->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php $data = app('App\Clients')->getClientData($cargos->shipper_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                    <td><?php echo App\Expense::getInvoicesOfFile($cargos->id);  ?></td>
                    <td>
                        <div class='dropdown'>
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
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    
                                    <?php if($permissionCargoInvoicesAdd) { ?>
                                        <?php if($cargos->cargo_operation_type != 3){?>
                                    <li>
                                        <a href="{{ route('createcashierwarehouseinvoicesoffile',$cargos->id) }}">Add Invoice</a>
                                    </li>
                                    <?php }?>
                                    <?php } ?>
                                    <li>
                                        <a target="_blank" href="{{ route('releasereceiptbycashier',[$cargos->id,$cargos->cargo_operation_type]) }}">Release Receipt</a>
                                    </li>
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



</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    
     var table = $('#example').DataTable({
        'stateSave': true,
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        "columnDefs": [ {
            "targets": [1,-1],
            "orderable": false
            }],
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
   
   

   

    $('#cargolisting').change(function(){
        $('#loading').show();
         var cargoI = $(this).val();
        if(cargoI == 1)
            //window.location = 'cargoimports';
            var urlz = '<?php echo route("cashiercargoimportsajax"); ?>'
        else if(cargoI == 2)
            //window.location = 'cargoexports';
            var urlz = '<?php echo route("cashiercargoexportsajax"); ?>'
        else
            var urlz = '<?php echo route("cashiercargoallajax"); ?>'

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

    if($('.to-date-filter').val() != '')
    {
        $('#loading').show();
         $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
         var fromDate = $('.from-date-filter').val();
         var toDate =  $('.to-date-filter').val();
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
    }
    
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
                data: {cargoid:cargoid,rowId:rowId,flagcargo:'cashiercargo'},
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

     //$('.customButtonInGrid').click(function(){
    $(document).delegate(".customButtonInGrid", "click", function(){
                var status = $(this).val();
                var hawbId = $(this).data('hawbid');
                var flag = $(this).data('flag');
                var thiz = $(this);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                Lobibox.confirm({
                    msg: "Are you sure to change status?",
                    callback: function (lobibox, type) {

                                 if(type == 'yes')
                                   {
                                         $.ajax({
                                            type    : 'post',
                                            url     : '<?php echo url('cargo/verificationinspection'); ?>',
                                            data    : {'status':status,'hawbId':hawbId,'flag':flag},
                                            success : function (response) {
                                                    thiz.val(status);
                                                }
                                            });
                                            if(status == 1)
                                            {
                                            thiz.val('0');    
                                            thiz.text('Pending');
                                            thiz.removeClass('customButtonSuccess');
                                            thiz.addClass('customButtonAlert');
                                            }
                                            else
                                            {
                                            thiz.val('1');    
                                            thiz.text('Done');    
                                            thiz.removeClass('customButtonAlert');
                                            thiz.addClass('customButtonSuccess');
                                            }
                                            Lobibox.notify('info', {
                                                size: 'mini',
                                                delay: 2000,
                                                rounded: true,
                                                delayIndicator: false,
                                                msg: 'Status has been updated successfully.'
                                            });
                                   }
                                  else
                                    {}    
                        }
                })
             })
})
</script>
@stop

