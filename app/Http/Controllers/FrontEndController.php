<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class FrontEndController extends Controller
{
    public function index(){
        return Inertia::render('Welcome', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'locations' => Location::where('status', 1)->limit(4)->get(),
        ]);
    }

    public function about()
    {

        return Inertia::render('about', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),

        ]);
    }

    public function contact()
    {

        return Inertia::render('contact', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
        ]);

    }

    public function locations()
    {
        return Inertia::render('locations', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
        ]);
    }
}
