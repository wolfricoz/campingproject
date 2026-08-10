<?php

namespace App\Http\Controllers;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use App\Models\Location;
use App\Services\DaysCalculator;
use App\Services\PriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ArrangementController extends Controller
{
    /**
     * Gets a list of arrangements, if a status is provided it will only return those with that status.
     */
    public function index(Request $request, ?string $status = null): Response
    {
        if ($status && ArrangementStatus::tryFrom($status) === null) {
            abort(404, 'Status not found');
        }

        $arrangements = Arrangement::with('customer', 'location')
            ->where(function (Builder $query) use ($status) {
                if (! $status) {
                    return $query;
                }

                return $query->where('booking_status', $status);
            })
            ->where('status', '=', 1)
            ->get();

        return Inertia::render('Admin/Arrangements/Index', [
            'arrangements' => $arrangements,
        ]);

    }

    /**
     * Updates or creates a new record.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer',
            'customer_id' => 'required|integer',
            'location_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $ignoreArrangementId = $data['id'] === 0 ? null : $data['id'];

        if (! Location::isAvailable($data['location_id'], $data['start_date'], $data['end_date'], $ignoreArrangementId)) {
            throw ValidationException::withMessages([
                'location_id' => __('Deze locatie is in de gekozen periode al bezet.'),
            ]);
        }

        $days = (new DaysCalculator)
            ->setStart(new \DateTime($data['start_date']))
            ->setEnd(new \DateTime($data['end_date']))
            ->calculate();

        $data['total_price'] = (new PriceCalculator)
            ->setDays($days)
            ->setLocation(Location::findOrFail($data['location_id']))
            ->calculate();

        if ($data['id'] === 0) {
            unset($data['id']);
            $result = Arrangement::create($data);
            $data['id'] = $result->id;
        } else {
            Arrangement::updateOrCreate(['id' => $data['id']], $data);
        }

        $result = Arrangement::find($data['id']);

        return response()->json(['status' => 'success!', 'updated_data' => $result]);

    }

    /**
     * Changes the status of the reservation
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:arrangements,id',
            'status' => ['required', Rule::enum(ArrangementStatus::class)],
        ]);

        $result = Arrangement::findOrFail($data['id']);
        $result->update(['booking_status' => $data['status']]);

        return response()->json(['status' => 'success!', 'updated_data' => $result]);

    }

    /**
     * Calculates the amount of nights that will be charged for the given period.
     */
    public function calculateDays(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $days = (new DaysCalculator)
            ->setStart(new \DateTime($data['start_date']))
            ->setEnd(new \DateTime($data['end_date']))
            ->calculate();

        return response()->json(['days' => $days]);
    }

    /**
     * Calculates the total price for the given location and amount of nights.
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'days' => 'required|integer|min:1',
        ]);
        $price = (new PriceCalculator)
            ->setDays($data['days'])
            ->setLocation(Location::findOrFail($data['location_id']))
            ->calculate();

        return response()->json(['price' => $price]);
    }
}
