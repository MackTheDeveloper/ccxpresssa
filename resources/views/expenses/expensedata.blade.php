<table class="table" id="example1">
    <thead>
        <tr>
            <th style="display: none">ID</th>
            <th>Voucher No.</th>
            <th>BL or AWB No.</th>
            <th>Expediteur</th>
            <th>Consignataire</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($dataExpense)) { 
            foreach($dataExpense as $k => $v)
            {
            	$v = (object) $v;
            ?>
        <tr>
            <td style="display: none">{{$v->expense_id}}</td>
            <td><?php echo $v->voucher_number; ?></td>
            <td><?php echo $v->bl_awb; ?></td>
            <td><?php echo $v->consignee; ?></td>
            <td><?php echo $v->shipper; ?></td>
            <td>
                <div class='dropdown'>
                <?php 
                    $delete =  route('deleteexpense',$v->expense_id);
                ?>
                <a class="delete-record-in-popup" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
            </div>
            </td>
            
        </tr>
        <?php } } ?>
    </tbody>
</table>
<script type="text/javascript">
$(document).ready(function() {
    $('#example1').DataTable({
       // "ordering": false
        "order": [[ 0, "desc" ]],
    });
    })
</script>