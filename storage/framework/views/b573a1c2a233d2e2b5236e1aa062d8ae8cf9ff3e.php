<?php $__env->startSection('title'); ?>
<?php echo 'Upload UPS Files'; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumbs'); ?>
<?php echo $__env->make('menus.ups-import', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<section class="content-header">
    <h1><?php echo 'Upload UPS Files'; ?></h1>
</section>

<section class="content">
    <?php if(Session::has('flash_message_error')): ?>
    <div class="alert alert-danger flash-danger">
        <?php echo e(Session::get('flash_message_error')); ?>

    </div>
    <?php endif; ?>
    <div class="box box-success">
        <div class="box-body">
            <?php
            $actionUrl = url('ups/importdata');
            ?>
            <?php echo e(Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','enctype'=>"multipart/form-data",'autocomplete'=>'off','id'=>'uploadFileForm'))); ?>

            <?php echo e(csrf_field()); ?>


            <div class="col-md-12">

                <div class="form-group">
                    <?php echo Form::label('action', 'Select Action', ['class' => 'col-md-2 control-label']); ?>
                    <div class="col-md-3">
                        <select class="selectpicker form-control" id="actionsdp" name="actions">
                            <option value="upload">Import Main Files</option>
                            <option value="upload_export_file">Export Main Files</option>
                            <option value="import_scan">Import Scan</option>
                            <option value="warehouse_scan">Warehouse Scan</option>
                            <option value="physical_scan">Physical Scan</option>
                            <option value="delivery_scan">Delivery Scan</option>
                            <option value="ups_commission_file">Commission File From Ups</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-2">Storage</label>

                    <div class="col-md-6">
                        <?php
                        echo Form::radio('storage', '1', true);
                        echo Form::label('Local', '', ['style' => 'margin-left:0.5%;margin-right:2%']);
                        echo Form::radio('storage', '2');
                        echo Form::label('Cloud', '', ['style' => 'margin-left:0.5%']);


                        ?>
                    </div>
                </div>
                <div class="form-group" style="display: none;" id="cloud_file">
                    <?php echo Form::label('s3file', 'Upload File', ['class' => 'col-md-2 control-label']); ?>
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
                <div class="upload_div">

                    <div class="form-group <?php echo e($errors->has('import_file') ? 'has-error' :''); ?>" id="local_file">
                        <?php echo Form::label('import_file', 'Upload File', ['class' => 'col-md-2 control-label import_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('import_file', ['id' => 'import_file']); ?>
                            <?php if($errors->has('import_file')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('import_file')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>


                    <div class="form-group <?php echo e($errors->has('agent_id') ? 'has-error' :''); ?>" style="display: none">
                        <?php echo Form::label('agent_id', 'Agent', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-3">
                            <?php echo Form::select('agent_id', $agents, $model->agent_id, ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                            <?php if($errors->has('agent_id')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('agent_id')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group <?php echo e($errors->has('file_name') ? 'has-error' :''); ?>" style="display: none">
                        <?php echo Form::label('file_name', 'File Name', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-3">
                            <?php echo Form::text('file_name', $model->file_name, ['class' => 'form-control local-file', 'placeholder' => 'Enter File Name']); ?>
                            <?php if($errors->has('file_name')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('file_name')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="export_div" style="display:none">

                    <div class="form-group <?php echo e($errors->has('export_file') ? 'has-error' :''); ?>">
                        <?php echo Form::label('export_file', 'Upload File', ['class' => 'col-md-2 control-label export_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('export_file', ['class' => 'local_file']); ?>
                            <?php if($errors->has('export_file')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('export_file')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo Form::label('arrival_date', 'Transport Date', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-2">
                            <?php echo Form::text('arrival_date', date('d-m-Y'), ['class' => 'form-control datepicker', 'placeholder' => 'Enter Transport Date']); ?>
                        </div>
                    </div>


                </div>
                <div class="ups_commission_div" style="display:none">

                    <div class="form-group <?php echo e($errors->has('ups_commission_file') ? 'has-error' :''); ?>">
                        <?php echo Form::label('ups_commission_file', 'Upload File', ['class' => 'col-md-2 control-label ups_commission_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('ups_commission_file', ['id' => 'ups_commission_file']); ?>
                            <?php if($errors->has('ups_commission_file')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('ups_commission_file')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>


                </div>
                <div class="import_scan_div" style="display: none">
                    <div class="form-group <?php echo e($errors->has('import_file_import_scan') ? 'has-error' :''); ?>">
                        <?php echo Form::label('import_file_import_scan', 'Upload File', ['class' => 'col-md-2 control-label import_file_import_scan']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('import_file_import_scan', ['id' => 'import_file_import_scan']); ?>
                        </div>
                    </div>
                </div>

                <div class="warehouse_scan_div" style="display: none;">
                    <div class="form-group <?php echo e($errors->has('warehouse') ? 'has-error' :''); ?>">
                        <?php echo Form::label('warehouse', 'Warehouse', ['class' => 'col-md-2 control-label']); ?>
                        <div class="col-md-3">
                            <?php echo Form::select('warehouse', $warehouses, '', ['class' => 'form-control selectpicker fagent_id', 'data-live-search' => 'true']); ?>
                        </div>
                    </div>

                    <div class="form-group <?php echo e($errors->has('import_file_warehouse_scan') ? 'has-error' :''); ?>">
                        <?php echo Form::label('import_file_warehouse_scan', 'Upload File', ['class' => 'col-md-2 control-label import_file_warehouse_scan']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('import_file_warehouse_scan', ['id' => 'import_file_warehouse_scan']); ?>
                        </div>
                    </div>
                </div>

                <div class="physical_scan_div" style="display:none">

                    <div class="form-group <?php echo e($errors->has('physical_scan_file') ? 'has-error' :''); ?>">
                        <?php echo Form::label('physical_scan_file', 'Upload File', ['class' => 'col-md-2 control-label physical_scan_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('physical_scan_file', ['id' => 'physical_scan_file']); ?>
                            <?php if($errors->has('export_file')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('physical_scan_file')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>


                </div>

                <div class="delivery_scan_div" style="display:none">

                    <div class="form-group <?php echo e($errors->has('delivery_scan_file') ? 'has-error' :''); ?>">
                        <?php echo Form::label('delivery_scan_file', 'Upload File', ['class' => 'col-md-2 control-label delivery_scan_file']); ?>
                        <div class="col-md-6">
                            <?php echo Form::file('delivery_scan_file', ['id' => 'delivery_scan_file']); ?>
                            <?php if($errors->has('export_file')): ?>
                            <span class="help-block">
                                <strong><?php echo e($errors->first('delivery_scan_file')); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-12 btm-sub">


                    <button type="submit" class="btn btn-success">
                        <?php
                        echo "Import";

                        ?>
                    </button>
                    <a class="btn btn-danger" href="<?php echo e(url('ups')); ?>" title="">Cancel</a>
                </div>

            </div>




            <?php echo e(Form::close()); ?>



        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_level_js'); ?>


<script type="text/javascript">
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $('input[type="file"]').each(function() {
            $(this).change(function() {
                if ($(this).val() != "") {
                    $(this).valid();
                }
            });

        });

        $('input[type="radio"]').each(function() {
            $(this).change(function() {
                if ($(this).val() == '1') {
                    $('#cloud_file').hide();
                    $('#cloud_file input').val(null);
                    $('input[type="file"]').each(function() {
                        $(this).show();
                        $('.' + $(this).attr('id')).show();
                    });
                } else {
                    $('input[type="file"]').each(function() {
                        $(this).hide();
                        $(this).val(null);
                        $('p').hide();
                        $('.' + $(this).attr('id')).hide();
                        $('#' + $(this).attr('id') + '-error').hide();
                    });
                    $('#cloud_file').show();
                }
            });

        });

        $('#actionsdp').change(function() {
            if ($(this).val() == 'upload') {
                $('.upload_div').show();
                $('.import_scan_div').hide();
                $('.warehouse_scan_div').hide();
                $('.export_div').hide();
                $('.ups_commission_div').hide();
                $('.physical_scan_div').hide();
                $('.delivery_scan_div').hide();
            } else if ($(this).val() == 'import_scan') {
                $('.import_scan_div').show();
                $('.upload_div').hide();
                $('.warehouse_scan_div').hide();
                $('.export_div').hide();
                $('.ups_commission_div').hide();
                $('.physical_scan_div').hide();
                $('.delivery_scan_div').hide();
            } else if ($(this).val() == 'upload_export_file') {
                $('.export_div').show();
                $('.import_scan_div').hide();
                $('.upload_div').hide();
                $('.warehouse_scan_div').hide();
                $('.ups_commission_div').hide();
                $('.physical_scan_div').hide();
                $('.delivery_scan_div').hide();
            } else if ($(this).val() == 'ups_commission_file') {
                $('.ups_commission_div').show();
                $('.export_div').hide();
                $('.import_scan_div').hide();
                $('.upload_div').hide();
                $('.warehouse_scan_div').hide();
                $('.physical_scan_div').hide();
                $('.delivery_scan_div').hide();
            } else if ($(this).val() == 'physical_scan') {
                $('.physical_scan_div').show();
                $('.ups_commission_div').hide();
                $('.export_div').hide();
                $('.import_scan_div').hide();
                $('.upload_div').hide();
                $('.warehouse_scan_div').hide();
                $('.delivery_scan_div').hide();
            } else if ($(this).val() == 'delivery_scan') {
                $('.delivery_scan_div').show();
                $('.physical_scan_div').hide();
                $('.ups_commission_div').hide();
                $('.export_div').hide();
                $('.import_scan_div').hide();
                $('.upload_div').hide();
                $('.warehouse_scan_div').hide();
            } else {
                $('.warehouse_scan_div').show();
                $('.import_scan_div').hide();
                $('.upload_div').hide();
                $('.export_div').hide();
                $('.ups_commission_div').hide();
                $('.physical_scan_div').hide();
                $('.delivery_scan_div').hide();
            }
        })

        $('#uploadFileForm').on('submit', function(event) {



        });

        $('#uploadFileForm').validate({
            rules: {
                s3file: {
                    required: true,
                },
                import_file: {
                    required: true,
                },
                export_file: {
                    required: true,
                    extension: "xls|xlsx"
                },
                ups_commission_file: {
                    required: true,
                    extension: "xls|xlsx"
                },
                import_file_import_scan: {
                    required: true,
                    extension: "txt|xls|xlsx"
                },
                import_file_warehouse_scan: {
                    required: true,
                    extension: "txt|xls|xlsx"
                },
                physical_scan_file: {
                    required: true,
                    extension: "xls|xlsx"
                },
                delivery_scan_file: {
                    required: true,
                    extension: "xls|xlsx"
                },
            },

            messages: {
                export_file: {
                    extension: "select valid input file format with extension 'xls' or 'xlsx'."
                },
                ups_commission_file: {
                    extension: "select valid input file format with extension 'xls' or 'xlsx'."
                },
                import_file_import_scan: {
                    extension: "select valid input file format with extension 'txt', 'xls' or 'xlsx'."
                },
                import_file_warehouse_scan: {
                    extension: "select valid input file format with extension 'txt', 'xls' or 'xlsx'."
                },
                physical_scan_file: {
                    extension: "select valid input file format with extension 'xls' or 'xlsx'.",
                },
                delivery_scan_file: {
                    extension: "select valid input file format with extension 'xls' or 'xlsx'.",
                },

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
                var actionArr = ['upload_export_file', 'import_scan', 'warehouse_scan', 'physical_scan', 'delivery_scan', 'ups_commission_file']
                var action = $('#actionsdp').val();
                var rules;
                if (actionArr.includes(action)) {
                    rules = ['xls', 'xlsx'];
                    if (!(rules.includes(ext))) {
                        $('#s3fileError').show();
                        $('#s3fileError').html("select valid input file format with extension 'xls' or 'xlsx'.");
                    } else {
                        $('#s3fileError').hide();
                    }
                } else {
                    rules = ['txt', 'xls', 'xlsx'];
                    if (!(rules.includes(ext))) {
                        $('#s3fileError').show();
                        $('#s3fileError').html("select valid input file format with extension 'txt','xls' or 'xlsx'.");
                    } else {
                        $('#s3fileError').hide();
                    }
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>