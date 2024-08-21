@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Delivery Boy' : 'Create Delivery Boy'; ?>
@stop

@section('breadcrumbs')
@include('menus.delivery-boy')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Delivery Boy' : 'Add Delivery Boy'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('deliveryboy/update', $model->id);
            else
                $actionUrl = url('deliveryboy/store');
            ?>

            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-2 required">
                        <?php echo Form::label('name', 'Name', ['class' => 'control-label']); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo Form::text('name', $model->name, ['class' => 'form-control fname', 'placeholder' => 'Enter Name']); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-2">
                        <?php echo Form::label('email', 'Email', ['class' => 'control-label']); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo Form::email('email', $model->email, ['class' => 'form-control femail', 'placeholder' => 'Enter Email']); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group {{ $errors->has('phone_number') ? 'has-error' :'' }}">
                    <div class="col-md-2 required">
                        <?php echo Form::label('phone_number', 'Phone Number', ['class' => 'control-label']); ?>
                    </div>
                    <div class="col-md-4">
                        <?php echo Form::text('phone_number', $model->phone_number, ['class' => 'form-control fphone_number', 'placeholder' => 'Enter Phone Number']); ?>
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
                <a class="btn btn-danger" href="{{url('deliveryboys')}}" title="">Cancel</a>
            </div>

            {{ Form::close() }}


        </div>
    </div>
</section>
@endsection

@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        $('#createforms').on('submit', function(event) {
            $('.fphone_number').each(function() {
                $(this).rules("add", {
                    required: true,
                    //number: true    
                    //telephonecheck : true
                })
            });
            $('.fname').each(function() {

                $(this).rules("add", {
                    required: true,
                })
            });
            $('.femail').each(function() {

                $(this).rules("add", {
                    email: true,
                    required: true,
                })
            });
        });

        $('#createforms').validate();
    });
</script>
@stop