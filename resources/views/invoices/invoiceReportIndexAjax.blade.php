<?php use App\Currency;?>
<?php 
    $permissionCargoInvoicesEdit = App\User::checkPermission(['update_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesDelete = App\User::checkPermission(['delete_cargo_invoices'],'',auth()->user()->id); 
    $permissionCargoInvoicesPaymentAdd = App\User::checkPermission(['add_cargo_invoice_payments'],'',auth()->user()->id); 
    $permissionCargoInvoicesCopy = App\User::checkPermission(['copy_cargo_invoices'],'',auth()->user()->id); 
?>
<table id="example" class="display nowrap" style="width:100%">

        <thead>

            <tr>
              
                <th>Date</th>
                <th>Invoice No.</th>
                <th>File No.</th>
                <th>AWB / BL No.</th>
                <th>Created By</th>
                <th>Status</th>
                
            </tr>
        </thead>
        <tbody>
            <?php $count = 0;?>
           @foreach ($data as $items)
                <tr data-editlink="{{ route('ViewDetails',[$items->id]) }}" id="<?php echo $items->id; ?>" class="edit-row">
                    <td><?php echo date('d-m-Y',strtotime($items->date)) ?></td>
                    <td>{{'#'.$items->bill_no}}</td>
                    <td>{{$items->file_no}}</td>
                    <td>{{$items->awb_no}}</td>
                    <td><?php $dataUser = app('App\User')->getUserName($items->created_by); 
                            echo !empty($dataUser->name) ? $dataUser->name : "-";?></td>
                    <td style="<?php echo ($items->payment_status == 'Paid') ? 'color:green' : 'color:red'; ?>">{{$items->payment_status}}</td>
                    
                    <?php $count++?>
                </tr>
            @endforeach
            
        </tbody>
        
    </table>

<script type="text/javascript">
    $(document).ready(function() {
        $('#example').DataTable({
            'stateSave': true,
            "ordering": false
         });


        var count = <?php echo $count;?>;
   for(i=0;i<count;i++){
   $('#sendMail'+i).click(function(event){
        event.preventDefault();
        $('#loading').fadeIn();
        var itemId = $(this).attr("value");
        console.log(itemId);
        $.ajax({
           type:"GET",
           url:"{{url('invoicesmail/send')}}",
           data:{itemId:itemId},
           success:function(res){               
            if(res){
                $('#loading').fadeOut(function(){$('#successModal').modal('show');});
            } else {
                $('#flash').html(res);
            }
           }
        });
     });
}
    })
</script>
