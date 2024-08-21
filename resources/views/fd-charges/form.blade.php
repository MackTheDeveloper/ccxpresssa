@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Charge' : 'Add Charge'; ?>
@stop


@section('breadcrumbs')
    @include('menus.fdcharges')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Charge' : 'Add Charge'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('fdcharges/update',$model->id);
                    else
                        $actionUrl = url('fdcharges/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('name', 'Name',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('name',$model->name,['class'=>'form-control','placeholder' => 'Enter Name','class'=>'fname form-control']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('code') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('code', 'Code',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('code',$model->code,['class'=>'form-control','placeholder' => 'Enter Code','class'=>'fcode form-control']); ?>
                        </div>
                    </div>
                </div>
                
                
            </div>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('charge') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('charge', 'Charge',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('charge',$model->charge,['class'=>'form-control','placeholder' => 'Enter Charge','class'=>'fcharge form-control']); ?>
                        </div>
                    </div>
                </div>
            </div>

            

            <div class="form-group col-md-12 btm-sub">
                            
                                <button type="submit" class="btn btn-success">
                                    <?php
                                        if(!$model->id)
                                            echo "Submit";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                            
                            <a class="btn btn-danger" href="{{url('fdcharges')}}" title="">Cancel</a>
            </div>

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    
        $(document).ready(function() {
             $('#createforms').on('submit', function (event) {
                $('.fname').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fcode').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fcharge').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                                number : true,
                                //min: 1,
                            })
                     });
                });
            $('#createforms').validate(
                /* {
                messages: {
                        charge: 
                        {
                        min : "Value must be greater than 0",
                        }
                    }
                } */
            );   
        });
        
</script>
@stop
