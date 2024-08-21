@extends('layouts.custom')

@section('title')
Disbursement Report
@stop

<?php if (checkloggedinuserdata() == 'Other') { ?>
    @section('breadcrumbs')
    @include('menus.reports')
    @stop
<?php } ?>


@section('content')
<section class="content-header">
    <h1>Disbursement Report</h1>
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
                <div class="col-md-2 typeimpexpdiv">
                    <?php echo Form::select('accounts', $cashBank, !empty(auth()->user()->default_cashbank_account_for_report) ? auth()->user()->default_cashbank_account_for_report : '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'accounts', 'placeholder' => 'Select Cash/Bank']); ?>
                    <?php if (checkloggedinuserdata() == 'Cashier') { ?>
                        <a style="margin-top:10px;" class="btn btn-success makedefaultcashbankofcashierinreport" href="{{route('makedefaultcashbankofcashierinreport')}}">Make as default Cash/Bank</a>
                    <?php } ?>
                </div>

                <?php if (checkloggedinuserdata() != 'Cashier') { ?>
                    <div class="col-md-2 typeimpexpdiv">
                        <?php echo Form::select('cashier', $cashier, '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'id' => 'cashier', 'multiple' => true, 'data-actions-box' => "true"]); ?>
                    </div>
                <?php } else {  ?>
                    <input type="hidden" id="cashier" value="<?php echo auth()->user()->id; ?>" />
                <?php } ?>
                <div class="from-date-filter-div filterout col-md-2">
                    <input type="text" value="<?php echo date('d-m-Y'); ?>" name="from_date_filter" placeholder=" -- From Date" class="form-control datepicker from-date-filter">
                </div>
                <div class="to-date-filter-div filterout col-md-2">
                    <input type="text" value="<?php echo date('d-m-Y'); ?>" name="to_date_filter" placeholder=" -- To Date" class="form-control datepicker to-date-filter">
                </div>

                <button type="submit" name="submit" id="submit" value="submit" class="btn btn-success">Submit</button>
                <a title="Click here to print" target="_blank" href="{{url('reports/genericdisbursementreport/print/'.date('d-m-Y').'/'.date('d-m-Y'))}}" id="pdfDisbursementLink"><i style="padding-top: 10px;padding-bottom: 10px;" class="fa fa-print btn btn-primary" style=""></i></a>
            </div>


            {{ Form::close() }}

            <div class="container-rep">
                <div id="div_basicdetails" class="notes box-s" style="margin-top: 2%;">Disbursement Detail
                    <?php $totalPayment = 0;
                    $arr = []; ?>
                    @foreach($allExpenseDetails as $expancesDetailCount)
                    {{-- @if(!in_array($expancesDetailCount->expense_id,$arr)) --}}
                    <?php $totalPayment += $expancesDetailCount->amount; ?>
                    <?php array_push($arr, $expancesDetailCount->expense_id) ?>
                    {{-- @endif --}}
                    @endforeach
                </div>

                <div class="notes" style="padding: 10px;float: left;width: 100%;<?php echo (checkloggedinuserdata() != 'Cashier') ? 'display:none' : 'display:block' ?>">
                    <div style="float: left;display:none" class="col-md-3">
                        <b>Total Disbursement : </b><span style="margin-left: 2%" class="totaldisbursement">{{number_format($totalPayment,2)}}</span>
                    </div>
                    <div style="float: left;<?php echo (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'USD') ? 'display:block' : 'display:none' ?>" class="col-md-4 usddiv">
                        <?php if (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'USD') {
                            $cashBankName = $cashBankSingle->name; ?>
                            <b><?php echo $cashBankName; ?> (USD) : </b><span style="margin-left: 2%" class="totaldisbursementUsd">{{number_format($allTotalExpenseOfUSDCount,2)}}</span>
                        <?php } ?>
                    </div>
                    <div style="float: left;<?php echo (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'HTG') ? 'display:block' : 'display:none' ?>" class="col-md-4 htgdiv">
                        <?php if (!empty($cashBankSingle) && $cashBankSingle->currencyCode == 'HTG') {
                            $cashBankName = $cashBankSingle->name; ?>
                            <b><?php echo $cashBankName; ?> (HTG) : </b><span style="margin-left: 2%" class="totaldisbursementHtg">{{number_format($allTotalExpenseOfHtgCount,2)}}</span>
                        <?php } ?>
                    </div>
                </div>
                <div class="detail-container">

                    <div id="filterExpenceData">
                        <table class="display nowrap display" style="width:100%" id="example">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>File Number</th>
                                    <th>Voucher Number</th>
                                    <th>Currency</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Disbursed Note</th>
                                    <th>Paid To</th>
                                    <th>Disbursed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $arr = [];
                                $totalPayment = 0; ?>

                                @foreach($allExpenseDetails as $expancesDetail)
                                {{-- @if(!in_array($expancesDetail->expense_id,$arr)) --}}
                                <tr>
                                    <td><?php echo  !empty($expancesDetail->disbursed_datetime) ? date("d-m-Y", strtotime($expancesDetail->disbursed_datetime)) : '-'; ?></td>
                                    <td>{{$expancesDetail->file_number}}</td>
                                    <td>{{$expancesDetail->voucher_number}}</td>
                                    <td><?php $dataCurrency = App\Vendors::getDataFromPaidTo($expancesDetail->expense_id);
                                        echo !empty($dataCurrency) ? $dataCurrency->code : '-';  ?></td>
                                    <td style="text-align:right">{{number_format($expancesDetail->amount,2)}}</td>
                                    <td>{{$expancesDetail->description}}</td>
                                    <td>{{$expancesDetail->expense_request_status_note}}</td>
                                    <td>
                                        <?php
                                        $paid_to = DB::table('vendors')->where('id', $expancesDetail->paid_to)->first();
                                        echo !empty($paid_to) ? $paid_to->company_name : '-';
                                        ?>
                                    </td>
                                    <td>{{$expancesDetail->name}}</td>
                                </tr>
                                <?php array_push($arr, $expancesDetail->expense_id) ?>

                                {{-- @endif --}}
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
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            autoclose: true
        });
        $('#example').DataTable({
            'stateSave': true,
            stateSaveParams: function(settings, data) {
                delete data.order;
            },
            //"ordering": false,
            "scrollX": true,
            "aaSorting": []
        });

        $('#createInvoiceForm').on('submit', function(event) {

            if ($('#accounts').val() == '') {
                alert('Please select any Cash/Bank account');
                return false;
            }

            $('#loading').show();
            var urlztnnn = '<?php echo url("reports/genericdisbursementreport"); ?>';
            event.preventDefault();
            var fromDate = $('.from-date-filter').val();
            var toDate = $('.to-date-filter').val();
            var cashBank = $('#accounts').val();
            if (cashBank == '')
                cashBank = 0;
            var cashier = $('#cashier').val();
            if (cashier == '')
                cashier = 0;
            var submit = $('#submit').val();
            urlztnnn += '/print/' + fromDate + '/' + toDate + '/' + cashBank + '/' + cashier;


            $('#pdfDisbursementLink').attr("href", urlztnnn);


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            /* if(id == '')
                var urlztnn = '<?php echo url("reports/getallcustomreportdata"); ?>';
            else */
            var urlztnn = '<?php echo url("reports/genericdisbursementreport"); ?>';
            $.ajax({
                url: urlztnn,
                //async:false,
                type: 'POST',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'cashBank': cashBank,
                    'cashier': cashier,
                    'submit': submit
                },
                success: function(data) {
                    $('.container-rep').html(data);
                    $('#loading').hide();
                }
            });
        });

        $('#accounts').change(function() {
            var url = '<?php echo url("reports/makedefaultcashbankofcashierinreport"); ?>';
            url += '/' + $(this).val();
            $('.makedefaultcashbankofcashierinreport').attr("href", url);
        })
    })
</script>
@stop