<table id="example" class="display nowrap" style="width:100%">
    <thead>
        <tr>
            <th style="display: none">Id</th>
            <th>File No.</th>
            <th>Consignee</th>
            <th>Shipper</th>
            <th>File Status</th>
            <th>Shipment No.</th>
            <th>AWB Tracking</th>
            <th>Assigned Date</th>
            <th>Package Type</th>
            <th>Billing Term</th>
            <th>Invoice No.</th>
            <th>Invoice Amount</th>
            <th>Paid Amount</th>
            <th>Payment Status</th>
            <th>Delivery Comment</th>
        </tr>
    </thead>

</table>
<style>
    .hide_column {
        display: none;
    }
</style>
<script type="text/javascript">
    $(document).ready(function() {
        DatatableInitiate();
    });

    function DatatableInitiate() {
        var i = 1;
        var table = $('#example').DataTable({
            "processing": true,
            "serverSide": true,
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            "columnDefs": [{
                "targets": [8, 9, 10, 11, 12, 13, 14],
                "orderable": false
            }, {
                targets: [0],
                className: "hide_column"
            }],
            "order": [
                [0, "asc"]
            ],
            "scrollX": true,
            "aaSorting": [],
            "ajax": {
                url: "{{url('deliveryboy/ups/listbydatatableserverside')}}", // json datasource
                "data": {
                    "id": "<?php echo $id; ?>",
                },
                error: function() { // error handling
                    $(".example-error").html("");
                    $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#example_processing").css("display", "none");

                }
            },
            "createdRow": function(row, data, index) {
                $('td', row).eq(11).addClass('alignright');
                $('td', row).eq(12).addClass('alignright');
            }
        });
    }
</script>