<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Transfer = 'transfer';
    case PaySafeCard = 'paysafecard';

    public function calculateCommission(float $amount, float $rate): float
    {
        $percent = match ($this) {
            self::Transfer => 0.0095,
            self::PaySafeCard => 0.14,
        };

        return round(($amount + ($amount * $percent) + (0.30 / $rate)) * $rate, 2);
    }

    public function notificationPassword(): string
    {
        return match ($this) {
            self::Transfer => config('services.hotpay.transfer.notification_password'),
            self::PaySafeCard => config('services.hotpay.paysafecard.notification_password'),
        };
    }

    public function secret(): string
    {
        return match ($this) {
            self::Transfer => config('services.hotpay.transfer.secret'),
            self::PaySafeCard => config('services.hotpay.paysafecard.secret'),
        };
    }
}
