<table id="fileDetails" class="table display nowrap" style="width:100%">
    <thead style="background: #d2d2d2;">
        <tr>
            <th>File Name</th>
            <th>Total Pages</th>
            <th>Status</th>
            <th>Added By</th>
            <th>Added On</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($fileDetails) && count($fileDetails) > 0) { ?>
            @foreach ($fileDetails as $items)
            <?php $userData = app('App\User')->getUserName($items->created_by); ?>
            <tr>
                <td><a target="_blank" href="{{URL::to('/public/manifestsAll/'.$items->file_name)}}">{{$items->file_name}}</a></td>
                <td>{{!empty($items->total_pages) ? $items->total_pages : '-'}}</td>
                <td><b style="<?php echo $items->upload_status == 'Uploaded' ? 'color:green' : '' ?>">{{$items->upload_status}}</b></td>
                <td>{{!empty($userData) ? $userData->name : '-'}}</td>
                <td>{{date('d-m-Y',strtotime($items->created_on))}}</td>
            </tr>
            @endforeach
        <?php } else { ?>
            <tr>
                <td>No Record Found</td>
            </tr>
        <?php } ?>
    </tbody>
</table>