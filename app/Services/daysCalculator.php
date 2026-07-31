<?php

namespace App\Services;

use App\Models\Location;
// We calculate the amount of days we have to charge people, customers are expected to checkout before  11 am,
// otherwise it'll be another day. Any booking that goes beyond 11 am will be charged an additional day.

class daysCalculator
{

    private \DateTime $start;
    private \DateTime $end;


    /**
     * @param \DateTime $start
     * @return $this
     */
    public function setStart(\DateTime $start): self
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @param \DateTime $end
     * @return $this
     */
    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Calculate the amount of days between the two dates.
     *
     * @return int
     */
    public function calculate(): int
    {
        // we calculate the difference between the dates first
        $startDay = (clone $this->start)->setTime(0, 0);
        $endDay = (clone $this->end)->setTime(0, 0);
        $days = $startDay->diff($endDay)->days;

        // then we check the hours of the final day, with a 5-minute grace period for people who may book on 11 am
        // exact. We don't want to charge them another day.
        $deadline = (clone $this->start)->setTime(11, 5);
        if ($endDay > $deadline){
            $days++;
        }
        if ($days < 1){
            return 1;
        }

        return $days;
    }


}
