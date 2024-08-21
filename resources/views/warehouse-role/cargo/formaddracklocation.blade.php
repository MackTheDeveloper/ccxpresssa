
                    <?php
                        $actionUrl = url('storeracklocationinwarehousefile');    
                    ?>
                    {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms_1','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
                    <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
                    <div class="col-md-12">
                        <div class="col-md-6">
                                <div class="form-group {{ $errors->has('rack_location') ? 'has-error' :'' }}">
                                    <div class="col-md-12">
                                    <?php echo Form::label('rack_location', 'Rack Location',['class'=>'control-label']); ?>
                                    </div>
                                    <div class="col-md-12 rack_location-field">
                                    <?php echo Form::select('rack_location', $dataAvailableLocations,$model->rack_location,['class'=>'form-control selectpicker', 'data-live-search' => 'true','multiple'=>true,'data-container'=>'body']); ?>
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
            var id = '<?php echo $id; ?>';
            $('.selectpicker').selectpicker();

            $('#createforms_1').on('submit', function (event) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    event.preventDefault();
  
                    $('#loading').show();
                    var form = $("#createforms_1");
                    var formData = form.serialize();
                    var id = $('#id').val();
                    var racks = $('#rack_location').val();
                    var urlz = '<?php echo url("storeracklocationinwarehousefile"); ?>';
                    $.ajax({
                    url:urlz,
                    async:false,
                    type:'POST',
                    data:{'id':id,'racks':racks},
                    success:function(data) {
                            $('#loading').hide();
                            Lobibox.notify('info', {
                            size: 'mini',
                            delay: 2000,
                            rounded: true,
                            delayIndicator: false,
                            msg: 'Rack Location has been assigned successfully.'
                            });
                            $('.rack-'+id).text(data);
                            $('#modalAddRackLocation').modal('toggle');
                        },
                    });
                });
        });
        
</script>

