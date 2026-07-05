<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * @return Response
     */
    public function index(): \Inertia\Response
    {
        $customer = Customer::where('user_id', auth()->user()->id)->first();

        return Inertia::render('booking', [
            'customer' => $customer,
            'locations' => Location::where('status', 1)->get(),
            'canLogin' => (Route::has('login') && !auth()->check()),
            'canRegister' => Route::has('register'),
        ]);
    }


}
