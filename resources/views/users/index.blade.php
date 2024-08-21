@extends('layouts.custom')

@section('title')
Users
@stop

<?php 
    $permissionUsersEdit = App\User::checkPermission(['update_users'],'',auth()->user()->id); 
    $permissionUsersDelete = App\User::checkPermission(['delete_users'],'',auth()->user()->id); 
    $permissionUsersResetPassword = App\User::checkPermission(['reset_password_users'],'',auth()->user()->id); 
?>

@section('breadcrumbs')
    @include('menus.user-management')
@stop

@section('content')
<section class="content-header">
    <h1>Users</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">
            <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr data-editlink="{{ route('edituser',[$user->id]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                    <td style="display: none;">{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td><?php $detailData = App\CashCreditDetailType::getData($user->department); echo $detailData->name; ?></td>
                    <td><?php echo ($user->status == 1) ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteuser',$user->id);
                        $edit =  route('edituser',$user->id);
                        ?>
                        <?php if($permissionUsersEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionUsersDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($permissionUsersResetPassword) { ?>
                                    <li><a class="clsResetPsw" href="#" data-toggle="modal" data-userid={{$user->id}} data-target="#resetPassword">Reset Password</a></li>
                                    <?php } ?>
                                </ul>
                        </div>
                        
                    </td>
                </tr>
            @endforeach
            
        </tbody>
        
    </table>
        </div>
    </div>


<div id="modalViewDetail" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">User Detail</h3>
            </div>
            <div class="modal-body" id="modalContentViewDetail" style="overflow: hidden;">
            </div>
        </div>

    </div>
</div>

<div id="modalViewUserActivities" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">User Activities</h3>
            </div>
            <div class="modal-body" id="modalContentViewUserActivities" style="overflow: hidden;">
            </div>
        </div>

    </div>
</div>


<div id="resetPassword" class="modal fade" role="dialog">
    <div class="modal-dialog">

    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">Reset Password</h3>
            </div>
            <div class="modal-body" style="overflow: hidden;">
                <div class="col-md-offset-1 col-md-10">
                    <form method="POST" id="resetPasswordForm">
                        {{ csrf_field() }}
                        <input type="hidden" value="" name="userId" id="userId">
                        <div class="form-group has-feedback">
                            <input type="password" name="password" class="form-control" placeholder="Password">
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                            <span class="text-danger">
                                <strong id="password-error"></strong>
                            </span>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 text-center">
                              <button type="button" id="resetPasswordFormButton" class="btn btn-primary btn-prime white btn-flat">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>


</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable(
    {
        'stateSave': true,
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        },
        
    });

    $('.customButtonInGrid').click(function(){
        var status = $(this).val();
        var userId = $(this).data('userid');
        var thiz = $(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        Lobibox.confirm({
            msg: "Are you sure to change status?",
            callback: function (lobibox, type) {

                         if(type == 'yes')
                           {
                                 $.ajax({
                                    type    : 'post',
                                    url     : '<?php echo url('user/changeuserstatus'); ?>',
                                    data    : {'status':status,'userId':userId},
                                    success : function (response) {
                                            thiz.val(status);
                                        }
                                    });
                                    if(status == 1)
                                    {
                                    thiz.val('0');    
                                    thiz.text('Inactive');
                                    thiz.removeClass('customButtonSuccess');
                                    thiz.addClass('customButtonAlert');
                                    }
                                    else
                                    {
                                    thiz.val('1');    
                                    thiz.text('Active');    
                                    thiz.removeClass('customButtonAlert');
                                    thiz.addClass('customButtonSuccess');
                                    }
                                    Lobibox.notify('info', {
                                        size: 'mini',
                                        delay: 2000,
                                        rounded: true,
                                        delayIndicator: false,
                                        msg: 'Status has been updated successfully.'
                                    });
                           }
                          else
                            {}    
                }
        })
     })

    // Set data before modal popup open
    //$('.clsResetPsw').click(function(){
    $(document).delegate(".clsResetPsw", "click", function(){
        $('#userId').val($(this).data('userid'));
        return true;
    })

    $('#resetPasswordForm').keydown(function(event){
    if(event.keyCode == 13) {
          $('#resetPasswordFormButton').trigger('click');
          event.preventDefault();
          return false;
        }
      });

    // Change password
    $('body').on('click', '#resetPasswordFormButton', function(){
        $('#loading').show();
        var resetPasswordForm = $("#resetPasswordForm");
        var formData = resetPasswordForm.serialize();
        $( '#password-error' ).html( "" );

        $.ajax({
            url:'user/resetpassword',
            type:'POST',
            data:formData,
            success:function(data) {
                console.log(data);
                if(data.errors) {
                    if(data.errors.password){
                        $( '#password-error' ).html( data.errors.password[0] );
                    }
                    $('#loading').hide();
                }
                if(data.success) {
                    // setInterval(function(){ 
                    //     $('#resetPassword').modal('hide');
                    // }, 3000);
                    $('#resetPassword').modal('hide');
                    $('#loading').hide();
                    Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'Password has been reset successfully.'
                            });

                }
            },
        });
    });

} )
</script>
@stop

