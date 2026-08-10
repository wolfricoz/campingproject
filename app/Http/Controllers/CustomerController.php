<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::all();

        return response()->json($customers);
    }

    public function find(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|string|email|max:255',
            'phone_number' => 'required|string|min:10|max:10',
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
        $data['phone_number'] = str_replace(' ', '', $data['phone_number']);

        $result = Customer::createNewCustomer($data);

        return response()->json(['status' => 'success!', 'updated_data' => $result]);

    }
}
