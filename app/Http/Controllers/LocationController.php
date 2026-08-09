<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::where('status', 1)->get();

        return response()->json($locations);
    }

    public function adminIndex()
    {
        $locations = Location::where('status', 1)->get();

        return Inertia::render('Admin/Locations/Index', ['locations' => $locations]);
    }

    public function store()
    {
        $data = request()->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'bedrooms' => 'required|integer|min:0',
            'size' => 'nullable|numeric|min:0|max:999999.99',
            'price_per_night' => 'nullable|numeric|min:0|max:999999.99',
            'has_electricity' => 'required|boolean',
            'has_water' => 'required|boolean',
            'has_shade' => 'required|boolean',
            'status' => 'required|integer|in:0,1',
            'is_advertised' => 'required|boolean',
        ]);
        $location = Location::updateOrCreate(
            ['id' => $data['id']],
            $data
        );

        return response()->json(['success' => true, 'updated_data' => $location]);

    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $arrangementData = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'arrangement_id' => 'nullable|integer',
        ]);
        $today = date('Y-m-d H:i:s');

        if ($arrangementData['start_date'] < $today) {
            return response()->json(['success' => false, 'message' => 'Je kan geen datum in het verleden kiezen']);
        }

        return response()->json([
            'available' => Location::isAvailable(
                $arrangementData['location_id'],
                $arrangementData['start_date'],
                $arrangementData['end_date'],
                $arrangementData['arrangement_id'] ?? null,
            ),
            'message' => 'Deze locatie is al in gebruik op deze datum!',
        ]);
    }
}
