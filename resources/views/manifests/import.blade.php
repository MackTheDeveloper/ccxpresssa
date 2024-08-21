@extends('layouts.custom')
@section('title')
<?php echo 'Import Manifest File'; ?>
@stop

@section('breadcrumbs')
@include('menus.manifests')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Import Manifest File'; ?></h1>
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
            $actionUrl = url('manifests/importdata');
            ?> {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','enctype'=>"multipart/form-data",'autocomplete'=>'off')) }} {{ csrf_field() }}
            <div class="col-md-12">


                <div class="upload_div">
                    <div class="form-group {{ $errors->has('import_file') ? 'has-error' :'' }}">
                        <?php echo Form::label('import_file', 'Upload File', ['class' => 'col-md-2 control-label import_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('import_file', ['id' => 'import_file']); ?>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-12 btm-sub">
                    <button type="submit" class="btn btn-success">
                        <?php
                        echo "Import";
                        ?>
                    </button>
                    <a class="btn btn-danger" href="{{url('manifests/listing')}}" title="">Cancel</a>
                </div>
            </div>
            {{ Form::close() }}
            <button style="float: right" id="clsReload" class="btn btn-warning"><span><i class="fa fa-refresh" aria-hidden="true" style="margin-right: 3%"></i></span>Refresh</button>
            <div style="display:none;float: left;width:100%;margin-top:20px" class="progress"></div>
            <div class="divFileDetails" style="width: 100%;clear: both;margin-top: 50px;background: #f5f5f5;">
                <table id="fileDetails" class="table display nowrap" style="width:100%">
                    <thead style="background: #d2d2d2;">
                        <tr>
                            <th>File Name</th>
                            <th>Total Pages</th>
                            <th>Status</th>
                            <th>Added By</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fileDetails) && count($fileDetails) > 0) { ?>
                            @foreach ($fileDetails as $items)
                            <?php $userData = app('App\User')->getUserName($items->created_by); ?>
                            <tr>
                                <td><a target="_blank" href="{{URL::to('/public/manifestsAll/'.$items->file_name)}}">{{$items->file_name}}</a></td>
                                <td>{{!empty($items->total_pages) ? $items->total_pages : '-'}}</td>
                                <td><b style="<?php echo $items->upload_status == 'Uploaded' ? 'color:green' : '' ?>">{{$items->upload_status}}</b></td>
                                <td>{{!empty($userData) ? $userData->name : '-'}}</td>
                                <td>{{date('d-m-Y',strtotime($items->created_on))}}</td>
                            </tr>
                            @endforeach
                        <?php } else { ?>
                            <tr>
                                <td>No Record Found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('page_level_js')
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<script type="text/javascript">
    $('#import_file').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });

    $(document).ready(function() {
        var manifestsFileDetailsId = '';
        /* var progressBarVal = 0;
        $(".progress").html('');
        var html = "<div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow=" + progressBarVal + " aria-valuemin='0' aria-valuemax='100' style='width:" + progressBarVal + "%'>" + progressBarVal + "%</div>";
        $(".progress").append(html); */
        var refreshIntervalId = '';
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.create-form').on('submit', function(event) {});
        $.validator.addMethod("checkuniquefile",
            function(value, element) {
                var result = false;
                var urlz = '<?php echo url("manifests/checkuniquefile"); ?>';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        value: value,
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This File has been already uploaded! Try another."
        );
        $('.create-form').validate({
            rules: {
                s3file: {
                    required: true,
                },
                import_file: {
                    required: true,
                    extension: "pdf",
                    checkuniquefile: true
                }
            },
            submitHandler: function(form) {
                $('.progress').show();
                var urlCheck = '<?php echo url("manifests/importdata"); ?>';
                var formData = new FormData(form);
                $.ajax({
                    url: urlCheck,
                    dataType: "json",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        manifestsFileDetailsId = data;
                        var urlCheck = '<?php echo url("manifests/reloadfilestatus"); ?>';
                        $.ajax({
                            url: urlCheck,
                            type: 'POST',
                            success: function(dataN) {
                                $('.divFileDetails').html(dataN);
                                //var refreshIntervalId = setInterval(progressStatus(data), 2000);
                                /* var refreshIntervalId = setInterval(function() {
                                    progressStatus(data);
                                }, 2000); */
                            }
                        })

                    }
                })
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


        var refreshIntervalId = setInterval(function() {
            progressStatus(manifestsFileDetailsId);
        }, 2000);

        function progressStatus(id) {
            if (id != '') {
                var urlCheck = '<?php echo url("manifests/progressstatus"); ?>';
                $.ajax({
                    url: urlCheck,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        'id': id
                    },
                    success: function(data) {
                        var progressBarVal = data.uploadPercentage;
                        $(".progress").html('');
                        var html = "<div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow=" + progressBarVal + " aria-valuemin='0' aria-valuemax='100' style='width:" + progressBarVal + "%'>" + progressBarVal + "%</div>";
                        $(".progress").append(html);
                        if (data.uploadStatus == 'Uploaded') {
                            clearInterval(refreshIntervalId);
                            var urlCheck = '<?php echo url("manifests/reloadfilestatus"); ?>';
                            $.ajax({
                                url: urlCheck,
                                type: 'POST',
                                success: function(dataH) {
                                    $('.divFileDetails').html(dataH);
                                }
                            })
                        }
                    }
                })
            }

        }

        $('#clsReload').click(function() {
            $('#loading').show();
            var urlCheck = '<?php echo url("manifests/reloadfilestatus"); ?>';
            $.ajax({
                url: urlCheck,
                type: 'POST',
                success: function(data) {
                    $('.divFileDetails').html(data);
                    /* var progressBarVal = 0;
                    $(".progress").html('');
                    var html = "<div class='progress-bar progress-bar-striped active' role='progressbar' aria-valuenow=" + progressBarVal + " aria-valuemin='0' aria-valuemax='100' style='width:" + progressBarVal + "%'>" + progressBarVal + "%</div>";
                    $(".progress").append(html);
                    */
                    $('#loading').hide();
                }
            })
        })

    });
</script>
@stop