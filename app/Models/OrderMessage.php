<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderMessage extends Model
{
    /** @use HasFactory<\Database\Factories\OrderMessage> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'message',
    ];

    protected $with = [
        'user',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (OrderMessage $orderMessage) {
            $orderMessage->uuid = (string) Str::uuid();
        });
    }
}
