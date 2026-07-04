<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function Laravel\Prompts\password;

class CustomerController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $customers = Customer::all();
        return response()->json($customers);
    }

    /**
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // validate the data
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

        $customer = Customer::find($data['id'] ?? 0);
        if ($data['create_account'] && $customer && empty($customer->user_id)) {
            // TODO: potentially remove password, and just send an e-mail to reset password.
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt(Str::random(21)),
            ]);
            $data['user_id'] = $user->id;
        }
        unset($data['create_account']);
        $result = Customer::updateOrCreate(['id' => $data['id']], $data);
        return response()->json(['status' => "success!", 'updated_data' => $result]);

    }
}
