<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',

        'amount',
        'currency',

        'metadata',

        'status',
    ];

    protected $casts = [
        'amount' => Money::class,
        'currency' => CurrencyAlpha3::class,
        'status' => OrderStatus::class,
        'metadata' => 'array',
    ];

    protected $attributes = [
        'metadata' => '[]',
    ];

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the order items.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    /**
     * Get the user associated with the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order's payments.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'paymentable');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->uuid = (string) Str::uuid();
        });
    }
}
