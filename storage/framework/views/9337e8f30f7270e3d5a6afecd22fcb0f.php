<?php $__env->startSection('title'); ?>
Dashboard
<?php $__env->stopSection(); ?>


<?php 
$permissionCourierImportListing = App\User::checkPermission(['listing_courier_import'],'',auth()->user()->id);
$permissionCourierExpensesListing = App\User::checkPermission(['listing_courier_expenses'],'',auth()->user()->id);
$permissionCourierInvoicesListing = App\User::checkPermission(['listing_courier_invoices'],'',auth()->user()->id);
$permissionCourierCustomExpensesListing = App\User::checkPermission(['listing_courier_custom_expenses'],'',auth()->user()->id);

$permissionCargoListing = App\User::checkPermission(['listing_cargo'],'',auth()->user()->id);
$permissionCargoExpensesListing = App\User::checkPermission(['listing_cargo_expenses'],'',auth()->user()->id);
$permissionCargoInvoicesListing = App\User::checkPermission(['listing_cargo_invoices'],'',auth()->user()->id);
$permissionWarehousesListing = App\User::checkPermission(['listing_warehouses'],'',auth()->user()->id);

$permissionClientsListing = App\User::checkPermission(['listing_clients'],'',auth()->user()->id);
$permissionVendorsListing = App\User::checkPermission(['listing_vendors'],'',auth()->user()->id);
$permissionCashBankListing = App\User::checkPermission(['listing_cash_bank'],'',auth()->user()->id);
$permissionCashBankDepositeVouchersListing = App\User::checkPermission(['listing_deposite_vouchers'],'',auth()->user()->id);

$permissionCashCreditReports = App\User::checkPermission(['cash_credit_reports'],'',auth()->user()->id);
$permissionClientCreditReports = App\User::checkPermission(['client_credit_reports'],'',auth()->user()->id);
$permissionMissingInvoiceReports = App\User::checkPermission(['missing_invoice_reports'],'',auth()->user()->id);
$permissionCustomReports = App\User::checkPermission(['custom_reports'],'',auth()->user()->id);

$permissionQB = App\User::checkPermission(['show_quickbooks'],'',auth()->user()->id)
?>
<?php $__env->startSection('content'); ?>
<section class="content-header" style="display: block;position: relative;top: 0px;">
    <h1 style="font-size: 20px !important;font-weight: 600;float: left;">Dashboard</h1>
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
<section class="content editupscontainer" style="float: left;clear: both;width: 100%">
    <div class="box box-success">
        <div class="box-body create-form dashboardform" style="width: 100%">

        	<div class="col-md-6" style="margin-bottom: 20px">
                <div style="width:100%;float: left;">
                    
                    <?php if($permissionCargoListing || $permissionCargoExpensesListing || $permissionCargoInvoicesListing || $permissionWarehousesListing) { ?>
                    <div style="width: 100%;font-weight: bold;font-size: 17px;margin-bottom: 5px;background: #e6e6e6;padding-left: 10px;">
                    <span>Cargo Management</span>
                    </div>
                    <?php } ?>
                    <div style="float: left;width: 100%">
                        <?php if($permissionCargoListing) { ?>
                        <a href="<?php echo route('cargoall') ?>">    
                        <div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
                            <span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Cargo Dossiers</span>
                            <span style="width: 100%;float: left;"><?php echo e(Form::image('images/Dossiers.png', 'alt text', array('class' => 'css-class'))); ?></span>
                        </div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCargoExpensesListing) { ?>
                        <a href="<?php echo route('expenses') ?>">    
                        <div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
                            <span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Cargo File Expenses</span>
                            <span style="width: 100%;float: left;"><?php echo e(Form::image('images/File-expense.png', 'alt text', array('class' => 'css-class'))); ?></span>
                        </div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCargoInvoicesListing) { ?>
                        <a href="<?php echo route('invoices') ?>">                            
                        <div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
                            <span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Cargo Facturations</span>
                            <span style="width: 100%;float: left;"><?php echo e(Form::image('images/Facturation.png', 'alt text', array('class' => 'css-class'))); ?></span>
                        </div>
                        </a>
                        <?php } ?>
                        <?php if($permissionWarehousesListing) { ?>
                        <a href="<?php echo route('warehouses') ?>">    
                        <div class="mblock" style="float:left;width: 25%;text-align: center;background: #e2e2e2;padding: 10px 0px 10px 0px;">
                            <span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Warehouses</span>
                            <span style="width: 100%;float: left;"><?php echo e(Form::image('images/Warehouse.png', 'alt text', array('class' => 'css-class'))); ?></span>
                        </div>
                        </a>
                        <?php } ?>
                    </div>
                </div>

        		<div style="width:100%;float: left;margin-right: 4%;margin-top: 20px">
        			
                    <?php if($permissionCourierImportListing || $permissionCourierExpensesListing || $permissionCourierInvoicesListing || $permissionCourierCustomExpensesListing) { ?>
        			<div style="width: 100%;font-weight: bold;font-size: 17px;margin-bottom: 5px;background: #e6e6e6;padding-left: 10px;">
        			<span>Courier Management</span>
        			</div>
                    <?php } ?>

        			<div style="float: left;width: 100%">
                        <?php if($permissionCourierImportListing) { ?>
                        <a href="<?php echo route('ups') ?>">    
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;;">Courier Dossiers</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Dossiers.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCourierExpensesListing) { ?>    
                        <a href="<?php echo route('upsexpenses') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 14px;font-weight: bold;">Courier File Expenses</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/File-expense.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCourierInvoicesListing) { ?>
                        <a href="<?php echo route('upsinvoices') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Courier Facturations</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Facturation.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCourierCustomExpensesListing) { ?>
                        <a href="<?php echo route('customexpneses') ?>">
        				<div class="mblock" style="float:left;width: 25%;text-align: center;background: #e2e2e2;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Customs</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Customs.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
        			</div>
        		</div>
            </div>

			
			<div class="col-md-6" style="margin-bottom: 20px;">
        		<div style="width:100%;float: left;margin-right: 4%">
        			
                    <?php if($permissionClientsListing || $permissionVendorsListing || $permissionCashBankListing || $permissionCashBankDepositeVouchersListing) { ?>
        			<div style="width: 100%;font-weight: bold;font-size: 17px;margin-bottom: 5px;background: #e6e6e6;padding-left: 10px;">
        			<span>Global Management</span>
        			</div>
                    <?php } ?>

        			<div style="float: left;width: 100%">
                        <?php if($permissionClientsListing) { ?>
                        <a href="<?php echo route('clients') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Clients</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Client.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionVendorsListing) { ?>
                        <a href="<?php echo route('vendors') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Vendors</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Vendors.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCashBankListing) { ?>
                        <a href="<?php echo route('cashcredit') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Accounts</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Petty-cash.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCashBankDepositeVouchersListing) { ?>
                        <a href="<?php echo route('depositcashcredit') ?>">
        				<div class="mblock" style="float:left;width: 25%;text-align: center;background: #e2e2e2;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Deposits</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Deposit.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
        			</div>
        		</div>

        		<div style="width:100%;float: left;margin-top: 20px">
        			
                    <?php if($permissionCashCreditReports || $permissionClientCreditReports || $permissionMissingInvoiceReports || $permissionCustomReports) { ?>
        			<div style="width: 100%;font-weight: bold;font-size: 17px;margin-bottom: 5px;background: #e6e6e6;padding-left: 10px;">
        			<span>Reports</span>
        			</div>
                    <?php } ?>

        			<div style="float: left;width: 100%">
                        <?php if($permissionCashCreditReports) { ?>
                        <a href="<?php echo route('cashcreditreport') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Cash/Bank</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Cash-bank.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionClientCreditReports) { ?>
                        <a href="<?php echo route('missingInvoiceReports') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Missing Invoices</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Missing-invoice.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionMissingInvoiceReports) { ?>
                        <a href="<?php echo route('clientcreditreport') ?>">
        				<div class="mblock" style="float:left;width: 24%;text-align: center;background: #e2e2e2;margin-right: 1%;padding: 10px 0px 10px 0px;">
        					<span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Client Ledger</span>
        					<span style="width: 100%;float: left;"><?php echo e(Form::image('images/Pending-invoice.png', 'alt text', array('class' => 'css-class'))); ?></span>
        				</div>
                        </a>
                        <?php } ?>
                        <?php if($permissionCustomReports) { ?>
                        <a href="<?php echo route('customreport') ?>">
                        <div class="mblock" style="float:left;width: 25%;text-align: center;background: #e2e2e2;padding: 10px 0px 10px 0px;">
                            <span style="width: 100%;float: left;font-size: 15px;font-weight: bold;">Customs</span>
                            <span style="width: 100%;float: left;"><?php echo e(Form::image('images/Pending-invoice.png', 'alt text', array('class' => 'css-class'))); ?></span>
                        </div>
                        </a>
                        <?php } ?>
					</div>
        		</div>
        	</div>

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

<?php $__env->stopSection(); ?>
<?php $__env->startSection('page_level_js'); ?>
<script type="text/javascript">
    $(document).ready(function() {
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

             
            
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/php/cargo/resources/views/home.blade.php ENDPATH**/ ?>