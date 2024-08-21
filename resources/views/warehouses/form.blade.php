@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Warehouse' : 'Add Warehouse'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('countries') }}">Manage Countries</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.warehouses')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Warehouse' : 'Add Warehouse'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('warehouse/update',$model->id);
                    else
                        $actionUrl = url('warehouse/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
             <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('warehouse_for') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('warehouse_for', 'Used For',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::select('warehouse_for',Config::get('app.warehouseFor'),$model->warehouse_for,['class'=>'form-control','placeholder' => 'Select','class'=>'fwarehouse_for form-control selectpicker']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <div class="col-md-4 required">
                        <?php echo Form::label('name', 'Name',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('name',$model->name,['class'=>'form-control','placeholder' => 'Enter Name','class'=>'fname form-control']); ?>
                        @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>
                </div>
                
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
                            
                            <a class="btn btn-danger" href="{{url('warehouses')}}" title="">Cancel</a>
            </div>

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
        $('select').change(function(){
                    if ($(this).val()!="")
                    {
                        $(this).valid();
                    }
                });
        $(document).ready(function() {
             $('#createforms').on('submit', function (event) {
                $('.fname').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fwarehouse_for').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                
                });
            $('#createforms').validate({
                errorPlacement: function(error, element) {
                        if (element.attr("name") == "warehouse_for" )
                        {
                        var pos = $('.fwarehouse_for button.dropdown-toggle');
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
