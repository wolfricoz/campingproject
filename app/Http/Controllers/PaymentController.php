<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceivedMail;
use App\Models\Arrangement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * @return RedirectResponse|Response
     */
    public function index(Request $request, string $guid)
    {
        $arrangement = Arrangement::where('guid', $guid)->get()->first();
        // Prevent the user from paying twice; while we'd love to get paid multiple times.. this would be a legal
        // problem.
        //        if ($arrangement->payment_received) {

        //            return redirect()->route('dashboard');
        //        }

        return Inertia::render('Payment', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'paid' => $arrangement->payment_received,
            'guid' => $guid,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // This route is called when payment is confirmed
        $data = $request->validate([
            'guid' => 'required|string',
        ]);

        // update arrangement to show payment was made
        $arrangement = Arrangement::where('guid', $data['guid'])->first();

        $arrangement->update(['payment_received' => true]);

        // Send Email
        Mail::to($arrangement->customer->email)->send(new PaymentReceivedMail($arrangement));

        // If the user is logged in, return to dashboard.
        if (auth()->check()) {
            return redirect()->route('dashboard')->with('success', 'Betaling successvol ontvangen!');
        }

        // if its a guest, return to home.
        return redirect()->route('home')->with('success', 'Betaling successvol ontvangen!');

    }
}
