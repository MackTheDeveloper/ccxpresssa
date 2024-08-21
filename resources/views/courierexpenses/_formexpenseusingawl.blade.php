@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Expense' : 'Add Expense'; ?>
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Expense' : 'Add Expense'; ?></h1>
</section>
<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="box box-success">
        <div class="box-body">


            <?php
            $actionUrl = route('storeexpenseusingawl');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <input type="hidden" name="flag" value="<?php echo $flag; ?>">
            <input type="hidden" name="bl_awb" value="" class="fbl_awb">
            <input type="hidden" name="voucher_number" class="voucher_number" value="<?php echo $voucherNo; ?>">
            <input type="hidden" class="count_expense" name="count_expense" value="1">
            <div class="expensemaincontainer">
                <div class="col-md-12">
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('exp_date') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('exp_date', 'Date', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('exp_date', date('Y-m-d'), ['class' => 'form-control datepicker fexpdate', 'placeholder' => 'Enter Date']); ?>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('file_number', 'No. Dossier/ File No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('file_number', $dataFileNumber, $cargoId, ['class' => 'form-control selectpicker ffilenumber', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('voucher_number') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('voucher_number', 'Voucher No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <span class="form-control">#<span class="voucher_number-span"><?php echo $voucherNo; ?></span></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('currency') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('currency', 'Currency', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('currency', Config::get('app.currency'), '', ['class' => 'form-control selectpicker']); ?>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-12">
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('cargo_id') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('cargo_id', 'AWB / BL No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <?php echo Form::select('cargo_id', $dataAwbNos, $cargoId, ['class' => 'form-control fawb', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('consignee') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('consignee', 'Consignataire / Consignee', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('consignee', '', ['class' => 'form-control', 'placeholder' => '']); ?>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('shipper') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('shipper', 'Expediteur / Shipper', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('shipper', '', ['class' => 'form-control', 'placeholder' => '']); ?>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="col-md-12">
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('billing_party', $dataBilligParty, '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('note') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('note', 'Note', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::textarea('note', '', ['class' => 'form-control', 'rows' => 2]); ?>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('cash_credit_account') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('cash_credit_account', 'Cash/Credit', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('cash_credit_account', $cashCredit, '', ['class' => 'form-control selectpicker fcashcredit', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                            <div class="col-md-12 balance-div" style="display: none;text-align: center;">
                                <span><b>Balance </b>$</span><span class="cash_credit_account_balance"></span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="expensesubcontainer col-md-12">
                    <span style="width: 100%;text-align: left;display: none;margin-bottom: 10px;" class="btn btn-success spanexpense spanexpense-0"><span class="fa fa-plus fa-plus-expense" data-faplusid="0" id="fa-plus-0" style="float: left;padding: 5px;"></span><span style="float: right;" class="remove-sec" data-removeid="0">Remove</span></span>
                    <div id="container-0" class="allcontainer">
                        <div class="col-md-12" style="width: 1078px;overflow-x: scroll;">
                            <table class="table table-expense" style="width: 1200px;">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;">Sr. No.</th>
                                        <th style="width: 10%;">Cost</th>
                                        <th style="width: 22%;">Description</th>
                                        <th style="width: 8%;">Amount</th>
                                        <th style="width: 11%;">Paid To</th>
                                        <th style="width: 4%;">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr id="0">
                                        <td style="text-align: center;">1</td>
                                        <td><?php echo Form::select('expenseDetails[expense_type][0]', $dataCost, '', ['class' => 'form-control expense_type selectpicker fexpense_type', 'data-live-search' => 'true', 'id' => 'expense_type-0', 'data-expense_type' => '0', 'placeholder' => 'Select ...', 'data-container' => '.expensesubcontainer']); ?></td>
                                        <td><?php echo Form::textarea('expenseDetails[description][0]', '', ['class' => 'form-control cvalidation', 'id' => 'description-0', 'rows' => 1]); ?></td>
                                        <td><?php echo Form::text('expenseDetails[amount][0]', '0.00', ['class' => 'form-control cvalidation famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?></td>
                                        <td><?php echo Form::select('expenseDetails[paid_to][0]', $allUsers, '', ['id' => 'paid_to-0', 'class' => 'form-control selectpicker fassignee', 'data-live-search' => 'true', 'data-container' => '.expensesubcontainer']); ?></td>
                                        <td><a href="javascript:void(0)" data-cid="0" class='btn btn-success btn-xs addmoreexpense'>+</a></td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
                <div class="col-md-12" style="text-align: center;">

                    <div class="form-group" style="padding-top: 28px;">
                        <button type="submit" id="CreateExpenseFormButton" class="btn btn-success btn-prime white btn-flat">Save</button>

                        <button type="submit" id="CreateExpenseFormButtonSaveNext" class="btn btn-success btn-prime white btn-flat">Save & Next</button>

                        <?php
                        $dataCargo = DB::table('cargo')->where('id', $cargoId)->first();
                        if ($flagFromWhere == 'flagFromListing')
                            $cancleRoute = route('cargoall');
                        elseif ($flagFromWhere == 'flagFromView')
                            $cancleRoute = route('viewcargo', [$cargoId, $dataCargo->cargo_operation_type]);
                        else
                            $cancleRoute = route('expenses');
                        ?>
                        <a class="btn btn-danger" href="<?php echo $cancleRoute; ?>" title="">Cancel</a>

                    </div>

                </div>

            </div>

            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
<?php
$datasConsignee = App\Clients::getClients();
?>
@section('page_level_js')
<script type="text/javascript">
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46 && charCode != 45) {
            return false;
        }
        return true;
    }
    $(document).ready(function() {
        var counter = 0;
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        $("#consignee").autocomplete({
            source: <?php echo $datasConsignee; ?>,
            minLength: 1,
        });
        $("#shipper").autocomplete({
            source: <?php echo $datasConsignee; ?>,
            minLength: 1,
        });
        //$('.expense_type').change(function(){
        $(document).on("change", "select.expense_type", function(e) {
            $('#loading').show();
            var id = $(this).data('expense_type');
            var costId = $('#expense_type-' + id).val();
            if (costId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzt = '<?php echo url("costs/getcostdata"); ?>';
                $.ajax({
                    url: urlzt,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'costId': costId
                    },
                    success: function(data) {
                        $('#loading').hide();
                        $('#description-' + id).val(data.costName);
                    }
                });
            } else {
                $('#loading').hide();
                $('#description-' + id).val('');
            }
        });
        <?php if ($cargoId) { ?>
            $('.fbl_awb').val($("#cargo_id option:selected").html());
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlznn = '<?php echo url("cargo/getcargodata"); ?>';
            $.ajax({
                url: urlznn,
                type: 'POST',
                dataType: "json",
                data: {
                    'cargoId': '<?php echo $cargoId; ?>'
                },
                success: function(data) {
                    $('#consignee').val(data.consigneeName);
                    $('#shipper').val(data.shipperName);
                }
            });
        <?php } ?>
        $('#file_number').change(function() {
            $('#loading').show();
            if ($(this).val() != '') {
                $('#cargo_id').val($(this).val());
                var ab = $("#cargo_id option:selected").html();
                $('.fbl_awb').val(ab);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzt = '<?php echo url("expense/getexpensedataoftoday"); ?>';
                $.ajax({
                    url: urlzt,
                    type: 'POST',
                    data: {
                        'courierId': $(this).val(),
                        'flag': '<?php echo "cargo" ?>'
                    },
                    success: function(data) {
                        $('#loading').hide();
                        $('.tableExpenses').html(data);
                    }
                });
                var urlzn = '<?php echo url("cargo/getcargodata"); ?>';
                $.ajax({
                    url: urlzn,
                    type: 'POST',
                    dataType: "json",
                    data: {
                        'cargoId': $(this).val()
                    },
                    success: function(data) {
                        $('#consignee').val(data.consigneeName);
                        $('#shipper').val(data.shipperName);
                    }
                });
                var urlz = '<?php echo url("expense/getcargofilenumberforprint"); ?>';
                $.ajax({
                    url: urlz,
                    type: 'POST',
                    data: {
                        'cargoId': $(this).val()
                    },
                    success: function(data) {
                        $('#printdbtn').attr('href', data);
                    }
                });
            } else {
                $('#cargo_id').val('');
                $('#consignee').val('');
                $('#shipper').val('');
                $('#loading').hide();
            }
        })
        $('#createExpenseForm').on('submit', function(event) {
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 1500);
            $('.ffilenumber').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.famount').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcashcredit').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });


            //$('#loading').hide();
        });

        $('#createExpenseForm').validate({
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var createExpenseForm = $("#createExpenseForm");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("expense/storeexpenseusingawl"); ?>';
                $.ajax({
                    url: urlz,
                    async: false,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $('#loading').show();
                        setTimeout(function() {
                            $("#loading").hide();
                        }, 1500);
                        $('.selectpicker').selectpicker('refresh');
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Expense has been added successfully.'
                        });

                    },
                });

                var urlzte = '<?php echo url("cashcredit/getbalance"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    type: 'POST',
                    data: {
                        'tId': $('#cash_credit_account').val()
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        $('.balance-div').show();

                        var blnc = parseInt(balance).toFixed(2);
                        $('.cash_credit_account_balance').html(blnc);
                    }
                });

                var submitButtonName = $(this.submitButton).attr("id");
                if (submitButtonName == 'CreateExpenseFormButtonSaveNext') {
                    $('.ffilenumber').removeClass('disableexpfld');
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 1500);
                    $('#createExpenseForm').find('input:text').val('');
                    $('#createExpenseForm').find('select').val('');
                    $('#createExpenseForm').find('textarea').val('');

                    $('.balance-div').hide();
                    $('.cash_credit_account_balance').html('');

                    $('.table-expense tbody tr').each(function(k, v) {
                        if ($(this).attr('id') != 0)
                            $(this).remove();
                    })
                    $("#currency").val($("#currency option:first").val());
                    $("#paid_to-0").val($("#paid_to-0 option:first").val());
                    $('.count_expense').val('1');
                    var urlznt = '<?php echo url("expense/generatevoucheronsavenext"); ?>';
                    $.ajax({
                        url: urlznt,
                        type: 'POST',
                        data: formData,
                        success: function(data) {
                            $('.voucher_number').val(data);
                            $('.voucher_number-span').text(data);
                        },
                    });
                    $('.selectpicker').selectpicker('refresh');

                } else {
                    $('.ffilenumber').addClass('disableexpfld');
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "file_number") {
                    var pos = $('.ffilenumber button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "cash_credit_account") {
                    var pos = $('.fcashcredit button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "expense_type") {
                    var pos = $('.fexpense_type button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });


        $('.allcontainer').on("click", ".addmoreexpense", function(e) {
            $('.count_expense').val(parseInt($('.count_expense').val()) + 1);
            counter = counter + 1;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlzte = '<?php echo url("expense/addmoreexpense"); ?>';
            $.ajax({
                url: urlzte,
                type: 'POST',
                data: {
                    'counter': counter
                },
                success: function(data) {
                    $('#loading').hide();
                    $('.table-expense tbody').append(data);
                }
            });
        });
        $(document).on('click', '.removeexpense', function() {
            $('.count_expense').val(parseInt($('.count_expense').val()) - 1);
            $('.table-expense tbody tr#' + $(this).data('cid')).remove();
        })


        $('#cash_credit_account').change(function() {
            $('#loading').show();
            var tId = $(this).val();
            if (tId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzte = '<?php echo url("cashcredit/getbalance"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    type: 'POST',
                    data: {
                        'tId': tId
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        $('.balance-div').show();

                        var blnc = parseInt(balance).toFixed(2);
                        $('.cash_credit_account_balance').html(blnc);
                    }
                });
            } else {
                $('#loading').hide();
                $('.balance-div').hide();
            }

        })
    });
</script>
@stop