<?php 
$permissionCourierImportEdit = App\User::checkPermission(['update_courier_import'],'',auth()->user()->id); 
$permissionCourierImportDelete = App\User::checkPermission(['delete_courier_import'],'',auth()->user()->id); 
$permissionCourierAddExpense = App\User::checkPermission(['add_courier_expenses'],'',auth()->user()->id); 
$permissionCourierAddInvoice = App\User::checkPermission(['add_courier_invoices'],'',auth()->user()->id); 
?>

<table id="example" class="display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th style="display: none">ID</th>
                        <th></th>
                        <th>File Number</th>
                        <th>Consignee</th>
                        <th>Shipper</th>
                        <th>Date</th>
                        <th>AWB Tracking</th>
                        <th>Destination</th>
                        <th>Origin</th>
                        <th>Weight</th>
                        <th>Billing Term</th>
                        <th>Commission Received</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $i = 1; ?>
                    @foreach ($upsData as $couriers)
                    <?php $dataPackage = App\Ups::checkPakckages($couriers->id); 
                    $cls = '';
                    if($dataPackage > 0)
                        $cls = 'expandpackage fa fa-plus';

                    $assignedCss = '';
                    $fcCss = '';
                    $checkFileAssigned = App\Ups::checkFileAssgned($couriers->id);
                    if($checkFileAssigned == 'no')
                        $assignedCss = 'color:#3097D1';

                    if($couriers->fc == 1)
                        $fcCss = 'color:#fb7400';
                    ?>
                    <tr style="<?php echo $assignedCss.';'.$fcCss; ?>" data-editlink="{{ route('viewdetailsups',$couriers->id) }}" id="<?php echo $couriers->id; ?>" class="edit-row">
                        <td style="display: none">{{$couriers->id}}</td>
                        <td style="display: block;text-align: center;padding-top: 15px;" class="<?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-upsid="<?php echo $couriers->id; ?>"></td>
                        <td>{{$couriers->file_number}}</td>
                        <td><?php $data = app('App\Clients')->getClientData($couriers->consignee_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                        <td><?php $data = app('App\Clients')->getClientData($couriers->shipper_name); echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                        <td><?php echo !empty($couriers->tdate) ? date('d-m-Y',strtotime($couriers->tdate)) : '-' ?></td>
                        <td>{{$couriers->awb_number}}</td>
                        <td>{{$couriers->destination}}</td>
                        <td>{{$couriers->origin}}</td>
                        <td><?php echo !empty($couriers->weight) ? $couriers->weight.' '.$couriers->unit : '-';?></td>
                        <td><?php echo App\Ups::getBillingTerm($couriers->id); ?></td>
                        <td><?php echo $couriers->commission_amount_approve == 'Y' ? 'Yes' : 'No';?></td>
                        <td>
                            <div class='dropdown'>
                                <?php 
                                $delete =  route('deleteups',[$couriers->id,'import']);
                                $edit =  route('editups',[$couriers->id,$couriers->courier_operation_type]);
                                ?>

                                <?php if($permissionCourierImportEdit) { ?>
                                    <a href="<?php echo $edit ?>" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                                    <?php } ?>
                                    <?php if($permissionCourierImportDelete) { ?>
                                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                        <?php } ?>
                                        <a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="{{url('files/upload',['ups',$couriers->id])}}" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>

                                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                        <ul class='dropdown-menu' style='left:auto;'>
                                            <?php if($permissionCourierAddExpense) { ?>
                                                <li>
                                                   <a href="{{ route('createupsexpense',$couriers->id) }}">Add File Expense</a>
                                               </li>
                                               <?php } ?>
                                               <?php if($permissionCourierAddInvoice) { ?>
                                                <li>
                                                    <a href="{{ route('createupsinvoice',$couriers->id) }}">Add Invoice</a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                            </div>

                                    </td>

                                </tr>
                                <?php $i++; ?>
                                @endforeach

                            </tbody>

                        </table>

<script type="text/javascript">
    var table = $('#example').DataTable({
        "columnDefs": [{
            "targets": [1,10],
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