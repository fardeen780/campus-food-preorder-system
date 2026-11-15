<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Bkash = 'bkash';
    case Nagad = 'nagad';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Bkash => 'bKash',
            self::Nagad => 'Nagad',
        };
    }
}