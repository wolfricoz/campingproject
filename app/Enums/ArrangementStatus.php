<?php

namespace App\Enums;

enum ArrangementStatus: string
{
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';
    CASE PENDING = 'pending';
    CASE CONFIRMED = 'confirmed';
    CASE CHECKEDIN = 'checked-in';
    CASE FINISHED = 'finished';
}
