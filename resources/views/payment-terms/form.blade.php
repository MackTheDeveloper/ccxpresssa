@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Payment Term' : 'Add Payment Term'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('paymentterms') }}">Manage Payment Terms</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.payment-terms')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Payment Term' : 'Add Payment Term'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('paymentterm/update',$model->id);
                    else
                        $actionUrl = url('paymentterm/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('title') ? 'has-error' :'' }}">
                        <?php echo Form::label('title', 'Title',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::text('title',$model->title,['class'=>'form-control','placeholder' => 'Enter Title','class'=>'ftitle form-control']); ?>
                        @if ($errors->has('title'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('title') }}</strong>
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
                            
                            <a class="btn btn-danger" href="{{url('paymentterms')}}" title="">Cancel</a>
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
                $('.ftitle').each(function () {
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
