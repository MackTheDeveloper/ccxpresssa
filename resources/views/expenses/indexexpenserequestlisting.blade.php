@extends('layouts.custom')

@section('title')
Expense Listing
@stop

@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        
        <li class="widemenu">
            <a href="{{ route('createexpenserequest','cargo') }}">Add Expense</a>
        </li>
        
    </ul>
</aside>
@stop
@section('content')
<section class="content-header">
    <h1>Expense Listing</h1>
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
            <table id="example" class="display" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th></th>
                <th>Date</th>
                <th>Voucher No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Total Amount ($)</th>
                <th>Expediteur / Shipper</th>
                <th>Consignataire / Consignee</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($cargoExpenseDataByVoucher as $items)
             <?php $dataExpenses = App\ExpenseDetails::checkExpenseuser($items->expense_id); 
                    $cls = '';
                    if($dataExpenses > 0)
                        $cls = 'fa fa-plus';

                    $dataCargo = App\Cargo::getCargoData($items->cargo_id); 
                ?>
                <tr>
                    <td style="display: none">{{$items->expense_id}}</td>
                    <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                    <td>{{$items->exp_date}}</td>
                    <td>{{$items->voucher_number}}</td>
                    <td><?php echo $dataCargo->file_number;  ?></td>
                    <td>{{$items->bl_awb}}</td>
                    <td><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                    <td>{{$items->shipper}}</td>
                    <td>{{$items->consignee}}</td>
                    <td><?php $btnClass = ($items->expense_request == 'Approved' || $items->expense_request == null) ? 'customButtonSuccess' : 'customButtonAlert'; ?><button style="margin-left: 5px" class="customButtonInGrid <?php echo $btnClass; ?>" data-expenseid="{{$items->expense_id}}" data-cargoid="{{$items->cargo_id}}" value="{{$items->expense_request}}"><?php echo $items->expense_request; ?></button></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteexpensevoucher',$items->expense_id);
                        $edit =  route('editexpenserequestvoucher',[$items->expense_id]);
                        ?>
                        
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        
                       
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
         "order": [[ 1, "desc" ]],
         drawCallback: function(){
              $('.paginate_button', this.api().table().container())          
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
           }
    });

   //$('.expandpackage').click(function(){
    $(document).delegate('.expandpackage','click',function(){
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
            $('.childrw').remove();
            $('.fa-minus').each(function(){
                $(this).removeClass('fa-minus');    
                $(this).addClass('fa-plus');
                //$(this).closest('tr').next('tr').remove();
            })

            thiz.removeClass('fa-plus');
            thiz.addClass('fa-minus');
            var expenseId = $(this).data('expenseid');
            var rowId = $(this).data('rowid');
            var urlzte = '<?php echo route("expandexpenses"); ?>';
            $.ajax({
                url:urlzte,
                type:'POST',
                data: {expenseId:expenseId,rowId:rowId,flaguser:'yes'},
                success:function(data) {
                    
                    $(data).insertAfter(parentTR).slideDown();
                },
            });
            //$('#loading').hide();
        }else
        {
            thiz.removeClass('fa-minus');
            thiz.addClass('fa-plus');
            $('.childrw').remove();
            //parentTR.next('tr').remove();
            //$('#loading').hide();

        }
    })

} )
</script>
@stop

