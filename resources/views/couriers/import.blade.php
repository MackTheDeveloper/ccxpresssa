@extends('layouts.custom')
@section('title')
<?php echo 'Import Courier'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <?php 
        $checkPermissionCreateCourier = App\User::checkPermission(['create_couriers'],'',auth()->user()->id); 
        $checkPermissionImportCourier = App\User::checkPermission(['import_couriers'],'',auth()->user()->id);
        ?>
        <?php if($checkPermissionCreateCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('couriers') }}">Manage Couriers</a>
        </li>
        <?php } ?>
        <?php if($checkPermissionImportCourier) { ?>
        <li class="widemenu">
            <a href="{{ route('createcourier') }}">Create Couriers</a>
        </li>
        <?php } ?>
    </ul>
</aside>
@stop
@section('content')
<section class="content-header">
    <h1><?php echo 'Import Courier'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                        $actionUrl = url('courier/importdata');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','enctype'=>"multipart/form-data",'autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <div class="col-md-12">
                        
                            <div class="form-group {{ $errors->has('import_file') ? 'has-error' :'' }}">
                                <?php echo Form::label('import_file', 'Import File',['class'=>'col-md-4 control-label']); ?>
                                <div class="col-md-6" style="margin-top: 8px">
                                <?php echo Form::file('import_file'); ?>
                                @if ($errors->has('import_file'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('import_file') }}</strong>
                                            </span>
                                @endif
                                </div>
                            </div>
                        
                    </div>


                    <div class="col-md-12">
                    <div class="form-group">
                            <div class="col-md-12" style="text-align: right;padding-right: 120px;">
                                <button type="submit" class="btn btn-success">
                                    <?php
                                            echo "Import";
                                        
                                        ?>
                                </button>
                            </div>
                        </div>
                     </div>   

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
