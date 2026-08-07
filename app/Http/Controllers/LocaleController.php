<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Onthoudt de taalkeuze in de sessie. SetLocale leest hem bij elk volgend request.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('app.available_locales'), true), 404);

        $request->session()->put('locale', $locale);

        return back();
    }
}
