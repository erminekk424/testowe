<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Game $game) {
            // produce a slug based on the game name
            $slug = Str::slug($game->name);

            // check to see if any other slugs exist that are the same & count them
            $count = static::whereRaw('slug ~* ?', ["^{$slug}(-[0-9]+)?$"])->count();

            // if other slugs exist that are the same, append the count to the slug
            $game->slug = $count ? "{$slug}-{$count}" : $slug;
            $game->uuid = (string) Str::uuid();
        });
    }
}
