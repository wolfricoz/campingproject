<?php

namespace App\Http\Controllers;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Returns the main page of the dashboard, including the data for the calendar
     * @return Response
     */
    public function index(Request $request, DateTime $month = null){
        // Set the dates for the beginning and end of the month

        if(!auth()->user()->hasPermissionTo('access dashboard')){
            return Inertia::render('Dashboard/Customer', [

            ]);
        }

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // We fetch the arrangements for the current month.
        $arrangements = Arrangement::with('customer', 'location')
            ->where(function (Builder $query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end]);
            })->whereIn('booking_status', [ArrangementStatus::CONFIRMED, ArrangementStatus::CHECKEDIN,
                ArrangementStatus::PENDING, ArrangementStatus::FINISHED])
            ->where('status', '=',1)
            ->get();



        //dd($arrangements);
        return Inertia::render('Dashboard', [
            'arrangements' => $arrangements,
        ]);
    }
}
