<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',

        'amount',
        'currency',

        'status',
        'method',

        'external_id',
    ];

    protected $casts = [
        'amount' => Money::class,
        'currency' => CurrencyAlpha3::class,

        'status' => PaymentStatus::class,
        'method' => PaymentMethod::class,
    ];

    /**
     * Get the user associated with the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent paymentable model (order or later something else).
     */
    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->uuid = (string) Str::uuid();
        });
    }
}
