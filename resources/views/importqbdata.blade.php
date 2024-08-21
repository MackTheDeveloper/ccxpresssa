@extends('layouts.custom')
@section('title')
<?php echo 'Upload Files'; ?>
@stop

@section('breadcrumbs')
    @include('menus.agent-ups-files')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Upload Files'; ?></h1>
</section>

<section class="content">
    @if(Session::has('flash_message_error'))
    <div class="alert alert-danger flash-danger">
        {{ Session::get('flash_message_error') }}
    </div>
    @endif
    <div class="box box-success">
        <div class="box-body">
                    <?php
                        $actionUrl = url('importqbdata');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','enctype'=>"multipart/form-data",'autocomplete'=>'off','id'=>'create-form')) }}
                    {{ csrf_field() }}

                    <div class="col-md-12">

                            <div class="form-group">
                                <?php echo Form::label('action', 'Select Module',['class'=>'col-md-2 control-label']); ?>
                                <div class="col-md-3">
                                <select class="selectpicker form-control" id="actionsdp" name="flag">
                                   <option value="customer">Customer</option>
                                   <option value="vendor">Vendor</option>
                                </select>
                                </div>
                            </div>
                      </div>    
                            


                     <div class="col-md-12">           
                        <div class="form-group">
                            <?php echo Form::label('import', 'Upload File',['class'=>'col-md-2 control-label import']); ?>
                                <div class="col-md-6">
                                    <?php echo Form::file('import',['id'=>'import']); ?>
                                </div>
                        </div>

                    </div>     
                            
                    
                            <div class="form-group col-md-12 btm-sub">
                    
                            
                                <button type="submit" class="btn btn-success">
                                    <?php
                                            echo "Import";
                                        
                                        ?>
                                </button>
                                <a class="btn btn-danger" href="{{url('ups')}}" title="">Cancel</a>
                            
                        
                            </div> 
                        
                    </div>


                    

                    {{ Form::close() }}


        </div>
    </div>
</section>
@endsection

@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {

        $('input[type="file"]').each(function(){
            $(this).change(function(){
                if ($(this).val()!="")
                {
                    $(this).valid();
                }
            });
            
        });

        $('input[type="radio"]').each(function(){
            $(this).change(function(){
                if($(this).val() == '1'){
                    $('#cloud_file').hide();
                    $('input[type="file"]').each(function(){
                        $(this).show();
                        $('#cloud_file input').val(null);
                        $('.'+$(this).attr('id')).show();
                    });
                } else {
                    $('input[type="file"]').each(function(){
                        $(this).hide();
                        $(this).val(null);
                        $('p').hide();
                        $('.'+$(this).attr('id')).hide();
                        $('#'+$(this).attr('id')+'-error').hide();
                    });
                    $('#cloud_file').show();
                }
            });
            
        });

        $('#create-form').on('submit', function (event) {
        });

        $('#create-form').validate({
            rules: {
                import: {
                  required: true,
                  extension: "xls|xlsx|ods"
                },
            },
            messages: {
                delivery_scan_file :{
                    extension:"select valid input file format with extension 'xls' or 'xlsx'.",   
                },
            },

        });
    });
</script>



@stop
