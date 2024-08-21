@extends('layouts.custom')

@section('title')
Invoice Listing
@stop

<?php 
        $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.warehouse-cargo-invoice')
@stop
<?php 
use App\Currency;
?>

@section('content')
<section class="content-header">
    <h1>Invoice Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success invoicecontainer">
        <div class="box-body">
                <div class="out-filter-secion col-md-6">
                        <div class="from-date-filter-div filterout col-md-5">
                                <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                            </div>
                            <div class="to-date-filter-div filterout col-md-5">
                                <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                            </div>
                            <div class="col-md-2">
                                    <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                            </div>
                    </div>
            <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Date</th>
                <th>Invoice No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Billing Party</th>
                <th>Currency</th>
                <th>Total Amount</th>
                <th>Paid Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 0;?>
            @foreach ($invoices as $items)
                <tr data-editlink="{{ route('editwarehouseinvoice',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $edit =  route('editwarehouseinvoice',$items->id);
                        ?>
                        
                        <a title="Click here to print"  target="_blank" href="../public/cargoInvoices/<?php echo 'printCargoInvoice_'.$items->id.'.pdf';?>"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCargoInvoicesEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                        <ul class='dropdown-menu' style='left:auto;'>
                            
                            <li><a href="{{ route('warehouseinvoicesmail') }}" id="sendMail{{$count}}" value="{{$items->id}}">Send Mail</a></li>
                        </ul>
                        
                        
                        </div>
                        
                    </td>
                </tr>
                <?php $count++;?>
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
    $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
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
            },{ type: 'date-uk', targets: 1 }],
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

    $('.customButtonInGridinvoicestatus').click(function(){
            var status = $(this).val();
            var invoiceId = $(this).data('invoiceid');
            var cargoId = $(this).data('cargoid');
            var thiz = $(this);
            $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });
            Lobibox.confirm({
            msg: "Are you sure to change status of payment?",
            callback: function (lobibox, type) {
            if(type == 'yes')
            {
                var urlz = '<?php echo route("changeinvoicestatus"); ?>';
                $.ajax({
                type    : 'post',
                url     : urlz,
                async : false,
                data    : {'status':status,'invoiceId':invoiceId},
                success : function (response) {
                thiz.val(status);
                }
                });
                if(status == 'Paid')
                {
                thiz.val('Pending');
                thiz.text('Pending');
                thiz.removeClass('customButtonSuccess');
                thiz.addClass('customButtonAlert');
                }
                else
                {
                thiz.val('Paid');
                thiz.text('Paid');
                thiz.removeClass('customButtonAlert');
                thiz.addClass('customButtonSuccess');
                }
                Lobibox.notify('info', {
                size: 'mini',
                delay: 2000,
                rounded: true,
                delayIndicator: false,
                msg: 'Payment Status has been updated successfully.'
                });
            }
            else
            {}
            }
            })
    })

    var count = <?php echo $count;?>;
   for(i=0;i<count;i++){
   $('#sendMail'+i).click(function(event){
        event.preventDefault();
        $('#loading').fadeIn();
        var itemId = $(this).attr("value");
        console.log(itemId);
        $.ajax({
           type:"GET",
           url:"{{url('cashierinvoices/send')}}",
           data:{itemId:itemId},
           success:function(res){               
            $('#loading').hide();
             Lobibox.notify('info', {
                size: 'mini',
                delay: 2000,
                rounded: true,
                delayIndicator: false,
                msg: 'Invoice email has been sent successfully.'
                });
           }
        });
     });
    }

    $(document).delegate(".to-date-filter", "change", function(){
        $('#loading').show();
         $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
         var fromDate = $('.from-date-filter').val();
         var toDate = $(this).val();
         var urlz = '<?php echo route("invoiceoutsidefiltering"); ?>';
         $.ajax({
                url:urlz,
                type:'POST',
                data:{'fromDate':fromDate,'toDate':toDate,'flag':'warehouseCargoInvoice'},
                success:function(data) {
                        $('#loading').hide();
                        $('.invoicecontainer').html(data);
                    }
                });
    })

    $(document).delegate(".allrecores", "click", function(){
        $('#loading').show();
         $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                    });
         $('.from-date-filter').val('');
         $('.to-date-filter').val('');
         var fromDate = $('.from-date-filter').val();
         var toDate = $('.to-date-filter').val();
         var urlz = '<?php echo route("invoiceoutsidefiltering"); ?>';
         $.ajax({
                url:urlz,
                type:'POST',
                data:{'fromDate':fromDate,'toDate':toDate,'flag':'warehouseCargoInvoice'},
                success:function(data) {
                        $('#loading').hide();
                        $('.invoicecontainer').html(data);
                    }
                });
    })
   

} )
</script>
@stop

