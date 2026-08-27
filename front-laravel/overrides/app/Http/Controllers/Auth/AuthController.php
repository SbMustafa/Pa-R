<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->role === 'benevole') {
            $benevole = $this->api->get('/benevoles/by-user/' . $user->id);

            if (($benevole['statut'] ?? null) === 'refuse') {
                Auth::logout();

                return back()
                    ->withErrors(['email' => 'Votre candidature bénévole a été refusée. Contactez NO MORE WASTE pour plus d\'informations.'])
                    ->onlyInput('email');
            }
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:commercant,benevole',
            'capacites' => 'nullable|array',
            'capacites.*' => 'string|max:50',
            'disponibilites' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        if ($user->role === 'commercant') {
            $this->api->post('/commercants', [
                'user_id' => $user->id,
                'nom' => $user->name,
                'email' => $user->email,
            ]);
        }

        if ($user->role === 'benevole') {
            $this->api->post('/benevoles', [
                'user_id' => $user->id,
                'nom' => $user->name,
                'email' => $user->email,
                'capacites' => implode(', ', $data['capacites'] ?? []),
                'disponibilites' => $data['disponibilites'] ?? '',
            ]);
        }

        Auth::login($user);

        return redirect('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
