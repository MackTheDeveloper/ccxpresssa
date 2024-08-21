@extends('layouts.custom')
@section('title')
<?php echo $model->expense_id ? 'Update UPS File Expense Request' : 'Create UPS File Expense Request'; ?>
@stop

<?php
if (!empty($model->expense_id)) {
    $counter = App\ExpenseDetails::where('expense_id', $model->expense_id)->where('deleted', '0')->count();
} else {
    $counter = 0;
}
?>

@section('breadcrumbs')
@include('menus.ups-expense')
@stop


@section('content')
<section class="content-header">
    <h1><?php echo $model->expense_id ? 'Update UPS File Expense Request' : 'Add UPS File Expense Request'; ?></h1>
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
            <input type="hidden" name="bl_awb" value="<?php echo $model->bl_awb ?>" class="fbl_awb">
            <input type="hidden" name="voucher_number" value="<?php echo $model->voucher_number; ?>">
            <input type="hidden" class="count_expense" name="count_expense" value="<?php echo $counter; ?>">


            <div class="expensemaincontainer">
                <div class="col-md-12">

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('exp_date') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('exp_date', 'Date', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::text('exp_date', date('d-m-Y', strtotime($model->exp_date)), ['class' => 'form-control datepicker fexpdate', 'placeholder' => 'Enter Date']); ?>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
                            <div class="col-md-12 required">
                                <?php echo Form::label('file_number', 'No. Dossier/ File No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <?php echo Form::select('file_number', $dataFileNumber, $model->ups_details_id, ['class' => 'form-control selectpicker ffilenumber', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('voucher_number') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('voucher_number', 'Voucher No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <span class="form-control">#<?php echo $model->voucher_number; ?></span>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-md-12">

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('ups_details_id') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('ups_details_id', 'AWB / BL No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12" style="pointer-events: none;opacity: 0.5">
                                <?php echo Form::select('ups_details_id', $dataAwbNos, $model->ups_details_id, ['class' => 'form-control fawb', 'placeholder' => 'Select ...']); ?>
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
                    <div class="col-md-4">
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
                                <?php echo Form::textarea('note', $model->note, ['class' => 'form-control', 'rows' => 2]); ?>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('cash_credit_account') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('cash_credit_account', 'Cash/Bank', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('cash_credit_account', $cashCredit, $model->cash_credit_account, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                            <div class="col-md-12 balance-div" style="display: block;text-align: center;">
                                <span><b>Balance</b> </span><span class="cash_credit_account_balance"><?php echo App\CashCredit::getbalance($model->cash_credit_account); ?></span>
                            </div>
                        </div>
                    </div>

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
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="col-md-12">
                                <?php echo Form::label('expense_type', 'Type', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12 consolidate_flag-md-6">
                            <?php
                                echo Form::radio('expense_type', '1', $model->expense_type == '1'  ? 'checked' : '', ['id' => 'cash_type']);
                                echo Form::label('cash_type', 'Cash');
                                echo Form::radio('expense_type', '2', $model->expense_type == '2'  ? 'checked' : '', ['id' => 'credit_type']);
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

                <div class="expensesubcontainer">
                    <span style="width: 100%;text-align: left;display: none;margin-bottom: 10px;" class="btn btn-success spanexpense spanexpense-0"><span class="fa fa-plus fa-plus-expense" data-faplusid="0" id="fa-plus-0" style="float: left;padding: 5px;"></span><span style="float: right;" class="remove-sec" data-removeid="0">Remove</span></span>
                    <div id="container-0" class="allcontainer">
                        <div class="col-md-12" style="">

                            <table class="table table-expense" style="">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;">Sr. No.</th>
                                        <th style="width: 10%;">Cost <button id="addNewItems" value="<?php echo url('items/addnewitem', ['cost-items']) ?>" type="button" class="addnewitems">Add New</button></th>
                                        <th style="width: 22%;">Description</th>
                                        <th style="width: 8%;">Amount</th>
                                        <th style="width: 11%;">Paid To <button id="addNewItems" value="<?php echo url('items/addnewitem', ['vendor']) ?>" type="button" class="addnewitems" data-module='Vendor'>Add Vendor</button></th>
                                        <th style="width: 4%;">Action</th>
                                    </tr>
                                </thead>


                                <tbody>
                                    <?php if (empty($dataExpenseDetails)) { ?>
                                        <tr id="0">
                                            <td style="text-align: center;">1</td>
                                            <td><?php echo Form::select('expenseDetails[expense_type][0]', $dataCost, '', ['class' => 'form-control expense_type selectpicker fexpense_type', 'data-live-search' => 'true', 'id' => 'expense_type-0', 'data-expense_type' => '0', 'placeholder' => 'Select ...', 'data-container' => '.expensesubcontainer']); ?></td>
                                            <td><?php echo Form::textarea('expenseDetails[description][0]', '', ['class' => 'form-control cvalidation', 'id' => 'description-0', 'rows' => 1]); ?></td>
                                            <td><?php echo Form::text('expenseDetails[amount][0]', '', ['class' => 'form-control cvalidation famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?></td>
                                            <td><?php echo Form::select('expenseDetails[paid_to][0]', $allUsers, '', ['id' => 'paid_to-0', 'class' => 'form-control selectpicker fassignee', 'data-live-search' => 'true', 'data-container' => '.expensesubcontainer']); ?></td>
                                            <td><a href="javascript:void(0)" data-cid="0" class='btn btn-success btn-xs addmoreexpense'>+</a></td>
                                        </tr>
                                        <?php } else {
                                        $i = 0;
                                        foreach ($dataExpenseDetails as $k => $v) {
                                            $disabled = $i == 0 ? false : true; ?>

                                            <tr id="<?php echo $i; ?>">
                                                <td style="text-align: center;"><?php echo $i + 1; ?></td>
                                                <td><?php echo Form::select("expenseDetails[expense_type][$i]", $dataCost, $v->expense_type, ['class' => 'form-control expense_type selectpicker fexpense_type', 'data-live-search' => 'true', 'id' => "expense_type-$i", 'data-expense_type' => $i, 'placeholder' => 'Select ...', 'data-container' => '.expensesubcontainer']); ?></td>
                                                <td><?php echo Form::textarea("expenseDetails[description][$i]", $v->description, ['class' => 'form-control cvalidation', 'id' => "description-$i", 'rows' => 1]); ?></td>
                                                <td><?php echo Form::text("expenseDetails[amount][$i]", $v->amount, ['class' => 'form-control cvalidation famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?></td>
                                                <td><?php echo Form::select("expenseDetails[paid_to][$i]", $allUsers, $v->paid_to, ['id' => 'paid_to-' . $i, 'class' => 'form-control selectpicker fassignee', 'data-live-search' => 'true', 'data-container' => '.expensesubcontainer', 'disabled' => $disabled]); ?></td>
                                                <td>
                                                    <a href="javascript:void(0)" data-cid="<?php echo $i; ?>" class='btn btn-success btn-xs addmoreexpense'>+</a>
                                                    <?php if ($i != 0) { ?>
                                                        <a style='' href='javascript:void(0)' data-cid="<?php echo $i; ?>" class='btn btn-danger btn-xs removeexpense' id=<?php echo $i; ?>>-</a>
                                                    <?php } ?>

                                                </td>
                                            </tr>
                                    <?php $i++;
                                        }
                                    } ?>
                                </tbody>

                            </table>
                            <div class="col-md-3" style="background: #f3f3f3;float: right;padding: 5px;">
                                <div style="width: 60%;float: left;font-weight: bold;">Total Expense</div>
                                <div style="width: 40%;float: left;text-align: right;"><?php echo app('App\Expense')::getExpenseTotal($model->expense_id);  ?></div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="text-align: center;">

                    <div class="form-group" style="padding-top: 28px;">
                        <button type="submit" id="CreateExpenseFormButton" class="btn btn-success btn-prime white btn-flat">Update</button>
                        <?php
                        if ($flagFromWhere == 'flagFromView')
                            $cancleRoute = route('viewdetailsups', [$model->ups_details_id]);
                        else
                            $cancleRoute = route('upsexpenses');
                        ?>
                        <a class="btn btn-danger" href="<?php echo $cancleRoute; ?>" title="">Cancel</a>
                    </div>

                </div>

            </div>

            {{ Form::close() }}

            <div style="text-align: right;margin-left: 20px;margin-top: 20px;display: none;">
                <a title="Click here to print" id="printdbtn" target="_blank" href=""><i class="fa fa-print btn btn-primary"></i></a>
            </div>


        </div>
    </div>
</section>
<div id="modalAddNewItems" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">Add New</h3>
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

        <?php if (empty($model->billing_party) || $model->expense_id) { ?>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var upsId = '<?php echo $model->ups_details_id; ?>';
            var urlznn = '<?php echo url("ups/getupsdata"); ?>';
            $.ajax({
                url: urlznn,
                type: 'POST',
                dataType: "json",
                data: {
                    'upsId': '<?php echo $model->ups_details_id; ?>'
                },
                success: function(data) {
                    $('#consignee').val(data.consigneeName);
                    $('#shipper').val(data.shipperName);
                    $('#billing_party').val(data.billing_party);
                    $('.selectpicker').selectpicker('refresh');

                    var html = '';
                    html += '<option selected value="' + data.id + '">' + data.awb_number + '</option>';

                    $('#ups_details_id').html(html);
                    $('#ups_details_id').selectpicker('refresh');

                    //$('#ups_details_id').val(upsId);
                    var ab = $("#ups_details_id option:selected").html();
                    $('.fbl_awb').val(ab);
                }
            });
        <?php } ?>

        $('#file_number').change(function() {
            $('#loading').show();
            if ($(this).val() != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var upsId = $(this).val();
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

                var urlzn = '<?php echo url("ups/getupsdata"); ?>';
                $.ajax({
                    url: urlzn,
                    type: 'POST',
                    dataType: "json",
                    data: {
                        'upsId': $(this).val()
                    },
                    success: function(data) {
                        $('#consignee').val(data.consigneeName);
                        $('#shipper').val(data.shipperName);
                        $('#billing_party').val(data.billing_party);
                        $('.selectpicker').selectpicker('refresh');

                        var html = '';
                        html += '<option selected value="' + data.id + '">' + data.awb_number + '</option>';

                        $('#ups_details_id').html(html);
                        $('#ups_details_id').selectpicker('refresh');

                        //$('#ups_details_id').val(upsId);
                        var ab = $("#ups_details_id option:selected").html();
                        $('.fbl_awb').val(ab);
                    }
                });
            } else {
                $('#ups_details_id').val('');
                $('#consignee').val('');
                $('#shipper').val('');
                $('#loading').hide();
            }
        })

        $('#createExpenseForm').on('submit', function(event) {
            // event.preventDefault();
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


            $('.ffilenumber').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcashier_id').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

        });
        $('#createExpenseForm').validate({
            submitHandler: function(form) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('#loading').show();
                var createExpenseForm = $("#createExpenseForm");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("upsexpense/updateupsexpense/$model->expense_id"); ?>';
                $.ajax({
                    url: urlz,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        console.log(data);
                        /*$('#expense_type').val('');
                        $('#amount').val('');
                        $('#consignee').val('');
                        $('#shipper').val('');
                        $('#billing_party').val('');*/
                        $('#loading').hide();
                        $('.selectpicker').selectpicker('refresh');
                        
                        <?php if ($flagFromWhere == 'flagFromView') { ?>
                            window.location.href = '<?php echo route('viewdetailsups', [$model->ups_details_id]) ?>';
                        <?php } else { ?>
                            window.location.href = '<?php echo route("upsexpenses") ?>';
                        <?php } ?>
                        /*Lobibox.notify('info', {
                                    size: 'mini',
                                    delay: 2000,
                                    rounded: true,
                                    delayIndicator: false,
                                    msg: 'Expense has been updated successfully.'
                                });*/
                        //console.log(data);
                        //window.location.href = "<?php echo route('upsexpenses'); ?>";

                        //var urlz1 = '<?php //echo url("cargo/viewcargo/$courierId/2#div_expenses"); 
                                        ?>';
                        //window.location.href = urlz1;

                        /*var urlztt = '<?php //echo url("expense/getexpensedataoftoday"); 
                                        ?>';

                        $.ajax({
                            url:urlztt,
                            type:'POST',
                            data:{'courierId':$('#file_number').val(),'flag':'<?php echo "cargo" ?>'},
                            success:function(data) {
                                    $('.tableExpenses').html(data);
                                    }
                            });*/
                    },
                });

            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "file_number") {
                    var pos = $('.ffilenumber button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "expense_type") {
                    var pos = $('.fexpense_type button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "cashier_id") {
                    var pos = $('.fcashier_id button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        var counter = 0;
        if ('<?php echo $counter; ?>' != 0) {
            counter = <?php echo $counter - 1; ?>;
        }

        $('.allcontainer').on("click", ".addmoreexpense", function(e) {
            $('#loading').show();
            $('.count_expense').val(parseInt($('.count_expense').val()) + 1);
            counter = counter + 1;
            var selectedVendor = $('#paid_to-0').val();
            if (counter == 0) {
                counter = 1;
            }
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
                    dataType: "json",
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