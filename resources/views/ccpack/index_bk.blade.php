@extends('layouts.custom')

@section('title')
CCpack Files Listing
@stop


@section('breadcrumbs')
    @include('menus.ccpack')
@stop
<?php
    $permissionUpdateCcpack = App\User::checkPermission(['update_ccpack'],'',auth()->user()->id);
    $permissionDeleteCcpack = App\User::checkPermission(['delete_ccpack'],'',auth()->user()->id);  
    $permissionCcpackAddInvoice = App\User::checkPermission(['add_ccpack_invoices'],'',auth()->user()->id); 
?>
<?php use App\Ups;?>
@section('content')
	<section class="content-header">
    	<h1>CCpack Files Listing</h1>
	</section>

	<section class="content">
		@if(Session::has('flash_message'))
    		<div class="alert alert-success flash-success">
        		{{ Session::get('flash_message') }}
    		</div>
    	@endif
    	<div class="box box-success">
        	<div class="box-body">
                <div class="row" style="margin-bottom: 2%">
                    <div class="col-md-3">
                        <?php echo Form::select('file_name',[0=>'All Files (I, E)',1=>'Import',2=>'Export'],0,['class'=>'form-control selectpicker','data-live-search' => 'true','id'=>'ccpacklisting']); ?>
                    </div>
                </div>
                <div class="ccpack_container">
                    <table id="example" class="display nowrap" style="width:100%">
                        <thead>
                            
                            <tr>
                                <th>File Number</th>
                                <th>Arrival Date</th>
                                <th>Awb Number</th>
                                <th>Consignee Name</th>
                                <th>Shipper Name</th>
                                <th>No. Of Pcs</th>
                                <th>Weight</th>
                                <th>Freight</th>
                                <th>Action</th>
                            </tr>
                           
                        </thead>
                        <tbody>
                            @foreach($ccpackData as $data)
                            <tr data-editlink="{{ route('viewdetailsccpack',$data->id) }}" id="<?php echo $data->id; ?>" class="edit-row">
                                <td>{{$data->file_number}}</td>
                                <td><?php echo date('d-m-Y',strtotime($data->arrival_date))?></td>
                                <td>{{$data->awb_number}}</td>
                                <td><?php echo Ups::getConsigneeName($data->consignee)?></td>
                                <td><?php echo Ups::getConsigneeName($data->shipper_name)?></td>
                                <td>{{$data->no_of_pcs}}</td>
                                <td>{{$data->weight.' '.'KGS'}}</td>
                                <td>{{$data->freight}}</td>
                                <?php 
                                    $delete =  route('deleteccpack',$data->id);
                                    $edit =  route('editccpack',$data->id);
                                ?>
                                <td>
                                    <div class='dropdown'>
                                        <?php if($permissionUpdateCcpack) { ?>
                                            <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                        <?php }?>
                                        <?php if($permissionDeleteCcpack) { ?>
                                            <a href="<?php echo $delete ?>" title="Delete" style = "margin-left : 10%" class = "delete-record"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                        <?php }?>
                                        <a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="{{url('files/upload',['ccpack',$data->id])}}" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>
                                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                            <ul class='dropdown-menu' style='left:auto;'>
                                                
                                                   <?php if($permissionCcpackAddInvoice) { ?>
                                                    <li>
                                                        <a href="{{ route('createccpackinvoices',$data->id) }}">Add Invoice</a>
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
        var table = $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [{
            "targets": [3],
            "orderable": true
        },{
            "targets": [-1],
            "orderable": false
            },{ type: 'date-uk', targets: 1 }],
        "scrollX": true,
        
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
                // $('.expandpackage').each(function(){
                //     if($(this).hasClass('fa-minus'))
                //     {
                //         $(this).removeClass('fa-minus');    
                //         $(this).addClass('fa-plus');
                //     }
                // })
        });
        $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
        });
      },
     
  });

        $('#ccpacklisting').change(function(){
            $('#loading').show();
               var ccpackId = $(this).val();
              
               var urlz = '<?php echo route("ccpackfilter"); ?>'
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

               $.ajax({
                    url:urlz,
                    type:'POST',
                    data:{'ccpackId':ccpackId},
                    success:function(data) {
                            $('.ccpack_container').html(data);
                             $('#loading').hide();
                             //alert(data);
                        }
                });
           })
    </script>
@endsection
