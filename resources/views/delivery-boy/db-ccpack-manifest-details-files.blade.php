<div style="margin-bottom: 10px;float:left;width:100%">
    <button style="float: right;" id="btnShipmentDelivered" class="btn btn-success btnShipmentDelivered" value="">Delivered</button>
    <button style="float: right;margin-right:10px" id="btnShipmentNotDelivered" class="btn btn-danger btnShipmentNotDelivered" value="{{route('deliveryboyshipmentnotdelivered',['CCPack',$deliveryBoyId])}}">Not Delivered</button>
</div>
<table id="example2" class="display nowrap" style="width:100%">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
            <th>File No.</th>
            <th>Consignee</th>
            <th>File Status</th>
            <th>Delivery Comment</th>
            <th>Invoice No.</th>
            <th>Invoice Amount</th>
            <th>Payment Status</th>
            <th>Assigned Date</th>
            <th>Arrival Date</th>
            <th>Shipper</th>
            <th>AWB Tracking</th>
            <th>No. Of Pcs</th>
            <th>Weight</th>
            <th>Freight</th>


        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($ccpackFileAssignedToDeliveryBoy as $k => $items) {
            $countPending = app('App\Common')->checkIfInvoiceStatusPending($items->id, 'ccpack');
            if ($items->ccpack_scan_status == 7) {
                $getCommentOfDelivery = app('App\Common')->getCommentOfDelivery($items->id, 'ccpack');
                if (!empty($getCommentOfDelivery))
                    $deliveryComment = $getCommentOfDelivery->notes . ' - ' . (!empty($items->reason_for_return) ? Config::get('app.reasonOfReturn')[$items->reason_for_return] : '-');
                else
                    $deliveryComment = '-';
            } else {
                $getCommentOfDelivery = app('App\Common')->getCommentOfDelivery($items->id, 'ccpack');
                if (!empty($getCommentOfDelivery))
                    $deliveryComment = !empty($getCommentOfDelivery) ? $getCommentOfDelivery->notes : '-';
                else
                    $deliveryComment = '-';
            }
            $closeCls = '';
            if ($items->file_close == 1) {
                $closeCls = 'trClosedFile';
            }
        ?>
            <tr class="<?php echo $closeCls; ?>">
                <td style="text-align: center"><input type="checkbox" name="singlecheckbox" class="singlecheckbox" id="{{$items->id}}" value="{{$items->id}}" /></td>
                <td>{{$items->file_number}}</td>
                <td>{{$items->consigneeName}}</td>
                <td>{{!empty($items->ccpack_scan_status) ? Config::get('app.ups_new_scan_status')[$items->ccpack_scan_status] : '-'}}</td>
                <td>{{$deliveryComment}}</td>
                <td>{{!empty($items->invoiceNumbers) ? $items->invoiceNumbers : '-'}}</td>
                <td class="alignright">{{!empty($items->totalAmount) ? number_format($items->totalAmount,2) : '0.00'}}</td>
                <td style="color:<?php echo $countPending > 0 ? 'red' : 'green';  ?>">{{($countPending > 0) ? 'Pending' : 'Paid'}}</td>
                <td>{{!empty($items->delivery_boy_assigned_on) ? date('d-m-Y',strtotime($items->delivery_boy_assigned_on)) : '-'}}</td>
                <td>{{!empty($items->arrival_date) ? date('d-m-Y',strtotime($items->arrival_date)) : '-'}}</td>
                <td>{{$items->shipperName}}</td>
                <td>{{$items->awb_number}}</td>
                <td>{{$items->no_of_pcs}}</td>
                <td>{{$items->weight . ' ' . 'KGS'}}</td>
                <td>{{$items->freight}}</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<div id="modalShipmentNotDelivered" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3 class="modal-title modal-title-block text-center primecolor">Shipment Return</h3>
                <input type="hidden" name="ids" class="ids" value="">
            </div>
            <div class="modal-body" id="modalContentShipmentNotDelivered" style="overflow: hidden;">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#btnShipmentDelivered').click(function() {
            if ($('.singlecheckbox:checked').length < 1) {
                alert("Please select the Files.");
                return false;
            } else {
                var url = '<?php echo url("delivery-boy-shipment-delivered-or-not"); ?>';
                if (confirm("Are you sure to change the status of the shipment?")) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            ids: $(this).val(),
                            module: 'CCPack',
                            flagButton: 'delivered'
                        },
                        success: function(data) {
                            window.location.href = '<?php echo route("manifestdetailsdeliveryboy", $deliveryBoyId) ?>';
                        },
                    });
                } else {
                    return false;
                }
            }
        })

        $('.btnShipmentNotDelivered').click(function() {
            if ($('.singlecheckbox:checked').length < 1) {
                alert("Please select the Files.");
                return false;
            } else {
                $('#modalShipmentNotDelivered').modal('show').find('#modalContentShipmentNotDelivered').load($(this).attr('value'));
            }
        })

        $(document).delegate("#selectAll", "click", function(e) {
            $('#example2 .singlecheckbox').prop('checked', this.checked);
            var checked = [];
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checked.push($(this).attr('id'))
                }
            });
            $('.ids').val(checked);
            $('.btnShipmentDelivered').val(checked);

            //console.log(checked);
        });

        $(document).on('click', '.singlecheckbox', function() {
            var checkedFlag = 0;
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checkedFlag = 1;
                } else {
                    checkedFlag = 0;
                    return false;
                }
            });
            if (checkedFlag == 0) {
                $('#selectAll').prop('checked', false);
            }
            if (checkedFlag == 1) {
                $('#selectAll').prop('checked', true);
            }

            var checked = [];
            $('input[name="singlecheckbox"]').each(function() {
                if ($(this).prop('checked') == true) {
                    checked.push($(this).attr('id'))
                }
            });
            $('.ids').val(checked);
            $('.btnShipmentDelivered').val(checked);
            //console.log(checked);
        });
        $('#example2').DataTable({
            "scrollX": true,
            "columnDefs": [{
                "targets": [0],
                "orderable": false
            }],
            "aaSorting": [],
            drawCallback: function() {
                $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function() {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
                $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() {
                        $("#loading").hide();
                    }, 200);
                });
            },

        });
    });
</script>