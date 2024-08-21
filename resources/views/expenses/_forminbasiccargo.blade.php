
<?php
    $actionUrl = route('storeexpenceinbasiccargo');
?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="redirectFlag" value="frombasicinfo">
<input type="hidden" name="flag" value="<?php echo $flag; ?>">
<input type="hidden" name="cargo_id" value="<?php echo $courierId; ?>">
<input type="hidden" name="bl_awb" value="" class="fbl_awb">
<input type="hidden" name="voucher_number" value="<?php echo $voucherNo; ?>">
<input type="hidden" class="count_expense" name="count_expense" value="1">
    <div class="col-md-8" style="float: right;">
                        <div class="col-md-4" style="float: right;margin-right: 13px;">
                        <span style="float: left;margin-right: 5px">File Number : </span><label style="float: left">{{$dataCargo->file_number}}</label>
                        </div>
                    </div>        
             <div class="col-md-12">
                            
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('consignee') ? 'has-error' :'' }}">
                                    <div class="col-md-12">
                                    <?php echo Form::label('consignee', 'Consignee',['class'=>'control-label']); ?>
                                    </div>
                                    <div class="col-md-12">
                                    <?php echo Form::text('consignee','',['class'=>'form-control','placeholder' => '']); ?>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('shipper') ? 'has-error' :'' }}">
                                    <div class="col-md-12">
                                    <?php echo Form::label('shipper', 'Shipper',['class'=>'control-label']); ?>
                                    </div>
                                    <div class="col-md-12">
                                    <?php echo Form::text('shipper','',['class'=>'form-control','placeholder' => '']); ?>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-4" style="pointer-events: none;opacity: 0.5">
                            <div class="form-group {{ $errors->has('voucher_number') ? 'has-error' :'' }}">
                                <div class="col-md-12">
                                <?php echo Form::label('voucher_number', 'Voucher No.',['class'=>'control-label']); ?>
                                </div>
                                <div class="col-md-12">
                                <span>#<?php echo $voucherNo; ?></span>
                                </div>
                            </div>
                        </div>
                            
                    </div>

                    <div class="col-md-12">
                        <div class="col-md-4">
                                <div class="form-group {{ $errors->has('note') ? 'has-error' :'' }}">
                                    <div class="col-md-12">
                                    <?php echo Form::label('note', 'Note',['class'=>'control-label']); ?>
                                    </div>
                                    <div class="col-md-12">
                                    <?php echo Form::textarea('note','',['class'=>'form-control','rows'=>2]); ?>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-2" style="float: right;">
                                <span style="margin-top: 56px;float: left;background: #ccc;padding: 5px 23px;">Currency : USD</span>
                            </div>
                    </div>


                <div class="expensesubcontainer">
                    <span style="width: 100%;text-align: left;display: none;margin-bottom: 10px;" class="btn btn-success spanexpense spanexpense-0"><span class="fa fa-plus fa-plus-expense" data-faplusid="0" id="fa-plus-0" style="float: left;padding: 5px;"></span><span style="float: right;" class="remove-sec" data-removeid="0">Remove</span></span>
                    <div id="container-0" class="allcontainer">
                        <div class="col-md-12" style="width: 1015px;overflow-x: scroll;">

                            <table class="table table-expense" style="width: 1200px;">
                                <thead>
                                    <tr>
                                        <th style="width: 6%;">Sr. No.</th>
                                        <th style="width: 10%;">Billing List</th>
                                        <th style="width: 11%;">Billing Account</th>
                                        <th style="width: 22%;">Description</th>
                                        <th style="width: 8%;">Amount</th>
                                        <th style="width: 10%;">Billing Party</th>
                                        <th style="width: 11%;">Cash Account</th>
                                        <th style="width: 10%;">Cont Number</th>
                                        <th style="width: 7%;">Action</th>
                                    </tr>
                                </thead>
                            

                            <tbody>
                                <tr id="0">
                                    <td>1</td>
                                    <td><?php echo Form::select('expense_type[0]', $dataBillingItems,'',['class'=>'form-control expense_type selectpicker fexpense_type','data-live-search' => 'true','id'=>'expense_type-0','data-expense_type'=>'0','placeholder' => 'Select ...']); ?></td>
                                    <td><?php echo Form::text('amount_billing_account[0]','',['class'=>'form-control amount_billing_account','id'=>'amount_billing_account-0','data-amount_billing_account'=>'0','placeholder' => '']); ?></td>
                                    <td><?php echo Form::textarea('description[0]','',['class'=>'form-control cvalidation','rows'=>1]); ?></td>
                                    <td><?php echo Form::text('amount[0]','',['class'=>'form-control cvalidation famount','placeholder' => '']); ?></td>
                                    <td><?php echo Form::select('billing_party[0]', $dataAssignee,'',['class'=>'form-control selectpicker fassignee','data-live-search' => 'true']); ?></td>
                                    <td><?php echo Form::select('cash_credit_account[0]', ['Cash'=>'Cash','Cheque'=>'Cheque'],'',['class'=>'form-control selectpicker fassignee','data-live-search' => 'true']); ?></td>
                                    <td><?php echo Form::text('cont_number[0]','',['class'=>'form-control cvalidation','placeholder' => '']); ?></td>
                                    <td><a href="javascript:void(0)" data-cid="0" class='btn btn-success btn-xs addmoreexpense'>+</a></td>
                                </tr>
                            </tbody>

                        </table>
                            

                    </div>
                    </div>
                    </div>

                     <div class="col-md-12" style="text-align: center;">
                        
                            <div class="form-group" style="padding-top: 28px;">
                                <button type="submit" id="CreateExpenseFormButton" class="btn btn-success btn-prime white btn-flat">Submit</button>
                                <a class="btn btn-danger" href="{{url('cargoall')}}" title="">Cancel</a>
                            </div>
                        
                    </div>
                
        {{ Form::close() }}

<script type="text/javascript">
$(document).ready(function() {
    $('.selectpicker').selectpicker();
     //$('.expense_type').change(function(){
    $(document).on("change",".expense_type",function (e) {    
        var id = $(this).data('expense_type');
        var billingId = $('#expense_type-'+id).val();
        
        $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
         var urlzt = '<?php echo url("billingitem/getbillinglistdata"); ?>';
         $.ajax({
                    url:urlzt,
                    dataType: "json",
                    type:'POST',
                    data:{'billingId':billingId},
                    success:function(data) {
                            $('#loading').hide();
                            $('#amount_billing_account-'+id).val(data.billingAccount);
                            }
                });
    });

    

    $('#createExpenseForm').on('submit', function (event) {

        event.preventDefault();
           /* $('.famount').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.fexpense_type').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });*/
            $('.fawb').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.ffilenumber').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            
        });
        $('#createExpenseForm').validate({
                    submitHandler: function (form) {
                        $.ajaxSetup({
                                headers:{
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                        $('#loading').show();
                        var createExpenseForm = $("#createExpenseForm");
                        var formData = createExpenseForm.serialize();
                        var urlz = '<?php echo url("expense/storeexpenseusingawl"); ?>';
                        $.ajax({
                        url:urlz,
                        type:'POST',
                        data:formData,
                        success:function(data) {
                                var urlz1 = '<?php echo url("cargo/viewcargo/$courierId/2#div_expenses"); ?>';
                               window.location.href = urlz1;
                                },
                        });
                        
                    }
            });

            $('.allcontainer').on("click",".addmoreexpense",function (e) {
                $('.count_expense').val(parseInt($('.count_expense').val())+1);
                var counter = parseInt($(this).data('cid'))+1;
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
                 var urlzte = '<?php echo url("expense/addmoreexpense"); ?>';
                 $.ajax({
                            url:urlzte,
                            type:'POST',
                            data:{'counter':counter},
                            success:function(data) {
                                    $('#loading').hide();
                                    $('.table-expense tbody').append(data);
                                    }
                        });

            });

            $(document).on('click','.removeexpense',function(){
                $('.count_expense').val(parseInt($('.count_expense').val())-1);
                $('.table-expense tbody tr#'+$(this).data('cid')).remove();
            })
});
</script>
