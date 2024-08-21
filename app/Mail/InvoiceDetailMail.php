<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoiceDetailMail extends Mailable
{
    use Queueable, SerializesModels;
    public $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice)
    {
         $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->invoice['flag'] == 'invoice-sent')
        {
            $email =  $this->view('emails.invoicedetails')->subject("Invoice generated");
            $email->attach($this->invoice['invoiceAttachment']);
            return $email;
        }
        else if($this->invoice['flag'] == 'limit-exceed')
        {
            return $this->view('emails.invoicelimitexceed')->subject("Notification for limit exceed of client");
        }
    }
}
