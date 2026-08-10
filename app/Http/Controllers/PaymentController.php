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
     * Shows the payment page, unless the arrangement has already been paid for.
     */
    public function index(Request $request, string $guid): RedirectResponse|Response
    {
        $arrangement = Arrangement::where('guid', $guid)->firstOrFail();

        if ($arrangement->payment_received) {
            return $this->redirectAfterPayment(__('Deze boeking is al betaald.'));
        }

        return Inertia::render('Payment', [
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
            'paid' => $arrangement->payment_received,
            'guid' => $guid,
        ]);
    }

    /**
     * Registers the payment for an arrangement and confirms it by mail.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'guid' => 'required|string',
        ]);

        $arrangement = Arrangement::where('guid', $data['guid'])->firstOrFail();

        if ($arrangement->payment_received) {
            return $this->redirectAfterPayment(__('Deze boeking is al betaald.'));
        }

        $arrangement->update(['payment_received' => true]);

        Mail::to($arrangement->customer->email)->send(new PaymentReceivedMail($arrangement));

        return $this->redirectAfterPayment();
    }

    /**
     * Logged in customers continue in their dashboard, guests return to the homepage.
     */
    private function redirectAfterPayment(?string $message = null): RedirectResponse
    {
        $route = auth()->check() ? 'dashboard' : 'home';

        return redirect()->route($route)->with('success', $message ?? __('Betaling successvol ontvangen!'));
    }
}
