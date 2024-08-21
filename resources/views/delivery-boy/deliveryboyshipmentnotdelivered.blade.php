<?php $actionUrl = url('delivery-boy-shipment-delivered-or-not'); ?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="ids" id="selectedIds" value="">
<input type="hidden" name="module" value="<?php echo $module; ?>">
<input type="hidden" name="flagButton" value="returned">

<div class="col-md-12">
    <div class="form-group">
        <div class="col-md-3">
            <?php echo Form::label('reason_for_return', 'Reason', ['class' => 'control-label']); ?>
        </div>
        <div class="col-md-6 consolidate_flag-md-6">
            <?php echo Form::select('reason_for_return', Config::get('app.reasonOfReturn'), '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'data-container' => 'body']); ?>
        </div>
    </div>
</div>
<div class="col-md-12 div_shipment_notes_for_return">
    <div class="form-group">
        <div class="col-md-3">
            <?php echo Form::label('shipment_notes_for_return', 'Comment', ['class' => 'control-label']); ?>
        </div>
        <div class="col-md-9 consolidate_flag-md-6">
            <?php echo Form::text('shipment_notes_for_return', '', ['class' => 'form-control', 'placeholder' => 'Enter Comment Here']); ?>
        </div>
    </div>
</div>

<div class="form-group col-md-12 btm-sub">
    <button type="submit" class="btn btn-success">Save</button>
</div>
{{ Form::close() }}

<script type="text/javascript">
    $(document).ready(function() {
        $('#selectedIds').val($('.ids').val());
        $('.selectpicker').selectpicker();
        $('#shipment_notes_for_return').val($('#reason_for_return option:selected').html());
        $('#createforms').on('submit', function(event) {
            if ($('#shipment_notes_for_return').val() == '') {
                Lobibox.notify('error', {
                    size: 'mini',
                    delay: 2000,
                    rounded: true,
                    delayIndicator: false,
                    msg: 'Please enter the any comment'
                });
                return false;
            }
            event.preventDefault();
            $('#loading').show();
            var form = $("#createforms");
            var formData = form.serialize();
            var urlz = '<?php echo url("delivery-boy-shipment-delivered-or-not"); ?>';
            $.ajax({
                url: urlz,
                //async: false,
                type: 'POST',
                data: formData,
                success: function(data) {
                    $('#loading').hide();
                    $('.selectpicker').selectpicker('refresh');
                    $('#modalShipmentNotDelivered').modal('toggle');
                    window.location.href = '<?php echo route("manifestdetailsdeliveryboy", $deliveryBoyId) ?>';
                },
            });
        });

        $('#reason_for_return').change(function() {
            $('#shipment_notes_for_return').val($('#reason_for_return option:selected').html());
        })
    });
</script>