{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'w3','autocomplete'=>'off')) }}
{{ csrf_field() }}

<?php
$disabledMonthDropdown = false;
if($cargoRenewContract){
    $disabledMonthDropdown = true;
}

if (!empty($model->id)) {
    $countaddproddetail = App\CargoProductDetails::where('cargo_id', $model->id)->count();
} else {
    $countaddproddetail = 0;
}
?>
<?php
if ($model->rental == '0') {
    $display = 'display:none';
} else {
    $display = 'display:block';
}
?>
<input type="hidden" name="cargo_operation_type" value="3" id="operation_type">
<input type="hidden" name="permissionToUpdateLocal" value="{{$premissionToUpdateLocal}}" id="permissionToUpdateLocal">
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
            <?php echo Form::label('file_number', 'File Number :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <span class="form-control" style="border-bottom:none;padding-left: 0px;font-weight:bold"><?php echo $model->file_number; ?></span>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('opening_date') ? 'has-error' :'' }}">
            <?php echo Form::label('opening_date', 'Opening Date :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <!-- 
                <input type="hidden" name="opening_date" value=<?php echo $model->opening_date ?>> -->
                <?php echo Form::text('opening_date', date('d-m-Y', strtotime($model->opening_date)), ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date', 'id' => 'openingDate']); ?>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('rental') ? 'has-error' :'' }}">
            <?php
            if ($model->rental == '1') {
                $checkRental = true;
                $checkNonRental = false;
            } else {
                $checkNonRental = true;
                $checkRental = false;
            }
            ?>
            <?php echo Form::label('rental', 'Rental :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <?php echo Form::radio('rental', '1', $checkRental, ['id' => 'rental']); ?> Rental
                <?php echo Form::radio('rental', '0', $checkNonRental, ['id' => 'non-rental']); ?> Non-rental

            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12" id="rentDetail" style="<?php $model->rental = '1' ? 'dispaly:block' : 'display:none'; ?>display: none;margin-left: 0px !important;padding-left: 0 !important;">
        <div class="col-md-3" style="display: none">
            <div class="form-group">
                <?php echo Form::label('rental_starting_date', 'From Date :', ['class' => 'control-label']); ?>
                <div class="col-md-4">
                    <?php echo Form::text('rental_starting_date', $model->rental_starting_date, ['class' => 'form-control datepicker', 'placeholder' => 'From Date']); ?>

                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label">Contract months:</label>
                </div>
                <div class="col-md-6">
                    <?php echo Form::select('contract_months', Config::get('app.months'), $model->contract_months, ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'contract_month', 'disabled' => $disabledMonthDropdown]); ?>
                </div>
            </div>
        </div>
        <div class="col-md-3" style="">
            <div class="form-group">

                <?php echo Form::label('rental_ending_date', 'Ending Date :', ['class' => 'control-label']); ?>
                <div class="col-md-6">

                    <input type="hidden" name="rental_hidden_ending_date" value="<?php echo date('d-m-Y', strtotime($model->rental_ending_date)) ?>" id="hidden_ending_date">
                    <?php echo Form::text('rental_ending_date', date('d-m-Y', strtotime($model->rental_ending_date)), ['class' => 'form-control datepicker', 'placeholder' => 'To Date', 'id' => 'ending_date']); ?>


                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <?php echo Form::label('rental_cost', 'Charge :', ['class' => 'control-label']); ?>
                <div class="col-md-6">
                    <?php echo Form::text('rental_cost', $model->rental_cost, ['class' => 'form-control', 'placeholder' => 'Enter Charge', 'id' => 'charge']); ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <?php
                if ($model->rental_paid_status == 'p') {
                    $checkPaid = true;
                    $checkUnpaid = false;
                } else {
                    $checkPaid = false;
                    $checkUnpaid = true;
                }
                ?>
                <?php echo Form::label('rental_paid_status', 'Status :', ['class' => 'control-label']); ?>
                <div class="col-md-6">
                    <?php echo Form::radio('rental_paid_status', 'p', $checkPaid, ['id' => 'paid', 'disabled' => false]); ?> Paid
                    <?php echo Form::radio('rental_paid_status', 'up', $checkUnpaid, ['id' => 'unpaid', 'disabled' => false]); ?> Pending

                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('contract_renew') ? 'has-error' :'' }}">
            <?php echo Form::label('contract_renew', 'Contract Renew :', ['class' => 'control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::radio('contract_renew', 'N', true, ['disabled' => false]); ?> No
                <?php echo Form::radio('contract_renew', 'Y', false, ['disabled' => false]); ?> Yes
            </div>
        </div>
    </div>

    <div class="col-md-6 renewal_month" style="display:none;">
            <div class="form-group {{ $errors->has('no_of_months') ? 'has-error' :'' }}">
                <?php echo Form::label('no_of_months', 'No. of Months :', ['class' => 'control-label']); ?>
                <div class="col-md-6">
                    <?php echo Form::number('no_of_months', '', ['class' => 'form-control', 'placeholder' => 'Enter No. of Months']); ?>
                </div>
            </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('consignee_name') ? 'has-error' :'' }}">
            <?php echo Form::label('consignee_name', 'Client :', ['class' => 'control-label']); ?>
            <div class="col-md-8">
                <?php echo Form::text('consignee_name', App\Ups::getConsigneeName($model->consignee_name), ['class' => 'form-control consignee_name_locale', 'placeholder' => 'Enter client Name']); ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group {{ $errors->has('agent_id') ? 'has-error' :'' }}">
            <?php echo Form::label('agent_id', 'Agent :', ['class' => 'control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::select('agent_id', $agents, $model->agent_id, ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => 'agent']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('consignee_address') ? 'has-error' :'' }}">
            <?php echo Form::label('consignee_address', 'Address :', ['class' => 'control-label']); ?>
            <div class="col-md-8">
                <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control consignee_address', 'placeholder' => 'Enter Address', 'rows' => 2, 'id' => 'address']); ?>
            </div>
        </div>
    </div>
</div>



<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('awb_bl_no') ? 'has-error' :'' }}">
            <?php echo Form::label('awb_bl_no', 'AWB/BL No :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <?php echo Form::text('awb_bl_no', $model->awb_bl_no, ['class' => 'form-control', 'placeholder' => 'Enter AWB/BL No']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('billing_party') ? 'has-error' :'' }}">

            <?php echo Form::label('billing_party', 'Billing Party', ['class' => 'control-label']); ?>

            <div class="col-md-8">
                <?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'invfieldtbl invfieldtblbillto hbilling_party form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => 'billingParty']); ?>
            </div>
            <div class="col-md-12 balance-div" style="display: none;text-align: center;">
                <span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('cash_credit') ? 'has-error' :'' }}">

            <?php echo Form::label('cash_credit', 'Cash/Credit', ['class' => 'control-label']); ?>

            <div class="col-md-8 consolidate_flag-md-6">
                <?php
                echo Form::radio('cash_credit', 'Cash', $model->cash_credit == 'Cash' ? 'checked' : '', ['class' => 'cash_credit']);
                echo Form::label('', 'Cash');
                echo Form::radio('cash_credit', 'Credit', $model->cash_credit == 'Credit' ? 'checked' : '', ['class' => 'cash_credit']);
                echo Form::label('', 'Credit');
                ?>
            </div>
        </div>
    </div>
</div>




<div class="col-md-12" style="display: none;">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('custom_file_number') ? 'has-error' :'' }}">
            <?php echo Form::label('custom_file_number', 'Custom File Number :', ['class' => 'control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::text('custom_file_number', $model->custom_file_number, ['class' => 'form-control', 'placeholder' => 'Enter Custom File Number']); ?>
            </div>
        </div>
    </div>
</div>






<h4 class="formdeviderh4">EXPLICATIONS / INFORMATIONS</h4>

<div class="form-group {{ $errors->has('information') ? 'has-error' :'' }}">
    <div class="col-md-9">
        <?php echo Form::textarea('information', $model->information, ['class' => 'form-control', 'placeholder' => 'Enter Information', 'rows' => 4, 'style' => 'border: 1px solid #ccd0d2;']); ?>
    </div>

</div>






<div class="col-md-12 btm-sub">
    <button type="submit" id="w3btn" class="btn btn-success">
        <?php
        if (!$model->id)
            echo "Submit";
        else
            echo "Update";
        ?>
    </button>
    <?php
    $dept = auth()->user()->department;

    if ($dept == '11') // Cashier
    {
        $listingCargoUrl = 'cashiercargoall';
    } else {
        $listingCargoUrl = 'cargoall';
    }
    ?>
    <a class="btn btn-danger" href="{{url($listingCargoUrl)}}" title="">Cancel</a>
</div>

{{ Form::close() }}
@if($cargoRenewContract)
<h4 class="formdeviderh4">CONTRACT RENEW HISTORY</h4>
<table class="table table-striped table-responsive">
    <thead>
        <tr>
            <th>Sr. No.</th>
            <th>Previous Date</th>
            <th>No. of Months</th>
            <th>Updated Date</th>
            <th>Updated By</th>
            <th>Updated At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cargoRenewContract as $key=>$row)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{date('d-m-Y', strtotime($row['previous_date']))}}</td>
            <td>{{$row['renew_months']}} Month{{$row['renew_months']>1?'s':''}}</td>
            <td>{{date('d-m-Y', strtotime($row['new_date']))}}</td>
            <td>{{$row['updated_by_name']}}</td>
            <td>{{date('d-m-Y H:i A', strtotime($row['updated_at']))}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<?php
$datas = App\Clients::getClientsAutocomplete();
?>

@section('page_level_js')
<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        if ($('#rental').prop('checked') == true) {
            $('#rentDetail').css('display', 'block');
        } else {
            $('#rentDetail').css('display', 'none');
        }
        <?php if ($model->id) { ?>
            var clientId = $('#billing_party').val();
            if (clientId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzte = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        if (balance.cash_credit == 'Credit') {
                            $('.balance-div').show();
                            var blnc = parseInt(balance.available_balance).toFixed(2);
                            $('.cash_credit_account_balance').html(blnc);
                        } else {
                            $('.balance-div').hide();
                        }

                    }
                });
            } else {
                $('#loading').hide();
                $('.balance-div').hide();
            }

        <?php } ?>

        $('#billing_party').change(function() {

            $('#loading').show();
            var clientId = $(this).val();
            if (clientId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzte = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    async: false,
                    url: urlzte,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(balance) {
                        $('#loading').hide();
                        if (balance.cash_credit == 'Credit') {
                            $('.balance-div').show();
                            var blnc = parseInt(balance.available_balance).toFixed(2);
                            $('.cash_credit_account_balance').html(blnc);
                        } else {
                            $('.balance-div').hide();
                        }

                    }
                });
            } else {
                $('#loading').hide();
                $('.balance-div').hide();
            }

        })


        $(".consignee_name_locale").autocomplete({
            select: function(event, ui) {
                event.preventDefault();

                //$("#consignee_name").val(ui.item.label);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var clientId = ui.item.value;
                var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    url: urlztnn,
                    dataType: "json",
                    async: false,
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(data) {
                        $('.consignee_address').val(data.company_address);
                    }
                });
            },
            focus: function(event, ui) {
                $('#loading').show();
                event.preventDefault();
                $(".consignee_name_locale").val(ui.item.label);
                $('#loading').hide();
            },
            change: function(event, ui) {
                if (ui.item == null || typeof(ui.item) == "undefined") {
                    //console.log("dsfdsf");
                    //$('#loading').show();
                    //$('#consignee_name').val("");
                    //$('#loading').hide();

                }
            },
            source: <?php echo $datas; ?>,
            minLength: 1,
        });


        $('#rental').click(function() {
            var months = $('#contract_month').val();
            var d = $('#openingDate').val();
            $.ajax({
                url: "{{url('local/getdate')}}",
                type: 'GET',
                data: {
                    date: d,
                    months: months,
                    flage: 'bm'
                },
                success: function(data) {
                    console.log(data);
                    $('#ending_date').val(data);
                    $('#hidden_ending_date').val(data);
                }
            });
            $('#rentDetail').slideDown('slow');
        });
        $('#non-rental').click(function() {
            $("#rentDetail input").each(function() {
                $(this).val("");
            });

            $('#rentDetail').slideUp('slow');
            // console.log("hello");

        });

        $('#contract_month').on('change', function() {
            var months = $(this).val();
            // console.log($('#openingDate').val());
            var d = $('#openingDate').val();
            //var date = d.getFullYear()+'-'+d.getMonth()+'-'+d.getDay();
            //console.log(date);
            // var d = new Date(d);
            // //d.getDay();

            // // console.log(d);
            // var newdate = new Date(d.getFullYear(),d.getMonth(),d.getDay());
            // var newdate = new Date(newdate.getMonth() + months);
            // console.log(newdate);
            changeContractEndDate(d,months,'contract_month')
            // $.ajax({
            //     url: "{{url('local/getdate')}}",
            //     type: 'GET',
            //     data: {
            //         date: d,
            //         months: months,
            //         flage: 'bm'
            //     },
            //     success: function(data) {
            //         console.log(data);
            //         $('#hidden_ending_date').val(data);
            //         $('#ending_date').val(data);
            //     }
            // });

        });

        $('#openingDate').on('change', function() {
            var date = $(this).val();
            var months = $('#contract_month').val();
            $.ajax({
                url: "{{url('local/getdate')}}",
                type: 'GET',
                data: {
                    date: date,
                    months: months,
                    flage: 'bd'
                },
                success: function(data) {
                    $('#hidden_ending_date').val(data);
                    $('#ending_date').val(data);

                }
            });
        });

        $('#w3').on('submit', function(event) {});

        $('#w3').validate({
            rules: {
                "consignee_name": {
                    required: '#rental:checked'
                },
                "billing_party": {
                    required: '#rental:checked'
                },
                "rental_cost": {
                    required: '#rental:checked'
                },
                "awb_bl_no": {
                    checkAwbNumber: true
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "billing_party") {
                    var pos = $('.hbilling_party button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $.validator.addMethod("checkAwbNumber",
            function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("cargo/checkuniqueawbnumber"); ?>';
                var flag = 'edit';
                var idz = '<?php echo $model->id; ?>';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        number: value,
                        flag: flag,
                        idz: idz
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Awb Number is already taken! Try another."
        );


    });
    
    $(document).on('change','input[name="contract_renew"]',function(){
        var contractRenew = $(this).val();
        if(contractRenew=='Y'){
            $('.renewal_month').show();
        }else{
            $('.renewal_month').hide().find('input').val('');
            // $('.renewal_month').find('input').val('').trigger('change');
            $('#ending_date').val($('#hidden_ending_date').val());

        }
    })
    $(document).on('keyup','.renewal_month input',function(){
        var months = $(this).val();
        var date = $('#hidden_ending_date').val();
        changeContractEndDate(date,months)
    })

    function changeContractEndDate(date,months,from=''){
        $.ajax({
            url: "{{url('local/getdate')}}",
            type: 'GET',
            data: {
                date: date,
                months: months,
                flage: 'bm'
            },
            success: function(data) {
                // console.log(data);
                if(from=='contract_month'){
                    $('#hidden_ending_date').val(data);
                }
                $('#ending_date').val(data);
            }
        });
    }


    $(document).ready(function() {
        var updatePermission = <?php echo $premissionToUpdateLocal; ?>;
        $("form :input[type=text]").each(function() {
            if (updatePermission == 0) {
                $(this).prop('readonly', true);
                $('#w3btn').prop('disabled', false);
                $('#agent').prop('disabled', true);
                //$('#billingParty').prop('disabled', true);
                $('#address').prop('readonly', true);
                //$('#hidden_ending_date').prop('readonly',false);
                //$('#operation_type').prop('disabled',false);
                //$('#contract_month').prop('disabled',false);

            } else {

                $(this).prop('disabled', false);
                $('#paid').prop('disabled', false);
                $('#unpaid').prop('disabled', false);
                //$('#openingDate').prop('disabled','true');
                //$('#ending_date').prop('disabled', 'true');
                //$('#charge').prop('disabled','true');

            }

        });


    });
</script>
@stop