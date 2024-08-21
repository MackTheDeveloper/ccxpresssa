@extends('layouts.custom')
@section('title')
<?php echo 'Upload Aeropost File'; ?>
@stop

@section('breadcrumbs')
@include('menus.aeropost')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Upload Aeropost File'; ?></h1>
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
            $actionUrl = url('aeropost/importdata');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','enctype'=>"multipart/form-data",'autocomplete'=>'off')) }}
            {{ csrf_field() }}

            <div class="col-md-12">


                <div class="upload_div">
                    <div class="form-group">
                        <label class="control-label col-md-2">Storage</label>

                        <div class="col-md-6">
                            <?php
                            echo Form::radio('storage', '1', true);
                            echo Form::label('Local', 'Local', ['style' => 'margin-left:0.5%;margin-right:2%']);
                            echo Form::radio('storage', '2');
                            echo Form::label('Cloud', 'Cloud', ['style' => 'margin-left:0.5%']);


                            ?>
                        </div>
                    </div>
                    <div class="form-group" style="display: none;" id="cloud_file">
                        <?php echo Form::label('s3file', 'Upload File', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="File" name="s3file" id="s3file" readonly="ture">
                                <div class="input-group-btn">
                                    <a class="btn btn-primary" type="submit" href="javascript:void(0)" id="s3Btn">
                                        Select
                                    </a>
                                </div>
                            </div>
                            <label id="s3fileError" class="error"></label>
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('import_file') ? 'has-error' :'' }}">
                        <?php echo Form::label('import_file', 'Upload File', ['class' => 'col-md-2 control-label import_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('import_file', ['id' => 'import_file']); ?>
                            @if ($errors->has('import_file'))
                            <span class="help-block">
                                <strong>{{ $errors->first('import_file') }}</strong>
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
                    <a class="btn btn-danger" href="{{url('aeroposts')}}" title="">Cancel</a>


                </div>

            </div>




            {{ Form::close() }}


        </div>
    </div>
</section>
@endsection

@section('page_level_js')
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js" type="text/javascript" charset="utf-8" async defer></script>
<script type="text/javascript">
    $('#import_file').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });
    $('input[type="radio"]').each(function() {
        $(this).change(function() {
            if ($(this).val() == '1') {
                $('#cloud_file').hide();
                $('input[type="file"]').each(function() {
                    $(this).show();
                    $('.' + $(this).attr('id')).show();
                });
            } else {
                $('input[type="file"]').each(function() {
                    $(this).hide();
                    $('p').hide();
                    $('.' + $(this).attr('id')).hide();
                    $('#' + $(this).attr('id') + '-error').hide();
                });
                $('#cloud_file').show();
            }
        });

    });
    $(document).ready(function() {
        $('.create-form').on('submit', function(event) {});
        $('.create-form').validate({
            rules: {
                s3file: {
                    required: true,
                },
                import_file: {
                    required: true,
                    extension: "xls|xlsx"
                }
            },
            errorPlacement: function(error, element) {

                if (element.attr("id") == "s3file") {
                    var pos = $('.input-group');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            },

        });

    });
</script>
<script type="text/javascript">
    $('#s3Btn').on('click', function() {
        var url = '<?php echo url('public/index.html'); ?>';
        var opener = window.open(url, 'targetWindow',
            'toolbar=no,location=middle,status=no,menubar=no,scrollbars=yes,resizable=yes,width=1000, height=600');

        function handlePostMessage(e) {
            var data = e.originalEvent.data;
            //console.log('js-data', data);
            if (data.source === 'richfilemanager') {
                console.log(data.resourceObject.attributes.path);
                $('#s3file').val(data.resourceObject.attributes.path);
                var value = $('#s3file').val();
                if (value != '') {
                    var arr = value.split('.');
                }
                $('#s3file').valid();
                var ext = arr[(arr.length) - 1];
                var rules = ['xls', 'xlsx'];

                if (!(rules.includes(ext))) {
                    $('#s3fileError').show();
                    $('#s3fileError').html("select valid input file format with extension 'xls' or 'xlsx'.");
                } else {
                    $('#s3fileError').hide();
                }

                console.log(arr[(arr.length) - 1]);


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