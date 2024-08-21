@extends('layouts.custom')

@section('title')
Invoice Listing
@stop

<?php 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.cashier-warehouse-cargo-invoice')
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
    <div id="flash"></div>
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
                        <th>Consingee</th>
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
                        @if($items->type_flag == 'Local')
                            <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" id="<?php echo $items->id; ?>" class="edit-row">
                        @else
                            <tr data-editlink="{{ route('editcashierwarehouseinvoicesoffile',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
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
                            <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php $edit =  route('editcashierwarehouseinvoicesoffile',$items->id);?>
                                    <a title="Click here to print"  target="_blank" href="{{ route('printinvoice',[$items->id,'cargo']) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;
                                    <?php
                                        if($permissionCargoInvoicesEdit) { 
                                            if($items->type_flag != 'Local'){
                                    ?>
                                                <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <?php } else { ?>
                                                <a href="javascript:void(0)" title="You can't edit this file."><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <?php 
                                            } 
                                        }
                                    ?>
                                    <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                    <ul class='dropdown-menu' style='left:auto;'>
                                        <li><a href="javascript:void(0)"  data-value="{{$items->id}}" class="sendmailonlocalfile">Send Mail</a></li>
                                        <?php 
                                        if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') { 
                                            if($permissionCargoInvoicesPaymentAdd) { 
                                                if($items->type_flag != 'Local'){
                                        ?>
                                        <li>
                                            <a href="{{ route('addcashierinvoicepayment',[$items->cargo_id,$items->id,0]) }}">Add Payment</a>
                                        </li>
                                        <?php   }
                                            } 
                                        } 
                                        ?>          
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
        $('#example').DataTable({
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
                    if(type == 'yes'){
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
                        if(status == 'Paid'){
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
                    else{}
                }
            })
        })
        $(document).delegate(".sendmailonlocalfile", "click", function(){
            $('#loading').fadeIn();
            var itemId = $(this).data("value");
            $.ajax({
                type:"GET",
                url:"{{url('cashierinvoices/send')}}",
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
                data:{'fromDate':fromDate,'toDate':toDate,'flag':'cashierCargoInvoice'},
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
                data:{'fromDate':fromDate,'toDate':toDate,'flag':'cashierCargoInvoice'},
                success:function(data) {
                    $('#loading').hide();
                    $('.invoicecontainer').html(data);
                }
            });
        })
    })
</script>
@stop

