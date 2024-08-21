<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->user['flag'] == 'resetPassword')
        {
        return $this->view('emails.resetpassword')->subject("Reset Password");            
        }elseif($this->user['flag'] == 'registration')
        {
        return $this->view('emails.registraionmail')->subject("Welocme");
        }elseif($this->user['flag'] == 'client-registration')
        {
        return $this->view('emails.client-registraionmail')->subject("Welocme");
        }
    }
}
