@extends('layouts.custom')

@section('title')
Administration Expense
@stop

@section('breadcrumbs')
@include('menus.aeropost-expense')
@stop

@section('content')
@if(Session::has('flash_message'))
<div class="alert alert-success flash-success">
    {{ Session::get('flash_message') }}
</div>
@endif
<section class="content-header">
    <h1>File Expense
        <?php //if($cargoExpenseData[0]->expense_request == 'Disbursement done') { 
        ?>
        <a style="float: right;padding-right: 20px;margin-top: 5px" title="Click here to print" target="_blank" href="{{ route('getprintsingleotherexpense',[$expenseId]) }}"><i class="fa fa-print" style="font-size: 25px"></i></a>
        <?php //} 
        ?>
        <a class="btn btn-success" style="float: right;margin-right: 1%;padding: 5px 25px;" title="Go Back" href="{{ route('otherexpenses') }}">Back</a>
    </h1>
</section>

<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms create-form" style="color: #636b6f">
            <?php if ($cargoExpenseData[0]->expense_request == 'Requested') { ?>
                <div class="col-md-12" style="float: right;">
                    <h4 style="color:red">Expense is still not approved, you can not make Disbursement.</h4>
                </div>
            <?php } else { ?>
                <div class="col-md-12" style="float: right;">
                    <?php $actionUrl = route('changeadministrationexpensestatusbycashier'); ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form create-form-expenenseinbasiccargo','id'=>'createExpenseForm','autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <input type="hidden" name="id" value="<?php echo $cargoExpenseData[0]->id; ?>">

                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('expense_request') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('expense_request', 'Status', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('expense_request', $expenseStatus, $cargoExpenseData[0]->expense_request != 'Approved' ? $cargoExpenseData[0]->expense_request : '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">

                        <div class="form-group {{ $errors->has('expense_request_status_note') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('expense_request_status_note', 'Notes', ['class' => 'control-label']); ?>
                            </div>

                            <div class="col-md-12">
                                <?php echo Form::textarea('expense_request_status_note', $cargoExpenseData[0]->expense_request != 'Approved' ? $cargoExpenseData[0]->expense_request_status_note : '', ['class' => 'form-control', 'rows' => 1]); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('cash_credit_account') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('cash_credit_account', 'Cash/Bank', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php echo Form::select('cash_credit_account', $cashCredit, $cargoExpenseData[0]->cash_credit_account, ['class' => 'form-control selectpicker fcashcredit', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            </div>
                            <div class="col-md-12 balance-div" style="display: block;text-align: center;">
                                <span><b>Balance</b> </span><span class="cash_credit_account_balance"><?php echo App\CashCredit::getbalance($cargoExpenseData[0]->cash_credit_account); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if ($cargoExpenseData[0]->expense_request != 'Disbursement done') { ?>
                        <div class="col-md-1">
                            <button style="margin-top: 35px" type="submit" class="btn btn-success">Submit</button>
                        </div>
                    <?php } ?>


                    {{ Form::close() }}
                </div>
            <?php } ?>

            <?php if (!empty($cargoExpenseData)) {
                $kv = 1;
                foreach ($cargoExpenseData as $ko => $vo) {
                    $vo = (object) $vo;
                    $cargoExpenseDetailsData = DB::table('other_expenses_details')->where('voucher_number', $vo->voucher_number)->where('expense_id', $vo->id)->where('deleted', 0)->orderBy('id', 'desc')->get();
            ?>

                    <?php if (!empty($cargoExpenseDetailsData)) {  ?>


                        <div class="mainblk" style="padding-bottom: 20px; margin: 0 auto;">

                            <div class="blockleft" style="margin-bottom: 20px; width: 100%; float: left; padding: 0 20px 0 20px; box-sizing: border-box; border-left: 1px dashed #ccc; border-right: 1px dashed #ccc;">

                                <div style="float: left;width: 100%; margin: 20px 0 30px 0;">
                                    <div style="text-align: left;float: left; width: 50%; font-size: 24px; font-weight: 600;text-transform: uppercase;">
                                        Chatelain Cargo Services
                                    </div>
                                    <div style="width: 50%; float: left;text-align: right; font-size: 18px; margin-top: 9px; color: #f30101;">
                                        <?php echo $vo->voucher_number; ?>
                                    </div>
                                </div>
                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                    <div style="float: left; width: 20%; font-weight: 600;">Date : </div>
                                    <div style="float: left;  width: 80%;  padding-bottom: 4px;">
                                        <?php echo date('d-m-Y', strtotime($vo->exp_date)); ?></div>
                                </div>
                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                    <div style="float: left;  width: 20%; font-weight: 600;">Cash/Bank : </div>
                                    <div style="float: left; width: 80%;"><?php $currencyData = App\CashCredit::getCashCreditData($vo->cash_credit_account);
                                                                            echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></div>
                                </div>
                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                    <div style="float: left;  width: 20%; font-weight: 600;">Expense Note : </div>
                                    <div style="float: left; width: 80%;"><?php echo !empty($vo->expense_request_status_note) ? $vo->expense_request_status_note : '-'; ?></div>
                                </div>
                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                    <div style="float: left;  width: 20%; font-weight: 600;">Description : </div>
                                    <div style="float: left; width: 80%;"><?php echo !empty($vo->note) ? $vo->note : '-'; ?></div>
                                </div>
                                <div style="float: left;width: 100%; margin-bottom: 10px;">
                                    <div style="float: left;  width: 20%; font-weight: 600;">Requested By : </div>
                                    <div style="float: left; width: 80%;">
                                        <?php $modelUser = new App\User();
                                        $dataUser = $modelUser->getUserName($vo->request_by);
                                        echo !empty($dataUser) ? $dataUser->name : '-'; ?>
                                    </div>
                                </div>



                                <h3 style="background: #ccc; padding: 5px; font-weight: normal; display: inline-block; width: 100%; text-align: center; box-sizing: border-box;
                                            margin-bottom: 0;">Cargo Expense</h3>
                                <table class="table" id="example1" CELLSPACING="0" CELLPADDING="0" width="100%" class="joureny" style="overflow: visible; text-align:left; font-variant: normal; font-weight: normal;font-size: 14px;background-color: fff;line-height: 20px;font-family: Asap, sans-serif;color: #333;padding: 0;font-style: normal;border:solid 1px hsl(0, 0%, 86%);margin-top:15px; width:900px;border-collapse: collapse;width: 100%">
                                    <thead>
                                        <tr>
                                            <th width="80px" style="border-right:1px solid hsl(0, 0%, 86%);border-bottom:1px solid hsl(0, 0%, 86%); border-top: 1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Sr No.</th>
                                            <th width="320px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Description</th>
                                            <th width="90px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Amount<?php
                                                                                                                                                                                                                        $currencyAData = App\Currency::getData($vo->currency);
                                                                                                                                                                                                                        if (empty($currencyAData))
                                                                                                                                                                                                                            $code = '';
                                                                                                                                                                                                                        else
                                                                                                                                                                                                                            $code = $currencyAData->code;
                                                                                                                                                                                                                        echo " (" . $code . ")"; ?></th>
                                            <th width="145px" style="border-right:1px solid hsl(0, 0%, 86%); border-bottom:1px solid hsl(0, 0%, 86%); border-collapse: collapse;padding: 5px;text-align: center;">Paid to</th>
                                        </tr>
                                    </thead>


                                    <tbody>
                                        <?php if (count($cargoExpenseDetailsData)  > 0) {
                                            $ik = 1;
                                            foreach ($cargoExpenseDetailsData as $k => $v) {
                                                $v = (object) $v;
                                        ?>
                                                <tr>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo $ik; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php echo !empty($v->description) ? $v->description : '-'; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: right;"><?php echo !empty($v->amount) ? number_format($v->amount, 2) : '0.00'; ?></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"><?php $dataUser = app('App\Vendors')->getVendorData($v->paid_to);
                                                                                                                                                                                                                                                        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-"; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;height: 30px"></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                                                    <td style="border-bottom:1px solid hsl(0, 0%, 86%);padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;"></td>
                                                </tr>
                                            <?php $ik++;
                                            } ?>
                                            <tr>
                                                <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: left;"></td>
                                                <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: left;vertical-align:top;">Total</td>
                                                <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: right;"><?php $totalD = App\OtherExpenses::getExpenseTotal($vo->id);
                                                                                                                                                    echo $totalD; ?></td>
                                                <td style="padding:5px;font-family: Asap, sans-serif;border-collapse: collapse;text-align: left;"></td>
                                            </tr>
                                        <?php } else { ?>
                                            <tr>
                                                <td style="border-bottom:1px solid hsl(0, 0%, 86%);border-right:1px solid hsl(0, 0%, 86%); padding:5px;font-family: Asap, sans-serif;font-size:14px; border-collapse: collapse;text-align: center;" colspan="6">No data found</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>


                    <?php  } ?>
                <?php $kv++;
                } ?> <?php } ?>



        </div>
    </div>
</section>



@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        $('#example').DataTable({
            "order": [
                [1, "desc"]
            ],
            drawCallback: function() {
                $('.paginate_button', this.api().table().container())
                    .on('click', function() {
                        $('#loading').show();
                        setTimeout(function() {
                            $("#loading").hide();
                        }, 200);
                        $('.expandpackage').each(function() {
                            if ($(this).hasClass('fa-minus')) {
                                $(this).removeClass('fa-minus');
                                $(this).addClass('fa-plus');
                            }
                        })
                    });
            }
        });

        //$('.expandpackage').click(function(){
        $(document).delegate('.expandpackage', 'click', function() {
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 200);
            //$('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var thiz = $(this);
            var parentTR = thiz.closest('tr');
            if (thiz.hasClass('fa-plus')) {
                $('.childrw').remove();
                $('.fa-minus').each(function() {
                    $(this).removeClass('fa-minus');
                    $(this).addClass('fa-plus');
                    //$(this).closest('tr').next('tr').remove();
                })

                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var expenseId = $(this).data('expenseid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("expandexpensescashier"); ?>';
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        expenseId: expenseId,
                        rowId: rowId
                    },
                    success: function(data) {

                        $(data).insertAfter(parentTR).slideDown();
                    },
                });
                //$('#loading').hide();
            } else {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.childrw').remove();
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            }
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

        $('#createExpenseForm').on('submit', function(event) {
            if ($('#expense_request').val() == 'Disbursement done' && $('#cash_credit_account').val() == '') {
                alert("Please select Cash/Bank account.");
                return false;
            }
        });



    })
</script>
@stop