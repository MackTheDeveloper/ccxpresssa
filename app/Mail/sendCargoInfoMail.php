<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendCargoInfoMail extends Mailable
{
    use Queueable, SerializesModels;
    public $localInvoiceDetail = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emaildata)
    {
         $this->localInvoiceDetail = $emaildata;        
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        $email = $this->view('emails.cargoInfo')->subject("Invoice generated")->with('email',$this)->attach($this->localInvoiceDetail['invoiceAttachment'],["as"=>"invoice.pdf"]);
        return $email;
    }
}
