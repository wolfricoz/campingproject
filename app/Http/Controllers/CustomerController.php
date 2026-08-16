<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * The columns the booking screen needs. Everything else stays behind, so no
     * more personal data leaves the server than the screen actually shows.
     *
     * @var list<string>
     */
    private const VISIBLE_COLUMNS = [
        'id',
        'name',
        'email',
        'phone_number',
        'street_name',
        'street_number',
        'postal_code',
        'city',
        'country',
        'user_id',
    ];

    /**
     * Lists the customers for the booking screen. Guarded by `view customers`.
     */
    public function index(): JsonResponse
    {
        $customers = Customer::query()
            ->select(self::VISIBLE_COLUMNS)
            ->orderBy('name')
            ->get();

        return response()->json($customers);
    }

    /**
     * Looks a customer up on e-mail and phone number for the desk.
     *
     * The phone number is normalised before it is validated, so the desk may
     * type it the way it is printed on the reservation: with or without spaces,
     * dashes or a country code.
     */
    public function find(Request $request): JsonResponse
    {
        $request->merge([
            'phone_number' => Customer::normalisePhoneNumber($request->string('phone_number')->toString()),
        ]);

        $data = $request->validate([
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|digits_between:10,15',
        ]);
        $customer = Customer::findByEmailAndPhoneNumber($data['email'], $data['phone_number']);

        return response()->json($customer);

    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer' => 'required|array',
            'customer.id' => 'required|integer|max:100000',
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
        $data = $data['customer'];
        $data['email'] = strtolower($data['email']);

        $result = Customer::createNewCustomer($data);

        return response()->json(['status' => 'success!', 'updated_data' => $result]);

    }
}
