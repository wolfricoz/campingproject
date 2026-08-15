<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AnonymizeCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:anonymize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all identifiable traits from customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Anonymizing customers...');
        $count = 0;

        $customers = Customer::join('arrangements', 'customers.id', '=', 'arrangements.customer_id')
            ->where('customers.created_at', '<', now()->subDays(2556))
            ->select('customers.*', 'arrangements.created_at as arrangement_created_at')
            ->latest('arrangements.created_at')->get();

        foreach ($customers as $customer) {

            // check their last arrangement; this way we dont remove users who still use our services.
            if (Carbon::parse($customer->arrangement_created_at)->lt(now()->subYears(7))) {

                $customer->anonymize();
                $count++;
            }
        }

        $this->info('Anonymized' . $count .  'customers!');

    }
}
