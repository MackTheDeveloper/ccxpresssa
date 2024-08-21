
                   
                    {{ Form::open(array('url' => '#','class'=>'form-horizontal create-form','id'=>'createforms_notes','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
                    <input type="hidden" name="hawb_id" value="<?php echo $houseAWBData->id; ?>">
                    <input type="hidden" name="flag_note" value="<?php echo $flag; ?>">
                    <div class="col-md-12">
                        <div class="col-md-12">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <div class="col-md-12">
                                    <?php echo Form::label('notes', 'Note',['class'=>'control-label']); ?>
                                    </div>
                                    <div class="col-md-12">
                                    <?php echo Form::textarea('notes','',['class'=>'form-control fflag_note','placeholder' => 'Enter Note','rows'=>4]); ?>
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

            $('#createforms_notes').on('submit', function (event) {
                    $('.fflag_note').each(function () {
                        $(this).rules("add",
                                {
                                    required: true,
                                })
                         });
            });

             $('#createforms_notes').validate({
                 submitHandler: function (form) {
                      $.ajaxSetup({
                            headers:{
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                     var createExpenseForm = $("#createforms_notes");
                        var formData = createExpenseForm.serialize();
                        var urlz = '<?php echo url("cargo/saveverificationnote"); ?>';
                        $.ajax({
                        url:urlz,
                        type:'POST',
                        data:formData,
                        success:function(data) {
                                Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'Notes has been added successfully.'
                                });
                                $('#modalAddVerificationNote').modal('toggle');
                            },
                 })
             }
        });
});             
        
</script>

