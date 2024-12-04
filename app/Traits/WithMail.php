<?php

namespace App\Traits;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

trait WithMail
{
    public function sendMail(string $recipient, Mailable $mailable)
    {
        return Mail::to($recipient)->send($mailable);
    }
}
