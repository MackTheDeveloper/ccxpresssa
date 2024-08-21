@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update User' : 'Create User'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('users') }}">Manage Users</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
@include('menus.user-management')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update User' : 'Add User'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('user/update', $model->id);
            else
                $actionUrl = url('register');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}

            <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                <div class="col-md-2 required">
                    <?php echo Form::label('name', 'Name', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-3">
                    <?php echo Form::text('name', $model->name, ['class' => 'form-control fname', 'placeholder' => 'Enter Name']); ?>
                    @if ($errors->has('name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('name') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group {{ $errors->has('email') ? 'has-error' :'' }}">
                <div class="col-md-2 required">
                    <?php echo Form::label('email', 'Email', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-3">
                    <?php echo Form::email('email', $model->email, ['class' => 'form-control femail', 'placeholder' => 'Enter Email']); ?>
                    @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group {{ $errors->has('department') ? 'has-error' :'' }}">
                <div class="col-md-2 required">
                    <?php echo Form::label('department', 'Department', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-3">
                    <?php echo Form::select('department', $departments, $model->department, ['class' => 'form-control selectpicker fdepartment', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                    @if ($errors->has('department'))
                    <span class="help-block">
                        <strong>{{ $errors->first('department') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="warehousediv form-group {{ $errors->has('warehouses') ? 'has-error' :'' }}" style="display: none;">
                <div class="col-md-2">
                    <?php echo Form::label('warehouses', 'Warehouses', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-3">
                    <?php echo Form::select('warehouses[]', $warehouses, $model->warehouses, ['class' => 'form-control selectpicker fdepartment', 'data-live-search' => 'true', 'multiple' => true]); ?>
                </div>
            </div>

            <?php if (!$model->id) { ?>
                <div class="form-group {{ $errors->has('password') ? 'has-error' :'' }}">
                    <div class="col-md-2 required">
                        <?php echo Form::label('password', 'Password', ['class' => 'control-label']); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo Form::password('password', ['class' => 'form-control fpassword', 'placeholder' => 'Enter Password']); ?>
                        @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group {{ $errors->has('password_confirmation') ? 'has-error' :'' }}">
                    <?php echo Form::label('password_confirmation', 'Confirm Password', ['class' => 'col-md-2 control-label']); ?>
                    <div class="col-md-3">
                        <?php echo Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Enter Password Again']); ?>
                    </div>
                </div>
            <?php } ?>

            <div class="col-md-6">
                <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                    <?php echo Form::label('status', 'Status', ['class' => 'col-md-4']); ?>
                    <div class="consolidate_flag-md-6 col-md-6">
                        <?php
                        echo Form::radio('status', '1', ($model->status == '1' || $model->status == '') ? 'checked' : '', ['class' => 'flagconsol', 'id' => 'active']);
                        echo Form::label('active', 'Active');
                        echo Form::radio('status', '0', $model->status == '0' ? 'checked' : '', ['class' => 'flagconsol', 'id' => 'inactive']);
                        echo Form::label('inactive', 'Inactive');
                        ?>
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

                <a class="btn btn-danger" href="{{url('users')}}" title="">Cancel</a>
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
    $(document).ready(function() {
        $('#createforms').on('submit', function(event) {
            $('.fname').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

            $('.femail').each(function() {
                $(this).rules("add", {
                    required: true,
                    email: true,
                })
            });

            $('.fdepartment').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fpassword').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

        });
        $('#createforms').validate({
            rules: {
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirmation: {
                    required: true,
                    equalTo: "#password",
                },
                "email": {
                    checkUniqueEmail: true
                }
            },
            messages: {
                password_confirmation: {
                    equalTo: "Password does not match."
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "department") {
                    var pos = $('.fdepartment button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $.validator.addMethod("checkUniqueEmail",
            function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("user/checkuniqueemail"); ?>';
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
                // return true if username is exist in database
                return result;
            },
            "This Email is already existed! Try another."
        );

        <?php if ($model->id) { ?>
            if ($('#department').val() == '14')
                $('.warehousediv').show();
            else
                $('.warehousediv').hide();
        <?php } ?>
        $('#department').change(function() {
            if ($(this).val() == '14')
                $('.warehousediv').show();
            else
                $('.warehousediv').hide();
        })
    });
</script>
@stop