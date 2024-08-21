	@extends('layouts.custom')
	@section('title')
	Expense Details
	@stop
	@section('sidebar')
	<aside class="main-sidebar">
	    <ul class="sidemenu nav navbar-nav side-nav">
	    	<?php 
        $checkPermissionCreateCargoImport = App\User::checkPermission(['import_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoImport = App\User::checkPermission(['update_import'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoImport = App\User::checkPermission(['delete_import'],'',auth()->user()->id);
        $checkPermissionImportIndexCargo = App\User::checkPermission(['import_cargo_index'],'',auth()->user()->id);     
        $checkPermissionAddImportExpenseCargo = App\User::checkPermission(['add_cargo_import_expense'],'',auth()->user()->id);


        $checkPermissionCreateCargoExport = App\User::checkPermission(['export_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoExport = App\User::checkPermission(['update_export'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoExport = App\User::checkPermission(['delete_export'],'',auth()->user()->id);
        $checkPermissionExportIndexCargo = App\User::checkPermission(['export_cargo_index'],'',auth()->user()->id);  
        $checkPermissionAddExportExpenseCargo = App\User::checkPermission(['add_cargo_export_expense'],'',auth()->user()->id);


        $checkPermissionCreateCargoLocale = App\User::checkPermission(['locale_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoLocale = App\User::checkPermission(['update_locale'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoLocale = App\User::checkPermission(['delete_locale'],'',auth()->user()->id);
        $checkPermissionLocaleIndexCargo = App\User::checkPermission(['locale_cargo_index'],'',auth()->user()->id);  
        $checkPermissionAddLocaleExpenseCargo = App\User::checkPermission(['add_cargo_locale_expense'],'',auth()->user()->id);
        
        //echo View::make('layouts.cargomenu',['id'=>0,'flag'=>'listing'])->render(); 
        ?>
         <?php if($checkPermissionImportIndexCargo) { ?>
                <li class="widemenu">
                    <a href="{{ route('viewcargo',[$rid,$id]) }}">Basic</a>
                </li>
        <?php } 
            if($checkPermissionCreateCargoImport) { ?>
                <li class="widemenu active">
                    <a href="{{ route('cargoexpensedetail',[$rid,$id]) }}">Expense/Cost</a>
                </li>
        <?php } ?>
                <li class="widemenu">
                    <a href="{{ route('invoicedetail',[$rid,$id]) }}">Invoice</a>
                </li>
                <li class="widemenu">
                    <a href="{{ route('reportdetail',[$rid,$id]) }}">Report</a>
                </li>

	           			

	    </ul>
	</aside>
	@stop
	@section('content')
	<section class="content-header">
	    <h1>Expense Detail</h1>
	</section>

	<section class="content editupscontainer">
	    @if(Session::has('flash_message'))
	        <div class="alert alert-success flash-success">
	            {{ Session::get('flash_message') }}
	        </div>
	    @endif
	    @if(Session::has('flash_message_error'))
	        <div class="alert alert-danger flash-danger">
	            {{ Session::get('flash_message_error') }}
	        </div>
	    @endif
	    
		<div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
	    <div class="box box-success">
	    <div class="box-body" style="float: left;width: 100%">
            <button id="btnCreateExpense" class="btn btn-success actionbtnindatatable btnModalPopup" value="<?php echo route('createexpenseinbasiccargo',[$rid,'cargo']) ?>">Add Expense</button>
	    <div class="tableExpenses table-responsive">
                        <table class="table" id="example1">
                            <thead>
                                <tr>
                                    <th>Billing Type</th>
                                    <th>Amount</th>
                                    <th>Client</th>
                                    <th>Assignee</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($dataExpense)) { 
                                    foreach($dataExpense as $k => $v)
                                    {
                                        $v = (object) $v;
                                    ?>
                                <tr>
                                    <td>
                                    <?php $billingData = app('App\BillingItems')->getBillingData($v->expense_type); 
                                        echo !empty($billingData->billing_name) ? $billingData->billing_name : "-";
                                    ?>
                                        </td>
                                    <td><?php echo $v->amount; ?></td>
                                    <td><?php echo $v->client; ?></td>
                                    <td><?php $dataUser = app('App\User')->getUserName($v->assignee); 
                                              echo !empty($dataUser->name) ? $dataUser->name : "No Assignee";
                                    ?></td>
                                    <td>
                                        <div class='dropdown'>
                                        <?php 
                                            $delete =  route('deleteexpense',$v->expense_id);
                                        ?>
                                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                    </div>
                                    </td>
                                    
                                </tr>
                                <?php } }  else { ?>
                                    <tr>
                                        <td>No data found.</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php } ?>
                            </tbody>
                        </table>
                    </div>
	        </div>
	        </div>

	<div id="modalCreateExpense" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">Add Expense</h3>
            </div>
            <div class="modal-body" id="modalContentCreateExpense" style="overflow: hidden;">
            </div>
        </div>

    </div>
</div>
	                
	        
	</section>
	@endsection
	@section('page_level_js')
	<script type="text/javascript">
	$(document).ready(function() {
		  $('#example1').DataTable({
        "ordering": false
    });

	})
	</script>
	@stop

