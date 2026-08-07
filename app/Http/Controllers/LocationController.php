<?php

namespace App\Http\Controllers;

use App\Models\Location;
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

        return Inertia::render('locations/index', ['locations' => $locations]);
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
}
