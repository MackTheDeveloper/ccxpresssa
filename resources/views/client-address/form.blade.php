@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Client Address' : 'Create Client Address'; ?>
@stop
@section('sidebar')
<aside class="main-sidebar">
    <ul class="sidemenu nav navbar-nav side-nav">
        <li class="widemenu">
            <a href="{{ route('clientaddresses') }}">Manage Client Addresses</a>
        </li>
    </ul>
</aside>
@stop

@section('breadcrumbs')
    @include('menus.client-address')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Client Address' : 'Add Client Address'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('clientaddress/update',$model->id);
                    else
                        $actionUrl = url('clientaddress/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
                    <input type="hidden" name="flagFromWhere" value="<?php echo $flagFromWhere; ?>">
            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('client_id') ? 'has-error' :'' }}">
                        <div class="col-md-12 required row">
                        <?php echo Form::label('client_id', 'Client',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php 
                        $clientId = $model->client_id;
                        $model->client_id = $model->company_name;
                        echo Form::text('client_id',$model->client_id,['class'=>'form-control fclient_id']); 
                        ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="hidden_client_id"  id="hidden_client_id" value="<?php echo $clientId; ?>">
                <input type="hidden" name="company_name"  id="company_name" value="<?php echo $model->company_name; ?>">
                
                <div class="col-md-4">
                     <div class="form-group {{ $errors->has('client_branch_id') ? 'has-error' :'' }}">
                        <div class="col-md-12 required row">
                        <?php echo Form::label('client_branch_id', 'Branch',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php echo Form::select('client_branch_id', $clientBranch,$model->client_branch_id,['class'=>'form-control selectpicker fclient_branch_id', 'data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                            <div class="form-group {{ $errors->has('address') ? 'has-error' :'' }}">
                                <div class="col-md-12 row required">
                                <?php echo Form::label('address', 'Address',['class'=>'control-label']); ?>
                                </div>
                                <div class="col-md-12 row">
                                <?php echo Form::textarea('address',$model->address,['class'=>'form-control faddress','rows'=>1]); ?>
                                </div>
                            </div>
                </div>
            </div>

            

            

            

            <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('country_id') ? 'has-error' :'' }}">
                        <div class="col-md-12 required row">
                        <?php echo Form::label('country_id', 'Country',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php echo Form::select('country_id', $country,$model->country_id,['class'=>'form-control selectpicker fcountry_id', 'data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('state_id') ? 'has-error' :'' }}">
                        <div class="col-md-12 required row">
                        <?php echo Form::label('state_id', 'State',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php echo Form::select('state_id', $state,$model->state_id,['class'=>'form-control selectpicker fstate_id', 'data-live-search' => 'true','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('city') ? 'has-error' :'' }}">
                        <div class="col-md-12 row required">
                        <?php echo Form::label('city', 'City',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php echo Form::text('city',$model->city,['class'=>'form-control fcity']); ?>
                        </div>
                    </div>
                </div>
            </div>

             <div class="col-md-12">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('zipcode') ? 'has-error' :'' }}">
                        <div class="col-md-12 row">
                        <?php echo Form::label('zipcode', 'Zip/Postal Code',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-12 row">
                        <?php echo Form::text('zipcode',$model->zipcode,['class'=>'form-control']); ?>
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
                            
                            <a class="btn btn-danger" href="{{url('clientaddresses')}}" title="">Cancel</a>
                        </div>

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });

        $( "#client_id" ).autocomplete({
                select: function (event, ui) {
                    event.preventDefault();
                    $("#hidden_client_id").val(ui.item.value);
                    $("#company_name").val(ui.item.label);

                     var client    =    ui.item.value;
                     var urlzt = '<?php echo url("clientbranch/getbranches"); ?>';
                        $.ajax({
                                url:urlzt,
                                type:'POST',
                                data:{'client':client},
                                success:function(data) {
                                        $('#client_branch_id').html(data);
                                        $('.selectpicker').selectpicker('refresh'); 
                                        $('#loading').hide();
                                        }
                            });
                },
                focus: function (event, ui) {
                    event.preventDefault();
                    $("#client_id").val(ui.item.label);
                },
                change: function (event, ui)
                    {
                        if (ui.item == null || typeof (ui.item) == "undefined")
                        {
                            $('#client_id').val("");
                        }
                    },
                source: <?php echo $dataClient; ?>,
                minLength:1,
                });

        <?php if(!empty($clientId)) { ?>
            var client    =    '<?php echo $clientId; ?>';
            var urlzt = '<?php echo url("clientbranch/getbranches"); ?>';
                        $.ajax({
                                url:urlzt,
                                type:'POST',
                                data:{'client':client},
                                success:function(data) {
                                        $('#client_branch_id').html(data);
                                        $('.selectpicker').selectpicker('refresh'); 
                                        $('#loading').hide();
                                        }
                            });
        <?php } ?>

        $('#country_id').change(function(){
            $('loading').show();
            var id = $(this).val();
            
            var urlzt = '<?php echo url("clients/getstatesdata"); ?>';
            $.ajax({
                    url:urlzt,
                    type:'POST',
                    data:{'id':id},
                    success:function(data) {
                            $('#state_id').html(data);
                            $('.selectpicker').selectpicker('refresh'); 
                            $('#loading').hide();
                            }
                });
        })

        $('#createforms').on('submit', function (event) {
            $('.fclient_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.fclient_branch_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });

            $('.faddress').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.fphone_number').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                            number:true,
                        })
            });
             
            $('.fcountry_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.fstate_id').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            $('.fcity').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
            
        });
        $('#createforms').validate({
              errorPlacement: function(error, element) {
                        if (element.attr("name") == "client_branch_id" )
                        {
                        var pos = $('.fclient_branch_id button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if (element.attr("name") == "country_id" )
                        {
                        var pos = $('.fcountry_id button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if (element.attr("name") == "state_id" )
                        {
                        var pos = $('.fstate_id button.dropdown-toggle');
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