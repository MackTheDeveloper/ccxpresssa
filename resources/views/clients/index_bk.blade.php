@extends('layouts.custom')

@section('title')
<?php echo $flag == 'B' ? 'Billing Parties' : 'Clients' ?>
@stop

<?php 
    $permissionClientsEdit = App\User::checkPermission(['update_clients'],'',auth()->user()->id); 
    $permissionClientsDelete = App\User::checkPermission(['delete_clients'],'',auth()->user()->id); 
    $permissionClientsResetPassword = App\User::checkPermission(['reset_password_clients'],'',auth()->user()->id); 
    
?>

@section('breadcrumbs')
    @include('menus.client-management')
@stop

@section('content')
<section class="content-header">
    <h1><?php echo $flag == 'B' ? 'Billing Parties' : 'Clients' ?></h1>
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
                <th><?php echo $flag == 'B' ? 'Billing Party' : 'Client' ?></th>
                <th>Phone Number</th>
                <th>Email</th>
                <?php if($flag == 'B') { ?>
                <th>Currency</th>
                <th>Cash/Credit</th>
                <th>Available Credit</th>
                <?php } ?>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <?php $currencyData = App\Currency::getData($user->currency); 
                if($flag == 'B')
                    $checkClient = App\Clients::checkExistingCurrencyClient($user->company_name); 
                ?>
                <tr data-editlink="{{ route('viewdetails',[$user->id,$flag]) }}" id="<?php echo $user->id; ?>" class="edit-row">
                    <td style="display: none;">{{$user->id}}</td>
                    <td>{{$user->company_name}}</td>
                    <td>{{$user->phone_number}}</td>
                    <td>{{$user->email}}</td>
                    <?php if($flag == 'B') { ?>
                    <td>{{$currencyData->code}}</td>
                    <td>{{$user->cash_credit}}</td>
                    <td class="alignright">{{$user->cash_credit == 'Credit' ? number_format($user->available_balance,2): '-'}}</td>
                    <?php } ?>
                    <td><?php $btnClass = $user->status == '1' ? 'customButtonSuccess' : 'customButtonAlert'; ?><button class="customButtonInGrid <?php echo $btnClass; ?>" data-userid="{{$user->id}}" value="{{$user->status}}"><?php echo Config::get('app.userStatus')[$user->status]; ?></button></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteclient',$user->id);
                        $edit =  route('editclient',[$user->id,$flag]);
                        ?>
                        <?php if($permissionClientsEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionClientsDelete) { 
                            if($user->company_name != 'UPS' && $user->company_name != 'Aeropost')
                            {
                            ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($permissionClientsResetPassword) { ?>
                                    <li><a class="clsResetPsw" href="#" data-toggle="modal" data-userid={{$user->id}} data-target="#resetPassword">Reset Password</a></li>
                                    <?php } ?>
                                    <?php if($flag == 'B' && $checkClient == 0) { ?>
                                    <li><a class="copyclient" href="#" data-userid={{$user->id}} data-currency={{$currencyData->code}}>Copy Client</a></li>
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
                <h3 class="modal-title text-center primecolor">Client Detail</h3>
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
                                    url     : '<?php echo url('clients/changeuserstatus'); ?>',
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
    $('.clsResetPsw').click(function(){
        $('#userId').val($(this).data('userid'));
        return true;
    })

    // Change password
    $('body').on('click', '#resetPasswordFormButton', function(){
        $('#loading').show();
        var resetPasswordForm = $("#resetPasswordForm");
        var formData = resetPasswordForm.serialize();
        $( '#password-error' ).html( "" );

        $.ajax({
            url:'clients/resetpassword',
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

    $('.copyclient').click(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var userId = $(this).data('userid');
        var currencyIs = $(this).data('currency');
        if(currencyIs == 'USD')
            var newCurrency = 'HTG';
        else    
            var newCurrency = 'USD';
        Lobibox.confirm({
            msg: "Are you sure to copy client with currency "+newCurrency+"?",
            callback: function (lobibox, type) {

                         if(type == 'yes')
                           {
                                 $.ajax({
                                    type    : 'post',
                                    url     : '<?php echo url('clients/copyclient'); ?>',
                                    data    : {'userId':userId,'newCurrency':newCurrency,'flag':'<?php echo $flag; ?>'},
                                    success : function (response) {
                                        window.location.href = '<?php echo route("clients",$flag); ?>';
                                        }
                                    });
                           }
                          else
                            {

                            }    
                }
        })
    })

} )
</script>
@stop

