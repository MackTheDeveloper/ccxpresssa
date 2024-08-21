@extends('layouts.general_email_layout')
@section("body_contents")
<div>
	<p>Dear <b><?php echo ucwords(strtolower($user['name']));?></b>,</p>
	<p>Your password has been changed successfully.</b></p>
	<p>You can access your account using below credentials</p>
	<p><b>Email :</b> {{$user['email']}}</p>
	<p><b>Password :</b> {{$user['plain_password']}}</p>
	<p><a href="{{url('home')}}"><b>Click Here</b></a> to access.</p>
	<p><b>Thanks</b></p>
</div>
   
@stop
