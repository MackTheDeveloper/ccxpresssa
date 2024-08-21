@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Cash/Bank' : 'Add Cash/Bank'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('cashcredit') }}">Manage Cash/Credit</a>
        </li>
        <li class="widemenu">
            <a href="{{ route('createdepositcashcredit') }}">Replenish Account</a>
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
    <h1><?php echo $model->id ? 'Update Cash/Bank' : 'Add Cash/Bank'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('cashcredit/update', $model->id);
            else
                $actionUrl = url('cashcredit/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12">

                <div class="col-md-6">
                    <input type="hidden" name="account_type" value="1">
                    <div class="form-group {{ $errors->has('detail_type') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                            <?php echo Form::label('detail_type', 'Cash/Bank Type', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('detail_type', $types, $model->detail_type, ['class' => 'form-control selectpicker fdetailtype', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            @if ($errors->has('detail_type'))
                            <span class="help-block">
                                <strong>{{ $errors->first('detail_type') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                            <?php echo Form::label('name', 'Name', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('name', $model->name, ['class' => 'form-control fname', 'placeholder' => 'Enter Name']); ?>
                            @if ($errors->has('name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>


            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('opening_balance') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                            <?php echo Form::label('opening_balance', 'Opening Balance', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('opening_balance', $model->opening_balance, ['class' => 'form-control fopeningbalance', 'placeholder' => 'Enter Opening Balance']); ?>
                            @if ($errors->has('opening_balance'))
                            <span class="help-block">
                                <strong>{{ $errors->first('opening_balance') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('as_of') ? 'has-error' :'' }}">
                        <?php echo Form::label('as_of', 'as of', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('as_of', $model->as_of, ['class' => 'form-control datepicker fasof', 'placeholder' => 'Enter as of']); ?>
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
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('currency') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                            <?php echo Form::label('currency', 'Currency', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('currency', $currency, $model->currency, ['class' => 'form-control selectpicker fcurrency', 'placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('description') ? 'has-error' :'' }}">
                        <?php echo Form::label('description', 'Description', ['class' => 'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::textarea('description', $model->description, ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Enter Description']); ?>
                            @if ($errors->has('description'))
                            <span class="help-block">
                                <strong>{{ $errors->first('description') }}</strong>
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

                <a class="btn btn-danger" href="{{url('cashcredit')}}" title="">Cancel</a>
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
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        $('#createforms').on('submit', function(event) {


            $('.fdetailtype').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

            $('.fopeningbalance').each(function() {
                $(this).rules("add", {
                    required: true,
                    number: true
                })
            });
            $('.fasof').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fcurrency').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
        });
        $('#createforms').validate({

            errorPlacement: function(error, element) {
                if (element.attr("name") == "detail_type") {
                    var pos = $('.fdetailtype button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "currency") {
                    var pos = $('.fcurrency button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                "name": {
                    required: true,
                    chechUniqueAccountName: true
                },

            }

        });

        $.validator.addMethod('chechUniqueAccountName', function(value, element) {
                //alert('test');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = true;
                var urlz = "<?php echo url('cashcredit/checkunique'); ?>";
                var id = '';
                <?php
                if ($model->id) { ?>
                    var id = "<?php echo $model->id; ?>";
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

                return result;
            },
            "This Name is already taken! Try another."

        );
    });
</script>
@stop