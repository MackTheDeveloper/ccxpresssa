<div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;">Disbursement Detail
    <?php $totalPayment = 0;
    $arr = []; ?>
    @foreach($allExpenseDetails as $expancesDetailCount)
    {{-- @if(!in_array($expancesDetailCount->expense_id,$arr)) --}}
    <?php $totalPayment += $expancesDetailCount->amount; ?>
    <?php array_push($arr, $expancesDetailCount->expense_id) ?>
    {{-- @endif --}}
    @endforeach
</div>

<div class="notes" style="padding: 10px;float: left;width: 100%;">
    <div style="float: left;display:none" class="col-md-3">
        <b>Total Disbursement : </b><span style="margin-left: 2%" class="totaldisbursement">{{number_format($totalPayment,2)}}</span>
    </div>
    <div style="float: left;<?php echo (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'USD') ? 'display:block' : 'display:none' ?>" class="col-md-4 usddiv">
        <b><?php echo $cashBankSingle->name; ?> (USD) : </b><span style="margin-left: 2%" class="totaldisbursementUsd">{{number_format($allTotalExpenseOfUSDCount,2)}}</span>
    </div>
    <div style="float: left;<?php echo (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'HTG') ? 'display:block' : 'display:none' ?>" class="col-md-4 htgdiv">
        <b><?php echo $cashBankSingle->name; ?> (HTG) : </b><span style="margin-left: 2%" class="totaldisbursementHtg">{{number_format($allTotalExpenseOfHtgCount,2)}}</span>
    </div>
</div>
<div class="detail-container">

    <div id="filterExpenceData">
        <table class="display nowrap display" style="width:100%" id="example">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>File Number</th>
                    <th>Voucher Number</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Disbursed Note</th>
                    <th>Paid To</th>
                    <th>Disbursed By</th>
                </tr>
            </thead>
            <tbody>
                <?php $arr = [];
                $totalPayment = 0; ?>

                @foreach($allExpenseDetails as $expancesDetail)
                {{-- @if(!in_array($expancesDetail->expense_id,$arr)) --}}
                <?php $dataCashCredit = App\CashCredit::getCashCreditData($expancesDetail->c_credit_account) ?>
                <tr>
                    <td><?php echo  !empty($expancesDetail->disbursed_datetime) ? date("d-m-Y", strtotime($expancesDetail->disbursed_datetime)) : '-'; ?></td>
                    <td>{{$expancesDetail->file_number}}</td>
                    <td>{{$expancesDetail->voucher_number}}</td>
                    <td><?php $dataCurrency = App\Currency::getData($dataCashCredit->currency);
                        echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?></td>
                    <td style="text-align:right">{{number_format($expancesDetail->amount,2)}}</td>
                    <td>{{$expancesDetail->description}}</td>
                    <td>{{$expancesDetail->expense_request_status_note}}</td>
                    <td>
                        <?php
                        $paid_to = DB::table('vendors')->where('id', $expancesDetail->paid_to)->first();
                        echo !empty($paid_to) ? $paid_to->company_name : '-';
                        ?>
                    </td>
                    <td>{{$expancesDetail->name}}</td>
                </tr>
                <?php array_push($arr, $expancesDetail->expense_id) ?>

                {{-- @endif --}}
                @endforeach
            </tbody>
        </table>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
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
            "scrollX": true,
            "aaSorting": []
        });
    })
</script>