@extends('layouts.custom')

@section('title')
Expense Detail
@stop


@section('breadcrumbs')
<?php if ($flag == 'cargoExpense') { ?>
    @include('menus.cargo-expense')
<?php }
if ($flag == 'housefileExpense') { ?>
    @include('menus.cargo-expense')
<?php }
if ($flag == 'aeropostExpense') { ?>
    @include('menus.aeropost-expense')
<?php }
if ($flag == 'ccpackExpense') { ?>
    @include('menus.ccpack-expense')
<?php }
if ($flag == 'upsExpense') { ?>
    @include('menus.ups-expense')
<?php }
if ($flag == 'upsMasterExpense') { ?>
    @include('menus.ups-expense')
<?php }
if ($flag == 'aeropostMasterExpense') { ?>
    @include('menus.aeropost-expense')
<?php }
if ($flag == 'ccpackMasterExpense') { ?>
    @include('menus.ccpack-expense')
<?php } ?>
@stop

@section('content')
<section class="content-header">
    <h1>Expense Detail <?php echo !empty($moduleData) ? 'File #' . $moduleData->file_number : '' ?></h1>
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
            <?php
            if ($flag == 'cargoExpense')
                $moduleId = $singleExpense->cargo_id;
            else if ($flag == 'housefileExpense')
                $moduleId = $singleExpense->house_file_id;
            else if ($flag == 'aeropostExpense')
                $moduleId = $singleExpense->aeropost_id;
            else if ($flag == 'ccpackExpense')
                $moduleId = $singleExpense->ccpack_id;
            else if ($flag == 'upsExpense')
                $moduleId = $singleExpense->ups_details_id;
            else if ($flag == 'upsMasterExpense')
                $moduleId = $singleExpense->ups_master_id;
            else if ($flag == 'aeropostMasterExpense')
                $moduleId = $singleExpense->aeropost_master_id;
            else if ($flag == 'ccpackMasterExpense')
                $moduleId = $singleExpense->ccpack_master_id;
            ?>
            <a class="btn btn-success approveAllExpense" style="float:right;margin-bottom:10px" href="{{route('approveallexpense',[$moduleId,$expenseId,$flag])}}">Approve All Expenses</a>
            <div class="row" style="margin-bottom: 10px">
                <div class="col-md-12">
                    <div class="trCancelledFileDiv1"></div>
                    <div class="trCancelledFileDiv2">Cancelled</div>
                </div>
            </div>
            <?php if ($flag == 'cargoExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Description</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\Cargo::getCargoData($items->cargo_id);
                        if (empty($dataCargo))
                            continue;

                        if ($items->request_by_role == 12 || $items->request_by_role == 10)
                            $edit =  route('editagentexpensesbyadmin', [$items->expense_id, 'flagFromNotificationPage']);
                        else
                            $edit =  route('editexpensevoucher', [$items->expense_id, 'flagFromNotificationPage']);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td><?php echo $items->note != '' ? $items->note : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getInvoicesOfFile($items->cargo_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    if ($items->request_by_role == 12)
                                        $edit =  route('editagentexpensesbyadmin', [$items->expense_id, 'flagFromExpenseListing']);
                                    else
                                        $edit =  route('editexpensevoucher', [$items->expense_id, 'flagFromExpenseListing']);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('getprintsingleexpense',[$items->expense_id,$items->cargo_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } else if ($flag == 'housefileExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Description</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\HawbFiles::getHouseFileData($items->house_file_id);
                        if (empty($dataCargo))
                            continue;


                        ?>
                        <tr data-editlink="{{ route('viewdetailshousefileexpense',$items->expense_id) }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td><?php echo $items->note != '' ? $items->note : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getHouseFileInvoicesOfFile($items->house_file_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deletehousefileexpense', $items->expense_id);
                                    if ($items->request_by_role == 12)
                                        $edit =  route('edithousefileexpenserequestedbyagent', [$items->expense_id]);
                                    else
                                        $edit =  route('edithousefileexpense', [$items->expense_id]);
                                    ?>
                                    <?php
                                    //echo App\Expense::getPrints($items->expense_id,$items->cargo_id); 
                                    /*$urlAction = url("/expense/getprintsingleexpense/$items->expense_id/$items->cargo_id");
                                $backgroundActions = new App\User;
                                $backgroundActions->backgroundPost($urlAction);*/
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('getprintsinglehousefileexpense',[$items->expense_id,$items->house_file_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;


                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach

                    </tbody>

                </table>
            <?php } else if ($flag == 'aeropostExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Description</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\Aeropost::getAeropostData($items->aeropost_id);
                        if (empty($dataCargo))
                            continue;


                        $edit =  route('aeropostexpenseedit', [$items->expense_id]);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td><?php echo $items->note != '' ? $items->note : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getAeropostInvoicesOfFile($items->aeropost_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('printoneaeropostexpense',[$items->expense_id,$items->aeropost_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } else if ($flag == 'ccpackExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Description</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\ccpack::getccpackdetail($items->ccpack_id);
                        if (empty($dataCargo))
                            continue;


                        $edit =  route('ccpackexpenseedit', [$items->expense_id]);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td><?php echo $items->note != '' ? $items->note : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getCcpackInvoicesOfFile($items->ccpack_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('printoneaeropostexpense',[$items->expense_id,$items->aeropost_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } else if ($flag == 'upsExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\Ups::getUpsData($items->ups_details_id);
                        if (empty($dataCargo))
                            continue;


                        if ($items->request_by_role == 12 || $items->request_by_role == 10)
                            $edit =  route('editagentupsexpensesbyadmin', [$items->expense_id, 'flagFromExpenseListing']);
                        else
                            $edit =  route('editupsexpense', $items->expense_id);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getUpsInvoicesOfFile($items->ups_details_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('getprintsingleupsexpense',[$items->expense_id,$items->ups_details_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } else if ($flag == 'upsMasterExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\UpsMaster::getMasterUpsData($items->ups_master_id);
                        if (empty($dataCargo))
                            continue;


                        if ($items->request_by_role == 12 || $items->request_by_role == 10)
                            $edit =  route('editagentupsmasterexpense', [$items->expense_id, 'flagFromExpenseListing']);
                        else
                            $edit =  route('editupsmasterexpense', $items->expense_id);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getUpsMasterInvoicesOfFile($items->ups_master_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('printsingleupsmasterexpense',[$items->expense_id,$items->ups_master_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } else if ($flag == 'aeropostMasterExpense') {  ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\AeropostMaster::getMasterAeropostData($items->aeropost_master_id);
                        if (empty($dataCargo))
                            continue;

                        $edit =  route('editaeropostmasterexpense', [$items->expense_id]);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getAeropostMasterInvoicesOfFile($items->aeropost_master_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('printsingleaeropostmasterexpense',[$items->expense_id,$items->aeropost_master_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } else if ($flag == 'ccpackMasterExpense') { ?>
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>
                                <div style="cursor:pointer" class="fa fa-plus fa-expand-collapse-all"></div>
                            </th>
                            <th>Date</th>
                            <th>Voucher No.</th>
                            <th>File No.</th>
                            <th>AWB / BL No.</th>
                            <th>Cash/Bank</th>
                            <th>Consignataire / Consignee</th>
                            <th>Shipper</th>
                            <th>Currency</th>
                            <th>Total Amount</th>
                            <th>Invoice Numbers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach ($expenseData as $items)
                        <?php $dataExpenses = App\ExpenseDetails::checkExpense($items->expense_id);
                        $cls = '';
                        if ($dataExpenses > 0)
                            $cls = 'fa fa-plus';

                        $dataCargo = App\CcpackMaster::getMasterCcpackData($items->ccpack_master_id);
                        if (empty($dataCargo))
                            continue;

                        $edit =  route('editccpackmasterexpense', [$items->expense_id]);
                        ?>
                        <tr data-editlink="{{ $edit }}" id="<?php echo $items->expense_id; ?>" class="edit-row <?php echo $items->deleted == '1' ? 'trCancelledFile' : '' ?>">
                            <td style="display: none">{{$items->expense_id}}</td>
                            <td style="display: block;text-align: center;padding-top: 13px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-expenseid="<?php echo $items->expense_id; ?>"></td>
                            <td><?php echo date('d-m-Y', strtotime($items->exp_date)) ?></td>
                            <td>{{$items->voucher_number}}</td>
                            <td><?php echo $dataCargo->file_number;  ?></td>
                            <td>{{$items->bl_awb}}</td>
                            <td><?php $currencyData = App\CashCredit::getCashCreditData($items->cash_credit_account);
                                echo !empty($currencyData) ? '(' . $currencyData->currency_code . ')' . ' ' . $currencyData->name : '-'; ?></td>
                            <td>{{$items->consignee}}</td>
                            <td>{{$items->shipper}}</td>
                            <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($items->expense_id);
                                echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                            <td class="alignright"><?php echo App\Expense::getExpenseTotal($items->expense_id);  ?></td>
                            <td><?php echo App\Expense::getCcpackMasterInvoicesOfFile($items->ccpack_master_id);  ?></td>
                            <td>{{$items->expense_request}}</td>
                            <td>
                                <div class='dropdown'>
                                    <?php
                                    $delete =  route('deleteexpensevoucher', $items->expense_id);
                                    ?>
                                    <a title="Click here to print" target="_blank" href="{{ route('printsingleccpackmasterexpense',[$items->expense_id,$items->ccpack_master_id]) }}"><i class="fa fa-print"></i></a>&nbsp; &nbsp;

                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>



                                </div>

                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>





</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "date-uk-pre": function(a) {
                if (a == null || a == "") {
                    return 0;
                }
                var ukDatea = a.split('-');
                return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
            },

            "date-uk-asc": function(a, b) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },

            "date-uk-desc": function(a, b) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        });
        $('#example').DataTable({
            'stateSave': true,
            "columnDefs": [{
                "targets": [1, -1],
                "orderable": false
            }, {
                type: 'date-uk',
                targets: 2
            }],
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container())
                    .on('click', function() {
                        $('#loading').show();
                        setTimeout(function() {
                            $("#loading").hide();
                        }, 200);
                        $('.expandpackage').each(function() {
                            if ($(this).hasClass('fa-minus')) {
                                $(this).removeClass('fa-minus');
                                $(this).addClass('fa-plus');
                            }
                        })
                    });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            }
        });

        setTimeout(function() {
            //$(".fa-expand-collapse-all").trigger('click');
            $(".expandpackage").trigger('click');
            if ($('.fa-expand-collapse-all').hasClass('fa-plus')) {
                $('.fa-expand-collapse-all').removeClass('fa-plus');
                $('.fa-expand-collapse-all').addClass('fa-minus');
            } else {
                $('.fa-expand-collapse-all').removeClass('fa-minus');
                $('.fa-expand-collapse-all').addClass('fa-plus');
            }
        }, 10);

        $(document).delegate('.fa-expand-collapse-all', 'click', function() {
            $('#loading').show();
            if ($(this).hasClass('fa-plus')) {
                $(this).removeClass('fa-plus');
                $(this).addClass('fa-minus');
            } else {
                $(this).removeClass('fa-minus');
                $(this).addClass('fa-plus');
            }
            $('.expandpackage').trigger('click');
        });

        //$('.expandpackage').click(function(){
        $(document).delegate('.expandpackage', 'click', function() {
            var rowId = $(this).data('rowid');
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 200);
            //$('#loading').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var thiz = $(this);
            var parentTR = thiz.closest('tr');
            if (thiz.hasClass('fa-plus')) {
                /*$('.childrw').remove();
                $('.fa-minus').each(function(){
                    $(this).removeClass('fa-minus');    
                    $(this).addClass('fa-plus');
                })*/

                thiz.removeClass('fa-plus');
                thiz.addClass('fa-minus');
                var expenseId = $(this).data('expenseid');
                var rowId = $(this).data('rowid');
                var urlzte = '<?php echo route("expandexpenses"); ?>';
                var flagW = '';
                <?php if ($flag == 'cargoExpense') { ?>
                    var flagW = 'cargo';
                <?php } ?>
                <?php if ($flag == 'upsExpense') { ?>
                    var flagW = 'Ups';
                <?php } ?>
                <?php if ($flag == 'housefileExpense') { ?>
                    var flagW = 'houseFile';
                <?php } ?>
                <?php if ($flag == 'aeropostExpense') { ?>
                    var flagW = 'aeropost';
                <?php } ?>
                <?php if ($flag == 'ccpackExpense') { ?>
                    var flagW = 'ccpack';
                <?php } ?>
                <?php if ($flag == 'upsMasterExpense') { ?>
                    var flagW = 'UpsMaster';
                <?php } ?>
                <?php if ($flag == 'aeropostMasterExpense') { ?>
                    var flagW = 'AeropostMaster';
                <?php } ?>
                <?php if ($flag == 'ccpackMasterExpense') { ?>
                    var flagW = 'CcpackMaster';
                <?php } ?>
                $.ajax({
                    url: urlzte,
                    type: 'POST',
                    data: {
                        expenseId: expenseId,
                        rowId: rowId,
                        flagW: flagW
                    },
                    success: function(data) {

                        $(data).insertAfter(parentTR).slideDown();
                    },
                });
                //$('#loading').hide();
            } else if (thiz.hasClass('fa-minus')) {
                thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.child-' + rowId).remove();
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            } else {
                /* thiz.removeClass('fa-minus');
                thiz.addClass('fa-plus');
                $('.child-'+rowId).remove(); */
                //parentTR.next('tr').remove();
                //$('#loading').hide();

            }
        })

        $('.approveAllExpense').click(function() {
            if (confirm("Are you sure, you want to approve all the expenses?")) {
                return true;
            } else {
                return false;
            }
        })


    })
</script>
@stop