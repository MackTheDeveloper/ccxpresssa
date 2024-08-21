@extends('layouts.custom')

@section('title')
House File Invoices
@stop

<?php 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesDelete = App\User::checkPermission(['delete_cargo_invoices'],'',auth()->user()->id);$permissionInvoicesPaymentAdd = 1;
    if($flagModule == 'cargo')
        $permissionInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    else if($flagModule == 'ups')
        $permissionInvoicesPaymentAdd = App\User::checkPermission(['add_courier_invoice_payments'],'',auth()->user()->id); 
    else if($flagModule == 'aeropost')
        $permissionInvoicesPaymentAdd = App\User::checkPermission(['add_aeropost_invoice_payments'],'',auth()->user()->id); 
    else if($flagModule == 'ccpack')
        $permissionInvoicesPaymentAdd = App\User::checkPermission(['add_ccpack_invoice_payments'],'',auth()->user()->id); 
    $permissionCargoInvoicesCopy = App\User::checkPermission(['copy_cargo_invoices'],'',auth()->user()->id); 
?>


<?php if(auth()->user()->department == 14) { ?>
@section('breadcrumbs')
    @include('menus.warehouse-cargo-invoice',['flagModule' => $flagModule])
@stop
<?php } else if(auth()->user()->department == 11) { ?>
@section('breadcrumbs')
    @include('menus.cashier-warehouse-cargo-invoice')
@stop
<?php } else { ?>
@section('breadcrumbs')
    @include('menus.cargo-invoice',['flagModule' => $flagModule])
@stop
<?php } ?>

<?php 
use App\Currency;
?>
@section('content')
<section class="content-header">
    <h1>House File Invoices</h1>
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
                <th>House File No.</th>
                <th>House AWB/BL No.</th>
                <th>Billing Party</th>
                <th>Consingee</th>
                <th>Currency</th>
                <th>Total Amount</th>
                <th>Paid Amount</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 0;?>
            @foreach ($invoices as $items)
            @if($items->type_flag == 'Local')
                <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @else 
                <tr data-editlink="{{ route('viewhousefileinvoicedetails',[$items->id,$flagModule]) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @endif
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td>{{$items->consignee_address}}</td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td><?php $dataUser = app('App\User')->getUserName($items->created_by); 
                            echo !empty($dataUser->name) ? $dataUser->name : "-";?></td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletehousefileinvoice',$items->id);
                        $edit =  route('edithousefileinvoice',[$items->id,$flagModule]);
                        ?>
                        
                        <a title="View & Print"  target="_blank" href="{{route('viewandprinthousefileinvoice',$items->id)}}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCargoInvoicesEdit) { 
                            if($items->type_flag != 'Local'){
                            ?>
                            <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php }
                            else { ?>
                            <a href="javascript:void(0)" title="You can't edit this file."><i class="fa fa-pencil" aria-hidden="true" data-toggle="popover" data-placement="bottom" data-content="Permission Denied"></i></a>&nbsp; &nbsp;
                        <?php
                            } 
                            }
                        ?>
                        <?php if($permissionCargoInvoicesDelete && checkloggedinuserdata() == 'Other') { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                         <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                         <ul class='dropdown-menu' style='left:auto;'>
                            <?php if($permissionCargoInvoicesCopy)
                                { ?>
                             <li>
                                    <li><a href="<?php echo route('copyhouseinvoice',[$items->id,'fromlisting',$flagModule]) ?>">Copy Invoice</a></li>
                             </li>
                            <?php } ?>
                            <?php 
                            if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') {
                                if($permissionInvoicesPaymentAdd) { 
                                    if($items->type_flag != 'Local'){
                                        if($flagModule == 'cargo')
                                        { ?>
                                        <li><a href="<?php echo route('addinvoicepayment',[$items->hawb_hbl_no,$items->id,0,'0','housefile']) ?>">Add Payment</a></li>
                                        <li><a href="<?php echo route('addinvoicepayment',[0,0,$items->bill_to]) ?>">Add Bulk Payment</a></li>
                                        <?php }
                                        else if($flagModule == 'ups')
                                        { ?>
                                            <li><a href="<?php echo route('addupsinvoicepayment',[$items->ups_id,$items->id,0]) ?>">Add Payment</a></li>
                                            <li><a href="<?php echo route('addupsinvoicepayment',[0,0,$items->bill_to]) ?>">Add Bulk Payment</a></li>
                                        <?php  }
                                        else if($flagModule == 'aeropost')
                                        { ?>
                                            <li><a href="<?php echo route('addaeropostinvoicepayment',[$items->aeropost_id,$items->id,0]) ?>">Add Payment</a></li>
                                            <li><a href="<?php echo route('addaeropostinvoicepayment',[0,0,$items->bill_to]) ?>">Add Bulk Payment</a></li>
                                        <?php  }
                                        else if($flagModule == 'ccpack')
                                        { ?>
                                            <li><a href="<?php echo route('addccpackinvoicepayment',[$items->ccpack_id,$items->id,0]) ?>">Add Payment</a></li>
                                            <li><a href="<?php echo route('addccpackinvoicepayment',[0,0,$items->bill_to]) ?>">Add Bulk Payment</a></li>
                                        <?php }
                                    }
                                }
                            }
                            ?>
                            <?php if($items->payment_status == 'Paid') { ?>
                            <li><a title="Print Receipt"  target="_blank" href="<?php echo route('printreceiptofinvoicepayment',[$items->id,'invoice',$flagModule == 'cargo' ? 'housefile' : $flagModule]) ?>">Payment Receipt</i></a>
                            </li>
                            <?php } ?>
                         </ul>
                        </div>
                        
                    </td>
                </tr>
                <?php $count++?>
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
   

   $(document).delegate(".sendmailonlocalfile", "click", function(){
        $('#loading').fadeIn();
        var itemId = $(this).data("value");
        console.log(itemId);
        $.ajax({
           type:"GET",
           url:"{{url('invoicesmail/send')}}",
           data:{itemId:itemId},
           success:function(res){               
             $('#loading').hide();
             Lobibox.notify('info', {
                size: 'mini',
                delay: 3000,
                rounded: true,
                delayIndicator: false,
                msg: 'Invoice email has been sent successfully.'
                });
           }
        });
     });

 $('[data-toggle="popover"]').popover(function(){

 });

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
                data:{'fromDate':fromDate,'toDate':toDate,'flag':'houseFileInvoice','flagModule':'<?php echo $flagModule; ?>'},
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
                data:{'fromDate':fromDate,'toDate':toDate,'flag':'houseFileInvoice'},
                success:function(data) {
                        $('#loading').hide();
                        $('.invoicecontainer').html(data);
                    }
                });
    })
} )
</script>
@stop

