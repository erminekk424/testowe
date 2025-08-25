<?php

namespace App\Models;

use App\Casts\Money;
use App\Enums\ProductType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsFluent;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'game_id',
        'user_id',

        'name',
        'slug',
        'description',
        'image',

        'attributes',
        'type',

        'price',
        'currency',

        'min_quantity',
        'max_quantity',

        'attributes->rarity',
        'attributes->color',
    ];

    protected $casts = [
        'type' => ProductType::class,
        'attributes' => AsFluent::class,

        'price' => Money::class,
        'currency' => CurrencyAlpha3::class,
    ];

    /**
     * Get the game associated with the offer.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the user associated with the offer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user associated with the offer.
     */
    public function orders(): HasManyThrough|Product
    {
        return $this->hasManyThrough(
            Order::class,
            OrderItem::class,
            'product_id', // klucz w tabeli OrderItem
            'id', // klucz w tabeli Order (domyślnie, więc można pominąć)
            'id', // lokalny klucz w modelu Product (domyślnie, więc można pominąć)
            'order_id' // klucz w tabeli OrderItem
        );
    }

    /**
     * Get the quantity fluent object
     */
    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => fluent([
                'min' => $attributes['min_quantity'],
                'max' => $attributes['max_quantity'],
            ]),
        );
    }

    public function scopePrice(Builder $query, ?int $gt = null, ?int $lt = null): Builder
    {
        if ($gt !== null) {
            $gt *= 100;
        }
        if ($lt !== null) {
            $lt *= 100;
        }

        if ($gt !== null && $lt !== null) {
            if ($gt > $lt) {
                [$gt, $lt] = [$lt, $gt];
            }

            return $query->whereBetween('price', [$gt, $lt]);
        }

        if ($gt !== null) {
            return $query->where('price', '>', $gt);
        }

        if ($lt !== null) {
            $query->where('price', '<', $lt);

            return $query;
        }

        return $query;
    }

    public function scopeRarity(Builder $query, string $rarity): Builder
    {
        return $query->where('attributes->rarity', $rarity);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    #[SearchUsingPrefix(['name'])]
    #[SearchUsingFullText(['description'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'game_id' => $this->game_id,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $offer) {
            // produce a slug based on the offer name
            $slug = Str::slug($offer->name);

            // check to see if any other slugs exist that are the same & count them
            $count = static::whereRaw('slug ~* ?', ["^{$slug}(-[0-9]+)?$"])->count();

            // if other slugs exist that are the same, append the count to the slug
            $offer->slug = $count ? "{$slug}-{$count}" : $slug;
            $offer->uuid = (string) Str::uuid();
        });
    }
}
