<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use PrinsFrank\Standards\Currency\CurrencyAlpha3;

class Money implements CastsAttributes
{
    /**
     * Create a new cast class instance.
     */
    public function __construct(
        protected ?CurrencyAlpha3 $currency = null,
    ) {}

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return \Brick\Money\Money::ofMinor($value, $this->currency ?? $attributes['currency']);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! $value instanceof \Brick\Money\Money) {
            return $value;
        }

        return [
            $key => $value->getMinorAmount()->toInt(),
            'currency' => $value->getCurrency()->getCurrencyCode(),
        ];
        //        return $value->getMinorAmount()->toInt();
    }
}
