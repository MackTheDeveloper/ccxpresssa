@extends('layouts.custom')

@section('title')
Cargo File Expense Listing
@stop

@section('breadcrumbs')
    @include('menus.cashier-cargo-expense')
@stop

@section('content')
<section class="content-header">
    <h1>Cargo File Expense Listing</h1>
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
            <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th><div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div></th>
                <th>Date</th>
                <th>Voucher No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Cash/Bank</th>
                <th>Description</th>
                <th>Consignataire / Consignee</th>
                <th>Currency</th>
                <th>Total Amount</th>
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
                        $cls = 'fa fa-plus';

                    $dataCargo = App\Cargo::getCargoData($items->cargo_id); 
                ?>
                <tr data-editlink="{{ route('getprintviewsingleexpensecashier',[$items->expense_id,$items->cargo_id,'fromNotification']) }}" id="<?php echo $items->expense_id; ?>" class="edit-row">
                    <td style="display: none">{{$items->expense_id}}</td>
                    <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                    <td><?php echo date('d-m-Y',strtotime($items->exp_date)) ?></td>
                    <td>{{$items->voucher_number}}</td>
                    <td><?php echo !empty($dataCargo) ? $dataCargo->file_number : '-';  ?></td>
                    <td>{{$items->bl_awb}}</td>
                    <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account); echo !empty($currencyData) ? '('.$currencyData->currency_code.')'.' '.$currencyData->name : '-'; ?></td>
                    <td><?php echo $items->note != '' ? $items->note : '-'; ?></td>
                    <td>{{$items->consignee}}</td>
                    <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id); echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                    <td><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                    <td>{{$items->expense_request}}</td>
                    <td>
                        <div class='dropdown'>
                        
                        
                        <a title="Click here to print"  target="_blank" href="{{ route('getprintsingleexpense',[$items->expense_id,$items->cargo_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;
                        
                        
                        
                       
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
    $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [ {
            "targets": [1,-1],
            "orderable": false
            }],
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
                    if($('.fa-expand-collapse-all').hasClass('fa-minus'))
                    {
                        $('.fa-expand-collapse-all').removeClass('fa-minus');    
                        $('.fa-expand-collapse-all').addClass('fa-plus');
                    }
                 });   
                 $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    if($('.fa-expand-collapse-all').hasClass('fa-minus'))
                    {
                        $('.fa-expand-collapse-all').removeClass('fa-minus');    
                        $('.fa-expand-collapse-all').addClass('fa-plus');
                    }
                    setTimeout(function() { $("#loading").hide(); }, 200);
                });    
           }
    });

    $(document).delegate('.fa-expand-collapse-all','click',function(){
        $('#loading').show();
        if($(this).hasClass('fa-plus'))
        {
            $(this).removeClass('fa-plus');
            $(this).addClass('fa-minus');
        }else{
            $(this).removeClass('fa-minus');
            $(this).addClass('fa-plus');
        }
        $('.expandpackage').trigger('click');
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
            /* $('.childrw').remove();
            $('.fa-minus').each(function(){
                $(this).removeClass('fa-minus');    
                $(this).addClass('fa-plus');
                //$(this).closest('tr').next('tr').remove();
            }) */

            thiz.removeClass('fa-plus');
            thiz.addClass('fa-minus');
            var expenseId = $(this).data('expenseid');
            var rowId = $(this).data('rowid');
            var urlzte = '<?php echo route("expandexpensescashier"); ?>';
            $.ajax({
                url:urlzte,
                type:'POST',
                data: {expenseId:expenseId,rowId:rowId},
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

