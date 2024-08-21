@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Replenish Account' : 'Replenish Account'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('createcashcredit') }}">Add Cash/Credit</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('cashcredit') }}">Manage Cash/Credit</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('depositcashcredit') }}">Manage Replenish Account</a>
        </li>

    </ul>
</aside>
@stop

@section('breadcrumbs')
@include('menus.cash-credit')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Replenish Account' : 'Replenish Account'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('depositcashcredit/update', $model->id);
            else
                $actionUrl = url('depositcashcredit/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}


            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('cash_credit_account') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                            <?php echo Form::label('cash_credit_account', 'Account', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-8">
                            <?php echo Form::select('cash_credit_account', $cashcreditAccounts, $model->cash_credit_account, ['class' => 'form-control selectpicker fcash_credit_account', 'placeholder' => 'Select ...']); ?>

                            @if ($errors->has('cash_credit_account'))
                            <span class="help-block">
                                <strong>{{ $errors->first('cash_credit_account') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('amount') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                            <?php echo Form::label('amount', 'Amount', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-8">
                            <?php echo Form::text('amount', $model->amount, ['class' => 'form-control famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?>
                            @if ($errors->has('amount'))
                            <span class="help-block">
                                <strong>{{ $errors->first('amount') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('deposit_date') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                            <?php echo Form::label('deposit_date', 'Deposit Date', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-8">
                            <?php echo Form::text('deposit_date', !empty($model->deposit_date) ? date('d-m-Y', strtotime($model->deposit_date)) : null, ['class' => 'form-control datepicker fdeposit_date', 'placeholder' => '']); ?>
                            @if ($errors->has('deposit_date'))
                            <span class="help-block">
                                <strong>{{ $errors->first('deposit_date') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('approved_by_user') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                            <?php echo Form::label('approved_by_user', 'Approved By', ['class' => 'control-label', 'placeholder' => 'Select User']); ?>
                        </div>
                        <div class="col-md-8">
                            <?php echo Form::select('approved_by_user', $allUsers, $model->approved_by_user, ['class' => 'form-control selectpicker fapproved_by_user', 'placeholder' => 'Select ...']); ?>
                            @if ($errors->has('approved_by_user'))
                            <span class="help-block">
                                <strong>{{ $errors->first('approved_by_user') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('comments') ? 'has-error' :'' }}">
                        <?php echo Form::label('comments', 'Comment', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-8">
                            <?php echo Form::textarea('comments', $model->comments, ['class' => 'form-control', 'placeholder' => '', 'rows' => '5']); ?>
                            @if ($errors->has('comments'))
                            <span class="help-block">
                                <strong>{{ $errors->first('comments') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
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

                <a class="btn btn-danger" href="{{url('depositcashcredit')}}" title="">Cancel</a>
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
    $('select').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        $('#createforms').on('submit', function(event) {
            $('.fcash_credit_account').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fdeposit_date').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.famount').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fapproved_by_user').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
        });
        $('#createforms').validate({
            errorPlacement: function(error, element) {
                if (element.attr("name") == "cash_credit_account") {
                    var pos = $('.fcash_credit_account button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "approved_by_user") {
                    var pos = $('.fapproved_by_user button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });
    })
</script>

@stop