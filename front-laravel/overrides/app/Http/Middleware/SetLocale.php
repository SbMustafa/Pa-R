<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applique la langue choisie par le visiteur (stockée en session). L'association
 * étant implantée à l'étranger (Naples, Porto, Dublin), le site est multilingue.
 */
class SetLocale
{
    public const LANGUES = [
        'fr' => 'Français',
        'en' => 'English',
        'it' => 'Italiano',
        'pt' => 'Português',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $langue = $request->session()->get('langue', config('app.locale'));

        if (array_key_exists($langue, self::LANGUES)) {
            App::setLocale($langue);
        }

        return $next($request);
    }
}
