@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Nature Of Service' : 'Create Nature Of Service'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('natureofservices') }}">Manage Nature Of Services</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.nature-of-service')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Nature Of Service' : 'Add Nature Of Service'; ?></h1>
</section>
<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            if($model->id)
            $actionUrl = url('natureofservice/update',$model->id);
            else
            $actionUrl = url('natureofservice/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','autocomplete'=>'off','id'=>'createforms')) }}
            {{ csrf_field() }}
            
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <?php echo Form::label('name', 'Name',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                            <?php echo Form::text('name',$model->name,['class'=>'form-control fname','placeholder' => 'Enter Name']); ?>
                            @if ($errors->has('name'))
                            <span class="help-block">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                            @endif
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
                
                <a class="btn btn-danger" href="{{url('natureofservice')}}" title="">Cancel</a>
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
});
$('#createforms').validate();
});
</script>
@stop