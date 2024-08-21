@extends('layouts.custom')

@section('title')
UPS File Expense Listing
@stop
<?php 
    $permissionCourierExpensesEdit = App\User::checkPermission(['update_courier_expenses'],'',auth()->user()->id); 
    $permissionCourierExpensesDelete = App\User::checkPermission(['delete_courier_expenses'],'',auth()->user()->id); 
?>


@section('breadcrumbs')
    @include('menus.ups-expense')
@stop

@section('content')
<section class="content-header">
    <h1>UPS File Expense Listing</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <div style="float: right;width: 200px;margin: 0px;height: 35px;position: absolute;left: 70%;z-index: 111;top: 18px;">
                <a title="Click here to print"  target="_blank" href="{{ route('getprintallupsexpense') }}"><i class="fa fa-print btn btn-primary"></i></a>
            </div>
            <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th></th>
                <th>Date</th>
                <th>Voucher No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Invoices</th>
                <th>Total Amount</th>
                <th>Cash/Bank</th>
                <th>Expediteur / Shipper</th>
                <th>Consignataire / Consignee</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($cargoExpenseDataByVoucher as $items)
             <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id); 
                    $cls = '';
                    if($dataExpenses > 0)
                        $cls = 'expandpackage fa fa-plus';

                    $dataUps = App\Ups::getUpsData($items->ups_details_id); 
                ?>
                <tr data-editlink="{{ route('viewdetailsupsexpense',$items->expense_id) }}" id="<?php echo $items->expense_id; ?>" class="edit-row">
                    <td style="display: none">{{$items->expense_id}}</td>
                    <td style="display: block;text-align: center;padding-top: 13px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                    <td><?php echo date('d-m-Y',strtotime($items->exp_date)) ?></td>
                    <td>{{$items->voucher_number}}</td>
                    <td><?php echo $dataUps->file_number;  ?></td>
                    <td>{{$items->bl_awb}}</td>
                    <td><?php echo App\Expense::getUpsInvoicesOfFile($items->ups_details_id);  ?></td>
                    <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                    <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account); echo !empty($currencyData) ? '('.$currencyData->currency_code.')'.' '.$currencyData->name : '-'; ?></td>
                    <td>{{$items->shipper}}</td>
                    <td>{{$items->consignee}}</td>
                    <td>{{$items->expense_request}}</td>
                    
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteexpensevoucher',$items->expense_id);
                        if($items->request_by_role == 12)
                            $edit =  route('editagentupsexpensesbyadmin',[$items->expense_id,'flagFromExpenseListing']);
                        else    
                            $edit =  route('editupsexpense',$items->expense_id);
                        
                        ?>
                        <?php 
                        //echo App\Expense::getPrints($items->expense_id,$items->cargo_id); 
                        /*$urlAction = url("/expense/getprintsingleexpense/$items->expense_id/$items->cargo_id");
                        $backgroundActions = new App\User;
                        $backgroundActions->backgroundPost($urlAction);*/
                        ?>
                        <a title="Click here to print"  target="_blank" href="{{ route('getprintsingleupsexpense',[$items->expense_id,$items->ups_details_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;
                        <?php if($permissionCourierExpensesEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCourierExpensesDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        
                       
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
    $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [ {
            "targets": [1,11],
            "orderable": false
            },{ type: 'date-uk', targets: 2 }],
         "order": [[ 0, "desc" ]],
         "scrollX": true,
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
            var expenseId = $(this).data('expenseid');
            var rowId = $(this).data('rowid');
            var urlzte = '<?php echo route("expandexpenses"); ?>';
            $.ajax({
                url:urlzte,
                type:'POST',
                data: {expenseId:expenseId,rowId:rowId,'flagW':'Ups'},
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

   

} )
</script>
@stop

