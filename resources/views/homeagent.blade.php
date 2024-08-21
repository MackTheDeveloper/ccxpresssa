@extends('layouts.custom')


@section('title')
Dashboard
@stop

@section('content')
<section class="content-header" style="display: block;position: relative;top: 0px;">
    <h1 style="font-size: 20px !important;font-weight: 600;">Dashboard</h1>
</section>
<section class="content editupscontainer">
    <div class="box box-success">
        <div class="box-body">

            
            <section class="content-header" style="margin: 0px 0px 10px 0px;padding: 0px 0px 10px 0px;border-bottom: 1px solid #ccc">
                    <h1 style="font-size: 18px !important;font-weight: 600;">Pending All Files</h1>
            </section>
                  <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th>Type</th>
                            <th>File No.</th>
                            <th>Opening Date</th>
                            <th>AWB/BL No</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allFiles as $items)
                            <?php 
                            if($items->flagModule == 'Cargo')
                                $url = route('viewcargodetailforagent',$items->id); 
                            else
                                $url = route('viewcourierdetailforagent',$items->id); 
                            ?>
                            <tr data-editlink="{{ $url }}" id="<?php echo $items->id; ?>" class="edit-row">
                                <td style="display: none;">{{$items->id}}</td>
                                <td>{{$items->flagModule}}</td>
                                <td>{{$items->file_number}}</td>
                                <td><?php echo date('d-m-Y',strtotime($items->opening_date)) ?></td>
                                <td><?php echo !empty($items->awb_bl_no) ? $items->awb_bl_no : '-'; ?></td>
                            </tr>
                        @endforeach
                        
                    </tbody>
                    
                </table>
            

        </div>
    </div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable(
    {
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        drawCallback: function(){
          $('.fg-button,.sorting,#example_length', this.api().table().container()).on('click', function(){
                $('#loading').show();
                setTimeout(function() { $("#loading").hide(); }, 200);
            });       
            $('#example_filter input').bind('keyup', function(e) {
                    $('#loading').show();
                    setTimeout(function() { $("#loading").hide(); }, 200);
            });
        },
        
    });

   

} )
</script>
@stop
