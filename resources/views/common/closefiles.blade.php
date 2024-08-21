@extends('layouts.custom')
@section('title')
Close File
@stop

@section('breadcrumbs')
@include('menus.closed-file')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Close File'; ?></h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
    <div class="alert alert-success flash-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    <div class="box box-success">
        <div class="box-body">
            <?php
            $actionUrl = url('closefilessubmit');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4">
                            <?php echo Form::label('module', 'Module', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('module', ['Cargo' => 'Cargo', 'houseFile' => 'House File', 'UPSMaster' => 'UPS Master','UPS' => 'UPS', 'AeropostMaster' => 'Aeropost Master', 'Aeropost' => 'Aeropost', 'CcpackMaster' => 'CCPack Master', 'CCPack' => 'CCPack'], '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'module']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4">
                            <?php echo Form::label('files', 'Files', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('files[]', $cargoFiles, '', ['class' => 'form-control selectpicker hfiles', 'data-live-search' => 'true', 'id' => 'files', 'data-container' => 'body', 'multiple' => true]); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-12 btm-sub">
                <button type="submit" class="btn btn-success">Close</button>

            </div>

            {{ Form::close() }}


        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        $('select').change(function() {
            if ($(this).val() != "") {
                $(this).valid();
            }
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#createforms').on('submit', function(event) {

            $('.hfiles').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });

        });
        $('#createforms').validate({
            errorPlacement: function(error, element) {
                if (element.attr("name") == "files[]") {
                    var pos = $('.hfiles button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });
        $('#module').change(function() {
            $('#loading').show();
            var module = $(this).val();
            var urlz = '<?php echo url("getfilesforclose"); ?>';
            $.ajax({
                type: "POST",
                url: urlz,
                data: {
                    module: module,
                },
                success: function(response) {
                    var List = JSON.parse(response);
                    var html = '';
                    $(List).each(function(k, v) {
                        html += '<option value="' + v.id + '">' + v.file_number + '</option>';
                    });
                    $('#files').html(html);
                    $('#files').selectpicker('refresh');
                    $('#loading').hide();
                }
            });
        })
    });
</script>
@stop