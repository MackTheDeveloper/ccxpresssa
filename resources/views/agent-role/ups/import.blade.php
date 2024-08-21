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
                        $actionUrl = url('agentups/importupsdataagent');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','enctype'=>"multipart/form-data",'autocomplete'=>'off','id'=>'create-form')) }}
                    {{ csrf_field() }}

                    <div class="col-md-12">

                            <div class="form-group">
                                <?php echo Form::label('action', 'Select Action',['class'=>'col-md-2 control-label']); ?>
                                <div class="col-md-3">
                                <select class="selectpicker form-control" id="actionsdp" name="actions">
                                   <option value="delivery_scan">Delivery Scan</option>
                                </select>
                                </div>
                            </div>
                             <div class="form-group">
                                    <label class="control-label col-md-2">Storage</label>
                                
                                    <div class="col-md-6">
                                        <?php
                                            echo Form::radio('storage','1',true);
                                            echo Form::label('Local', 'Local',['style'=>'margin-left:0.5%;margin-right:2%']);
                                            echo Form::radio('storage', '2'); 
                                            echo Form::label('Cloud', 'Cloud',['style'=>'margin-left:0.5%']);

                                        
                                        ?>
                                    </div>
                                </div>
                            <div class="form-group" style="display: none;" id="cloud_file">
                                <?php echo Form::label('s3file', 'Upload File',['class'=>'col-md-2 control-label']); ?>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="File" name="s3file" id="s3file" readonly="true">
                                        <div class="input-group-btn">
                                            <a class="btn btn-primary" type="submit" href="javascript:void(0)" id="s3Btn">
                                               Select
                                            </a>
                                        </div>
                                    </div>
                                    <label id="s3fileError" class="error"></label>
                                </div>
                            </div>
                            

                            <!-- <div class="destination_scan_div">
                                    <div class="form-group {{ $errors->has('import_file_destination_scan') ? 'has-error' :'' }}">
                                        <?php echo Form::label('import_file_destination_scan', 'Upload File',['class'=>'col-md-2 control-label']); ?>
                                        <div class="col-md-6">
                                        <?php echo Form::file('import_file_destination_scan'); ?>
                                        </div>
                                    </div>
                            </div> -->


                            <div class="delivery_scan_div">
                               
                                <div class="form-group {{ $errors->has('delivery_scan_file') ? 'has-error' :'' }}">
                                        <?php echo Form::label('delivery_scan_file', 'Upload File',['class'=>'col-md-2 control-label delivery_scan_file']); ?>
                                        <div class="col-md-6">
                                        <?php echo Form::file('delivery_scan_file',['id'=>'delivery_scan_file']); ?>
                                        @if ($errors->has('export_file'))
                                                    <span class="help-block">
                                                        <strong>{{ $errors->first('delivery_scan_file') }}</strong>
                                                    </span>
                                        @endif
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
                s3file: {
                    required: true,
                },
                delivery_scan_file: {
                  required: true,
                  extension: "xls|xlsx"
                },
            },
            messages: {
                delivery_scan_file :{
                    extension:"select valid input file format with extension 'xls' or 'xlsx'.",   
                },
            },

            errorPlacement: function(error, element) {

                if (element.attr("id") == "s3file" )
                {
                    var pos = $('.input-group');
                    error.insertAfter(pos);
                }
                else
                {
                    error.insertAfter(element);
                }
            },
        });
    });
</script>

<script type="text/javascript">
    $('#s3Btn').on('click', function() {
                var url = '<?php echo url('public/index.html');?>';
                var opener = window.open(url, 'targetWindow',
                    'toolbar=no,location=middle,status=no,menubar=no,scrollbars=yes,resizable=yes,width=1000, height=600');

                function handlePostMessage(e) {
                    var data = e.originalEvent.data;
                    //console.log('js-data', data);
                    if (data.source === 'richfilemanager') {
                        console.log(data.resourceObject.attributes.path);
                        $('#s3file').val(data.resourceObject.attributes.path);
                        var value = $('#s3file').val();
                        if(value != ''){
                            var arr = value.split('.');
                        }
                        $('#s3file').valid();
                        var ext = arr[(arr.length)-1];
                        var actionArr = ['delivery_scan']
                        var action = $('#actionsdp').val();
                        var rules;
                        if(actionArr.includes(action)){
                            rules = ['xls','xlsx'];
                            if(!(rules.includes(ext))){
                                $('#s3fileError').show();
                                $('#s3fileError').html("select valid input file format with extension 'xls' or 'xlsx'.");
                            } else {
                                $('#s3fileError').hide();
                            }   
                        }
                        console.log(arr[(arr.length)-1]);
                        

                        var path = data.resourceObject.attributes.name;
                        console.log(path);
                        var url = 'https://s3.us-east-1.amazonaws.com/cargo-live-site/Files/1471382139_dr_jacquie_smiles_300x300.jpg';
                       // location.replace(url);
                        
                        opener.close();
                    }

                    // remove an event handler
                    $(window).off('message', handlePostMessage);
                }

                $(window).on('message', handlePostMessage);
            });
</script>

@stop
