@extends('layouts.custom')

@section('title')
Custom Expenses
@stop

<?php 
    $permissionCourierCustomExpenseEdit = App\User::checkPermission(['update_courier_custom_expenses'],'',auth()->user()->id); 
    $permissionCourierCustomExpenseDelete = App\User::checkPermission(['delete_courier_custom_expenses'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.ups-expense')
@stop

@section('content')
<section class="content-header">
    <h1>Custom Expenses</h1>
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
                <th></th>
                <th>Date</th>
                <th>Voucher No.</th>
                <th>AWB/File No.</th>
                <th>Consignee Name</th>
                <th>Custom File No.</th>
                <th>Total Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($cargoExpenseDataByVoucher as $items)
             <?php $dataExpenses = App\CustomExpensesDetails::checkExpense($items->id); 
                    $cls = '';
                    if($dataExpenses > 0)
                        $cls = 'expandpackage fa fa-plus';

                    $dataUps = App\Ups::getUpsData($items->ups_details_id); 

                    
                    $editLing = route('editcustomexpnese',[$items->id]);
                ?>
                <tr data-editlink="<?php echo $editLing; ?>" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none">{{$items->id}}</td>
                    <td style="display: block;text-align: center;padding-top: 13px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->id; ?>"></td>
                    <td><?php echo date('d-m-Y',strtotime($items->exp_date)) ?></td>
                    <td>{{$items->voucher_number}}</td>
                    <td><?php echo $items->ups_file_number;  ?></td>
                    <td><?php $data = app('App\Ups')->getUpsData($items->ups_details_id); 
                    if(!empty($data))
                    {
                    $dataclient = app('App\Clients')->getClientData($data->consignee_name); 
                    echo !empty($dataclient->company_name) ? $dataclient->company_name : '-'; 
                    }else{
                        echo "-";
                    }
                    ?></td>
                    <td><?php echo $items->custom_file_number;  ?></td>
                    <td class="alignright"><?php echo App\CustomExpenses::getExpenseTotal($items->id);  ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                            $delete =  route('deletecustomexpnesevoucher',$items->id);   
                            $edit =  route('editcustomexpnese',$items->id);
                        ?>
                        <?php if($permissionCourierCustomExpenseEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCourierCustomExpenseDelete) { ?>
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
    $('#example').DataTable({
        'stateSave': true,
         "columnDefs": [ {
            "targets": [1,7],
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
            var urlzte = '<?php echo route("expandcustomexpenses"); ?>';
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

