<?php

namespace App\Services;

/**
 * Calculates the amount of days that has to be charged for a booking.
 *
 * Customers are expected to check out before 11:00; any booking that runs
 * beyond that is charged an additional day.
 */
class DaysCalculator
{
    private \DateTime $start;

    private \DateTime $end;

    public function setStart(\DateTime $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Calculate the amount of days between the two dates, with a five minute
     * grace period for customers who check out at exactly 11:00.
     */
    public function calculate(): int
    {
        $startDay = (clone $this->start)->setTime(0, 0);
        $endDay = (clone $this->end)->setTime(0, 0);
        $days = $startDay->diff($endDay)->days;

        $deadline = (clone $this->start)->setTime(11, 5);
        if ($endDay > $deadline) {
            $days++;
        }

        if ($days < 1) {
            return 1;
        }

        return $days;
    }
}
