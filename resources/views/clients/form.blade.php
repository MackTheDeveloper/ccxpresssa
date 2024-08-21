@extends('layouts.custom')
@section('title')
<?php echo ($model->id ? ($flag == 'B' ? 'Update Billing Party' : 'Update Client') : ($flag == 'B' ? 'Add Billing Party' : 'Add Client')); ?>
@stop


@section('breadcrumbs')
@include('menus.client-management')
@stop

<?php
if (!empty($model->id)) {
    $countcontacts = App\ClientContact::where('client_id', $model->id)->count();
} else {
    $countcontacts = 0;
} ?>

@section('content')
<section class="content-header">
    <h1><?php echo ($model->id ? ($flag == 'B' ? 'Update Billing Party' : 'Update Client') : ($flag == 'B' ? 'Add Billing Party' : 'Add Client')); ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('clients/update', $model->id);
            else
                $actionUrl = url('clients/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <input type="hidden" name="client_flag" value="<?php echo $flag; ?>">

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
                <?php $styleDisable = '';
                if ($model->company_name == 'CHATELAIN CARGO SERVICE S.A USD' || $model->company_name == 'UPS Miami USD' || $model->company_name == 'Aeropost Miami USD' || $model->company_name == 'CHATELAIN CARGO SERVICES INC USD') {
                    $styleDisable = 'opacity: 0.5;pointer-events: none;';
                } ?>
                <div class="col-md-4" style="<?php echo $styleDisable; ?>">
                    <div class="form-group {{ $errors->has('company_name') ? 'has-error' :'' }}">
                        <div class="col-md-12 required row">
                            <?php echo Form::label('company_name', ($flag == 'B') ? 'Billing Party' : 'Client', ['class' => 'control-label']); ?>
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

            <?php if($flag == 'B') { ?>
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('email_two') ? 'has-error' :'' }}">
                        <div class="col-md-12 row">
                            <?php echo Form::label('email_two', 'Email 2', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                            <?php echo Form::email('email_two', $model->email_two, ['class' => 'form-control femail_two']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('email_three') ? 'has-error' :'' }}">
                        <div class="col-md-12 row">
                            <?php echo Form::label('email_three', 'Email 3', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                            <?php echo Form::email('email_three', $model->email_three, ['class' => 'form-control femail_three']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

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
                <div class="col-md-8 creditlimitdiv" style="display: none;margin: 0px;padding: 0px;">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('credit_limit') ? 'has-error' :'' }}">
                            <div class="col-md-12 row required">
                                <?php echo Form::label('credit_limit', "Credit Limit" . ($model->id ? " (Add more credit)" : ''), ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12 row">
                                <?php if ($model->id) { ?>
                                    <?php echo Form::text('credit_limit_add', '', ['class' => 'form-control fcredit_limit']); ?>
                                    <label>Initial Credit : <?php echo $model->credit_limit; ?></label><?php } else { ?>
                                    <?php echo Form::text('credit_limit', '', ['class' => 'form-control fcredit_limit']); ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($model->id) { ?>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('available_balance') ? 'has-error' :'' }}">
                                <div class="col-md-12 row required">
                                    <?php echo Form::label('available_balance', 'Available Credit', ['class' => 'control-label']); ?>
                                </div>
                                <div class="col-md-12 row">
                                    <?php echo Form::text('available_balance', $model->available_balance, ['class' => 'form-control fcredit_limit', 'readonly' => true]); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
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

                        <?php if (empty($model->id) || empty($dataContacts)) { ?>

                            <tr id="tbtr-0" style="border-bottom: 1px solid #ccc;height: 39px;">
                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                    <?php echo Form::text("clientContact[name][0]", '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'name-0']); ?>
                                </td>
                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                    <?php echo Form::text("clientContact[personal_contact][0]", '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'personal_contact-0']); ?>
                                </td>
                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                    <?php echo Form::text("clientContact[cell_number][0]", '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'cell_number-0', 'onkeypress' => 'return isNumber(event)']); ?>
                                </td>
                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                    <?php echo Form::text("clientContact[direct_line][0]", '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'direct_line-0', 'onkeypress' => 'return isNumber(event)']); ?>
                                </td>
                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                    <?php echo Form::text("clientContact[work][0]", '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'work-0', 'onkeypress' => 'return isNumber(event)']); ?>
                                </td>
                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                    <?php echo Form::email("clientContact[email][0]", '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'email-0']); ?>
                                </td>
                                <td style="text-align: center;"><a href="javascript:void(0)" class='btn btn-success btn-xs addmorecontact'>+</a>
                                </td>
                            </tr>

                            <?php } else {
                            $i = 0;
                            foreach ($dataContacts as $k => $v) {   ?>

                                <tr id="tbtr-<?php echo $i; ?>" style="border-bottom: 1px solid #ccc;height: 39px;">
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[name][$i]", $v->name, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "name-$i"]); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[personal_contact][$i]", $v->personal_contact, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "personal_contact-$i"]); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[cell_number][$i]", $v->cell_number, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "cell_number-$i", 'onkeypress' => 'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[direct_line][$i]", $v->direct_line, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "direct_line-$i", 'onkeypress' => 'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::text("clientContact[work][$i]", $v->work, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "work-$i", 'onkeypress' => 'return isNumber(event)']); ?>
                                    </td>
                                    <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                        <?php echo Form::email("clientContact[email][$i]", $v->email, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "email-$i"]); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="javascript:void(0)" class='btn btn-success btn-xs addmorecontact'>+</a>
                                        <?php if ($i != 0) { ?>
                                            <a style='' href='javascript:void(0)' class='btn btn-danger btn-xs removecontact' id=<?php echo $i; ?>>-</a>
                                        <?php } ?>
                                    </td>
                                </tr>

                        <?php $i++;
                            }
                        } ?>

                    </tbody>
                </table>
            </div>
            <?php
            if (isset($model->id) && $flag == 'B') {
                $companyName = $model->company_name;
                $findStr = '';
                if (strpos($companyName, 'USD') !== false) {
                    $findStr = str_replace('USD', 'HTG', $companyName);
                } else if (strpos($companyName, 'HTG') !== false) {
                    $findStr = str_replace('HTG', 'USD', $companyName);
                }

                $anotherCurrencyClient = App\Clients::where('company_name', $findStr)->first();
                if (!empty($anotherCurrencyClient)) {
            ?>
                    <div class="col-md-12">
                        <input style="margin-right:10px;margin-top:10px" type="checkbox" name="copycontacts" value="1" checked>Would you like to update contact details in another currency?
                    </div>
            <?php }
            } ?>
            <div class="form-group col-md-12 btm-sub">
                <button type="submit" class="btn btn-success">
                    <?php
                    if (!$model->id)
                        echo "Submit";
                    else
                        echo "Update";
                    ?>
                </button>
                <a class="btn btn-danger" href="{{url('clients',$flag)}}" title="">Cancel</a>
            </div>

            {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
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

        $('#country').change(function() {
            $('loading').show();
            var id = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlzt = '<?php echo url("clients/getstatesdata"); ?>';
            $.ajax({
                url: urlzt,
                type: 'POST',
                data: {
                    'id': id
                },
                success: function(data) {
                    $('#state').html(data);
                    $('.selectpicker').selectpicker('refresh');
                    $('#loading').hide();
                }
            });
        })

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
            /*  $('.fcredit_limit').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                            number:true,
                        })
            }); */
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

        /*  jQuery.validator.addMethod(
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
                } else if (element.attr("id") == "currency") {
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
                        flag: '<?php echo $flag; ?>',
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

        <?php if ($model->id) { ?>
            if ($('#flag_prod_tax_type').prop('checked') == true)
                $('.ratediv').show();
            else
                $('.ratediv').hide();
        <?php } ?>
        $('#flag_prod_tax_type').click(function() {
            var tVal = $(this).val();
            if ($(this).prop('checked') == false)
                $('.ratediv').hide();
            else
                $('.ratediv').show();
        })


        <?php if ($model->id) { ?>
            var tVal = $('.cash_credit:checked').val();
            if (tVal == 'Credit')
                $('.creditlimitdiv').show();
            else
                $('.creditlimitdiv').hide();
        <?php } ?>
        $('.cash_credit').click(function() {
            var tVal = $(this).val();
            if (tVal == 'Cash')
                $('.creditlimitdiv').hide();
            else
                $('.creditlimitdiv').show();
        })

        countcontacts = 0;
        if ('<?php echo $countcontacts; ?>' != 0) {
            countcontacts = <?php echo $countcontacts - 1; ?>;
        }
        $(document).on("click", ".addmorecontact", function(e) {
            $('#loading').show();
            countcontacts = countcontacts + 1;
            if (countcontacts == 0) {
                countcontacts = 1;
            }
            e.preventDefault();
            var str = '<tr id="tbtr-' + countcontacts + '" style="border-bottom: 1px solid #ccc;height: 39px;"><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="name-' + countcontacts + '" name="clientContact[name][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="personal_contact-' + countcontacts + '" name="clientContact[personal_contact][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="cell_number-' + countcontacts + '" onkeypress="return isNumber(event)" name="clientContact[cell_number][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="direct_line-' + countcontacts + '" onkeypress="return isNumber(event)" name="clientContact[direct_line][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="work-' + countcontacts + '" onkeypress="return isNumber(event)" name="clientContact[work][' + countcontacts + ']" type="text" value=""></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl unitpricefld" id="email-' + countcontacts + '" name="clientContact[email][' + countcontacts + ']" type="email" value=""></td><td style="text-align: center;"><a href="javascript:void(0)" class="btn btn-success btn-xs addmorecontact" style="margin-right:5px">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removecontact" id="' + countcontacts + '">-</a></td></tr>';
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
@stop