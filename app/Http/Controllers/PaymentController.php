<?php

namespace App\Http\Controllers;

use App\Models\Arrangement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * @param Request $request
     * @param string $guid
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function index(Request $request, string $guid)
    {
        $arrangement = Arrangement::where('guid', $guid)->get();
        // Prevent the user from paying twice; while we'd love to get paid multiple times.. this would be a legal
        // problem.
        if ($arrangement->payment_received) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('payment', [
            'canLogin' => (Route::has('login') && !auth()->check()),
            'canRegister' => Route::has('register'),
            'guid' => $guid,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, string $guid): \Illuminate\Http\RedirectResponse
    {
        // This route is called when payment is confirmed

        // update arrangement to show payment was made
        Arrangement::where('guid', $guid)->update(['payment_received' => true]);

        // Send Email
        return redirect()->route('dashboard');

    }
}
