<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class AnonymizeCustomers extends Command
{
    /**
     * The amount of years a customer's details are kept after their last stay.
     */
    private const RETENTION_YEARS = 7;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:anonymize';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Removes all identifiable traits from customers';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Anonymizing customers...');

        $cutoff = now()->subYears(self::RETENTION_YEARS);

        // We ask for the customers themselves and not for a join on their bookings.
        // A join returns one row per booking, and every row would then be judged on
        // its own: a guest who booked eight years ago and came back last month would
        // be anonymized on that first booking, which is exactly what we want to avoid.
        $customers = Customer::query()
            ->where('created_at', '<', $cutoff)
            ->whereHas('arrangements')
            ->whereDoesntHave('arrangements', function (Builder $query) use ($cutoff): void {
                $query->where('created_at', '>=', $cutoff);
            })
            ->get();

        foreach ($customers as $customer) {
            $customer->anonymize();
        }

        $this->info('Anonymized '.$customers->count().' customers!');
    }
}
