@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Payment' : 'Add Payment'; ?>
@stop


@section('breadcrumbs')
    @include('menus.cargo-invoice')
@stop


@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Payment' : 'Add Payment'; ?></h1>
</section>
<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="box box-success" style="float: left;">
        <div class="box-body">
            <?php
            if($model->id)
            $actionUrl = url('invoicepayment/update',$model->id);
            else
            $actionUrl = url('invoicepayment/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('courierorcargo') ? 'has-error' :'' }}">
                        <?php echo Form::label('courierorcargo', 'Courier/Cargo',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::select('courierorcargo', $courierCargo,'Cargo',['class'=>'form-control selectpicker fclient','data-live-search' => 'true']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('client') ? 'has-error' :'' }}">
                        <?php echo Form::label('client', 'Client',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::select('client', $allUsers,$model->client,['class'=>'form-control selectpicker fclient','data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Invoice Number',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6"  style=<?php echo !empty($cargoId) ? "opacity:0.5;pointer-events:none;" : ""  ?>>
                            <?php echo Form::select('invoice_id',$invoiceArray ,$model->invoice_id,['class'=>'form-control selectpicker finvoice_id','data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                 <div class="col-md-4">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Amount Received',['class'=>'col-md-6 control-label']); ?>
                        <div class="col-md-6">
                            <input type="text" name="amount_received" class="form-control amount_received" value="0.00">
                        </div>
                    </div>
                </div>
                <input type="hidden" value="" id="invoice_number" name="invoice_number">
            </div>

            
            <div class="bodypayment col-md-12" style="margin-top: 25px;display: none;">
                <table id="example" class="display table" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Description</th>
                            <th>Billing Party</th>
                            <th>Date</th>
                            <th class="alignright">Original Amount</th>
                            <th class="alignright">Due Amount</th>
                            <th class="alignright">Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>No Data Found.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="col-md-12" style="padding-right: 5px;">
                <div class="col-md-4" style="float: right;text-align: right;padding-right: 0;">
                    <span class="col-md-8" style="font-weight: bold;">Amount to Apply</span>
                    <span class="total-amt-apply col-md-4">0.00</span>
                </div>
                <div class="col-md-4" style="float: right;clear: both;;text-align: right;padding-right: 0;">
                    <span class="col-md-8" style="font-weight: bold;">Amount to Credit</span>
                    <span class="col-md-4">
                        <input name="amt-credit-to-client" class="total-amt-credit form-control" value="0.00" style="border: none;box-shadow: none;text-align: right;padding: 0px">
                    </span>
                </div>
            </div>
            </div>

            

            <div class="col-md-12" style="margin-top: 25px;">
                
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('payment_via') ? 'has-error' :'' }}">
                        <?php echo Form::label('payment_via', 'Payment Via',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-8">
                            <?php echo Form::select('payment_via', $paymentVia,'',['class'=>'form-control selectpicker fpayment_via','data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group {{ $errors->has('payment_via_note') ? 'has-error' :'' }}">
                        <?php echo Form::label('payment_via_note', 'Comment',['class'=>'col-md-2 control-label']); ?>
                        <div class="col-md-8">
                            <?php echo Form::textarea('payment_via_note',$model->payment_via_note,['class'=>'form-control fpayment_via_note','rows'=>2,'placeholder'=>'Ex: Cheque,Cash,Bank Transfer,Credit Card']); ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-12">

            <div class="form-group col-md-12 btm-sub">
                
                <button type="submit" class="btn btn-success">Receive Payment</button>
                
                
            </div>
        </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
        return false;
    }
        return true;
    }

    $(function() {
            $('.amount_received').blur(function() {
                $(this).formatCurrency({ negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
            })
            .keyup(function(e) {
                var e = window.event || e;
                var keyUnicode = e.charCode || e.keyCode;
                if (e !== undefined) {
                    switch (keyUnicode) {
                        case 16: break; // Shift
                        case 17: break; // Ctrl
                        case 18: break; // Alt
                        case 27: this.value = ''; break; // Esc: clear entry
                        case 35: break; // End
                        case 36: break; // Home
                        case 37: break; // cursor left
                        case 38: break; // cursor up
                        case 39: break; // cursor right
                        case 40: break; // cursor down
                        case 78: break; // N (Opera 9.63+ maps the "." from the number key section to the "N" key too!) (See: http://unixpapa.com/js/key.html search for ". Del")
                        case 110: break; // . number block (Opera 9.63+ maps the "." from the number block to the "N" key (78) !!!)
                        case 190: break; // .
                        default: $(this).formatCurrency({ negativeFormat: '-%s%n', roundToDecimalPlace: -1, eventOnDecimalsEntered: true ,symbol:''});
                    }
                }
            })
        });

    $(document).ready(function() {

         $('#example1').DataTable(
        {
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

        $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
        });

        $('#invoice_id').change(function(){
                $('#client option[value=""]').prop('selected', true);
                $('#client').selectpicker('refresh'); 
                $('#loading').show();
                $('#invoice_number').val($("#invoice_id option:selected").html());

                $('.total-amt-apply').text('0.00');
                $('.total-amt-credit').text('0.00');
                $('.amount_received').val('0.00');
                $('.bodypayment').show();
                $('#loading').show();
                 var invoiceId = $(this).val();
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var urlztnn = '<?php echo url("invoicepayment/getselectedinvoicedata"); ?>';
                    $.ajax({
                            url:urlztnn,
                            type:'POST',
                            data:{'invoiceId':invoiceId},
                            success:function(data) {
                                        $('.bodypayment table tbody').html(data);
                                        $('#loading').hide();
                                    }
                        });

        })

        $('#courierorcargo').change(function(){
                $('.bodypayment').hide();
                $('#client option[value=""]').prop('selected', true);
                $('#client').selectpicker('refresh'); 
                $('#invoice_id option[value=""]').prop('selected', true);
                $('#invoice_id').selectpicker('refresh'); 
                var flag = $(this).val();
                $('#loading').show();
                $('#invoice_number').val($("#invoice_id option:selected").html());

                $('.total-amt-apply').text('0.00');
                $('.total-amt-credit').text('0.00');
                $('.amount_received').val('0.00');
                $('#loading').show();
                 var invoiceId = $(this).val();
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var urlztnn = '<?php echo url("invoicepayment/getcourierorcargodata"); ?>';
                    $.ajax({
                            url:urlztnn,
                            type:'POST',
                            data:{'flag':flag},
                            success:function(data) {
                                        $('#invoice_id').html(data);
                                        $('.selectpicker').selectpicker('refresh'); 
                                        $('.bodypayment table tbody').html("");
                                        $('#loading').hide();
                                    }
                        });
        })            

        

            $('#createInvoiceForm').on('submit', function (event) {
                    
                });

                $('#createInvoiceForm').validate({
                    submitHandler : function(form) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        if($('#client').val() == '' && $('#invoice_id').val() == '')
                        {
                            alert("Please choose either Client or Invoice number.");
                            return false;
                        }


                        var createInvoiceForm = $("#createInvoiceForm");
                        var formData = createInvoiceForm.serialize();
                        var urlztnn = '<?php echo url("invoicepayment/storeall"); ?>';
                            $.ajax({
                                url:urlztnn,
                                async:false,
                                type:'POST',
                                data:formData,
                                success:function(data) {
                                    window.location.href = "<?php echo route('invoicepaymentcreateall'); ?>";                                        
                                }
                        })
                        
                    },
                    errorPlacement: function(error, element) {
                        if (element.hasClass("finvoice_id" ))
                        {
                        var pos = $('.finvoice_id button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if  (element.attr("name") == "client" )
                        {
                        var pos = $('.fclient button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else
                        {
                        error.insertAfter(element);
                        }
                    }
                });

            $('#client').change(function(){
                var flagV = $('#courierorcargo').val();
                $('#invoice_id option[value=""]').prop('selected', true);
                $('#invoice_id').selectpicker('refresh'); 
                $('.total-amt-apply').text('0.00');
                $('.total-amt-credit').text('0.00');
                $('.amount_received').val('0.00');
                $('.bodypayment').show();
                $('#loading').show();
                 var clientId = $(this).val();
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var urlztnn = '<?php echo url("invoicepayment/getinvoicesofclient"); ?>';
                    $.ajax({
                            url:urlztnn,
                            type:'POST',
                            data:{'clientId':clientId,'flagV':flagV},
                            success:function(data) {
                                        $('.bodypayment table tbody').html(data);
                                        $('#loading').hide();
                                    }
                        });
            })

            var toalAmtApply = 0.00;
            $('#selectAll').click(function(e){
                var table= $(e.target).closest('table');
                $('td input:checkbox',table).prop('checked',this.checked);

                if($(this).prop('checked') == true)
                {
                    toalAmtApply = 0.00;
                    $('tbody tr').each(function(k,v){
                        var id = $(this).attr('id');
                        dueAmt = $('#due-amt-'+id).text().replace(/\,/g,'');
                        $('#due-amt-fill-'+id).val(dueAmt).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                        toalAmtApply =  parseFloat(toalAmtApply) + parseFloat(dueAmt);
                        $('.total-amt-apply').text(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                        $('.amount_received').val(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    })
                }
                else
                {
                    $('tbody tr').each(function(k,v){
                        var id = $(this).attr('id');
                        $('#due-amt-fill-'+id).val("");
                        $('.total-amt-apply').text("0.00")
                        toalAmtApply = 0.00;
                        $('.amount_received').val(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                        $('.total-amt-credit').val(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    })

                }

            });

            //$('.singlecheckbox').click(function(){
            $(document).on('click','.singlecheckbox',function(){    
                var id = $(this).val();
                var dueAmt = 0;
                if($(this).prop('checked') == true)
                {
                    dueAmt = $('#due-amt-'+id).text().replace(/\,/g,'');
                    $('#due-amt-fill-'+id).val(dueAmt).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    toalAmtApply =  parseFloat(toalAmtApply) + parseFloat(dueAmt);
                    $('.total-amt-apply').text(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    $('.amount_received').val(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                }
                else
                {
                    dueAmt = $('#due-amt-'+id).text().replace(/\,/g,'');
                    toalAmtApply =  parseFloat(toalAmtApply) - parseFloat(dueAmt);
                    $('.total-amt-apply').text(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    $('.amount_received').val(toalAmtApply).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    $('#due-amt-fill-'+id).val("");
                }
            })

            $(document).on("focusout",".amount_received",function (e) {
                enterCreditedAmt = $(this).val().replace(/\,/g,'');
                var paymentTotalAmt = $('.total-amt-apply').text().replace(/\,/g,'');
                if(parseFloat(enterCreditedAmt) > parseFloat(paymentTotalAmt))
                {
                    var doCreditAmt = parseFloat(enterCreditedAmt) - parseFloat(paymentTotalAmt);
                    $('.total-amt-credit').val(doCreditAmt).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                    $('.total-amt-apply').text(enterCreditedAmt).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                }else
                {
                    $('.total-amt-credit').val('0.00');
                }
            });

            $(document).on("focusout",".input-due-amt",function (e) {
                var cTotal = 0.00;
                $('.input-due-amt').each(function(k,v){
                    if($(this).val() != "")
                        cTotal = parseFloat(cTotal) + parseFloat($(this).val().replace(/\,/g,''));
                })
                $('.total-amt-apply').text(cTotal).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
                $('.amount_received').val(cTotal).formatCurrency({  negativeFormat: '-%s%n', roundToDecimalPlace: 2 ,symbol:''});
            });
            
    })
</script>
@stop