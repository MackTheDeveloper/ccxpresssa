<?php $actionUrl = url('reports/apdisbursement-submit'); ?>
{{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
{{ csrf_field() }}
<input type="hidden" name="selectedIds" id="selectedIds" value="">
<div class="col-md-12">
    <div class="form-group">
        <div class="col-md-3">
            <?php echo Form::label('expense_request_status_note', 'Notes', ['class' => 'control-label']); ?>
        </div>
        <div class="col-md-6 consolidate_flag-md-6">
            <?php echo Form::textarea('expense_request_status_note', '', ['class' => 'form-control', 'rows' => 1]); ?>
        </div>
    </div>
</div>
<div class="col-md-12">
    <div class="form-group" style="color: green;margin-top: 10px;">
        <div class="col-md-3">
            <?php echo Form::label('cash_credit_account', 'Cash/Bank', ['class' => 'control-label']); ?>
        </div>
        <div class="col-md-6 consolidate_flag-md-6">
            <?php echo Form::select('cash_credit_account', $cashCredit, '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'data-container' => 'body']); ?>
        </div>
    </div>
</div>
<div class="form-group col-md-12 btm-sub">
    <button type="submit" class="btn btn-success">Submit</button>
</div>
{{ Form::close() }}

<script type="text/javascript">
    $(document).ready(function() {
        $('#selectedIds').val($('.idsforapdisbursement').val());
        $('.selectpicker').selectpicker();

        $('#createforms').on('submit', function(event) {
            $('#loading').show();
            var form = $("#createforms");
            var formData = form.serialize();
            var urlz = '<?php echo url("reports/apdisbursement-submit"); ?>';
            $.ajax({
                url: urlz,
                //async: false,
                type: 'POST',
                data: formData,
                success: function(data) {
                    $('#loading').hide();
                    $('.selectpicker').selectpicker('refresh');
                    $('#modalApDisbursement').modal('toggle');
                },
            });
        });
    });
</script>