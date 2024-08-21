<?php use Illuminate\Support\Facades\DB;
      use App\Currency;
?>
            <table class="display nowrap" style="width:100%" id="filterDisbursmentInfo">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>File Number</th>
                        <th>Voucher Number</th>
                        <th>Currency</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Paid To</th>
                        <th>Cash/Bank</th>
                    </tr>
                </thead>
            <tbody>
@foreach($expancesDetail as $expancesDetail)
            <tr>
                    <td><?php echo  !empty($expancesDetail->disbursed_datetime) ? date("d-m-Y", strtotime($expancesDetail->disbursed_datetime)) : '-';?></td>
                    <td>
                        <?php 
                            $file_number = DB::table('cargo')->where('id',$expancesDetail->file_number)->get();
                            foreach($file_number as $file_number){
                                echo $file_number->file_number;
                            }
                        ?>
                    </td>
                    <td>{{$expancesDetail->voucher_number}}</td>
                    <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($expancesDetail->expense_id); echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                    <td>{{$expancesDetail->amount}}</td>
                    <td>{{$expancesDetail->description}}</td>
                    <td>
                        <?php 
                            $paid_to = DB::table('vendors')->where('id',$expancesDetail->paid_to)->first();
                            echo !empty($paid_to) ? $paid_to->company_name : '-';
                            ?>
                    </td>
                    <td>
                        <?php 
                            $cashCredit = DB::table('cashcredit')->where('id',$expancesDetail->c_credit_account)->get();
                            foreach($cashCredit as $cashCredit){
                                if($cashCredit->name != '')
                                    echo $cashCredit->name;
                                else{
                                    echo('-');
                                }
                            }
                            ?>
                        
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
<script type="text/javascript">
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
    $('#filterDisbursmentInfo').DataTable(
    {
        'stateSave': true,
        stateSaveParams: function (settings, data) {
            delete data.order;
        },
        "columnDefs": [ {
            "targets": [-1],
            "orderable": false
            },{ type: 'date-uk', targets: 0 }],
        "order": [[ 0, "desc" ]],
        "aaSorting": [],
        //"order": [[ 0, "desc" ]],
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