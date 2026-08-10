<?php

namespace App\Services;

use App\Models\Location;

/**
 * Calculates the total price of a booking.
 *
 * All prices are currently all-in, so the calculation is a simple multiplication.
 * Dynamic pricing for extras (electricity for example) can be added here later.
 */
class PriceCalculator
{
    public Location $location;

    public int $days;

    public function setDays(int $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function calculate(): int|float
    {
        return $this->location->price_per_night * $this->days;
    }
}
