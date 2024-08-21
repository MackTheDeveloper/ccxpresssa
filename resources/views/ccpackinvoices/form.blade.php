@extends('layouts.custom')
@section('title')
<?php if (empty($model->flag_invoice)) {
    echo $model->id ? 'Update CCPack Invoice' : 'Add CCPack Invoice';
} else {
    echo $model->id ? 'Update CCPack Old Invoice' : 'Add CCPack Old Invoice';
} ?>
@stop

<?php if (empty($model->flag_invoice)) { ?>
    @section('breadcrumbs')
    @include('menus.ccpack-invoices')
    @stop
<?php } else { ?>
    @section('breadcrumbs')
    @include('menus.old-invoices')
    @stop
<?php } ?>

<?php
$permissionCcpackInvoicesCopy = App\User::checkPermission(['copy_ccpack_invoices'], '', auth()->user()->id);
$permissionCcpackInvoicesDelete = App\User::checkPermission(['delete_ccpack_invoices'], '', auth()->user()->id);
$permissionBillingPartyAdd = App\User::checkPermission(['add_billing_party'], '', auth()->user()->id);
if (!empty($model->id)) {
    $countcontainerdetail = App\ccpackInvoiceItemDetails::where('invoice_id', $model->id)->count();
} else {
    $countcontainerdetail = 0;
}
?>
<?php

use App\ccpack; ?>
@section('content')

@if(Session::has('flash_message'))
<div class="alert alert-success flash-success">
    {{ Session::get('flash_message') }}
</div>
@endif

<section class="content-header">
    <h1><?php if (empty($model->flag_invoice)) {
            echo $model->id ? 'Update CCPack Invoice' : 'Add CCPack Invoice';
        } else {
            echo $model->id ? 'Update CCPack Old Invoice' : 'Add CCPack Old Invoice';
        } ?></h1>
</section>

<section class="content">
    <div class="box box-success" style="float: left;">
        <div class="box-body">
            <?php
            if ($model->id)
                $actionUrl = url('ccpackinvoice/update', $model->id);
            else
                $actionUrl = url('ccpackinvoice/store');
            ?>
            {{ Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <input type="hidden" name="flagFromWhere" value="<?php echo $flagFromWhere; ?>">
            <input type="hidden" name="flag_invoice" value="<?php echo $model->flag_invoice; ?>">
            <input type="hidden" class="count_invoice_items" name="count_invoice_items" value="<?php echo ($countcontainerdetail == 0) ? 1 : $countcontainerdetail; ?>">
            <div class="invoice_cotainer">

                <div class="col-md-12" id="container-0" style="margin-left: 25%">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('file_number') ? 'has-error' :'' }}">
                            <div class="col-md-12">
                                <?php echo Form::label('file_number', 'No. Dossier/ File No.', ['class' => 'control-label']); ?>
                            </div>
                            <div class="col-md-12">
                                <?php //echo Form::select('file_number', $dataFileNumber,$model->ups_id,['class'=>'form-control ffilenumber selectpicker','data-live-search' => 'true','placeholder' => 'Select ...']); 
                                ?>
                                <input class="form-control ffilenumber" name="file_number" id="file_number" value="<?php echo $model->ccpack_id; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6" style="pointer-events: none;opacity: 0.5">
                        <div class="form-group {{ $errors->has('currency') ? 'has-error' :'' }}">
                            <?php echo Form::label('currency', 'Currency', ['class' => 'col-md-12  control-label']); ?>
                            <div class="col-md-6">
                                <?php echo Form::select('currency', $currency, $model->currency, ['class' => 'form-control fcurrency selectpicker', 'placeholder' => 'Select ...']); ?>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" value="<?php echo $model->ccpack_id; ?>" id="ccpack_id" name="ccpack_id">
                    <input type="hidden" value="" id="limit_exceed" name="limit_exceed">
                </div>

                <div class="col-md-12" style="margin-top: 20px">
                    <div class="sec1" style="float: left;width: 100%">
                        <div class="d1 col-md-2">
                            {{ Form::image('images/invoice_logo.png', 'alt text', array('class' => 'css-class')) }}
                        </div>
                        <div class="d2 col-md-8">
                            <h3 style="text-align: center;font-weight: bold;font-style: italic;margin-top: 0px;">Chatelain Cargo Services S.A</h3>

                            <span style="width: 100%;float: left;text-align: center;font-size: 10px">Aeroport International de Port-au-Prince, P.O.Box 1056 Port-au-Prince, Haiti</span>
                            <span style="width: 100%;float: left;text-align: center;font-size: 10px">Tel: (509) 250-1652 a 250-1656, Fax: (509) 250-3898(P-A-P)</span>
                            <span style="width: 100%;float: left;text-align: center;font-size: 10px">Fax: (1-305) 436-3793(U.S.A)</span>
                            <span style="width: 100%;float: left;text-align: center;font-size: 10px">Email: pvc@chatelaincargo.com</span>

                        </div>
                        <div class="d3 col-md-2" style="font-weight: bold;text-align: right;margin-top: 8%">INVOICE</div>
                    </div>
                    <div class="sec2" style="float: left;width: 100%;margin-top: 20px">
                        <div class="col-md-8 row">
                            <div class="col-md-12">
                                <div class="col-md-2 row">
                                    <span>Billing party: </span>
                                </div>
                                <div class="col-md-6">
                                    <?php echo Form::select('bill_to', $allUsers, $model->bill_to, ['class' => 'form-control selectpicker invfield hbill_to invfieldtbl invfieldtblbillto', 'data-live-search' => 'true', 'id' => 'bill_to', 'placeholder' => 'Select ...']); ?>
                                </div>
                                <?php if ($permissionBillingPartyAdd) { ?>
                                    <div class="col-md-3" style="text-align: left;">
                                        <button data-module='Billing Party' style="float: left;" id="addNewItems" value="<?php echo url('items/addnewitem', ['client']) ?>" type="button" class="addnewitems">Add Billing Party</button>
                                    </div>
                                <?php } ?>
                                <div class="col-md-9 balance-div" style="display: none;text-align: center;">
                                    <span><b>Available Credit : </b> </span><span class="cash_credit_account_balance"></span>
                                </div>

                            </div>
                            <div class="col-md-12" style="margin-top: 15px;">
                                <div class="col-md-6 row">
                                    <span style="float: left;width: 20%">Email: </span>
                                    <div style="float: left;width: 80%">
                                        <?php echo Form::text('email', $model->email, ['class' => 'form-control invfield hemail', 'id' => 'email']); ?>
                                    </div>
                                </div>
                                <div class="col-md-6 row">
                                    <span style="float: left;width: 20%;padding-left: 10px;">Tel: </span>
                                    <div style="float: left;width: 80%">
                                        <?php echo Form::text('telephone', $model->telephone, ['class' => 'form-control invfield htelephone', 'id' => 'telephone']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" style="float: right;">
                            <table style="border: 1px solid #ccc;">
                                <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;">
                                    <td style="border-right: 1px solid #ccc">Date</td>
                                    <td>No facture</td>
                                </tr>
                                <tr>
                                    <td style="border-right: 1px solid #ccc"><?php echo Form::text('date', $model->date, ['class' => 'form-control invfield hdate datepicker']); ?></td>
                                    <?php if (empty($model->flag_invoice)) { ?>
                                        <td style="pointer-events: none;opacity: 0.5"><?php echo Form::text('bill_no', $model->bill_no, ['class' => 'form-control invfield hbill_no']); ?></td>
                                    <?php } else { ?>
                                        <td style=""><?php echo Form::text('bill_no', $model->bill_no, ['class' => 'form-control invfield hbill_no hbill_no_old']); ?></td>
                                    <?php } ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="sec3" style="float: left;width: 100%;margin-top: 10px">
                        <div class="col-md-6">
                            <label style="border: 1px solid #ccc;width: 100%;text-align: center;border-bottom: none;margin-bottom: 0px !important;">Expediteur / Shipper</label>
                            <?php echo Form::textarea('shipper', $model->shipper, ['class' => 'form-control hshipper', 'rows' => '4']); ?>
                        </div>
                        <div class="col-md-6">
                            <label style="border: 1px solid #ccc;width: 100%;text-align: center;border-bottom: none;margin-bottom: 0px !important;">Consignataire / Consignee</label>
                            <?php echo Form::textarea('consignee_address', $model->consignee_address, ['class' => 'form-control  hconsignee_address', 'rows' => '4']); ?>
                        </div>
                    </div>
                    <div class="sec4" style="float: left;width: 100%;margin-top: 10px">
                        <div class="col-md-12">
                            <table style="border: 1px solid #ccc;width: 100%">
                                <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;font-weight: bold;">
                                    <td>No. Dossier/ File No</td>
                                    <td>AWB / BL No.</td>
                                    <td>Transporteur / Carrier</td>
                                    <td>Import / Export</td>
                                    <td>Poids / Weight</td>
                                </tr>
                                <tr>
                                    <td><?php echo Form::text('file_no', $model->file_no, ['class' => 'form-control invfield hfile_no']); ?></td>
                                    <td><?php echo Form::text('awb_no', $model->awb_no, ['class' => 'form-control invfield hawb_no']); ?></td>
                                    <td><?php echo Form::text('carrier', $model->carrier, ['class' => 'form-control invfield hcarrier']); ?></td>
                                    <td><?php echo Form::text('type_flag', $model->type_flag, ['class' => 'form-control invfield htype_flag']); ?></td>
                                    <td><?php echo Form::text('weight', $model->weight, ['class' => 'form-control invfield hweight']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="sec5" style="float: left;width: 100%;margin-top: 10px">
                        <div class="col-md-12">
                            <table class="invoicemoreexpenses" style="border: 1px solid #ccc;width: 100%">
                                <thead>
                                    <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;font-weight: bold;">
                                        <td style="border-right: 1px solid #ccc;width: 25%;">Items <button id="addNewItems" value="<?php echo url('items/addnewitem', ['billing-items']) ?>" type="button" class="addnewitems" data-module='Billing Item'>Add Billing Item</button></td>
                                        <td style="border-right: 1px solid #ccc;text-align: center;padding-left: 5px;width: 25%">Description</td>
                                        <td style="border-right: 1px solid #ccc">Qty</td>
                                        <td style="border-right: 1px solid #ccc">Unit Price</td>
                                        <td style="border-right: 1px solid #ccc">Total</td>
                                        <td style="width: 10%;">Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dataInvoiceDetails)) { ?>
                                        <tr id="tbtr-0" style="border-bottom: 1px solid #ccc;height: 39px;">
                                            <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                <?php echo Form::select('fees_name[0]', $dataBillingItems, '', ['class' => 'form-control invfield invfieldtbl firstddexpensetyle selectpicker feesnamefld', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => 'feesname-0', 'data-cid' => 0]); ?>
                                            </td>
                                            <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                <?php echo Form::text('fees_name_desc[0]', '', ['class' => 'form-control invfield invfieldtbl feesnamedescfld', 'id' => 'fees_name_desc-0', 'data-cid' => 0]); ?>
                                            </td>
                                            <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                <?php echo Form::text('quantity[0]', '', ['class' => 'form-control invfield invfieldtbl quantityfld', 'id' => 'quantity-0', 'data-cid' => 0, 'onkeypress' => 'return isNumber(event)']); ?>
                                            </td>
                                            <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                <?php echo Form::text('unit_price[0]', '', ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => 'unit_price-0', 'data-cid' => 0, 'onkeypress' => 'return isNumber(event)']); ?>
                                            </td>
                                            <td style="padding-left: 5px;border-right: 1px solid #ccc;">
                                                <?php echo Form::text('total_of_items[0]', '', ['class' => 'form-control invfield invfieldtbl totalfld', 'id' => 'total_of_items-0', 'readonly', 'style' => 'background:none']); ?>
                                            </td>
                                            <td style="text-align: center;"><a href="javascript:void(0)" class='btn btn-success btn-xs addmoreexpense'>+</a></td>
                                        </tr>
                                        <?php } else {
                                        $i = 0;
                                        foreach ($dataInvoiceDetails as $k => $v) {    ?>
                                            <tr id="tbtr-<?php echo $i; ?>" style="border-bottom: 1px solid #ccc;height: 39px;">
                                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                    <?php echo Form::select("fees_name[$i]", $dataBillingItems, $v->fees_name, ['class' => 'form-control invfield invfieldtbl firstddexpensetyle selectpicker feesnamefld', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => "feesname-$i", 'data-cid' => $i]); ?>
                                                </td>
                                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                    <?php echo Form::text("fees_name_desc[$i]", $v->fees_name_desc, ['class' => 'form-control invfield invfieldtbl feesnamedescfld', 'id' => "fees_name_desc-$i", 'data-cid' => $id]); ?>
                                                </td>
                                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                    <?php echo Form::text("quantity[$i]", $v->quantity, ['class' => 'form-control invfield invfieldtbl quantityfld', 'id' => "quantity-$i", 'data-cid' => "$i"]); ?>
                                                </td>
                                                <td style="border-right: 1px solid #ccc;padding-left: 5px">
                                                    <?php echo Form::text("unit_price[$i]", $v->unit_price, ['class' => 'form-control invfield invfieldtbl unitpricefld', 'id' => "unit_price-$i", 'data-cid' => $i]); ?>
                                                </td>
                                                <td style="padding-left: 5px;border-right: 1px solid #ccc;">
                                                    <?php echo Form::text("total_of_items[$i]", $v->total_of_items, ['class' => 'form-control invfield invfieldtbl totalfld', 'id' => "total_of_items-$i", 'readonly', 'style' => 'background:none']); ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <a href="javascript:void(0)" class='btn btn-success btn-xs addmoreexpense'>+</a>
                                                    <?php if ($i != 0) { ?>
                                                        <a style='' href='javascript:void(0)' class='btn btn-danger btn-xs removeexpense' id=<?php echo $i; ?>>-</a>
                                                    <?php } ?>

                                                </td>
                                            </tr>
                                    <?php $i++;
                                        }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="sec6" style="float: left;width: 100%;margin-top: 10px">
                        <div class="col-md-4">
                            <?php echo Form::label('memo', 'Memo', ['class' => 'control-label']); ?>
                            <?php echo Form::text('memo', $model->memo, ['class' => 'form-control hmemo']); ?>
                        </div>
                        <div class="col-md-8" style="padding-right: 62px;">
                            <div style="float: left;width: 100%">
                                <span style="float:left;width: 40%;text-align: right;padding-right: 30px;padding-top: 10px;">Sous Total</span>
                                <span style="float:left;width: 50%"><?php echo Form::text('sub_total', $model->sub_total, ['class' => 'form-control invfield hsub_total', 'style' => 'text-align:right;background: #ececec;', 'readonly']); ?></span>
                                <span class="invfield currency_code" style="float: left;width: 10%;margin-top: 0px;height: 34px;padding-top: 7px;background: #ececec;"></span>
                            </div>
                            <div style="float: left;width: 100%">
                                <span style="float:left;width: 40%;text-align: right;padding-right: 30px;padding-top: 10px;">TCA</span>
                                <span style="float:left;width: 50%"><?php echo Form::text('tca', $model->tca, ['class' => 'form-control invfield htca', 'style' => 'text-align:right;background: #ececec;', 'readonly']); ?></span>
                                <span class="invfield currency_code" style="float: left;width: 10%;margin-top: 0px;height: 34px;padding-top: 7px;background: #ececec;"></span>
                            </div>
                            <div style="float: left;width: 100%">
                                <span style="float:left;width: 40%;text-align: right;padding-right: 30px;padding-top: 10px;">Total</span>
                                <span style="float:left;width: 50%"><?php echo Form::text('total', $model->total, ['class' => 'form-control invfield htotal', 'style' => 'text-align:right;background: #ececec;', 'readonly']); ?></span>
                                <span class="invfield currency_code" style="float: left;width: 10%;margin-top: 0px;height: 34px;padding-top: 7px;background: #ececec;"></span>
                            </div>
                            <div style="float: left;width: 100%">
                                <span style="float:left;width: 40%;text-align: right;padding-right: 30px;padding-top: 10px;">Paiements / Credits</span>
                                <span style="float:left;width: 50%"><?php echo Form::text('credits', $model->credits, ['class' => 'form-control invfield hcredits', 'style' => 'text-align:right;background: #ececec;', 'id' => 'credit','readonly'=>true, 'onkeypress' => 'return isNumber(event)']); ?></span>
                                <span class="invfield currency_code" style="float: left;width: 10%;margin-top: 0px;height: 34px;padding-top: 7px;background: #ececec;"></span>
                            </div>
                            <div style="float: left;width: 100%">
                                <span style="float:left;width: 40%;text-align: right;padding-right: 30px;padding-top: 10px;">Solde du</span>
                                <span style="float:left;width: 50%"><?php echo Form::text('balance_of', $model->balance_of, ['class' => 'form-control invfield hbalance_of', 'id' => 'balance_of', 'style' => 'text-align:right;background: #ececec;', 'readonly']); ?></span>
                                <span class="invfield currency_code" style="float: left;width: 10%;margin-top: 0px;height: 34px;padding-top: 7px;background: #ececec;"></span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-12 btm-sub">
                <?php if ($model->id && $permissionCcpackInvoicesDelete) { ?>
                    {{-- <a class="btn btn-danger" onclick="return confirm('Are you sure to delete?')" href="{{ route('deleteccpackinvoicefromedit',[$model->id]) }}">Delete</a> --}}
                <?php } ?>
                <?php if ($model->id && $permissionCcpackInvoicesCopy) { ?>
                    <a class="btn btn-success" href="{{ route('copyccpackinvoice',[$model->id]) }}">Copy Invoice</a>
                <?php } ?>

                <button type="submit" class="btn btn-success">Save</button>
                <input type="hidden" class="checkClickedOrnot" value="" />
                <input type="hidden" class="saveandprintinupdate" name="saveandprintinupdate" value="<?php echo $model->id ? '1' : '0' ?>" />
                <button type="submit" id="CreateButtonSavePrint" class="btn btn-success btn-prime white btn-flat">Save & Print</button>
                <?php if ($flagFromWhere == 'flagFromView') {
                    $cancleRoute = route('viewdetailsccpack', [$model->ccpack_id]);
                } else if ($model->flag_invoice == 'old') {
                    $cancleRoute = route('oldinvoices');
                } else {
                    $cancleRoute = route('ccpackinvoices');
                }
                ?>
                <a class="btn btn-danger" href="{{$cancleRoute}}" title="">Cancel</a>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
<div id="modalAddNewItems" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title modal-title-block text-center primecolor">Add New</h3>
            </div>
            <div class="modal-body" id="modalContentAddNewItems" style="overflow: hidden;">
            </div>
        </div>

    </div>
</div>
@endsection


@section('page_level_js')
<script type="text/javascript">
    $(function() {
        $('.unitpricefld,.quantityfld,.hcredits').blur(function() {
                $(this).formatCurrency({
                    negativeFormat: '-%s%n',
                    roundToDecimalPlace: 2,
                    symbol: ''
                });
            })
            .keyup(function(e) {
                var e = window.event || e;
                var keyUnicode = e.charCode || e.keyCode;
                if (e !== undefined) {
                    switch (keyUnicode) {
                        case 16:
                            break; // Shift
                        case 17:
                            break; // Ctrl
                        case 18:
                            break; // Alt
                        case 27:
                            this.value = '';
                            break; // Esc: clear entry
                        case 35:
                            break; // End
                        case 36:
                            break; // Home
                        case 37:
                            break; // cursor left
                        case 38:
                            break; // cursor up
                        case 39:
                            break; // cursor right
                        case 40:
                            break; // cursor down
                        case 78:
                            break; // N (Opera 9.63+ maps the "." from the number key section to the "N" key too!) (See: http://unixpapa.com/js/key.html search for ". Del")
                        case 110:
                            break; // . number block (Opera 9.63+ maps the "." from the number block to the "N" key (78) !!!)
                        case 190:
                            break; // .
                        default:
                            $(this).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: -1,
                                eventOnDecimalsEntered: true,
                                symbol: ''
                            });
                    }
                }
            })
    });

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46 && charCode != 45) {
            return false;
        }
        return true;
    }
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        autoclose: true
    });
    $('select').change(function() {
        if ($(this).val() != "") {
            $(this).valid();
        }
    });
    var tcaApplicable = 0;
    <?php if (isset($id)) { ?>

        $('.unitpricefld').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });
        $('.totalfld').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });
        $('.hsub_total').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });
        $('.htca').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });
        $('.htotal').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });
        $('.hcredits').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });
        $('.hbalance_of').formatCurrency({
            negativeFormat: '-%s%n',
            roundToDecimalPlace: 2,
            symbol: ''
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        clientId = $('#bill_to').val();
        var urlztn = '<?php echo url("clients/getclientdata"); ?>';
        $.ajax({
            url: urlztn,
            dataType: "json",
            async: false,
            type: 'POST',
            data: {
                'clientId': clientId
            },
            success: function(data) {
                if (data.flag_prod_tax_type == 1)
                    tcaApplicable = 1;
                else
                    tcaApplicable = 0;

                if (data.cash_credit == 'Credit') {
                    $('.balance-div').show();
                    var blnc = parseInt(data.available_balance).toFixed(2);
                    $('.cash_credit_account_balance').html(blnc).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                } else {
                    $('.balance-div').hide();
                }
            }
        })
    <?php } else { ?>
        tcaApplicable = 0;
    <?php } ?>
    $(document).ready(function() {
        /* $('#file_number').inputpicker({

            data: <?php //echo  $NdataFileNumber; ?>,

            fields: [{
                    name: 'file_number',
                    text: 'File Number'
                },
                {
                    name: 'consignee',
                    text: 'Consignee'
                },
                {
                    name: 'shipper',
                    text: 'Shipper'
                },
                {
                    name: 'awb_number',
                    text: 'AWB Number'
                }
            ],
            autoOpen: true,
            headShow: true,
            fieldText: 'file_number',
            fieldValue: 'value',
            filterOpen: true,
        }); */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var urlztnn2 = '<?php echo url("ccpackinvoice/getccpackinvoiceforinputpicker"); ?>';
        urlztnn2 += '?ccpackId=' + '<?php echo $ccpackId ?>';
        $('#file_number').inputpicker({

            //data: allInvoices,
            url: urlztnn2,
            //fields: ['file_number', 'consignee', 'shipper', 'awb_number'],
            fields: [{
                    name: 'file_number',
                    text: 'File Number'
                },
                {
                    name: 'consignee',
                    text: 'Consignee'
                },
                {
                    name: 'shipper',
                    text: 'Shipper'
                },
                {
                    name: 'awb_number',
                    text: 'AWB Number'
                }
            ],
            fieldText: 'file_number',
            fieldValue: 'value',
            pagination: true,
            pageMode: '',
            pageField: 'p',
            pageLimitField: 'per_page',
            limit: 10,
            pageCurrent: 1,
            autoOpen: true,
            headShow: true,
            filterOpen: true,
        });

        var subTotal = 0;


        <?php if (isset($id)) { ?>
            //$('.hfile_no').val($("#file_number option:selected").html());
        <?php } ?>

        <?php if (isset($model->ccpack_id) && !isset($id)) { ?>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var urlzt = '<?php echo url("ccpackinvoice/getccpackdetailforinvoice"); ?>';
            $.ajax({
                url: urlzt,
                dataType: "json",
                type: 'POST',
                data: {
                    'ccpackId': '<?php echo $model->ccpack_id; ?>'
                },
                success: function(data) {
                    $('.hbill_to').val(data.consignee);
                    $('#bill_to').trigger('change');
                    $('.hshipper').val(data.shipper);
                    $('.hconsignee_address').val(data.consignee);
                    $('.hfile_no').val(data.file_number);
                    $('.hawb_no').val(data.awb_number);
                    if (data.ccpack_operation_type == 1)
                        $('.htype_flag').val('IMPORT');
                    else
                        $('.htype_flag').val('EXPORT');
                    $('.hweight').val(data.weight);
                    //$('.selectpicker').selectpicker('refresh');
                    $('#loading').hide();
                }
            });
        <?php } ?>

        $('#file_number').change(function() {
            $('#loading').show();
            if ($(this).val() != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('#ccpack_id').val($(this).val());
                var urlzt = '<?php echo url("ccpackinvoice/getccpackdetailforinvoice"); ?>';
                $.ajax({
                    url: urlzt,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'ccpackId': $(this).val()
                    },
                    success: function(data) {
                        $('.hbill_to').val(data.consignee);
                        $('#bill_to').trigger('change');
                        $('.hshipper').val(data.shipper);
                        $('.hconsignee_address').val(data.consignee);
                        $('.hfile_no').val(data.file_number);
                        $('.hawb_no').val(data.awb_number);
                        if (data.ccpack_operation_type == 1)
                            $('.htype_flag').val('IMPORT');
                        else
                            $('.htype_flag').val('EXPORT');
                        $('.hweight').val(data.weight);
                        $('.selectpicker').selectpicker('refresh');
                        $('#loading').hide();
                    }
                });
            } else {
                $('.hshipper').val('');
                $('.hconsignee_address').val('');
                $('.hfile_no').val('');
                $('.hawb_no').val('');
                $('.htype_flag').val('');
                $('.hweight').val('');
                $('#loading').hide();
            }
        })

        countcontainerdetail = 0;
        if ('<?php echo $countcontainerdetail; ?>' != 0) {
            countcontainerdetail = <?php echo $countcontainerdetail - 1; ?>;
        }
        $(document).on("click", ".addmoreexpense", function(e) {
            $('#loading').show();
            $('.count_invoice_items').val(parseInt($('.count_invoice_items').val()) + 1);
            countcontainerdetail = countcontainerdetail + 1;
            if (countcontainerdetail == 0) {
                countcontainerdetail = 1;
            }
            e.preventDefault();
            var str = '<tr id="tbtr-' + countcontainerdetail + '" style="border-bottom: 1px solid #ccc;height: 39px;"><td style="border-right: 1px solid #ccc;padding-left: 5px"><select class="form-control invfield invfieldtbl selectpicker feesnamefld" data-cid="' + countcontainerdetail + '" id="feesname-' + countcontainerdetail + '" data-live-search="true" name="fees_name[' + countcontainerdetail + ']" tabindex="-98">' + $('select.firstddexpensetyle').html() + '</select></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input class="form-control invfield invfieldtbl feesnamedescfld" name="fees_name_desc[' + countcontainerdetail + ']" type="text" value="" data-cid="' + countcontainerdetail + '" id="fees_name_desc-' + countcontainerdetail + '"></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input onkeypress="return isNumber(event)" class="form-control invfield invfieldtbl quantityfld" name="quantity[' + countcontainerdetail + ']" type="text" value="" data-cid="' + countcontainerdetail + '" id="quantity-' + countcontainerdetail + '"></td><td style="border-right: 1px solid #ccc;padding-left: 5px"><input onkeypress="return isNumber(event)" class="form-control invfield invfieldtbl unitpricefld" name="unit_price[' + countcontainerdetail + ']" type="text" value="" data-cid="' + countcontainerdetail + '" id="unit_price-' + countcontainerdetail + '"></td><td style="padding-left: 5px;border-right: 1px solid #ccc;"><input class="form-control invfield invfieldtbl totalfld" name="total_of_items[' + countcontainerdetail + ']" id="total_of_items-' + countcontainerdetail + '" type="text" value="" readonly style="background:none"></td><td style="text-align: center;"><a href="javascript:void(0)" class="btn btn-success btn-xs addmoreexpense" style="margin-right:5px">+</a><a style="" href="javascript:void(0)" class="btn btn-danger btn-xs removeexpense" id="' + countcontainerdetail + '">-</a></td></tr>';
            $('table.invoicemoreexpenses tbody').append(str);
            $('.selectpicker').selectpicker();
            $('#loading').hide();

            $('.unitpricefld,.quantityfld').blur(function() {
                    $(this).formatCurrency({
                        negativeFormat: '-%s%n',
                        roundToDecimalPlace: 2,
                        symbol: ''
                    });
                })
                .keyup(function(e) {
                    var e = window.event || e;
                    var keyUnicode = e.charCode || e.keyCode;
                    if (e !== undefined) {
                        switch (keyUnicode) {
                            case 16:
                                break; // Shift
                            case 17:
                                break; // Ctrl
                            case 18:
                                break; // Alt
                            case 27:
                                this.value = '';
                                break; // Esc: clear entry
                            case 35:
                                break; // End
                            case 36:
                                break; // Home
                            case 37:
                                break; // cursor left
                            case 38:
                                break; // cursor up
                            case 39:
                                break; // cursor right
                            case 40:
                                break; // cursor down
                            case 78:
                                break; // N (Opera 9.63+ maps the "." from the number key section to the "N" key too!) (See: http://unixpapa.com/js/key.html search for ". Del")
                            case 110:
                                break; // . number block (Opera 9.63+ maps the "." from the number block to the "N" key (78) !!!)
                            case 190:
                                break; // .
                            default:
                                $(this).formatCurrency({
                                    negativeFormat: '-%s%n',
                                    roundToDecimalPlace: -1,
                                    eventOnDecimalsEntered: true,
                                    symbol: ''
                                });
                        }
                    }
                })
        });
        $(document).on("click", ".removeexpense", function(e) {
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 1000);
            $('.count_invoice_items').val(parseInt($('.count_invoice_items').val()) - 1);
            e.preventDefault();
            var id = $(this).attr('id');
            $("table.invoicemoreexpenses tbody tr#tbtr-" + id).remove();

            $('.hsub_total').val('0.00');
            $('.htca').val('0.00');
            $('.htotal').val('0.00');
            $('.hcredits').val('0.00');
            $('.hbalance_of').val('0.00');

            // sub total
            subTotal = 0;
            $('.totalfld').each(function(k, v) {
                subTotal = parseFloat(subTotal) + parseFloat($(this).val().replace(/\,/g, ''));
            })
            $('.hsub_total').val(subTotal).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });

            // TBA / TCA
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var tca = 0;
            $('.htca').val(tca.toFixed(2));
            $('select.feesnamefld').each(function(k, v) {
                //var cid =  $(this).parents('td').next().next('td').find('.unitpricefld').data('cid');
                var cid = $(this).data('cid');
                var billingId = $(this).val();
                var urlztn = '<?php echo url("billingitem/getbillinglistdata"); ?>';
                $.ajax({
                    url: urlztn,
                    async: false,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'billingId': billingId
                    },
                    success: function(data) {
                        console.log(data);
                        ttl = $('#total_of_items-' + cid).val().replace(/\,/g, '');
                        if (tcaApplicable == 1 && data.percentageType == 1) {
                            tca = tca + (ttl * data.percentage / 100);
                            $('.htca').val(tca).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: 2,
                                symbol: ''
                            });
                        }

                        vTotal = parseFloat($('.hsub_total').val().replace(/\,/g, '')) + parseFloat($('.htca').val().replace(/\,/g, ''));
                        $('.htotal').val(vTotal).formatCurrency({
                            negativeFormat: '-%s%n',
                            roundToDecimalPlace: 2,
                            symbol: ''
                        });;
                        $('#leftoading').hide();

                    }
                });
            });

            var tVal = $('#credit').val().replace(/\,/g, '');
            var httl = $('.htotal').val().replace(/\,/g, '');
            if (httl == '')
                httl = parseFloat(0.00);

            if (tVal == '')
                var balanceOf = parseFloat(httl) - parseFloat(0.00);
            else
                var balanceOf = parseFloat(httl) - parseFloat(tVal);
            $('#balance_of').val(balanceOf).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        });

        //$('.unitpricefld').focusout(function(){
        $(document).on("focusout", ".unitpricefld", function(e) {
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 1000);
            var unitp = $(this).val().replace(/\,/g, '');
            var cid = $(this).data('cid');
            var qty = $('#quantity-' + cid).val().replace(/\,/g, '');
            $('#total_of_items-' + cid).val((qty * unitp)).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });

            // sub total
            subTotal = 0;
            $('.totalfld').each(function(k, v) {
                subTotal = parseFloat(subTotal) + parseFloat($(this).val().replace(/\,/g, ''));
            })
            $('.hsub_total').val(subTotal).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });

            // TBA / TCA
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var tca = 0;
            $('.htca').val(tca.toFixed(2));
            $('select.feesnamefld').each(function(k, v) {
                var billingId = $(this).val();
                if (billingId != "") {
                    var dcid = $(this).data('cid');
                    var urlztn = '<?php echo url("billingitem/getbillinglistdata"); ?>';
                    $.ajax({
                        url: urlztn,
                        dataType: "json",
                        async: false,
                        type: 'POST',
                        data: {
                            'billingId': billingId
                        },
                        success: function(data) {
                            console.log(tcaApplicable);
                            console.log(data);

                            ttl = $('#total_of_items-' + dcid).val().replace(/\,/g, '');
                            if (tcaApplicable == 1 && data.percentageType == 1) {
                                tca = tca + (ttl * data.percentage / 100);
                                $('.htca').val(tca).formatCurrency({
                                    negativeFormat: '-%s%n',
                                    roundToDecimalPlace: 2,
                                    symbol: ''
                                });
                            }

                            vTotal = parseFloat($('.hsub_total').val().replace(/\,/g, '')) + parseFloat($('.htca').val().replace(/\,/g, ''));
                            $('.htotal').val(vTotal).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: 2,
                                symbol: ''
                            });
                            $('#leftoading').hide();
                        }
                    });
                }
            });

            var tVal = $('#credit').val().replace(/\,/g, '');
            var httl = $('.htotal').val().replace(/\,/g, '');
            if (httl == '')
                httl = parseFloat(0.00);

            if (tVal == '')
                var balanceOf = parseFloat(httl) - parseFloat(0.00);
            else
                var balanceOf = parseFloat(httl) - parseFloat(tVal);
            $('#balance_of').val(balanceOf).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        })
        //$('.quantityfld').focusout(function(){
        $(document).on("focusout", ".quantityfld", function(e) {
            $('#loading').show();
            setTimeout(function() {
                $("#loading").hide();
            }, 1000);
            var qty = $(this).val().replace(/\,/g, '');
            var cid = $(this).data('cid');
            var unitp = $('#unit_price-' + cid).val().replace(/\,/g, '');
            $('#total_of_items-' + cid).val((qty * unitp)).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });

            // sub total
            subTotal = 0;
            $('.totalfld').each(function(k, v) {
                subTotal = parseFloat(subTotal) + parseFloat($(this).val().replace(/\,/g, ''));
            })
            $('.hsub_total').val(subTotal).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });

            // TBA / TCA
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var tca = 0;
            $('.htca').val(tca.toFixed(2));
            $('select.feesnamefld').each(function(k, v) {
                var billingId = $(this).val();
                if (billingId != "") {
                    var dcid = $(this).data('cid');
                    var urlztn = '<?php echo url("billingitem/getbillinglistdata"); ?>';
                    $.ajax({
                        url: urlztn,
                        dataType: "json",
                        async: false,
                        type: 'POST',
                        data: {
                            'billingId': billingId
                        },
                        success: function(data) {
                            console.log(tcaApplicable);
                            console.log(data);

                            ttl = $('#total_of_items-' + dcid).val().replace(/\,/g, '');
                            if (tcaApplicable == 1 && data.percentageType == 1) {
                                tca = tca + (ttl * data.percentage / 100);
                                $('.htca').val(tca).formatCurrency({
                                    negativeFormat: '-%s%n',
                                    roundToDecimalPlace: 2,
                                    symbol: ''
                                });
                            }

                            vTotal = parseFloat($('.hsub_total').val().replace(/\,/g, '')) + parseFloat($('.htca').val().replace(/\,/g, ''));
                            $('.htotal').val(vTotal).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: 2,
                                symbol: ''
                            });
                            $('#leftoading').hide();
                        }
                    });
                }
            });

            var tVal = $('#credit').val().replace(/\,/g, '');
            var httl = $('.htotal').val().replace(/\,/g, '');
            if (httl == '')
                httl = parseFloat(0.00);

            if (tVal == '')
                var balanceOf = parseFloat(httl) - parseFloat(0.00);
            else
                var balanceOf = parseFloat(httl) - parseFloat(tVal);
            $('#balance_of').val(balanceOf).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        })


        $(document).on("focusout", "#credit", function(e) {
            //$(this).val(parseFloat($(this).val()).toFixed(2));
        });
        $('#credit').keyup(function() {
            var tVal = $(this).val().replace(/\,/g, '')
            var httl = $('.htotal').val().replace(/\,/g, '');
            if (httl == '')
                httl = parseFloat(0.00);


            if (tVal == '') {
                $(this).val('0.00');
                var balanceOf = parseFloat(httl) - parseFloat(0.00);
            } else
                var balanceOf = parseFloat(httl) - parseFloat(tVal);
            $('#balance_of').val(balanceOf).formatCurrency({
                negativeFormat: '-%s%n',
                roundToDecimalPlace: 2,
                symbol: ''
            });
        })

        $('#createInvoiceForm').on('submit', function(event) {
            // event.preventDefault();
            /* $('.famount').each(function () {
                 $(this).rules("add",
                         {
                             required: true,
                         })
             });
             $('.fexpense_type').each(function () {
                 $(this).rules("add",
                         {
                             required: true,
                         })
             });*/






            $('.htelephone').each(function() {
                $(this).rules("add", {
                    required: true,
                    //number: true    
                    telephonecheck: true
                })
            });
            $('.hbill_to').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
            $('.hbill_no_old').each(function() {
                $(this).rules("add", {
                    required: true,
                    checkuniquebillnoforoldinvoice: true
                })
            });
            $('.fcurrency').each(function() {
                $(this).rules("add", {
                    required: true,
                })
            });
        });

        jQuery.validator.addMethod(
            "telephonecheck",
            function(phone_number, element) {
                return this.optional(element) || /^(?=.*[0-9])[- +()0-9]+$/.test(phone_number);
            },
            "Please enter a valid number."
        );

        $.validator.addMethod("checkuniquebillnoforoldinvoice",
            function(value, element) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var result = false;
                var urlz = '<?php echo url("oldinvoices/checkuniquebillnoforoldinvoice"); ?>';
                <?php if ($model->id) { ?>
                    var id = '<?php echo $model->id; ?>';
                <?php } else { ?>
                    var id = '';
                <?php } ?>

                $.ajax({
                    type: "POST",
                    async: false,
                    url: urlz,
                    data: {
                        value: value,
                        id: id
                    },
                    success: function(data) {
                        result = (data == 0) ? true : false;
                    }
                });
                // return true if username is exist in database
                return result;
            },
            "This Bill No is already taken! Try another."
        );

        $('#createInvoiceForm').validate({
            submitHandler: function(form) {
                var clientId = $('#bill_to').val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var goAhead = 1;
                <?php if (empty($model->id)) { ?>
                    if ($('.checkClickedOrnot').val() != '1') {
                        var bNo = $('.hbill_no').val();
                        var urlCheck = '<?php echo url("invoice/checkexistingbillno"); ?>';
                        $.ajax({
                            url: urlCheck,
                            async: false,
                            dataType: "json",
                            type: 'POST',
                            data: {
                                'billNo': bNo,
                                'flag': 'ccpack'
                            },
                            success: function(data) {
                                if (data.exist == 1) {
                                    if (confirm("Invoice with Bill No " + bNo + " already created. Do you want to continue with incremented number ?")) {
                                        //var bNoN = bNo.split("-")
                                        //$('.hbill_no').val(bNoN[0]+'-'+(parseInt(bNoN[1])+1));
                                        $('.hbill_no').val(data.billNo);
                                    } else {
                                        goAhead = 0;
                                        window.location.href = '<?php echo route("ccpackinvoices") ?>';
                                        event.preventDefault();
                                        return false;
                                    }
                                }
                            }
                        })
                    }
                <?php } ?>
                if (goAhead == 0) {
                    return false;
                }
                var submitButtonName = $(this.submitButton).attr("id");
                if ($(this.submitButton).attr("id") == 'CreateButtonSavePrint')
                    $('.flagBtn').val('saveprint');
                else
                    $('.flagBtn').val('');

                if (submitButtonName == 'CreateButtonSavePrint') {
                    $('.checkClickedOrnot').val('1');
                    $('#loading').show();
                    var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                    $.ajax({
                        url: urlztnn,
                        dataType: "json",
                        async: false,
                        type: 'POST',
                        data: {
                            'clientId': clientId
                        },
                        success: function(data) {
                            console.log(data);
                            var finalTotal = $('.hbalance_of').val();
                            var clientLimit = data.available_balance;

                            var createInvoiceForm = $("#createInvoiceForm");
                            var formData = createInvoiceForm.serialize();
                            if (data.cash_credit == 'Credit' && parseFloat(finalTotal) > parseFloat(clientLimit)) {
                                $('#limit_exceed').val('yes');
                                if (confirm("Total amount is exceeding client limit, Do you want to proceed?")) {
                                    //form.submit();
                                    var urlznt = '<?php echo url("ccpackinvoice/storeccpackinvoiceandprint"); ?>';

                                    $.ajax({
                                        url: urlznt,
                                        type: 'POST',
                                        data: formData,
                                        success: function(data) {
                                            console.log(data);
                                            Lobibox.notify('info', {
                                                size: 'mini',
                                                delay: 2000,
                                                rounded: true,
                                                delayIndicator: false,
                                                msg: 'Invoice has been created successfully.'
                                            });
                                            $('#loading').hide();
                                            window.open(data, '_blank');

                                            //newWindow.print();
                                            <?php if (empty($model->flag_invoice)) { ?>
                                                window.location.href = '<?php echo route("ccpackinvoices") ?>';
                                            <?php } else { ?>
                                                window.location.href = '<?php echo route("oldinvoices") ?>';
                                            <?php } ?>

                                            // window.print(data, '_blank');
                                            // window.loaction(data);
                                        },
                                    });
                                } else {
                                    $('#loading').hide();
                                }
                            } else {
                                $('#limit_exceed').val('no');
                                //form.submit();
                                var urlznt = '<?php echo url("ccpackinvoice/storeccpackinvoiceandprint"); ?>';

                                $.ajax({
                                    url: urlznt,
                                    type: 'POST',
                                    data: formData,
                                    success: function(data) {
                                        console.log(data);
                                        Lobibox.notify('info', {
                                            size: 'mini',
                                            delay: 2000,
                                            rounded: true,
                                            delayIndicator: false,
                                            msg: 'Invoice has been created successfully.'
                                        });
                                        $('#loading').hide();
                                        window.open(data, '_blank');
                                        //newWindow.print();
                                        <?php if (empty($model->flag_invoice)) { ?>
                                            window.location.href = '<?php echo route("ccpackinvoices") ?>';
                                        <?php } else { ?>
                                            window.location.href = '<?php echo route("oldinvoices") ?>';
                                        <?php } ?>

                                        // window.print(data,'_blank' );
                                        //window.loaction(data);
                                    },
                                });
                            }
                        }
                    });
                } else {
                    $('#loading').show();
                    var urlztnn = '<?php echo url("clients/getclientdata"); ?>';
                    $.ajax({
                        url: urlztnn,
                        dataType: "json",
                        async: false,
                        type: 'POST',
                        data: {
                            'clientId': clientId
                        },
                        success: function(data) {
                            var finalTotal = $('.hbalance_of').val();
                            var clientLimit = data.available_balance;
                            if (data.cash_credit == 'Credit' && parseFloat(finalTotal) > parseFloat(clientLimit)) {
                                $('#limit_exceed').val('yes');
                                if (confirm("Total amount is exceeding client limit, Do you want to proceed?")) {
                                    form.submit();
                                } else {
                                    $('#loading').hide();
                                }
                            } else {
                                $('#limit_exceed').val('no');
                                form.submit();
                            }
                        }
                    });
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "file_number") {
                    var pos = $('.ffilenumber button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "bill_to") {
                    var pos = $('.hbill_to button.dropdown-toggle');
                    error.insertAfter(pos);
                } else if (element.attr("name") == "currency") {
                    var pos = $('.fcurrency button.dropdown-toggle');
                    error.insertAfter(pos);
                } else {
                    error.insertAfter(element);
                }
            }
        });

        $(document).on("change", "select.feesnamefld", function(e) {
            $('#loading').show();
            var id = $(this).data('cid');
            var billingId = $('#feesname-' + id).val();
            if (billingId != '') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var urlzt = '<?php echo url("billingitem/getbillingdata"); ?>';
                $.ajax({
                    url: urlzt,
                    dataType: "json",
                    type: 'POST',
                    data: {
                        'billingId': billingId
                    },
                    success: function(data) {
                        $('#loading').hide();
                        $('#fees_name_desc-' + id).val(data.billingName);


                        if ($('#quantity-' + id).val() != '' && $('#unit_price-' + id).val() != '' && $('#total_of_items-' + id).val() != '') {
                            // sub total
                            subTotal = 0;
                            $('.totalfld').each(function(k, v) {
                                subTotal = parseFloat(subTotal) + parseFloat($(this).val().replace(/\,/g, ''));
                            })
                            $('.hsub_total').val(subTotal).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: 2,
                                symbol: ''
                            });

                            // TBA / TCA
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                            var tca = 0;
                            $('.htca').val(tca.toFixed(2));
                            $('select.feesnamefld').each(function(k, v) {
                                var billingId = $(this).val();
                                if (billingId != "") {
                                    var dcid = $(this).data('cid');
                                    var urlztn = '<?php echo url("billingitem/getbillinglistdata"); ?>';
                                    $.ajax({
                                        url: urlztn,
                                        dataType: "json",
                                        async: false,
                                        type: 'POST',
                                        data: {
                                            'billingId': billingId
                                        },
                                        success: function(data) {
                                            console.log(data);
                                            ttl = $('#total_of_items-' + dcid).val().replace(/\,/g, '');
                                            if (tcaApplicable == 1 && data.percentageType == 1) {
                                                tca = tca + (ttl * data.percentage / 100);
                                                $('.htca').val(tca).formatCurrency({
                                                    negativeFormat: '-%s%n',
                                                    roundToDecimalPlace: 2,
                                                    symbol: ''
                                                });
                                            }


                                            vTotal = parseFloat($('.hsub_total').val().replace(/\,/g, '')) + parseFloat($('.htca').val().replace(/\,/g, ''));
                                            $('.htotal').val(vTotal).formatCurrency({
                                                negativeFormat: '-%s%n',
                                                roundToDecimalPlace: 2,
                                                symbol: ''
                                            });
                                            $('#leftoading').hide();
                                        }
                                    });
                                }
                            });

                            var tVal = $('#credit').val().replace(/\,/g, '');
                            var httl = $('.htotal').val().replace(/\,/g, '');
                            if (httl == '')
                                httl = parseFloat(0.00);

                            if (tVal == '')
                                var balanceOf = parseFloat(httl) - parseFloat(0.00);
                            else
                                var balanceOf = parseFloat(httl) - parseFloat(tVal);
                            $('#balance_of').val(balanceOf).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: 2,
                                symbol: ''
                            });
                        }

                    }
                });
            } else {
                $('#loading').hide();
                $('#fees_name_desc-' + id).val('');
            }
        });

        $(document).on("change", "#bill_to", function(e) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#loading').show();
            var clientId = $(this).val();
            if (clientId != '') {
                var dcid = $(this).data('cid');
                var urlztn = '<?php echo url("clients/getclientdata"); ?>';
                $.ajax({
                    url: urlztn,
                    dataType: "json",
                    async: false,
                    type: 'POST',
                    data: {
                        'clientId': clientId
                    },
                    success: function(data) {
                        $('#loading').hide();
                        $('#currency').val(data.currency);
                        $('.currency_code').text($("#currency option:selected").html());
                        $('#email').val(data.email);
                        $('#telephone').val(data.phone_number);
                        if (data.flag_prod_tax_type == 1)
                            tcaApplicable = 1;
                        else
                            tcaApplicable = 0;

                        if (data.cash_credit == 'Credit') {
                            $('.balance-div').show();
                            var blnc = parseInt(data.available_balance).toFixed(2);
                            $('.cash_credit_account_balance').html(blnc).formatCurrency({
                                negativeFormat: '-%s%n',
                                roundToDecimalPlace: 2,
                                symbol: ''
                            });
                        } else {
                            $('.balance-div').hide();
                        }
                        $('.selectpicker').selectpicker('refresh');
                    }
                });
            } else {
                $('#loading').hide();
                $('#email').val('');
                $('#telephone').val('');
            }
        });




        <?php if ($model->id) { ?>
            $('.currency_code').text($("#currency option:selected").html());
        <?php } ?>
        $('#currency').change(function() {
            $('#loading').show();
            if ($(this).val() != "") {
                $('.currency_code').text($("#currency option:selected").html());
                $('#loading').hide();
            } else {
                $('.currency_code').text("");
                $('#loading').hide();
            }
        })


    })
</script>
@stop