<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class upsCommissionMail extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($commissionAttachment)
    {
        $GLOBALS = $commissionAttachment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = $GLOBALS['flag'] == 'aeroPost' ? 'Aeropost Commission' : ($GLOBALS['flag'] == 'Free Domicile Report' ? 'Free Domicile' : 'Ups Commission');
        $email = $this->view('emails.upsCommissionMail')->subject($subject)->attach($GLOBALS['attachment']);
        return $email;
    }
}
