@extends('layouts.custom')
@section('title')
<?php 
if($permissionFlag == 1)
    echo "User Permissions";
else
    echo "Group Permissions";
?>
@stop


@section('breadcrumbs')
    @include('menus.permission-management')
@stop

@section('content')
<section class="content-header">
    <h1>
        <?php 
        if($permissionFlag == 1)
            echo "User Permissions";
        else
            echo "Group Permissions";
        ?>
    </h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success permission-index" style="float: left;">
        <div class="box-body">
            <div class="permissions-index">
                    {{ Form::open(array('url' => "permissions/$permissionFlag",'class'=>'form-horizontal','id' => 'filteroutside','autocomplete'=>'off')) }}
                    {{ csrf_field() }}

                    <div class="form-group {{ $errors->has('department') ? 'has-error' :'' }}">
                        <?php echo Form::label('department', 'Department',['class'=>'col-md-2 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::select('department', $departments,'',['class'=>'form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select Department ...','name'=>"Permissions[user_group_id]"]); ?>
                        @if ($errors->has('department'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('department') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>

                    <div class="form-group form-group-user {{ $errors->has('user') ? 'has-error' :'' }}" style="display: none;">
                        <?php echo Form::label('user', 'User',['class'=>'col-md-2 control-label']); ?>
                        <div class="col-md-6">
                        <?php echo Form::select('user', array(),'',['class'=>'form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select User ...','name'=>"Permissions[user_id]"]); ?>
                        @if ($errors->has('user'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('user') }}</strong>
                                    </span>
                        @endif
                        </div>
                    </div>

                    <input type="hidden" name="Permissions[type_flag]" value="<?php echo $permissionFlag; ?>">
                    

                     <div class="form-group col-md-12 btm-sub">
                            
                                <button type="submit" class="btn btn-success btn-filter-outside">
                                        <?php
                                            echo "Submit";
                                        ?>
                                </button>
                            <a class="btn btn-danger" href="{{url('permissions',1)}}" title="">Cancel</a>
                        </div>
                    
                    <div id="partial-container">
                    </div>
                    
                    
               {{ Form::close() }}  
                </div>
            
        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    <?php if($permissionFlag == 1) { ?>
    $('.form-group-user').show();
    <?php } ?>

    // Adnvace Option show/hide
      $(document).on('click','.advance', function(e){
            $('#loading').show();
            e.preventDefault();
            console.log('here1');
            $(this).next('.childList').removeClass('hide');
            $(this).addClass('hide');
            $('#loading').hide();
        })

    // Hide Permission option on change of user id
        $(document).on("change","#user",function(){
            $('#loading').show();
            $("#partial-container").html('');
            $('#loading').hide();
        });  

    <?php if($permissionFlag == 1) { ?>    
    $('#department').change(function(){
        $('#loading').show();
        var deptId = $(this).val();
        $("#partial-container").html('');
        $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
        $.ajax({
            type    : 'post',
            url     : '<?php echo url('permissions/getuser'); ?>',
            data    : {'deptId':deptId},
            success : function (response) {
                var userList = JSON.parse(response);
                var html = '<option value="">-- Select --</option>';
                 $(userList).each(function(k,v){
                    html += '<option value="'+v.id+'">'+ v.name+'</option>';
                });
                $('#user').html(html);
                $('#user').selectpicker('refresh');
                $('#loading').hide();
                },
                error: function () {
                     $('#loading').hide();
                }
            });
    })
    <?php } ?>

    // On button submit
        $('body').on('click', '.btn-filter-outside', function () {

            <?php if($permissionFlag == 1) { ?>
                if($('#department').val() == '')
                {
                    /*var lobibox = Lobibox.notify('error', {
                        size: 'mini',
                        rounded: true,
                        sound: false,
                        icon: false,
                        delayIndicator: false,
                        delay: 1500,
                        //delay: false,
                        position: {
                            left: 600, top: 100
                        },
                        msg: 'Please select department.'
                        });*/
                    alert("Please select department.");
                    return false;
                }
                if($('#user').val() == '')
                {
                    /*Lobibox.notify('error', {
                        size: 'mini',
                        rounded: true,
                        sound: false,
                        icon: false,
                        delayIndicator: false,
                        delay: 1500,
                        //delay: false,
                        position: {
                            left: 600, top: 150
                        },
                        msg: 'Please select user.'
                        });*/
                    alert("Please select user.");
                    return false;
                }
            <?php }else { ?>
                if($('#department').val() == '')
                {
                    /*Lobibox.notify('error', {
                        size: 'mini',
                        rounded: true,
                        sound: false,
                        icon: false,
                        delayIndicator: false,
                        delay: 1500,
                        //delay: false,
                        position: {
                            left: 600, top: 100
                        },
                        msg: 'Please select department.'
                        });*/
                    alert("Please select department.");
                    return false;
                }
            <?php } ?>

            $('#loading').show();
            var form = $('#filteroutside');
            $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
            $.ajax({
                type: 'post',
                url: '<?php echo url('permissions/filteroutside'); ?>',
                data: form.serialize(),
                success: function (response) {
                    if(response == '1')
                    {
                        var url = '<?php echo url("permissions/$permissionFlag"); ?>';
                        window.location = url;

                    }else{
                    $("#partial-container").html(response);
                    $('input:checkbox').bootstrapToggle();
                    $('#loading').hide();
                    }
                },
                error: function () {
                    return false;
                }
            });
            
            $(document).on("change", ".childSwitch", function () {
                var toggle = $(this).prop('checked');
                if (toggle == true) {
                    var parentVal = $(this).parents('.childList').parent('.mainContainer').find('.parentSwitch').prop('checked');
                    if (parentVal == false) {
                        $(this).parents('.childList').parent('.mainContainer').find('.parentSwitch').prop('checked', true).change();
                    }
                }
            });
            
            $(document).on("change", ".parentSwitch", function () {
                var toggle = $(this).prop('checked');
                if (toggle == false) {
                    $(this).parent('.toggle').parent('.mainContainer').find('.childSwitch').prop('checked', false).change();
                } else if (toggle == true) {
                    console.log('here');
                    $(this).parent('.toggle').next('.advance').next('.childList').removeClass('hide');
                    $(this).parent('.toggle').next('.advance').addClass('hide');
                    //$(this).addClass('hide');
                    //$(this).parents('.mainContainer').find('.advance').trigger('click');
                    //$(this).parents('.mainContainer').find('.childSwitch').first().prop('checked', true).change();
                    $(this).parents('.mainContainer').find('.childSwitch').prop('checked', true).change();

                }
            });
            $(document).on("change", ".btnEdit", function () {
                var toggle = $(this).prop('checked');
                if (toggle == true) {
                    $(this).parent('.toggle').parent('td').prev('td').find('.btnView').prop('checked', true).change();
                }
            });
            $(document).on("change", ".btnView", function () {
                var toggle = $(this).prop('checked');
                if (toggle == false) {
//                  $(this).bootstrapToggle('off');
                    $(this).parent('.toggle').parent('td').next('td').find('.btnEdit').prop('checked', false).change();
                }
            });
            return false;
        });

} )
</script>
@stop

