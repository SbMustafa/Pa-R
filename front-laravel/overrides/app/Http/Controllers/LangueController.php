<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LangueController extends Controller
{
    public function changer(Request $request, string $langue): RedirectResponse
    {
        if (array_key_exists($langue, SetLocale::LANGUES)) {
            $request->session()->put('langue', $langue);
        }

        return redirect()->back();
    }
}
