<p>Dear <?php $modelClient = new App\Clients;
        $clientDetail = $modelClient->where('id', $email->localInvoiceDetail['billing_party'])->first();
        echo ucwords(strtolower($clientDetail->company_name)); ?>
</p>
<p>New invoice has been generated successfully.</b></p>
<p><b>No. Dossier/ File No. :</b> {{$email->localInvoiceDetail['file_number']}}</p>
<a href="<?php echo url('/'); ?>/<?php echo  $email->localInvoiceDetail['invoiceAttachment']; ?>">Go to invoice</a>