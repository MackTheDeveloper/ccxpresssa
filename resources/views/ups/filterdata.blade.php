

     <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <th style="display: none">ID</th>
                <th>File Status</th>
                <th>Agent</th>
                <th>Date</th>
                <th>AWB Tracking</th>
                <th>Destination</th>
                <th>Origin</th>
                <th>Weight</th>
                <th>Billing Term</th>
                <th>Action</th>
            </tr>
        </thead>
       
        <tbody>
            <?php $i = 1; ?>
            @foreach ($upsData as $couriers)
                <?php $dataPackage = App\Ups::checkPakckages($couriers->id); 
                    $cls = '';
                    if($dataPackage > 0)
                        $cls = 'fa fa-plus';

                    $assignedCss = '';
                    $fcCss = '';
                    $checkFileAssigned = App\Ups::checkFileAssgned($couriers->id);
                    if($checkFileAssigned == 'no')
                        $assignedCss = 'color:#3097D1';

                    if($couriers->fc == 1)
                        $fcCss = 'color:#fb7400';
                ?>
                <tr style="<?php echo $assignedCss.';'.$fcCss; ?>" data-editlink="{{ route('editups',$couriers->id) }}" id="<?php echo $couriers->id; ?>" class="edit-row">
                    <td style="display: block;text-align: center;padding-top: 25px;" class="expandpackage <?php echo $cls; ?>" data-rowid=<?php echo $i; ?> data-upsid="<?php echo $couriers->id; ?>"></td>
                    <td style="display: none">{{$couriers->id}}</td>
                    <td><?php echo !empty($couriers->ups_scan_status) ? Config::get('app.ups_new_scan_status')[$couriers->ups_scan_status] : '-' ?></td>
                    <td><?php $data = app('App\User')->getUserName($couriers->agent_id); echo !empty($data->name) ? $data->name : '-'; ?></td>
                    <td><?php echo date('d-m-Y',strtotime($couriers->tdate)) ?></td>
                    <td>{{$couriers->awb_number}}</td>
                    <td>{{$couriers->destination}}</td>
                    <td>{{$couriers->origin}}</td>
                    <td>{{$couriers->weight}}</td>
                    <td><?php echo App\Ups::getBillingTerm($couriers->id); ?></td>
                    <td>
                        <div class='dropdown'>
                        <?php 
                        $delete =  route('deleteups',$couriers->id);
                        $edit =  route('editups',$couriers->id);
                        ?>
                        
                        <a href="<?php echo $edit ?>" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                        
                        <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                        <a class="upload-file" href="javascript:void(0)" id="upload-file-btn" value="{{url('files/upload',['ups',$couriers->id])}}" style="margin-left: 12px"><i class="fa fa-upload" aria-hidden="true"></i></a>
                        
                        <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                                <ul class='dropdown-menu' style='left:auto;'>
                                    <li>
                                         <a href="{{ route('createupsexpense',$couriers->id) }}">Add UPS Expense</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('createupsinvoice',$couriers->id) }}">Add Invoice</a>
                                    </li>
                                </ul>
                        </div>
                        
                    </td>
                    
                </tr>
                <?php $i++; ?>
            @endforeach
            
        </tbody>
        
    </table>
      

<script type="text/javascript">
$(document).ready(function() {

  
    
     var table = $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [{
            "targets": [0],
            "orderable": false
        }],
        "scrollX": true,
        "order": [[ 1, "desc" ]],
         drawCallback: function(){
              $('.fg-button,.sorting,#example_length', this.api().table().container())          
                 .on('click', function(){
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
           }
    });

    
$('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    

})
</script>


