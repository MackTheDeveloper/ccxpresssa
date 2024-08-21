<?php 
    $permissionCashCreditReports = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
    $permissionClientCreditReports = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
    $permissionMissingInvoiceReports = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
    $permissionCustomReports = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
    $permissionWarehouseReports = App\User::checkPermission(['warehouse_reports'],'',auth()->user()->id); 
    $permissionNonBilledFilesReports = App\User::checkPermission(['non_billed_files'],'',auth()->user()->id);
    $permissionInvoiceReport = App\User::checkPermission(['invoice_report'],'',auth()->user()->id);
    $permissionCashierReport = App\User::checkPermission(['Cashier_report'],'',auth()->user()->id);
    $currentRoute = Route::currentRouteName();
?>


<ul class="nav nav-tabs faq-cat-tabs slider1">
<?php if($permissionCashCreditReports) { ?>
<li>
<a class="<?php echo $currentRoute == "cashcreditallreport" ? "activeMenu" : ""; ?>" href="{{ route('cashcreditallreport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Cash/Bank Report</span></a>
</li>
<?php } ?>
<?php if($permissionClientCreditReports) { ?>
<li>
<a class="<?php echo $currentRoute == "clientcreditallreport" ? "activeMenu" : ""; ?>" href="{{ route('clientcreditallreport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Client Ledger Report</span></a>
</li>
<?php } ?>
<?php if($permissionMissingInvoiceReports) { ?>
<li>
<a class="<?php echo $currentRoute == "missingInvoiceReports" ? "activeMenu" : ""; ?>" href="{{ route('missingInvoiceReports') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Missing Invoices Report</span></a>
</li>
<?php } ?>
<?php if($permissionCustomReports) { ?>
<li>
<a class="<?php echo $currentRoute == "customreport" ? "activeMenu" : ""; ?>" href="{{ route('customreport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Custom Report</span></a>
</li>
<?php } ?>
<?php if($permissionWarehouseReports) { ?>
<li>
<a class="<?php echo $currentRoute == "warehousereport" ? "activeMenu" : ""; ?>" href="{{ route('warehousereport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Warehouse Report</span></a>
</li>
<?php } ?>
<?php if($permissionNonBilledFilesReports) { ?>
<li>
<a class="<?php echo $currentRoute == "nonbilledfiles" ? "activeMenu" : ""; ?>" href="{{ route('nonbilledfiles') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Non Billed Files Report</span></a>
</li>
<?php } ?>
<?php if($permissionInvoiceReport) {?>
<li style="display:none">
<a class="<?php echo $currentRoute == "invoicePaymentReport" ? "activeMenu" : ""; ?>" href="{{ route('invoicePaymentReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Invoice Payment Report</span></a>
</li>
<?php }?>

<?php if($permissionCashierReport) {?>
<li>
<a class="<?php echo $currentRoute == "cashierReport" ? "activeMenu" : ""; ?>" href="{{ route('cashierReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Cashier Report</span></a>
</li>
<?php }?>

<li>
<a class="<?php echo $currentRoute == "commissionReport" ? "activeMenu" : ""; ?>" href="{{ route('commissionReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Commission Report</span></a>
</li>
<li>
<a class="<?php echo $currentRoute == "freeDomicileReport" ? "activeMenu" : ""; ?>" href="{{ route('freeDomicileReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Free Domicile Report</span></a>
</li>
<li>
<a class="<?php echo $currentRoute == "combineReport" ? "activeMenu" : ""; ?>" href="{{ route('combineReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Combine Report</span></a>
</li>
<li>
<a class="<?php echo $currentRoute == "upsexpensepayments" ? "activeMenu" : ""; ?>" href="{{ route('upsexpensepayments') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Payments/Expenses</span></a>
</li>
<li>
<a class="<?php echo $currentRoute == "upsprofitreports" ? "activeMenu" : ""; ?>" href="{{ route('upsprofitreports') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Profit Report</span></a>
</li>
<li>
<a class="<?php echo $currentRoute == "templeteReport" ? "activeMenu" : ""; ?>" href="{{ route('templeteReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">Consolidation Report</span></a>
</li>
<li>
<a class="<?php echo $currentRoute == "upsMissingInvoiceReport" ? "activeMenu" : ""; ?>" href="{{ route('upsMissingInvoiceReport') }}"><i style="color: #fff" class="fa fa-bar-chart fa-2x"></i><span class="menuname">List of Expenses Not Yet Invoiced</span></a>
</li>
</ul>



<script type="text/javascript">
     $('.slider1').bxSlider({
    slideWidth: 360,
    minSlides: 2,
    maxSlides: 3,
    slideMargin: 10,
    touchEnabled: false
  });

</script>
<style type="text/css">
	.nav {
    padding-left: 0;
    margin-bottom: 0;
    list-style: none;
}
	.slider1 li {
    width: 170px !important;
	}
	.slider1 {
		padding:5px 0px 0px 25px;
	}
	.slider1 li a:hover { border: none !important;  }
	.bx-wrapper { border: 0px; }
	.breadcrumbs {padding-top: 0px !important}
	.bx-default-pager { display: none  }
	.bx-viewport { height:110px !important; width: 92% !important;margin-left: 4% !important; }
</style>