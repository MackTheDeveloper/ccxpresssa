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
            if ($model->id)
                $actionUrl = url('invoicepayment/update', $model->id);
            else
                $actionUrl = url('invoicepayment/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12" style="display:none">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('courierorcargo') ? 'has-error' :'' }}">
                        <?php echo Form::label('courierorcargo', 'Courier/Cargo', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::select('courierorcargo', $courierCargo, 'Cargo', ['class' => 'form-control selectpicker fclient', 'data-live-search' => 'true']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('client') ? 'has-error' :'' }}">
                        <?php echo Form::label('client', 'Client', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::select('client', $allUsers, $model->client, ['class' => 'form-control selectpicker fclient', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" style="display:none">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Invoice Number', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6" style=<?php echo !empty($cargoId) ? "opacity:0.5;pointer-events:none;" : ""  ?>>
                            <?php echo Form::select('invoice_id', $invoiceArray, $model->invoice_id, ['class' => 'form-control selectpicker finvoice_id', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Amount Received', ['class' => 'col-md-6 control-label']); ?>
                        <div class="col-md-6">
                            <input type="text" name="amount_received" class="form-control amount_received" value="0.00">
                        </div>
                    </div>
                </div>
                <input type="hidden" value="" id="invoice_number" name="invoice_number">
            </div>

            <div class="col-md-4" style="margin-top: 10px;background: #e8e8e8;">
                <div class="col-md-2">USD:</div>
                <div class="col-md-4 amountInUSD">0.00</div>
                <div class="col-md-2">HTG:</div>
                <div class="col-md-4 amountInHTG">0.00</div>
            </div>


            <div class="bodypayment col-md-12" style="margin-top: 25px;display: none;">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Description</th>
                            <th>Shipment Number</th>
                            <th>Billing Party</th>
                            <th>Date</th>
                            <th>Currency</th>
                            <th class="alignright">Original Amount</th>
                            <th class="alignright">Due Amount</th>
                            <th class="alignright">Credit Amount</th>
                            <th class="alignright">Payment</th>
                            <th class="alignright">Exchange Payment</th>
                            <th style="display: none">typeFlag</th>
                            <th style="display: none">ID</th>
                            <th style="display: none">input3</th>
                            <th style="display: none">input4</th>
                        </tr>
                    </thead>
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
                    <div class="col-md-4" style="float: right;clear: both;;text-align: right;padding-right: 0;">
                        <span class="col-md-8" style="font-weight: bold;">Exchange Amount to Apply</span>
                        <span class="exchange_currency_amount col-md-4">0.00</span>
                    </div>
                    <div class="col-md-8" style="float: right;clear:both;padding-right: 0;margin-top: 20px;">
                        <span class="col-md-5">
                            <?php echo Form::select('exchange_currency', $currency, '', ['class' => 'form-control selectpicker exchange_currency', 'data-live-search' => 'true', 'placeholder' => 'Select Payment Currency']); ?>
                        </span>
                        <span class="col-md-5 exchange_rate_span">
                            <input type="text" name="exchange_rate" class="form-control exchange_rate" value="0.00">
                        </span>
                        <span class="col-md-2">
                            <a href="javascript:void(0)" class="btn btn-success applycurrencyexchange">Apply</a>
                        </span>
                    </div>
                </div>
            </div>



            <div class="col-md-12" style="margin-top: 25px;">

                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('payment_via') ? 'has-error' :'' }}">
                        <?php echo Form::label('payment_via', 'Payment Via', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-8">
                            <?php echo Form::select('payment_via', $paymentVia, '', ['class' => 'form-control selectpicker fpayment_via', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group {{ $errors->has('payment_via_note') ? 'has-error' :'' }}">
                        <?php echo Form::label('payment_via_note', 'Comment', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-8">
                            <?php echo Form::textarea('payment_via_note', $model->payment_via_note, ['class' => 'form-control fpayment_via_note', 'rows' => 2, 'placeholder' => 'Ex: Cheque,Cash,Bank Transfer,Credit Card']); ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-12">
                <input type="hidden" name="flagBtn" class="flagBtn" id="flagBtn" value="">
                <div class="form-group col-md-12 btm-sub">

                    <button type="submit" class="btn btn-success">Receive Payment</button>
                    <button type="submit" id="CreateButtonSavePrint" class="btn btn-success btn-prime white btn-flat">Receive Payment & Print</button>

                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
<style>
    .hide_column {
        display: none;
    }
</style>
@endsection
@section('page_level_js')
<script type="text/javascript">
    var selectedCheckBoxArray = [];
    var toalAmtApply = 0.00;
    var exchangeAmountToApply = 0.00

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
                $(this).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
            })
            .keyup(function(e) {
                var e = window.event || e;
                var keyUnicode = e.charCode || e.keyCode;
                if (e !== undefined) {
                    switch (keyUnicode) {
                        case 16:
                            break; // Shift
                        case 17:
                            break; // Ctrl
                        case 18:
                            break; // Alt
                        case 27:
                            this.value = '';
                            break; // Esc: clear entry
                        case 35:
                            break; // End
                        case 36:
                            break; // Home
                        case 37:
                            break; // cursor left
                        case 38:
                            break; // cursor up
                        case 39:
                            break; // cursor right
                        case 40:
                            break; // cursor down
                        case 78:
                            break; // N (Opera 9.63+ maps the "." from the number key section to the "N" key too!) (See: http://unixpapa.com/js/key.html search for ". Del")
                        case 110:
                            break; // . number block (Opera 9.63+ maps the "." from the number block to the "N" key (78) !!!)
                        case 190:
                            break; // .
                        default:
                            $(this).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: -1,
                                eventOnDecimalsEntered: true,
                                symbol: ''
                            });
                    }
                }
            })
    });

    $(document).ready(function() {
        DatatableInitiate();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var urlztnn2 = '<?php echo url("invoicepayment/getclientsforallpayment"); ?>';
        $.ajax({
            url: urlztnn2,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">-- Select --</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.id + '">' + v.company_name + '</option>';
                });
                $('#client').html(html);
                $('#client').selectpicker('refresh');
                $('#loading').hide();
            }
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#invoice_id').change(function() {
            $('#client option[value=""]').prop('selected', true);
            $('#client').selectpicker('refresh');
            $('#selectAll').prop('checked', false);
            $('#loading').show();
            $('#invoice_number').val($("#invoice_id option:selected").html());

            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.exchange_currency_amount').text('0.00');
            $('.exchange_currency_amount').text('0.00');
            toalAmtApply = 0.00;
            exchangeAmountToApply = 0.00
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
                url: urlztnn,
                type: 'POST',
                data: {
                    'invoiceId': invoiceId
                },
                success: function(data) {
                    $('.bodypayment table tbody').html(data);
                    $('#loading').hide();
                }
            });

        })

        $('#courierorcargo').change(function() {
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
                url: urlztnn,
                type: 'POST',
                data: {
                    'flag': flag
                },
                success: function(data) {
                    $('#invoice_id').html(data);
                    $('.selectpicker').selectpicker('refresh');
                    $('.bodypayment table tbody').html("");
                    $('#loading').hide();
                }
            });
        })



        $('#createInvoiceForm').on('submit', function(event) {

        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var submitButtonName = $(this.submitButton).attr("id");
                if ($(this.submitButton).attr("id") == 'CreateButtonSavePrint')
                    $('.flagBtn').val('saveprint');
                else
                    $('.flagBtn').val('');

                var checkDisabled = 0;
                $('.input-due-amt').each(function(k, v) {
                    if ($(this).val() != '')
                        checkDisabled = 1;
                })

                if (checkDisabled == 0) {
                    alert("Please enter any payment amount.");
                    $('#loading').hide();
                    return false;
                }

                if ($('#client').val() == '' && $('#invoice_id').val() == '') {
                    alert("Please choose either Client or Invoice number.");
                    $('#loading').hide();
                    return false;
                }


                var createInvoiceForm = $("#createInvoiceForm");
                var formData = createInvoiceForm.serialize();
                var urlztnn = '<?php echo url("invoicepayment/storeall"); ?>';
                $.ajax({
                    url: urlztnn,
                    //async:false,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        //return false;
                        if (submitButtonName == 'CreateButtonSavePrint') {
                            //$('#client').val('');
                            //$('#client').trigger('change');
                            DatatableInitiate($('#client').val());
                            window.open(data, '_blank');
                        } else {
                            window.location.href = "<?php echo route('invoicepaymentcreateall'); ?>";
                        }
                        $('#loading').hide();

                    }
                })

            },
            errorPlacement: function(error, element) {
                if (element.hasClass("finvoice_id")) {
                    var pos = $('.finvoice_id button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "client") {
                    var pos = $('.fclient button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        <?php if ($clientId) { ?>
            $('#client').val('<?php echo $clientId; ?>');
            $('#client').selectpicker('refresh');
            $('#selectAll').prop('checked', false);
            var flagV = $('#courierorcargo').val();
            $('#invoice_id option[value=""]').prop('selected', true);
            $('#invoice_id').selectpicker('refresh');
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.exchange_currency_amount').text('0.00');
            toalAmtApply = 0.00;
            exchangeAmountToApply = 0.00
            $('.bodypayment').show();
            $('#loading').show();
            var clientId = $('#client').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            //var urlztnn = '<?php echo url("invoicepayment/getinvoicesofclient"); ?>';
            var urlztnn = '<?php echo url("invoicepayment/getcargoandcourierinvoicesofclient"); ?>';
            $.ajax({
                url: urlztnn,
                type: 'POST',
                data: {
                    'clientId': clientId,
                    'flagV': flagV
                },
                success: function(data) {
                    $('.bodypayment table tbody').html(data);
                    $('#loading').hide();
                }
            });
        <?php } ?>

        $('#client').change(function() {
            $('#selectAll').prop('checked', false);
            var flagV = $('#courierorcargo').val();
            $('#invoice_id option[value=""]').prop('selected', true);
            $('#invoice_id').selectpicker('refresh');
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.exchange_currency_amount').text('0.00');
            toalAmtApply = 0.00;
            exchangeAmountToApply = 0.00
            $('.bodypayment').show();
            $('#loading').show();
            var clientId = $(this).val();
            setTimeout(function() {
                var urlztnn = '<?php echo url("invoicepayment/getclientdataforcredit"); ?>';
                $.ajax({
                    url: urlztnn,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'clientId': clientId,
                    },
                    success: function(data) {
                        if (data.status == '1') {
                            $('.amountInUSD').text(data.amountInUSD);
                            $('.amountInHTG').text(data.amountInHTG);
                            $('.paymentCredit').prop('readonly', false);
                        } else {
                            $('.amountInUSD').text('0.00');
                            $('.amountInHTG').text('0.00');
                            $('.paymentCredit').prop('readonly', true);
                        }
                    }
                });
            }, 500);
            DatatableInitiate(clientId);
            $('#loading').hide();
            /* $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); */
            //var urlztnn = '<?php //echo url("invoicepayment/getinvoicesofclient"); 
                                ?>';
            /* var urlztnn = '<?php //echo url("invoicepayment/getcargoandcourierinvoicesofclient"); 
                                ?>';
            $.ajax({
                url: urlztnn,
                type: 'POST',
                data: {
                    'clientId': clientId,
                    'flagV': flagV
                },
                success: function(data) {
                    $('.bodypayment table tbody').html(data);
                    $('#loading').hide();
                }
            }); */
        })

        toalAmtApply = 0.00;
        $('#selectAll').click(function(e) {
            var table = $('#example');
            $('td input:checkbox', table).prop('checked', this.checked);

            if ($(this).prop('checked') == true) {
                toalAmtApply = 0.00;
                exchangeAmountToApply = 0.00;
                $('#example tbody tr').each(function(k, v) {
                    var id = $(this).attr('id');
                    dueAmt = $('#due-amt-' + id).text().replace(/\,/g, '');
                    $('#due-amt-fill-' + id).val(dueAmt).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                    $('#exchange_amount-' + id).val(dueAmt).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                    toalAmtApply = parseFloat(toalAmtApply) + parseFloat(dueAmt);
                    $('.total-amt-apply').text(toalAmtApply).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                    $('.amount_received').val(toalAmtApply).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });

                    exchangeAmountToApply = parseFloat(exchangeAmountToApply) + parseFloat($('#exchange_amount-' + id).val().replace(/\,/g, ''));
                    $('.exchange_currency_amount').text(exchangeAmountToApply).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                })
            } else {
                $('#example tbody tr').each(function(k, v) {
                    var id = $(this).attr('id');
                    $('#due-amt-fill-' + id).val("");

                    $('.total-amt-apply').text("0.00")
                    $('.exchange_currency_amount').text("0.00")
                    toalAmtApply = 0.00;
                    exchangeAmountToApply = 0.00;
                    $('.amount_received').val(toalAmtApply).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                    $('.total-amt-credit').val(toalAmtApply).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });

                    $('#exchange_amount-' + id).val("");
                })

            }

        });

        //$('.singlecheckbox').click(function(){
        exchangeAmountToApply = 0.00;
        $(document).on('click', '.singlecheckbox', function() {
            /* var checkBoxId = $(this).val();
            var rowIndex = $.inArray(checkBoxId, selectedCheckBoxArray); //Checking if the

            if (this.checked && rowIndex === -1) {
                selectedCheckBoxArray.push(checkBoxId); // If checkbox selected and element is not in the list->Then push it in array.
            } else if (!this.checked && rowIndex !== -1) {
                selectedCheckBoxArray.splice(rowrowIndex, 1); // Remove it from the array.
            } */
            var checkedFlag = 0;
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checkedFlag = 1;
                } else {
                    checkedFlag = 0;
                    return false;
                }
            });
            if (checkedFlag == 0) {
                $('#selectAll').prop('checked', false);
            }
            if (checkedFlag == 1) {
                $('#selectAll').prop('checked', true);
            }
            var id = $(this).val();
            var dueAmt = 0;
            if ($(this).prop('checked') == true) {
                dueAmt = $('#due-amt-' + id).text().replace(/\,/g, '');
                $('#due-amt-fill-' + id).val(dueAmt).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                $('#exchange_amount-' + id).val(dueAmt).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                toalAmtApply = parseFloat(toalAmtApply) + parseFloat(dueAmt);
                $('.total-amt-apply').text(toalAmtApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                $('.amount_received').val(toalAmtApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });

                exchangeAmountToApply = parseFloat(exchangeAmountToApply) + parseFloat($('#exchange_amount-' + id).val().replace(/\,/g, ''));
                $('.exchange_currency_amount').text(exchangeAmountToApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
            } else {
                dueAmt = $('#due-amt-' + id).text().replace(/\,/g, '');
                toalAmtApply = parseFloat(toalAmtApply) - parseFloat(dueAmt);
                $('.total-amt-apply').text(toalAmtApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                $('.amount_received').val(toalAmtApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                $('#due-amt-fill-' + id).val("");


                exchangeAmountToApply = parseFloat(exchangeAmountToApply) - parseFloat($('#exchange_amount-' + id).val().replace(/\,/g, ''));
                $('.exchange_currency_amount').text(exchangeAmountToApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                $('#exchange_amount-' + id).val("");
            }
        })

        exchangeAmountToApply = 0.00;
        $('.applycurrencyexchange').click(function() {
            exchangeAmountToApply = 0.00;
            var selectedExchangeCurrency = $('.exchange_currency option:selected').val();
            $('#loading').show();
            $('input[name="singlecheckbox"]:checked').each(function() {
                var invoiceCurrency = $(this).data('currency');
                var id = $(this).attr('id');
                if (selectedExchangeCurrency != invoiceCurrency) {

                    if (typeof($('#exchange_value-' + invoiceCurrency).val()) === "undefined")
                        var excVal = 1;
                    else
                        var excVal = $('#exchange_value-' + invoiceCurrency).val();

                    $('#exchange_amount-' + id).val($('#due-amt-fill-' + id).val().replace(/\,/g, '') * excVal).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });

                    // Manish - For C/N
                    $('#paymentCredit-' + id).val($('#paymentCredit-' + id).val().replace(/\,/g, '') * excVal).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                    // Manish - For C/N
                }else {
                    $('#exchange_amount-' + id).val($('#due-amt-fill-' + id).val().replace(/\,/g, '') * 1).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });

                    // Manish - For C/N
                    $('#paymentCredit-' + id).val('');
                    // Manish - For C/N
                }

                exchangeAmountToApply = parseFloat(exchangeAmountToApply) + parseFloat($('#exchange_amount-' + id).val().replace(/\,/g, ''));
                $('.exchange_currency_amount').text(exchangeAmountToApply).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });

            });
            $('#loading').hide();
        })

        $('.exchange_currency').change(function() {
            var checked = '';
            $('input[name="singlecheckbox"]:checked').each(function() {
                checked += this.value + ',';
            });
            selectedInvoiceIds = checked.replace(/,\s*$/, "");



            $('#loading').show();
            var id = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlztnn = '<?php echo url("invoicepayment/getcurrencyratesection"); ?>';
            $.ajax({
                url: urlztnn,
                type: 'POST',
                data: {
                    'id': id,
                    'selectedInvoiceIds': selectedInvoiceIds
                },
                success: function(data) {
                    $('.exchange_rate_span').html(data);
                    $('#loading').hide();
                }
            });
        })

        $(document).on("focusout", ".amount_received", function(e) {
            enterCreditedAmt = $(this).val().replace(/\,/g, '');
            var paymentTotalAmt = $('.total-amt-apply').text().replace(/\,/g, '');
            if (parseFloat(enterCreditedAmt) > parseFloat(paymentTotalAmt)) {
                var doCreditAmt = parseFloat(enterCreditedAmt) - parseFloat(paymentTotalAmt);
                $('.total-amt-credit').val(doCreditAmt).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
                $('.total-amt-apply').text(enterCreditedAmt).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
            } else {
                $('.total-amt-credit').val('0.00');
            }
        });

        // Manish - For C/N
        $(document).on("focusout", ".paymentCredit", function(e) {
            var thiz = $(this);
            var paymentCreditTotal = 0.00;
            $('.paymentCredit').each(function(k, v) {
                if ($(this).val() != "")
                    paymentCreditTotal = parseFloat(paymentCreditTotal) + parseFloat($(this).val().replace(/\,/g, ''));
            })

            clientId = $('#client').val();
            if (clientId == '') {
                invoiceId = $('#invoice_id').val();
                var urlztnn = '<?php echo url("invoicepayment/getclientdataforcreditfrominvoice"); ?>';
                $.ajax({
                    url: urlztnn,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'invoiceId': invoiceId,
                    },
                    success: function(data) {
                        if ($('.exchange_currency option:selected').text() == 'HTG')
                            var comparisionCurrency = data.amountInHTG;
                        else if ($('.exchange_currency option:selected').text() == 'USD')
                            var comparisionCurrency = data.amountInUSD;
                        else {
                            if (data.clientCurrencyCode == 'HTG')
                                var comparisionCurrency = data.amountInHTG;
                            else
                                var comparisionCurrency = data.amountInUSD;
                        }
                        
                        if (comparisionCurrency.replace(/\,/g, '') < paymentCreditTotal) {
                            alert("Not enough C/N")
                            $(thiz.val(''));
                        }
                    }
                });
            } else {
                var urlztnn = '<?php echo url("invoicepayment/getclientdataforcredit"); ?>';
                $.ajax({
                    url: urlztnn,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'clientId': clientId,
                    },
                    success: function(data) {
                        if ($('.exchange_currency option:selected').text() == 'HTG')
                            var comparisionCurrency = data.amountInHTG;
                        else if ($('.exchange_currency option:selected').text() == 'USD')
                            var comparisionCurrency = data.amountInUSD;
                        else {
                            if (data.clientCurrencyCode == 'HTG')
                                var comparisionCurrency = data.amountInHTG;
                            else
                                var comparisionCurrency = data.amountInUSD;
                        }

                        if (comparisionCurrency.replace(/\,/g, '') < paymentCreditTotal) {
                            alert("Not enough C/N")
                            $(thiz.val(''));
                        }
                    }
                });
            }
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    var id = $(this).val();
                    var dueAmts = $('#due-amt-' + id).text().replace(/\,/g, '');
                    var paymentCredit = $('#paymentCredit-' + id).val();
                    if (parseInt(paymentCredit) > parseInt(dueAmts)) {

                        $('#paymentCredit-' + id).val(dueAmts).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });
                    } else {
                        $('#paymentCredit-' + id).val(paymentCredit).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });
                    }
                }
            });
        });
        // Manish - For C/N

        $(document).on("focusout", ".input-due-amt", function(e) {

            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    var id = $(this).val();
                    var dueAmts = $('#due-amt-' + id).text().replace(/\,/g, '');
                    var payment = $('#due-amt-fill-' + id).val();
                    if (parseInt(payment) > parseInt(dueAmts)) {

                        $('#due-amt-fill-' + id).val(dueAmts).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });
                        $('#exchange_amount-' + id).val(dueAmts).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });

                    } else {
                        $('#due-amt-fill-' + id).val(payment).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });
                        $('#exchange_amount-' + id).val(payment).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });
                    }

                }
            });
            var idT = $(this).attr('id');
            var idTD = idT.split("-");

            $('#exchange_amount-' + idTD[3]).val($(this).val().replace(/\,/g, ''));
            var cTotal = 0.00;
            var dTotal = 0.00;
            $('.input-due-amt').each(function(k, v) {
                if ($(this).val() != "")
                    cTotal = parseFloat(cTotal) + parseFloat($(this).val().replace(/\,/g, ''));
            })

            $('.exchange_amount').each(function(k, v) {
                if ($(this).val() != "")
                    dTotal = parseFloat(dTotal) + parseFloat($(this).val().replace(/\,/g, ''));
            })
            $('.total-amt-apply').text(cTotal).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
            $('.exchange_currency_amount').text(dTotal).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
            $('.amount_received').val(cTotal).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        });

    })

    function DatatableInitiate(clientId = '') {
        $('#example').DataTable({
            "lengthMenu": [
                [10, 25, 50, 100, 500, 1000, 2000],
                [10, 25, 50, 100, 500, 1000, 2000]
            ],
            "bDestroy": true,
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                    "targets": [-1, 0, 2, 3, 5, 6, 7, 8, 9, 10],
                    // "targets": [-1, 0, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    "orderable": false
                },
                {
                    targets: [11, 12, 13, 14],
                    className: "hide_column"
                }
            ],
            "order": [
                [4, "asc"]
            ],
            "scrollX": true,
            "ajax": {
                url: "{{url('invoicepayment/listbyall')}}", // json datasource
                data: function(d) {
                    //$('#loading').show();
                    d.clientId = clientId;
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            fnPreDrawCallback: function() {
                $('#loading').show();
            },
            fnDrawCallback: function() {
                $('#loading').hide();
            },
            "createdRow": function(row, data, index) {
                var invoiceId = data[12];
                $('td', row).eq(6).addClass('alignright');
                $('td', row).eq(7).attr('id', 'due-amt-' + invoiceId);
                $('td', row).eq(7).addClass('alignright due-amt');
                $('td', row).eq(9).addClass('alignright due-amt-fill');
                $('td', row).eq(10).addClass('alignright');
                $(row).attr('data-trid', invoiceId);
                $(row).attr('id', invoiceId);
                //console.log(row);
                //row.insertAfter('<input type="hidden" name="courierorcargo[6352]" value="UPS Housefile">');
                //$("<input type='hidden' value='UPS Housefile'>").insertAfter(row);
                //row.append('<input type="hidden" name="courierorcargo[6352]" value="UPS Housefile">');


            }
        });
    }

    $("#example").on('draw.dt', function() {
        $('#selectAll').prop('checked', false);
        $('.total-amt-apply').text('0.00');
        $('.total-amt-credit').text('0.00');
        $('.amount_received').val('0.00');
        $('.exchange_currency_amount').text('0.00');
        toalAmtApply = 0.00;
        exchangeAmountToApply = 0.00;
        /* for (var i = 0; i < selectedCheckBoxArray.length; i++) {
            checkboxId = selectedCheckBoxArray[i];
            $('#' + checkboxId).attr('checked', true);
        } */
    });
</script>
@stop