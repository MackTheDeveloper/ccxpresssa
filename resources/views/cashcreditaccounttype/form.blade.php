@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Account Type' : 'Create Account Type'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('cashcreditaccounttype') }}">Manage Account Types</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.account-types')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Account Type' : 'Add Account Type'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('cashcreditaccounttype/update',$model->id);
                    else
                        $actionUrl = url('cashcreditaccounttype/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="required col-md-4">
                        <?php echo Form::label('quickbook_account_type_id', 'QuickBook Type',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::select('quickbook_account_type_id', $QbAccounts,$model->quickbook_account_type_id,['class'=>'form-control selectpicker fquickbook_account_type_id','data-live-search' => 'true','placeholder' => 'Select Account ...']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                        <?php echo Form::label('name', 'System Type',['class'=>'control-label']); ?>
                        </div>
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
                            
                            <a class="btn btn-danger" href="{{url('cashcreditaccounttype')}}" title="">Cancel</a>
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
        $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
        $('#createforms').on('submit', function (event) {
           $('.fname').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.fquickbook_account_type_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            
        });
        $('#createforms').validate({
            errorPlacement: function(error, element) {
                           if (element.attr("name") == "quickbook_account_type_id" )
                            {
                                var pos = $('.fquickbook_account_type_id button.dropdown-toggle');
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