<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;use Illuminate\Mail\Mailable;use Illuminate\Queue\SerializesModels;
class LoginOtpMail extends Mailable {use Queueable,SerializesModels;public function __construct(public string $code,public string $name,public int $expiresInMinutes=5){}public function build(){return $this->subject('UJUZI SHOP MALL sign-in verification code')->view('emails.login-otp');}}
