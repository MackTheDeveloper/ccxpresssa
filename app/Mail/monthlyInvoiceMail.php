<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class monthlyInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
public $localInvoiceDetail = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoiceData)
    {
        $this->localInvoiceDetail = $invoiceData; 

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {       
        //pre($this->localInvoiceDetail['invoiceAttachment']);
         $email = $this->view('emails.monthlyInvoiceDetail')->with('email',$this)->attach($this->localInvoiceDetail['invoiceAttachment']);
        return $email;
    }
}
