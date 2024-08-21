<?php use App\Currency;?>
@extends('layouts.custom')
@section('title')
Invoice Details
@stop

@section('breadcrumbs')
    @include('menus.reports')
@stop

@section('content')
<section class="content-header" style="    display: block;position: relative;top: 0px;">
	<h1 style="font-size: 20px !important;margin-top: 0px;font-weight: 600;">Invoice Details</h1>
</section>
<section class="content editupscontainer">
<div class="box box-success">
	<div class="box-body">
		@foreach($details as $details)

	<!-- <div class="row" style="background-color: #00a75f;color: white;margin-right: 28%"><h5 style="padding-left: 10%">Basic Details</h5></div> -->
		<div id="div_basicdetails" class="notes box-s" style="margin-top:12px;margin-bottom:-15px;" >Basic Details</div>
		<div class="detail-container">
			<div class="row" style="margin-bottom: 10px">
				<div class="col-md-3" style="">
					<span class="viewblk1">Invoice Number:</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk2 viewblk2full">{{$details->bill_no}}</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk1">File Number:</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk2 viewblk2full">{{$details->file_no}}</span>
				</div>
		
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-md-3" style="">
					<span class="viewblk1">Date:</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk2 viewblk2full"><?php echo date('d-m-Y',strtotime($details->date)) ?></span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk1">Billing Party:</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk2 viewblk2full"><?php $dataUser = app('App\Clients')->getClientData($details->bill_to); echo !empty($dataUser->company_name) ? $dataUser->company_name : "-";?></span>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-md-3" style="">
					<span class="viewblk1">Currency:</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk2 viewblk2full"><?php $dataCurrency = Currency::getData($details->currency); 
                            echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?></span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk1">Total Amount:</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk2 viewblk2full">{{number_format($details->total,2)}} <?php $totalAmount = number_format($details->total,2);?></span>
				</div>
			</div>
			<div class="row" style="margin-bottom: 10px">
				<div class="col-md-3" style="">
					<span class="viewblk1">Consignee:</span>
				</div>
				<div class="col-md-3" style="">
                <span class="viewblk2 viewblk2full">{{$details->consignee_address}}</span>
				</div>
				<div class="col-md-3" style="">
					<span class="viewblk1">Created By:</span>
				</div>
				<div class="col-md-3">
					<span class="viewblk2 viewblk2full"><?php $dataUser = app('App\User')->getUserName($details->created_by); 
                            echo !empty($dataUser->name) ? $dataUser->name : "-";?></span>
				</div>
			</div>
			<div class="row" style="">
				<div class="col-md-3" style="">
					<span class="viewblk1">Status:</span>
				</div>
				<div class="col-md-3" style="">
					<span style = "<?php echo ($details->payment_status == 'Paid') ? 'color:green;' : 'color:red;'; ?>">{{$details->payment_status}}</span>
                </div>
                <div class="col-md-3" style="">
                        <span class="viewblk1">TCA:</span>
                    </div>
                    <div class="col-md-3" style="">
                        <span><?php echo (!empty($details->tca) && $details->tca != '0.00') ? $details->tca : 'N/A'; ?></span>
                </div>
			</div>
		</div>
    @endforeach
    
    <div id="div_basicdetails" class="notes box-s" style="margin-top: 2%">Invoice Item Details</div>
    <div class="detail-container">
            <table id="example1" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $dataInvoiceDetails  = DB::table('invoice_item_details')->where('invoice_id',$details->id)->get();
                            $dataInvoiceDetails = json_decode(json_encode($dataInvoiceDetails));
                            foreach($dataInvoiceDetails as $k => $v) {    ?>
                            <tr>
                                <td>
                                    <?php echo $v->fees_name_desc; ?></div>
                                </td>
                                <td>
                                    <?php echo number_format($v->quantity,2); ?></div>
                                </td>
                                <td>
                                    <?php echo number_format($v->unit_price,2); ?></div>
                                </td>
                                <td ">
                                    <?php echo number_format($v->total_of_items,2); ?></div>
                                </td>
                            </tr>
                        <?php  }  ?>
                    </tbody>
                </table>
    </div>

    <div id="div_basicdetails" class="notes box-s" style="margin-top: 2%">Collection Details
        <?php 
            if(!empty($details->cargo_id) && empty($details->housefile_module))
				$urlPaymentReceipt =  route('printreceiptofinvoicepayment',[$details->id,'invoice','cargo']);
			else if(!empty($details->housefile_module) && $details->housefile_module == 'cargo')
                $urlPaymentReceipt =  route('printreceiptofinvoicepayment',[$details->id,'invoice','housefile']);	
            else if(!empty($details->ups_id))
                $urlPaymentReceipt =  route('printreceiptofinvoicepayment',[$details->id,'invoice','ups']);
            else if(!empty($details->aeropost_id))
                $urlPaymentReceipt =  route('printreceiptofinvoicepayment',[$details->id,'invoice','aeropost']);
            else if(!empty($details->ccpack_id))
                $urlPaymentReceipt =  route('printreceiptofinvoicepayment',[$details->id,'invoice','ccpack']);
        ?>
        <a target="_blank" class="btn" style="color:#fff;background-color: #3f7b30;border-color: #3f7b30;float:right;height: 30px;padding-top: 3px" href="{{$urlPaymentReceipt}}">Payment Receipt</a>
    </div>
    <div class="notes" style="padding: 10px;float: left;width: 100%;position: relative;">
        <div class="col-md-4">
            <b>HTG : </b><span style="margin-left: 2%" class="totalhtg">{{number_format($totalOfCurrency[3],2)}}</span>
        </div>
        <div class="col-md-4">
            <b>USD : </b><span style="margin-left: 2%" class="totalusd">{{number_format($totalOfCurrency[1],2)}}</span>
        </div>
        <div class="col-md-4" style="display:none">
            <b>Total : </b><span style="margin-left: 2%" class="totalusd">
                {{number_format($totalOfCurrency['total'],2)}} HTG
        </span>
        </div>
    </div>

	<div class="detail-container">
		
		<?php $count = 1;?>
		
		<table id="example" class="display nowrap" style="width:100%">
			<thead>
					<tr>
                        <th>Receipt No.</th>
						<th>Amount</th>
						<th>Exchange Currency</th>
						<th>Exchange Amount</th>
						<th>Payment Via</th>
						<th>Payment Description</th>
                        <th>Date & Time</th>
                        <th>Received By</th>
					</tr>
				</thead>
			<tbody>
		@foreach($paymentDetail as $paymentDetail)
					
					<tr>
                        <td>{{$paymentDetail->paymentId}}</td>
						<td>{{$paymentDetail->amount}}</td>
						<td>
							@if($paymentDetail->exchange_currency != '')
								<?php $dataCurrency = Currency::getData($paymentDetail->exchange_currency); 
                            		echo !empty($dataCurrency->code) ? $dataCurrency->code : "-";?>
                            @else
                            	{{'-'}}
                            @endif
                        </td>
						<td style="text-align:right">
							<?php $currency = $paymentDetail->exchange_currency; ?>
							@if($currency != '' && $paymentDetail->exchange_amount != '')
								{{$paymentDetail->exchange_amount}}
							@else
								{{"-"}}
							@endif
						</td>
                       
                        <td>
                        	{{$paymentDetail->payment_via}}
                        </td>
                        <td>
                            @if($paymentDetail->payment_via_note != '')
                                {{$paymentDetail->payment_via_note}}
                            @else
                                {{"-"}}
                            @endif
                        </td>
                        <td>
					        <?php echo date('d-m-Y h:i:s',strtotime($paymentDetail->created_at));?>
                        </td>
                        <td>{{$paymentDetail->paymentReceivedBy}}</td>
                        
					</tr>
						
										<!-- <div id="div_basicdetails" class="notes box-s" style="margin: 2%">
						Exchange Currency Detail
					</div> -->
			<?php $count++;?>

		@endforeach
	</tbody>
	</table>

</div>
</div>
</div>
</section>
@endsection
@section('page_level_js')
<script type="text/javascript">
	$('#example').DataTable(
    {
        "order": [[ 0, "asc" ]],
        "scrollX": true,
    });
    $('#example1').DataTable(
    {
        "order": [[ 0, "asc" ]],
        "scrollX": true,
    });
</script>
@stop