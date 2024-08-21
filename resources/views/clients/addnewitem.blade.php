<?php
if ($model->id)
    $actionUrl = url('clients/update', $model->id);
else
    $actionUrl = url('clients/store');
?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="client_flag" value="B">
<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('category') ? 'has-error' :'' }}">
            <div class="col-md-12 required row">
                <?php echo Form::label('category', 'Client Type', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::select('category', $categories, $model->category, ['class' => 'form-control selectpicker fcategory', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('company_name') ? 'has-error' :'' }}">
            <div class="col-md-12 required row">
                <?php echo Form::label('company_name', 'Billing Party', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('company_name', $model->company_name, ['class' => 'form-control fcompany_name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('email') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('email', 'Email', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::email('email', $model->email, ['class' => 'form-control femail']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">

    <div class="col-md-4">
        <div class="form-group {{ $errors->has('phone_number') ? 'has-error' :'' }}">
            <div class="col-md-12 row required">
                <?php echo Form::label('phone_number', 'Phone Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('phone_number', $model->phone_number, ['class' => 'form-control fphone_number']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('branch_name') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('branch_name', 'Branch Name', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('branch_name', $model->branch_name, ['class' => 'form-control']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('tax_number') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('tax_number', 'Tax Number', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('tax_number', $model->tax_number, ['class' => 'form-control']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('payment_term') ? 'has-error' :'' }}">
            <div class="col-md-12 required row">
                <?php echo Form::label('payment_term', 'Payment Term', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::select('payment_term', $paymentTerms, $model->payment_term, ['class' => 'form-control selectpicker fpayment_term', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('website') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('website', 'Webiste', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('website', $model->website, ['class' => 'form-control']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('fax') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('fax', 'Fax', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('fax', $model->fax, ['class' => 'form-control']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">

    <div class="col-md-4">
        <div class="form-group">
            <div class="col-md-12 row required">
                <?php echo Form::label('currency', 'Currency', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::select('currency', $currency, $model->currency, ['class' => 'form-control selectpicker fcurrency', 'data-live-search' => 'true', 'id' => 'currency', 'placeholder' => 'select...']) ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group {{ $errors->has('company_address') ? 'has-error' :'' }}">
            <div class="col-md-12 row required">
                <?php echo Form::label('company_address', 'Address', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::textarea('company_address', $model->company_address, ['class' => 'form-control fcompany_address', 'rows' => 2]); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">

    <div class="col-md-4">
        <div class="form-group {{ $errors->has('country') ? 'has-error' :'' }}">
            <div class="col-md-12 required row">
                <?php echo Form::label('country', 'Country', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::select('country', $country, $model->country, ['class' => 'form-control selectpicker fcountry', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('state') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('state', 'State', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('state', $model->state, ['class' => 'form-control fstate']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('city') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('city', 'City', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('city', $model->city, ['class' => 'form-control fcity']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('zipcode') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('zipcode', 'Zip code', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('zipcode', $model->zipcode, ['class' => 'form-control fzipcode']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">

    <div class="col-md-4">
        <div class="form-group {{ $errors->has('cash_credit') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('cash_credit', 'Cash/Credit', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row consolidate_flag-md-6">
                <?php
                if (!empty($model->id)) {
                    echo Form::radio('cash_credit', 'Cash', $model->cash_credit == 'Cash' ? 'checked' : '', ['class' => 'cash_credit']);
                    echo Form::label('', 'Cash');
                    echo Form::radio('cash_credit', 'Credit', $model->cash_credit == 'Credit' ? 'checked' : '', ['class' => 'cash_credit']);
                    echo Form::label('', 'Credit');
                } else {
                    echo Form::radio('cash_credit', 'Cash', true, ['class' => 'cash_credit']);
                    echo Form::label('', 'Cash');
                    echo Form::radio('cash_credit', 'Credit', '', ['class' => 'cash_credit']);
                    echo Form::label('', 'Credit');
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4 creditlimitdiv" style="display: none">
        <div class="form-group {{ $errors->has('credit_limit') ? 'has-error' :'' }}">
            <div class="col-md-12 row required">
                <?php echo Form::label('credit_limit', 'Credit Limit', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('credit_limit', $model->credit_limit, ['class' => 'form-control fcredit_limit']); ?>
            </div>
        </div>
    </div>
</div>



<div class="col-md-12">
    <div class="col-md-4">
        <div class="form-group {{ $errors->has('flag_prod_tax_type') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('flag_prod_tax_type', 'TCA Applicable?', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::checkbox('flag_prod_tax_type', '1', $model->flag_prod_tax_type, array('id' => 'flag_prod_tax_type')); ?> Yes
            </div>
        </div>
    </div>
    <div class="col-md-4 ratediv" style="display: none;pointer-events: none;opacity: 0.5">
        <?php if ($model->flag_prod_tax_amount == '0.00' || empty($model->id)) {
            $model->flag_prod_tax_amount = '0.00';
        } ?>
        <div class="form-group {{ $errors->has('flag_prod_tax_amount') ? 'has-error' :'' }}">
            <div class="col-md-12 row">
                <?php echo Form::label('flag_prod_tax_amount', 'Percentage', ['class' => 'control-label']); ?>
            </div>
            <div class="col-md-12 row">
                <?php echo Form::text('flag_prod_tax_amount', '10.00', ['class' => 'form-control', 'placeholder' => 'Enter Billing Item Name', 'onkeypress' => 'return isNumber(event)']); ?>
            </div>
        </div>
    </div>
</div>

<div class="col-md-12">
    <h4 style="background: #f3f3f3;padding: 10px;">Contact Details</h4>
</div>


<div class="col-md-12 container-contact">
    <table class="tableexpense" style="border: 1px solid #ccc;width: 100%">
        <thead>
            <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;font-weight: bold;">
                <td style="border-right: 1px solid #ccc;width: 15%;">Contact Name</td>
                <td style="border-right: 1px solid #ccc;text-align: center;padding-left: 5px;width: 12%;">Position</td>
                <td style="border-right: 1px solid #ccc;width: 12%;">Cell Number</td>
                <td style="border-right: 1px solid #ccc;width: 12%;">Direct line</td>
                <td style="border-right: 1px solid #ccc;width: 12%;">Work</td>
                <td style="border-right: 1px solid #ccc;width: 21%;">Email</td>
                <td style="width: 10%;">Action</td>
            </tr>
        </thead>
        <tbody>

            <tr id="tbtr-0" style="border-bottom: 1px solid #ccc;height: 39px;">
                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                    <?php echo Form::text("clientContact[name][0]", '', ['class' => 'form-control invfield invfieldtbl', 'id' => 'name-0']); ?>
                </td>
                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                    <?php echo Form::text("clientContact[personal_contact][0]", '', ['class' => 'form-control invfield invfieldtbl', 'id' => 'personal_contact-0']); ?>
                </td>
                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                    <?php echo Form::text("clientContact[cell_number][0]", '', ['class' => 'form-control invfield invfieldtbl', 'id' => 'cell_number-0', 'onkeypress' => 'return isNumber(event)']); ?>
                </td>
                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                    <?php echo Form::text("clientContact[direct_line][0]", '', ['class' => 'form-control invfield invfieldtbl', 'id' => 'direct_line-0', 'onkeypress' => 'return isNumber(event)']); ?>
                </td>
                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                    <?php echo Form::text("clientContact[work][0]", '', ['class' => 'form-control invfield invfieldtbl', 'id' => 'work-0', 'onkeypress' => 'return isNumber(event)']); ?>
                </td>
                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                    <?php echo Form::email("clientContact[email][0]", '', ['class' => 'form-control invfield invfieldtbl', 'id' => 'email-0']); ?>
                </td>
                <td style="text-align: center;"><a href="javascript:void(0)" class='btn btn-success btn-xs addmorecontact'>+</a>
                </td>
            </tr>

        </tbody>
    </table>
</div>


<div class="form-group col-md-12 btm-sub">

    <button type="submit" class="btn btn-success">
        <?php
        if (!$model->id)
            echo "Submit";
        else
            echo "Update";
        ?>
    </button>


</div>

{{ Form::close() }}



<script type="text/javascript">
    $('select').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
            return false;
        }
        return true;
    }
    $(document).ready(function() {

        $('.selectpicker').selectpicker();
        $('#createforms').on('submit', function(event) {
            $('.fcategory').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcompany_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fphone_number').each(function() {
                $(this).rules("add", {
                    required: true,
                    //number:true,
                    //telephonecheck : true
                })
            });
            $('.fpayment_term').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcredit_limit').each(function() {
                $(this).rules("add", {
                    required: true,
                    number: true,
                })
            });
            $('.fcompany_address').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcurrency').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

            $('.fcountry').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
        });

        /* jQuery.validator.addMethod(
            "telephonecheck",
            function(phone_number, element) {
                return this.optional(element) || /^(?=.*[0-9])[- +()0-9]+$/.test(phone_number);
            },
                "Please enter a valid number."
        ); */

        $('#createforms').validate({
            rules: {
                "company_name": {
                    required: true,
                    checkUniqueCompany: true
                },
            },
            submitHandler: function(form) {
                $('#loading').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                var createExpenseForm = $("#createforms");
                var formData = createExpenseForm.serialize();
                var urlz = '<?php echo url("clients/storenewitem"); ?>';
                $.ajax({
                    url: urlz,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        $('#loading').hide();
                        //$('.selectpicker').selectpicker('refresh');
                        Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Item has been added successfully.'
                        });

                        var urlzte = '<?php echo url("clients/getclientdropdowndataaftersubmit"); ?>';
                        $.ajax({
                            async: false,
                            url: urlzte,
                            type: 'POST',
                            data: '',
                            success: function(response) {
                                $('#loading').hide();
                                var oldVal = $('#bill_to option:selected').val();
                                $('#bill_to').html(response);
                                $('#bill_to').val(oldVal);
                                $('.selectpicker').selectpicker('refresh');
                            }
                        });

                        $('#modalAddNewItems').modal('toggle');
                    },
                });
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "category") {
                    var pos = $('.fcategory button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "payment_term") {
                    var pos = $('.fpayment_term button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "country") {
                    var pos = $('.fcountry button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr('id') == 'currency') {
                    var pos = $('.fcurrency button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }

        });

        $.validator.addMethod("checkUniqueCompany",
            function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("clients/checkuniquecompany"); ?>';
                <?php if ($model->id) { ?>
                    var id = '<?php echo $model->id; ?>';
                <?php } else { ?>
                    var id = '';
                <?php } ?>

                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        value: value,
                        id: id,
                        flag: 'O',
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Client/Vendor is already taken! Try another."
        );

        $('#flag_prod_tax_type').click(function() {
            var tVal = $(this).val();
            if ($(this).prop('checked') == false)
                $('.ratediv').hide();
            else
                $('.ratediv').show();
        })



        $('.cash_credit').click(function() {
            var tVal = $(this).val();
            if (tVal == 'Cash')
                $('.creditlimitdiv').hide();
            else
                $('.creditlimitdiv').show();
        })

        countcontacts = 0;
        $('.container-contact').on("click", ".addmorecontact", function(e) {
            $('#loading').show();
            countcontacts = countcontacts + 1;
            if (countcontacts == 0) {
                countcontacts = 1;
            }
            e.preventDefault();
            var str = '<tr id="tbtr-' + countcontacts + '" style="border-bottom: 1px solid #ccc;height: 39px;"><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl" id="name-' + countcontacts + '" name="clientContact[name][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl" id="personal_contact-' + countcontacts + '" name="clientContact[personal_contact][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl" id="cell_number-' + countcontacts + '" onkeypress="return isNumber(event)" name="clientContact[cell_number][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl" id="direct_line-' + countcontacts + '" onkeypress="return isNumber(event)" name="clientContact[direct_line][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl" id="work-' + countcontacts + '" onkeypress="return isNumber(event)" name="clientContact[work][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl" id="email-' + countcontacts + '" name="clientContact[email][' + countcontacts + ']" type="email" value=""></td><td style="text-align: center;"><a href="javascript:void(0)" class="btn btn-success btn-xs addmorecontact" style="margin-right:5px">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removecontact" id="' + countcontacts + '">-</a></td></tr>';
            $('table.tableexpense tbody').append(str);
            $('#loading').hide();
        });

        $(document).on("click", ".removecontact", function(e) {
            $('#loading').show();
            e.preventDefault();
            var id = $(this).attr('id');
            $("table.tableexpense tbody tr#tbtr-" + id).remove();
            $('#loading').hide();
        });


    });
</script>