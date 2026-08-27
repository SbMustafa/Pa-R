<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail contenant le lien de définition du mot de passe. Sert à l'activation d'un
 * compte créé par l'admin ($nouveauCompte = true) comme au « mot de passe oublié ».
 */
class DefinirMotDePasse extends Notification
{
    public function __construct(
        public string $token,
        public bool $nouveauCompte = false,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lien = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutes = config('auth.passwords.users.expire', 60);

        if ($this->nouveauCompte) {
            return (new MailMessage)
                ->subject('Votre compte NO MORE WASTE a été créé')
                ->greeting('Bienvenue chez NO MORE WASTE')
                ->line("Un compte vient d'être créé pour vous par l'association.")
                ->line('Pour l\'activer, définissez votre mot de passe :')
                ->action('Définir mon mot de passe', $lien)
                ->line("Ce lien est valable {$minutes} minutes.")
                ->line("Si vous n'êtes pas concerné par cette création de compte, ignorez ce message.")
                ->salutation('L\'équipe NO MORE WASTE');
        }

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->greeting('Bonjour')
            ->line('Vous avez demandé la réinitialisation de votre mot de passe.')
            ->action('Réinitialiser mon mot de passe', $lien)
            ->line("Ce lien est valable {$minutes} minutes.")
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.")
            ->salutation('L\'équipe NO MORE WASTE');
    }
}
