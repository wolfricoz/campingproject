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
}
