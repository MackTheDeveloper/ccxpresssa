<!DOCTYPE html>
<html>

<head>
  <title>Free Domicile Report</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style type="text/css">
    th,
    td {
      padding: 8px;
      text-align: center;
    }

    body {
      background-color: #fff;
    }
  </style>
</head>

<body>
  <h3 style="background: #ccc;padding:5px;font-weight:normal;font-size: 18px;margin-bottom: 25px">Free Domicile Report</h3>
  <table id="example" class="table table-bordered" style="width:100%;">
    <thead>
      <tr>
        <th>File Number</th>
        <th>Date</th>
        <th>AWB Number</th>
        <th>Billing Term</th>
        <th>Shipper</th>
        <th>Consignee</th>
        <th>Origin</th>
        <th>Destination</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($upsData)) { ?>
        @foreach($upsData as $data)
        <tr>
          <td>{{$data->fileNumber}}</td>
          <td>{{date('d-m-Y',strtotime($data->date))}}</td>
          <td><?php echo !empty($data->trackingNumber) ? $data->trackingNumber : '-'; ?></td>
          <td><?php echo App\Ups::getBillingTerm($data->id); ?></td>
          <td><?php echo !empty($data->shipperName) ? $data->shipperName : '-'; ?></td>
          <td><?php echo !empty($data->consigneeName) ? $data->consigneeName : '-'; ?></td>
          <td>{{$data->origin}}</td>
          <td>{{$data->destination}}</td>
        </tr>
        @endforeach
      <?php } else { ?>
        <tr>
          <td colspan="8">No Date Found.</td>
        </tr>
      <?php  } ?>
    </tbody>
  </table>
</body>

</html>