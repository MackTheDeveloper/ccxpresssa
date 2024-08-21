@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Storage Charge' : 'Add Storage Charge'; ?>
@stop


@section('breadcrumbs')
    @include('menus.storage-charges')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Storage Charge' : 'Add Storage Charge'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('storagecharge/update',$model->id);
                    else
                        $actionUrl = url('storagecharge/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('grace_period') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                        <?php echo Form::label('grace_period', 'Grace Period (days)',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('grace_period',$model->grace_period,['class'=>'form-control fgrace_period','placeholder' => 'Enter Grace Period','onkeypress'=>'return isNumber(event)']); ?>
                        </div>
                    </div>
                </div>
            </div>   
            
            <div class="col-md-12">             
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('measure') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                        <?php echo Form::label('measure', 'Weight / Volume',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::select('measure',array('K'=>'Kg','P'=>'Pound','M'=>'Cubic Meter','F'=>'Cubic Feet'),$model->measure,['class'=>'fmeasure form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('charge') ? 'has-error' :'' }}">
                        <div class="col-md-5 required">
                        <?php echo Form::label('charge', 'Charge (After Grace Period)',['class'=>'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                        <?php echo Form::text('charge',$model->charge,['class'=>'form-control fcharge','placeholder' => 'Enter Charge','onkeypress'=>'return isNumber(event)']); ?>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                    <?php echo Form::label('status', 'Status',['class'=>'col-md-5']); ?>
                    <div class="consolidate_flag-md-6 col-md-6">
                    <?php 
                       echo Form::radio('status', '1',($model->status == '1' || $model->status == '') ? 'checked' : '',['class'=>'flagconsol','id'=>'statusactive']); 
                                echo Form::label('statusactive', 'Active');
                                echo Form::radio('status', '0',$model->status == '0' ? 'checked' : '',['class'=>'flagconsol','id'=>'statusinactive']); 
                                echo Form::label('statusinactive', 'Inactive');   
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
                            
                            <a class="btn btn-danger" href="{{url('storagecharges')}}" title="">Cancel</a>
            </div>

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
         function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
                return false;
            }
            return true;
        }
        $('select').change(function(){
                    if ($(this).val()!="")
                    {
                        $(this).valid();
                    }
                });
        $(document).ready(function() {
             $('#createforms').on('submit', function (event) {
                
                $('.fgrace_period').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fmeasure').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fcharge').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                });
            $('#createforms').validate({
                errorPlacement: function(error, element) {
                        if (element.attr("name") == "measure" )
                        {
                        var pos = $('.fmeasure button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else
                        {
                        error.insertAfter(element);
                        }
                    },
                    rules: {
                        "measure": {
                            required: true,
                            checkuniquemeasure: true
                        }
                    }
                    
            });   

            $.validator.addMethod("checkuniquemeasure", 
                function(value, element) {
                    $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                    });
                    var result = false;
                    var urlz = '<?php echo url("storagecharge/checkuniquemeasure"); ?>';
                    <?php if(!empty($model->id)) { ?>
                        var id = '<?php echo $model->id; ?>';
                    <?php } else { ?>
                        var id = '';
                    <?php } ?>
                    $.ajax({
                        type:"POST",
                        async: false,
                        url: urlz,
                        data: {measure: value,id:id},
                        success: function(data) {
                            result = (data == 0) ? true : false;
                        }
                    });
                    // return true if username is exist in database
                    return result; 
                }, 
                "Already taken! Try another."
            );

        });
        
</script>
@stop
