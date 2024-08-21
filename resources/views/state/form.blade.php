@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update State' : 'Add State'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('states') }}">Manage States</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.state')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update State' : 'Add State'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('state/update',$model->id);
                    else
                        $actionUrl = url('state/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('country_id') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('country_id', 'Country',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::select('country_id', $country,$model->country_id,['class'=>'form-control selectpicker fcountry_id', 'data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        @if ($errors->has('country_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('country_id') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('name', 'Name',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('name',$model->name,['class'=>'form-control','placeholder' => 'Enter State Name','class'=>'fname form-control']); ?>
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
                    <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                    <?php echo Form::label('status', 'Status',['class'=>'col-md-4']); ?>
                    <div class="consolidate_flag-md-6 col-md-6">
                    <?php 
                       echo Form::radio('status', '1',$model->status == '1' || $model->status == '' ? 'checked' : '',['class'=>'flagconsol']); 
                                echo Form::label('', 'Active');
                                echo Form::radio('status', '0',$model->status == '0' ? 'checked' : '',['class'=>'flagconsol']); 
                                echo Form::label('', 'Inactive');   
                    ?>
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
                            
                            <a class="btn btn-danger" href="{{url('states')}}" title="">Cancel</a>
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
                
                $('.fcountry_id').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fname').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                });
            $('#createforms').validate({
                 errorPlacement: function(error, element) {
                        if (element.attr("name") == "country_id" )
                        {
                        var pos = $('.fcountry_id button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else
                        {
                        error.insertAfter(element);
                        }
                    }
            });   
        });
        
</script>
@stop
