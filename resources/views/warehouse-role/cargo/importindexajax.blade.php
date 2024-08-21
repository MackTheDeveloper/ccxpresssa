<?php
$permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);
?>
<div class="box-body">
    <select id="cargolisting" class="form-control">
        <option value="4">All Files</option>
        <option selected="" value="1">Import Files</option>
        <option value="2">Export Files</option>


    </select>
    <table id="example" class="display nowrap" style="width:100%;">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>File No.</th>
                <th>File Name</th>
                <th>Agent</th>
                <th>Opening date</th>
                <th>Arrival date</th>
                <th>Consignee</th>
                <th>Shipper</th>
                <th>AWB/BL No</th>
                <th>Package/Container</th>
                <th></th>
                <th>Warehouse Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($dataWarehouseCargo as $cargos)
            <?php $assignedCss = '';
            if ($cargos->file_close == 1)
                $assignedCss = 'color:red';
            ?>
            <tr style="<?php echo $assignedCss; ?>" data-editlink="{{ route('viewcargodetailforwarehouse',$cargos->id) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                <td style="display: none">{{$cargos->id}}</td>
                <td>{{$cargos->file_number}}</td>
                <td><?php echo !empty($cargos->file_name) ? $cargos->file_name : '-'; ?></td>
                <td><?php $data = app('App\User')->getUserName($cargos->agent_id);
                    echo !empty($data->name) ? $data->name : '-'; ?></td>
                <td><?php echo date('d-m-Y', strtotime($cargos->opening_date)) ?></td>
                <td><?php echo date('d-m-Y', strtotime($cargos->arrival_date)) ?></td>
                <td><?php $data = app('App\Clients')->getClientData($cargos->consignee_name);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                <td><?php $data = app('App\Clients')->getClientData($cargos->shipper_name);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                <td><?php echo !empty($cargos->awb_bl_no) ? $cargos->awb_bl_no : '-'; ?></td>
                <td><?php echo $cargos->flag_package_container == 1 ? 'Package' : 'Container'; ?></td>
                <td><?php if ($cargos->flag_package_container == 1) {
                        $dataPackage = App\CargoPackages::getData($cargos->id);
                        echo 'Weight : ' . (!empty($dataPackage->pweight) ? $dataPackage->pweight : '-') . ' Volume : ' . (!empty($dataPackage->pvolume) ? $dataPackage->pvolume : '-') . ' Pieces : ' . (!empty($dataPackage->ppieces) ? $dataPackage->ppieces : '-');
                    } else {
                        $dataContainer = App\CargoContainers::getData($cargos->id);
                        echo $dataContainer->containerNumbers;
                    } ?></td>
                <td><?php echo ($cargos->consolidate_flag == 1 && !empty($cargos->warehouse_status)) ? Config::get('app.warehouseStatus')[$cargos->warehouse_status] : '-' ?></td>
                <?php if ($cargos->file_close == 1) { ?>
                    <td></td>
                <?php } else { ?>
                    <td>
                        <div class='dropdown'>


                            <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                            <ul class='dropdown-menu' style='left:auto;'>
                                <?php if ($permissionCargoInvoicesAdd) { ?>
                                    <li>
                                        <a href="{{ route('createwarehouseinvoice',$cargos->id) }}">Add Invoice</a>
                                    </li>
                                <?php } ?>


                            </ul>
                        </div>
                    </td>
                <?php } ?>
            </tr>
            <?php $i++; ?>
            @endforeach

        </tbody>

    </table>
</div>




<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#example').DataTable({
            'stateSave': true,
            "order": [
                [0, "desc"]
            ],
            "scrollX": true,
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container())
                    .on('click', function() {
                        $('#loading').show();
                        setTimeout(function() {
                            $("#loading").hide();
                        }, 200);
                        $('.expandpackage').each(function() {
                            if ($(this).hasClass('fa-minus')) {
                                $(this).removeClass('fa-minus');
                                $(this).addClass('fa-plus');
                            }
                        })
                    });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            }
        });


        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });

        $('#cargolisting').change(function() {
            $('#loading').show();
            var cargoI = $(this).val();
            if (cargoI == 1)
                //window.location = 'cargoimports';
                var urlz = '<?php echo route("warehousecargoimportsajax"); ?>'
            else if (cargoI == 2)
                //window.location = 'cargoexports';
                var urlz = '<?php echo route("warehousecargoexportsajax"); ?>'
            else
                var urlz = '<?php echo route("warehousecargoallajax"); ?>'

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: urlz,
                type: 'POST',
                data: {
                    'cargoI': cargoI
                },
                success: function(data) {
                    $('.cargocontainer').html(data);
                    $('#loading').hide();
                }
            });
        })
    })
</script>