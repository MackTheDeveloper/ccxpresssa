
                    <?php
                    if($model->id)
                        $actionUrl = url('warehouse/update',$model->id);
                    else
                        $actionUrl = url('warehouse/store');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
                    <input type="hidden" name="flagModule" value="<?php echo $flagModule; ?>">
                    <input type="hidden" name="id" value="<?php echo $moduleId; ?>">
                    <div class="col-md-12">
                        <div class="col-md-6">
                                <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
                                    <div class="col-md-12">
                                    <?php echo Form::label('warehouse', 'Warehouse',['class'=>'control-label']); ?>
                                    </div>
                                    <div class="col-md-12">
                                    <?php echo Form::select('warehouse', $warehouses,$model->warehouse,['class'=>'form-control selectpicker', 'data-live-search' => 'true','placeholder' => 'Select ...','data-container'=>'body']); ?>
                                    </div>
                                </div>
                            </div>
                    </div>

            

                    <div class="form-group col-md-12 btm-sub">
                                    
                                        <button type="submit" class="btn btn-success">
                                            Save
                                        </button>
                                    
                    </div>

                    {{ Form::close() }}



<script type="text/javascript">
    
        $(document).ready(function() {
            $('.selectpicker').selectpicker();

            $('#createforms').on('submit', function (event) {
                    event.preventDefault();

                    $('#loading').show();
                    var form = $("#createforms");
                    var formData = form.serialize();
                    var urlz = '<?php echo url("warehouse/storewarehouseinfile"); ?>';
                    $.ajax({
                    url:urlz,
                    async:false,
                    type:'POST',
                    data:formData,
                    success:function(data) {
                            $('#loading').hide();
                            $('.selectpicker').selectpicker('refresh');
                            Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Warehouse has been assigned successfully.'
                            });
                            $('#modalAddCashCreditWarehouseInFile').modal('toggle');
                        },
                    });
                });
        });
        
</script>

