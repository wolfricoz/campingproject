<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Brings the phone numbers that are already stored into the notation the
     * model now enforces, so existing customers stay findable at the desk.
     */
    public function up(): void
    {
        Customer::query()->chunkById(200, function ($customers): void {
            foreach ($customers as $customer) {
                $stored = $customer->getRawOriginal('phone_number');
                $normalised = Customer::normalisePhoneNumber($stored);

                if ($normalised !== $stored) {
                    $customer->update(['phone_number' => $normalised]);
                }
            }
        });
    }

    /**
     * The original notation is not recoverable, so there is nothing to undo.
     */
    public function down(): void {}
};
