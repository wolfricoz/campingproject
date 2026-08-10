<?php

namespace App\Enums;

enum ArrangementStatus: string
{
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CHECKEDIN = 'checked-in';
    case FINISHED = 'finished';

    /**
     * The permission an employee needs to move a reservation into this status.
     */
    public function permission(): string
    {
        return match ($this) {
            self::CANCELLED => 'cancel bookings',
            self::REJECTED => 'reject bookings',
            self::PENDING => 'edit bookings',
            self::CONFIRMED => 'approve bookings',
            self::CHECKEDIN => 'check in bookings',
            self::FINISHED => 'check out bookings',
        };
    }
}
