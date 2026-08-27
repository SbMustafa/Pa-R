<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Définition et réinitialisation du mot de passe par lien envoyé par email.
 * Sert aussi bien à l'activation d'un compte créé par l'admin (l'admin ne voit
 * jamais le mot de passe) qu'au « mot de passe oublié » classique.
 */
class PasswordController extends Controller
{
    public function showDemande(): View
    {
        return view('auth.mot-de-passe-oublie');
    }

    public function envoyerLien(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            // L'envoi est synchrone : si le SMTP est mal configuré ou injoignable,
            // l'exception remonterait jusqu'à une page 500 — ce qui, en plus de
            // casser la page, révélerait que le compte existe (une adresse inconnue,
            // elle, ne déclenche aucun envoi). On journalise et on répond comme
            // d'habitude ; l'administrateur retrouve la cause dans les logs.
            Log::error("Envoi du lien de réinitialisation impossible : {$e->getMessage()}");
        }

        // Réponse volontairement identique que l'adresse existe ou non,
        // pour ne pas révéler quels comptes sont enregistrés.
        return back()->with('success', 'Si un compte existe pour cette adresse, un lien vient d\'être envoyé.');
    }

    public function showFormulaire(Request $request, string $token): View
    {
        return view('auth.definir-mot-de-passe', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function enregistrer(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $statut = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($statut !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($statut)]);
        }

        return redirect()->route('login')->with('success', 'Mot de passe défini, vous pouvez vous connecter.');
    }
}
