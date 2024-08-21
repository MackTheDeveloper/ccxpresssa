@extends('layouts.custom')

@section('title')
A R Aging Summary
@stop

@section('breadcrumbs')
@include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>A R Aging Summary</h1>
</section>

<section class="content">
  
    
    <div class="box box-success">
        <div class="box-body">
            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'arAgingForm','autocomplete'=>'off')) }}
            {{ csrf_field() }}
            <div class="row" style="margin-bottom:20px">
                <div class="filterout col-md-12">
                    <div class="col-md-1 row">
                        <label style="margin-top:7px">As of</label>
                    </div>
                    
                   {{--  <div class="from-date-filter-div filterout col-md-2">
                        <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                    </div> --}}
                    <div class="to-date-filter-div filterout col-md-2">
                        <input type="text" value="{{date('d-m-Y')}}" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                    </div>
                    <div class="col-md-2">
                        
                        <button type="submit" id="clsExportToExcel" class="btn btn-success"><span><i class="fa fa-file-excel-o" aria-hidden="true"></i></span> Export</button>
                    </div>
                </div>
            </div>
            {{ Form::close() }}

        
        </div>
    </div>
</section>

@endsection
@section('page_level_js')
<script type="text/javascript">
    $(document).ready(function() {


        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        
       
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#arAgingForm').validate({
             rules: {
                "from_date_filter": {
                    required: true
                },
                "to_date_filter": {
                    required: true
                }
            },
            submitHandler: function(form) {
                $('#loading').show();
                var fromDate = $('.from-date-filter').val();
                var toDate = $('.to-date-filter').val();
                
                
                    var urlztnn = '<?php echo route("exportArAging"); ?>';
                    urlztnn += '/' + fromDate + '/' + toDate;
                    $.ajax({
                        url: urlztnn,
                        async: true,
                        type: 'POST',
                        data: {
                            'fromDate': fromDate,
                            'toDate': toDate,
                        },
                        success: function(dataRes) {
                            window.open(urlztnn, '_blank');
                            $('#loading').hide();
                        }
                    });
                
            },
        });
    })

    
</script>
@stop