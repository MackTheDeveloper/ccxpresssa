
<tr class="childrw child-<?php echo $rowId; ?>">
    <td colspan="17" style="font-weight: bold;font-size: 14px;color: #cea5a5;">Package Detail</td>
</tr>
            <tr class="childrw childrwheader child-<?php echo $rowId; ?>">
                <th colspan="2">Shipment Number</th>
                <th>Package Weight</th>
                <th>Package Weight Unit</th>
                <th colspan="1">Inbound Contrainer Number</th>
                <th colspan="1">Package Tracking Number</th>
                <th colspan="2">Package Load</th>
                <th colspan="2">Incomplete Shipping Flag</th>
                <th colspan="7">Container Flag</th>
            </tr>


            @foreach ($packageData as $packageData)
                <tr class="childrw child-<?php echo $rowId; ?>">
                    <td colspan="2">{{$packageData->shipment_number}}</td>
                    <td>{{$packageData->package_weight}}</td>
                    <td>{{$packageData->package_weight_unit}}</td>
                    <td colspan="1">{{$packageData->inbound_container_number}}</td>
                    <td colspan="1">{{$packageData->package_tracking_number}}</td>
                    <td colspan="2">{{$packageData->package_load}}</td>
                    <td colspan="2"><?php echo $packageData->incomplete_shipping_flag == 'N' ? 'No' : 'Yes' ?></td>
                    <td colspan="7"><?php echo $packageData->container_flag == 'N' ? 'No' : 'Yes' ?></td>
                </tr>
            @endforeach
            
    







@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {

})
</script>
@stop

