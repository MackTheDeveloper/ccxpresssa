@extends('layouts.custom')

@section('title')
Client Credit Report
@stop

@section('breadcrumbs')
    @include('menus.reports')
@stop

@section('content')
<section class="content-header">
    <h1>Client Credit Report</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success">
        <div class="box-body">

            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="col-md-12">
                 <div class="col-md-6">
                    <div class="form-group">
                        <?php echo Form::label('clients', 'Clients',['class'=>'col-md-4 control-label']); ?>
                        <div class="col-md-8">
                        <?php echo Form::select('clients', $clients,'',['class'=>'form-control selectpicker fclients','placeholder' => 'Select ...']); ?>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>

            
            {{ Form::close() }}
            <div class="container-rep">
                <table id="example" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                <tbody>
                </tbody>
            </table>

            </div>

            
        </div>
    </div>

</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable({
        'stateSave': true,
        "ordering": false
    });

     $('#createInvoiceForm').on('submit', function (event) {
        $('#loading').show();
            $('.fclients').each(function () {
                $(this).rules("add",
                        {
                            required: true,
                        })
            });
        });
     $('#createInvoiceForm').validate({
        submitHandler : function(form) {
                   var cashCreditId = $('#clients').val(); 
                   var clientName = $('#clients option:selected').html();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var urlztnn = '<?php echo url("reports/getclientcreditdata"); ?>';
                    $.ajax({
                            url:urlztnn,
                            async:false,
                            type:'POST',
                            data:{'cashCreditId':cashCreditId,'clientName':clientName},
                            success:function(data) {
                                        $('.container-rep').html(data);
                                        $('#loading').hide();
                                    }
                        });
                },
         errorPlacement: function(error, element) {
                            if (element.attr("name") == "clients" )
                            {
                                var pos = $('.fclients button.dropdown-toggle');
                                error.insertAfter(pos);
                            }
                            else
                            {
                                error.insertAfter(element);
                            }
                            $('#loading').hide();
                        }
     });
} )
</script>
@stop

