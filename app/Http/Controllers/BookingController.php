<?php

namespace App\Http\Controllers;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Services\daysCalculator;
use App\Services\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        $customer = null;
        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->user()->id)->first();
            //            dd($customer);
        }

        return Inertia::render('Booking', [
            'customer' => $customer,
            'locations' => Location::where('status', 1)->get(),
            'canLogin' => (Route::has('login') && ! auth()->check()),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {

        $customerData = $request->validate([
            'customer' => 'required|array',
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.phone_number' => 'nullable|string|max:50',
            'customer.street_name' => 'nullable|string|max:255',
            'customer.street_number' => 'nullable|string|max:50',
            'customer.postal_code' => 'nullable|string|max:20',
            'customer.city' => 'nullable|string|max:255',
            'customer.country' => 'nullable|string|max:255',
            'customer.create_account' => 'required|boolean',
        ]);

        $arrangementData = $request->validate([

            'location_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // An occupied location may never be booked twice. This runs before the customer is
        // created, otherwise a rejected booking would still leave a customer behind.
        if (! Location::isAvailable($arrangementData['location_id'], $arrangementData['start_date'], $arrangementData['end_date'])) {
            throw ValidationException::withMessages([
                'location_id' => __('Deze locatie is in de gekozen periode al bezet.'),
            ]);
        }

        $data = $customerData['customer'];

        // Clean up the data
        $data['email'] = strtolower($data['email']);
        $data['phone_number'] = str_replace(' ', '', $data['phone_number']);

        // if no customer is found, we check on the e-mail and phone number; if they match we use that customer to
        // prevent database polution.
        $customerResult = Customer::createNewCustomer($data);

        $days = (new daysCalculator)
            ->setStart(new \DateTime($arrangementData['start_date']))
            ->setEnd(new \DateTime($arrangementData['end_date']))
            ->calculate();

        $arrangementData['total_price'] = (new PriceCalculator)
            ->setDays($days)
            ->setLocation(Location::findOrFail($arrangementData['location_id']))
            ->calculate();

        // Attempt to update the data, if the id is not found create a new record
        $arrangementData['customer_id'] = $customerResult->id;
        $result = Arrangement::create($arrangementData);
        $arrangementData['id'] = $result->id;

        $arrangementResult = Arrangement::find($arrangementData['id']);

        return redirect()->route('payment', ['guid' => $arrangementResult['guid']]);

    }
}
