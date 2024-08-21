<?php 
    use App\Currency;
?>


<?php if($flag == 'cargoInvoice') { 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesDelete = App\User::checkPermission(['delete_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    $permissionCargoInvoicesCopy = App\User::checkPermission(['copy_cargo_invoices'],'',auth()->user()->id); 
    ?>        
    
        <div class="box-body">
                <div class="out-filter-secion col-md-6">
                        <div class="from-date-filter-div filterout col-md-5">
                            <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                        </div>
                        <div class="to-date-filter-div filterout col-md-5">
                            <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                        </div>
                        <div class="col-md-2">
                                <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                        </div>
                    </div>
        <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>Date</th>
                <th>Invoice No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Billing Party</th>
                <th>Currency</th>
                <th>Total Amount</th>
                <th>Paid Amount</th>
                <th>Created By</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 0;?>
            @foreach ($invoices as $items)
            @if($items->type_flag == 'Local')
                <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @else 
                <tr data-editlink="{{ route('viewcargoinvoicedetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @endif
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td><?php $dataUser = app('App\User')->getUserName($items->created_by); 
                            echo !empty($dataUser->name) ? $dataUser->name : "-";?></td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteinvoice',$items->id);
                        $edit =  route('editinvoice',$items->id);
                        ?>
                        
                        <a title="View & Print"  target="_blank" href="{{route('viewandprintcargoinvoice',$items->id)}}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCargoInvoicesEdit) { 
                            if($items->type_flag != 'Local'){
                            ?>
                            <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php }
                            else { ?>
                            <a style="display:none" href="javascript:void(0)" title="You can't edit this file."><i class="fa fa-pencil" aria-hidden="true" data-toggle="popover" data-placement="bottom" data-content="Permission Denied"></i></a>&nbsp; &nbsp;
                        <?php
                            } 
                            }
                        ?>
                        <?php if($permissionCargoInvoicesDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                        <ul class='dropdown-menu' style='left:auto;'>
                            <li>
                                <a href="javascript:void(0)"  data-value="{{$items->id}}" class="sendmailonlocalfile"> Send Mail </a>
                            </li>
                            <?php if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') { ?>
                                <?php if($permissionCargoInvoicesPaymentAdd) { 
                                    if($items->type_flag != 'Local'){ ?> 
                                        <li>
                                            <a href="{{ route('addinvoicepayment',[$items->cargo_id,$items->id,0]) }}">Add Payment</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('addinvoicepayment',[0,0,$items->bill_to]) }}">Add Bulk Payment</a>
                                        </li>
                                    <?php } } ?>
                                <?php } else { ?>        
                                    <?php if($items->type_flag != 'Local'){ ?> 
                                    <li>    
                                    <a title="Print Receipt"  target="_blank" href="{{route('printreceiptofinvoicepayment',[$items->id,'invoice','cargo'])}}">Payment Receipt</i></a>
                                    </li>
                                    <?php  } ?>
                                <?php } ?>
                    </ul>
                        </div>
                        
                    </td>
                </tr>
                <?php $count++?>
            @endforeach
            
        </tbody>
    </table>
    </div>
<?php } ?>

<?php if($flag == 'upsInvoice') { 
    $permissionCourierInvoicesEdit = App\User::checkPermission(['update_courier_invoices'],'',auth()->user()->id);
    $permissionCourierInvoicesDelete = App\User::checkPermission(['delete_courier_invoices'],'',auth()->user()->id);
    $permissionCourierInvoicePaymentsAdd = App\User::checkPermission(['add_courier_invoice_payments'],'',auth()->user()->id); 
    $permissionUpsInvoicesCopy = App\User::checkPermission(['copy_courier_invoices'],'',auth()->user()->id); 
    ?>     
    <div class="box-body">
            <div class="out-filter-secion col-md-6">
                    <div class="from-date-filter-div filterout col-md-5">
                        <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-5">
                        <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                    </div>
                </div>
    <table id="example" class="display nowrap" style="width:100%">
    <thead>
            <tr>
                    <th style="display: none">ID</th>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>File No.</th>
                    <th>Billing Term</th>
                    <th>AWB / BL No.</th>
                    <th>Billing Party</th>
                    <th>Type</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
    </thead>
    <tbody>
        @foreach ($invoices as $items)
                <tr data-editlink="{{ route('viewupsinvoicedetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td><?php echo App\Ups::getBillingTerm($items->ups_id); ?></td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td>{{$items->type_flag}}</td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : ''; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteupsinvoice',$items->id);
                        $edit =  route('editupsinvoice',$items->id);
                        ?>

                        <a title="View & Print"  target="_blank" href="{{route('viewandprintupsinvoice',$items->id)}}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCourierInvoicesEdit) { ?>    
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCourierInvoicesDelete) { ?>    
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <?php if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') { ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($permissionCourierInvoicePaymentsAdd) { ?>
                                     <li>
                                        <a href="{{ route('addupsinvoicepayment',[$items->ups_id,$items->id,0]) }}">Add Payment</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('addupsinvoicepayment',[0,0,$items->bill_to]) }}">Add Bulk Payment</a>
                                    </li>
                                    <?php } ?>
                                </ul>
                        <?php } ?>
                        </div>
                        
                    </td>
                </tr>
            @endforeach
        
    </tbody>
</table>
</div>   
<?php } ?>

<?php if($flag == 'aeropostInvoice') { 
    $permissionAeropostInvoicesEdit = App\User::checkPermission(['update_aeropost_invoices'],'',auth()->user()->id);
    $permissionAeropostInvoicesDelete = App\User::checkPermission(['delete_aeropost_invoices'],'',auth()->user()->id);
    $permissionAeropostInvoicePaymentsAdd = App\User::checkPermission(['add_aeropost_invoice_payments'],'',auth()->user()->id); 
    $permissionAeropostInvoicesCopy = App\User::checkPermission(['copy_aeropost_invoices'],'',auth()->user()->id); 
    ?>
    <div class="box-body">
            <div class="out-filter-secion col-md-6">
                    <div class="from-date-filter-div filterout col-md-5">
                        <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-5">
                        <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                    </div>
                </div>
    <table id="example" class="display nowrap" style="width:100%">
    <thead>
            <tr>
                    <th style="display: none">ID</th>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>File No.</th>
                    <th>AWB / BL No.</th>
                    <th>Billing Party</th>
                    <th>Type</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
    </thead>
    <tbody>
            @foreach ($invoices as $items)
            <tr data-editlink="{{ route('viewaeropostinvoicedetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                <td style="display: none">{{$items->id}}</td>
                <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                <td>{{$items->bill_no}}</td>
                <td>{{$items->file_no}}</td>
                <td>{{$items->awb_no}}</td>
                <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                        echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                <td>{{$items->type_flag}}</td>
                <td><?php $dataCurrency = Currency::getData($items->currency); 
                        echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                <td class="alignright">{{number_format($items->total,2)}}</td>
                <td class="alignright">{{number_format($items->credits,2)}}</td>
                <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : ''; ?>">{{$items->payment_status}}</td>
                <td>
                    <div class='dropdown'>
                    <?php 
                    $delete =  route('deleteaeropostinvoice',$items->id);
                    $edit =  route('editaeropostinvoice',$items->id);
                    ?>

                    <a title="View & Print"  target="_blank" href="{{route('viewandprintaeropostinvoice',$items->id)}}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                    <?php if($permissionAeropostInvoicesEdit) { ?>    
                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                    <?php } ?>
                    <?php if($permissionAeropostInvoicesDelete) { ?>    
                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                    <?php } ?>
                    <?php if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') { ?>
                    <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                            <ul class='dropdown-menu' style='left:auto;'>
                                <?php if($permissionAeropostInvoicePaymentsAdd) { ?>
                                 <li>
                                    <a href="{{ route('addaeropostinvoicepayment',[$items->aeropost_id,$items->id,0]) }}">Add Payment</a>
                                </li>
                                <li>
                                    <a href="{{ route('addaeropostinvoicepayment',[0,0,$items->bill_to]) }}">Add Bulk Payment</a>
                                </li>
                                <?php } ?>
                            </ul>
                    <?php } ?>
                    </div>
                    
                </td>
            </tr>
        @endforeach
        
    </tbody>
</table>
</div>   

<?php } ?>

<?php if($flag == 'ccpackInvoice') { 
    $permissionCCpackInvoicesEdit = App\User::checkPermission(['update_ccpack_invoices'],'',auth()->user()->id);
    $permissionCCpackInvoicesDelete = App\User::checkPermission(['delete_courier_invoices'],'',auth()->user()->id);
    $permissionCCpackInvoicePaymentsAdd = App\User::checkPermission(['add_ccpack_invoice_payments'],'',auth()->user()->id); 
    ?>
    <div class="box-body">
            <div class="out-filter-secion col-md-6">
                    <div class="from-date-filter-div filterout col-md-5">
                        <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-5">
                        <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                    </div>
                </div>
    <table id="example" class="display nowrap" style="width:100%">
    <thead>
            <tr>
                    <th style="display: none">ID</th>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>File No.</th>
                    <th>AWB / BL No.</th>
                    <th>Billing Party</th>
                    <th>Type</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
    </thead>
    <tbody>
            @foreach ($invoices as $items)
                <tr data-editlink="{{ route('viewccpackinvoicedetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td>{{$items->type_flag}}</td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : ''; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteinvoice',$items->id);
                        $edit =  route('editccpackinvoice',$items->id);
                        ?>

                        <a title="View & Print"  target="_blank" href="{{route('viewandprintccpackinvoice',$items->id)}}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCCpackInvoicesEdit) { ?>    
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <?php if($permissionCCpackInvoicesDelete) { ?>    
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        <?php if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') { ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <?php if($permissionCCpackInvoicePaymentsAdd) { ?>
                                     <li>
                                        <a href="{{ route('addccpackinvoicepayment',[$items->ccpack_id,$items->id,0]) }}">Add Payment</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('addccpackinvoicepayment',[0,0,$items->bill_to]) }}">Add Bulk Payment</a>
                                    </li>
                                    <?php } ?>
                                </ul>
                        <?php } ?>
                        </div>
                        
                    </td>
                </tr>
            @endforeach
        
    </tbody>
</table>
</div>   

<?php } ?>

<?php if($flag == 'cashierCargoInvoice') { 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    ?>
    <div class="box-body">
            <div class="out-filter-secion col-md-6">
                    <div class="from-date-filter-div filterout col-md-5">
                        <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-5">
                        <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                    </div>
                </div>
    <table id="example" class="display nowrap" style="width:100%">
    <thead>
            <tr>
                    <th style="display: none">ID</th>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>File No.</th>
                    <th>AWB / BL No.</th>
                    <th>Billing Party</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Action</th>
            </tr>
    </thead>
    <tbody>
            @foreach ($invoices as $items)
            @if($items->type_flag == 'Local')
                <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @else
                <tr data-editlink="{{ route('editcashierwarehouseinvoicesoffile',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @endif
            <td style="display: none">{{$items->id}}</td>
            <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
            <td>{{$items->bill_no}}</td>
            <td>{{$items->file_no}}</td>
            <td>{{$items->awb_no}}</td>
            <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                    echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
            <td><?php $dataCurrency = Currency::getData($items->currency); 
                    echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
            <td class="alignright">{{number_format($items->total,2)}}</td>
            <td class="alignright">{{number_format($items->credits,2)}}</td>
            <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
            <td>
                <div class='dropdown'>
                <?php 
                $edit =  route('editcashierwarehouseinvoicesoffile',$items->id);
                ?>
                
                <a title="Click here to print"  target="_blank" href="public/cargoInvoices/<?php echo 'printCargoInvoice_'.$items->id.'.pdf';?>"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
               <?php if($permissionCargoInvoicesEdit) { 
                    if($items->type_flag != 'Local'){
                    ?>
                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                <?php }
                    else { ?>
                    <a href="javascript:void(0)" title="You can't edit this file."><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                <?php
                    } 
                    }
                ?>
                <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                <ul class='dropdown-menu' style='left:auto;'>
                    
                    <li><a href="javascript:void(0)"  data-value="{{$items->id}}" class="sendmailonlocalfile">Send Mail</a></li>
                    
                <?php if($items->payment_status == 'Pending' || $items->payment_status == 'Partial') { ?>
                
                        
                            <?php if($permissionCargoInvoicesPaymentAdd) { 
                                    if($items->type_flag != 'Local'){
                                ?>

                            <li>
                                <a href="{{ route('addcashierinvoicepayment',[$items->cargo_id,$items->id,0]) }}">Add Payment</a>
                            </li>
                            <?php }

                                } ?>
                        </ul>
                <?php } ?>
                </div>
            </td>
        </tr>
    @endforeach
        
    </tbody>
</table>
</div>   

<?php } ?>

<?php if($flag == 'warehouseCargoInvoice') { 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    ?>
    <div class="box-body">
            <div class="out-filter-secion col-md-6">
                    <div class="from-date-filter-div filterout col-md-5">
                        <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-5">
                        <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                    </div>
                </div>
    <table id="example" class="display nowrap" style="width:100%">
    <thead>
            <tr>
                    <th style="display: none">ID</th>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>File No.</th>
                    <th>AWB / BL No.</th>
                    <th>Billing Party</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
    </thead>
    <tbody>
            @foreach ($invoices as $items)
                <tr data-editlink="{{ route('editwarehouseinvoice',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $edit =  route('editwarehouseinvoice',$items->id);
                        ?>
                        
                        <a title="Click here to print"  target="_blank" href="../public/cargoInvoices/<?php echo 'printCargoInvoice_'.$items->id.'.pdf';?>"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCargoInvoicesEdit) { ?>
                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php } ?>
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                        <ul class='dropdown-menu' style='left:auto;'>
                            
                            <li><a href="{{ route('warehouseinvoicesmail') }}" id="" value="{{$items->id}}">Send Mail</a></li>
                        </ul>
                        
                        
                        </div>
                        
                    </td>
                </tr>
            @endforeach
        
    </tbody>
</table>
</div>   

<?php } ?>

<?php if($flag == 'houseFileInvoice') { 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesDelete = App\User::checkPermission(['delete_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    $permissionCargoInvoicesCopy = App\User::checkPermission(['copy_cargo_invoices'],'',auth()->user()->id); 
    ?>
    <div class="box-body">
            <div class="out-filter-secion col-md-6">
                    <div class="from-date-filter-div filterout col-md-5">
                        <input type="text" name="from_date_filter" value="<?php echo (!empty($fromDate) && isset($fromDate)) ? date('d-m-Y',strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div>
                    <div class="to-date-filter-div filterout col-md-5">
                        <input type="text" name="to_date_filter" value="<?php echo (!empty($toDate) && isset($toDate)) ? date('d-m-Y',strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                            <a href="javascript:void(0)" style="width:100%" class="allrecores btn btn-success">Show All</a>
                    </div>
                </div>
    <table id="example" class="display nowrap" style="width:100%">
    <thead>
            <tr>
                    <th style="display: none">ID</th>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>File No.</th>
                    <th>AWB / BL No.</th>
                    <th>Billing Party</th>
                    <th>Currency</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
    </thead>
    <tbody>
            @foreach ($invoices as $items)
            @if($items->type_flag == 'Local')
                <tr data-editlink="{{ route('viewcargolocalfiledetailforcashier',$items->cargo_id) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @else 
                <tr data-editlink="{{ route('viewhousefileinvoicedetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
            @endif
                    <td style="display: none">{{$items->id}}</td>
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\Clients')->getClientData($items->bill_to); 
                            echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></td>
                    <td><?php $dataCurrency = Currency::getData($items->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></td>
                    <td class="alignright">{{number_format($items->total,2)}}</td>
                    <td class="alignright">{{number_format($items->credits,2)}}</td>
                    <td><?php $dataUser = app('App\User')->getUserName($items->created_by); 
                            echo !empty($dataUser->name) ? $dataUser->name : "-";?></td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deletehousefileinvoice',$items->id);
                        $edit =  route('edithousefileinvoice',$items->id);
                        ?>
                        
                        <a title="View & Print"  target="_blank" href="{{route('viewandprinthousefileinvoice',$items->id)}}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;    
                        <?php if($permissionCargoInvoicesEdit) { 
                            if($items->type_flag != 'Local'){
                            ?>
                            <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        <?php }
                            else { ?>
                            <a href="javascript:void(0)" title="You can't edit this file."><i class="fa fa-pencil" aria-hidden="true" data-toggle="popover" data-placement="bottom" data-content="Permission Denied"></i></a>&nbsp; &nbsp;
                        <?php
                            } 
                            }
                        ?>
                        <?php if($permissionCargoInvoicesDelete) { ?>
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <?php } ?>
                        </div>
                        
                    </td>
                </tr>
            @endforeach
        
    </tbody>
</table>
</div>   

<?php } ?>


<script type="text/javascript">

$(document).ready(function() {
    $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
        "date-uk-pre": function ( a ) {
            if (a == null || a == "") {
                return 0;
            }
            var ukDatea = a.split('-');
            return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
        },
    
        "date-uk-asc": function ( a, b ) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },
    
        "date-uk-desc": function ( a, b ) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });
    $('#example').DataTable(
    {
        'stateSave': true,
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            },{ type: 'date-uk', targets: 1 }],
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
} )
</script>


