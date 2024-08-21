<tr id="{{$counter}}">
    <td style="text-align: center;"><?php echo $counter + 1; ?></td>

    <td><?php echo Form::select("expenseDetails[expense_type][$counter]", $dataCost, '', ['class' => 'form-control expense_type selectpicker fexpense_type', 'data-live-search' => 'true', 'id' => "expense_type-$counter", 'data-expense_type' => "$counter", 'placeholder' => 'Select ...', 'data-container' => '.expensesubcontainer']); ?></td>

    <td><?php echo Form::textarea("expenseDetails[description][$counter]", '', ['class' => 'form-control cvalidation', 'id' => "description-$counter", 'rows' => 1]); ?></td>

    <td><?php echo Form::text("expenseDetails[amount][$counter]", '0.00', ['class' => 'form-control cvalidation famount', 'placeholder' => '', 'onkeypress' => 'return isNumber(event)']); ?></td>

    <td><?php echo Form::select("expenseDetails[cash_credit_account][$counter]", ['Cash' => 'Cash', 'Cheque' => 'Cheque'], '', ['class' => 'form-control selectpicker fassignee']); ?></td>

    <td style="pointer-events: none;opacity: 0.5"><?php echo Form::select("expenseDetails[paid_to][$counter]", $dataPaidTo, Auth::user()->id, ['class' => 'form-control selectpicker fassignee', 'data-live-search' => 'true', 'data-container' => '.expensesubcontainer']); ?></td>

    <td><a href="javascript:void(0)" data-cid="{{$counter}}" class='btn btn-success btn-xs addmoreexpense'>+</a>
        <a href="javascript:void(0)" data-cid="{{$counter}}" class='btn btn-danger btn-xs removeexpense'>-</a></td>
</tr>

<script type="text/javascript">
    $(document).ready(function() {
        $('.selectpicker').selectpicker();
    });
</script>