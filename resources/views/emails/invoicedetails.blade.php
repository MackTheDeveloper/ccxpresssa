	<p>Dear <b><?php $modelClient = new App\Clients; $dataClient = $modelClient->getClientData($invoice['bill_to']); echo ucwords(strtolower($dataClient->name));?></b>,</p>
	<p>New invoice has been generated successfully.</b></p>
  <p><b>No. Dossier/ File No. :</b> {{$invoice['file_no']}}</p>
  <a href="<?php echo url('/'); ?>/<?php echo $invoice['invoiceAttachment']; ?>">Go to invoice</a>
	<p>Plese find attached invoice copy.</p>

	
