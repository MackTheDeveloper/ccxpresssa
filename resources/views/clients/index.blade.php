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
<style>
    .hide_column {
    display : none;
}
</style>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    DatatableInitiate();
    
    //$('.customButtonInGrid').click(function(){
    $(document).delegate(".customButtonInGrid", "click", function(){ 
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
    //$('.clsResetPsw').click(function(){
    $(document).delegate(".clsResetPsw", "click", function(){ 
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

    
    //$('.copyclient').click(function(){
    $(document).delegate(".copyclient", "click", function(){ 
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
function DatatableInitiate(){
$('#example').DataTable(
    {
        "processing": true,
        "serverSide": true,
        'stateSave': true,
        stateSaveParams: function (settings, data) {
            delete data.order;
        },
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            },
            { targets: [ 0 ],
            className: "hide_column" 
            }],
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        /* drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        }, */
        "ajax":{
            url :"{{url('clientsdatatable/listbydatatableserverside')}}", // json datasource
            data : {'flag':'<?php echo $flag; ?>'},
            error: function(){  // error handling
                $(".example-error").html("");
                $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#example_processing").css("display","none");

            }
        },
        "createdRow": function ( row, data, index ) {
            $('#loading').show();
            setTimeout(function() { $("#loading").hide(); }, 1000);
            var clientId = data[0];
            var editLink = '<?php echo url("clients/viewdetails"); ?>';
            editLink += '/'+clientId+'/'+'<?php echo $flag; ?>';
            $(row).attr('data-editlink',editLink);
            $(row).addClass('edit-row');
            $(row).attr('id',clientId);
            $('td', row).eq(6).addClass('alignright');
        }
        
    });
}
</script>
@stop

