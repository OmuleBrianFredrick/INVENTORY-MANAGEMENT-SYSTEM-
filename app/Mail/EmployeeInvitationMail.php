<?php

namespace App\Mail;

use App\Models\EmployeeInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmployeeInvitation $invitation, public string $token)
    {
    }

    public function build()
    {
        return $this->subject('You are invited to UJUZI SHOP MALL')
            ->view('emails.employee-invitation');
    }
}
