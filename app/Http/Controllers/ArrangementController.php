<?php

namespace App\Http\Controllers;

use App\Models\Arrangement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArrangementController extends Controller
{
    /**
     * @return void
     */
    public function index(): void {}

    /**
     * Updates or creates a new record.
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // validate the data
        $data = $request->validate([
            'id' => 'required|integer',
            'customer_id' => 'required|integer',
            'location_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        // Attempt to update the data, if the id is not found create a new record
        $result = Arrangement::updateOrCreate(['id' => $data['id']], $data);

        return response()->json(['status' => "success!", 'updated_data' => $result]);

    }



    /**
     * @return void
     */
    public function cancel(): void {

    }
}
