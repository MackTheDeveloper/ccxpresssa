@extends('layouts.custom')

@section('title')
Cargo Invoices
@stop

<?php 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesDelete = App\User::checkPermission(['delete_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    $permissionCargoInvoicesCopy = App\User::checkPermission(['copy_cargo_invoices'],'',auth()->user()->id); 
    $permissionInvoicePaymentDelete = App\User::checkPermission(['delete_cargo_invoice_payments'],'',auth()->user()->id);
?>

@section('breadcrumbs')
    @include('menus.reports')
@stop
<?php 
use App\Currency;
?>
@section('content')
<section>
  <div class="row">
    <div class="col-md-3 content-header" style="margin-left: 1%">
      <h1>Invoice Payment Report</h1>
    </div>
   
</div>

</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="modal fade" id="successModal" role="dialog">
            <div class="modal-dialog modal-sm" style="width: 350px">
                <div class="modal-content">
                         <!--   <div class="modal-header" style="background-color: #00a75f">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: white">&times;</button>
                                </div> -->
                    <div class="modal-body">
                        <center>
                            <h1 class="modal-title" id="mainLabel">Success!</h1> 
                            <h4 class="modal-title" id="secondaryLabel">Mail has been sent succesfully.</h4>
                        </center>                
                    </div>
                    <div class="modal-footer">
                        <center> 
                            <button type="button" class="btn btn-lg" data-dismiss="modal" style="background-color: #00a75f;color: white;width: 100px" id="resBtn">Ok</button>
                         </center>
                    </div>    
                </div>
            </div>
        </div>
        <div class="box-body">
                <div class="col-md-8" style="margin-bottom:20px">
                        <div class="row">
                            {!! Form::open(['url'=>'invoice/searchByDate']) !!}
                            
                            <div class="col-md-3"> 
                              {{Form::text('date','',['class'=>'form-control','placeholder'=>'Search By Date','style'=>'width:200px','id'=>'searchDate'])}}</div>
                            <div class="col-md-1"> 
                              {{Form::submit('Search',['class'=>'btn btn-primary','id'=>'searchDateBtn'])}}
                            </div>
                        </div>        
                        {!! Form::close() !!}
                    </div>
                    <div id="allData">
            <table id="example1" class="display nowrap" style="width:100%" data-linker = "route()">

        <thead>

            <tr>
                <th style="display: none">ID</th>
                <th>Date</th>
                <th>Invoice No.</th>
                <th>File No.</th>
                <th>Awb_no</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Action</th>
                
            </tr>
        </thead>
        <tbody>
            <?php $count = 0;?>
            @foreach ($invoices as $items)
             @if($items->type_flag == "Local")
                <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" id="<?php echo $items->id; ?>" class="edit-row">
             @else
              <tr data-editlink="{{ route('ViewDetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
             @endif
                
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{'#'.$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    
                    <td><?php $dataUser = app('App\User')->getUserName($items->created_by); 
                            echo !empty($dataUser->name) ? $dataUser->name : "-";?></td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    <td><div class='dropdown'><?php if($permissionInvoicePaymentDelete) { ?>
                        <a class="delete-record" href="{{route('deleteinvoicepayment',[$items->id])}}" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                    <?php } ?></div></td>
                </tr>
                <?php $count++?>
            @endforeach
            
        </tbody>
        
    </table>
                    </div>
        </div>
    </div>
    <div id="modalAddNewItems" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                         <div class="modal-header" style="background-color: #00a75f">
                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                            <center><h3 style="color: white">Details</h3></center>
                                        </div>
                                        <div class="modal-body" id="modalContentAddNewItems" >
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
    $('table').DataTable(
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
           url:"{{url('invoicesmail/send')}}",
           data:{itemId:itemId},
           success:function(res){               
            if(res){
                $('#loading').fadeOut(function(){$('#successModal').modal('show');});
            } else {
                $('#flash').html(res);
            }
           }
        });
     });
}

$('#searchDate').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    

    $('#searchDateBtn').click(function(event){
        $('#loading').show();
        event.preventDefault();
        var date = $('#searchDate').val();
         $.ajax({
           type:"GET",
           url:"{{url('invoice/searchByDate')}}",
           data:{date:date},
           success:function(res){ 
           $('#allData').html(res);  
           $('#loading').hide();
           // console.log(res.length);            
           //  if(res.length > 0){
           //      $('#example').css('display','none');
           //  var res = JSON.parse(res);
           //  var disData = ` <table id="example" class="display nowrap" style="width:100%">
           //                      <thead>
           //                          <tr>
           //                              <th>Date</th>
           //                              <th>Invoice No.</th>
           //                              <th>File No.</th>
           //                              <th>AWB / BL No.</th>
           //                              <th>Billing Party</th>
           //                              <th>Currency</th>
           //                              <th>Total Amount</th>
           //                              <th>Paid Amount</th>
           //                              <th>Created By</th>
           //                              <th>Status</th>
           //                          </tr>
           //                      </thead>
           //                      <tbody>`;
           //  res.forEach(element => {
           //          disData += `<tr>
           //                      <td>`+element.date+`</td>
           //                      <td>`+element.bill_no+`</td>
           //                      <td>`+element.file_no+`</td>
           //                      <td>`+element.awb_no+`</td>
           //                      <td>`+element.bill_to+`</td>
           //                      <td>`+element.currency+`</td>
           //                      <td>`+element.total+`</td>
           //                      <td>`+element.credits+`</td>
           //                      <td>`+element.created_by+`</td>
           //                      <td>`+element.payment_status+`</td>
           //                  </tr>`
           //      });
           //      disData += `</tbody>
           //              </table>`;
           //              $('#allData').html(disData);
                    //}
           }
        });
    });

} )
</script>
@stop

