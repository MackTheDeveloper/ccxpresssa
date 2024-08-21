<div style="float: right;width: 200px;margin: 0px;height: 35px;position: absolute;left: 70%;z-index: 111;top:68px">
        <a title="Click here to print"  target="_blank" href="../public/reports_pdf/<?php echo $pdf_file; ?>"><i class="fa fa-print btn btn-primary"></i></a>
    </div>
<table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php $i = 1; ?>
                        @foreach ($dataCashCredit as $dinfo)
                        <?php $amtDesc = explode('-', $dinfo->description); ?>
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{date('d-m-Y h:i:s',strtotime($dinfo->created_on))}}</td>
                                <td><?php echo $amtDesc[1]; ?></td>
                                <td><?php echo $dinfo->cash_credit_flag == 1 ? $amtDesc[0] : '-';  ?></td>
                                <td><?php echo $dinfo->cash_credit_flag == 2 ? $amtDesc[0] : '-';  ?></td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                </tbody>
            </table>

<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable({
        'stateSave': true,
        "ordering": false
    });
});
</script>            
            
       
