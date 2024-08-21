<?php use Illuminate\Support\Facades\DB;
      use App\Currency;
?>
    <table id="filterInvoiceInfo" class="display nowrap" style="width:100%">
            <thead>
            <tr>
                <th>Invoice Currency</th>
                <th>Invoice Number</th>
                <th>Original Amount</th>
                <th>File Number</th>
                <th>Client</th>
                <th>Paid Amount</th>
                <th>Payment Currency</th>
                <th>Payment Amount</th>
                <th>Payment Via</th>
                <th>Payment Description</th>
                <th>Payment Date & Time</th>
            </tr>
        </thead>
        <tbody>
        <?php $totalPayment = 0;$arr = [];?>
            @foreach($paymentReceivedByCashier as $paymentDetail)
            <tr>
                <td>
                    <?php $dataCurrency = Currency::getData($paymentDetail->invoiceCurrency); 
                          echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
                </td>
                <td>{{$paymentDetail->invoice_number}}</td>
                <td>{{$paymentDetail->originalAmount}}</td>
                <td>{{$paymentDetail->file_number}}</td>
                <td><?php $client = DB::table('clients')->where('id',$paymentDetail->client)->get();
                        foreach($client as $client)
                            echo $client->company_name;
                    ?>
                            
                </td>
                <td>{{$paymentDetail->amount}}</td>
                <td>
                    <?php $currency = $paymentDetail->exchange_currency ?>
                    @if($currency != '')
                        <?php $dataCurrency = Currency::getData($paymentDetail->exchange_currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?>
                    @else
                        {{"-"}}
                    @endif              
                </td>
                <td>
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
                    <?php echo date("d-m-Y h:i:s", strtotime($paymentDetail->created_at));?>
                </td>
            </tr>
            <?php array_push($arr,$paymentDetail->invoice_id)?>
            </tbody>
            
            
            @endforeach
        </table>

        <script type="text/javascript">
            $('#filterInvoiceInfo').DataTable(
            {
                'stateSave': true,
                "columnDefs": [ {
                    "targets": [-1],
                    "orderable": false
                    }],
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

        </script>

