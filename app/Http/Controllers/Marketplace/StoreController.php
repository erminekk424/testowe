<?php

namespace App\Http\Controllers\Marketplace;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarketplaceShowRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Product;
use App\Sorts\ProductPopularitySort;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
//use Laravel\Octane\Facades\Octane;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class StoreController extends Controller
{
    public function redirect()
    {
        $firstProduct = Product::whereHas('game', fn($q) => $q->whereNull('deleted_at'))->firstOrFail();

        return redirect()->route('marketplace.show', [
            'game' => $firstProduct->game,
            'productType' => $firstProduct->type,
        ]);
    }

    public function show(MarketplaceShowRequest $request, Game $game, ProductType $productType)
    {
        $page = $request->validated('page', 1);
        $filters = $request->only(['filter.name', 'filter.price', 'filter.rarity']);

//        [$games, $products] = Octane::concurrently([

//            store('octane')
        $games = Cache::
//                ->tags(['marketplace'])
        remember(key: 'marketplace:games',
            ttl: now()->addMinutes(2),
            callback: function () {
                return Game::select([
                    'games.id',
                    'games.name',
                    'games.slug',
                    'games.uuid',
                    DB::raw('json_agg(DISTINCT products.type) AS types'),
                ])
                    ->join('products', 'products.game_id', '=', 'games.id')
                    ->groupBy('games.id')
                    ->get()
                    ->map(function ($game) {
                        $game->types = is_string($game->types)
                            ? json_decode($game->types)
                            : $game->types;
                        return $game;
                    });
            }
        );

//            store('octane')
        $products = Cache::
//                ->tags(['marketplace'])
        remember(
            key: "marketplace:products:{$game->uuid}.{$productType->value}:page{$page}:" . md5(json_encode($filters)),
            ttl: now()->addMinutes(1),
            callback: function () use ($game, $productType, $filters, $page) {
                return QueryBuilder::for(Product::class)
                    ->where('game_id', $game->id)
                    ->where('type', $productType)
                    ->allowedFilters([
                        AllowedFilter::partial('name'),
                        AllowedFilter::scope('price'),
                        AllowedFilter::scope('rarity'),
                    ])
                    ->allowedSorts([
                        'price',
                        AllowedSort::custom('popularity', new ProductPopularitySort),
                    ])
                    ->paginate(
                        perPage: 10,
                        columns: [
                            'uuid',
                            'name',
                            'image',
                            'price',
                            'currency',
                            'attributes',
                            'min_quantity',
                            'max_quantity',
                        ],
                        page: $page
                    )
                    ->onEachSide(1);
            }
        );

//        ]);
//
//        // TODO: Paginate and cache!!
//        $products = QueryBuilder::for(Product::class)
//            ->where('game_id', $game->id)
//            ->where('type', $productType)
//            ->allowedFilters([
//                AllowedFilter::partial('name'),
//                AllowedFilter::scope('price'),
//                AllowedFilter::scope('rarity'),
//            ])
//            ->allowedSorts([
//                'price',
//                AllowedSort::custom('popularity', new ProductPopularitySort),
//            ])
//            ->paginate(
//                perPage: 10,
//                columns: [
//                    'uuid',
//                    'name',
//                    'image',
//                    'price',
//                    'currency',
//                    'attributes',
//                    'min_quantity',
//                    'max_quantity',
//                ],
//                page: $page
//            )
//            ->onEachSide(1);

        return Inertia::render('marketplace/show', [
            'games' => Inertia::defer(fn() => GameResource::collection($games))->merge(),
            'products' => Inertia::defer(fn() => $products->toResourceCollection())->merge(),
//            'productsCount' => Inertia::defer(fn() => $products->total),
            'marketplace' => [
                'gameId' => $game->uuid,
                'type' => $productType,
            ],
        ]);
//
//        return Inertia::render('marketplace/show', [
//            'products' => Inertia::defer(fn() => $products->toResourceCollection())->merge(),
//            'games' => Inertia::defer(fn() => GameResource::collection($games))->merge(),
//            'marketplace' => [
//                'gameId' => $game->uuid,
//                'type' => $productType,
//            ],
//        ]);
    }
}
