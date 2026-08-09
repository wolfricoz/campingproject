<?php

namespace App\Http\Controllers;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Returns the main page of the dashboard, including the data for the calendar
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if (! auth()->user()->hasPermissionTo('access dashboard')) {
            $reservations = Arrangement::with('location')
                ->where('customer_id', auth()->user()->customer->id)
                ->orderByDesc('created_at')
                ->get();

            return Inertia::render('Customer/Dashboard', [
                'reservations' => $reservations,

            ]);
        }

        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = isset($data['month'])
            ? Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth()
            : Carbon::now()->startOfMonth();

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $arrangements = Arrangement::with('customer', 'location')
            ->where(function (Builder $query) use ($start, $end) {
                $query->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })->whereIn('booking_status', [ArrangementStatus::CONFIRMED, ArrangementStatus::CHECKEDIN,
                ArrangementStatus::PENDING, ArrangementStatus::FINISHED])
            ->where('status', '=', 1)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'arrangements' => $arrangements,
            'month' => $month->format('Y-m'),
        ]);
    }
}
