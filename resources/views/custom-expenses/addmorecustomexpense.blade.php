<tr id="{{$counter}}">
                                    <td style="text-align: center;"><?php echo $counter+1; ?></td>

                                    

                                    <td><?php echo Form::textarea("expenseDetails[description][$counter]",'duties and taxes',['class'=>'form-control cvalidation','id'=>"description-$counter",'rows'=>1]); ?></td>

                                    <td><?php echo Form::text("expenseDetails[amount][$counter]",'0.00',['class'=>'form-control cvalidation famount','placeholder' => '','onkeypress'=>'return isNumber(event)']); ?></td>

                                    <td><a href="javascript:void(0)" data-cid="{{$counter}}" class='btn btn-success btn-xs addmoreexpense'>+</a>
                                        <a href="javascript:void(0)" data-cid="{{$counter}}" class='btn btn-danger btn-xs removeexpense'>-</a></td>
                                </tr>

<script type="text/javascript">
    $(document).ready(function() {
        $('.selectpicker').selectpicker();
    });
</script>
                

     