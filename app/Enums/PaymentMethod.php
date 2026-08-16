<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case IDEAL = 'ideal';
    case BANK_TRANSFER = 'bank_transfer';

    /**
     * The name the guest sees on the payment page and the desk sees on the reservation.
     */
    public function label(): string
    {
        return match ($this) {
            self::IDEAL => 'iDeal',
            self::BANK_TRANSFER => 'Bankoverschrijving',
        };
    }
}
