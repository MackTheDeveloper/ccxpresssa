<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;">Collection Detail</div>
<div class="notes" style="padding: 10px;float: left;width: 100%;position: relative;display:block">
    <div style="float: left;<?php echo (!empty($currencySingle) && $currencySingle->code == 'HTG') ? 'display:block' : 'display:none' ?>" class="col-md-3">
        <b>HTG : </b><span style="margin-left: 2%" class="totalhtg">{{number_format($totalOfCurrency[3],2)}}</span>
    </div>
    <div style="float: left;<?php echo (!empty($currencySingle) && $currencySingle->code == 'USD') ? 'display:block' : 'display:none' ?>" class="col-md-3">
        <b>USD : </b><span style="margin-left: 2%" class="totalusd">{{number_format($totalOfCurrency[1],2)}}</span>
    </div>

    <div style="float: left;display:none" class="col-md-2">
        <b>Total in HTG : </b><span style="margin-left: 2%" class="finaltotal">
            {{number_format($totalOfCurrency['totalInHtg'],2)}}
        </span>
    </div>
</div>
<div class="detail-container">

    <div id="filterExpenceData">
        <table class="display nowrap display" style="width:100%" id="example">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>File Number</th>
                    <th>Receipt No.</th>
                    <th>Consignee</th>
                    <th>Currency</th>
                    <th>Original Amount</th>
                    <th>Payable Amount</th>
                    <th>Payment Currency</th>
                    <th>Paid Amount</th>
                    <th>Payment Via</th>
                    <th>Payment Description</th>
                    <th>Payment Date & Time</th>
                    <th>Received By</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalPayment = 0;
                $arr = []; ?>
                @foreach($paymentReceivedByCashierNew as $paymentDetail)
                {{-- @if(!in_array($paymentDetail->invoice_id,$arr)) --}}
                <?php
                $redLink = route('viewInvoiceDetailsWithCollection', [$paymentDetail->invoice_id]);
                ?>
                <tr data-editlink="{{ $redLink }}" id="<?php echo $paymentDetail->invoice_id; ?>" class="edit-row">
                    <td>{{$paymentDetail->invoice_number}}</td>
                    <td>{{$paymentDetail->file_number}}</td>
                    <td>{{$paymentDetail->receipt_number}}</td>
                    <td>{{$paymentDetail->consignee_address}}</td>
                    <td>
                        <?php $dataCurrency = App\Currency::getData($paymentDetail->invoiceCurrency);
                        echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
                    </td>
                    <td style="text-align:right">{{$paymentDetail->originalAmount}}</td>
                    <td style="text-align:right">{{$paymentDetail->amount}}</td>
                    <td>
                        <?php $currency = $paymentDetail->exchange_currency ?>
                        @if($currency != '')
                        <?php $dataCurrency = App\Currency::getData($paymentDetail->exchange_currency);
                        echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
                        @else
                        {{"-"}}
                        @endif
                    </td>
                    <td style="text-align:right">
                        @if($currency != '' && $paymentDetail->exchange_amount != '')
                        {{$paymentDetail->exchange_amount}}
                        @else
                        {{"-"}}
                        @endif
                    </td>
                    <td>{{$paymentDetail->payment_via}}</td>
                    <td>
                        @if($paymentDetail->payment_via_note != '')
                        {{$paymentDetail->payment_via_note}}
                        @else
                        {{"-"}}
                        @endif
                    </td>
                    <td>
                        <?php echo date("d-m-Y h:i:s", strtotime($paymentDetail->created_at)); ?>
                    </td>
                    <td>{{$paymentDetail->paymentReceivedBy}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>



<script type="text/javascript">
    $(document).ready(function() {
        var disbursementLink = $('#pdfDisbursementLink').attr("href");
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        $('#example').DataTable({
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            //"ordering": false,
            //"order": [[ 0, "asc" ]],
            "scrollX": true,
            "aaSorting": []
        });


    })
</script>