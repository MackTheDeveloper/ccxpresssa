<h4>Hello {{$email->localInvoiceDetail['company_name']}}</h4>
<p>This is your monthly invoice.Please check it out and try to pay as soon as possible.</p>
<p><b>Your pending payment is :</b>{{$email->localInvoiceDetail['total']}}</p>