<?php

namespace App\Http\Controllers;

use App\Models\Arrangement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Enums\ArrangementStatus;




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
        if ($data['id'] === 0) {
            unset($data['id']);
            $result = Arrangement::create($data);
        } else {
            $result = Arrangement::updateOrCreate(['id' => $data['id']], $data);
        }

        return response()->json(['status' => "success!", 'updated_data' => $result]);

    }



    /**
     * Changes the status of the reservation
     * @return JsonResponse
     */
    public function update(): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'status' => 'required|string',
        ]);
        if(!ArrangementStatus::tryFrom($data['status']) !== null){
            return response()->json(['status' => "Status does not exist."]);
        }
        $result = Arrangement::update(['id' => $data['id']], $data);
        return response()->json(['status' => "success!", 'updated_data' => $result]);

    }
}
