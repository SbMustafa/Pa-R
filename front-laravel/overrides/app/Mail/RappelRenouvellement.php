<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelRenouvellement extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array  $commercant  fiche renvoyée par l'API Go
     * @param  int  $joursRestants  négatif si l'échéance est dépassée
     */
    public function __construct(
        public array $commercant,
        public int $joursRestants,
    ) {
    }

    public function envelope(): Envelope
    {
        $sujet = $this->joursRestants < 0
            ? 'Votre adhésion NO MORE WASTE a expiré'
            : 'Votre adhésion NO MORE WASTE arrive à échéance';

        return new Envelope(subject: $sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.rappel-renouvellement');
    }
}
