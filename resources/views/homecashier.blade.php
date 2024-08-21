@extends('layouts.custom')
<?php $permissionQB = App\User::checkPermission(['show_quickbooks'],'',auth()->user()->id) ?>

@section('title')
Dashboard
@stop

@section('content')
<section class="content-header" style="display: block;position: relative;top: 0px;">
    <h1 style="font-size: 20px !important;font-weight: 600;float:left">Dashboard</h1>
    <?php 
    if($permissionQB)
    {
        //session_start();
        if(!isset($_SESSION)) 
            { 
                session_start(); 
            } 
        if (isset($_SESSION['sessionAccessToken'])) { ?>
            <h1 style="float: right;
    margin-top: 0px;
    margin-bottom: 0px;
     color: #00a65a;">Connected with QuickBooks</h1>
        <?php }else { ?> 
            <h1 style="float: right;
    margin-top: 0px;
    margin-bottom: 0px;
     color: #00a65a;">Not Connected with QuickBooks <a href="<?php echo route('home'); ?>">Connect Now</a></h1>
    <?php } } ?>
</section>
<section class="content editupscontainer" style="float: left;clear: both;width: 100%;">
    <div class="box box-success">
        <div class="box-body">

            
           
           Cashier 

        </div>
    </div>
</section>
<div id="modalQBLoginConnection" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">QuickBooks Connect</h3>
            </div>
            <div class="modal-body" id="modalContentQBLoginConnection" style="overflow: hidden;text-align: center;">
            </div>
        </div>

    </div>
</div>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable({
        "ordering": false
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var urlztnn = '<?php echo url("qb/checkconnectedornot"); ?>';
    $.ajax({
    url:urlztnn,
    type:'POST',
    data:'',
    success:function(data) {
            if(data == 0)
            {
                var urlz = '<?php echo url("qb/loginwithconnection"); ?>';   
                $('#modalQBLoginConnection').modal('show').find('#modalContentQBLoginConnection').load(urlz);
            }
                
        }
    });    
})
</script>
@stop
