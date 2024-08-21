@extends('layouts.custom')
@section('title')
<?php echo 'Add Guarantee Check'; ?>
@stop


@section('breadcrumbs')
@include('menus.check-guarantee')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo 'Add Guarantee Check'; ?></h1>
</section>


<section class="content">
    <div class="box box-success">
        <div class="box-body">
            <?php
            $actionUrl = url('check-guarantee/storecheck');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createformscheck','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <input type="hidden" name="file_number" class="file_number" value="" />
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4 required">
                            <?php echo Form::label('master_cargo_id', 'File Number', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::select('master_cargo_id', $fileNumber, '', ['class' => 'form-control selectpicker fmaster_cargo_id', 'data-live-search' => 'true', 'id' => 'master_cargo_id', 'placeholder' => 'Select file number']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4 required">
                            <?php echo Form::label('date', 'Date', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('date', null, ['class' => 'form-control fdate datepicker', 'placeholder' => 'Enter Date']); ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="col-md-4 required">
                            <?php echo Form::label('amount', 'Amount', ['class' => 'control-label']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo Form::text('amount', '0.00', ['class' => 'form-control famount', 'placeholder' => 'Enter Amount']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <?php echo Form::label('check_type', 'Type', ['class' => 'col-md-4']); ?>
                        <div class="consolidate_flag-md-6 col-md-6">
                            <?php
                            echo Form::radio('check_type', '1', 'checked', ['class' => 'flagconsol', 'id' => 'check_type_decsa']);
                            echo Form::label('check_type_decsa', 'DECSA');
                            echo Form::radio('check_type', '2', '', ['class' => 'flagconsol', 'id' => 'check_type_veconinter']);
                            echo Form::label('check_type_veconinter', 'Veconinter');
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group col-md-12 btm-sub">
                <button type="submit" class="btn btn-success">
                    <?php
                    echo "Submit";
                    ?>
                </button>
                <a class="btn btn-danger" href="{{url('check-guarantee')}}" title="">Cancel</a>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $('#master_cargo_id').change(function() {
            $('.file_number').val($("#master_cargo_id option:selected").text());
        })

        //$('.selectpicker').selectpicker();
        $('#createformscheck').on('submit', function(event) {

            $('.fmaster_cargo_id').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.fdate').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.famount').each(function() {
                $(this).rules("add", {
                    required: true,
                    number: true
                })
            });
        });

        $('#createformscheck').validate({
            errorPlacement: function(error, element) {
                if (element.attr("name") == "file_number") {
                    var pos = $('.fmaster_cargo_id button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
</script>
@stop