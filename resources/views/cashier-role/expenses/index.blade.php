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
                <th>Shipper</th>
                <th>Currency</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
    </table>
        </div>
    </div>
</section>
<style>
    .hide_column {
    display : none;
}
</style>  
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    DatatableInitiate();
   
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
})
function DatatableInitiate(status = ''){
    var i = 1;
    $('#example').DataTable({
        "bDestroy": true,
        "processing": true,
        "serverSide": true,
        'stateSave': true,
        stateSaveParams: function (settings, data) {
            delete data.order;
        },
        "columnDefs": [ {
            "targets": [1,-1],
            "orderable": false
            },{ targets: [ 0 ],
            className: "hide_column" 
            }],
         "order": [[ 0, "desc" ]],
         "scrollX": true,
         drawCallback: function(){
              $('.fg-button,.sorting,#example_length', this.api().table().container())          
                 .on('click', function(){
                    /* $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200); */
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
                    if($('.fa-expand-collapse-all').hasClass('fa-minus'))
                    {
                        $('.fa-expand-collapse-all').removeClass('fa-minus');    
                        $('.fa-expand-collapse-all').addClass('fa-plus');
                    }
                    /* $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200); */
                }); 
        },
        "ajax":{
            url :"{{url('cashier/expense/listbydatatableserverside')}}", // json datasource
            data : function ( d ) {
                d.status = status;
                // d.custom = $('#myInput').val();
                // etc
            },
            // type: "post",  // method  , by default get
            error: function(){  // error handling
                $(".example-error").html("");
                $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#example_processing").css("display","none");

            }
        },
        "createdRow": function ( row, data, index ) {
            var cls  = '';
            $('#loading').show();
            setTimeout(function() { $("#loading").hide(); }, 1000);
            var expenseId = data[0];
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            var url = '<?php echo route("cashierexpensecheckoperationfordatatableserverside"); ?>';
            $.ajax({
                url:url,
                type:'POST',
                data: {'expenseId':expenseId,'flag':'checkExpense'},
                success:function(data) {
                    if(data > 0)
                        cls = 'fa fa-plus';

                        $('td', row).eq(1).addClass('expandpackage '+cls);
                        $('td', row).eq(1).attr('style', 'display: block;text-align: center;padding-top: 13px;');
                        $('td', row).eq(1).attr('data-expenseid',expenseId);
                        $('td', row).eq(1).attr('data-rowid',i);

                        $('td', row).eq(11).attr('style', 'text-align: right;');

                        $.ajax({
                        url:url,
                        type:'POST',
                        dataType : 'json',
                        data: {'expenseId':expenseId,'flag':'getExpenseData'},
                        success:function(data) {
                            var editLink = '<?php echo url("expense/getprintviewsingleexpensecashier"); ?>';
                            editLink += '/'+expenseId+'/'+data.cargo_id+'/fromNotification';
                            $(row).attr('data-editlink',editLink);
                            $(row).addClass('edit-row');
                            $(row).attr('id',expenseId);
                            
                        }
                    })
                    i++;
                }
            })
            
        }
    });
}
</script>
@stop

