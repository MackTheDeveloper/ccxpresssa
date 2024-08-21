<?php if($flagModule == 'admin-manager') {  if(!empty($noti)) { $countNoti = 0; foreach($noti as $k => $v) { 

        $countNoti = $countNoti + 1;
        if($countNoti > 5) 
            break;

        $unreadnoti = '';
        if($v->notificationStatus == 1) 
            $unreadnoti = 'unreadnoti'; ?>
    <?php if($v->flagModule == 'CargoWarehouseFileStatusChanged') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargo',[$v->id,$v->cargo_operation_type,'fromNotification'])}}"><?php echo $v->notificationMessage; ?></a></li>
    <?php }  else if($v->flagModule == 'CargoExpense') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('editagentexpensesbyadmin',[$v->expense_id,'flagFromExpenseListing'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } else if($v->flagModule == 'UpsExpense')  { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('editagentupsexpensesbyadmin',[$v->expense_id,'flagFromExpenseListing'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } else if($v->flagModule == 'CargoWarehouseInvoiceGenerated')  { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargoinvoicedetails',[$v->id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } else if($v->flagModule == 'CargoWarehouseInvoiceStatusChangedByCashier')  { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargoinvoicedetails',[$v->id,'fromNotificationCargoWarehouseInvoiceStatusChangedByCashier'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } else if($v->flagModule == 'CargoFileSentToWarehouse')  { ?>
    <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargo',[$v->id,$v->cargo_operation_type,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } ?>
<?php } } ?><li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li><?php } ?>


<?php if($flagModule == 'cashier') { if(!empty($noti)) { $countNoti = 0; foreach($noti as $k => $v) {
    $countNoti = $countNoti + 1;
        if($countNoti > 5) 
            break;

    $unreadnoti = '';
        if($v->notificationStatus == 1) 
            $unreadnoti = 'unreadnoti'; ?>
    <?php if($v->flagModule == 'CargoExpense') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleexpensecashier',[$v->expense_id,$v->cargo_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php }  else if($v->flagModule == 'UpsExpense') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleupsexpensecashier',[$v->expense_id,$v->ups_details_id])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } else if($v->flagModule == 'CargoWarehouseFileStatusChanged') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargodetailforcashier',[$v->id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></li>
    <?php } ?>
<?php } } ?><li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li><?php } ?>

<?php if($flagModule == 'agent') { if(!empty($noti)) {  $countNoti = 0; foreach($noti as $k => $v) {
    $countNoti = $countNoti + 1;
        if($countNoti > 5) 
            break;

    $unreadnoti = '';
        if($v->notificationStatus == 1) 
            $unreadnoti = 'unreadnoti'; ?>
    <?php if($v->flagModule == 'Cargo File Assigned') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargodetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></li>
    <?php }  else if($v->flagModule == 'Courier File Assigned') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcourierdetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></li>
    <?php } else if($v->flagModule == 'CargoExpense') { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsingleexpense',[$v->expense_id,$v->cargo_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
    <?php }  else if($v->flagModule == 'CargoWarehouseFileStatusChanged') {  ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargodetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></li>    
    <?php } else { ?>
        <li><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsingleupsexpense',[$v->expense_id,$v->ups_details_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></li>
    <?php } ?>
<?php } } ?><li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li><?php } ?>

<?php if($flagModule == 'warehouse') { if(!empty($noti)) { $countNoti = 0;  foreach($noti as $k => $v) {
    $countNoti = $countNoti + 1;
        if($countNoti > 5) 
            break;

    $unreadnoti = '';
        if($v->notificationStatus == 1) 
            $unreadnoti = 'unreadnoti'; ?>
    <?php if($v->flagModule == 'Cargo') { ?>
    <li><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargodetailforwarehouse',[$v->id,'fromNotification'])}}"><?php echo $v->notificationMessage; ?></a></li>
    <?php } else if($v->flagModule == 'Invoice') { ?>
    <li><a class="<?php echo $unreadnoti; ?>"  href="{{route('editwarehouseinvoice',[$v->id,'fromNotification'])}}"><?php echo $v->notificationMessage; ?></a></li>    
    <?php } ?>
<?php } } ?><li><a style="text-align: center;font-weight: bold;" href="{{route('viewallnotifications')}}" target="_blank">View All</a></li><?php } ?>