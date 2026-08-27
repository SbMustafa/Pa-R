<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PlanningBenevole extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $benevole,
        public array $affectations,
        public int $jours,
        protected string $contenuFichier,
        protected string $nomFichier,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Votre planning NO MORE WASTE');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.planning-benevole');
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->contenuFichier, $this->nomFichier)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
