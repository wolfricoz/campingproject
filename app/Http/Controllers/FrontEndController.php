<?php

namespace App\Http\Controllers;

use App\Mail\GeneralMailMail;
use App\Models\Location;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class FrontEndController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'locations' => Location::where('status', 1)->where('is_advertised', true)->limit(4)->get(),
            'news' => News::query()->published()->latest()->limit(5)->get(),
        ]);
    }

    public function about()
    {

        return Inertia::render('About', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function contact()
    {

        return Inertia::render('Contact', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
        ]);

    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        Mail::to(config('mail.contact_email'))->send(new GeneralMailMail('Contact Aanvraag: '.$data['title'], $data['message']));

        return back();
    }

    public function locations()
    {
        return Inertia::render('Locations/Index', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'locations' => Location::where('status', 1)->get(),

        ]);
    }
}
