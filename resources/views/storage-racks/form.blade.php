@extends('layouts.custom')
@section('title')
<?php echo $model->id ? 'Update Storage Rack' : 'Add Storage Rack'; ?>
@stop


@section('breadcrumbs')
    @include('menus.storage-racks')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $model->id ? 'Update Storage Rack' : 'Add Storage Rack'; ?></h1>
</section>

<section class="content">
    <div class="box box-success">
        <div class="box-body">
                    <?php
                    if($model->id)
                        $actionUrl = url('storagerack/update',$model->id);
                    else
                        $actionUrl = url('storagerack/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-1">
                    <?php echo Form::label('location', 'Location',['class'=>'control-label']); ?>
                </div>
                <div class="col-md-2">
                    <div class="form-group {{ $errors->has('rack_department') ? 'has-error' :'' }}">
                        <div class="col-md-12">
                        <?php echo Form::select('rack_department',Config::get('app.rackDepartment') ,$model->rack_department,['class'=>'frack_department form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select Department']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group {{ $errors->has('main_section') ? 'has-error' :'' }}">
                        <div class="col-md-12">
                        <?php echo Form::select('main_section',$locationNumbers ,$model->main_section,['class'=>'fmain_section form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select Main Section']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group {{ $errors->has('sub_section') ? 'has-error' :'' }}">
                        <div class="col-md-12">
                        <?php echo Form::select('sub_section',$alphaAll ,$model->sub_section,['class'=>'fsub_section form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select Sub Section']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group {{ $errors->has('location_number') ? 'has-error' :'' }}">
                        <div class="col-md-12">
                        <?php echo Form::select('location_number',$locationNumbers ,$model->location_number,['class'=>'flocation_number form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select Number']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-2" style="display: none;">
                    <div class="form-group {{ $errors->has('location_number') ? 'has-error' :'' }}">
                        <div class="col-md-12">
                        <input type="button" class="btn btn-success check-availability" value="Check Availability" />
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="col-md-12">
                <div class="col-md-1"></div>
                <div class="col-md-6">
                <span class="msgavailability"></span>
                </div>
            </div>

            <div class="col-md-12" style="margin-top: 10px">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('status') ? 'has-error' :'' }}">
                    <?php echo Form::label('status', 'Status',['class'=>'col-md-2']); ?>
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
                            
                                <button type="submit" class="btn btn-success btn-success-form" disabled="">
                                    <?php
                                        if(!$model->id)
                                            echo "Submit";
                                        else
                                            echo "Update";
                                        ?>
                                </button>
                            
                            <a class="btn btn-danger" href="{{url('storageracks')}}" title="">Cancel</a>
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
                
                $('.frack_department').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                     });
                $('.fmain_section').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                });
                $('.fsub_section').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                });
                $('.flocation_number').each(function () {
                    $(this).rules("add",
                            {
                                required: true,
                            })
                });
            });
            $('#createforms').validate({
                errorPlacement: function(error, element) {
                        if (element.attr("name") == "rack_department" )
                        {
                        var pos = $('.frack_department button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if (element.attr("name") == "main_section" )
                        {
                        var pos = $('.fmain_section button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if (element.attr("name") == "sub_section" )
                        {
                        var pos = $('.fsub_section button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else if (element.attr("name") == "location_number" )
                        {
                        var pos = $('.flocation_number button.dropdown-toggle');
                        error.insertAfter(pos);
                        }
                        else
                        {
                        error.insertAfter(element);
                        }
                    }
            });   


            <?php if($model->id) { ?>
                $('.btn-success-form').attr('disabled',false);
            <?php } ?>
            $('.frack_department,.fmain_section,.fsub_section,.flocation_number').change(function(){
                        var rackDepartment = $('.frack_department :selected').val();
                        var mainSection = $('.fmain_section :selected').val();
                        var subSection = $('.fsub_section :selected').val();
                        var locationNumber = $('.flocation_number :selected').val();
                        if(rackDepartment != '' && mainSection != '' && subSection != '' && locationNumber != '')
                        {
                        $.ajaxSetup({
                            headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var result = false;
                        var urlz = '<?php echo url("storagerack/checkuniqueracklocation"); ?>';
                        var flag = '<?php echo $model->id ? 'edit' : '' ?>';
                        var idz = "<?php echo $model->id ? $model->id : '' ?>";
                        $.ajax({
                            type:"POST",
                            async: false,
                            url: urlz,
                            data: {rackDepartment: rackDepartment,flag:flag,idz:idz,mainSection:mainSection,subSection:subSection,locationNumber:locationNumber},
                            success: function(data) {
                                if(data == 0)
                                {
                                    $('.msgavailability').text('Storage Rack Location is available.').css('color','green');
                                    $('.btn-success-form').attr('disabled',false);
                                }
                                else
                                {
                                    $('.msgavailability').text('This Storage Rack Location is already created.').css('color','red');
                                    $('.btn-success-form').attr('disabled',true);
                                }
                            }
                        });
                    }else
                    {
                        $('.msgavailability').text('Please select all options.').css('color','red');
                        $('.btn-success-form').attr('disabled',true);
                    }
                })

            $('.check-availability').click(function(){
                        var rackDepartment = $('.frack_department :selected').val();
                        var mainSection = $('.fmain_section :selected').val();
                        var subSection = $('.fsub_section :selected').val();
                        var locationNumber = $('.flocation_number :selected').val();
                        if(rackDepartment != '' && mainSection != '' && subSection != '' && locationNumber != '')
                        {
                        $.ajaxSetup({
                            headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        var result = false;
                        var urlz = '<?php echo url("storagerack/checkuniqueracklocation"); ?>';
                        var flag = '<?php echo $model->id ? 'edit' : '' ?>';
                        var idz = "<?php echo $model->id ? $model->id : '' ?>";
                        $.ajax({
                            type:"POST",
                            async: false,
                            url: urlz,
                            data: {rackDepartment: rackDepartment,flag:flag,idz:idz,mainSection:mainSection,subSection:subSection,locationNumber:locationNumber},
                            success: function(data) {
                                if(data == 0)
                                {
                                    $('.msgavailability').text('Storage Rack Location is available.').css('color','green');
                                    $('.btn-success-form').attr('disabled',false);
                                }
                                else
                                {
                                    $('.msgavailability').text('This Storage Rack Location is already created.').css('color','red');
                                    $('.btn-success-form').attr('disabled',true);
                                }
                            }
                        });
                    }else
                    {
                        $('.msgavailability').text('Please select all options.').css('color','red');
                        $('.btn-success-form').attr('disabled',true);
                    }
                })
            });
        
</script>
@stop
