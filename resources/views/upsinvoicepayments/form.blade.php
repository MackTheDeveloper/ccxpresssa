@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Payment' : 'Add Payment'; ?>
@stop


@section('breadcrumbs')
@include('menus.ups-invoice')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Payment' : 'Add Payment'; ?></h1>
</section>
<section class="content">
    <div class="box box-success" style="float: left;">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('upsinvoicepayment/update', $model->id);
            else
                $actionUrl = url('upsinvoicepayment/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('client') ? 'has-error' :'' }}">
                        <?php echo Form::label('client', 'Client', ['class' => 'col-md-12 control-label']); ?>
                        <div class="col-md-12" style=<?php echo !empty($upsId) ? "opacity:0.5;pointer-events:none;" : ""  ?>>
                            <?php echo Form::select('client', $allUsers, $model->client, ['class' => 'form-control selectpicker fclient', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <?php echo Form::label('shipment_number', 'Shipment Number', ['class' => 'col-md-12 control-label']); ?>
                        <div class="col-md-12">
                            <?php echo Form::select('shipment_number', $dataShipmentNumbers, $model->shipment_number, ['class' => 'form-control selectpicker fshipment_number', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Invoice Number', ['class' => 'col-md-12 control-label']); ?>
                        <div class="col-md-12" style=<?php echo !empty($upsId) ? "opacity:0.5;pointer-events:none;" : ""  ?>>
                            <?php echo Form::select('invoice_id', $invoiceArray, $model->invoice_id, ['class' => 'form-control selectpicker finvoice_id', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Amount Received', ['class' => 'col-md-12 control-label']); ?>
                        <div class="col-md-12">
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
                <table id="example" class="display table" style="width:100%">
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
                            <th></th>
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
                <input type="hidden" name="flagInvoiceOrClient" class="flagInvoiceOrClient" id="flagInvoiceOrClient" value="">
                <div class="form-group col-md-12 btm-sub">

                    <button type="submit" class="btn btn-success">Receive Payment</button>
                    <button type="submit" id="CreateButtonSavePrint" class="btn btn-success btn-prime white btn-flat">Receive Payment & Print</button>
                    <a class="btn btn-danger" href="{{route('invoicepaymentslisting', ['ups'])}}" title="">Cancel</a>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var urlztnn2 = '<?php echo url("upsinvoicepayment/getclients"); ?>';
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

        var urlztnn = '<?php echo url("upsinvoicepayment/getshipmentnumbers"); ?>';
        $.ajax({
            url: urlztnn,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">-- Select --</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.shipment_number + '">' + v.shipment_number + '</option>';
                });
                $('#shipment_number').html(html);
                $('#shipment_number').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        var urlztnn1 = '<?php echo url("upsinvoicepayment/getupsinvoices"); ?>';
        $.ajax({
            url: urlztnn1,
            type: 'POST',
            data: {},
            success: function(response) {
                console.log(response);
                var userList = JSON.parse(response);
                var html = '<option value="">-- Select --</option>';
                $(userList).each(function(k, v) {
                    html += '<option value="' + v.id + '">' + v.bill_no + '</option>';
                });
                $('#invoice_id').html(html);
                //$('#invoice_id option[value="<?php //echo $model->invoice_id ?>"]').attr("selected", "selected");
                $('#invoice_id').val('<?php echo $model->invoice_id ?>');
                $('#invoice_id').selectpicker('refresh');
                $('#loading').hide();
            }
        });

        <?php if ($invoceId) { ?>
            $('#loading').show();
            $('#flagInvoiceOrClient').val('invoice');
            $('#invoice_number').val($("#invoice_id option:selected").html());
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.exchange_currency_amount').text('0.00');
            $('.bodypayment').show();
            $('#loading').show();
            var invoiceId = '<?php echo $invoceId ?>';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Manish - For C/N
            setTimeout(function() {
                var urlztnn = '<?php echo url("invoicepayment/getclientdataforcreditfrominvoice"); ?>';
                $.ajax({
                    url: urlztnn,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'invoiceId': invoiceId,
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
            // Manish - For C/N

            var urlztnn = '<?php echo url("upsinvoicepayment/getselectedupsinvoicedata"); ?>';
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
        <?php } ?>

        <?php if ($billingParty) { ?>
            $('#flagInvoiceOrClient').val('client');
            $('#invoice_id option[value=""]').prop('selected', true);
            $('#invoice_id').selectpicker('refresh');
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.exchange_currency_amount').text('0.00');
            $('.bodypayment').show();
            $('#loading').show();
            var clientId = '<?php echo $billingParty ?>';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Manish - For C/N
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
            // Manish - For C/N

            var urlztnn = '<?php echo url("upsinvoicepayment/getupsinvoicesofclient"); ?>';
            $.ajax({
                url: urlztnn,
                type: 'POST',
                data: {
                    'clientId': clientId
                },
                success: function(data) {
                    $('.bodypayment table tbody').html(data);
                    $('#loading').hide();
                }
            });
        <?php } ?>

        $('#invoice_id').change(function() {
            $('#flagInvoiceOrClient').val('invoice');
            $('#selectAll').prop('checked', false);
            $('#loading').show();
            $('#client option[value=""]').prop('selected', true);
            $('#client').selectpicker('refresh');
            $('#invoice_number').val($("#invoice_id option:selected").html());
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
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

            // Manish - For C/N
            setTimeout(function() {
                var urlztnn = '<?php echo url("invoicepayment/getclientdataforcreditfrominvoice"); ?>';
                $.ajax({
                    url: urlztnn,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'invoiceId': invoiceId,
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
            // Manish - For C/N

            var urlztnn = '<?php echo url("upsinvoicepayment/getselectedupsinvoicedata"); ?>';
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
                var urlztnn = '<?php echo url("upsinvoicepayment/store"); ?>';
                $.ajax({
                    url: urlztnn,
                    //async:false,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $('#loading').hide();
                        if (submitButtonName == 'CreateButtonSavePrint') {
                            window.open(data, '_blank');
                        }
                        window.location.href = "<?php echo route('invoicepaymentslisting', ['ups']); ?>";
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

        $('#shipment_number').change(function() {
            $('#client').trigger('change');
        });

        $('#client').change(function() {
            $('#flagInvoiceOrClient').val('client');
            $('#selectAll').prop('checked', false);
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

            // Manish - For C/N
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
            // Manish - For C/N

            var shipmentNumber = $('#shipment_number').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlztnn = '<?php echo url("upsinvoicepayment/getupsinvoicesofclient"); ?>';
            $.ajax({
                url: urlztnn,
                type: 'POST',
                data: {
                    'clientId': clientId,
                    'shipmentNumber': shipmentNumber
                },
                success: function(data) {
                    $('.bodypayment table tbody').html(data);
                    $('#loading').hide();
                }
            });
        })


        var toalAmtApply = 0.00;
        $('#selectAll').click(function(e) {
            var table = $(e.target).closest('table');
            $('td input:checkbox', table).prop('checked', this.checked);

            if ($(this).prop('checked') == true) {
                $('.paymentCredit').prop('disabled',false);
                $('.exchange_amount').prop('disabled',false);
                $('.input-due-amt').prop('disabled',false);
                toalAmtApply = 0.00;
                exchangeAmountToApply = 0.00;
                $('tbody tr').each(function(k, v) {
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
                $('.paymentCredit').prop('disabled',true);
                $('.exchange_amount').prop('disabled',true);
                $('.input-due-amt').prop('disabled',true);
                $('tbody tr').each(function(k, v) {
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
        var exchangeAmountToApply = 0.00;
        $(document).on('click', '.singlecheckbox', function() {

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
                $('#paymentCredit-' + id).prop('disabled',false);
                $('#exchange_amount-' + id).prop('disabled',false);
                $('#due-amt-fill-' + id).prop('disabled',false);
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
                $('#paymentCredit-' + id).prop('disabled',true);
                $('#exchange_amount-' + id).prop('disabled',true);
                $('#due-amt-fill-' + id).prop('disabled',true);
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

        var exchangeAmountToApply = 0.00;
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
            var urlztnn = '<?php echo url("upsinvoicepayment/getcurrencyratesection"); ?>';
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
</script>
@stop