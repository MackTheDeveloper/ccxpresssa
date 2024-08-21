<?php $actionUrl = url('assign-delivery-boy-submit'); ?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="module" value="<?php echo $module; ?>">
<input type="hidden" name="selectedIds" id="selectedIds" value="">
<div class="col-md-12">
    <div class="form-group">
        <div class="col-md-3">
            <?php echo Form::label('delivery_boy', 'Delivery Boy', ['class' => 'control-label']); ?>
        </div>
        <div class="col-md-6 consolidate_flag-md-6">
            <?php echo Form::select('delivery_boy', $deliveryBoys, $model->delivery_boy, ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'data-container' => 'body']); ?>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="form-group" style="color: green;margin-top: 10px;">
        <b>Note :</b> Courier Files Invoices has been generated already. Please Click on <b>Save & Print Invoices</b> Button to Assign Delivery Boy and Print Invoice of Selected Files
    </div>
</div>
<div class="form-group col-md-12 btm-sub">
    <button type="submit" class="btn btn-success">Save & Print Invoices</button>
</div>
{{ Form::close() }}

<script type="text/javascript">
    $(document).ready(function() {
        $('#selectedIds').val($('.ids').val());
        $('.selectpicker').selectpicker();

        $('#createforms').on('submit', function(event) {
            event.preventDefault();
            $('#loading').show();
            var form = $("#createforms");
            var formData = form.serialize();
            var urlz = '<?php echo url("assign-delivery-boy-submit"); ?>';
            $.ajax({
                url: urlz,
                //async: false,
                type: 'POST',
                data: formData,
                success: function(data) {
                    $('#loading').hide();
                    $('.selectpicker').selectpicker('refresh');
                    $('#modalAssignDeliveryBoy').modal('toggle');
                    window.open(data, '_blank');
                    <?php if ($module == 'ups') { ?>
                        window.location.href = '<?php echo route("warehouseups") ?>';
                    <?php } ?>
                    <?php if ($module == 'aeropost') { ?>
                        window.location.href = '<?php echo route("warehouseaeroposts") ?>';
                    <?php } ?>
                    <?php if ($module == 'ccpack') { ?>
                        window.location.href = '<?php echo route("warehouseccpack") ?>';
                    <?php } ?>
                },
            });
        });
    });
</script>