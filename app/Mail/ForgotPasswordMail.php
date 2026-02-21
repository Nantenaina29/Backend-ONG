<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $password; // Ity no hitahiry ilay password vaovao

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Réinitialisation de votre mot de passe')
                    ->html("<p>Bonjour,</p>
                            <p>Votre mot de passe a été réinitialisé. Voici votre nouveau mot de passe : 
                            <strong style='color: #10b981;'>{$this->password}</strong></p>
                            <p>Veuillez le changer dès votre connexion.</p>");
    }
}