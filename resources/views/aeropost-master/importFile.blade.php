{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createImportforms','autocomplete'=>'off','enctype'=>"multipart/form-data")) }}
{{ csrf_field() }}
<?php if ($model->id) { ?>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
            <?php echo Form::label('file_number', 'File Number :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <span class="form-control" style="border-bottom:none;padding-left: 0px;font-weight:bold"><?php echo $model->file_number; ?></span>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('tracking_number', 'AWB/BL No :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <?php echo Form::text('tracking_number', $model->tracking_number, ['class' => 'form-control', 'placeholder' => 'Enter AWB/BL No']); ?>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12 packagediv" style="margin-left: 1%;">
    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-3 form-group">
                    <?php echo Form::label('pweight', 'Weight :', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-5">
                    <?php echo Form::text('weight', $model->weight, ['class' => 'form-control weight', 'placeholder' => 'Enter Weight', 'onkeypress' => 'return isNumber(event)', 'id' => 'weight']); ?>
                </div>
                <div class="col-md-4">
                    <?php echo Form::select('measure_weight', Config::get('app.measureMass'), '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'measure_weight']); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-3 form-group">
                    <?php echo Form::label('volume', 'Volume :', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-5">
                    <?php echo Form::text('volume', $model->volume, ['class' => 'form-control volume', 'placeholder' => 'Enter Volume', 'onkeypress' => 'return isNumber(event)', 'id' => 'volume']); ?>
                </div>
                <div class="col-md-4">
                    <?php echo Form::select('measure_volume', Config::get('app.measureDimension'), '', ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'measure_volume']); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-3 form-group">
                    <?php echo Form::label('pieces', 'Pieces :', ['class' => 'control-label']); ?>
                </div>
                <div class="col-md-4">
                    <?php echo Form::text('pieces', $model->pieces, ['class' => 'form-control pieces', 'placeholder' => 'Enter Pieces', 'onkeypress' => 'return isNumber(event)']); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <div class="required">
                <?php echo Form::label('consignee_name', 'Consignee Name :', ['class' => ' control-label']); ?>
            </div>
            <div class="col-md-8">
                <?php echo Form::text('consignee_name', Config::get('app.aeropostMasterImport')['consignee'], ['class' => 'form-control fconsignee_name', 'placeholder' => 'Enter Consignee Name']); ?>
            </div>
        </div>
    </div>
</div>
<?php $aeropostMasterImportConsignee = app('App\AeropostMaster')->getConsigneeShipper(Config::get('app.aeropostMasterImport')['consignee']); ?>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('consignee_address', 'Consignee Address :', ['class' => ' control-label']); ?>
            <div class="col-md-8">
                <?php echo Form::textarea('consignee_address', $aeropostMasterImportConsignee->company_address, ['class' => 'form-control', 'placeholder' => 'Enter Consignee Address', 'rows' => 2]); ?>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('shipper_name', 'Shipper Name :', ['class' => 'control-label']); ?>
            <div class="col-md-8">
                <?php echo Form::text('shipper_name', Config::get('app.aeropostMasterImport')['shipper'], ['class' => 'form-control', 'placeholder' => 'Enter Shipper Name']); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('agent_id', 'Agent :', ['class' => 'control-label']); ?>
            <div class="col-md-6">
                <?php echo Form::select('agent_id', $agents, $model->agent_id, ['class' => 'form-control selectpicker fagent_id invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
            </div>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('arrival_date', 'Arrival Date :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <?php echo Form::text('arrival_date', !empty($model->arrival_date) ? date('d-m-Y', strtotime($model->arrival_date)) : '', ['class' => 'form-control datepicker', 'placeholder' => 'Enter Date']); ?>
            </div>
        </div>
    </div>
</div>
<?php if ($model->id) { ?>
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="form-group">
                <?php echo Form::label('billing_party', 'Billing Party :', ['class' => 'control-label']); ?>
                <div class="col-md-6">
                    <?php echo Form::select('billing_party', $billingParty, $model->billing_party, ['class' => 'form-control selectpicker invfieldtbl', 'data-live-search' => 'true', 'placeholder' => 'Select ...']); ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<div class="col-md-12">
    <div class="col-md-6">
        <div class="form-group">
            <?php echo Form::label('import_file', 'Upload  File :', ['class' => 'control-label']); ?>
            <div class="col-md-4">
                <?php echo Form::file('import_file', ['id' => 'import_file']); ?>
            </div>
        </div>
    </div>
</div>
<h4 class="formdeviderh4">EXPLICATIONS / INFORMATIONS</h4>
<div class="form-group">
    <div class="col-md-9">
        <?php echo Form::textarea('information', $model->information, ['class' => 'form-control', 'placeholder' => 'Enter Information', 'rows' => 4, 'style' => 'border: 1px solid #ccd0d2;']); ?>
    </div>

</div>
<div class="form-group col-md-12 btm-sub">
    <button type="submit" class="btn btn-success">
        <?php
        if (!$model->id)
            echo "Submit";
        else
            echo "Update";
        ?>
    </button>
    <a class="btn btn-danger" href="{{url('aeropost-master')}}" title="">Cancel</a>
</div>
{{ Form::close() }}

<?php
$datas = App\Clients::getClientsAutocomplete();
?>


<script type="text/javascript">
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $(document).ready(function() {
        $('#createImportforms').on('submit', function(event) {

            $('.fconsignee_name').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
        });
        $('#createImportforms').validate({
            rules: {
                "awb_number": {
                    required: true,
                    checkAwbNumber: true
                },
                import_file: {
                    required: <?php echo ($model->id) ? 'false' : 'true' ?>,
                },
            }
        });

        $.validator.addMethod("checkAwbNumber",
            function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("aeropost-master/checkuniqueaeropostmasterawbnumber"); ?>';
                var flag = '';
                var idz = '';
                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        number: value,
                        flag: flag,
                        idz: idz
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Awb Number is already taken! Try another."
        );
    });
</script>