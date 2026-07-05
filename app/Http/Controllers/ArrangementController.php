<?php

namespace App\Http\Controllers;

use App\Models\Arrangement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Enums\ArrangementStatus;
use Inertia\Inertia;
use Inertia\Response;


class ArrangementController extends Controller
{
    /**
     * Gets a list of arrangements, if a status is provided it will only return those with that status.
     * @param Request $request
     * @param string|null $status
     * @return Response
     */
    public function index(Request $request, string $status = null) {
        // Set the dates for the beginning and end of the month
//        $start = Carbon::now()->startOfMonth();
//        $end = Carbon::now()->endOfMonth();
        if ($status && ArrangementStatus::tryFrom($status) === null) {
            abort(404, 'Status not found');
        }
        // We fetch the arrangements for the current month.
        $arrangements = Arrangement::with('customer', 'location')
            ->where(function (Builder $query) use ($status) {
                if (!$status) {
                    return $query;
                }
                return $query->where('booking_status', $status);
            })
            ->where('status', '=',1)
            ->get();



        //dd($arrangements);
        return Inertia::render('Arrangements/index', [
            'arrangements' => $arrangements,
        ]);


    }

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
            $data['id'] = $result->id;
        } else {
            Arrangement::updateOrCreate(['id' => $data['id']], $data);
        }

        $result = Arrangement::find($data['id']);

        return response()->json(['status' => "success!", 'updated_data' => $result]);

    }



    /**
     * Changes the status of the reservation
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
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
