@extends('layouts.custom')
@section('title')
UPS Files Listing
@stop

<?php 
$permissionCourierImportEdit = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
$permissionCourierImportDelete = App\User::checkPermission(['delete_courier_import'],'',auth()->user()->id); 
$permissionCourierAddExpense = App\User::checkPermission(['add_courier_expenses'],'',auth()->user()->id); 
$permissionCourierAddInvoice = App\User::checkPermission(['add_courier_invoices'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
@include('menus.ups-import')
@stop

@section('content')
<section class="content-header">
    <h1>UPS Files Listing</h1>
</section>

<section class="content">


    @if(Session::has('flash_message_import'))
    <div class="alert alert-success-custom flash-success">
        <span><?php echo Session::get('flash_message_import')['totalUploaded']; ?></span><br/>
        <span><?php echo Session::get('flash_message_import')['totalAdded']; ?></span><br/>
        <span><?php echo Session::get('flash_message_import')['totalUpdated']; ?></span><br/><br/>
        <span><a href="{{route('viewlogfiles')}}">View Log Files</a></span>
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



            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            
            <div class="col-md-12" style="display: none">
               <div class="col-md-6">
                <div class="form-group">
                    <?php echo Form::label('date_range', 'Search By Date',['class'=>'col-md-4 control-label']); ?>
                    <div class="col-md-8">
                        <?php echo Form::text('date_range','',['class'=>'form-control date_range','placeholder' => 'Enter Date']); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
            <div class="form-group">
                <?php echo Form::label('file_name', 'Search By File Name',['class'=>'col-md-4 control-label']); ?>
                <div class="col-md-6">
                    <?php echo Form::select('file_name', $upsFileNames,'',['class'=>'form-control selectpicker ffile_name','data-live-search' => 'true','placeholder' => 'All Files']); ?>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>

            </div>
        

        </div>
       

    {{ Form::close() }}

    <div class="row" style="margin-bottom: 2%">
        <div class="col-md-3">
            <?php echo Form::select('file_name',[0=>'All Files (I, E)',1=>'Import',2=>'Export'],0,['class'=>'form-control selectpicker','data-live-search' => 'true','id'=>'upslisting']); ?>
        </div>
        <div class="col-md-3">
            <?php echo Form::select('scan',Config::get('app.scanArr'),2,['class'=>'form-control selectpicker','data-live-search' => 'true','id'=>'upsscan']); ?>
        </div>
    </div>
    <div class="container-rep courier_container">

        
            <table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th></th>
                        <th>File Number</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
                        <th>Date</th>
                        <th>AWB Tracking</th>
                        <th>Destination</th>
                        <th>Origin</th>
                        <th>Weight</th>
                        <th>Billing Term</th>
                        <th>Commission Received</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1; ?>
                    @foreach ($upsData as $couriers)
                    <?php $dataPackage = App\Ups::checkPakckages($couriers->id); 
                    $cls = '';
                    if($dataPackage > 0)
                        $cls = 'expandpackage fa fa-plus';

                    $assignedCss = '';
                    $fcCss = '';
                    $checkFileAssigned = App\Ups::checkFileAssgned($couriers->id);
                    if($checkFileAssigned == 'no')
                        $assignedCss = 'color:#3097D1';

                    if($couriers->fc == 1)
                        $fcCss = 'color:#fb7400';
                    ?>
                    <tr style="<?php echo $assignedCss.';'.$fcCss; ?>" data-editlink="{{ route('viewdetailsups',$couriers->id) }}" id="<?php echo $couriers->id; ?>" class="edit-row">
                        <td style="display: none">{{$couriers->id}}</td>
                        <td style="display: block;text-align: center;padding-top: 15px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-upsid="<?php echo $couriers->id; ?>"></td>
                        <td>{{$couriers->file_number}}</td>
                        <td><?php $data = app('App\Clients')->getClientData($couriers->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                        <td><?php $data = app('App\Clients')->getClientData($couriers->shipper_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                        <td><?php echo $couriers->courier_operation_type == 1 ? (!empty($couriers->arrival_date) ? date('d-m-Y',strtotime($couriers->arrival_date)) : '-') : (!empty($couriers->tdate) ? date('d-m-Y',strtotime($couriers->tdate)) : '-') ?></td>
                        <td>{{$couriers->awb_number}}</td>
                        <td>{{$couriers->destination}}</td>
                        <td>{{$couriers->origin}}</td>
                        <td><?php echo !empty($couriers->weight) ? $couriers->weight.' '.$couriers->unit : '-';?>
                        </td>
                        <td><?php echo App\Ups::getBillingTerm($couriers->id); ?></td>
                        <td><?php echo $couriers->commission_amount_approve == 'Y' ? 'Yes' : 'No';?></td>
                        <td>
                            <div class='dropdown'>
                                <?php 
                                $delete =  route('deleteups',[$couriers->id,'']);
                                $edit =  route('editups',[$couriers->id,$couriers->courier_operation_type]);
                                ?>

                                <?php if($permissionCourierImportEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <?php } ?>
                                    <?php if($permissionCourierImportDelete) { ?>
                                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>

                                        <?php } ?>
                                        <a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="{{url('files/upload',['ups',$couriers->id])}}" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>
                                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                        <ul class='dropdown-menu' style='left:auto;'>
                                            <?php if($permissionCourierAddExpense) { ?>
                                                <li>
                                                   <a href="{{ route('createupsexpense',$couriers->id) }}">Add File Expense</a>
                                               </li>
                                               <?php } ?>
                                               <?php if($permissionCourierAddInvoice) { ?>
                                                <li>
                                                    <a href="{{ route('createupsinvoice',$couriers->id) }}">Add Invoice</a>
                                                </li>
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
        $(document).ready(function() {

            $('.date_range').daterangepicker();

    //$('.date_range').change(function(){
   //$('.date_range').on('apply.daterangepicker', function(ev, picker) {     
    $('.date_range').on('apply.daterangepicker', function(ev, picker) {
        $('#loading').show();
        var startDate = picker.startDate.format('YYYY-MM-DD');
        var endDate = picker.endDate.format('YYYY-MM-DD');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:'ups/filderbydaterange',
            type:'POST',
            data: {startDate:startDate,endDate:endDate},
            success:function(data) {
                $('.container-rep').html(data);
                $('#loading').hide();
            },
        });
    })

    $('.date_range').on('cancel.daterangepicker', function(ev, picker) {
      $('#loading').show();
      $('.date_range').val('');
      var startDate = picker.startDate.format('YYYY-MM-DD');
      var endDate = picker.endDate.format('YYYY-MM-DD');
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
      $.ajax({
        url:'ups/fildergetalldata',
        type:'POST',
        data: {startDate:startDate,endDate:endDate},
        success:function(data) {
            $('.container-rep').html(data);
            $('#loading').hide();
        },
    });
  });
    
    var table = $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [{
            "targets": [1,10],
            "orderable": false
        },{ type: 'date-uk', targets: 5 }],
        "scrollX": true,
        "order": [[ 0, "desc" ]],
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
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
      },
     
  });

    
    $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    // Apply the search


    //$('.expandpackage').click(function(){
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
            $('.child-'+rowId).remove();
            //parentTR.next('tr').remove();
            //$('#loading').hide();

        }
    })

        $('#createInvoiceForm').on('submit', function (event) {
            $('#loading').show();
        });
        $('#createInvoiceForm').validate({
            submitHandler : function(form) {
             var fileName = $('#file_name').val(); 
             $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
             var urlztnn = '<?php echo url("ups/filderbyfilename"); ?>';
             $.ajax({
                url:urlztnn,
                async:false,
                type:'POST',
                data:{'fileName':fileName},
                success:function(data) {
                    $('.container-rep').html(data);
                    $('#loading').hide();
                }
            });
         },
         errorPlacement: function(error, element) {
            if (element.attr("name") == "file_name" )
            {
                var pos = $('.ffile_name button.dropdown-toggle');
                error.insertAfter(pos);
            }
            else
            {
                error.insertAfter(element);
            }
            $('#loading').hide();
        }
    });

        $('#upslisting').change(function(){
            $('#loading').show();
               var upsId = $(this).val();
              // alert(upsId);
               var urlz = '<?php echo route("upsfilter"); ?>'
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

               $.ajax({
                    url:urlz,
                    type:'POST',
                    data:{'upsId':upsId},
                    success:function(data) {
                            $('.courier_container').html(data);
                             $('#loading').hide();
                             //alert(data);
                        }
                });
           });

        $('#upsscan').change(function(){
            $('#loading').show();
               var upsId = $(this).val();
              // alert(upsId);
               var urlz = '<?php echo route("upsscanfilter"); ?>'
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

               $.ajax({
                    url:urlz,
                    type:'POST',
                    data:{'upsId':upsId},
                    success:function(data) {
                        //alert(data);
                             $('.courier_container').html(data);
                             $('#loading').hide();
                             //alert(data);
                        }
                });
           })

         
    })
</script>
@stop

