@extends('layouts.custom')

@section('title')
Custom Report
@stop


@section('breadcrumbs')
    @include('menus.reports')
@stop


@section('content')
<section class="content-header">
    <h1>All Invoices</h1>
</section>

<section class="content">
    @if(Session::has('flash_message'))
        <div class="alert alert-success flash-success">
            {{ Session::get('flash_message') }}
        </div>
    @endif
    <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
    <div class="box box-success invoicecontainer">
        <div class="box-body">

            

            {{ Form::open(array('url' => '','class'=>'form-horizontal create-form','id'=>'createInvoiceForm','autocomplete'=>'off')) }}
                    {{ csrf_field() }}
            <div class="row" style="margin-bottom:20px">
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                </div>
                <div class="col-md-2 typeimpexpdiv" style="display:none">
                    <?php echo Form::select('type',['Invoices'=>'Invoices','Files'=>'Files'],'',['class'=>'form-control selectpicker','data-live-search' => 'true','id'=>'type']); ?>
                </div>
                <button type="submit" id="" class="btn btn-success">Submit</button>
                <button type="submit" value="showAll" id="showAll" class="btn btn-success">Show All</button>
            </div>

            
            {{ Form::close() }}
            <div class="container-rep">
                <table id="example" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="display: none">ID</th>
                            <th class="checkflag1">Invoice Number</th>
                            <th class="">Date</th>
                            <th class="">Billing Party</th>
                            <th class="">Total Amount</th>
                        </tr>
                    </thead>
                
            </table>

            </div>

            
        </div>
    </div>

</section>
<style>
    .hide_column {
        display : none;
    }
</style>
@endsection
@section('page_level_js')
<script type="text/javascript">
DatatableInitiate();
$(document).ready(function() {
    $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    
    $('#createInvoiceForm').on('submit', function (event) {
            $('.from-date-filter').each(function () {
                            $(this).rules("add",
                            {
                            required: true,
                            })
                        });
            $('.to-date-filter').each(function () {
                $(this).rules("add",
                {
                required: true,
                })
            });
        });
     $('#createInvoiceForm').validate({
        submitHandler : function(form) {
                $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
                var submitButtonName =  $(this.submitButton).attr("id");
                if(submitButtonName == 'showAll')
                {
                    $('.from-date-filter').val('');
                    $('.to-date-filter').val('');
                    var fromDate = '';
                    var toDate = '';
                }else
                {
                    var fromDate = $('.from-date-filter').val();
                    var toDate = $('.to-date-filter').val();
                }
                var type = $('#type').val(); 
                DatatableInitiate(fromDate,toDate);
            },
         errorPlacement: function(error, element) {
                            error.insertAfter(element);
                            $('#loading').hide();
                        }
     });
})
function DatatableInitiate(fromDate = '',toDate = ''){
    /* if(type == '')
        var type = $('#type').val();     */
    var type = 'Invoices';    
    
$('#example').DataTable(
    {
        "bDestroy": true,
        "processing": true,
        "serverSide": true,
        'stateSave': true,
        stateSaveParams: function (settings, data) {
            delete data.order;
        },
        "columnDefs": [{ targets: [ 0 ],
            className: "hide_column" 
            }],
        "order": [[ 0, "desc" ]],
        "scrollX": true,
        "ajax":{
            url :"{{url('reports/listbydatatableserversideinreports')}}", // json datasource
            // type: "post",  // method  , by default get
            data : function ( d ) {
                d.typeFileOrInvoice = type;
                d.toDate = toDate;
                d.fromDate = fromDate;
                // d.custom = $('#myInput').val();
                // etc
            },
            error: function(){  // error handling
                $(".example-error").html("");
                $("#example").append('<tbody class="example-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#example_processing").css("display","none");

            }
        },
        "createdRow": function ( row, data, index ) {
        }
        
    });
}
</script>
@stop

