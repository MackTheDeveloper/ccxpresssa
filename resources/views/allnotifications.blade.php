@extends('layouts.custom')
@section('title')
Notifications
@stop

@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
    <h1 style="font-size: 20px !important;font-weight: 600;">All Notifications</h1>
</section>
<section class="content editupscontainer">
    <div class="box box-success">
        <div class="box-body">

            <div class="detail-container">

                <div style="float: left;width: 100%;margin-bottom: 10px;font-weight: bold;text-align: center">
                    <div class="resultdata50 resultdata50" style="float: left;clear:both">Notification</div>
                    <div class="resultdata10" style="float: left;margin-right: 2px">File Number</div>
                    <div class="resultdata20" style="float: left;margin-right: 2px">Client</div>
                    <div class="resultdata12">Date/Time</div>
                </div>
                <?php if (!empty($notiAll)) { ?>
                    <div>
                        @foreach ($notiAll as $k => $v)

                        <?php if ($flagModule == 'admin-manager') {
                            $unreadnoti = '';
                            if ($v->notificationStatus == 1)
                                $unreadnoti = 'unreadnoti'; ?>
                            <?php if ($v->flagModule == 'CargoExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'cargoExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'UpsExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'upsExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'UpsMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'upsMasterExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'houseFileExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'housefileExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'aeropostExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'aeropostExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'AeropostMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'aeropostMasterExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'ccpackExpense') {  ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'ccpackExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'CcpackMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('expensenotificationoffile',[$v->expense_id,'ccpackMasterExpense'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'administrationExpense') {  ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('editotherexpense',[$v->id])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } ?>
                            <div class="resultdata10" style="float: left;margin-right: 2px"><?php echo !empty($v->file_number) ? $v->file_number : '-'; ?></div>
                            <div class="resultdata20" style="float: left;margin-right: 2px"><?php echo !empty($v->client) ? $v->client : '-'; ?></div>
                            <div class="resultdata12"><?php echo date('d-m-Y H:i:s', strtotime($v->notificationDateTime)) ?></div>
                        <?php } ?>

                        <?php if ($flagModule == 'cashier') {
                            $unreadnoti = '';
                            if ($v->notificationStatus == 1)
                                $unreadnoti = 'unreadnoti'; ?>
                            <?php if ($v->flagModule == 'CargoExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleexpensecashier',[$v->expense_id,$v->cargo_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'UpsExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleupsexpensecashier',[$v->expense_id,$v->ups_details_id])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'UpsMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('viewupsmasterexpenseforcashier',[$v->expense_id,$v->ups_master_id])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'houseFileExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsinglehousefileexpensecashier',[$v->expense_id,$v->house_file_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'aeropostExpense') {  ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleaeropostexpensecashier',[$v->expense_id,$v->aeropost_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'AeropostMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('viewaeropostmasterexpenseforcashier',[$v->expense_id,$v->aeropost_master_id])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'ccpackExpense') {  ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleccpackexpensecashier',[$v->expense_id,$v->ccpack_id,'fromNotification'])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'CcpackMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('viewccpackmasterexpenseforcashier',[$v->expense_id,$v->ccpack_master_id])}}"><?php echo $v->notificationMessage;; ?></a></div>
                            <?php } else if ($v->flagModule == 'administrationExpense') {  ?>
                                <div class="resultdata50 resultdata50" style="float: left;clear:both"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintviewsingleadministrationexpensecashier',[$v->id,'fromNotification'])}}"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } ?>
                            <div class="resultdata10" style="float: left;margin-right: 2px"><?php echo !empty($v->file_number) ? $v->file_number : '-'; ?></div>
                            <div class="resultdata20" style="float: left;margin-right: 2px"><?php echo !empty($v->client) ? $v->client : '-'; ?></div>
                            <div class="resultdata12"><?php echo date('d-m-Y H:i:s', strtotime($v->notificationDateTime)) ?></div>
                        <?php } ?>

                        <?php if ($flagModule == 'agent') {
                            $unreadnoti = '';
                            if ($v->notificationStatus == 1)
                                $unreadnoti = 'unreadnoti'; ?>

                            <?php if ($v->flagModule == 'Cargo File Assigned') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcargodetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'UPS File Assigned') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('viewcourierdetailforagent',$v->id)}}"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'CargoExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsingleexpense',[$v->expense_id,$v->cargo_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'UpsExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsingleupsexpense',[$v->expense_id,$v->ups_details_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'UpsMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('printsingleupsmasterexpense',[$v->expense_id,$v->ups_master_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'houseFileExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('getprintsinglehousefileexpense',[$v->expense_id,$v->house_file_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'aeropostExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('printoneaeropostexpense',[$v->expense_id,$v->aeropost_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'AeropostMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('printsingleaeropostmasterexpense',[$v->expense_id,$v->aeropost_master_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'ccpackExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('printoneccpackexpense',[$v->expense_id,$v->ccpack_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } else if ($v->flagModule == 'CcpackMasterExpense') { ?>
                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('printsingleccpackmasterexpense',[$v->expense_id,$v->ccpack_master_id,'fromNotification'])}}" target="_blank"><?php echo $v->notificationMessage; ?></a></div>
                            <?php } ?>

                            <div class="resultdata10" style="float: left;margin-right: 2px"><?php echo $v->file_number; ?></div>
                            <div class="resultdata20" style="float: left;margin-right: 2px"><?php echo !empty($v->client) ? $v->client : '-'; ?></div>
                            <div class="resultdata12"><?php echo date('d-m-Y H:i:s', strtotime($v->notificationDateTime)) ?></div>
                        <?php } ?>

                        <?php if ($flagModule == 'warehouse') {
                            if (checkNonBoundedWH() == 'Yes') {
                                $unreadnoti = '';
                                if ($v->notificationStatus == 1)
                                    $unreadnoti = 'unreadnoti'; ?>

                                <div class="resultdata50 resultdata50" style="float: left;"><a class="<?php echo $unreadnoti; ?>" href="{{route('acceptfiles',[$v->id,'fromNotification',$v->flagModule])}}"><?php echo $v->notificationMessage; ?></a></div>

                                <div class="resultdata10" style="float: left;margin-right: 2px"><?php echo $v->file_number; ?></div>
                                <div class="resultdata20" style="float: left;margin-right: 2px"><?php echo !empty($v->client) ? $v->client : '-'; ?></div>
                                <div class="resultdata12"><?php echo date('d-m-Y H:i:s', strtotime($v->notificationDateTime)) ?></div>
                        <?php }
                        } ?>




                        @endforeach
                    </div>
                <?php } else { ?>
                    <h4 style="float: left;width: 100%;font-size: 15px;">No Notifications Found.</h4>
                <?php } ?>

            </div>
        </div>
    </div>
</section>
@endsection