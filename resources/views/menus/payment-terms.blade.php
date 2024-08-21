<?php 
    $permissionPaymentTermsAdd = App\User::checkPermission(['add_payment_terms'],'',auth()->user()->id); 

    $currentRoute = Route::currentRouteName();
?>
<?php if($permissionPaymentTermsAdd) { ?>
<a class="<?php echo $currentRoute == "createpaymentterm" ? "activeMenu" : ""; ?>" href="{{ route('createpaymentterm') }}"><i style="color: #fff" class="fa fa-plus-circle fa-2x"></i><span class="menuname">Add Payment Term</span></a>
<?php } ?>
<a class="<?php echo $currentRoute == "paymentterms" ? "activeMenu" : ""; ?>" style="margin-left: 10px" href="{{ route('paymentterms') }}"><i style="color: #fff" class="fa fa-list fa-2x"></i><span class="menuname">Payment Terms</span></a>