@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Vendor' : 'Create Vendor'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('vendors') }}">Manage Vendors</a>
        </li>
    </ul>
</aside>
@stop

<?php
if (!empty($model->id)) {
    $countcontacts = App\ClientContact::where('vendor_id', $model->id)->count();
} else {
    $countcontacts = 0;
} ?>

@section('breadcrumbs')
@include('menus.vendors')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Vendor' : 'Add Vendor'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('vendors/update', $model->id);
            else
                $actionUrl = url('vendors/store');
            ?>


            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}


            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('vendor_type') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                            <?php echo Form::label('vendor_type', 'Type', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('vendor_type', $vendorType, $model->vendor_type, ['class' => 'form-control selectpicker fvendor_type', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('email') ? 'has-error' :'' }}">
                        <div class="col-md-5">
                            <?php echo Form::label('email', 'Email', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::email('email', $model->email, ['class' => 'form-control femail', 'placeholder' => 'Enter Email']); ?>
                            @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('company_name') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                            <?php echo Form::label('company_name', 'Vendor/Supplier', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('company_name', $model->company_name, ['class' => 'form-control fcompany_name', 'placeholder' => 'Enter Company Name']); ?>
                            @if ($errors->has('company_name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('company_name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('company_phone') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                            <?php echo Form::label('company_phone', 'Phone Number', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('company_phone', $model->company_phone, ['class' => 'form-control fcompany_phone', 'placeholder' => 'Enter Phone Number']); ?>
                            @if ($errors->has('company_phone'))
                            <span class="help-block">
                                <strong>{{ $errors->first('company_phone') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('company_phone') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                            <?php echo Form::label('currency', 'Currency', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('currency', $currency, $model->currency, ['class' => 'form-control selectpicker fcurrency', 'data-live-search' => 'true', 'id' => 'currency']) ?>
                            @if ($errors->has('company_phone'))
                            <span class="help-block">
                                <strong>{{ $errors->first('company_phone') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="col-md-5">
                            <?php echo Form::label('payment_term', 'Payment Term', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('payment_term', $paymentTerms, $model->payment_term, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" style="display: none">
                    <div class="form-group {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                        <?php echo Form::label('opening_balance', 'Opening Balance', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('opening_balance', $model->opening_balance, ['class' => 'form-control', 'placeholder' => 'Enter Opening Balance']); ?>
                            @if ($errors->has('opening_balance'))
                            <span class="help-block">
                                <strong>{{ $errors->first('opening_balance') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('street') ? 'has-error' :'' }}">
                        <div class="col-md-5">
                            <?php echo Form::label('street', 'Company Address', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('street', $model->street, ['class' => 'form-control fstreet', 'rows' => 2, 'placeholder' => 'Street']); ?>
                            @if ($errors->has('street'))
                            <span class="help-block">
                                <strong>{{ $errors->first('street') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('city') ? 'has-error' :'' }}">
                        <div class="col-md-6">
                            <?php echo Form::text('city', $model->city, ['class' => 'form-control fcity', 'rows' => 2, 'placeholder' => 'City/Town']); ?>
                            @if ($errors->has('city'))
                            <span class="help-block">
                                <strong>{{ $errors->first('city') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('state', $model->state, ['class' => 'form-control fstate', 'rows' => 2, 'placeholder' => 'State/Province']); ?>
                            @if ($errors->has('state'))
                            <span class="help-block">
                                <strong>{{ $errors->first('state') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('zipcode') ? 'has-error' :'' }}">
                        <div class="col-md-6">
                            <?php echo Form::text('zipcode', $model->zipcode, ['class' => 'form-control fzipcode', 'rows' => 2, 'placeholder' => 'Zip Code']); ?>
                            @if ($errors->has('zipcode'))
                            <span class="help-block">
                                <strong>{{ $errors->first('zipcode') }}</strong>
                            </span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('country', $model->country, ['class' => 'form-control fcountry', 'rows' => 2, 'placeholder' => 'Country']); ?>
                            @if ($errors->has('country'))
                            <span class="help-block">
                                <strong>{{ $errors->first('country') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('as_of') ? 'has-error' :'' }}">
                        <?php echo Form::label('as_of', 'as of', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('as_of', $model->as_of, ['class' => 'form-control datepicker', 'placeholder' => 'Enter as of']); ?>
                            @if ($errors->has('as_of'))
                            <span class="help-block">
                                <strong>{{ $errors->first('as_of') }}</strong>
                            </span>
                            @endif
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
            <?php if (isset($model->id)) {
                $companyName = $model->company_name;
                $findStr = '';
                if (strpos($companyName, 'USD') !== false) {
                    $findStr = str_replace('USD', 'HTG', $companyName);
                } else if (strpos($companyName, 'HTG') !== false) {
                    $findStr = str_replace('HTG', 'USD', $companyName);
                }

                $anotherCurrencyClient = App\Vendors::where('company_name', $findStr)->first();
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

                <a class="btn btn-danger" href="{{url('vendors')}}" title="">Cancel</a>
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
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        $('#createforms').on('submit', function(event) {

            $('.fvendor_type').each(function() {

                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcompany_phone').each(function() {
                $(this).rules("add", {
                    required: true,
                    //number: true    
                    //telephonecheck : true
                })
            });
            $('.fzipcode').each(function() {

                $(this).rules("add", {
                    number: true,
                })
            });
            $('.femail').each(function() {

                $(this).rules("add", {
                    email: true,
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
                    checkUniqueCompanyName: true
                }
            },

            errorPlacement: function(error, element) {

                if (element.attr("name") == "vendor_type") {
                    var pos = $('.fvendor_type button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function() {
                $("#loading").show();
                $('#createforms')[0].submit();
            }
        });


        $.validator.addMethod('checkUniqueCompanyName', function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("vendors/checkunique"); ?>';

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
                        id: id
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is not exist in database
                return result;
            },
            "This Client/Vendor is already taken! Try another."
        );

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