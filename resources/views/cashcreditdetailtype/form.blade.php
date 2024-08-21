@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Account Sub Type' : 'Create Account Sub Type'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('cashcreditdetailtype') }}">Manage Detail Types</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.detail-types')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Account Sub Type' : 'Add Account Sub Type'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('cashcreditdetailtype/update',$model->id);
                    else
                        $actionUrl = url('cashcreditdetailtype/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('account_type_id') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                        <?php echo Form::label('account_type_id', 'System Type',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::select('account_type_id', $accoutTypes,$model->account_type_id,['class'=>'form-control selectpicker faccount_type_id','data-live-search' => 'true','placeholder' => 'Select Account ...']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('quickbook_account_sub_type_id') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                        <?php echo Form::label('quickbook_account_sub_type_id', 'QuickBook Detail Type',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::select('quickbook_account_sub_type_id',$subAccounts,$model->quickbook_account_sub_type_id,['class'=>'form-control selectpicker fquickbook_account_sub_type_id','data-live-search' => 'true','placeholder' => 'Select Sub Account ...']); ?>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="quickbook_account_type_id" class="quickbook_account_type_id" value="<?php echo $model->quickbook_account_type_id; ?>">
                

                 <div class="col-md-6">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' :'' }}">
                        <div class="required col-md-4">
                        <?php echo Form::label('name', 'System Detail Type',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('name',$model->name,['class'=>'form-control fname','placeholder' => 'Enter Name']); ?>
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
                            
                            <a class="btn btn-danger" href="{{url('cashcreditdetailtype')}}" title="">Cancel</a>
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

        if($('#quickbook_account_sub_type_id').val() == '')
        {
            setTimeout(function(){
                $('#account_type_id').trigger('change');
            },100);
        }

        $('#account_type_id').change(function(){
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            var id = $(this).val();
            $.ajax({
            type    : 'post',
            dataType: "json",
            url     : '<?php echo url('cashcreditdetailtype/getqbsubaccounts'); ?>',
            data    : {'id':id},
            success : function (response) {
                //console.log(response.subTypes);
                
                var userList = response.subTypes;
                var html = '<option value="">-- Select --</option>';
                 $(userList).each(function(k,v){
                    html += '<option value="'+v.id+'">'+ v.name+'</option>';
                });
                $('#quickbook_account_sub_type_id').html(html);
                $('.quickbook_account_type_id').val(response.quickbook_account_type_id);
                $('#quickbook_account_sub_type_id').selectpicker('refresh');
                $('#loading').hide();
                },
                error: function () {
                     $('#loading').hide();
                }
            });
        })

         $('#createforms').on('submit', function (event) {
           

            $('.faccount_type_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });

            $('.fquickbook_account_sub_type_id').each(function () {
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
                if (element.attr("name") == "account_type_id" )
                {
                    var pos = $('.faccount_type_id button.dropdown-toggle');
                    error.insertAfter(pos);
                }
                else if (element.attr("name") == "quickbook_account_sub_type_id" )
                {
                    var pos = $('.fquickbook_account_sub_type_id button.dropdown-toggle');
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