<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DefinirMotDePasse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Création d'un compte de connexion par l'administration (commerçant, bénévole).
 * Le mot de passe initial est aléatoire et n'est jamais communiqué : l'utilisateur
 * reçoit un lien d'activation par email et choisit lui-même son mot de passe.
 */
class CreationCompte
{
    /**
     * @return array{user: User, lien_envoye: bool} Le compte est créé même si le mail
     *   ne part pas : l'échec d'envoi ne doit pas interrompre la création de la fiche
     *   (sinon on laisserait un User sans commerçant/bénévole associé). La personne
     *   peut toujours récupérer son accès via « mot de passe oublié ».
     */
    public function creerAvecLienActivation(string $nom, string $email, string $role): array
    {
        $user = User::create([
            'name' => $nom,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'role' => $role,
        ]);

        $token = Password::broker()->createToken($user);
        $lienEnvoye = true;

        try {
            $user->notify(new DefinirMotDePasse($token, nouveauCompte: true));
        } catch (\Throwable $e) {
            Log::error("Envoi du lien d'activation à {$email} impossible : {$e->getMessage()}");
            $lienEnvoye = false;
        }

        return ['user' => $user, 'lien_envoye' => $lienEnvoye];
    }
}
