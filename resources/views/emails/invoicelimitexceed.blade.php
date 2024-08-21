@extends('layouts.general_email_layout')
@section("body_contents")
<div>
	<p>Dear <b>Admin,</b>,</p>
	
	<p>Credit limit exceed of client <?php $modelClient = new App\Clients; $dataClient = $modelClient->getClientData($invoice['bill_to']); echo ucwords(strtolower($dataClient->name));?></b></p>

  <p><b>Available Balance. :</b> {{$dataClient->available_balance}}</p>

  <p><b>Invoice Amount. :</b> {{$invoice['balance_of']}}</p>

	<p><b>No. Dossier/ File No. :</b> {{$invoice['file_no']}}</p>
	<p><b>AWB / BL No. :</b> {{$invoice['awb_no']}}</p>
	<p><b>No facture :</b> {{$invoice['bill_no']}}</p>

	<p><b>Expediteur / Shipper :</b> {{$invoice['shipper']}}</p>
	<p><b>Consignataire / Consignee :</b> {{$invoice['consignee_address']}}</p>
	<p><b>Poids / Weight :</b> {{$invoice['weight']}}</p>
	<p><b>Import / Export :</b> {{$invoice['type_flag']}}</p>
	
		
		
	<p><b>Thanks</b></p>
</div>
   
@stop
