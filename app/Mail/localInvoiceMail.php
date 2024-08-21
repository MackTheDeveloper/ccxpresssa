<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class localInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
    public $localInvoiceDetail = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->localInvoiceDetail = $input;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         //pre($this->localInvoiceDetail);
         $email = $this->view('emails.localinvoiceDetail')->subject("Invoice generated")->with('email',$this);
         return $email;
    }
}
