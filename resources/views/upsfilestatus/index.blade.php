@extends('layouts.custom')

@section('title')
File Status
@stop


@section('breadcrumbs')
    @include('menus.ups-file-status')
@stop


@section('content')
	<section class="content-header">
		<h1>File Progress Status Listing</h1>
    </section>
    <section class="content">
        <div class="box box-success">
            <div class="box-body">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">Id</th>
                            <th>File Status</th>
                            <th>Occurrence</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($fileStatus as $data)
                        <tr data-editlink="{{ route('editfilestatus',[$data->id]) }}" id="<?php echo $data->id; ?>" class="edit-row">
                            <td style="display: none">{{$data->id}}</td>
                            <td>{{$data->status}}</td>
                            <td><?php echo $data->after_or_before == '1' ? 'Before' : 'After' ;?></td>
                            <td>
                                <?php 
                                    
                                    $edit =  route('editfilestatus',$data->id);
                                ?>
                                 <div class='dropdown'>
                                    <a href="<?php echo $edit ?>" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                    
                                </div>
                            </td>
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
        var table = $('#example').DataTable({
        'stateSave': true,
        "columnDefs": [{
            
            "orderable": false,
        }],
        "scrollX": true,
        "order": [[ 0, "desc" ]],
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
    </script>
@stop