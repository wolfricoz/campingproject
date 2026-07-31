<?php

namespace App\Services;

// Currently all prices are ALL-in, so the calculations will be quite simple; in the future we could easily adapt
// this class to have dynamic pricing for extra features (eg. electricity).

use App\Models\Location;

class PriceCalculator
{
    public Location $location;
    public int $days;


    /**
     * @param int $days
     * @return $this
     */
    public function setDays(int $days): self
    {
        $this->days = $days;
        return $this;
    }

    /**
     * @param Location $location
     * @return $this
     */
    public function setLocation(Location $location): self
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return float|int
     */
    public function calculate(): int | float  {
        // Because the calculation is simple, we currently just calculate and return. In the future we can include
        // indepth calculations and variables.
        $pricePerNight = $this->location->price_per_night;

        return $pricePerNight * $this->days;

    }


}
