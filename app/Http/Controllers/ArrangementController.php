<?php

namespace App\Http\Controllers;

use App\Enums\ArrangementStatus;
use App\Enums\PaymentMethod;
use App\Mail\PaymentReceivedMail;
use App\Models\Arrangement;
use App\Models\Location;
use App\Services\DaysCalculator;
use App\Services\PriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ArrangementController extends Controller
{
    /**
     * Gets a list of arrangements, if a status is provided it will only return those with that status.
     *
     * The overview searches and sorts on the server and comes back per page, so the list stays
     * workable once the camping has been running for a few months.
     */
    public function index(Request $request, ?string $status = null): Response
    {
        if ($status && ArrangementStatus::tryFrom($status) === null) {
            abort(404, 'Status not found');
        }

        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string',
            'direction' => 'nullable|in:asc,desc',
        ]);

        $arrangements = Arrangement::with('customer', 'location')
            ->when($status, function (Builder $query) use ($status) {
                return $query->where('booking_status', $status);
            })
            ->where('status', '=', 1)
            ->filter($filters)
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Arrangements/Index', [
            'arrangements' => $arrangements,
            'filters' => $filters,
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
     * Changes the status of the reservation.
     *
     * Approving, rejecting, cancelling and checking in or out are separate
     * permissions, so the requested status decides what the user needs.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:arrangements,id',
            'status' => ['required', Rule::enum(ArrangementStatus::class)],
        ]);

        $status = ArrangementStatus::from($data['status']);

        if (! auth()->user()->hasPermissionTo($status->permission())) {
            abort(403, __('Je hebt geen toegang tot deze pagina.'));
        }

        $result = Arrangement::findOrFail($data['id']);
        $result->update(['booking_status' => $data['status']]);

        return response()->json(['status' => 'success!', 'updated_data' => $result]);

    }

    /**
     * Registers that the payment for a reservation came in.
     *
     * Confirming the reservation and confirming the payment are two different
     * things, so the front desk has a separate button for each of them.
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => 'required|integer|exists:arrangements,id',
            // The desk registers a payment that came in by bank; the guest may already
            // have picked a method online, in that case it is kept.
            'payment_method' => ['nullable', Rule::enum(PaymentMethod::class)],
        ]);

        $arrangement = Arrangement::findOrFail($data['id']);

        $method = isset($data['payment_method']) ? PaymentMethod::from($data['payment_method']) : null;

        if (! $arrangement->registerPayment($method)) {
            return response()->json([
                'status' => 'success!',
                'message' => __('Deze boeking is al betaald.'),
                'updated_data' => $arrangement,
            ]);
        }

        Mail::to($arrangement->customer->email)->send(new PaymentReceivedMail($arrangement));

        return response()->json(['status' => 'success!', 'updated_data' => $arrangement]);
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
