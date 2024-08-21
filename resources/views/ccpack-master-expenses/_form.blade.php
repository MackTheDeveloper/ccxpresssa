@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update CCPack Master File Expense Request' : 'Add CCPack Master File Expense Request'; ?>
@stop

@section('breadcrumbs')
@include('menus.ccpack-expense')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update CCPack Master File Expense Request' : 'Add CCPack Master File Expense Request'; ?></h1>
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
            <input type="hidden" name="bl_awb" value="" class="fbl_awb">
            <input type="hidden" name="voucher_number" class="voucher_number" value="<?php echo $voucherNo; ?>">
            <input type="hidden" class="count_expense" name="count_expense" value="1">
            <div class="expensemaincontainer">
                <div class="col-md-12">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('exp_date') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('exp_date', 'Date', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('exp_date', date('d-m-Y'), ['class' => 'form-control datepicker fexpdate', 'placeholder' => 'Enter Date']); ?>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('file_number', 'No. Dossier/ File No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('file_number', $dataFileNumber, $ccpackMasterId, ['class' => 'form-control selectpicker ffilenumber', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('voucher_number') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('voucher_number', 'Voucher No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <span class="form-control">#<span class="voucher_number-span"><?php echo $voucherNo; ?></span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('ccpack_master_id') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('ccpack_master_id', 'AWB / BL No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <?php echo Form::select('ccpack_master_id', $dataAwbNos, $ccpackMasterId, ['class' => 'form-control fawb', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('consignee') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('consignee', 'Consignataire / Consignee', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('consignee', '', ['class' => 'form-control', 'placeholder' => '']); ?>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4 shipperdiv">
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
                    <div class="col-md-8">
                        <div class="form-group {{ $errors->has('note') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('note', 'Note', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::textarea('note', '', ['class' => 'form-control', 'rows' => 2]); ?>
                            </div>

                        </div>
                    </div>
                    <?php if (checkloggedinuserdata() != 'Agent') { ?>
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('cash_credit_account') ? 'has-error' :'' }}">
                                <div class="col-md-12">
                                    <?php echo Form::label('cash_credit_account', 'Cash/Bank', ['class' => 'control-label']); ?>
                                </div>
                                <div class="col-md-12">
                                    <?php echo Form::select('cash_credit_account', $cashCredit, '', ['class' => 'form-control selectpicker fcashcredit', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                                </div>
                                <div class="col-md-12 balance-div" style="display: none;text-align: center;">
                                    <span><b>Balance</b> </span><span class="cash_credit_account_balance"></span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="col-md-12">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('cashier_id') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('cashier_id', 'Cashier', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('cashier_id', $cashier, $model->cashier_id, ['class' => 'form-control selectpicker fcashier_id', 'data-live-search' => 'true', 'id' => 'admin_managers', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" style="pointer-events: none;opacity: 0.5">
                        <div class="form-group {{ $errors->has('currency') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('currency', 'Currency', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('currency', $currency, $model->currency, ['class' => 'form-control selectpicker']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-12">
                    <?php if (checkloggedinuserdata() == 'Agent') { ?>
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('admin_manager_role') ? 'has-error' :'' }}">
                                <div class="col-md-12">
                                    <?php echo Form::label('admin_manager_role', 'Admin/Manager', ['class' => 'control-label']); ?>
                                </div>
                                <div class="col-md-12">
                                    <?php echo Form::select('admin_manager_role', $adminManagersRole, '', ['class' => 'form-control selectpicker fcashcredit', 'data-live-search' => 'true']); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="col-md-12">
                                <?php echo Form::label('expense_type', 'Type', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12 consolidate_flag-md-6">
                                <?php
                                echo Form::radio('expense_type', '1', 'checked', ['id' => 'cash_type']);
                                echo Form::label('cash_type', 'Cash');
                                echo Form::radio('expense_type', '2', '', ['id' => 'credit_type']);
                                echo Form::label('credit_type', 'Credit');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="col-md-12">
                                <?php echo Form::label('vendor_bill_number', 'Vendor Bill Number', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12 consolidate_flag-md-6">
                            <?php echo Form::text('vendor_bill_number',$model->vendor_bill_number,['class'=>'form-control','placeholder' => 'Enter Vendor Bill Number']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="expensesubcontainer col-md-12">
                    <span style="width: 100%;text-align: left;display: none;margin-bottom: 10px;" class="btn btn-success spanexpense spanexpense-0"><span class="fa fa-plus fa-plus-expense" data-faplusid="0" id="fa-plus-0" style="float: left;padding: 5px;"></span><span style="float: right;" class="remove-sec" data-removeid="0">Remove</span></span>
                    <div id="container-0" class="allcontainer">
                        <div class="col-md-12" style="">
                            <table class="table table-expense" style="">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;">Sr. No.</th>
                                        <th style="width: 10%;">Cost <button id="addNewItems" value="<?php echo url('items/addnewitem', ['cost-items']) ?>" type="button" class="addnewitems" data-module='Cost Item'>Add Cost Item</button></th>
                                        <th style="width: 22%;">Description</th>
                                        <th style="width: 8%;">Amount</th>
                                        <th style="width: 11%;">Paid To <button id="addNewItems" value="<?php echo url('items/addnewitem', ['vendor']) ?>" type="button" class="addnewitems" data-module='Vendor'>Add Vendor</button></th>
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

                <input type="hidden" name="flagBtn" class="flagBtn" id="flagBtn" value="">

                <div class="col-md-12" style="text-align: center;">

                    <div class="form-group" style="padding-top: 28px;">
                        <button type="submit" id="CreateExpenseFormButton" class="btn btn-success btn-prime white btn-flat">Save</button>

                        <button type="submit" id="CreateExpenseFormButtonSaveNext" class="btn btn-success btn-prime white btn-flat">Save & Next</button>

                        <button type="submit" id="CreateExpenseFormButtonSavePrint" class="btn btn-success btn-prime white btn-flat">Save & Print</button>

                        <?php
                        if ($flagFromWhere == 'flagFromListing')
                            $cancleRoute = route('ccpack-master');
                        elseif ($flagFromWhere == 'flagFromView')
                            $cancleRoute = route('viewdetailsccpackmaster', [$ccpackMasterId]);
                        else
                            $cancleRoute = route('ccpackmasterexpenses');
                        ?>
                        <a class="btn btn-danger" href="<?php echo $cancleRoute; ?>" title="">Cancel</a>

                    </div>

                </div>

            </div>

            {{ Form::close() }}
        </div>
    </div>
</section>

<div id="modalAddNewItems" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title modal-title-block text-center primecolor">Add New</h3>
            </div>
            <div class="modal-body" id="modalContentAddNewItems" style="overflow: hidden;">
            </div>
        </div>

    </div>
</div>

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
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        var counter = 0;
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
        <?php if ($ccpackMasterId) { ?>
            $('.fbl_awb').val($("#ccpack_master_id option:selected").html());
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlznn = '<?php echo url("ccpack-master/getccpackmasterdata"); ?>';
            $.ajax({
                url: urlznn,
                type: 'POST',
                dataType: "json",
                data: {
                    'ccpackMasterId': '<?php echo $ccpackMasterId; ?>'
                },
                success: function(data) {
                    $('#consignee').val(data.consigneeName);
                    $('#shipper').val(data.shipperName);
                    $('#billing_party').val(data.billing_party);
                    $('.selectpicker').selectpicker('refresh');
                }
            });
        <?php } ?>
        $('#file_number').change(function() {
            $('#loading').show();
            if ($(this).val() != '') {
                $('#ccpack_master_id').val($(this).val());
                var ab = $("#ccpack_master_id option:selected").html();
                $('.fbl_awb').val(ab);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzn = '<?php echo url("ccpack-master/getccpackmasterdata"); ?>';
                $.ajax({
                    url: urlzn,
                    type: 'POST',
                    dataType: "json",
                    data: {
                        'ccpackMasterId': $(this).val()
                    },
                    success: function(data) {
                        $('#consignee').val(data.consigneeName);
                        $('#shipper').val(data.shipperName);
                        $('#billing_party').val(data.billing_party);
                        $('#billing_party').selectpicker('refresh');
                        $('#loading').hide();
                    }
                });
            } else {
                $('#ccpack_master_id').val('');
                $('#consignee').val('');
                $('#shipper').val('');
                $('#billing_party').val('');
                $('#billing_party').selectpicker('refresh');
                $('#loading').hide();
            }
        })
        $('#createExpenseForm').on('submit', function(event) {
            $('#loading').show();
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
            $('.fcashier_id').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $("#loading").hide();
        });

        $('#createExpenseForm').validate({
            rules: {
                "cash_credit_account": {
                    checkCurrency: true
                }
            },
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var bNo = $('.voucher_number').val();
                var urlCheck = '<?php echo url("expense/checkexistingvoucherno"); ?>';
                var goAhead = 1;
                $.ajax({
                    url: urlCheck,
                    async: false,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'billNo': bNo,
                        'flag': 'cargo'
                    },
                    success: function(data) {
                        if (data.exist == 1) {
                            if (confirm("Expense with Voucher No #" + bNo + " already created. Do you want to continue with incremented number ?")) {
                                $('.voucher_number-span').text(data.billNo);
                                $('.voucher_number').val(data.billNo);
                            } else {
                                goAhead = 0;
                                window.location.href = '<?php echo route("ccpackmasterexpenses") ?>';
                                event.preventDefault();
                                return false;
                            }
                        }
                    }
                })

                if (goAhead == 0) {
                    return false;
                }

                var submitButtonName = $(this.submitButton).attr("id");
                if ($(this.submitButton).attr("id") == 'CreateExpenseFormButtonSavePrint')
                    $('.flagBtn').val('saveprint');
                else
                    $('.flagBtn').val('');


                var createExpenseForm = $("#createExpenseForm");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("ccpack-master/expense/store"); ?>';
                $.ajax({
                    url: urlz,
                    async: false,
                    type: 'POST',
                    data: formData,
                    success: function(data) {

                        $('#loading').hide();
                        console.log(data);
                        $('.selectpicker').selectpicker('refresh');
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Expense has been added successfully.'
                        });

                        if (submitButtonName == 'CreateExpenseFormButtonSavePrint') {
                            window.open(data, '_blank');
                        }

                        <?php if ($flagFromWhere == 'flagFromView') { ?>
                            window.location.href = '<?php echo route('viewdetailsccpackmaster', [$ccpackMasterId]) ?>';
                        <?php } else { ?>
                            window.location.href = '<?php echo route("ccpackmasterexpenses") ?>';
                        <?php } ?>

                    },
                });

                var urlzte = '<?php echo url("cashcredit/getbalance"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'tId': $('#cash_credit_account').val()
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        $('.balance-div').show();

                        var blnc = '(' + balance.currency_code + ') ' + parseInt(balance.available_balance).toFixed(2);
                        $('.cash_credit_account_balance').html(blnc);
                    }
                });


                if (submitButtonName == 'CreateExpenseFormButtonSaveNext') {
                    window.location.href = '<?php echo route("createccpackmasterexpense") ?>';
                } else {
                    $('.ffilenumber').addClass('disableexpfld');
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "file_number") {
                    var pos = $('.ffilenumber button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "cashier_id") {
                    var pos = $('.fcashier_id button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $.validator.addMethod('checkCurrency', function(value, element) {

            var csrf = "<?php echo csrf_token(); ?>"

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrf
                }
            });
            var vendor = $('#paid_to-0').val();
            var result = false;
            //alert(vendor);
            var urlz = '<?php echo url('upsexpense/checkCurrency'); ?>';
            $.ajax({
                async: false,
                type: 'POST',
                url: urlz,
                data: {
                    'account': value,
                    'vendor': vendor
                },
                success: function(data) {
                    result = (data == 0) ? true : false;
                },
            });
            return result;
        }, "You can't use this account! The currency of the account must match with vendor's currency.");

        $('.allcontainer').on("click", ".addmoreexpense", function(e) {
            $('#loading').show();
            var selectedVendor = $("#paid_to-0").val();
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
                    'counter': counter,
                    'selectedVendor': selectedVendor
                },
                success: function(data) {
                    $('#loading').hide();
                    $('.table-expense tbody').append(data);
                }
            });
        });
        $(document).on('click', '.removeexpense', function() {
            $('#loading').show();
            $('.count_expense').val(parseInt($('.count_expense').val()) - 1);
            $('.table-expense tbody tr#' + $(this).data('cid')).remove();
            $('#loading').hide();
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
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'tId': tId
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        $('.balance-div').show();

                        var blnc = '(' + balance.currency_code + ') ' + parseInt(balance.available_balance).toFixed(2);
                        $('.cash_credit_account_balance').html(blnc);
                    }
                });
            } else {
                $('#loading').hide();
                $('.balance-div').hide();
            }

        })


        var vendor = $('#paid_to-0').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var urlzte = '<?php echo url("vendors/getvendordata"); ?>';
        $.ajax({
            async: false,
            url: urlzte,
            dataType: "json",
            type: 'POST',
            data: {
                'vendor': vendor
            },
            success: function(balance) {
                $('#currency').val(balance.currency);
            }
        });
        $('#paid_to-0').on('change', function() {
            var vendor = $('#paid_to-0').val();
            $('select.fassignee').each(function() {
                $(this).val(vendor);
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlzte = '<?php echo url("vendors/getvendordata"); ?>';
            $.ajax({
                async: false,
                url: urlzte,
                dataType: "json",
                type: 'POST',
                data: {
                    'vendor': vendor
                },
                success: function(balance) {
                    $('#currency').val(balance.currency);
                }
            });
            $('.selectpicker').selectpicker('refresh');
        });
    });
</script>
@stop