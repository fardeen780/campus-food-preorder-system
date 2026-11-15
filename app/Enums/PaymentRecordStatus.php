<?php

namespace App\Enums;

enum PaymentRecordStatus: string
{
    case Successful = 'successful';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Successful => 'Successful',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
        };
    }
}