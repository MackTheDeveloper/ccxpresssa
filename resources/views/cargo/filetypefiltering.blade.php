<?php
$permissionCargoEdit = App\User::checkPermission(['update_cargo'], '', auth()->user()->id);
$permissionCargoDelete = App\User::checkPermission(['delete_cargo'], '', auth()->user()->id);
$permissionCargoExpensesAdd = App\User::checkPermission(['add_cargo_expenses'], '', auth()->user()->id);
$permissionCargoInvoicesAdd = App\User::checkPermission(['add_cargo_invoices'], '', auth()->user()->id);
?>

<div class="box-body">
    <select id="cargolisting" class="form-control">
        <option selected="" value="4">All Files (I, E, L)</option>
        <option value="1">Import Files</option>
        <option value="2">Export Files</option>
        <option value="3">Locale Files</option>
    </select>

    <select id="cargofiletype" class="form-control">
        <option <?php echo $flagFileType == '' ? 'selected' : '' ?> value="">All Files (Cons. & Non cons.)</option>
        <option <?php echo $flagFileType == '1' ? 'selected' : '' ?> value="1">Consolidate Files</option>
        <option <?php echo $flagFileType == '0' ? 'selected' : '' ?> value="0">Non consolidate Files</option>
    </select>
    <div class="out-filter-secion col-md-4">
        <div class="from-date-filter-div filterout col-md-6">
            <input type="text" name="from_date_filter" value="<?php echo isset($fromDate) ? date('d-m-Y', strtotime($fromDate)) : ''; ?>" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
        </div>
        <div class="to-date-filter-div filterout col-md-6">
            <input type="text" name="to_date_filter" value="<?php echo isset($toDate) ? date('d-m-Y', strtotime($toDate)) : ''; ?>" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
        </div>
    </div>
    <table id="example" class="display nowrap" style="width:100%;float: left;">
        <thead>
            <tr>
                <th style="display: none">ID</th>
                <th>File No.</th>
                <th>Agent</th>
                <th>Date</th>
                <th>AWB/BL No</th>
                <th>Type</th>
                <th>Consignee/Client</th>
                <th>Invoice Numbers</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            @foreach ($cargos as $cargos)
            <?php $assignedCss = '';
            $checkFileAssigned = App\Cargo::checkFileAssgned($cargos->id);
            if ($checkFileAssigned == 'no')
                $assignedCss = 'color:#3097D1';
            if ($cargos->file_close == 1)
                $assignedCss = 'color:red';
            ?>
            <tr style="<?php echo $assignedCss; ?>" data-editlink="{{ route('viewcargo',[$cargos->id,$cargos->cargo_operation_type]) }}" id="<?php echo $cargos->id; ?>" class="edit-row">
                <td style="display: none">{{$cargos->id}}</td>
                <td>{{$cargos->file_number}}</td>
                <td><?php $data = app('App\User')->getUserName($cargos->agent_id);
                    echo !empty($data->name) ? $data->name : '-'; ?></td>
                <td><?php echo date('d-m-Y', strtotime($cargos->opening_date)) ?></td>
                <td>{{$cargos->awb_bl_no}}</td>
                <td><?php echo ($cargos->cargo_operation_type == 1 ? 'Import' : ($cargos->cargo_operation_type == 2 ? 'Export' : 'Locale')) ?></td>
                <td><?php $data = app('App\Clients')->getClientData($cargos->consignee_name);
                    echo !empty($data->company_name) ? $data->company_name : '-'; ?></td>
                <td><?php echo App\Expense::getInvoicesOfFile($cargos->id);  ?></td>
                <?php if ($cargos->file_close == 1) { ?>
                    <td></td>
                <?php } else { ?>
                    <td>
                        <div class='dropdown'>
                            <?php
                            $delete =  route('deletecargo', [$cargos->id, $cargos->cargo_operation_type]);
                            $edit =  route('editcargo', [$cargos->id, $cargos->cargo_operation_type]);
                            ?>
                            <?php if ($permissionCargoEdit) { ?>
                                <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>&nbsp; &nbsp;
                            <?php } ?>
                            <?php if ($permissionCargoDelete) { ?>
                                <a class="delete-record" href="<?php echo $delete ?>" data-confirm="test" title="Delete"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                            <?php } ?>

                            <button class='fa fa-cogs btn  btn-sm dropdown-toggle' type='button' data-toggle='dropdown' style="margin-left: 10px"></button>
                            <ul class='dropdown-menu' style='left:auto;'>
                                <?php
                                if ($permissionCargoExpensesAdd) {
                                    $countPending = 0;
                                    $countPending = App\Expense::getPendingExpenses($cargos->id);
                                ?>
                                    <li>
                                        <a href="{{ route('createexpenseusingawl',['cargo',$cargos->id,'flagFromListing']) }}">Add Expense
                                            <?php $csStyle = 'display:none';
                                            if ($countPending != 0) {
                                                $csStyle = '';
                                            } ?><span style="<?php echo $csStyle; ?>" class="pendingexpense"><?php echo $countPending; ?></span>
                                        </a>
                                    </li>
                                <?php } ?>
                                <?php if ($permissionCargoInvoicesAdd) {  ?>
                                    <li>
                                        <a href="{{ route('createinvoice',$cargos->id) }}">Add Invoice</a>
                                    </li>
                                <?php } ?>
                                <?php if ($cargos->consolidate_flag == 1 && ($cargos->cargo_operation_type == 1 || $cargos->cargo_operation_type == 2)) { ?>
                                    <li>
                                        <button id="btnAddWarehouseInFile" data-module="Warehouse" class="btnModalPopup" value="<?php echo route('addwarehouseinfile', $cargos->id) ?>">Add Warehouse</button>
                                    </li>
                                <?php } ?>
                                <li>
                                    <button id="btnAddCashCreditInFile" data-module="Payment Mode" class="btnModalPopup" value="<?php echo route('addcashcreditinfile', $cargos->id) ?>">Add Payment Mode</button>
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


<div id="modalCreateExpense" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title text-center primecolor">Add Expense</h3>
            </div>
            <div class="modal-body" id="modalContentCreateExpense" style="overflow: hidden;">
            </div>
        </div>

    </div>
</div>


<script type="text/javascript">
    $(document).ready(function() {

        var table = $('#example').DataTable({
            'stateSave': true,
            "columnDefs": [{
                "targets": [-1],
                "orderable": false
            }],
            "scrollX": true,
            "order": [
                [0, "desc"]
            ],
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
                var urlz = '<?php echo route("cargoimportsajax"); ?>'
            else if (cargoI == 2)
                //window.location = 'cargoexports';
                var urlz = '<?php echo route("cargoexportsajax"); ?>'
            else if (cargoI == 3)
                //window.location = 'cargolocales';
                var urlz = '<?php echo route("cargolocalesajax"); ?>'
            else
                var urlz = '<?php echo route("cargoallajax"); ?>'

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

        $('#cargofiletype').change(function() {
            $('#loading').show();
            var flagFileType = $(this).val();
            var urlz = '<?php echo route("filterusingcargofiletype"); ?>'

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: urlz,
                type: 'POST',
                data: {
                    'flagFileType': flagFileType
                },
                success: function(data) {
                    $('.cargocontainer').html(data);
                    $('#loading').hide();
                }
            });

        })
    })
</script>