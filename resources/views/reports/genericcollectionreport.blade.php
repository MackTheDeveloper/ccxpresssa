@extends('layouts.custom')

@section('title')
Collection Report
@stop


@section('breadcrumbs')
    @include('menus.reports')
@stop


@section('content')
<section class="content-header">
    <h1>Collection Report</h1>
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
        <div class="row" style="margin-bottom:20px">
            <div class="from-date-filter-div filterout col-md-2">
                <input type="text" value="<?php echo date('d-m-Y'); ?>" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
            </div>
            <div class="to-date-filter-div filterout col-md-2">
                <input type="text" value="<?php echo date('d-m-Y'); ?>" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
            </div>
            <div class="col-md-2 typeimpexpdiv">
                <?php echo Form::select('currency',$currency,'',['class'=>'form-control selectpicker','data-live-search' => 'true','id'=>'currency','placeholder'=>'Select Currency']); ?>
            </div>
            
            <button type="submit" name="submit" id="submit" value="submit" class="btn btn-success">Submit</button>
            <a title ="Click here to print"  target="_blank" href="{{url('reports/genericcollectionreport/print/'.date('d-m-Y').'/'.date('d-m-Y'))}}" id="pdfDisbursementLink"><i style="padding-top: 10px;padding-bottom: 10px;" class="fa fa-print btn btn-primary" style=""></i></a>
        </div>

        
        {{ Form::close() }}

            <div class="container-rep">
                    <div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;">Collection Detail</div>
                    <div class="notes" style="padding: 10px;float: left;width: 100%;position: relative;display:none">
                        <div style="float: left;<?php echo (!empty($currencySingle) && $currencySingle->code == 'HTG') ? 'display:block' : 'display:none' ?>" class="col-md-3">
                        <b>HTG : </b><span style="margin-left: 2%" class="totalhtg">{{number_format($totalOfCurrency[3],2)}}</span>
                        </div>
                        <div style="float: left;<?php echo (!empty($currencySingle) && $currencySingle->code == 'USD') ? 'display:block' : 'display:none' ?>" class="col-md-3">
                        <b>USD : </b><span style="margin-left: 2%" class="totalusd">{{number_format($totalOfCurrency[1],2)}}</span>
                        </div>
                        
                        <div style="float: left;display:block;display:none" class="col-md-2">
                        <b>Total in HTG : </b><span style="margin-left: 2%" class="finaltotal">
                            {{number_format($totalOfCurrency['totalInHtg'],2)}}
                        </span>
                        </div>
                    </div>
                        <div class="detail-container">
                            
                            <div id="filterExpenceData">
                            <table class="display nowrap display" style="width:100%" id="example">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>File Number</th>
                                        <th>Receipt No.</th>
                                        <th>Consignee</th>
                                        <th>Currency</th>
                                        <th>Original Amount</th>
                                        <th>Payable Amount</th>
                                        <th>Payment Currency</th>
                                        <th>Paid Amount</th>
                                        <th>Payment Via</th>
                                        <th>Payment Description</th>
                                        <th>Payment Date & Time</th>
                                        <th>Received By</th>
                                    </tr>
                                </thead>
                            <tbody>
                                <?php $totalPayment = 0;$arr = [];?>
                                @foreach($paymentReceivedByCashierNew as $paymentDetail)
                                {{-- @if(!in_array($paymentDetail->invoice_id,$arr)) --}}
                                <?php 
                                    $redLink = route('viewInvoiceDetailsWithCollection',[$paymentDetail->invoice_id]);
                                ?>
                                <tr data-editlink="{{ $redLink }}" id="<?php echo $paymentDetail->invoice_id; ?>" class="edit-row">
                                    <td>{{$paymentDetail->invoice_number}}</td>
                                    <td>{{$paymentDetail->file_number}}</td>
                                    <td>{{$paymentDetail->paymentId}}</td>
                                    <td>{{$paymentDetail->consignee_address}}</td>
                                    <td>
                                        <?php $dataCurrency = App\Currency::getData($paymentDetail->invoiceCurrency); 
                                              echo !empty($dataCurrency->code) ? $dataCurrency->code : "-"; ?>
                                    </td>
                                    <td style="text-align:right">{{$paymentDetail->originalAmount}}</td>
                                    <td style="text-align:right">{{$paymentDetail->amount}}</td>
                                    <td>
                                        <?php $currency = $paymentDetail->exchange_currency ?>
                                        @if($currency != '')
                                            <?php $dataCurrency = App\Currency::getData($paymentDetail->exchange_currency); 
                                                echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?>
                                        @else
                                            {{"-"}}
                                        @endif            	
                                    </td>
                                    <td style="text-align:right">
                                        @if($currency != '' && $paymentDetail->exchange_amount != '')
                                            {{$paymentDetail->exchange_amount}}
                                        @else
                                            {{"-"}}
                                        @endif
                                    </td>
                                    <td>{{$paymentDetail->payment_via}}</td>
                                    <td>
                                        @if($paymentDetail->payment_via_note != '')
                                            {{$paymentDetail->payment_via_note}}
                                        @else
                                            {{"-"}}
                                        @endif
                                    </td>
                                    <td>
                                        <?php echo date("d-m-Y h:i:s", strtotime($paymentDetail->created_at));?>
                                    </td>
                                    <td>{{$paymentDetail->paymentReceivedBy}}</td>
                                </tr>
                                @endforeach
                        </tbody>
                    </table>
                </div>
                        </div>

            </div>

            
        </div>
    </div>

</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
$(document).ready(function() {
    var disbursementLink = $('#pdfDisbursementLink').attr("href");
    $('.datepicker').datepicker({format: 'dd-mm-yyyy',todayHighlight: true,autoclose:true});
    $('#example').DataTable({
        'stateSave': true,
        stateSaveParams: function (settings, data) {
            delete data.order;
        },
        //"ordering": false,
        //"order": [[ 0, "asc" ]],
        "scrollX": true,
        "aaSorting": []
    });

    $('#createInvoiceForm').on('submit', function (event) {
        if($('#currency').val() == '')
        {
            alert('Please select any Currency');
            return false;
        }

        $('#loading').show();
        var urlztnnn = '<?php echo url("reports/genericcollectionreport"); ?>';
        event.preventDefault();
        var fromDate = $('.from-date-filter').val();
        var toDate = $('.to-date-filter').val();
        var currency = $('#currency').val();
        if(currency == '')
            currency = 0;
        var submit = $('#submit').val();
        urlztnnn += '/print/'+fromDate+'/'+toDate+'/'+currency;


        $('#pdfDisbursementLink').attr("href",urlztnnn);


        $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        /* if(id == '')
            var urlztnn = '<?php echo url("reports/getallcustomreportdata"); ?>';
        else */
        var urlztnn = '<?php echo url("reports/genericcollectionreport"); ?>';
        $.ajax({
                url:urlztnn,
                //async:false,
                type:'POST',
                data:{'fromDate':fromDate,'toDate':toDate,'submit':submit,'currency':currency},
                success:function(data) {
                            $('.container-rep').html(data);
                            $('#loading').hide();
                        }
            });
    });
})
</script>
@stop

