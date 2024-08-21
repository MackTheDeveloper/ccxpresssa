<p><b>Invoice Info.</b></p>
<p><b>No. Dossier/ File No. :</b> {{$email->localInvoiceDetail['file_number']}}</p>
<a href="<?php echo url('/'); ?>/<?php echo  $email->localInvoiceDetail['invoiceAttachment'];?>">Go to invoice</a>