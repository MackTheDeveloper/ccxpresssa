@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Payment' : "Add" . ((!empty($billingParty) || !empty($fromMenu)) ? ' Bulk' : '') . " Payment"; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">

        <li class="widemenu">
            <a href="{{ route('invoices') }}">Invoice Listing</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('createinvoice') }}">Add Invoice</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('pendinginvoices') }}">Pending Invoices</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('invoices') }}">Invoice Report</a>
        </li>
        <li class="widemenu active">
            <a href="{{ route('addinvoicepayment') }}">Add Payment</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
@include('menus.cashier-warehouse-cargo-invoice')
@stop


@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Payment' : "Add" . ((!empty($billingParty) || !empty($fromMenu)) ? ' Bulk' : '') . " Payment"; ?></h1>
</section>
<section class="content">
    <div class="box box-success" style="float: left;">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('invoicepayment/update', $model->id);
            else
                $actionUrl = url('invoicepayment/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('client') ? 'has-error' :'' }}">
                        <?php echo Form::label('client', 'Billing Party', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6" style=<?php echo (!empty($billingParty) || !empty($cargoId)) ? "opacity:0.5;pointer-events:none;" : ""  ?>>
                            <?php echo Form::select('client', $allUsers, $model->client, ['class' => 'form-control selectpicker fclient', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('invoice_id') ? 'has-error' :'' }}">
                        <?php echo Form::label('invoice_id', 'Invoice Number', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6" style=<?php echo (!empty($billingParty) || !empty($cargoId)) ? "opacity:0.5;pointer-events:none;" : ""  ?>>
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


            <div class="bodypayment col-md-12" style="margin-top: 25px;display: none;">
                <table id="example" class="display table" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Description</th>
                            <th>Billing Party</th>
                            <th>Date</th>
                            <th>Currency</th>
                            <th class="alignright">Original Amount</th>
                            <th class="alignright">Due Amount</th>
                            <th class="alignright">Payment</th>
                            <th class="alignright">Exchange Payment</th>
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

                <div class="form-group col-md-12 btm-sub">

                    <button type="submit" class="btn btn-success">Receive Payment</button>

                    <a class="btn btn-danger" href="{{url('invoices')}}" title="">Cancel</a>
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

        $('#example1').DataTable({
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            },

        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        <?php if ($invoceId) { ?>
            $('#loading').show();
            $('#invoice_number').val($("#invoice_id option:selected").html());
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.bodypayment').show();
            $('#loading').show();
            var invoiceId = '<?php echo $invoceId ?>';
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
        <?php } ?>

        <?php if ($billingParty) { ?>
            $('#invoice_id option[value=""]').prop('selected', true);
            $('#invoice_id').selectpicker('refresh');
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            $('.bodypayment').show();
            $('#loading').show();
            var clientId = '<?php echo $billingParty ?>';
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlztnn = '<?php echo url("invoicepayment/getinvoicesofclient"); ?>';
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
            $('#loading').show();
            $('#client option[value=""]').prop('selected', true);
            $('#client').selectpicker('refresh');
            $('#invoice_number').val($("#invoice_id option:selected").html());
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            toalAmtApply = 0.00;
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

        $('#createInvoiceForm').on('submit', function(event) {

        });

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

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
                var urlztnn = '<?php echo url("cashierinvoicepayment/store"); ?>';
                $.ajax({
                    url: urlztnn,
                    async: false,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        window.location.href = "<?php echo route('cashierwarehouseinvoicesoffile'); ?>";
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

        $('#client').change(function() {
            $('#invoice_id option[value=""]').prop('selected', true);
            $('#invoice_id').selectpicker('refresh');
            $('.total-amt-apply').text('0.00');
            $('.total-amt-credit').text('0.00');
            $('.amount_received').val('0.00');
            toalAmtApply = 0.00;
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
        })

        var toalAmtApply = 0.00;
        $('#selectAll').click(function(e) {
            console.log("hello");
            var table = $(e.target).closest('table');
            $('td input:checkbox', table).prop('checked', this.checked);

            if ($(this).prop('checked') == true) {
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
        $(document).on('click', '.singlecheckbox', function() {

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

        $(document).on("focusout", ".input-due-amt", function(e) {
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