<?php
$permissionCargoExpensesAdd = App\User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
?>

<div class="box-body">
    <select id="cargolisting" class="form-control">
        <option selected="" value="4">All Shipments</option>
        <option value="1">Import Shipments</option>
        <option value="2">Export Shipments</option>
        <option value="3">Locale Shipments</option>
    </select>
    <div class="out-filter-secion col-md-4">
        <div class="from-date-filter-div filterout col-md-6">
            <input type="text" name="from_date_filter" value="<?php echo !empty(Session::get('cargoListingFromDate')) ?  Session::get('cargoListingFromDate') : date('d-m-Y'); ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
        </div>
        <div class="to-date-filter-div filterout col-md-6">
            <input type="text" name="to_date_filter" value="<?php echo !empty(Session::get('cargoListingToDate')) ?  Session::get('cargoListingToDate') : date('d-m-Y'); ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
        </div>
    </div>
    <table id="example" class="display nowrap" style="width:100%;margin-top: 10px;float: left;">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>File No.</th>
                <th>File Name</th>
                <th>Agent</th>
                <th>Date</th>
                <th>AWB/BL No</th>
                <th>Type</th>
                <th>Consignee/Client</th>
                <th>Shipper</th>
                <th>Warehouse Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($cargos as $cargos)
            <?php $assignedCss = '';
            if ($cargos->file_close == 1)
                $assignedCss = 'color:red';
            ?>
            <tr style="<?php echo $assignedCss; ?>" data-editlink="{{ route('viewcargodetailforagent',$cargos->id) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                <td style="display: none">{{$cargos->id}}</td>
                <td>{{$cargos->file_number}}</td>
                <td><?php $data = app('App\User')->getUserName($cargos->agent_id);
                    echo !empty($data->name) ? $data->name : '-'; ?></td>
                <td><?php echo !empty($cargos->file_name) ? $cargos->file_name : '-'; ?></td>
                <td>{{$cargos->opening_date}}</td>
                <td>{{$cargos->awb_bl_no}}</td>
                <td><?php echo ($cargos->cargo_operation_type == 1 ? 'Import' : ($cargos->cargo_operation_type == 2 ? 'Export' : 'Locale')) ?></td>
                <td><?php $data = app('App\Clients')->getClientData($cargos->consignee_name);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                <td><?php $data = app('App\Clients')->getClientData($cargos->shipper_name);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                <td><?php echo ($cargos->consolidate_flag == 1 && !empty($cargos->warehouse_status)) ? Config::get('app.warehouseStatus')[$cargos->warehouse_status] : '-' ?></td>
                <?php if ($cargos->file_close == 1) { ?>
                    <td></td>
                <?php } else { ?>
                    <td>
                        <div class='dropdown'>


                            <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                            <ul class='dropdown-menu' style='left:auto;'>
                                <?php if ($permissionCargoExpensesAdd) { ?>
                                    <li>
                                        <a href="{{ route('createagentexpenses',['cargo',$cargos->id,'flagFromListing']) }}">Add Expense</a>
                                    </li>
                                <?php } ?>
                                <?php if ($cargos->consolidate_flag == 1 && ($cargos->cargo_operation_type == 1 || $cargos->cargo_operation_type == 2)) { ?>
                                    <li>
                                        <button id="btnAddWarehouseInFile" data-module="Warehouse" class="btnModalPopup" value="<?php echo route('addwarehouseinfile', $cargos->id) ?>">Add Warehouse</button>
                                    </li>
                                <?php } ?>
                                <li>
                                    <button id="btnAddCashCreditInFile" data-module="Cash/Credit" class="btnModalPopup" value="<?php echo route('addcashcreditinfile', $cargos->id) ?>">Add Cash/Credit</button>
                                </li>


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
                var urlz = '<?php echo route("agentcargoimportsajax"); ?>'
            else if (cargoI == 2)
                //window.location = 'cargoexports';
                var urlz = '<?php echo route("agentcargoexportsajax"); ?>'
            else if (cargoI == 3)
                //window.location = 'cargolocales';
                var urlz = '<?php echo route("agentcargolocalesajax"); ?>'
            else
                var urlz = '<?php echo route("agentcargoallajax"); ?>'

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