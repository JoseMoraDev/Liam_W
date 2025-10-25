<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuperarPasswd extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;

    public function __construct($token)
    {
        // URL apunta al frontend
        $this->resetUrl = "http://localhost:3000/reset-password/{$token}";
    }

    public function build()
    {
        return $this->subject('Recupera tu contraseña')
            ->view('recuperar-passwd')
            ->with(['resetUrl' => $this->resetUrl]);
    }
}
