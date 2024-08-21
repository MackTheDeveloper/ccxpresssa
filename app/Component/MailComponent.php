<?php
namespace App\Component;
use App\Mail\RegistrationMail;
use Illuminate\Support\Facades\Mail;
/**
 * 
 */
class MailComponent
{
	protected $key;
	
	function __construct()
	{
		
	}

	public function sendMail($toEmail = '',$data = array())
	{
		Mail::to($toEmail)->send(new RegistrationMail($data));
		return true;
	}
	
}
?>