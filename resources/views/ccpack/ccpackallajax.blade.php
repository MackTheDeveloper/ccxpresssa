<?php
    $permissionCourierAddExpense = App\User::checkPermission(['add_courier_expenses'],'',auth()->user()->id); 
    $permissionCourierAddInvoice = App\User::checkPermission(['add_courier_invoices'],'',auth()->user()->id); 
?>
<?php use App\Ups;?>

<table id="example" class="display nowrap" style="width:100%">
                        <thead>
                            
                            <tr>
                                <th>File Number</th>
                                <th>Arrival Date</th>
                                <th>Awb Number</th>
                                <th>Consignee Name</th>
                                <th>Shipper Name</th>
                                <th>No. Of Pcs</th>
                                <th>Weight</th>
                                <th>Freight</th>
                                <th>Action</th>
                            </tr>
                           
                        </thead>
                        <tbody>
                            @foreach($ccpackData as $data)
                            <tr>
                                <td>{{$data->file_number}}</td>
                                <td><?php echo date('d-m-Y',strtotime($data->arrival_date))?></td>
                                <td>{{$data->awb_number}}</td>
                                <td><?php echo Ups::getConsigneeName($data->consignee)?></td>
                                <td>{{$data->shipper_name}}</td>
                                <td>{{$data->no_of_pcs}}</td>
                                <td>{{$data->weight}}</td>
                                <td>{{$data->freight}}</td>
                                <?php 
                                    $delete =  route('deleteccpack',$data->id);
                                    $edit =  route('editccpack',$data->id);
                                ?>
                                <td>
                                    <div class='dropdown'>
                                        <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                        <a href="<?php echo $delete ?>" title="Delete" style = "margin-left : 10%" class = "delete-record"><i class="fa fa-trash-o" aria-hidden="true"></i></a>

                                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                            <ul class='dropdown-menu' style='left:auto;'>
                                                <?php if($permissionCourierAddExpense) { ?>
                                                    <li>
                                                       <a href="{{ route('createccpackexpense',$data->id) }}">Add File Expense</a>
                                                   </li>
                                                   <?php } ?>
                                                   <?php if($permissionCourierAddInvoice) { ?>
                                                    <li>
                                                        <a href="{{ route('createccpackinvoices',$data->id) }}">Add Invoice</a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                    </div>
                                </td>
                            </tr>
                             @endforeach
                        </tbody>
                    </table>

<script type="text/javascript">
    var table = $('#example').DataTable({
        "columnDefs": [{
            "targets": [3],
            "orderable": false
        }],
        "scrollX": true,
        "order": [[ 0, "desc" ]],
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
                $('.expandpackage').each(function(){
                    if($(this).hasClass('fa-minus'))
                    {
                        $(this).removeClass('fa-minus');    
                        $(this).addClass('fa-plus');
                    }
                })
        });
        $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
        });
      },
     
  });
</script>